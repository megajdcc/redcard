<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace negocio\libs;
use assets\libs\connection;
use PDO;

class content_gallery {
	private $con;
	private $business = array(
		'id' => null, 
		'url' => null,
		'gallery' => array(),
		'featured' => array(),
		'title' => null
	);
	private $error = array(
		'notification' => null,
		'image' => null,
		'title' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->business['id'] = $_SESSION['business']['id_negocio'];
		$this->business['url'] = $_SESSION['business']['url'];
		$this->load_data();
		return;
	}

	private function load_data(){
		$query = "SELECT p.id_preferencia, np.preferencia
			FROM preferencia p 
			LEFT JOIN negocio_preferencia np ON p.id_preferencia = np.id_preferencia AND np.id_negocio = ?
			WHERE p.llave = 'business_featured_image'";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(1, $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['featured'] = array('id' => $row['id_preferencia'], 'preference' => $row['preferencia'], 'gallery_id' => null);
		}
		$query = "SELECT id_imagen, titulo, imagen, situacion FROM negocio_imagen WHERE id_negocio = ? AND situacion != 0";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(1, $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->business['gallery'][$row['id_imagen']] = array('title' => $row['titulo'], 'image' => $row['imagen'], 'situacion' => $row['situacion']);
			if($this->business['featured']['preference'] == $row['imagen']){
				$this->business['featured']['gallery_id'] = $row['id_imagen'];
			}
		}
		return;
	}

	public function set_image(array $post, $files = null){
		$this->set_title($post['title']);
		if(!array_filter($this->error)){
			// RECORTAR NOMBRE DE IMAGEN
			$image_prefix = _safe('-'.$this->business['url'].'-esmart-club');
			$max = 150 - strlen($image_prefix);
			$safe_name = $this->friendly_url($this->business['title']);
			if(strlen($safe_name) > $max){
				$file = substr($safe_name, 0, $max);
			}else{
				$file = $safe_name;
			}
			$file_name = $file.$image_prefix;

			$image = new \assets\libraries\bulletproof\bulletproof($files);
			$image->setName($file_name);
			$image->setLocation(ROOT.'/assets/img/business/gallery');
			if($image['image']){
				if($image->upload()){
					// REVISAR QUE SEA UNICA
					try{
						$query = "SELECT 1 FROM negocio_imagen WHERE imagen = :imagen";
						$stmt = $this->con->prepare($query);
						$stmt->bindValue(':imagen', $image->getName().'.'.$image->getMime(), PDO::PARAM_STR);
						$stmt->execute();
					}catch(\PDOException $ex){
						$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
						return false;
					}
					if($row = $stmt->fetch()){
						$image->setName($image->getName().'-'.time()); // AGREGAR EL TIEMPO SI NO LO ES
					}
					$file_name = $image->getName().'.'.$image->getMime();
					move_uploaded_file($files['image']['tmp_name'], $image->getFullPath());
					$query = "INSERT INTO negocio_imagen (
						id_negocio,
						imagen,
						titulo
						) VALUES (
						:id_negocio,
						:imagen,
						:titulo
					)";
					$params = array(
						':id_negocio' => $this->business['id'],
						':imagen' => $file_name,
						':titulo' => $this->business['title']
					);
					try{
						$stmt = $this->con->prepare($query);
						$stmt->execute($params);
					}catch(\PDOException $ex){
						$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
						return false;
					}
					if(is_null($this->business['featured']['preference'])){
						$query = "INSERT INTO negocio_preferencia (
							id_negocio,
							id_preferencia,
							preferencia
							) VALUES (
							:id_negocio,
							:id_preferencia,
							:preferencia
						)";
						$params = array(
							':id_negocio' => $this->business['id'],
							':id_preferencia' => $this->business['featured']['id'],
							':preferencia' => $file_name
						);
						try{
							$stmt = $this->con->prepare($query);
							$stmt->execute($params);
						}catch(\PDOException $ex){
							$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
							return false;
						}
					}
					$_SESSION['notification']['success'] = 'Imagen subida correctamente';
					header('Location: '.HOST.'/negocio/contenidos/galeria');
					die();
					return;
				}
				$this->error['image'] = $image['error'];
				return false;
			}
			if($files['image']['error'] == 1){
				$this->error['image'] = 'Has excedido el límite de imagen de 2MB.';
			}else{
				$this->error['image'] = 'Este campo es obligatorio.';
			}
			return false;
		}
		return false;
	}

	public function default_image(array $post){
		if(array_key_exists($post['id'], $this->business['gallery'])){
			if($this->business['gallery'][$post['id']]['situacion'] == 2){
				$query = "UPDATE negocio_imagen SET situacion = 1 WHERE id_imagen = ?";
				try{
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(1, $post['id'], PDO::PARAM_INT);
					$stmt->execute();
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
			}
			$query = "UPDATE negocio_preferencia 
				SET preferencia = :preferencia 
				WHERE id_preferencia = :id_preferencia AND id_negocio = :id_negocio";
			$params = array(
				':preferencia' => $this->business['gallery'][$post['id']]['image'], 
				':id_preferencia' => $this->business['featured']['id'], 
				':id_negocio' => $this->business['id']
			);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$_SESSION['notification']['success'] = 'Imagen seleccionada como predeterminada en la galería del perfil.';
			header('Location: '.HOST.'/negocio/contenidos/galeria');
			die();
			return;
		}
	}

	public function hide_image(array $post){
		if(array_key_exists($post['id'], $this->business['gallery'])){
			$query = "UPDATE negocio_imagen SET situacion = 2 WHERE id_imagen = ?";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(1, $post['id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($this->business['featured']['preference'] == $this->business['gallery'][$post['id']]['image']){
				unset($this->business['gallery'][$post['id']]);
				$current = current($this->business['gallery']);
				if($current && $current['situacion'] == 1){
					$query = "UPDATE negocio_preferencia 
						SET preferencia = :preferencia 
						WHERE id_preferencia = :id_preferencia AND id_negocio = :id_negocio";
					$params = array(
						':preferencia' => $current['image'], 
						':id_preferencia' => $this->business['featured']['id'], 
						':id_negocio' => $this->business['id']
					);
					try{
						$stmt = $this->con->prepare($query);
						$stmt->execute($params);
					}catch(\PDOException $ex){
						$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
						return false;
					}
				}
			}
			$_SESSION['notification']['success'] = 'La imagen se ha ocultado correctamente. Ya no será visible en el perfil de negocio.';
			header('Location: '.HOST.'/negocio/contenidos/galeria');
			die();
			return;
		}
	}

	public function show_image(array $post){
		if(array_key_exists($post['id'], $this->business['gallery'])){
			$query = "UPDATE negocio_imagen SET situacion = 1 WHERE id_imagen = ?";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(1, $post['id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($this->business['gallery'][$this->business['featured']['gallery_id']]['situacion'] == 2){
				$query = "UPDATE negocio_preferencia 
					SET preferencia = :preferencia 
					WHERE id_preferencia = :id_preferencia AND id_negocio = :id_negocio";
				$params = array(
					':preferencia' => $this->business['gallery'][$post['id']]['image'], 
					':id_preferencia' => $this->business['featured']['id'], 
					':id_negocio' => $this->business['id']
				);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
			}
			$_SESSION['notification']['success'] = 'La imagen se muestra correctamente. Ahora es visible en el perfil de negocio.';
			header('Location: '.HOST.'/negocio/contenidos/galeria');
			die();
			return;
		}
	}

	public function delete_image(array $post){
		if(array_key_exists($post['id'], $this->business['gallery'])){
			$query = "UPDATE negocio_imagen SET situacion = 0 WHERE id_imagen = ?";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(1, $post['id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			unset($this->business['gallery'][$post['id']]);
			$current = current($this->business['gallery']);
			if($current){
				$query = "UPDATE negocio_preferencia 
					SET preferencia = :preferencia 
					WHERE id_preferencia = :id_preferencia AND id_negocio = :id_negocio";
				$params = array(
					':preferencia' => $current['image'], 
					':id_preferencia' => $this->business['featured']['id'], 
					':id_negocio' => $this->business['id']
				);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
			}else{
				$query = "DELETE FROM negocio_preferencia WHERE id_preferencia = :id_preferencia AND id_negocio = :id_negocio";
				$params = array(
					':id_preferencia' => $this->business['featured']['id'], 
					':id_negocio' => $this->business['id']
				);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
			}
			$_SESSION['notification']['success'] = 'Imagen eliminada correctamente.';
			header('Location: '.HOST.'/negocio/contenidos/galeria');
			die();
			return;
		}
	}

	private function set_title($string = null){
		if($string){
			$string = trim($string);
			$this->business['title'] = $string;
			return true;
		}
		$this->error['title'] = 'Este campo es obligatorio.';
		return false;
	}

	public function get_gallery(){
		$html = null;
		foreach ($this->business['gallery'] as $key => $value) {
			if($value['situacion'] == 1){
				$btn = '<button class="btn btn-xs btn-primary" type="submit" name="hide" data-toggle="tooltip" title="Ocultar al p&uacute;blico"><i class="fa fa-eye-slash m0"></i></button>';
			}elseif($value['situacion'] == 2){
				$btn = '<button class="btn btn-xs btn-success" type="submit" name="show" data-toggle="tooltip" title="Mostrar al p&uacute;blico"><i class="fa fa-eye m0"></i></button>';
			}else{
				$btn = '';
			}
			if($value['image'] == $this->business['featured']['preference']){
				$feat = '<span class="btn btn-xs btn-secondary" data-toggle="tooltip" title="Imagen predeterminada"><i class="fa fa-star m0"></i></span>';
			}else{
				$feat = '<button class="btn btn-xs btn-default" type="submit" name="default" data-toggle="tooltip" title="Hacer predeterminada"><i class="fa fa-star-o m0"></i></button>';
			}
			$html .= 
			'<div class="col-sm-6 col-md-3 detail-gallery-preview mb30">
				<a href="'.HOST.'/assets/img/business/gallery/'._safe($value['image']).'">
					<img class="img-thumbnail img-rounded gallery-img" src="'.HOST.'/assets/img/business/gallery/'.$value['image'].'" title="'._safe($value['title']).'" alt="'._safe($value['title']).'">
				</a>
				<div class="p15 center">
					<form method="post" action="'._safe(HOST.'/negocio/contenidos/galeria#galeria').'">
						<input type="hidden" value="'.$key.'" name="id">
						'.$feat.'
						'.$btn.'
						<button class="btn btn-xs btn-danger delete-gallery-image" type="submit" name="delete" data-toggle="tooltip" title="Eliminar imagen"><i class="fa fa-trash-o m0"></i></button>
					</form>
				</div>
			</div>';
		}
		if(is_null($html)){
			$html = '<p>No hay im&aacute;genes en la galer&iacute;a. Sube una para mostrarla en el perfil de tu negocio.</p>';
		}
		return $html;
	}

	public function get_image_error(){
		if($this->error['image']){
			$error = '<p class="text-danger">'.$this->error['image'].'</p>';
			return $error;
		}
	}

	public function get_title(){
		return _safe($this->business['title']);
	}

	public function get_title_error(){
		if($this->error['title']){
			$error = '<p class="text-danger">'.$this->error['title'].'</p>';
			return $error;
		}
	}

	public function get_url(){
		return _safe($this->business['url']);
	}

	public function get_profile_url(){
		return HOST.'/'._safe($this->business['url']);
	}

	public function get_notification(){
		if(isset($_SESSION['notification']['success'])){
			$notification = 
			'<div class="alert alert-icon alert-dismissible alert-success" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notification']['success']).'
			</div>';
			unset($_SESSION['notification']['success']);
			return $notification;
		}
		if($this->error['notification']){
			$error = 
			'<div class="alert alert-icon alert-dismissible alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->error['notification']).'
			</div>';
			return $error;
		}
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\content_gallery.txt', '['.date('d/M/Y h:i:s A').' | '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['notification'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}

	private function friendly_url($url){
		// 1. No spaces to begin or end, replace all special characters and lowercase everything
		$url = strtolower($this->replace_accents(trim($url)));
		// decode html maybe needed if there's html I normally don't use this
		//$url = html_entity_decode($url,ENT_QUOTES,'UTF8');
		// 2. Replacing spaces and union characters with -
		$find = array(' ', '&', '\r\n', '\n', '+',',');
		$url = str_replace($find, '-', $url);
		// 3. Delete and replace the rest of special characters
		$find = array('/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/');
		$repl = array('', ' ', '');
		$url = str_replace(' ','-',trim(preg_replace($find, $repl, $url)));
		return $url;
	}

	private function replace_accents($var){
		$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
		$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
		$var = str_replace($a, $b, $var);
		return $var;
	}
}
?>