<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace assets\libs;
use PDO;

class recover_account {
	private $con;
	private $recover = array(
		'input_pass' => null,
		'input_email' => null
	);
	private $error = array(
		'input_pass' => null, 
		'input_email' => null,
		'warning' => null,
		'error' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		return;
	}

	public function recover_password(array $post){
		$this->set_input_pass($post['input']);
		if(!array_filter($this->error)){
			$query = "SELECT id_usuario, email FROM usuario WHERE email = :input OR username = :input2";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':input', $this->recover['input_pass'], PDO::PARAM_STR);
				$stmt->bindValue(':input2', $this->recover['input_pass'], PDO::PARAM_STR);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				$email = $row['email'];
				$hash = md5( rand(0,1000) );
				$title = 'Reestablecer contraseña';
				$content = 'Para reestablecer tu contrase&ntilde;a, debes seguir este enlace para comprobar tu identidad: <a style="outline:none; color:#0082b7; text-decoration:none;" href="'.HOST.'/cambiar-contrasena?email='.$email.'&codigo='.$hash.'">reestablecer contrase&ntilde;a</a>.<br>Si no solicitaste cambiar tu contrase&ntilde;a, por favor ignora este mensaje.';
				$body_alt = 
					'Para reestablecer tu contrase&ntilde;a, debes seguir este enlace para comprobar tu identidad: '.HOST.'/cambiar-contrasena?email='.$email.'&codigo='.$hash.'. Si no solicitaste cambiar tu contrase&ntilde;a, por favor ignora este mensaje.';
				require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/phpmailer/PHPMailerAutoload.php';
				$mail = new \PHPMailer;
				$mail->CharSet = 'UTF-8';
				// $mail->SMTPDebug = 3; // CONVERSACION ENTRE CLIENTE Y SERVIDOR
				// $mail->isSMTP();
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
				$mail->Subject = 'Reestablecer contraseña';
				$mail->Body    = $this->email_template($title, $content);
				$mail->AltBody = $body_alt;

				if(!$mail->send()){
					$this->error['error'] = 'El correo de confirmación no se pudo enviar debido a una falla en el servidor. Intenta nuevamente.';
					return;
				}

				$query = "UPDATE usuario SET hash_activacion = :hash WHERE id_usuario = :id_usuario";
				try{
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(':hash', $hash, PDO::PARAM_STR);
					$stmt->bindValue(':id_usuario', $row['id_usuario'], PDO::PARAM_INT);
					$stmt->execute();
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}

				$_SESSION['notification']['success'] = 'Se ha enviado al correo electrónico vinculado con esta cuenta un enlace para restablecer su contraseña. Si no lo recibe por favor verifique su bandeja de correo no deseado. Si el correo se encuentra ahí, por favor márquelo como correo seguro.';
				header('Location: '.HOST.'/recuperar-cuenta');
				die();
				return;
			}
			$this->error['input_email'] = 'No existe ninguna cuenta asociada con ese nombre de usuario o correo electrónico.';
			return false;
		}
		$this->error['warning'] = 'Un campo tiene errores. Verifícalo cuidadosamente.';
		return false;
	}

	public function send_email(array $post){
		$this->set_input_email($post['input']);
		if(!array_filter($this->error)){
			$query = "SELECT id_usuario, email, verificado FROM usuario WHERE email = :input OR username = :input2";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':input', $this->recover['input_email'], PDO::PARAM_STR);
				$stmt->bindValue(':input2', $this->recover['input_email'], PDO::PARAM_STR);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				if($row['verificado'] == 0){
					$email = $row['email'];
					$hash = md5( rand(0,1000) );

					$title = 'Confirmación de correo electrónico';
					$content = 'Confirma tu correo electr&oacute;nico <a style="outline:none; color:#0082b7; text-decoration:none;" href="'.HOST.'/login?email='.$email.'&codigo='.$hash.'">haciendo clic en este enlace</a>.';
					$body_alt =
						'Confirma tu correo electr&oacute;nico siguiendo este enlace: '.HOST.'/login?email='.$email.'&codigo='.$hash;
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
					$mail->Body    = $this->email_template($title, $content);
					$mail->AltBody = $body_alt;

					if(!$mail->send()){
						$this->error['error'] = 'El correo de confirmación no se pudo enviar debido a una falla en el servidor. Intenta nuevamente.';
						return;
					}

					$query = "UPDATE usuario SET hash_activacion = :hash WHERE id_usuario = :id_usuario";
					try{
						$stmt = $this->con->prepare($query);
						$stmt->bindValue(':hash', $hash, PDO::PARAM_STR);
						$stmt->bindValue(':id_usuario', $row['id_usuario'], PDO::PARAM_INT);
						$stmt->execute();
					}catch(\PDOException $ex){
						$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
						return false;
					}

					$_SESSION['notification']['success'] = 'Se ha enviado un nuevo correo de verificación. Si no te ha llegado, por favor verifica el correo basura. Si el correo se encuentra ahí, márcalo como correo seguro.';
				}else{
					$_SESSION['notification']['info'] = 'Esta cuenta ya está verificada.';
				}
				header('Location: '.HOST.'/recuperar-cuenta');
				die();
				return;
			}
			$this->error['input_email'] = 'No existe ninguna cuenta asociada con ese nombre de usuario o correo electrónico.';
			return false;
		}
		$this->error['warning'] = 'Un campo tiene errores. Verifícalo cuidadosamente.';
		return false;
	}

	private function email_template($title, $content){
		$html = 
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>'._safe($title).'</title>
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
										<strong>'._safe($title).'</strong>
									</td>
								</tr>
								<tr>
									<td class="tablepadding" align="center" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
										'.$content.'<br>
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

	private function set_input_pass($input = null){
		if($input){
			$this->recover['input_pass'] = trim($input);
			if(filter_var($input, FILTER_VALIDATE_EMAIL)){
				return true;
			}elseif(!preg_match('/^[a-zA-Z0-9]+$/ui',$input)){
				$this->error['input_pass'] = 'Ingresa un nombre de usuario o correo electrónico correctos.';
				return false;
			}else{
				return true;
			}
		}
		$this->error['input_pass'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_input_email($input = null){
		if($input){
			$this->recover['input_email'] = trim($input);
			if(filter_var($input, FILTER_VALIDATE_EMAIL)){
				return true;
			}elseif(!preg_match('/^[a-zA-Z0-9]+$/ui',$input)){
				$this->error['input_email'] = 'Ingresa un nombre de usuario o correo electrónico correctos.';
				return false;
			}else{
				return true;
			}
		}
		$this->error['input_email'] = 'Este campo es obligatorio.';
		return false;
	}

	public function get_input_pass(){
		return _safe($this->recover['input_pass']);
	}

	public function get_input_pass_error(){
		if($this->error['input_pass']){
			return '<p class="text-danger">'._safe($this->error['input_pass']).'</p>';
		}
	}

	public function get_input_email(){
		return _safe($this->recover['input_email']);
	}

	public function get_input_email_error(){
		if($this->error['input_email']){
			return '<p class="text-danger">'._safe($this->error['input_email']).'</p>';
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
		file_put_contents(ROOT.'\assets\error_logs\recover_account.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>