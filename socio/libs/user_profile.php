<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace socio\libs;
use assets\libs\connection;
use PDO;

class user_profile {
	private $con;
	private $user = array(
		'id' => null,
		'username' => null,
		'email' => null,
		'eSmarties' => null,
		'image' => null,
		'name' => null,
		'last_name' => null,
		'city_id' => null,
		'city' => null,
		'state' => null,
		'country' => null,
		'alias' => null,
		'invited' => 0
	);
	private $invite = array(
		'email' => null,
		'message' => null
	);
	private $error = array(
		'email' => null,
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
		$query = "SELECT u.username, u.email, u.esmarties, u.imagen, u.nombre, u.apellido, u.id_ciudad 
			FROM usuario u 
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
			$this->user['city_id'] = $row['id_ciudad'];
			if(!empty($row['nombre']) || !empty($row['apellido'])){
				$this->user['alias'] = _safe($row['nombre'].' '.$row['apellido']);
			}else{
				$this->user['alias'] = _safe($row['username']);
			}
			if($this->user['city_id']){
				$query = "SELECT c.ciudad, e.estado, p.pais 
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
					$this->user['state'] = $row['estado'];
					$this->user['country'] = $row['pais'];
				}
			}
			$query = "SELECT COUNT(*) FROM usuario_referencia WHERE id_usuario = :id_usuario";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				$this->user['invited'] = $row['COUNT(*)'];
			}
		}
		return;
	}

	public function set_email($email = null){
		if($email){
			$this->invite['email'] = trim($email);
			$email = filter_var($email, FILTER_VALIDATE_EMAIL);
			if(!$email){
				$this->error['email'] = 'Escribe una dirección de correo electrónico correcta. Ejemplo: usuario@ejemplo.com.';
				return false;
			}
			$query = "SELECT 1 FROM usuario WHERE email = :email";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':email', $this->invite['email'], PDO::PARAM_STR);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				$this->error['email'] = 'Esta persona ya es un socio de Travel Points.';
				return false;
			}
			$this->invite['email'] = $email;
			return;
		}
		$this->error['email'] = 'Este campo es obligatorio.';
		return false;
	}

	public function set_message($message = null){
		if($message){
			$this->invite['message'] = trim($message);
			return;
		}
		return false;
	}

	public function invite_friend(array $post){
		$this->set_email($post['email']);
		$this->set_message($post['message']);
		if(!array_filter($this->error)){
			if(isset($_SESSION['last_email'])){
				if($_SESSION['last_email'] + 15 > time()){
					$left = $_SESSION['last_email'] + 15 - time();
					$this->error['error'] = 'Debes esperar '.$left.' segundos para enviar otra invitación.';
					return false;
				}else{
					$_SESSION['last_email'] = time();
				}
			}else{
				$_SESSION['last_email'] = time();
			}
			$vars['title'] = '¡'.$this->user['alias'].' te ha invitado a Travel Points!';
			$vars['header'] = '¡'.$this->user['alias'].' te ha invitado a Travel Points!';
			$vars['content'] =  '¡Afíliate! es gratis y siempre lo será. Descubre una infinidad de negocios que te regalan cosas y puntos para que los canjees por regalos. | Affiliate! It\'s free and always will be. Discover an infinity of businesses that give you things and points for gifts .';
			$vars['register_link'] = HOST.'/hazte-socio?ref='._safe($this->user['username']);
			$vars['message_title'] = $this->user['alias'].' te ha escrito un mensaje | he wrote you a message';
			$vars['message'] = $this->invite['message'];
			$body_alt =
				$this->user['alias'].' te ha invitado a ser parte de Travel Points. ¡Bienvenido al club de los compradores inteligentes! 
				'.$this->invite['message'].'. Sigue este enlace para registrarte: '.HOST.'/hazte-socio?ref='.$this->user['username'];
			require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/phpmailer/PHPMailerAutoload.php';
			$mail = new \PHPMailer;
			$mail->CharSet = 'UTF-8';
			// $mail->SMTPDebug = 3; // CONVERSACION ENTRE CLIENTE Y SERVIDOR
			$mail->isSMTP();
			$mail->Host = 'single-5928.banahosting.com';
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = 'ssl';
			$mail->Port = 465;
			// El correo que hará el envío
			$mail->Username = 'notification@travelpoints.com.mx';
			$mail->Password = '20464273jd';
			$mail->setFrom('notification@travelpoints.com.mx', 'Travel Points');
			// El correo al que se enviará
			$mail->addAddress($this->invite['email']);
			// Hacerlo formato HTML
			$mail->isHTML(true);
			// Formato del correo
			$mail->Subject = $vars['title'];
			$mail->Body    = $this->email_template($vars);
			$mail->AltBody = $body_alt;

			if(!$mail->send()){
				$this->error['error'] = 'Debido a problemas con nuestros servidores, no se ha podido enviar la invitación. Intenta nuevamente.';
			}else{
				$_SESSION['notification']['success'] = 'La invitación se ha enviado exitosamente.';
				header('Location: '.HOST.'/socio/perfil/');
				die();
			}
			return;
		}
		return false;
	}

	private function email_template(array $vars){
		if($vars['message'] && $vars['message_title']){
			$message = 
				'<tr>
					<td class="tablepadding" align="center" style="color: #444; padding:10px; font-size:13px; line-height:20px;">
						'._safe($vars['message_title']).'
					</td>
				</tr>
				<tr>
					<td class="tablepadding" align="center" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
						'._safe($vars['message']).'
					</td>
				</tr>';
		}else{
			$message = '';
		}
		$html = 
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>'.$vars['title'].'</title>
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
											<img alt="Travel Points" src="'.HOST.'/assets/img/LOGOV.png" style="padding-bottom: 0; display: inline !important; width:250px; height:auto;">
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
					<td>
						<table border="0" cellpadding="0" cellspacing="0" width="100%">
							<tbody>
								<tr>
									<td bgcolor="#0082b7" align="center" style="padding:16px 10px; line-height:24px; color:#ffffff; font-weight:bold">
										'._safe($vars['header']).'
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td class="tablepadding" style="color: #444; padding:20px; font-size:14px; line-height:20px;">
						<table border="0" cellpadding="0" cellspacing="0" width="100%">
							<tbody>
								<tr>
									<td class="tablepadding" align="center" style="color: #444; padding:10px; font-size:13px; line-height:20px;">
										<img style="width: 50px; height: 50px; margin-right: 8px; object-fit: cover; overflow: hidden; border: 0; border-radius: 6px; vertical-align: middle;" src="'.HOST.'/assets/img/user_profile/'.$this->user['image'].'" alt="Foto de '._safe($this->user['alias']).'">
									</td>
								</tr>
								<tr>
									<td align="center" class="tablepadding" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
										<strong>¡Bienvenido al club de los compradores inteligentes! | Welcome to the smart shoppers club!</strong>
									</td>
								</tr>
								<tr>
									<td class="tablepadding" align="center" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
										'._safe($vars['content']).'
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td class="tablepadding" style="border-top:1px solid #eaeaea;border-bottom:1px solid #eaeaea;padding:13px 20px;"><table width="100%" align="center" cellpadding="0" cellspacing="0" border="0">
							<tbody>
								'.$message.'
								<tr>
									<td class="tablepadding" align="center" style="color: #444; padding:20px; font-size:14px; line-height:30px;">
										<a style="outline:none; background-color:#0082b7; text-decoration:none; padding: 10px 15px; color:#fff;" href="'.$vars['register_link'].'" target="_blank">
											¡Hazte socio! | Become a partner!
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
									<a href="https://www.facebook.com/TravelPointsMX" target="_blank" style="text-decoration:none; outline:none;">
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
							&copy; Travel Points 2017 Todos los derechos reservados.
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

	public function get_header_title(){
		$html = 
		'<a href="'.HOST.'/socio/'.$this->get_username().'" target="_blank" class="text-default">'.$this->get_alias().'</a>';
		return $html;
	}

	public function get_image(){
		$html = 
			'<a hreF="'.HOST.'/socio/perfil/editar">
				<img src="'.HOST.'/assets/img/user_profile/'.$this->user['image'].'" alt="Foto de perfil de '.$this->user['alias'].'">
			</a>';
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

	public function get_email(){
		return _safe($this->user['email']);
	}

	public function get_name(){
		return _safe($this->user['name']);
	}

	public function get_last_name(){
		return _safe($this->user['last_name']);
	}

	public function get_city(){
		return _safe($this->user['city']);
	}

	public function get_state(){
		return _safe($this->user['state']);
	}

	public function get_country(){
		return _safe($this->user['country']);
	}

	public function get_location(){
		if($this->user['city'] && $this->user['country']){
			return _safe($this->user['city'].', '.$this->user['country']);
		}
	}

	public function get_invited(){
		return _safe($this->user['invited']);
	}

	public function get_invite_email(){
		return _safe($this->invite['email']);
	}

	public function get_invite_email_error(){
		if($this->error['email']){
			return '<p class="text-danger">'._safe($this->error['email']).'</p>';
		}
	}

	public function get_invite_message(){
		return _safe($this->invite['message']);
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
		file_put_contents(ROOT.'\assets\error_logs\user_profile.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>