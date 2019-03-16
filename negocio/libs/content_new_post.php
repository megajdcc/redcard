<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace negocio\libs;
use assets\libs\connection;
use PDO;

class content_new_post {
	private $con;
	private $business = array(
		'id' => null, 
		'url' => null,
		'image' => array('tmp_name' => null, 'file_name' => null, 'path' => null),
		'title' => null,
		'content' => null
	);
	private $error = array(
		'notification' => null,
		'image' => null,
		'title' => null,
		'content' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->business['id'] = $_SESSION['business']['id_negocio'];
		$this->business['url'] = $_SESSION['business']['url'];
		return;
	}

	public function set_post(array $post, array $files){
		$this->set_title($post['title']);
		$this->set_content($post['content']);
		$this->set_image($files);
		if(!array_filter($this->error)){
			if($this->business['image']['tmp_name'] && $this->business['image']['path']){
				if(!move_uploaded_file($this->business['image']['tmp_name'], $this->business['image']['path'])){
					$this->error['notification'] = 'La publicación no se ha podido crear correctamente.';
					return false;
				}
			}
			$query = "INSERT INTO negocio_publicacion (id_negocio, titulo, contenido, imagen) 
				VALUES (:id_negocio, :titulo, :contenido, :imagen)";
			$params = array(
				':id_negocio' => $this->business['id'], 
				':titulo' => $this->business['title'], 
				':contenido' => $this->business['content'],
				'imagen' => $this->business['image']['file_name']
			);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$_SESSION['notification']['success'] = 'Publicación creada exitosamente.';
			header('Location: '.HOST.'/negocio/contenidos/publicaciones');
			die();
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

	public function get_image_error(){
		if($this->error['image']){
			$error = '<p class="text-danger">'.$this->error['image'].'</p>';
			return $error;
		}
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
		file_put_contents(ROOT.'\assets\error_logs\content_new_post.txt', '['.date('d/M/Y h:i:s A').' | '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
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