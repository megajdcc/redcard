<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace negocio\libs;
use assets\libs\connection;
use PDO;

class manage_certificates {
	private $con;
	private $status;
	private $businessId;
	private $employeeId;
	private $url;
	private $safe_name;
	private $cert_url;
	private $user = array ('id' => null, 'username' => null);
	private $code;
	private $name;
	private $date = array('start' => null, 'end' => null);
	private $quantity;
	private $cost;
	private $currency;
	private $description;
	private $condition;
	private $restriction;
	private $image = array('tmp' => null, 'name' => null, 'path' => null);
	private $token;
	private $action;
	private $pagination = array('total' => null, 'rpp' => null, 'max' => null, 'page' => null, 'offset' => null, 'function' => null);
	private $error = array(
		'user' => null,
		'code' => null,
		'name' => null,
		'date-start' => null,
		'date-end' => null,
		'quantity' => null,
		'cost' => null,
		'currency' => null,
		'description' => null,
		'token' => null,
		'image' => null,
		'action' => null,
		'error' => null,
		'warning' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->businessId = $_SESSION['business']['id_negocio'];
		$this->employeeId = $_SESSION['user']['id_usuario'];
		$this->url = $_SESSION['business']['url'];
		$this->loadData();
		return;
	}

	private function loadData(){
		$query = "SELECT situacion FROM negocio WHERE id_negocio = :id_negocio";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->businessId, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->status = $row['situacion'];
		}
		if($this->status != 1 && basename($_SERVER['SCRIPT_NAME']) == 'reservar.php'){
			$this->error['error'] = 'Tu negocio no se encuentra activo. Por el momento no puedes reservar ningún certificado. Contacta a eSmart Club.';
		}
		return true;
	}

	public function useCertificate(array $post){
		// $this->set_action($post['action']);
		$this->setUser($post['username']);
		$this->setCode($post['certificate']);
		// $this->setToken($post['token']);
		if($this->user['id'] && $this->code && !array_filter($this->error)){
			$this->registerUsedCertificate();
			return true;
		}
		if($this->status != 1 && basename($_SERVER['SCRIPT_NAME']) == 'reservar.php'){
			return false;
		}
		$this->error['warning'] = 'Uno o más campos tienen errores. Por favor revísalos cuidadosamente.';
		return false;
	}

	private function registerUsedCertificate(){
		$query = "INSERT INTO usar_certificado (
			id_usuario,
			id_certificado,
			situacion
			) VALUES (
			:id_usuario,
			:id_certificado,
			2
		)";
		$query_params = array(
			':id_usuario' => $this->user['id'],
			':id_certificado' => $this->code
		);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($query_params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$query = "SELECT disponibles, (SELECT COUNT(*) FROM usar_certificado uc WHERE uc.id_certificado = ne.id_certificado AND uc.situacion != 0) as usados FROM negocio_certificado ne WHERE  id_certificado = :id_certificado";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_certificado', $this->code, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			if($row['usados'] >= $row['disponibles']){
				$query = "UPDATE negocio_certificado SET situacion = 2 WHERE id_certificado = :id_certificado";
				try{
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(':id_certificado', $this->code, PDO::PARAM_INT);
					$stmt->execute();
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
			}
		}
		$_SESSION['notification']['success'] = 'Certificado reservado exitosamente.';
		header('Location: '.HOST.'/negocio/certificados/reservar');
		die();
		return;
	}

	public function setData(array $post, array $files){
		$this->setName($post['name']);
		$this->setDateStart($post['date-start']);
		$this->setDateEnd($post['date-end']);
		$this->setQuantity($post['quantity']);
		$this->setCost($post['cost']);
		$this->setCurrency($post['currency']);
		$this->setDescription($post['description']);
		$this->setCondition($post['condition']);
		$this->setRestriction($post['restriction']);
		// $this->setToken($post['token']);
		$this->set_image($files);
		if(!array_filter($this->error)){
			$this->create_url();
			$this->uploadCertificate();
			return true;
		}
		return false;
	}

	private function uploadCertificate(){
		if(!move_uploaded_file($this->image['tmp'], $this->image['path'])){
			return false;
		}
		$query = "INSERT INTO negocio_certificado (
			id_negocio, 
			url, 
			imagen,
			nombre, 
			descripcion, 
			precio, 
			iso, 
			fecha_inicio, 
			fecha_fin, 
			condiciones, 
			restricciones, 
			disponibles
			) VALUES (
			:id_negocio, 
			:url, 
			:imagen,
			:nombre, 
			:descripcion, 
			:precio, 
			:iso, 
			:fecha_inicio, 
			:fecha_fin, 
			:condiciones, 
			:restricciones, 
			:disponibles
		)";
		$query_params = array(
			':id_negocio' => $this->businessId,
			':url' => $this->cert_url,
			':imagen' => $this->image['name'],
			':nombre' => $this->name,
			':descripcion' => $this->description,
			':precio' => $this->cost,
			':iso' => $this->currency,
			':fecha_inicio' => $this->date['start'],
			':fecha_fin' => $this->date['end'],
			':condiciones' => $this->condition,
			':restricciones' => $this->restriction,
			':disponibles' => $this->quantity
		);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($query_params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$_SESSION['notification']['success'] = 'Certificado subido con éxito.';
		header('Location: '.HOST.'/negocio/certificados/crear');
		die();
		return;
	}

	private function create_url(){
		$max = 255 - strlen('-'.$this->url);
		if(strlen($this->safe_name) > $max){
			$cert_url = substr($this->safe_name, 0, $max);
		}else{
			$cert_url = $this->safe_name;
		}
		$this->cert_url = $cert_url.'-'.$this->url;
		return;
	}

	private function set_image($files = null){
		// RECORTAR NOMBRE DE IMAGEN
		$image_prefix = _safe('-'.$this->url.'-certificado-esmart-club');
		$max = 150 - strlen($image_prefix);
		$safe_name = $this->safe_name;
		if(strlen($safe_name) > $max){
			$file = substr($safe_name, 0, $max);
		}else{
			$file = $safe_name;
		}
		$file_name = $file.$image_prefix;

		$image = new \assets\libraries\bulletproof\bulletproof($files);
		$image->setName($file_name);
		$image->setLocation(ROOT.'/assets/img/business/certificate');
		if($image['image']){
			if($image->upload()){
				// REVISAR QUE SEA UNICA
				try{
					$query = "SELECT 1 FROM negocio_certificado WHERE imagen = :imagen";
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
				$this->image['tmp'] = $files['image']['tmp_name'];
				$this->image['name'] = $image->getName().'.'.$image->getMime();
				$this->image['path'] = $image->getFullPath();
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
		return false;
	}

	private function set_action($action = null){
		if($action == 1){
			$this->action = 1;
			return true;
		}
		if($action == 2){
			$this->action = 2;
			return true;
		}
		$this->error['action'] = 'Selecciona una opción';
		return false;
	}

	private function setUser($username = null){
		if($username){
			if(!preg_match('/^[a-zA-Z0-9]+$/ui',$username)){
				$this->error['username'] = 'El nombre de usuario del socio solo debe contener letras y números.';
				$this->user['username'] = $username;
				return $this;
			}
			$query = "SELECT id_usuario FROM usuario WHERE username = :username";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':username', $username, PDO::PARAM_STR);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				$this->user['id'] = $row['id_usuario'];
				$this->user['username'] = $username;
				return $this;
			}
			$this->error['user'] = 'Este socio no existe.';
			$this->user['username'] = $username;
			return $this;
		}
		$this->error['user'] = 'Este campo es obligatorio.';
		return $this;
	}

	private function setCode($code = null){
		if($code){
			if(!preg_match('/^[a-zA-Z0-9-]+$/ui',$code)){
				$this->error['code'] = 'Solo se permiten números.';
				$this->code = $code;
				return $this;
			}
			$query = "SELECT id_certificado FROM negocio_certificado WHERE id_certificado = :id_certificado";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_certificado', $code, PDO::PARAM_STR);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				$this->code = $row['id_certificado'];
				return $this;
			}
			$this->error['code'] = 'El certificado no existe.';
			$this->code = $code;
			return $this;
		}
		$this->error['code'] = 'Este campo es obligatorio.';
		return $this;
	}

	private function setName($name = null){
		if($name){
			$this->name = $name;
			$this->safe_name = $this->friendly_url($name);
			return $this;
		}
		$this->error['name'] = 'Este campo es obligatorio..';
		return $this;
	}

	private function setDateStart($datetime = null){
		if($datetime){
			$datetime = str_replace('/', '-', $datetime);
			$datetime = strtotime($datetime);
			if(!$datetime){
				$this->error['date-start'] = 'Formato de fecha y hora incorrecto. Utiliza la herramienta.';
				return false;
			}
			$datetime = date("Y/m/d H:i:s", $datetime);
			$this->date['start'] = $datetime;
			return true;
		}
		$this->error['date-start'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setDateEnd($datetime = null){
		if($datetime){
			$datetime = str_replace('/', '-', $datetime);
			$datetime = strtotime($datetime);
			if(!$datetime){
				$this->error['date-end'] = 'Formato de fecha y hora incorrecto. Utiliza la herramienta.';
				return false;
			}
			$datetime = date("Y/m/d H:i:s", $datetime);
			$this->date['end'] = $datetime;
			return true;
		}
		$this->error['date-end'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setQuantity($quantity = null){
		if($quantity){
			$quantity = filter_var($quantity, FILTER_VALIDATE_INT);
			if(!$quantity){
				$this->error['quantity'] = 'Este campo es obligatorio.';
				return $this;
			}
			$this->quantity = $quantity;
			return $this;
		}
		$this->error['quantity'] = 'Este campo es obligatorio.';
		return $this;
	}

	private function setCost($cost = null){
		if($cost){
			$cost = str_replace(',','.',$cost);
			$cost = filter_var($cost, FILTER_VALIDATE_FLOAT);
			if(!$cost){
				$this->error['cost'] = 'Este campo es obligatorio.';
				return $this;
			}
			$this->cost = $cost;
			return $this;
		}
		$this->error['cost'] = 'Este campo es obligatorio.';
		return $this;
	}

	private function setCurrency($currency = null){
		if($currency){
			if(strlen($currency)!=3){
				$this->error['currency'] = 'Este campo es obligatorio..';
				return $this;
			}
			$this->currency = $currency;
			return $this;
		}
		$this->error['currency'] = 'Este campo es obligatorio..';
		return $this;
	}

	private function setDescription($description = null){
		if($description){
			$this->description = $description;
			return $this;
		}
		$this->error['description'] = 'Este campo es obligatorio.';
		return $this;
	}

	private function setCondition($condition = null){
		$this->condition = $condition;
		return $this;
	}

	private function setRestriction($restriction = null){
		$this->restriction = $restriction;
		return $this;
	}

	private function setToken($token = null){
		if($token){
			$query = "SELECT codigo_seguridad FROM negocio_empleado WHERE id_empleado = :id_empleado";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_empleado', $this->employeeId, PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				if(password_verify($token,$row['codigo_seguridad'])){
					$this->token = true;
					return $this;
				}
				$this->error['token'] = 'Authorization Code does not match.';
				return $this;
			}
		}
		$this->error['token'] = 'You must enter your authorization code.';
		return $this;
	}

	public function setPaginationFunction($string = null){
		if($string){
			$this->pagination['function'] = $string;
		}
	}

	public function setPagination($page = null, $rpp = null){
		if($page && $rpp){
			$page = filter_var($page, FILTER_VALIDATE_INT);
			if(!$page){
				$this->error['pagination'] = 'Current page must be INT.';
				return $this;
			}
			$rpp = filter_var($rpp, FILTER_VALIDATE_INT);
			if(!$rpp){
				$this->error['pagination'] = 'Rows per page must be INT.';
				return $this;
			}
			switch ($this->pagination['function']){
				case 'active':
					$query = "SELECT COUNT(*) FROM negocio_certificado WHERE fecha_inicio < now()";
					break;
				case 'coming':
					$query = "SELECT COUNT(*) FROM negocio_certificado WHERE fecha_inicio > now()";
					break;
				case 'history';
					$query = "SELECT COUNT(*) FROM negocio_certificado WHERE fecha_fin < now()";
					break;
				default:
					$query = "SELECT COUNT(*) FROM negocio_certificado";
					break;
			}
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return;
			}
			if($row = $stmt->fetch()){
				$this->pagination['total'] = $row['COUNT(*)'];
				$this->pagination['rpp'] = $rpp;
				$this->pagination['max'] = (int)ceil($this->pagination['total'] / $this->pagination['rpp']);
				$this->pagination['page'] = min($this->pagination['max'], $page);
				$this->pagination['offset'] = ($this->pagination['page'] - 1) * $this->pagination['rpp'];
				return $this;
			}
		}
		$this->error['pagination'] = 'Pagination not properly initialized.';
		return $this;
	}

	public function get_actions(){
		if($this->action == 2){
			$html =
			'<input id="use" type="radio" name="action" value="1" required><label for="use">Utilizar el certificado de regalo</label>
			<input id="hold" type="radio" name="action" value="2" checked required><label for="hold">Apartar el certificado de regalo y utilizarlo despu&eacute;s</label>';
		}else{
			$html =
			'<input id="use" type="radio" name="action" value="1" checked required><label for="use">Utilizar el certificado de regalo</label>
			<input id="hold" type="radio" name="action" value="2" required><label for="hold">Apartar el certificado de regalo y utilizarlo despu&eacute;s</label>';
		}
		return $html;
	}

	public function get_action_error(){
		if($this->error['action']){
			$error = '<p class="text-danger">'._safe($this->error['action']).'</p>';
			return $error;
		}
	}

	public function getUser(){
		return _safe($this->user['username']);
	}

	public function getUserError(){
		if($this->error['user']){
			$error = '<p class="text-danger">'._safe($this->error['user']).'</p>';
			return $error;
		}
	}

	public function getCode(){
		return _safe($this->code);
	}

	public function get_certificates(){
		$html = null;
		$query = "SELECT id_certificado, imagen, nombre, precio, iso FROM negocio_certificado WHERE id_negocio = :id_negocio AND fecha_inicio < now() AND fecha_fin > now() AND situacion = 1";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->businessId, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$price = $this->min_precision($row['precio']).' '.$row['iso'];
			$name = _safe($row['nombre']);
			if($this->code == $row['id_certificado']){
				$html .= '<option title="'.$name.' - '.$price.'" data-content="<img src=\''.HOST.'/assets/img/business/certificate/'.$row['imagen'].'\' class=\'meta-img img-rounded\' alt=\'\'><strong>'.$name.'</strong> '.$price.'" value="'.$row['id_certificado'].'" selected>'.$name.'</option>';
			}else{
				$html .= '<option title="'.$name.' - '.$price.'" data-content="<img src=\''.HOST.'/assets/img/business/certificate/'.$row['imagen'].'\' class=\'meta-img img-rounded\' alt=\'\'><strong>'.$name.'</strong> '.$price.'" value="'.$row['id_certificado'].'">'.$name.'</option>';
			}
		}
		return $html;
	}

	public function getCodeError(){
		if($this->error['code']){
			$error = '<p class="text-danger">'._safe($this->error['code']).'</p>';
			return $error;
		}
	}

	public function getName(){
		return _safe($this->name);
	}

	public function getNameError(){
		if($this->error['name']){
			$error = '<p class="text-danger">'._safe($this->error['name']).'</p>';
			return $error;
		}
	}

	public function getDateStart(){
		if($this->date['start']){
			$this->date['start'] = date('m/d/Y h:i A', strtotime($this->date['start']));
		}
		return _safe($this->date['start']);
	}

	public function getDateStartError(){
		if($this->error['date-start']){
			$error = '<p class="text-danger">'._safe($this->error['date-start']).'</p>';
			return $error;
		}
	}

	public function getDateEnd(){
		if($this->date['end']){
			$this->date['end'] = date('m/d/Y h:i A', strtotime($this->date['end']));
		}
		return _safe($this->date['end']);
	}

	public function getDateEndError(){
		if($this->error['date-end']){
			$error = '<p class="text-danger">'._safe($this->error['date-end']).'</p>';
			return $error;
		}
	}

	public function getQuantity(){
		return _safe($this->quantity);
	}

	public function getQuantityError(){
		if($this->error['quantity']){
			$error = '<p class="text-danger">'._safe($this->error['quantity']).'</p>';
			return $error;
		}
	}

	public function getCost(){
		return _safe($this->cost);
	}

	public function getCostError(){
		if($this->error['cost']){
			$error = '<p class="text-danger">'._safe($this->error['cost']).'</p>';
			return $error;
		}
	}

	public function getCurrencies(){
		if(!$this->currency){
			$query = "SELECT preferencia FROM negocio_preferencia WHERE id_negocio = :id_negocio AND id_preferencia = 2";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_negocio', $this->businessId, PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				$this->currency = $row['preferencia'];
			}
		}
		$query = "SELECT iso, divisa FROM divisa";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			if($this->currency == $row['iso']){
				echo '<option value="'.$row['iso'].'" selected>'.$row['iso'].'</option>';
			}else{
				echo '<option value="'.$row['iso'].'">'.$row['iso'].'</option>';
			}
		}
	}

	public function getCurrencyError(){
		if($this->error['currency']){
			$error = '<p class="text-danger">'._safe($this->error['currency']).'</p>';
			return $error;
		}
	}

	public function getDescription(){
		return _safe($this->description);
	}

	public function getDescriptionError(){
		if($this->error['description']){
			$error = '<p class="text-danger">'._safe($this->error['description']).'</p>';
			return $error;
		}
	}

	public function getCondition(){
		return _safe($this->condition);
	}

	public function getRestriction(){
		return _safe($this->restriction);
	}

	public function getTokenError(){
		if($this->error['token']){
			$error = '<p class="text-danger">'._safe($this->error['token']).'</p>';
			return $error;
		}
	}

	public function getImageError(){
		if($this->error['image']){
			$error = '<p class="text-danger">'._safe($this->error['image']).'</p>';
			return $error;
		}
	}

	public function getPage(){
		return $this->pagination['page'];
	}

	public function getTotal(){
		return $this->pagination['total'];
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
		file_put_contents(ROOT.'\assets\error_logs\manage_certificates.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}

	private function min_precision($x){
		$p = 2;
		$e = pow(10,$p);
		return floor($x*$e)==$x*$e?sprintf("%.${p}f",$x):$x;
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