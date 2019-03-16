<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace socio\libs;
use assets\libs\connection;
use PDO;

class user_edit {
	private $con;
	private $user = array(
		'id' => null,
		'username' => null,
		'email' => null,
		'eSmarties' => 0,
		'image' => null,
		'name' => null,
		'last_name' => null,
		'gender' => null,
		'birthdate' => null,
		'phone' => null,
		'city_id' => null,
		'city' => null,
		'state_id' => null,
		'state' => null,
		'country_id' => null,
		'country' => null,
		'alias' => null
	);
	private $error = array(
		'email' => null,
		'name' => null,
		'last_name' => null,
		'gender' => null,
		'birthdate' => null,
		'phone' => null,
		'city' => null,
		'state' => null,
		'country' => null,
		'warning' => null,
		'error' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->user['id'] = $_SESSION['user']['id_usuario'];
		$this->load_data();
		return;
	}

	private function load_data(){
		$query = "SELECT username, email, esmarties, imagen, nombre, apellido, sexo, fecha_nacimiento, telefono, id_ciudad 
			FROM usuario 
			WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->user['username'] = $row['username'];
			$this->user['email'] = $row['email'];
			$this->user['eSmarties'] = $row['esmarties'];
			if(!empty($row['imagen'])){
				$this->user['image'] = $row['imagen'];
			}else{
				$this->user['image'] = 'default.jpg';
			}
			$this->user['name'] = $row['nombre'];
			$this->user['last_name'] = $row['apellido'];
			$this->user['gender'] = $row['sexo'];
			$this->user['birthdate'] = $row['fecha_nacimiento'];
			$this->user['phone'] = $row['telefono'];
			$this->user['city_id'] = $row['id_ciudad'];
			if(!empty($row['nombre']) || !empty($row['apellido'])){
				$this->user['alias'] = $row['nombre'].' '.$row['apellido'];
			}else{
				$this->user['alias'] = $row['username'];
			}
			if($this->user['city_id']){
				$query = "SELECT c.ciudad, e.id_estado, e.estado, p.id_pais, p.pais 
					FROM ciudad c
					INNER JOIN estado e ON c.id_estado = e.id_estado 
					INNER JOIN pais p ON e.id_pais = p.id_pais
					WHERE c.id_ciudad = :id_ciudad";
				try{
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(':id_ciudad', $this->user['city_id'], PDO::PARAM_INT);
					$stmt->execute();
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				if($row = $stmt->fetch()){
					$this->user['city'] = $row['ciudad'];
					$this->user['state_id'] = $row['id_estado'];
					$this->user['state'] = $row['estado'];
					$this->user['country_id'] = $row['id_pais'];
					$this->user['country'] = $row['pais'];
				}
			}
		}
		return;
	}

	public function set_image($files){
		$image = new \assets\libraries\bulletproof\bulletproof($files);
		$image->setName('foto-de-perfil-de-'.$this->friendly_url($this->user['username']).'-en-esmart-club');
		$image->setLocation(ROOT.'/assets/img/user_profile');
		if($image['image']){
			$upload = $image->upload();
			if(!$upload){
				$this->error['error'] = $image['error'];
				return false;
			}
			$file_name = $image->getName().'.'.$image->getMime();
			if(move_uploaded_file($files['image']['tmp_name'], $image->getFullPath())){
				if($this->user['image'] != $file_name){
					if($this->user['image'] != 'default.jpg' && file_exists(ROOT.'/assets/img/user_profile/'.$this->user['image'])){
						unlink(ROOT.'/assets/img/user_profile/'.$this->user['image']);
					}
					$query = "UPDATE usuario SET imagen = :imagen WHERE id_usuario = :id_usuario";
					$params = array(':imagen' => $file_name,':id_usuario' => $this->user['id']);
					try{
						$stmt = $this->con->prepare($query);
						$stmt->execute($params);
					}catch(\PDOException $ex){
						$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
						return false;
					}
				}
				$_SESSION['notification']['success'] = 'Foto de perfil subida exitosamente.';
				header('Location: '.HOST.'/socio/perfil/editar');
				die();
				return;
			}else{
				$this->error['error'] = 'La subida falló debido a un error.';
			}
		}
		if($files['image']['error'] == 1){
			$this->error['image'] = 'Has excedido el límite de imagen de 2MB.';
		}
		return;
	}

	public function set_email(array $post){
		if($post['email']){
			$email = filter_var($post['email'], FILTER_VALIDATE_EMAIL);
			if(!$email){
				// $this->error['email'] = 'Please enter a correct e-mail address. Example: user@example.com.';
				$this->error['email'] = 'Escribe una dirección de correo electrónico correcta. Ejemplo: usuario@ejemplo.com.';
				return false;
			}
			if($this->user['email'] != $email){
				$query = "SELECT 1 FROM usuario WHERE email = :email";
				try{
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(':email', $email, PDO::PARAM_STR);
					$stmt->execute();
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				if($row = $stmt->fetch()){
					// $this->error['email'] = $email.' is already registered. Try with another e-mail.';
					$this->error['email'] = 'Correo electrónico ya registrado. Prueba con uno distinto.';
					return false;
				}
				$hash = md5( rand(0,1000) );

				$body_alt =
					'Hola, '.$this->user['alias'].'. Has solicitado un cambio de correo electrónico, para completar el proceso debes confirmar tu nuevo correo electrónico entrando a este enlace: '.HOST.'/login?email='.$email.'&codigo='.$hash;
				require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/phpmailer/PHPMailerAutoload.php';
				$mail = new \PHPMailer;
				$mail->CharSet = 'UTF-8';
				// $mail->SMTPDebug = 3; // CONVERSACION ENTRE CLIENTE Y SERVIDOR
				$mail->isSMTP();
				$mail->Host = 'a2plcpnl0735.prod.iad2.secureserver.net';
				$mail->SMTPAuth = true;
				$mail->SMTPSecure = 'ssl';
				$mail->Port = 465;
				// El correo que hará el envío
				$mail->Username = 'notificacion@esmartclub.com';
				$mail->Password = 'Alan@2017_pv';
				$mail->setFrom('notificacion@esmartclub.com', 'eSmart Club');
				// El correo al que se enviará
				$mail->addAddress($email);
				// Hacerlo formato HTML
				$mail->isHTML(true);
				// Formato del correo
				$mail->Subject = 'Confirmación de correo electrónico.';
				$mail->Body    = $this->email_template($email, $hash);
				$mail->AltBody = $body_alt;

				if(!$mail->send()){
					$this->error['error'] = 'El correo de confirmación no se pudo enviar debido a una falla en el servidor. Intenta nuevamente.';
					return;
				}

				$query = "UPDATE usuario SET email = :email, hash_activacion = :hash, verificado = 0 WHERE id_usuario = :id_usuario";
				$query_params = array(':email' => $email, ':hash' => $hash, ':id_usuario' => $this->user['id']);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($query_params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}

				unset($_SESSION['user']);
				unset($_SESSION['business']);
				unset($_SESSION['notification']);
				header('Location: '.HOST.'/');
				die();
				return;
			}
			return false;
		}
		// $this->error['email'] = 'You must enter an e-mail.';
		$this->error['email'] = 'Este campo es obligatorio.';
		return false;
	}

	private function email_template($email, $hash){
		$html = 
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Confirmaci&oacute;n de correo electr&oacute;nico</title>
<style type="text/css">
@media only screen and (max-width: 600px) {
 table[class="contenttable"] {
 width: 320px !important;
 border-width: 3px!important;
}
 table[class="tablefull"] {
 width: 100% !important;
}
 table[class="tablefull"] + table[class="tablefull"] td {
 padding-top: 0px !important;
}
 table td[class="tablepadding"] {
 padding: 15px !important;
}
}
</style>
</head>
<body style="margin:0; border: none; background:#f7f8f9">
	<table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
		<tr>
			<td align="center" valign="top"><table class="contenttable" border="0" cellpadding="0" cellspacing="0" width="600" bgcolor="#ffffff" style="border-width: 8px; border-style: solid; border-collapse: separate; border-color:#e9e9e9; margin-top:40px; font-family:Arial, Helvetica, sans-serif">
				<tr>
					<td>
						<table border="0" cellpadding="0" cellspacing="0" width="100%">
							<tbody>
								<tr>
									<td width="100%" height="40">&nbsp;</td>
								</tr>
								<tr>
									<td valign="top" align="center">
										<a href="'.HOST.'" target="_blank">
											<img alt="eSmart Club" src="'.HOST.'/assets/img/logo.png" style="padding-bottom: 0; display: inline !important;">
										</a>
									</td>
								</tr>
								<tr>
									<td width="100%" height="40">&nbsp;</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td class="tablepadding" style="color: #444; padding:20px; font-size:14px; line-height:20px; border-top-width:1px; border-top-style:solid; border-top-color:#ececec;">
						<table border="0" cellpadding="0" cellspacing="0" width="100%">
							<tbody>
								<tr>
									<td align="center" class="tablepadding" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
										<strong>Confirmaci&oacute;n de correo electr&oacute;nico</strong>
									</td>
								</tr>
								<tr>
									<td class="tablepadding" align="center" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
										Hola, '.$this->user['alias'].'. Has solicitado un cambio de correo electrónico, para completar el proceso debes confirmar tu nuevo correo electrónico haciendo clic <a style="outline:none; color:#0082b7; text-decoration:none;" href="'.HOST.'/login?email='.$email.'&codigo='.$hash.'">aquí</a>.<br>
										Para cualquier aclaraci&oacute;n contacta a nuestro equipo de soporte.<br>
										<a style="outline:none; color:#0082b7; text-decoration:none;" href="mailto:soporte@esmartclub.com">
											soporte@esmartclub.com
										</a>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td bgcolor="#fcfcfc" class="tablepadding" style="padding:20px 0; border-top-width:1px;border-top-style:solid;border-top-color:#ececec;border-collapse:collapse">
						<table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-size:13px;color:#999999; font-family:Arial, Helvetica, sans-serif">
							<tbody>
								<tr>
									<td align="center" class="tablepadding" style="line-height:20px; padding:20px;">
										Marina Vallarta Business Center, Oficina 204, Plaza Marina.<br>
										Puerto Vallarta, México.<br>
										01 800 400 INFO (4636), (322) 225 9635.<br>
										<a style="outline:none; color:#0082b7; text-decoration:none;" href="mailto:info@infochannel.si">info@infochannel.si</a>
									</td>
								</tr>
							</tbody>
						</table>
						<table align="center">
							<tr>
								<td style="padding-right:10px; padding-bottom:9px;">
									<a href="https://www.facebook.com/eSmart-Club-130433773794677" target="_blank" style="text-decoration:none; outline:none;">
										<img src="'.HOST.'/assets/img/facebook.png" width="32" height="32" alt="Facebook">
									</a>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-size:13px;color:#999999; font-family:Arial, Helvetica, sans-serif">
				<tbody>
					<tr>
						<td class="tablepadding" align="center" style="line-height:20px; padding:20px;">
							&copy; eSmart Club 2017 Todos los derechos reservados.
						</td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>
</table>
</body>
</html>';
		return $html;
	}

	public function set_information(array $post){
		$this->set_name($post['name']);
		$this->set_last_name($post['last_name']);
		if(isset($post['gender'])){
			$this->set_gender($post['gender']);
		}
		$this->set_birthdate($post['birthdate']);
		$this->set_phone($post['phone']);
		$this->set_city($post['city'], $post['state'], $post['country']);
		if(!array_filter($this->error)){
			$query = "UPDATE usuario SET 
				nombre = :nombre,
				apellido = :apellido,
				sexo = :sexo,
				fecha_nacimiento = :fecha_nacimiento,
				telefono = :telefono,
				id_ciudad = :id_ciudad 
				WHERE id_usuario = :id_usuario";
			$params = array(
				':nombre' => $this->user['name'],
				':apellido' => $this->user['last_name'],
				':sexo' => $this->user['gender'],
				':fecha_nacimiento' => $this->user['birthdate'],
				':telefono' => $this->user['phone'],
				':id_ciudad' => $this->user['city_id'],
				'id_usuario' => $this->user['id']
			);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$_SESSION['notification']['success'] = 'Información de usuario actualizada exitosamente.';
			header('Location: '.HOST.'/socio/perfil/editar');
			die();
			return;
		}
		$this->error['warning'] = 'Uno o más campos tienen errores. Verifícalos cuidadosamente.';
		return false;
	}

	private function set_name($name = null){
		if($name){
			$name = trim($name);
			if(!preg_match("/^[\p{L} ']+$/ui",$name)) {
				$this->error['name'] = 'Solo se permiten letras, espacios y ( \' ).';
				return false;
			}
			$this->user['name'] = $name = ucfirst($name);
			return;
		}
		$this->user['name'] = null;
		return;
	}

	private function set_last_name($last_name = null){
		if($last_name){
			$last_name = trim($last_name);
			if(!preg_match("/^[\p{L} ']+$/ui",$last_name)) {
				$this->error['last_name'] = 'Solo se permiten letras, espacios y ( \' ).';
				return false;
			}
			$this->user['last_name'] = ucfirst($last_name);
			return;
		}
		$this->user['last_name'] = null;
		return;
	}

	private function set_gender($gender = null){
		if($gender){
			if($gender != 1 && $gender != 2){
				$this->error['sex'] = 'Selecciona tu sexo.';
				return false;
			}
			$this->user['gender'] = $gender;
			return;
		}
		$this->user['gender'] = null;
		return;
	}

	private function set_birthdate($birthdate = null){
		if($birthdate){
			$birthdate = strtotime(str_replace('/', '-', $birthdate));
			if(!$birthdate){
				$this->error['birthdate'] = 'Formato de fecha incorrecto. Utiliza la herramienta.';
				return false;
			}
			$this->user['birthdate'] = date("Y/m/d", $birthdate);
			return;
		}
		$this->user['birthdate'] = null;
		return;
	}

	private function set_phone($phone = null){
		if($phone){
			$phone = trim($phone);
			if(!preg_match("/^[0-9() -]+$/ui",$phone)) {
				$this->error['phone'] = 'Solo se permiten números, espacios, guiones y paréntesis.';
				return false;
			}
			$this->user['phone'] = $phone;
			return;
		}
		$this->user['phone'] = null;
		return;
	}

	public function set_city($city = null, $state = null, $country = null){
		if($city && $state && $country){
			if(!filter_var($city, FILTER_VALIDATE_INT)){
				$this->error['city'] = 'Selecciona una ciudad de la lista.';
				return false;
			}
			if(!filter_var($state, FILTER_VALIDATE_INT)){
				$this->error['state'] = 'Selecciona un estado de la lista.';
				return false;
			}
			if(!filter_var($country, FILTER_VALIDATE_INT)){
				$this->error['country'] = 'Selecciona un país de la lista.';
				return false;
			}
			$query = "SELECT c.id_estado, e.id_pais 
				FROM ciudad c 
				INNER JOIN estado e ON c.id_estado = e.id_estado
				WHERE c.id_ciudad = :id_ciudad";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_ciudad', $city, PDO::PARAM_STR);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				if($state == $row['id_estado'] && $country == $row['id_pais']){
					$this->user['city_id'] = $city;
					$this->user['state_id'] = $state;
					$this->user['country_id'] = $country;
				}
			}
		}
		return;
	}

	public function get_header_title(){
		$html = 
		'<a href="'.HOST.'/socio/'.$this->get_username().'" target="_blank" class="text-default">'.$this->get_alias().'</a>';
		return $html;
	}

	public function get_image(){
		$html = '<img src="'.HOST.'/assets/img/user_profile/'.$this->user['image'].'" alt="Foto de perfil de '.$this->user['alias'].'">';
		return $html;
	}

	public function get_alias(){
		return _safe($this->user['alias']);
	}

	public function get_eSmarties(){
		$eSmarties = round($this->user['eSmarties'],2);
		return $eSmarties;
	}

	public function get_username(){
		return _safe($this->user['username']);
	}

	public function get_username_error(){
		if($this->error['username']){
			$error = '<p class="text-danger">'._safe($this->error['username']).'</p>';
			return $error;
		}
	}

	public function get_email(){
		return _safe($this->user['email']);
	}

	public function get_email_error(){
		if($this->error['email']){
			$error = '<p class="text-danger">'._safe($this->error['email']).'</p>';
			return $error;
		}
	}

	public function get_name(){
		return _safe($this->user['name']);
	}

	public function get_name_error(){
		if($this->error['name']){
			$error = '<p class="text-danger">'._safe($this->error['name']).'</p>';
			return $error;
		}
	}

	public function get_last_name(){
		return _safe($this->user['last_name']);
	}

	public function get_last_name_error(){
		if($this->error['last_name']){
			$error = '<p class="text-danger">'._safe($this->error['last_name']).'</p>';
			return $error;
		}
	}

	public function get_gender(){
		if($this->user['gender'] == 1){
			$html = '<input id="male" type="radio" name="gender" value="1" checked><label for="male">Hombre</label><input id="female" type="radio" name="gender" value="2"><label for="female">Mujer</label>';
		}elseif($this->user['gender'] == 2){
			$html = '<input id="male" type="radio" name="gender" value="1"><label for="male">Hombre</label><input id="female" type="radio" name="gender" value="2" checked><label for="female">Mujer</label>';
		}else{
			$html = '<input id="male" type="radio" name="gender" value="1"><label for="male">Hombre</label><input id="female" type="radio" name="gender" value="2"><label for="female">Mujer</label>';
		}
		return $html;
	}

	public function get_gender_error(){
		if($this->error['gender']){
			$error = '<p class="text-danger">'._safe($this->error['gender']).'</p>';
			return $error;
		}
	}

	public function get_birthdate(){
		if($this->user['birthdate']){
			$birthdate = date('d/m/Y', strtotime($this->user['birthdate']));
			return $birthdate;
		}
	}

	public function get_birthdate_error(){
		if($this->error['birthdate']){
			$error = '<p class="text-danger">'._safe($this->error['birthdate']).'</p>';
			return $error;
		}
	}

	public function get_phone(){
		return _safe($this->user['phone']);
	}

	public function get_phone_error(){
		if($this->error['phone']){
			$error = '<p class="text-danger">'._safe($this->error['phone']).'</p>';
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
			if($this->user['country_id'] == $row['id_pais']){
				$html .= '<option value="'.$row['id_pais'].'" selected>'.$country.'</option>';
			}else{
				$html .= '<option value="'.$row['id_pais'].'">'.$country.'</option>';
			}
		}
		return $html;
	}

	public function get_country_error(){
		if($this->error['country']){
			$error = '<p class="text-danger">'._safe($this->error['country']).'</p>';
			return $error;
		}
	}

	public function get_state(){
		$html = null;
		if($this->user['country_id']){
			$query = "SELECT id_estado, estado FROM estado WHERE id_pais = :id_pais";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_pais', $this->user['country_id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$state = _safe($row['estado']);
				if($this->user['state_id'] == $row['id_estado']){
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
			$error = '<p class="text-danger">'._safe($this->error['state']).'</p>';
			return $error;
		}
	}

	public function get_city(){
		$html = null;
		if($this->user['state_id']){
			$query = "SELECT id_ciudad, ciudad FROM ciudad WHERE id_estado = :id_estado";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_estado', $this->user['state_id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$city = _safe($row['ciudad']);
				if($this->user['city_id'] == $row['id_ciudad']){
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
			$error = '<p class="text-danger">'._safe($this->error['city']).'</p>';
			return $error;
		}
	}

	public function get_location(){
		if($this->user['city'] && $this->user['country']){
			return _safe($this->user['city'].', '.$this->user['country']);
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
		file_put_contents(ROOT.'\assets\error_logs\user_edit.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}

	private function friendly_url($url){
		$url = strtolower($this->replace_accents(trim($url))); // 1. Trim spaces around, replace all special chars and lowercase all
		$find = array(' ', '&', '\r\n', '\n', '+', ','); // 2. Reple spaces and union characters with ' - '
		$url = str_replace($find, '-', $url);
		$find = array('/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/'); // 3. Delete and replace the rest of special chars
		$repl = array('', ' ', '');
		$url = str_replace(' ', '-', trim(preg_replace($find, $repl, $url)));
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