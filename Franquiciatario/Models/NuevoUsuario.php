<?php 


namespace Franquiciatario\models;
use assets\libs\connection;
use PDO;

class NuevoUsuario {
	private $con;
	private $username = null;
	private $email = null;
	private $password = null;
	private $referral = array ('id' => null, 'username' => null);
	private $errors = array('method' => null, 'username' => null, 'email' => null, 'password' => null, 'retype' => null, 'referral' => null);

	public function __construct(connection $con){
		$this->con = $con->con;
	}

	public function setData(array $post){

		$this->setUsername($post['username']);
		$this->setEmail($post['email']);
		$this->setPassword($post['password'], $post['password-retype']);
		$this->setReferral($post['referral']);
		if($this->username && $this->email && $this->password && !array_filter($this->errors)){
			$this->register();
			return true;
		}
		return false;
	}

	private function register(){
		$query = "INSERT INTO usuario (
			username, 
			email, 
			password,
			hash_activacion
			) VALUES (
			:username, 
			:email, 
			:password,
			:hash_activacion
		)";
		$hash = md5( rand(0,1000) );
		$query_params = array(
			':username' => $this->username,
			':email' => $this->email,
			':password' => $this->password,
			':hash_activacion' => $hash
		);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($query_params);
			$lastId = $this->con->lastInsertId();


		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}

		// Lo registramos en solicitud hotel.
		
		$query1 = "insert into solicitudfr(id_franquiciatario,id_usuario,condicion) values(:franquiciatario,:usuario, :condicion)";

		try {
			$stm = $this->con->prepare($query1);

			$stm->execute(array(':franquiciatario'=>$_SESSION['id_franquiciatario'],
							':usuario'=>$lastId,
							':condicion'=>1));
			} catch (PDOException $e) {
			$this->error_log(__METHOD__,__LINE__,$e->getMessage());

			}
		

		
		// Si existe referencia, la inserta
		if($this->referral['id']){
			$query = "INSERT INTO usuario_referencia (id_usuario, id_nuevo_usuario) VALUES (:id_usuario, :id_nuevo_usuario)";
			$query_params = array(':id_usuario' => $this->referral['id'], ':id_nuevo_usuario' => $lastId);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($query_params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
		}

		$body_alt =
			'Bienvenido a Travel Points '.$this->username.'. Para completar tu registro debes confirmar tu correo electrónico entrando a este enlace: '.HOST.'/login?email='.$this->email.'&codigo='.$hash;
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
		$mail->addAddress($this->email);
		// Hacerlo formato HTML
		$mail->isHTML(true);
		// Formato del correo
		$mail->Subject = 'Confirmación de correo electrónico.';
		$mail->Body    = $this->email_template($this->email, $hash);
		$mail->AltBody = $body_alt;

		if(!$mail->send()){
			$_SESSION['notification']['info'] = 'El correo de aviso no se pudo enviar debido a una falla en el servidor. Intenta solicitando un nuevo correo de confirmación.';
		}

		$_SESSION['notification']['success'] = '¡Felicidades! Ya eres socio de Travel Points. Hemos enviado un correo de verificación a tu cuenta de correo electrónico: '.$this->email.'. Es necesario que verifiques tu cuenta para poder iniciar sesión.';
		$_SESSION['register_email'] = $this->email;
		header('Location: '.HOST.'/Franquiciatario/usuarios/nuevousuario');
		die();
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
										<strong>Bienvenido a Travel Points</strong>
									</td>
								</tr>
								<tr>
									<td class="tablepadding" align="center" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
										Para completar tu registro debes confirmar tu correo electrónico haciendo clic <a style="outline:none; color:#0082b7; text-decoration:none;" href="'.HOST.'/login?email='.$email.'&codigo='.$hash.'">aquí</a>.
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

	private function setUsername($username){
		if($username){
			$username = trim($username);
			if(!preg_match('/^[a-zA-Z0-9]+$/ui',$username)){
				// $this->errors['username'] = 'The username must only contain letters and numbers. Special characters including accents are not allowed.';
				$this->errors['username'] = 'Tu nombre de usuario debe contener solo caracteres alfanuméricos.';
				$this->username = $username;
				return $this;
			}
			$length = strlen($username);
			if($length < 3 || $length > 50){
				$this->errors['username'] = 'Tu nombre de usuario debe contener entre 3 y 50 caracteres.';
				$this->username = $username;
				return false;
			}
			$query = "SELECT 1 FROM usuario WHERE username = :username";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':username', $username, PDO::PARAM_STR);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				// $this->errors['username'] = 'This username is already registered.';
				$this->errors['username'] = 'Este nombre de usuario ya está registrado.';
				$this->username = $username;
				return $this;
			}
			$this->username = $username;
			return $this;
		}
		$this->errors['username'] = 'Este campo es obligatorio.';
		return $this;
	}

	private function setEmail($email){
		if($email){
			$email = filter_var($email, FILTER_VALIDATE_EMAIL);
			if(!$email){
				// $this->errors['email'] = 'Please enter a correct e-mail address. Example: user@example.com.';
				$this->errors['email'] = 'Escribe una dirección de correo electrónico correcta. Ejemplo: usuario@ejemplo.com.';
				$this->email = $email;
				return $this;
			}
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
				// $this->errors['email'] = 'This e-mail is already registered.';
				$this->errors['email'] = 'Este correo electrónico ya está registrado.';
				$this->email = $email;
				return $this;
			}
			$this->email = $email;
			return $this;
		}
		// $this->errors['email'] = 'You must enter your e-mail.';
		$this->errors['email'] = 'Este campo es obligatorio.';
		return $this;
	}

	private function setPassword($password, $retype){
		if($password){
			if($this->username && $password == $this->username){
				// $this->errors['password'] = 'Your username and password must be different.';
				$this->errors['password'] = 'Tu contraseña y nombre de usuario deben ser diferentes.';
				return $this;
			}
			if($this->email && $password == $this->email){
				// $this->errors['password'] = 'Your username and password must be different.';
				$this->errors['password'] = 'Tu contraseña y correo electrónico deben ser diferentes.';
				return $this;
			}
			if(strlen($password) < 6){
				$this->errors['password'] = 'Tu contraseña debe tener al menos 6 caracteres.';
				return false;
			}
			if($password == $retype){
				$options = ['cost' => 12];
				$this->password = password_hash($password, PASSWORD_BCRYPT, $options);
				return $this;
			}
			// $this->errors['retype'] = 'Passwords do not match.';
			$this->errors['retype'] = 'Las contraseñas no coinciden.';
			return $this;
		}
		// $this->errors['password'] = 'You must enter a password.';
		$this->errors['password'] = 'Este campo es obligatorio.';
		return $this;
	}

	public function setReferral($referral){
		if($referral){
			$referral = trim($referral);
			if(!preg_match('/^[a-zA-Z0-9]+$/ui',$referral)){
				// $this->errors['referral'] = 'The username must only contain letters and numbers. Special characters including accents are not allowed.';
				$this->errors['referral'] = 'El nombre de usuario debe contener solo caracteres alfanuméricos.';
				$this->referral['username'] = $referral;
				return $this;
			}
			$query = "SELECT id_usuario FROM usuario WHERE username = :username";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':username', $referral, PDO::PARAM_STR);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				$this->referral['id'] = $row['id_usuario'];
				$this->referral['username'] = $referral;
				return $this;
			}
			$this->errors['referral'] = 'El nombre de usuario es incorrecto o no existe.';
			$this->referral['username'] = $referral;
			return $this;
		}
		return $this;
	}

	public function getMethodError(){
		if($this->errors['method']){
			$error = 
			'<div class="alert alert-icon alert-dismissible alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				<strong>Oh no! </strong> It looks like we are having technical issues. Sorry for the inconvenience. Please try again later <i class="fa fa-smile-o" aria-hidden="true"></i>.
			</div>';
			return $error;
		}
	}

	public function getUsername(){
		return _safe($this->username);
	}

	public function getUsernameError(){
		if($this->errors['username']){
			$error = '<p class="text-danger">'._safe($this->errors['username']).'</p>';
			return $error;
		}
	}

	public function getEmail(){
		return _safe($this->email);
	}

	public function getEmailError(){
		if($this->errors['email']){
			$error = '<p class="text-danger">'._safe($this->errors['email']).'</p>';
			return $error;
		}
	}

	public function getPasswordError(){
		if($this->errors['password']){
			$error = '<p class="text-danger">'._safe($this->errors['password']).'</p>';
			return $error;
		}
	}

	public function getRetypePasswordError(){
		if($this->errors['retype']){
			$error = '<p class="text-danger">'._safe($this->errors['retype']).'</p>';
			return $error;
		}
	}

	public function getReferral(){
		return _safe($this->referral['username']);
	}

	public function getReferralError(){
		if($this->errors['referral']){
			$error = '<p class="text-danger">'._safe($this->errors['referral']).'</p>';
			return $error;
		}
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\regeistrodeusuariohotel.txt', '['.date('d/M/Y h:i:s A').' on '.$method.' on line '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		foreach ($this->errors as $key => $value){
			$this->errors[$key] = null;
		}
		$this->errors['method'] = true;
		return $this;
	}
}
?>
