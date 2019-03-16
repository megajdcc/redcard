<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace assets\libs;
use PDO;

class affiliate_business {
	private $con;
	private $reserved_words = array(
		'admin',
		'assets',
		'errors',
		'errores',
		'business',
		'negocio',
		'member',
		'socio',
		'tienda',
		'store',
		'affiliate-business',
		'afiliar-negocio',
		'ajax',
		'change-password',
		'cambiar-contrasena',
		'certificate',
		'certificado',
		'contact-us',
		'contacto',
		'hazte-socio',
		'signup',
		'index',
		'home',
		'listings',
		'listados',
		'login',
		'logout',
		'negocio_certificados',
		'negocio_eventos',
		'negocio_opiniones',
		'negocio_publicaciones',
		'about-us',
		'nosotros',
		'perfil_negocio',
		'perfil_socio',
		'faq',
		'preguntas-frecuentes',
		'what-is-esmart-club',
		'que-es-esmart-club',
		'recover-account',
		'recuperar-cuenta',
		'terms-of-service',
		'terminos-y-condiciones'
	);
	private $register = array(
		'user_id' => null,
		'name' => null,
		'description' => null,
		'brief' => null,
		'category_id' => null,
		'commission' => 6,
		'url' => null,
		'email' => null,
		'phone' => null,
		'website' => null,
		'address' => null,
		'postal_code' => null,
		'city_id' => null,
		'state_id' => null,
		'country_id' => null,
		'latitude' => null,
		'longitude' => null,
		'logo' => array('tmp' => null, 'name' => null, 'path' => null),
		'photo' => array('tmp' => null, 'name' => null, 'path' => null)
	);
	private $error = array(
		'name' => null,
		'description' => null,
		'brief' => null,
		'category' => null,
		'commission' => null,
		'url' => null,
		'email' => null,
		'phone' => null,
		'website' => null,
		'address' => null,
		'postal_code' => null,
		'city' => null,
		'location' => null,
		'logo' => null,
		'photo' => null,
		'warning' => null,
		'error' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->register['user_id'] = $_SESSION['user']['id_usuario'];
		return;
	}

	public function set_data(array $post, array $files){
		$this->set_name($post['name']);
		$this->set_description($post['description']);
		$this->set_brief($post['brief']);
		$this->set_category_id($post['category_id']);
		$this->set_commission($post['commission']);
		$this->set_url($post['url']);
		$this->set_email($post['email']);
		$this->set_phone($post['phone']);
		$this->set_website($post['website']);
		$this->set_address($post['address']);
		$this->set_postal_code($post['postal_code']);
		$this->set_city_id($post['city_id']);
		$this->set_state_id($post['state_id']);
		$this->set_country_id($post['country_id']);
		$this->set_location($post['latitude'], $post['longitude']);
		$this->set_logo($files);
		$this->set_photo($files);
		if(!array_filter($this->error)){
			$this->register_business();
			return true;
		}
		$this->error['warning'] = 'Uno o más campos tienen errores. Verifícalos cuidadosamente.';
		return false;
	}

	private function register_business(){
		$query = "INSERT INTO solicitud_negocio (
			id_usuario, 
			nombre, 
			descripcion, 
			breve, 
			id_categoria, 
			comision, 
			url, 
			email, 
			telefono, 
			sitio_web, 
			direccion, 
			codigo_postal, 
			id_ciudad, 
			latitud, 
			longitud, 
			logo, 
			foto
			) VALUES (
			:id_usuario, 
			:nombre, 
			:descripcion, 
			:breve, 
			:id_categoria, 
			:comision, 
			:url, 
			:email, 
			:telefono, 
			:sitio_web, 
			:direccion, 
			:codigo_postal, 
			:id_ciudad, 
			:latitud, 
			:longitud, 
			:logo, 
			:foto
		)";
		$query_params = array(
			':id_usuario' => $this->register['user_id'],
			':nombre' => $this->register['name'],
			':descripcion' => $this->register['description'],
			':breve' => $this->register['brief'],
			':id_categoria' => $this->register['category_id'],
			':comision' => $this->register['commission'],
			':url' => $this->register['url'],
			':email' => $this->register['email'],
			':telefono' => $this->register['phone'],
			':sitio_web' => $this->register['website'],
			':direccion' => $this->register['address'],
			':codigo_postal' => $this->register['postal_code'],
			':id_ciudad' => $this->register['city_id'],
			':latitud' => $this->register['latitude'],
			':longitud' => $this->register['longitude'],
			':logo' => $this->register['logo']['name'],
			':foto' => $this->register['photo']['name']
		);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($query_params);
			$last_id = $this->con->lastInsertId();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if(
			move_uploaded_file($this->register['logo']['tmp'], $this->register['logo']['path']) &&
			move_uploaded_file($this->register['photo']['tmp'], $this->register['photo']['path'])
		){
			$content = 'Se ha recibido una nueva solicitud para afiliar un negocio. <a style="outline:none; color:#0082b7; text-decoration:none;" href="'.HOST.'/admin/negocios/solicitud/'.$last_id.'">Haz clic aqu&iacute; para verla</a>.';
			$body_alt =
				'Se ha recibido una nueva solicitud para afiliar negocio. Sigue este enlace para verla: '.HOST.'/admin/negocios/solicitud/'.$last_id;
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
			$mail->addAddress('soporte@esmartclub.com');
			// Hacerlo formato HTML
			$mail->isHTML(true);
			// Formato del correo
			$mail->Subject = 'Nueva solicitud de negocio';
			$mail->Body    = $this->email_template($content);
			$mail->AltBody = $body_alt;

			$mail->send();

			$_SESSION['notification']['success'] = 'Se ha enviado la solicitud para afiliar tu negocio exitosamente. Te mantendremos informado de cualquier avance.';
			header('Location: '.HOST.'/socio/negocios/solicitudes');
			die();
		}
		$this->error['error'] = 'Estamos teniendo problemas técnicos, disculpa las molestias. Intenta más tarde.';
		return false;
	}

	private function email_template($content){
		$html = 
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nueva solicitud de negocio</title>
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
										<strong>Nueva solicitud de negocio</strong>
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


	private function set_name($string = null){
		if($string){
			$string = trim($string);
			$this->register['name'] = $string;
			return true;
		}
		$this->error['name'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_description($string = null){
		if($string){
			$string = trim($string);
			$this->register['description'] = $string;
			return true;
		}
		$this->error['description'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_brief($string = null){
		if($string){
			$this->register['brief'] = trim($string);
			if(strlen($string) > 60){
				$this->error['brief'] = 'La descripción corta no debe exceder los 60 caracteres.';
				return false;
			}
			return true;
		}
		$this->error['brief'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_category_id($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				$this->error['category'] = 'Selecciona una categoría.';
				return false;
			}
			$this->register['category_id'] = $string;
			return true;
		}
		$this->error['category'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_commission($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 6 || $string > 100){
				$this->error['commission'] = 'Ingresa un número entero entre 6 y 100.';
				return false;
			}
			$this->register['commission'] = $string;
			return true;
		}
		$this->error['commission'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_url($string = null){
		if($string){
			$string = strtolower(trim($string));
			if(in_array($string,$this->reserved_words)){
				$this->error['url'] = 'La url del negocio no puede ser "'._safe($string).'", la cual es una palabra reservada.';
			}
			if(!preg_match('/^[a-z0-9-]+$/ui',$string)){
				$this->error['url'] = 'La url del negocio solo debe contener letras, números y guiones. No se permiten acentos, caracteres especiales o espacios.';
			}
			$this->register['url'] = $string;
			return;
		}
		$this->error['url'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_email($string = null){
		if($string){
			$email = filter_var($string, FILTER_VALIDATE_EMAIL);
			if(!$email){
				$this->error['email'] = 'Escribe una dirección de correo electrónico correcta. Ejemplo: usuario@ejemplo.com.';
				$this->register['email'] = $string;
				return false;
			}
			$this->register['email'] = $email;
			return true;
		}
		$this->error['email'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_phone($string = null){
		if($string){
			$string = trim($string);
			if(!preg_match('/^[0-9() +-]+$/ui',$string)){
				$this->error['phone'] = 'Escribe un número telefónico correcto. Ejemplo: (123) 456-78-90.';
				$this->register['phone'] = $string;
				return false;
			}
			$this->register['phone'] = $string;
			return true;
		}
		$this->error['phone'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_website($string = null){
		if($string){
			if(!preg_match('_^(?:(?:https?|ftp)://)?(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,}))\.?)(?::\d{2,5})?(?:[/?#]\S*)?$_iuS',$string)){
				$this->error['website'] = 'Escribe un enlace correcto. Ejemplo: www.esmartclub.com o http://esmartclub.com';
				$this->register['website'] = $string;
				return false;
			}
			if(!preg_match("@^https?://@", $string)){
				$this->register['website'] = 'http://'.$string;
			}else{
				$this->register['website'] = $string;
			}
		}
		return true;
	}

	private function set_address($string = null){
		if($string){
			$string = trim($string);
			$this->register['address'] = $string;
			return true;
		}
		$this->error['address'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_postal_code($string = null){
		if($string){
			$string = trim($string);
			$this->register['postal_code'] = $string;
			return true;
		}
		$this->error['postal_code'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_city_id($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				$this->error['city'] = 'Selecciona una ciudad.';
				return false;
			}
			$this->register['city_id'] = $string;
			return true;
		}
		$this->error['city'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_state_id($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				return false;
			}
			$this->register['state_id'] = $string;
			return true;
		}
		return false;
	}

	private function set_country_id($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				return false;
			}
			$this->register['country_id'] = $string;
			return true;
		}
		return false;
	}

	private function set_location($lat = null, $lon = null){
		if($lat & $lon){
			if(!filter_var($lat, FILTER_VALIDATE_FLOAT) || !filter_var($lon, FILTER_VALIDATE_FLOAT)){
				$this->error['location'] = 'Utiliza el marcador del mapa para ubicar tu negocio.';
				return false;
			}else{
				$this->register['latitude'] = trim($lat);
				$this->register['longitude'] = trim($lon);
				return true;
			}
		}
		$this->error['location'] = 'Es obligatorio ubicar tu negocio en el mapa.';
		return false;
	}

	private function set_logo($files = null){
		$image = new \assets\libraries\bulletproof\bulletproof($files);
		$image->setLocation(ROOT.'/assets/img/business_request');
		if($image['logo']){
			if($image->upload()){
				$this->register['logo']['tmp'] = $files['logo']['tmp_name'];
				$this->register['logo']['name'] = $image->getName().'.'.$image->getMime();
				$this->register['logo']['path'] = $image->getFullPath();
				return true;
			}
			$this->error['logo'] = $image['error'];
			return false;
		}
		if($files['logo']['error'] == 1){
			$this->error['logo'] = 'Has excedido el límite de imagen de 2MB.';
		}else{
			$this->error['logo'] = 'Este campo es obligatorio.';
		}
		return false;
	}

	private function set_photo($files = null){
		$image = new \assets\libraries\bulletproof\bulletproof($files);
		$image->setLocation(ROOT.'/assets/img/business_request');
		if($image['photo']){
			if($image->upload()){
				$this->register['photo']['tmp'] = $files['photo']['tmp_name'];
				$this->register['photo']['name'] = $image->getName().'.'.$image->getMime();
				$this->register['photo']['path'] = $image->getFullPath();
				return true;
			}
			$this->error['photo'] = $image['error'];
			return false;
		}
		if($files['photo']['error'] == 1){
			$this->error['photo'] = 'Has excedido el límite de imagen de 2MB.';
		}else{
			$this->error['photo'] = 'Este campo es obligatorio.';
		}
		return false;
	}

	public function get_name(){
		return _safe($this->register['name']);
	}

	public function get_name_error(){
		if($this->error['name']){
			$error = '<p class="text-danger">'._safe($this->error['name']).'</p>';
			return $error;
		}
	}

	public function get_description(){
		return _safe($this->register['description']);
	}

	public function get_description_error(){
		if($this->error['description']){
			$error = '<p class="text-danger">'._safe($this->error['description']).'</p>';
			return $error;
		}
	}

	public function get_brief(){
		return _safe($this->register['brief']);
	}

	public function get_brief_error(){
		if($this->error['brief']){
			$error = '<p class="text-danger">'._safe($this->error['brief']).'</p>';
			return $error;
		}
	}

	public function get_categories(){
		$categories = null;
		$query = "SELECT id_categoria, categoria FROM negocio_categoria";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$category = _safe($row['categoria']);
			if($this->register['category_id'] == $row['id_categoria']){
				$categories .= '<option value="'.$row['id_categoria'].'" selected>'.$category.'</option>';
			}else{
				$categories .= '<option value="'.$row['id_categoria'].'">'.$category.'</option>';
			}
		}
		return $categories;
	}

	public function get_category_error(){
		if($this->error['category']){
			$error = '<p class="text-danger">'._safe($this->error['category']).'</p>';
			return $error;
		}
	}

	public function get_commission(){
		return _safe($this->register['commission']);
	}

	public function get_commission_error(){
		if($this->error['commission']){
			$error = '<p class="text-danger">'._safe($this->error['commission']).'</p>';
			return $error;
		}
	}

	public function get_url(){
		return _safe($this->register['url']);
	}

	public function get_url_error(){
		if($this->error['url']){
			$error = '<p class="text-danger">'._safe($this->error['url']).'</p>';
			return $error;
		}
	}

	public function get_email(){
		return _safe($this->register['email']);
	}

	public function get_email_error(){
		if($this->error['email']){
			$error = '<p class="text-danger">'._safe($this->error['email']).'</p>';
			return $error;
		}
	}

	public function get_phone(){
		return _safe($this->register['phone']);
	}

	public function get_phone_error(){
		if($this->error['phone']){
			$error = '<p class="text-danger">'._safe($this->error['phone']).'</p>';
			return $error;
		}
	}

	public function get_website(){
		return _safe($this->register['website']);
	}

	public function get_website_error(){
		if($this->error['website']){
			$error = '<p class="text-danger">'._safe($this->error['website']).'</p>';
			return $error;
		}
	}

	public function get_address(){
		return _safe($this->register['address']);
	}

	public function get_address_error(){
		if($this->error['address']){
			$error = '<p class="text-danger">'._safe($this->error['address']).'</p>';
			return $error;
		}
	}

	public function get_postal_code(){
		return _safe($this->register['postal_code']);
	}

	public function get_postal_code_error(){
		if($this->error['postal_code']){
			$error = '<p class="text-danger">'._safe($this->error['postal_code']).'</p>';
			return $error;
		}
	}

	public function get_countries(){
		$countries = null;
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
			if($this->register['country_id'] == $row['id_pais']){
				$countries .= '<option value="'.$row['id_pais'].'" selected>'.$country.'</option>';
			}else{
				$countries .= '<option value="'.$row['id_pais'].'">'.$country.'</option>';
			}
		}
		return $countries;
	}

	public function get_states(){
		$states = null;
		if($this->register['country_id']){
			$query = "SELECT id_estado, estado FROM estado WHERE id_pais = :id_pais";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_pais', $this->register['country_id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$state = _safe($row['estado']);
				if($this->register['state_id'] == $row['id_estado']){
					$states .= '<option value="'.$row['id_estado'].'" selected>'.$state.'</option>';
				}else{
					$states .= '<option value="'.$row['id_estado'].'">'.$state.'</option>';
				}
			}
		}
		return $states;
	}

	public function get_cities(){
		$cities = null;
		if($this->register['state_id']){
			$query = "SELECT id_ciudad, ciudad FROM ciudad WHERE id_estado = :id_estado";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_estado', $this->register['state_id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$city = _safe($row['ciudad']);
				if($this->register['city_id'] == $row['id_ciudad']){
					$cities.= '<option value="'.$row['id_ciudad'].'" selected>'.$city.'</option>';
				}else{
					$cities.= '<option value="'.$row['id_ciudad'].'">'.$city.'</option>';
				}
			}
		}
		return $cities;
	}

	public function get_city_error(){
		if($this->error['city']){
			$error = '<p class="text-danger">'._safe($this->error['city']).'</p>';
			return $error;
		}
	}

	public function get_latitude(){
		return _safe($this->register['latitude']);
	}

	public function get_longitude(){
		return _safe($this->register['longitude']);
	}

	public function get_location_error(){
		if($this->error['location']){
			return '<p class="text-danger">'._safe($this->error['location']).'</p>';
		}
	}

	public function get_logo_error(){
		if($this->error['logo']){
			$error = '<p class="text-danger">'._safe($this->error['logo']).'</p>';
			return $error;
		}
	}

	public function get_photo_error(){
		if($this->error['photo']){
			$error = '<p class="text-danger">'._safe($this->error['photo']).'</p>';
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
		file_put_contents(ROOT.'\assets\error_logs\affiliate_business.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
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