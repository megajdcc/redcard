<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace admin\libs;
use assets\libs\connection;
use PDO;

class member_list {
	private $con;
	private $user = array('id' => null);
	private $members = array();
	private $error = array(
		'warning' => null,
		'error' => null
	);
	private $pagination = array(
		'total' => null,
		'rpp' => null,
		'max' => null,
		'page' => null,
		'offset' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->user['id'] = $_SESSION['user']['id_usuario'];
		return;
	}

	public function load_data($search = null, $page = null, $rpp = null){
		if($search){
			$search_query = "WHERE CONCAT(u.nombre,' ',u.apellido) LIKE :search1 OR u.username LIKE :search2";
		}else{
			$search_query = '';
		}
		$query = "SELECT COUNT(*) FROM usuario u $search_query";
		try{
			$stmt = $this->con->prepare($query);
			if($search){
				$stmt->bindValue(':search1', '%'.$search.'%', PDO::PARAM_STR);
				$stmt->bindValue(':search2', '%'.$search.'%', PDO::PARAM_STR);
			}
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->pagination['total'] = $row['COUNT(*)'];
			$this->pagination['rpp'] = $rpp;
			$this->pagination['max'] = (int)ceil($this->pagination['total'] / $this->pagination['rpp']);
			$this->pagination['page'] = min($this->pagination['max'], $page);
			$this->pagination['offset'] = ($this->pagination['page'] - 1) * $this->pagination['rpp'];
			// Variables retornables
			$pagination['page'] = $this->pagination['page'];
			$pagination['total'] = $this->pagination['total'];
			// Cargar los certificados
			$query = "SELECT u.id_usuario, u.username, u.email, u.esmarties, u.imagen, u.nombre, u.apellido, u.sexo, u.fecha_nacimiento, c.ciudad, p.pais, u.telefono, u.id_rol, u.activo, u.verificado, u.ultimo_login, u.creado
				FROM usuario u
				LEFT JOIN ciudad c ON u.id_ciudad = c.id_ciudad
				LEFT JOIN estado e ON c.id_estado = e.id_estado
				LEFT JOIN pais p ON e.id_pais = p.id_pais
				$search_query
				ORDER BY u.creado ASC
				LIMIT :limit OFFSET :offset";
			try{
				$stmt = $this->con->prepare($query);
				if($search){
					$stmt->bindValue(':search1', '%'.$search.'%', PDO::PARAM_STR);
					$stmt->bindValue(':search2', '%'.$search.'%', PDO::PARAM_STR);
				}
				$stmt->bindValue(':limit', $this->pagination['rpp'], PDO::PARAM_INT);
				$stmt->bindValue(':offset', $this->pagination['offset'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->members[$row['id_usuario']] = array(
					'username' => $row['username'],
					'email' => $row['email'],
					'eSmarties' => $row['esmarties'],
					'image' => $row['imagen'],
					'name' => $row['nombre'],
					'last_name' => $row['apellido'],
					'gender' => $row['sexo'],
					'birthdate' => $row['fecha_nacimiento'],
					'city' => $row['ciudad'],
					'country' => $row['pais'],
					'phone' => $row['telefono'],
					'role_id' => $row['id_rol'],
					'active' => $row['activo'],
					'verified' => $row['verificado'],
					'last_login' => $row['ultimo_login'],
					'created_at' => $row['creado']
				);
			}
		return $pagination;
		}
		return false;
	}

	public function get_members(){
		$members = null;
		foreach ($this->members as $key => $value) {
			if(empty($value['image'])){
				$image = HOST.'/assets/img/user_profile/default.jpg';
			}else{
				$image = HOST.'/assets/img/user_profile/'._safe($value['image']);
			}
			if($value['active'] == 1){
				$status = ' green';
				$btn = '<button class="btn btn-xs btn-danger user-ban" name="ban_user" value="'.$key.'" type="submit"><i class="fa fa-ban m0"></i></button>';
			}elseif($value['active'] == 2){
				$status = ' yellow';
				$btn = '<button class="btn btn-xs btn-danger user-ban" name="ban_user" value="'.$key.'" type="submit"><i class="fa fa-ban m0"></i></button>';
			}else{
				$status = '';
				$btn = '<button class="btn btn-xs btn-success user-ban" name="unban_user" value="'.$key.'" type="submit"><i class="fa fa-check-circle m0"></i></button>';
			}
			if($key == $this->user['id'] ||$_SESSION['user']['id_rol'] == 3){
				$form = '<td></td>';
			}else{
				$form = '<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'">
				<td>'.$btn.'</td>
				</form>';
			}
			if($value['gender'] == 1){
				$gender = 'Hombre';
			}elseif($value['gender'] == 2){
				$gender = 'Mujer';
			}else{
				$gender = '';
			}
			$username = _safe($value['username']);
			$name = _safe($value['name']);
			$last_name = _safe($value['last_name']);
			$email = _safe($value['email']);
			$phone = _safe($value['phone']);
			$date = date('d/m/Y', strtotime($value['created_at']));
			$eSmarties = _safe($value['eSmarties']);
			$birthdate = date('d/m/Y', strtotime($value['birthdate']));
			if(!empty($value['city']) && !empty($value['country'])){
				$location = _safe($value['city'].', '.$value['country']);
			}else{
				$location = '';
			}
			if($value['verified'] == 1){
				$verified = 'S&iacute;';
			}elseif($value['verified'] == 0){
				$verified = 'No';
			}else{
				$verified = '';
			}
			$last_login = date('d/m/Y', strtotime($value['last_login']));
			$members .= 
				'<tr>
					'.$form.'
					<td>'.$key.'</td>
					<td>
						<div class="user user-md">
							<a href="'.HOST.'/socio/'.$username.'" target="_blank"><img src="'.$image.'"></a>
							<div class="notification'.$status.'"></div>
						</div>
					</td>
					<td>'.$username.'</td>
					<td>'.$email.'</td>
					<td>'.$eSmarties.'</td>
					<td><a class="label label-xs label-info" href="'.HOST.'/admin/usuarios/esmartties?socio='.$username.'" target="_blank">Ver historial</a></td>
					<td>'.$name.'</td>
					<td>'.$last_name.'</td>
					<td>'.$gender.'</td>
					<td>'.$birthdate.'</td>
					<td>'.$phone.'</td>
					<td>'.$location.'</td>
					<td>'.$verified.'</td>
					<td>'.$last_login.'</td>
					<td>'.$date.'</td>
				</tr>';
		}
		$html = 
			'<div class="table-responsive">
				<table class="table table-hover">
					<thead>
					<tr>
						<th></th>
						<th>#</th>
						<th>Foto</th>
						<th>Username</th>
						<th>Correo electr&oacute;nico</th>
						<th>eSmartties</th>
						<th></th>
						<th>Nombre</th>
						<th>Apellido</th>
						<th>Sexo</th>
						<th>F.Nacimiento</th>
						<th>Tel&eacute;fono</th>
						<th>Origen</th>
						<th>Verif.</th>
						<th>Ult. Login</th>
						<th>Registrado</th>
					</tr>
					</thead>
					<tbody>
					'.$members.'
					</tbody>
				</table>
			</div>';
		return $html;
	}

	public function ban_user(array $post){
		if($post['ban_user'] == $this->user['id']){
			$this->error['error'] = 'No puedes suspenderte a tí mismo.';
			return false;
		}
		if(!array_key_exists($post['ban_user'], $this->members)){
			$this->error['error'] = 'Error al tratar de suspender al usuario.';
			return false;
		}else{
			$id = (int)$post['ban_user'];
		}
		$query = "UPDATE usuario SET activo = 0 WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $id, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		// SE MANDA LA NOTIFICACION AL USUARIO
		$user_email = $this->members[$id]['email'];
		$username = $this->members[$id]['username'];
		$body_alt = 
			'Tu cuenta '.$username.' ha sido suspendida. Para cualquier aclaración contacta a eSmart Club. soporte@esmartclub.com';
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
		$mail->addAddress($user_email);
		// Hacerlo formato HTML
		$mail->isHTML(true);
		// Formato del correo
		$mail->Subject = 'Tu cuenta ha sido suspendida.';
		$mail->Body    = $this->email_template();
		$mail->AltBody = $body_alt;
		// Enviar
		if(!$mail->send()){
			$_SESSION['notification']['info'] = 'El correo de aviso no se pudo enviar debido a una falla en el servidor.';
		}
		$_SESSION['notification']['success'] = 'Usuario suspendido exitosamente.';
		header('Location: '.HOST.'/admin/usuarios/');
		die();
		return;
	}

	private function email_template(){
		$html = 
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tu cuenta ha sido suspendida</title>
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
										<strong>Lamentamos informarte que tu cuenta ha sido suspendida</strong>
									</td>
								</tr>
								<tr>
									<td class="tablepadding" align="center" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
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

	public function unban_user(array $post){
		if($post['unban_user'] == $this->user['id']){
			$this->error['error'] = 'No puedes quitar tu suspensión a tí mismo.';
			return false;
		}
		if(!array_key_exists($post['unban_user'], $this->members)){
			$this->error['error'] = 'Error al tratar de suspender al usuario.';
			return false;
		}else{
			$id = (int)$post['unban_user'];
		}
		$query = "UPDATE usuario SET activo = 1 WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $id, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$_SESSION['notification']['success'] = 'El usuario ya no está suspendido.';
		header('Location: '.HOST.'/admin/usuarios/');
		die();
		return;
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
		file_put_contents(ROOT.'\assets\error_logs\member_list.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>