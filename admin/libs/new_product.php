<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace admin\libs;
use assets\libs\connection;
use PDO;

class new_product {
	private $con;
	private $product = array(
		'name' => null,
		'category' => null,
		'price' => null,
		'quantity' => null,
		'description' => null,
		'conditions' => null,
		'send_price' => null,
		'image' => array('tmp_name' => null, 'file_name' => null, 'path' => null),
		'coupon' => array('tmp_name' => null, 'file_name' => null, 'path' => null),
	);
	private $categories = array();
	private $error = array(
		'image' => null,
		'coupon' => null,
		'name' => null,
		'description' => null,
		'price' => null,
		'quantity' => null,
		'category' => null,
		'send_price' => null,
		'conditions' => null,
		'warning' => null,
		'error' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->load_data();
		return;
	}

	private function load_data(){
		$query = "SELECT id_categoria, categoria FROM producto_categoria ";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row =  $stmt->fetch()){
			$this->categories[$row['id_categoria']] = $row['categoria'];
		}
		return;
	}

	public function new_product(array $post, array $files){
		$this->set_name($post['name']);
		$this->set_description($post['description']);
		$this->set_category($post['category']);
		$this->set_price($post['price']);
		$this->set_quantity($post['quantity']);
		$this->set_send_price($post['send_price']);
		$this->set_conditions($post['conditions']);
		$this->set_image($files);
		$this->set_coupon($files);
		if(!array_filter($this->error)){
			if(!move_uploaded_file($this->product['image']['tmp_name'], $this->product['image']['path'])){
				$this->error['error'] = 'El producto no se ha podido subir correctamente.';
				return false;
			}
			if($this->product['category'] == 2 && $this->product['coupon']['tmp_name'] && $this->product['coupon']['path']){
				if(!move_uploaded_file($this->product['coupon']['tmp_name'], $this->product['coupon']['path'])){
					$this->error['error'] = 'El producto no se ha podido subir correctamente.';
					return false;
				}
			}else{
				$this->product['coupon']['file_name'] = null;
			}
			$query = "INSERT INTO producto (nombre, descripcion, id_categoria, precio, disponibles, envio, condiciones, imagen, cupon) 
				VALUES (:nombre, :descripcion, :id_categoria, :precio, :disponibles, :envio, :condiciones, :imagen, :cupon)";
			$params = array(
				':nombre' => $this->product['name'], 
				':descripcion' => $this->product['description'], 
				':id_categoria' => $this->product['category'],
				':precio' => $this->product['price'],
				':disponibles' => $this->product['quantity'],
				':envio' => $this->product['send_price'],
				':condiciones' => $this->product['conditions'],
				':imagen' => $this->product['image']['file_name'],
				':cupon' => $this->product['coupon']['file_name']
			);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$_SESSION['notification']['success'] = 'Producto subido exitosamente.';
			header('Location: '.HOST.'/admin/tienda/nuevo-producto');
			die();
			return;
		}
		$this->error['warning'] = 'Uno o más campos tienen errores. Verifícalos cuidadosamente.';
		return false;
	}

	private function set_image($files = null){
		if(!$this->product['name']){
			return;
		}
		// RECORTAR NOMBRE DE IMAGEN
		$image_prefix = _safe('-producto-esmart-club');
		$max = 150 - strlen($image_prefix);
		$safe_name = $this->friendly_url($this->product['name']);
		if(strlen($safe_name) > $max){
			$file = substr($safe_name, 0, $max);
		}else{
			$file = $safe_name;
		}
		$file_name = $file.$image_prefix;
		$image = new \assets\libraries\bulletproof\bulletproof($files);
		$image->setName($file_name);
		$image->setLocation(ROOT.'/assets/img/store');
		if($image['image']){
			if($image->upload()){
				// REVISAR QUE SEA UNICA
				try{
					$query = "SELECT 1 FROM producto WHERE imagen = :imagen";
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
				$this->product['image']['tmp_name'] = $files['image']['tmp_name'];
				$this->product['image']['file_name'] = $image->getName().'.'.$image->getMime();
				$this->product['image']['path'] = $image->getFullPath();
				return true;
			}
			$this->error['image'] = $image['error'];
			return false;
		}
		if($files['image']['error'] == 1){
			$this->error['image'] = 'Has excedido el límite de imagen de 2MB.';
		}else{
			$this->error['image'] = 'Este campo es obligatorio.';
		}
		return;
	}

	private function set_coupon($files = null){
		if(!$this->product['name']){
			return;
		}
		// RECORTAR NOMBRE DE IMAGEN
		$image_prefix = _safe('-cupon-esmart-club');
		$max = 150 - strlen($image_prefix);
		$safe_name = $this->friendly_url($this->product['name']);
		if(strlen($safe_name) > $max){
			$file = substr($safe_name, 0, $max);
		}else{
			$file = $safe_name;
		}
		$file_name = $file.$image_prefix;
		$image = new \assets\libraries\bulletproof\bulletproof($files);
		$image->setName($file_name);
		$image->setLocation(ROOT.'/assets/img/store/coupon');
		if($image['coupon']){
			if($image->upload()){
				// REVISAR QUE SEA UNICA
				try{
					$query = "SELECT 1 FROM producto WHERE cupon = :cupon";
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(':cupon', $image->getName().'.'.$image->getMime(), PDO::PARAM_STR);
					$stmt->execute();
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				if($row = $stmt->fetch()){
					$image->setName($image->getName().'-'.time()); // AGREGAR EL TIEMPO SI NO LO ES
				}
				$this->product['coupon']['tmp_name'] = $files['coupon']['tmp_name'];
				$this->product['coupon']['file_name'] = $image->getName().'.'.$image->getMime();
				$this->product['coupon']['path'] = $image->getFullPath();
				return true;
			}
			$this->error['coupon'] = $image['error'];
			return false;
		}
		if($files['coupon']['error'] == 1){
			$this->error['coupon'] = 'Has excedido el límite de imagen de 2MB.';
		}
		return;
	}

	private function set_name($string = null){
		if($string){
			$string = trim($string);
			$this->product['name'] = $string;
			return true;
		}
		$this->error['name'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_description($string = null){
		if($string){
			$string = trim($string);
			$this->product['description'] = $string;
			return true;
		}
		$this->error['description'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_category($string = null){
		if(array_key_exists($string, $this->categories)){
			$this->product['category'] = $string;
			return true;
		}
		$this->error['category'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_price($string = null){
		if($string){
			$price = filter_var($string, FILTER_VALIDATE_FLOAT);
			if(!$price){
				$this->error['price'] = 'El formato debe ser un número sin signos o comas. Ejemplo: 25.95';
				return false;
			}
			$this->product['price'] = $price;
			return true;
		}
		$this->error['price'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_quantity($string = null){
		if($string){
			$qty = filter_var($string, FILTER_VALIDATE_INT);
			if(!$qty){
				$this->error['quantity'] = 'El formato debe ser un número entero sin signos, puntos o comas. Ejemplo: 15.';
				return false;
			}
			$this->product['quantity'] = $qty;
			return true;
		}
		$this->error['quantity'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_send_price($string = null){
		if($string){
			$price = filter_var($string, FILTER_VALIDATE_FLOAT);
			if(!$price){
				$this->error['send_price'] = 'El formato debe ser un número sin signos o comas. Ejemplo: 25.95';
				return false;
			}
			$this->product['send_price'] = $price;
			return true;
		}
		return false;
	}

	private function set_conditions($string = null){
		if($string){
			$string = trim($string);
			$this->product['conditions'] = $string;
			return true;
		}
		return false;
	}

	public function get_name(){
		return _safe($this->product['name']);
	}

	public function get_name_error(){
		if($this->error['name']){
			$error = '<p class="text-danger">'.$this->error['name'].'</p>';
			return $error;
		}
	}

	public function get_description(){
		return _safe($this->product['description']);
	}

	public function get_description_error(){
		if($this->error['description']){
			$error = '<p class="text-danger">'.$this->error['description'].'</p>';
			return $error;
		}
	}

	public function get_image_error(){
		if($this->error['image']){
			$error = '<p class="text-danger">'.$this->error['image'].'</p>';
			return $error;
		}
	}

	public function get_coupon_error(){
		if($this->error['coupon']){
			$error = '<p class="text-danger">'.$this->error['coupon'].'</p>';
			return $error;
		}
	}

	public function get_categories(){
		$html = null;
		foreach ($this->categories as $key => $value) {
			$category = _safe($value);
			if($this->product['category'] == $key){
				$html .= '<option value="'.$key.'" selected>'.$category.'</option>';
			}else{
				$html .= '<option value="'.$key.'">'.$category.'</option>';
			}
		}
		return $html;
	}

	public function get_category_error(){
		if($this->error['category']){
			$error = '<p class="text-danger">'.$this->error['category'].'</p>';
			return $error;
		}
	}

	public function get_price(){
		return _safe($this->product['price']);
	}

	public function get_price_error(){
		if($this->error['price']){
			$error = '<p class="text-danger">'.$this->error['price'].'</p>';
			return $error;
		}
	}

	public function get_quantity(){
		return _safe($this->product['quantity']);
	}

	public function get_quantity_error(){
		if($this->error['quantity']){
			$error = '<p class="text-danger">'.$this->error['quantity'].'</p>';
			return $error;
		}
	}

	public function get_send_price(){
		return _safe($this->product['send_price']);
	}

	public function get_send_price_error(){
		if($this->error['send_price']){
			$error = '<p class="text-danger">'.$this->error['send_price'].'</p>';
			return $error;
		}
	}

	public function get_conditions(){
		return _safe($this->product['conditions']);
	}

	public function get_conditions_error(){
		if($this->error['conditions']){
			$error = '<p class="text-danger">'.$this->error['conditions'].'</p>';
			return $error;
		}
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
		file_put_contents(ROOT.'\assets\error_logs\new_product.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
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