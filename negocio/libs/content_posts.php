<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace negocio\libs;
use assets\libs\connection;
use PDO;

class content_posts {
	private $con;
	private $business = array(
		'id' => null, 
		'url' => null,
		'posts' => array(),
		'post_id' => null,
		'image' => array('tmp_name' => null, 'file_name' => null, 'path' => null),
		'title' => null,
		'content' => null,
		'modal' => null
	);
	private $error = array(
		'image' => null,
		'title' => null,
		'content' => null,
		'modal' => false,
		'warning' => null,
		'error' => null
	);
	private $pagination = array(
		'total' => null,
		'rpp' => null,
		'max' => null,
		'page' => null,
		'offset' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->business['id'] = $_SESSION['business']['id_negocio'];
		$this->business['url'] = $_SESSION['business']['url'];
		return;
	}

	public function load_data($page = null, $rpp = null){
		$query = "SELECT COUNT(*) FROM negocio_publicacion WHERE situacion = 1 AND id_negocio = :id_negocio";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->pagination['total'] = $row['COUNT(*)'];
			$this->pagination['rpp'] = $rpp;
			$this->pagination['max'] = (int)ceil($this->pagination['total'] / $this->pagination['rpp']);
			$this->pagination['page'] = min($this->pagination['max'], $page);
			$this->pagination['offset'] = ($this->pagination['page'] - 1) * $this->pagination['rpp'];
			// Variables retornables
			$pagination['page'] = $this->pagination['page'];
			$pagination['total'] = $this->pagination['total'];
			// Cargar los posts
			$query = "SELECT id_publicacion, titulo, contenido, imagen, creado 
				FROM negocio_publicacion 
				WHERE id_negocio = :id_negocio AND situacion = 1 
				ORDER BY creado DESC LIMIT :limit OFFSET :offset";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
				$stmt->bindValue(':limit', $this->pagination['rpp'], PDO::PARAM_INT);
				$stmt->bindValue(':offset', $this->pagination['offset'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->business['posts'][$row['id_publicacion']] = array(
					'title' => $row['titulo'],
					'content' => $row['contenido'],
					'image' => $row['imagen'],
					'created_at' => $row['creado']
				);
			}
			return $pagination;
		}
		return false;
	}

	public function get_posts(){
		$html = null;
		foreach ($this->business['posts'] as $key => $value) {
			$title = _safe($value['title']);
			$content = _safe($value['content']);
			$date['created'] = date('d/m/Y \a \l\a\s g:i A', strtotime($value['created_at']));
			$image = _safe($value['image']);
			if($this->error['modal'] && $this->business['post_id'] == $key){
				$image_error = $this->get_image_error();
				$title_error = $this->get_title_error();
				$content_error = $this->get_content_error();
				$this->business['modal'] = 
				'<script>
					$("#modal-'.$key.'").modal("show");
				</script> ';
			}else{
				$image_error = $title_error = $content_error = null;
			}
			if($image){
				$image_html = 
					'<div class="col-xs-6 col-sm-3 detail-gallery-preview">
						<a href="'.HOST.'/assets/img/business/post/'.$image.'">
							<img class="img-thumbnail img-rounded post-img" src="'.HOST.'/assets/img/business/post/'.$image.'">
						</a>
					</div>
					<div class="col-xs-6 col-sm-9">';
			}else{
				$image_html = '<div class="col-sm-12">';
			}
			$html .= 
			'<div class="background-white p20 mb30">
				<div class="row">
					'.$image_html.'
						<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'">
							<div>
								<input type="hidden" name="id" value="'.$key.'">
								<button class="btn btn-xs btn-danger pull-right delete-post" type="submit" name="delete_post"><i class="fa fa-times m0"></i></button>
							</div>
						</form>
						<button class="btn btn-xs btn-info pull-right edit-post mr5" type="button" data-toggle="modal" data-target="#modal-'.$key.'"><i class="fa fa-pencil m0"></i></button>
						<strong class="text-default">'.$title.'</strong>
						<p>'.nl2br($content).'</p>
					</div>
				</div>
			</div>
			<div class="modal fade" id="modal-'.$key.'" tabindex="-1" role="dialog" aria-labelledby="label-'.$key.'">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="label-'.$key.'">Editar publicaci&oacute;n</h4>
						</div>
						<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'" enctype="multipart/form-data">
							<div class="modal-body">
								<div class="p30">
									<div class="form-group" data-toggle="tooltip" title="Puedes actualizar la imagen o dejar el campo en blanco para conservar la actual">
										<label for="post-image">Foto <i class="fa fa-question-circle text-secondary"></i></label>
										<input type="file" id="post-image" name="image">
										'.$image_error.'
									</div><!-- /.form-group -->
									<div class="form-group">
										<label for="title">T&iacute;tiulo <span class="required">*</span></label>
										<input class="form-control" type="text" id="title" name="title" value="'.$title.'" placeholder="T&iacute;tiulo" required>
										'.$title_error.'
									</div><!-- /.form-group -->
									<div class="form-group">
										<label for="content">Contenido <span class="required">*</span></label>
										<textarea class="form-control" id="content" name="content" rows="5" placeholder="Contenido" required>'.$content.'</textarea>
										'.$content_error.'
									</div><!-- /.form-group -->
								</div>
							</div>
							<div class="modal-footer">
								<input type="hidden" name="id" value="'.$key.'">
								<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
								<button type="submit" class="btn btn-success" name="edit_post">Guardar cambios</button>
							</div>
						</form>
					</div>
				</div>
			</div>';
		}
		if(is_null($html)){
			$html = '<div class="background-white p30"><h4>No hay publicaciones</h4></div>';
		}
		return $html;
	}

	public function delete_post(array $post){
		if(array_key_exists($post['id'], $this->business['posts'])){
			$query = "UPDATE negocio_publicacion SET situacion = 0 WHERE id_negocio = :id_negocio AND id_publicacion = :id_publicacion";
			$params = array(':id_negocio' => $this->business['id'], 'id_publicacion' => $post['id']);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$_SESSION['notification']['success'] = 'Publicación eliminada correctamente.';
			header('Location: '._safe($_SERVER['REQUEST_URI']));
			die();
		}
		return;
	}

	public function edit_post(array $post, array $files){
		if(array_key_exists($post['id'], $this->business['posts'])){
			$this->business['post_id'] = $post['id'];
			$this->set_title($post['title']);
			$this->set_content($post['content']);
			$this->set_image($files);
			if(!array_filter($this->error)){
				if($this->business['image']['tmp_name'] && $this->business['image']['path']){
					if(file_exists(ROOT.'/assets/img/business/post/'.$this->business['posts'][$post['id']]['image'])){
						unlink(ROOT.'/assets/img/business/post/'.$this->business['posts'][$post['id']]['image']);
					}
					if(!move_uploaded_file($this->business['image']['tmp_name'], $this->business['image']['path'])){
						$this->error['error'] = 'La publicación no se ha podido editar correctamente.';
						return false;
					}
					$file_name = $this->business['image']['file_name'];
				}else{
					$file_name = $this->business['posts'][$post['id']]['image'];
				}
				$query = "UPDATE negocio_publicacion SET 
					titulo = :titulo,
					contenido = :contenido,
					imagen = :imagen
					WHERE id_negocio = :id_negocio AND id_publicacion = :id_publicacion";
				$params = array(
					':titulo' => $this->business['title'],
					':contenido' => $this->business['content'],
					':imagen' => $file_name,
					':id_negocio' => $this->business['id'],
					':id_publicacion' => $post['id']
				);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				$_SESSION['notification']['success'] = 'Publicación editada correctamente.';
				header('Location: '._safe($_SERVER['REQUEST_URI']));
				die();
			}
			$this->error['warning'] = 'Uno o más campos tienen errores. Revísalos cudiadosamente.';
			$this->error['modal'] = true;
			return false;
		}
		return;
	}

	private function set_image($files = null){
		if(!$this->business['title']){
			return;
		}
		// RECORTAR NOMBRE DE IMAGEN
		$image_prefix = _safe('-'.$this->business['url'].'-publicacion-esmart-club');
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
		$image->setLocation(ROOT.'/assets/img/business/post');
		if($image['image']){
			if($image->upload()){
				// REVISAR QUE SEA UNICA
				try{
					$query = "SELECT 1 FROM negocio_publicacion WHERE imagen = :imagen";
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
				$this->business['image']['tmp_name'] = $files['image']['tmp_name'];
				$this->business['image']['file_name'] = $image->getName().'.'.$image->getMime();
				$this->business['image']['path'] = $image->getFullPath();
				return true;
			}
			$this->error['image'] = $image['error'];
			return false;
		}
		if($files['image']['error'] == 1){
			$this->error['image'] = 'Has excedido el límite de imagen de 2MB.';
		}
		return;
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

	private function set_content($string = null){
		if($string){
			$string = trim($string);
			$this->business['content'] = $string;
			return true;
		}
		$this->error['content'] = 'Este campo es obligatorio.';
		return false;
	}

	public function get_image_error(){
		if($this->error['image']){
			return '<p class="text-danger">'.$this->error['image'].'</p>';
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

	public function get_content(){
		return _safe($this->business['content']);
	}

	public function get_content_error(){
		if($this->error['content']){
			$error = '<p class="text-danger">'.$this->error['content'].'</p>';
			return $error;
		}
	}

	public function show_modal(){
		return $this->business['modal'];
	}

	public function get_profile_url(){
		return HOST.'/'._safe($this->business['url']);
	}

	public function get_notification(){
		$html = null;
		if(isset($_SESSION['notification']['success'])){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-success" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notification']['success']).'
			</div>';
			unset($_SESSION['notification']['success']);
		}
		if(isset($_SESSION['notification']['info'])){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-info" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notification']['info']).'
			</div>';
			unset($_SESSION['notification']['info']);
		}
		if($this->error['warning']){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-warning" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->error['warning']).'
			</div>';
		}
		if($this->error['error']){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->error['error']).'
			</div>';
		}
		return $html;
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\content_posts.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
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