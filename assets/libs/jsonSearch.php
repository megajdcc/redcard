<?php 
namespace assets\libs;
use PDO;

class jsonSearch {
	private $con;

	public function __construct(connection $con){
		$this->con = $con->con;
	}





	public function getRestaurantes($busquedad  = null){


			$sql = "SELECT n.id_negocio, n.nombre, np.preferencia as imagen,n.breve from negocio as n 
				left JOIN preferencia p ON p.llave = 'business_header'
				left JOIN negocio_preferencia np ON n.id_negocio = np.id_negocio AND np.id_preferencia = p.id_preferencia
				 right JOIN negocio_categoria as nc on n.id_categoria = 1
				where n.situacion = 1  && n.nombre LIKE :nombre || n.breve LIKE :breve group by n.nombre";



			try {
				
				$stm = $this->con->prepare($sql);
				$stm->execute(array(':nombre'=>'%'.$busquedad.'%', ':breve'=>'%'.$busquedad.'%'));
			} catch (PDOException $e) {
				return false;
			}

			$datos = array();
			while($row = $stm->fetch()){

				if(!$row['nombre']){
					$row['display'] = '';
				}else{
					$row['display'] = htmlentities($row['nombre']);
				}

				if(!$row['imagen']){
					$row['imagen'] = 'restaurant.png';
				}

				$row['id_restaurant'] = $row['id_negocio'];

				$datos[] = $row;

			}

			return json_encode($datos);

	}


	public function getHotel($busqueda =null){

		$sql = "SELECT h.imagen as imghotel, h.nombre as nombrehotel,u.username,u.nombre,u.apellido,u.imagen from hotel as h join solicitudhotel as sh on h.id = sh.id_hotel 
					RIGHT JOIN usuario as u on sh.id_usuario = u.id_usuario where u.activo = 1 AND(CONCAT(u.nombre,' ',u.apellido) LIKE ? OR username LIKE ? OR h.nombre LIKE ? OR u.email LIKE ?)";

					try {
						
						$stmt = $this->con->prepare($sql);
						$stmt->bindValue(1, '%'.$busqueda.'%', PDO::PARAM_STR);
						$stmt->bindValue(2, '%'.$busqueda.'%', PDO::PARAM_STR);
						$stmt->bindValue(3, '%'.$busqueda.'%', PDO::PARAM_STR);
						$stmt->bindValue(4, $busqueda, PDO::PARAM_STR);
						$stmt->execute();
					} catch (PDOException $e) {
						$this->error_log(__METHOD__,__LINE__,$e->getMessage());
						return false;
					}

					$users = array();
					while($row = $stmt->fetch()){
					if(!$row['nombre'] || !$row['apellido']){
						$row['display'] = '';
					}else{
						$row['display'] = htmlentities($row['nombre'].' '.$row['apellido']);
					}
					if(!$row['imagen']){
						$row['imagen'] = 'default.jpg';
					}

					if(!$row['imghotel']){
						$row['imghotel'] = 'default.jpg';
					}

					if(!$row['nombrehotel']){
						$row['displayhotel'] = '';
					}else{
						$row['displayhotel'] = htmlentities($row['nombrehotel']);
					}

					unset($row['nombre']);
					unset($row['apellido']);
			

					$row['username'] = htmlentities($row['username']);
					$row['nombrehotel'] = htmlentities($row['nombrehotel']);
						$users[] = $row;
					}
					return json_encode($users);

	}

	public function getUsers($search = null){
		$query = "SELECT username, imagen, nombre, apellido 
			FROM usuario WHERE activo = 1 
			AND (CONCAT(nombre,' ',apellido) LIKE ? OR username LIKE ? OR email LIKE ?)";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(1, '%'.$search.'%', PDO::PARAM_STR);
			$stmt->bindValue(2, '%'.$search.'%', PDO::PARAM_STR);
			$stmt->bindValue(3, $search, PDO::PARAM_STR);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$users = array();
		while($row = $stmt->fetch()){
			if(!$row['nombre'] || !$row['apellido']){
				$row['display'] = '';
			}else{
				$row['display'] = htmlentities($row['nombre'].' '.$row['apellido']);
			}
			if(!$row['imagen']){
				$row['imagen'] = 'default.jpg';
			}
			unset($row['nombre']);
			unset($row['apellido']);
			$row['username'] = htmlentities($row['username']);
			$users[] = $row;
		}
		return json_encode($users);
	}

	public function get_businesses($search = null){
		$query = "SELECT n.url, n.nombre, n.saldo, np.preferencia as imagen FROM negocio n
			LEFT JOIN negocio_preferencia np ON n.id_negocio = np.id_negocio 
			INNER JOIN preferencia p ON p.id_preferencia = np.id_preferencia AND p.llave = 'business_header'
			WHERE nombre LIKE ? OR url LIKE ?";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(1, $search, PDO::PARAM_STR);
			$stmt->bindValue(2, $search, PDO::PARAM_STR);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$business = array();
		while($row = $stmt->fetch()){
			$row['url'] = _safe($row['url']);
			$row['nombre'] = _safe($row['nombre']);
			$row['imagen'] = _safe($row['imagen']);
			$row['balance'] = number_format((float)$row['saldo'], 2, '.', '');
			$business[] = $row;
		}
		return json_encode($business);
	}

	public function get_username_cert($username = null){
		$html = null;
		if($username){
			$now = date('Y/m/d H:i:s', time());
			$query = "SELECT uc.id_uso, ne.imagen, ne.nombre as certificado, ne.descripcion, ne.condiciones, ne.restricciones, ne.precio, ne.iso, u.nombre, u.apellido, u.username 
				FROM usar_certificado uc 
				INNER JOIN negocio_certificado ne ON uc.id_certificado = ne.id_certificado AND ne.id_negocio = :id_negocio AND ne.fecha_inicio < :now1 AND ne.fecha_fin > :now2 
				INNER JOIN usuario u ON uc.id_usuario = u.id_usuario
				WHERE u.username = :username AND uc.situacion = 2";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_negocio', $_SESSION['business']['id_negocio'], PDO::PARAM_STR);
				$stmt->bindValue(':username', $username, PDO::PARAM_STR);
				$stmt->bindValue(':now1', $now, PDO::PARAM_STR);
				$stmt->bindValue(':now2', $now, PDO::PARAM_STR);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$id = $row['id_uso'];
				$html .= 
				'<div class="row">
					<div class="col-sm-3">
						<img src="'.HOST.'/assets/img/business/certificate/'.$row['imagen'].'" class="img-responsive" alt="">
					</div>
					<div class="col-sm-9">
						<div class="row">
							<div class="col-sm-8">
								<div class="radio sideways">
									<input id="use-'.$id.'" type="radio" name="use_cert['.$id.']" value="1" checked required><label for="use-'.$id.'">Utilizar certificado</label>
									<input id="trash-'.$id.'" type="radio" name="use_cert['.$id.']" value="0" required><label for="trash-'.$id.'">Desechar certificado</label>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="radio">
									<label>Valuado en: $'.$this->min_precision($row['precio']).' '.$row['iso'].'</label>
								</div>
							</div>
						</div>
						<hr>
						<div class="form-group">
							<label><strong>'._safe($row['certificado']).'</strong></label>
							<p>'._safe($row['descripcion']).'</p>
						</div>
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label>Condiciones</label>
									<p>'._safe($row['condiciones']).'</p>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label>Restricciones</label>
									<p>'._safe($row['restricciones']).'</p>
								</div>
							</div>
						</div>
					</div>
				</div>';
			}
		}
		if(!$html){
			$html = '<p class="text-default">No hay certificados reservados.</p>';
		}
		return $html;
	}

	public function get_username_load($username = null){
		$query = "SELECT username, imagen, nombre, apellido FROM usuario WHERE activo = 1 AND username = :username";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':username', $username, PDO::PARAM_STR);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			if($row['nombre'] || $row['apellido']){
				$display = _safe(trim($row['nombre'].' '.$row['apellido']));
			}else{
				$display = '';
			}
			$username = _safe($row['username']);
			if($row['imagen']){
				$image = _safe($row['imagen']);
			}else{
				$image = 'default.jpg';
			}
			$html = 
			'<div><img src="/assets/img/user_profile/'.$image.'" class="meta-img img-rounded" alt=""><strong>'.$display.'</strong> @'.$username.'</div>';
		}else{
			$html = '
			<div><img src="/assets/img/user_profile/default.jpg" class="meta-img img-rounded" alt="">';
		}
		return $html;
	}

	public function calculate_esmarties($iso = null, $total = null, $commission = null){
		$esmarties = null;
		if($iso && $total && $commission){
			if($iso == 'MXN'){
				$rate = 1;
			}else{
				$converter = new \assets\libraries\CurrencyConverter\CurrencyConverter;
				$cacheAdapter = new \assets\libraries\CurrencyConverter\Cache\Adapter\FileSystem(dirname(dirname(__DIR__)) . '/assets/cache/');
				$cacheAdapter->setCacheTimeout(\DateInterval::createFromDateString('10 second'));
				$converter->setCacheAdapter($cacheAdapter);
				$rate = $converter->convert($iso, 'MXN');
			}
			$esmarties = $total * $rate * ($commission/100);
			$esmarties = floor($esmarties * 10000)/10000;
		}
		return $esmarties;
	}

	public function getStates($country = null){
		$query = "SELECT id_estado, estado FROM estado WHERE id_pais = :id_pais";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_pais', $country, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return;
		}
		while($row = $stmt->fetch()){
			$states[] = $row;
		}
		return json_encode($states);
	}

	public function getCities($state = null){
		$cities = null;
		$query = "SELECT id_ciudad, ciudad FROM ciudad WHERE id_estado = :id_estado";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_estado', $state, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return;
		}
		while($row = $stmt->fetch()){
			$cities[] = $row;
		}
		return json_encode($cities);
	}

	public function add_bookmark($business_id){
		if(array_key_exists($business_id, $_SESSION['user']['follow_business'])){
			return false;
		}
		$query = "INSERT INTO seguir_negocio (id_usuario, id_negocio) VALUES (:id_usuario, :id_negocio)";
		$params = array(':id_usuario' => $_SESSION['user']['id_usuario'],':id_negocio' => $business_id);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$_SESSION['user']['follow_business'][$business_id] = true;
		return true;
	}

	public function del_bookmark($business_id){
		if(!array_key_exists($business_id, $_SESSION['user']['follow_business'])){
			return false;
		}
		$query = "DELETE FROM seguir_negocio WHERE id_usuario = :id_usuario AND id_negocio = :id_negocio";
		$params = array(':id_usuario' => $_SESSION['user']['id_usuario'],':id_negocio' => $business_id);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		unset($_SESSION['user']['follow_business'][$business_id]);
		return true;
	}

	public function add_recommend($business_id){
		if(array_key_exists($business_id, $_SESSION['user']['recommend_business'])){
			return false;
		}
		$query = "INSERT INTO recomendar_negocio (id_usuario, id_negocio) VALUES (:id_usuario, :id_negocio)";
		$params = array(':id_usuario' => $_SESSION['user']['id_usuario'],':id_negocio' => $business_id);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$_SESSION['user']['recommend_business'][$business_id] = true;
		return true;
	}

	public function del_recommend($business_id){
		if(!array_key_exists($business_id, $_SESSION['user']['follow_business'])){
			return false;
		}
		$query = "DELETE FROM recomendar_negocio WHERE id_usuario = :id_usuario AND id_negocio = :id_negocio";
		$params = array(':id_usuario' => $_SESSION['user']['id_usuario'],':id_negocio' => $business_id);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		unset($_SESSION['user']['recommend_business'][$business_id]);
		return true;
	}

	public function add_wishlist($certificate_id){
		if(array_key_exists($certificate_id, $_SESSION['user']['certificate_wishlist'])){
			return false;
		}
		$query = "INSERT INTO lista_deseos_certificado (id_usuario, id_certificado) VALUES (:id_usuario, :id_certificado)";
		$query_params = array(':id_usuario' => $_SESSION['user']['id_usuario'],':id_certificado' => $certificate_id);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($query_params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$_SESSION['user']['certificate_wishlist'][$certificate_id] = true;
		return true;
	}

	public function del_wishlist($certificate_id){
		if(!array_key_exists($certificate_id, $_SESSION['user']['certificate_wishlist'])){
			return false;
		}
		$query = "DELETE FROM lista_deseos_certificado WHERE id_usuario = :id_usuario AND id_certificado = :id_certificado";
		$query_params = array(':id_usuario' => $_SESSION['user']['id_usuario'],':id_certificado' => $certificate_id);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($query_params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		unset($_SESSION['user']['certificate_wishlist'][$certificate_id]);
		return true;
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\json_search.txt', '['.date('d/M/Y h:i:s A').' on '.$method.' on line '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		return $this;
	}

	private function min_precision($x){
		$p = 2;
		$e = pow(10,$p);
		return floor($x*$e)==$x*$e?sprintf("%.${p}f",$x):$x;
	}

	public function set_friendly_url($url){
		$url = $this->friendly_url($url);
		return $url;
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