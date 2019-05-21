<?php # Desarrollado por Info Channel
namespace assets\libs;
use PDO;

class contact_form {
	private $con;
	private $form = array(
		'name' => null,
		'subject' => null,
		'email' => null,
		'message' => null
	);
	private $error = array(
		'name' => null,
		'subject' => null,
		'email' => null,
		'message' => null,
		'warning' => null,
		'error' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		return;
	}

	public function send_message(array $post){
		$this->set_name($post['name']);
		$this->set_subject($post['subject']);
		$this->set_email($post['email']);
		$this->set_message($post['message']);
		if(!array_filter($this->error)){
			$body_alt = '';
			require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/phpmailer/PHPMailerAutoload.php';
			$mail = new \PHPMailer;
			$mail->CharSet = 'UTF-8';

			$mail->isSMTP();
			$mail->Host = 'single-5928.banahosting.com';
			 $mail->SMTPAuth = true;
			 $mail->SMTPSecure = 'ssl';
			 $mail->Port = 465;

			// $mail->Port = 465;
			// El correo que hará el envío
			$mail->Username = 'notification@travelpoints.com.mx';
			$mail->Password = '20464273jd';
			
			$mail->setFrom('notification@travelpoints.com.mx', 'Travel Points');
			// El correo al que se enviará
			$mail->addAddress("soporte@infochannel.si");
				// $mail->addAddress("megajdcc2009@gmail.com");
			$mail->isHTML(true);
			$mail->Subject = $this->form['subject'];
			$mail->Body    = $this->email_template($this->form);
			// $mail->AltBody = $body_alt;
			if(!$mail->send()){
				$this->error['error'] = 'Ocurrió un error al intentar contactarnos. Inténtalo nuevamente.'.$mail->ErrorInfo;
				return false;
			}else{
				$_SESSION['notification']['success'] = 'Se ha enviado un correo a nuestro equipo de soporte. Te contactaremos tan pronto nos sea posible. Gracias.';
				header('Location: '.HOST.'/contacto');
				die();
				return true;
			}
		}
		$this->error['warning'] = 'Uno o más campos tienen errores. Verifícalos cuidadosamente.';
		return false;
	}

	private function email_template(array $form){
		$html = 
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contacto</title>
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
											<img alt="Travel Points" src="'.HOST.'/assets/img/LOGOV.png" style="padding-bottom: 0; display: inline !important;width:250px; height:auto">
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
										<strong>'._safe($form['name']).'</strong> ('._safe($form['email']).')
									</td>
								</tr>
								<tr>
									<td class="tablepadding" align="center" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
										'.nl2br(_safe($form['message'])).'
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
							&copy; Info Channel '.date('Y').' Todos los derechos reservados.
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

	private function set_name($string = null){
		if($string){
			$string = trim($string);
			$this->form['name'] = $string;
			return true;
		}else{
			$this->error['name'] = 'Este campo es obligatorio.';
			return false;
		}
	}

	private function set_subject($string = null){
		if($string){
			$string = trim($string);
			$this->form['subject'] = $string;
			return true;
		}else{
			$this->error['subject'] = 'Este campo es obligatorio.';
			return false;
		}
	}

	private function set_email($string = null){
		if($string){
			$this->form['email'] = trim($string);
			$email = filter_var($string, FILTER_VALIDATE_EMAIL);
			if(!$email){
				$this->error['email'] = 'Escribe una dirección de correo electrónico correcta. Ejemplo: usuario@ejemplo.com.';
				return false;
			}else{
				return true;
			}
		}else{
			$this->error['email'] = 'Este campo es obligatorio.';
			return false;
		}
	}

	private function set_message($string = null){
		if($string){
			$string = trim($string);
			$this->form['message'] = $string;
			return true;
		}else{
			$this->error['message'] = 'Este campo es obligatorio.';
			return false;
		}
	}

	public function get_name(){
		return _safe($this->form['name']);
	}

	public function get_name_error(){
		if($this->error['name']){
			return '<p class="text-danger">'._safe($this->error['name']).'</p>';
		}
	}

	public function get_subject(){
		return _safe($this->form['subject']);
	}

	public function get_subject_error(){
		if($this->error['subject']){
			return '<p class="text-danger">'._safe($this->error['subject']).'</p>';
		}
	}

	public function get_email(){
		return _safe($this->form['email']);
	}

	public function get_email_error(){
		if($this->error['email']){
			return '<p class="text-danger">'._safe($this->error['email']).'</p>';
		}
	}

	public function get_message(){
		return _safe($this->form['message']);
	}

	public function get_message_error(){
		if($this->error['message']){
			return '<p class="text-danger">'._safe($this->error['message']).'</p>';
		}
	}

	public function get_notification(){
		$html = null;
		if(isset($_SESSION['notification']['success'])){
			$html .= 
			'<div class="alert alert-success alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<i class="fa fa-check-circle"></i>
				'._safe($_SESSION['notification']['success']).'
			</div>';
			unset($_SESSION['notification']['success']);
		}
		if(isset($_SESSION['notification']['info'])){
			$html .= 
			'<div class="alert alert-info alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<i class="fa fa-exclamation-circle"></i>
				'._safe($_SESSION['notification']['info']).'
			</div>';
			unset($_SESSION['notification']['info']);
		}
		if($this->error['warning']){
			$html .= 
			'<div class="alert alert-warning alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<i class="fa fa-exclamation-triangle"></i>
				'._safe($this->error['warning']).'
			</div>';
		}
		if($this->error['error']){
			$html .= 
			'<div class="alert alert-danger alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<i class="fa fa-exclamation-circle"></i>
				'._safe($this->error['error']).'
			</div>';
		}
		return $html;
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\contact_form.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>