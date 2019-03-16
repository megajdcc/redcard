<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace negocio\libs;
use assets\libs\connection;
use PDO;

class preference_info {
	private $con;
	private $business = array(
		'id' => null, 
		'url' => null,
		'name' => null,
		'description' => null,
		'brief' => null,
		'category_id' => null,
		'commission' => null,
		'email' => array(),
		'phone' => array(),
		'website' => null,
		'address' => null,
		'postal_code' => null,
		'city_id' => null,
		'state_id' => null,
		'country_id' => null,
		'latitude' => null,
		'longitude' => null
	);
	private $categories = array();
	private $error = array(
		'name' => null,
		'description' => null,
		'brief' => null,
		'category' => null,
		'commission' => null,
		'email' => null,
		'phone' => null,
		'website' => null,
		'address' => null,
		'postal_code' => null,
		'city' => null,
		'state' => null,
		'country' => null,
		'coordinates' => null,
		'notification' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->business['id'] = $_SESSION['business']['id_negocio'];
		$this->business['url'] = $_SESSION['business']['url'];
		$this->load_data();
		return;
	}

	private function load_data(){
		$query = "SELECT n.nombre, n.descripcion, n.breve, n.id_categoria, n.comision, n.sitio_web, n.direccion, n.codigo_postal, n.id_ciudad, e.id_estado, p.id_pais, n.latitud, n.longitud FROM negocio n 
			INNER JOIN ciudad c ON n.id_ciudad = c.id_ciudad 
			INNER JOIN estado e ON c.id_estado = e.id_estado 
			INNER JOIN pais p ON e.id_pais = p.id_pais 
			WHERE n.id_negocio = :id_negocio";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['name'] = $row['nombre'];
			$this->business['description'] = $row['descripcion'];
			$this->business['brief'] = $row['breve'];
			$this->business['category_id'] = $row['id_categoria'];
			$this->business['commission'] = $row['comision'];
			$this->business['website'] = $row['sitio_web'];
			$this->business['address'] = $row['direccion'];
			$this->business['postal_code'] = $row['codigo_postal'];
			$this->business['city_id'] = $row['id_ciudad'];
			$this->business['state_id'] = $row['id_estado'];
			$this->business['country_id'] = $row['id_pais'];
			$this->business['latitude'] = $row['latitud'];
			$this->business['longitude'] = $row['longitud'];
			// Cargar emails de negocio
			$query = "SELECT id_email, email FROM negocio_email WHERE id_negocio = :id_negocio";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->business['email'][$row['id_email']]= $row['email'];
			}
			// Cargar telefonos
			$query = "SELECT id_telefono, telefono FROM negocio_telefono WHERE id_negocio = :id_negocio";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->business['phone'][$row['id_telefono']] = $row['telefono'];
			}
			// Cargar las categorías
			$query = "SELECT id_categoria, categoria FROM negocio_categoria";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->categories[$row['id_categoria']] = $row['categoria'];
			}
		}
		return;
	}

	public function set_information(array $post){
		$this->set_name($post['name']);
		$this->set_description($post['description']);
		$this->set_brief($post['brief']);
		$this->set_category($post['category_id']);
		$this->set_commission($post['commission']);
		$this->set_website($post['website']);
		$emails = $this->set_email($post['email']);
		$phones = $this->set_phone($post['phone']);
		$this->set_address($post['address']);
		$this->set_postal_code($post['postal_code']);
		$this->set_city_id($post['city_id']);
		$this->set_state_id($post['state_id']);
		$this->set_country_id($post['country_id']);
		$this->set_latitude($post['latitude']);
		$this->set_longitude($post['longitude']);
		if(!array_filter($this->error)){
			$query = "UPDATE negocio SET 
				nombre = :nombre,
				descripcion = :descripcion,
				breve = :breve,
				id_categoria = :id_categoria,
				comision = :comision,
				sitio_web = :sitio_web,
				direccion = :direccion,
				codigo_postal = :codigo_postal,
				id_ciudad = :id_ciudad,
				latitud = :latitud,
				longitud = :longitud
				WHERE id_negocio = :id_negocio";
			$query_params = array(
				':nombre' => $this->business['name'],
				':descripcion' => $this->business['description'],
				':breve' => $this->business['brief'],
				':id_categoria' => $this->business['category_id'],
				':comision' => $this->business['commission'],
				':sitio_web' => $this->business['website'],
				':direccion' => $this->business['address'],
				':codigo_postal' => $this->business['postal_code'],
				':id_ciudad' => $this->business['city_id'],
				':latitud' => $this->business['latitude'],
				':longitud' => $this->business['longitude'],
				':id_negocio' => $this->business['id']
			);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($query_params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			// Actualizar emails
			$update_emails = $emails['update'];
			$insert_emails = $emails['insert'];
			foreach ($update_emails as $key => $value) {
				$query = "UPDATE negocio_email SET email = :email WHERE id_email = :id_email";
				$params = array(':email' => $value, ':id_email' => $key);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
			}
			foreach ($insert_emails as $key => $value) {
				$query = "INSERT INTO negocio_email (id_negocio, email) VALUES (:id_negocio, :email)";
				$params = array(':id_negocio' => $this->business['id'], ':email' => $value);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
			}
			// Actualizar telefonos
			$update_phones = $phones['update'];
			$insert_phones = $phones['insert'];
			foreach ($update_phones as $key => $value) {
				$query = "UPDATE negocio_telefono SET telefono = :telefono WHERE id_telefono = :id_telefono";
				$params = array(':telefono' => $value, ':id_telefono' => $key);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
			}
			foreach ($insert_phones as $key => $value) {
				$query = "INSERT INTO negocio_telefono (id_negocio, telefono) VALUES (:id_negocio, :telefono)";
				$params = array(':id_negocio' => $this->business['id'], ':telefono' => $value);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
			}
			$_SESSION['notification']['success'] = 'Información del negocio actualizada correctamente.';
			header('Location: '.HOST.'/negocio/preferencias/');
			die();
			return;
		}
		$this->error['notification'] = 'Uno o más campos tienen errores. Por favor revisalos cuidadosamente.';
		return false;
	}

	private function set_name($string = null){
		if($string){
			$string = trim($string);
			$this->business['name'] = $string;
			return;
		}
		$this->error['name'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_description($string = null){
		if($string){
			$string = trim($string);
			$this->business['description'] = $string;
			return;
		}
		$this->error['description'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_brief($string = null){
		if($string){
			$this->business['brief'] = trim($string);
			if(strlen($string) > 60){
				$this->error['brief'] = 'La descripción corta no debe exceder los 60 caracteres.';
				return false;
			}
			return true;
		}
		$this->error['brief'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_category($string = null){
		if(array_key_exists($string, $this->categories)){
			$this->business['category_id'] = $string;
			return;
		}
		$this->error['category'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_commission($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 6 || $string > 100){
				$this->error['commission'] = 'La comision debe ser un número entero entre 6 y 100.';
				return false;
			}
			$this->business['commission'] = $string;
			return;
		}
		$this->error['commission'] = 'Este campo es obligatorio';
		return false;
	}

	private function set_website($string = null){
		if($string){
			if(!preg_match('_^(?:(?:https?|ftp)://)?(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,}))\.?)(?::\d{2,5})?(?:[/?#]\S*)?$_iuS',$string)){
				$this->error['website'] = 'Escribe un enlace correcto. Ejemplo: www.esmartclub.com o http://esmartclub.com';
				$this->business['website'] = $string;
				return false;
			}
			if(!preg_match("@^https?://@", $string)){
				$this->business['website'] = 'http://'.$string;
			}else{
				$this->business['website'] = $string;
			}
		}else{
			$this->business['website'] = null;
		}
		return;
	}

	public function set_email($emails){
		$current_emails = $this->business['email'];
		$new_emails = array('update' => array(), 'insert' => array());
		foreach($emails as $key => $value){
			$key = key($current_emails);
			if($value){
				$email = trim($value);
				if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
					$this->error['email'] = 'Escribe una dirección de correo electrónico correcta. Ejemplo: usuario@ejemplo.com.';
				}
			}else{
				$email = null;
			}
			if(!is_null($email)){
				if($key != null){
					$new_emails['update'][$key] = $email;
					unset($current_emails[$key]);
				}else{
					$new_emails['insert'][] = $email;
				}
			}
		}
		foreach ($current_emails as $key => $value) {
			$new_emails['update'][$key] = null;
		}
		if(!array_filter($new_emails['update'])){
			$this->error['email'] = 'Debes tener al menos un correo electrónico.';
		}
		if(isset($new_emails['insert'])){
			$this->business['email'] = array_merge($new_emails['update'], $new_emails['insert']);
		}else{
			$this->business['email'] = $new_emails['update'];
		}
		return $new_emails;
	}

	public function set_phone($phones){
		$current_phones = $this->business['phone'];
		$new_phones = array('update' => array(), 'insert' => array());
		foreach($phones as $key => $value){
			$key = key($current_phones);
			if($value){
				$phone = trim($value);
				if(!preg_match('/^[0-9() +-]+$/ui',$phone)){
					$this->error['phone'] = 'Escribe un número telefónico correcto. Ejemplo: (123) 456-78-90';
				}
			}else{
				$phone = null;
			}
			if(!is_null($phone)){
				if($key != null){
					$new_phones['update'][$key] = $phone;
					unset($current_phones[$key]);
				}else{
					$new_phones['insert'][] = $phone;
				}
			}
		}
		foreach ($current_phones as $key => $value) {
			$new_phones['update'][$key] = null;
		}
		if(!array_filter($new_phones['update'])){
			$this->error['phone'] = 'Debes tener al menos un número telefónico.';
		}
		if(isset($new_phones['insert'])){
			$this->business['phone'] = array_merge($new_phones['update'], $new_phones['insert']);
		}else{
			$this->business['phone'] = $new_phones['update'];
		}
		return $new_phones;
	}

	private function set_address($string = null){
		if($string){
			$string = trim($string);
			$this->business['address'] = $string;
			return;
		}
		$this->error['address'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_postal_code($string = null){
		if($string){
			$string = trim($string);
			$this->business['postal_code'] = $string;
			return;
		}
		$this->error['postal_code'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_city_id($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				$this->error['city'] = 'Este campo es obligatorio.';
				$this->business['city_id'] = null;
				return false;
			}
			$this->business['city_id'] = $string;
			return;
		}
		$this->error['city'] = 'Este campo es obligatorio.';
		$this->business['city_id'] = null;
		return false;
	}

	private function set_state_id($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				$this->error['state'] = 'Este campo es obligatorio.';
				$this->business['state_id'] = null;
				return false;
			}
			$this->business['state_id'] = $string;
			return;
		}
		$this->error['state'] = 'Este campo es obligatorio.';
		$this->business['state_id'] = null;
		return false;
	}

	private function set_country_id($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				$this->error['country'] = 'Este campo es obligatorio.';
				$this->business['country_id'] = null;
				return false;
			}
			$this->business['country_id'] = $string;
			return;
		}
		$this->error['country'] = 'Este campo es obligatorio.';
		$this->business['country_id'] = null;
		return false;
	}

	private function set_latitude($string = null){
		if($string){
			$string = trim($string);
			if(is_numeric($string)){
				$this->business['latitude'] = $string;
				return;
			}
		}
		$this->error['coordinates'] = 'Error al validar las coordenadas del mapa.';
		return false;
	}

	private function set_longitude($string = null){
		if($string){
			$string = trim($string);
			if(is_numeric($string)){
				$this->business['longitude'] = $string;
				return;
			}
		}
		$this->error['coordinates'] = 'Error al validar las coordenadas del mapa.';
		return false;
	}

	public function get_profile_url(){
		return HOST.'/'._safe($this->business['url']);
	}

	public function get_name(){
		return _safe($this->business['name']);
	}

	public function get_name_error(){
		if($this->error['name']){
			$error = '<p class="text-danger">'._safe($this->error['name']).'</p>';
			return $error;
		}
	}

	public function get_description(){
		return _safe($this->business['description']);
	}

	public function get_description_error(){
		if($this->error['description']){
			$error = '<p class="text-danger">'._safe($this->error['description']).'</p>';
			return $error;
		}
	}

	public function get_brief(){
		return _safe($this->business['brief']);
	}

	public function get_brief_error(){
		if($this->error['brief']){
			$error = '<p class="text-danger">'._safe($this->error['brief']).'</p>';
			return $error;
		}
	}

	public function get_category(){
		$html = null;
		foreach ($this->categories as $key => $value) {
			if($this->business['category_id'] == $key){
				$html .= '<option value="'.$key.'" selected>'._safe($value).'</option>';
			}else{
				$html .= '<option value="'.$key.'">'._safe($value).'</option>';
			}
		}
		return $html;
	}
	public function get_category_for_report(){
		$html = null;
		foreach ($this->categories as $key => $value) {
			if($this->business['category_id'] == $key){
				$html .= '<option value="'.$key.'">'._safe($value).'</option>';
			}else{
				$html .= '<option value="'.$key.'">'._safe($value).'</option>';
			}
		}
		return $html;
	}

	public function get_category_error(){
		if($this->error['category']){
			$error = '<p class="text-danger">'._safe($this->error['category']).'</p>';
			return $error;
		}
	}

	public function get_commission(){
		return _safe($this->business['commission']);
	}

	public function get_commission_error(){
		if($this->error['commission']){
			$error = '<p class="text-danger">'._safe($this->error['commission']).'</p>';
			return $error;
		}
	}

	public function get_url(){
		return _safe($this->business['url']);
	}

	public function get_email(){
		$i = 0;
		foreach($this->business['email'] as $key => $value){
			if($i == 0){
				$html = 
					'<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-at"></i></span>
						<input class="form-control" type="email" id="email" name="email[]" value="'._safe($value).'" placeholder="Correo electr&oacute;nico" required />
					</div><!-- /.input-group -->
					<div id="email-wrap">';
				$i++;
			}elseif(!is_null($value)){
				$html .= 
					'<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-at"></i></span>
						<input class="form-control" type="text" name="email[]" value="'._safe($value).'" placeholder="Correo electr&oacute;nico" />
						<a href="#" class="input-group-addon remove-field"><i class="fa fa-times text-danger"></i></a>
					</div><!-- /.input-group -->';
			}
		}
		$html .= '</div>';
		return $html;
	}

	public function get_email_error(){
		if($this->error['email']){
			$error = '<p class="text-danger">'._safe($this->error['email']).'</p>';
			return $error;
		}
	}

	public function get_phone(){
		$i = 0;
		foreach($this->business['phone'] as $key => $value){
			if($i == 0){
				$html = 
					'<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-phone"></i></span>
						<input class="form-control" type="text" id="phone" name="phone[]" value="'._safe($value).'" placeholder="N&uacute;mero telef&oacute;nico" required />
					</div><!-- /.input-group -->
					<div id="phone-wrap">';
				$i++;
			}elseif(!is_null($value)){
				$html .= 
					'<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-phone"></i></span>
						<input class="form-control" type="text" name="phone[]" value="'._safe($value).'" placeholder="N&uacute;mero telef&oacute;nico" />
						<a href="#" class="input-group-addon remove-field"><i class="fa fa-times text-danger"></i></a>
					</div><!-- /.input-group -->';
			}
		}
		$html .= '</div>';
		return $html;
	}

	public function get_phone_error(){
		if($this->error['phone']){
			$error = '<p class="text-danger">'._safe($this->error['phone']).'</p>';
			return $error;
		}
	}

	public function get_website(){
		return _safe($this->business['website']);
	}

	public function get_website_error(){
		if($this->error['website']){
			$error = '<p class="text-danger">'._safe($this->error['website']).'</p>';
			return $error;
		}
	}

	public function get_address(){
		return _safe($this->business['address']);
	}

	public function get_address_error(){
		if($this->error['address']){
			$error = '<p class="text-danger">'._safe($this->error['address']).'</p>';
			return $error;
		}
	}

	public function get_postal_code(){
		return _safe($this->business['postal_code']);
	}

	public function get_postal_code_error(){
		if($this->error['postal_code']){
			$error = '<p class="text-danger">'._safe($this->error['postal_code']).'</p>';
			return $error;
		}
	}

	public function get_country(){
		$html = null;
		$query = "SELECT id_pais, pais FROM pais";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$country = _safe($row['pais']);
			if($this->business['country_id'] == $row['id_pais']){
				$html .= '<option value="'.$row['id_pais'].'" selected>'.$country.'</option>';
			}else{
				$html .= '<option value="'.$row['id_pais'].'">'.$country.'</option>';
			}
		}
		return $html;
	}

	public function get_country_error(){
		if($this->error['country']){
			$error = '<p class="text-danger">'.$this->error['country'].'</p>';
			return $error;
		}
	}

	public function get_state(){
		$html = null;
		if($this->business['country_id']){
			$query = "SELECT id_estado, estado FROM estado WHERE id_pais = :id_pais";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_pais', $this->business['country_id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$state = _safe($row['estado']);
				if($this->business['state_id'] == $row['id_estado']){
					$html .= '<option value="'.$row['id_estado'].'" selected>'.$state.'</option>';
				}else{
					$html .= '<option value="'.$row['id_estado'].'">'.$state.'</option>';
				}
			}
		}
		return $html;
	}

	public function get_state_error(){
		if($this->error['state']){
			$error = '<p class="text-danger">'.$this->error['state'].'</p>';
			return $error;
		}
	}

	public function get_city(){
		$html = null;
		if($this->business['state_id']){
			$query = "SELECT id_ciudad, ciudad FROM ciudad WHERE id_estado = :id_estado";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_estado', $this->business['state_id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$city = _safe($row['ciudad']);
				if($this->business['city_id'] == $row['id_ciudad']){
					$html .= '<option value="'.$row['id_ciudad'].'" selected>'.$city.'</option>';
				}else{
					$html .= '<option value="'.$row['id_ciudad'].'">'.$city.'</option>';
				}
			}
		}
		return $html;
	}

	public function get_city_error(){
		if($this->error['city']){
			$error = '<p class="text-danger">'.$this->error['city'].'</p>';
			return $error;
		}
	}

	public function get_latitude(){
		return _safe($this->business['latitude']);
	}

	public function get_longitude(){
		return _safe($this->business['longitude']);
	}

	public function get_coordinates_error(){
		if($this->error['coordinates']){
			$error = '<p class="text-danger">'._safe($this->error['coordinates']).'</p>';
			return $error;
		}
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
		file_put_contents(ROOT.'\assets\error_logs\preference_info.txt', '['.date('d/M/Y h:i:s A').' | '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['notification'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>