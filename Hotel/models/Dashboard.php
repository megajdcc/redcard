<?php 
namespace Hotel\models;

use assets\libs\connection;
use PDO;


class Dashboard {

	private $con;
	private $user = array('id' => null);
	private $businesses = array();
	private $suspend = array(
		0 => 'Dar de baja',
		1 => 'Activar',
		2 => 'Suspender',
		3 => 'Cerrado por temporada'
	);
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
		try {

			if(isset($_SESSION['user'])){
				$this->user['id'] = $_SESSION['user']['id_usuario'];
			}
			
		} catch (\Exception $e) {
			
		}
		return;
	}

	public function load_data($page = null, $rpp = null){
		$query = "SELECT COUNT(*) FROM negocio";
		try{
			$stmt = $this->con->prepare($query);
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
			$query = "SELECT n.id_negocio, n.nombre, n.url, c.ciudad, p.pais, n.saldo, n.situacion, l.preferencia as logo,
				(SELECT ne.email
				FROM negocio_email ne
				WHERE ne.id_negocio = n.id_negocio
				LIMIT 1) as email,
				(SELECT nt.telefono
				FROM negocio_telefono nt
				WHERE nt.id_negocio = n.id_negocio
				LIMIT 1) as telefono,
				(SELECT nv.creado
				FROM negocio_venta nv
				WHERE nv.id_negocio = n.id_negocio
				ORDER BY creado DESC LIMIT 1) as venta
				FROM negocio n
				INNER JOIN ciudad c ON n.id_ciudad = c.id_ciudad
				INNER JOIN estado e ON c.id_estado = e.id_estado
				INNER JOIN pais p ON e.id_pais = p.id_pais
				INNER JOIN preferencia pr
				INNER JOIN negocio_preferencia l ON l.id_negocio = n.id_negocio AND l.id_preferencia = pr.id_preferencia AND pr.llave = 'business_logo'
				WHERE n.saldo < 0
				ORDER BY n.saldo ASC
				LIMIT :limit OFFSET :offset";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':limit', $this->pagination['rpp'], PDO::PARAM_INT);
				$stmt->bindValue(':offset', $this->pagination['offset'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->businesses[$row['id_negocio']] = array(
					'name' => $row['nombre'],
					'url' => $row['url'],
					'city' => $row['ciudad'],
					'country' => $row['pais'],
					'balance' => $row['saldo'],
					'logo' => $row['logo'],
					'status' => $row['situacion'],
					'email' => $row['email'],
					'phone' => $row['telefono'],
					'last_sale' => $row['venta']
				);
			}
			return $pagination;
		}
		return false;
	}

	public function get_businesses(){
		$members = null;
		foreach ($this->businesses as $key => $value) {
			$image = HOST.'/assets/img/business/logo/'._safe($value['logo']);
			$name = _safe($value['name']);
			$email = _safe($value['email']);
			$url = _safe($value['url']);
			$balance = number_format((float)$value['balance'], 2, '.', '');
			$phone = _safe($value['phone']);
			$date = date('d/m/Y g:i A', strtotime($value['last_sale']));
			if(!empty($value['city']) && !empty($value['country'])){
				$location = _safe($value['city'].', '.$value['country']);
			}else{
				$location = '';
			}
			$members .= 
				'<tr>
					<td>'.$key.'</td>
					<td>'.$this->get_dropdown_options($value['status'], $key).'</td>
					<td><strong class="required">'.$balance.'</strong></td>
					<td>
						<div class="user user-md">
							<a href="'.HOST.'/'.$url.'" target="_blank"><img src="'.$image.'"></a>
						</div>
					</td>
					<td>'.$name.'</td>
					<td>'.$email.'</td>
					<td>'.$phone.'</td>
					<td>'.$location.'</td>
					<td>'.$date.'</td>
				</tr>';
		}
		$html = 
			'<div class="table-responsive">
				<table class="table table-hover table-bordered">
					<thead>
					<tr>
						<th>#</th>
						<th>Situacion</th>
						<th>Saldo</th>
						<th>Logo</th>
						<th>Nombre</th>
						<th>Correo electr&oacute;nico</th>
						<th>N. Telef&oacute;nico</th>
						<th>Origen</th>
						<th>&Uacute;ltima venta</th>
					</tr>
					</thead>
					<tbody>
					'.$members.'
					</tbody>
				</table>
			</div>';
		return $html;
	}

	public function get_businesses_pdf(){
		$query = "SELECT n.id_negocio, n.nombre, c.ciudad, p.pais, n.saldo, n.situacion, l.preferencia as logo,
			(SELECT ne.email
			FROM negocio_email ne
			WHERE ne.id_negocio = n.id_negocio
			LIMIT 1) as email,
			(SELECT nt.telefono
			FROM negocio_telefono nt
			WHERE nt.id_negocio = n.id_negocio
			LIMIT 1) as telefono,
			(SELECT nv.creado
			FROM negocio_venta nv
			WHERE nv.id_negocio = n.id_negocio
			ORDER BY creado DESC LIMIT 1) as venta
			FROM negocio n
			INNER JOIN ciudad c ON n.id_ciudad = c.id_ciudad
			INNER JOIN estado e ON c.id_estado = e.id_estado
			INNER JOIN pais p ON e.id_pais = p.id_pais
			INNER JOIN preferencia pr
			INNER JOIN negocio_preferencia l ON l.id_negocio = n.id_negocio AND l.id_preferencia = pr.id_preferencia AND pr.llave = 'business_logo'
			WHERE n.saldo < 0
			ORDER BY n.saldo ASC";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$rows = null;
		while($row = $stmt->fetch()){
			$id = $row['id_negocio'];
			$name = _safe($row['nombre']);
			if(!empty($row['ciudad']) && !empty($row['pais'])){
				$location = _safe($row['ciudad'].', '.$row['pais']);
			}else{
				$location = '';
			}
			$balance = number_format((float)$row['saldo'], 2, '.', '');
			$image = HOST.'/assets/img/business/logo/'._safe($row['logo']);
			switch ($row['situacion']) {
				case 0:
					$status_tag = 'Baja';
					break;
				case 1:
					$status_tag = 'Activo';
					break;
				case 2:
					$status_tag = 'Suspendido';
					break;
				case 3:
					$status_tag = 'Cerrado por temporada';
					break;
				default:
					$status_tag = '';
					break;
			}
			$email = _safe($row['email']);
			$phone = _safe($row['telefono']);
			$date = date('d/m/Y g:i A', strtotime($row['venta']));
			$rows .= 
				'<tr>
					<td>'.$id.'</td>
					<td>'.$status_tag.'</td>
					<td>'.$balance.'</td>
					<td>'.$name.'</td>
					<td>'.$email.'</td>
					<td>'.$phone.'</td>
					<td>'.$location.'</td>
					<td>'.$date.'</td>
				</tr>';
		}
		$html = 
'<style type="text/css">
	#cabecera{
		background:#f7f8f9;
		padding:10px 20px;
		border-radius: 6px;
	}
	h1,h2{
		float:left;
	}
	table {
		width: 100%;
		border-spacing: 0;
		border-collapse: collapse;
		padding: 8px;
	}
	.table-bordered, th, td{
		border: 1px solid #ddd;
		padding: 5px;
	}

</style>
<page style="font-size: 10px">
	<div id="cabecera">
		<h1>eSmart Club</h1>
		<h2>Reporte de negocios deudores</h2>
	</div>
	<table class="table-bordered">
		<thead>
			<tr>
				<th>#</th>
				<th>Situacion</th>
				<th>Saldo</th>
				<th>Nombre</th>
				<th>Correo electr&oacute;nico</th>
				<th>N. Telef&oacute;nico</th>
				<th>Origen</th>
				<th>&Uacute;ltima venta</th>
			</tr>
		</thead>
		<tbody>
		'.$rows.'
		</tbody>
	</table>
</page>';

		require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/vendor/autoload.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/vendor/spipu/html2pdf/html2pdf.class.php';
		$html2pdf = new \HTML2PDF('P','A4','es');
		$html2pdf->WriteHTML($html);
		$html2pdf->Output('reporte.pdf');
		return;
	}
	public function get_sales_pdf(){
		
		$query = "SELECT iso FROM divisa";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->sales[$row['iso']] = 0;
		}
		$query = "SELECT nv.id_venta, u.username, u.nombre, u.apellido, u.esmarties, p.pais, nv.iso, nv.venta, nv.comision, nv.bono_esmarties, nv.creado, n.nombre as user_negoico, r.username as e_username, r.nombre as e_nombre, r.apellido as e_apellido 
			FROM negocio_venta nv
			INNER JOIN usuario u ON nv.id_usuario = u.id_usuario
			INNER JOIN negocio n ON n.id_negocio = nv.id_negocio
			LEFT JOIN ciudad c ON u.id_ciudad = c.id_ciudad
			LEFT JOIN estado e ON c.id_estado = e.id_estado
			LEFT JOIN pais p ON e.id_pais = p.id_pais
			INNER JOIN usuario r ON nv.id_empleado = r.id_usuario 
			WHERE n.id_negocio = nv.id_negocio
			ORDER BY nv.id_venta ASC";

			
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$count=0;
		$rows = null;
		while($row = $stmt->fetch()){
			$rows .= 
				'<tr>
					<td>'.$count++.'</td>
					<td>'.$row['creado'].'</td>
					<td>'.$row['user_negoico'].'</td>
					<td>'.$row['nombre'].''.$row['apellido'].'</td>
					<td>$'.$row['venta'].'</td>
					<td>$'.$row['comision'].'</td>
					<td>$'.$row['esmarties'].'</td>
				</tr>';
		}
		$html = 
'<style type="text/css">
	#cabecera{
		background:#f7f8f9;
		padding:10px 20px;
		border-radius: 6px;
	}
	h1,h2{
		float:left;
	}
	table {
		width: 100%;
		border-spacing: 0;
		border-collapse: collapse;
		padding: 8px;
	}
	.table-bordered, th, td{
		border: 1px solid #ddd;
		padding: 5px;
	}

</style>
<page style="font-size: 10px">
	<div id="cabecera">
		<h1>eSmart Club</h1>
		<h2>Reporte de negocios deudores</h2>
	</div>
	<table class="table-bordered">
		<thead>
			<tr>
				<th>#</th>
				<th>Feca y hora</th>
				<th>Negocio</th>
				<th>Usario</th>
				<th>Venta</th>
				<th>Correo electr&oacute;nico</th>
				<th>Balance</th>
			</tr>
		</thead>
		<tbody>
		'.$rows.'
		</tbody>
	</table>
</page>';

		require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/vendor/autoload.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/vendor/spipu/html2pdf/html2pdf.class.php';
		$html2pdf = new \HTML2PDF('P','A4','es');
		$html2pdf->WriteHTML($html);
		$html2pdf->Output('reporte.pdf');
		return;
	}

	private function get_dropdown_options($status, $business_id){
		switch ($status) {
			case 0:
				$status_tag = 'Baja';
				$class = 'btn-danger';
				break;
			case 1:
				$status_tag = 'Activo';
				$class = 'btn-success';
				break;
			case 2:
				$status_tag = 'Suspendido';
				$class = 'btn-danger';
				break;
			case 3:
				$status_tag = 'Cerrado por temporada';
				$class = 'btn-warning';
				break;
			default:
				$status_tag = '';
				$class = 'btn-default';
				break;
		}
		$options = null;
		foreach ($this->suspend as $key => $value) {
			if($key == $status){
				continue;
			}
			$options .= 
			'<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'">
				<li><a href="#" class="change-business-status">'.$value.'</a></li>
				<input type="hidden" value="'.$key.'" name="suspend_id">
				<input type="hidden" value="'.$business_id.'" name="business_id">
			</form>';
		}
		$html = 
			'<div class="role-dropdown">
				<div class="dropdown">
					<button class="btn '.$class.' btn-xs dropdown-toggle mimic-header-nav-user-image" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
						<span>'.$status_tag.'</span> <i class="fa fa-chevron-down"></i>
					</button>
					<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
					'.$options.'
					</ul>
				</div><!-- /.dropdown -->
			</div><!-- /.header-nav-user -->';
		return $html;
	}

	public function change_business_status(array $post){
		if(!array_key_exists($post['business_id'], $this->businesses) || !array_key_exists($post['suspend_id'], $this->suspend)){
			$this->error['error'] = 'Error al tratar de actualizar el estado de un negocio.';
			return false;
		}else{
			$business_id = (int)$post['business_id'];
			$suspend_id = (int)$post['suspend_id'];
		}
		$query = "UPDATE negocio SET situacion = :situacion WHERE id_negocio = :id_negocio";
		$params = array(
			':situacion' => $suspend_id,
			':id_negocio' => $business_id
		);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$query = "SELECT ne.email, s.email as email_solicitud
			FROM negocio_email ne 
			INNER JOIN negocio n ON ne.id_negocio = n.id_negocio
			INNER JOIN solicitud_negocio s ON n.id_solicitud = s.id_solicitud 
			WHERE ne.id_negocio = :id_negocio LIMIT 1";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $business_id, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$business_email = $row['email'];
			$request_email = $row['email_solicitud'];
		}
		$business_name = $this->businesses[$business_id]['name'];
		switch ($suspend_id) {
			case 0:
				$title = 'Tu negocio ha sido dado de baja';
				$header = 'Lamentamos informarte que tu negocio "'.$business_name.'" ha sido dado de baja.';
				break;
			case 1:
				$title = 'Tu negocio ha sido reactivado';
				$header = '¡Enhorabuena! Tu negocio "'.$business_name.'" ya está activo.';
				break;
			case 2:
				$title = 'Tu negocio ha sido suspendido';
				$header = 'Lamentamos informarte que tu negocio "'.$business_name.'" ha sido suspendido.';
				break;
			case 3:
				$title = 'Tu negocio ha cerrado por temporada';
				$header = 'Te informarmos que tu negocio "'.$business_name.'" ha sido suspendido temporalmente y etiquetado como cerrado por temporada.';
				break;
			default:
				$title = '';
				$header = '';
				break;
		}
		// SE MANDA LA NOTIFICACION AL USUARIO
		$body_alt = $header.' Para cualquier aclaración contacta a eSmart Club. soporte@esmartclub.com';
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
		$mail->addAddress($business_email);
		if($business_email != $request_email){
			$mail->AddCC($request_email);
		}
		// Hacerlo formato HTML
		$mail->isHTML(true);
		// Formato del correo
		$mail->Subject = $title;
		$mail->Body    = $this->email_template($title, $header);
		$mail->AltBody = $body_alt;
		// Enviar
		if(!$mail->send()){
			$_SESSION['notification']['info'] = 'El correo de aviso no se pudo enviar debido a una falla en el servidor.';
		}
		$_SESSION['notification']['success'] = 'Estado de negocio actualizado exitosamente.';
		header('Location: '.HOST.'/admin/');
		die();
		return;
	}

	private function email_template($title, $header){
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
										<strong>'._safe($header).'</strong>
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
		file_put_contents(ROOT.'\assets\error_logs\business_dashboard.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>