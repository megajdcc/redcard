<?php 
namespace assets\libs;
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';

use PDO;
use \Dompdf\Dompdf as pdf;
use \Dompdf\Options;
use \Dompdf\Positioner;

class product_detail {
	private $con;
	private $user = array(
		'id' => null,
		'esmarties' => null,
		'email' => null,
		'address' => null,
		'postal' => null
	);
	private $product = array();
	private $error = array(
		'address' => null,
		'postal' => null,
		'warning' => null,
		'error' => null
	);

	private $idventa;
	public function __construct(connection $con){
		$this->con = $con->con;
		if(isset($_SESSION['user']['id_usuario'])){
			$this->user['id'] = $_SESSION['user']['id_usuario'];
		}
		return;
	}

	public function load_data($id = null){
		if(empty($id)){
			return false;
		}else{
			$id = _safe($id);
		}
		if($this->user['id']){
			$query = "SELECT esmarties, email, domicilio, codigo_postal FROM usuario WHERE id_usuario = :id_usuario";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				$this->user['esmarties'] = $row['esmarties'];
				$this->user['address'] = $row['domicilio'];
				$this->user['postal'] = $row['codigo_postal'];
				$this->user['email'] = $row['email'];
			}
		}
		$query = "SELECT p.envio,p.id_producto, p.nombre, p.descripcion, p.id_categoria, pc.categoria, p.precio, p.disponibles, p.imagen, p.cupon, (SELECT COUNT(*) FROM venta_tienda vt WHERE p.id_producto = vt.id_producto) as usados 
			FROM producto p
			INNER JOIN producto_categoria pc ON p.id_categoria = pc.id_categoria
			WHERE p.id_producto = :id_producto
			ORDER BY p.creado DESC LIMIT 1";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_producto', $id, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->product['id'] = $row['id_producto'];
			$this->product['name'] = $row['nombre'];
			$this->product['description'] = $row['descripcion'];
			$this->product['category_id'] = $row['id_categoria'];
			$this->product['category'] = $row['categoria'];
			$this->product['price'] = $row['precio'];
			$this->product['available'] = $row['disponibles'];
			$this->product['image'] = $row['imagen'];
			$this->product['coupon'] = $row['cupon'];
			$this->product['used'] = $row['usados'];
			$this->product['envio'] = $row['envio'];
			return true;
		}
		return false;
	}


	public function getNameProduct(){
		return _safe($this->product['name']);
	}


	public function getCertificado(){
		$url = $this->product['coupon'];

		return $url;
	}
	public function getCategoryProduct(){
		return _safe($this->product['category']);
	}

	public function getImageProduct(){
		return $this->product['image'];
	}

	public function getPriceProduct(){
		return number_format((float)$this->product['price'],2,',','.');
	}

	public function comprobante(int $tipo = null){


			if($tipo == 1){
				ob_start();

			require_once($_SERVER['DOCUMENT_ROOT'].'/assets/viewreports/comprobanteventa.php');

		
	
			$html = ob_get_clean();
			$option = new Options();
			$option->isPhpEnabled(true);
			$option->isRemoteEnabled(true);
			$option->setIsHtml5ParserEnabled(true);
			
			$dompdf = new pdf($option);
		
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A5', 'landscape');
			$dompdf->render();

			$dato = array('Attachment' => 0);
			$titulo = "Travel Points: Comprobante de compra de ".$this->product['name'];
			$dompdf->stream($titulo.'.pdf',$dato);

			}else if($tipo == 2){
			
			ob_start();
			require_once($_SERVER['DOCUMENT_ROOT'].'/assets/viewreports/comprobanteventaenvio.php');

		
	
			$html = ob_get_clean();
			$option = new Options();
			$option->isPhpEnabled(true);
			$option->isRemoteEnabled(true);
			$option->setIsHtml5ParserEnabled(true);
			
			$dompdf = new pdf($option);
		
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A5', 'landscape');
			$dompdf->render();

			$dato = array('Attachment' => 0);
			$titulo = "Travel Points: Comprobante de compra de ".$this->product['name'];
			$dompdf->stream($titulo.'.pdf',$dato);

			}else if($tipo == 3 ){
				ob_start();
			require_once($_SERVER['DOCUMENT_ROOT'].'/assets/viewreports/comprobanteventacertificado.php');

		
	
			$html = ob_get_clean();
			$option = new Options();
			$option->isPhpEnabled(true);
			$option->isRemoteEnabled(true);
			$option->setIsHtml5ParserEnabled(true);
			
			$dompdf = new pdf($option);
		
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A5', 'landscape');
			$dompdf->render();

			$dato = array('Attachment' => 0);
			$titulo = "Travel Points: Comprobante de compra de ".$this->product['name'];
			$dompdf->stream($titulo.'.pdf',$dato);
			}
			

			// return true;

	}

	public function setIdVenta($id){
		$this->idventa = $id;
	}

	public function getIdventa(){
		$sql = "select max(id_venta) as id from venta_tienda";
		$stm = $this->con->prepare($sql);
		$stm->execute();
		return $stm->fetch(PDO::FETCH_ASSOC)['id'];
	}

	public function buy_item(array $post){
		if(!$this->user['id'] || $post['buy'] != $this->product['id']){
			$this->error['error'] = 'No se ha podido completar la compra debido a un error.';
			return false;
		}

		if($this->product['price'] > $this->user['esmarties']){
			$this->error['error'] = 'No tienes suficientes Travel Points para comprar este producto.';
			return false;
		}

		if($post['type'] == 1 || $post['type'] == 2 || $post['type'] == 3){
			$id = $post['buy'];
			$type = $post['type'];
			if($type == 3){
				$status = 1;
			}else{
				$status = 0;
			}
			if($type == 2){
				$this->set_address($post['address']);
				$this->set_postal($post['postal']);
			}
		}
		if(!array_filter($this->error)){

			switch ($type) {
				case 1:
					$content = 'Puede pasar a recoger su producto en tienda a partir de ma&ntilde;ana y durante los próximos 7 d&iacute;as naturales.';

					$contentingles = "You can pick up your product in the store from tomorrow and during the next 7 calendar days.";
					break;
				case 2:
					$content = 'Su pedido llegar&aacute; en los pr&oacute;ximos 5 d&iacute;as despu&eacute;s de completar el pago del env&iacute;o.';

					$contentingles = 'Your order will arrive in the next 5 days after completing the payment of the shipment.';
					break;
				case 3:
					$content = 'Se ha adjuntado un certificado en este correo electrónico para utilizar su servicio.';

					$contentingles = 'A certificate has been attached to this email to use your service.';
					break;
				default:
					$content = '';
					break;
			}
			$content = 'Usted ha realizado una compra por concepto de <strong>'.$this->get_name().'</strong> con un cargo de <strong>Tp$'.$this->get_price().'</strong> Travel Points.<br>'.$content;


			$contentingles = 'You have made a purchase for the concept of <strong>'.$this->get_name().'</strong> with a charge of <strong>Tp$'.$this->get_price().'</strong> Travel Points.<br>'.$contentingles;

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
			$mail->addAddress($this->user['email']);
			// Hacerlo formato HTML
			$mail->isHTML(true);
			// Formato del correo
			$mail->Subject = 'Compra realizada exitosamente | Purchase made successfully';
			$mail->Body    = $this->email_template($content,$contentingles);
			if($this->product['category_id'] && $this->product['coupon']){
				$path =  $_SERVER['DOCUMENT_ROOT'].'/assets/img/store/coupon/'.$this->product['coupon'];
				$mail->addAttachment($path);
			}

			if(!$mail->send()){
				$this->error['error'] = 'Ha ocurrido un error en el proceso de compra. Intentalo nuevamente.';
				return;
			}

			$mail2 = new \PHPMailer;
			$mail2->CharSet = 'UTF-8';
			// $mail->SMTPDebug = 3; // CONVERSACION ENTRE CLIENTE Y SERVIDOR
			$mail2->isSMTP();
			$mail2->Host = 'single-5928.banahosting.com';
			$mail2->SMTPAuth = true;
			$mail2->SMTPSecure = 'ssl';
			$mail2->Port = 465;
			// El correo que hará el envío
			$mail2->Username = 'notification@travelpoints.com.mx';
			$mail2->Password = '20464273jd';
			$mail2->setFrom('notification@travelpoints.com.mx', 'Travel Points');
			// El correo al que se enviará
			$mail2->addAddress('soporte@infochannel.si');
			// Hacerlo formato HTML
			$mail2->isHTML(true);
			// Formato del correo
			$mail2->Subject = 'Nueva venta';
			$mail2->Body    = $this->email_template_2();
			$mail2->send();

			if($type==2){
				$query = "UPDATE usuario SET domicilio = :domicilio, codigo_postal = :codigo_postal WHERE id_usuario = :id_usuario";
				$params = array(
					':id_usuario' => $this->user['id'],
					':domicilio' => $this->user['address'],
					':codigo_postal' => $this->user['postal']
				);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
			}
			$query = "INSERT INTO venta_tienda (
				id_producto,
				id_usuario, 
				precio,
				entrega,
				situacion
				) VALUES (
				:id_producto,
				:id_usuario, 
				:precio,
				:entrega,
				:situacion
			)";
			$params = array(
				':id_producto' => $id,
				':id_usuario' => $this->user['id'],
				':precio' => $this->product['price'],
				':entrega' => $type,
				':situacion' => $status
			);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
				$this->setIdVenta($this->con->lastInsertId());
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$query = "SELECT p.disponibles, (SELECT COUNT(*) FROM venta_tienda vt WHERE p.id_producto = vt.id_producto) as usados 
				FROM producto p
				WHERE p.id_producto = :id_producto";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_producto', $id, PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				$left = $row['disponibles'] - $row['usados'];
			}
			if($left <= 0){
				$query = "UPDATE producto SET situacion = 2 WHERE id_producto = :id_producto";
				try{
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(':id_producto', $id, PDO::PARAM_INT);
					$stmt->execute();
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
			}
			$query = "UPDATE usuario SET esmarties = esmarties - :precio WHERE id_usuario = :id_usuario";
			$params = array(
				':precio' => $this->product['price'],
				':id_usuario' => $this->user['id']
			);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			echo $type;

			if($type == 1){
				$_SESSION['notification']['success'] = '!Felicidades! Has comprado <strong>'.$this->product['name'].'</strong> con exito y podrás recogerlo en nuestra tienda ubicada en Marina Vallarta Business Center, Oficina 204. Interior Plaza Marina, en el fraccionamiento Marina Vallarta, Puerto Vallarta. Teléfono (322) 2259635. de lunes a viernes de 9AM a 6PM y los Sabados de 9AM a 2PM. Guarda este comprobante para futuras referencias.
				<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'" target="_blank"> 
				<br>
				<button type="submit" data-path="'._safe($_SERVER['REQUEST_URI']).'" name="emitircomprobante" class="emit-comprobante btn btn-danger"><i class="fa fa-file-pdf-o"></i>Descargar Comprobante</button>
				</form>
				';
			}else if($type == 2){

				$_SESSION['notification']['ventaexitosa'] = true; 
				$_SESSION['notification']['success'] = '!Felicidades! Has comprado <strong>'.$this->product['name'].'</strong> con exito y podrás recogerlo en nuestra tienda ubicada en Marina Vallarta Business Center, Oficina 204. Interior Plaza Marina, en el fraccionamiento Marina Vallarta, Puerto Vallarta. Teléfono (322) 2259635. de lunes a viernes de 9AM a 6PM y los Sabados de 9AM a 2PM. Guarda este comprobante para futuras referencias.
				<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'" target="_blank"> 
				<br>

				<button type="submit" data-path="'._safe($_SERVER['REQUEST_URI']).'" name="emitircomprobante2" class="emit-comprobante btn btn-danger"><i class="fa fa-file-pdf-o"></i>Descargar Comprobante</button>
				</form>';
			}else if($type == 3){
				$_SESSION['notification']['success'] = '!Felicidades! Has comprado <strong>'.$this->product['name'].'</strong> con exito y lo hemos enviado a tu email <strong>'.$this->user['email'].'</strong>.Presenta el certificado digital que te enviamos desde tu dispositivo movil o descarga tu certificado.
				
				<br>

				<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'" target="_blank"> 
				<br>

				<button type="submit" data-path="'._safe($_SERVER['REQUEST_URI']).'" name="emitircomprobante3" class="emit-comprobante btn btn-danger"><i class="fa fa-file-pdf-o"></i>Descargar Certificado</button>
				</form>';
			}
		
		
			//$_SESSION['notification']['success'] = 'Producto comprado exitosamente.';
			header('Location: '._safe($_SERVER['REQUEST_URI']));
			die();
			return;
		}
		$this->error['warning'] = 'Uno o más campos tienen errores. Verifícalos cuidadosamente.';
		return false;
	}

	public function get_id(){
		return _safe($this->product['id']);
	}

	public function get_unsafe_name(){
		return $this->product['name'];
	}

	public function get_name(){
		return _safe($this->product['name']);
	}

	public function get_description(){
		return nl2br(_safe($this->product['description']));
	}

	public function get_category(){
		return _safe($this->product['category']);
	}

	public function get_price(){
		return number_format((float)$this->product['price'], 2, '.', '');
	}

	public function get_available(){
		$available = $this->product['available'] - $this->product['used'];
		return $available;
	}

	public function get_image(){
		return HOST.'/assets/img/store/'._safe($this->product['image']);
	}

	public function get_esmarties(){
		if($this->user['id']){
			return '<div class="background-white p20 mb30"><h4>Mis Travel Points: <span class="text-primary">'._safe($this->user['esmarties']).'</span></h4></div>';
		}
	}

	public function get_address(){
		return _safe($this->user['address']);
	}

	public function get_address_error(){
		return '<p class="text-danger">'._safe($this->error['address']).'</p>';
	}

	public function get_postal(){
		return _safe($this->user['postal']);
	}

	public function get_postal_error(){
		return '<p class="text-danger">'._safe($this->error['postal']).'</p>';
	}


	public function getPrecioEnvio(){

		$precio = '$0,00';

		if($this->product['envio']){
			$precio = '$'.number_format((float)$this->product['envio'],2,',','.').'MXN';

		}
		return $precio;
	}

	public function get_buy_button(){
		if($this->user['id']){
			if($this->product['category_id'] == 1){
				$html =
				'<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'">
					<button class="btn btn-primary" type="button" data-toggle="modal" data-target="#myModal">¡Comprar!</button>
				</form>
				<!-- Modal -->
				<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
					<div class="modal-dialog modal-lg" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title" id="myModalLabel">Seleccionar tipo de env&iacute;o</h4>
							</div>
							<div class="modal-body">
								<div class="mb50">
									<strong>Adquirir producto en tienda</strong>
									<p>
										El producto puede ser recogido en la tienda sin costo alguno.
									</p>
									<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'">
										<input type="hidden" name="type" value="1">
										<button type="submit" class="btn btn-success buy-product" name="buy" value="'.$this->product['id'].'">Comprar y recoger en tienda</button>
									</form>
								</div>
								<div class="mb30">
									<strong>Enviar por paqueter&iacute;a privada</strong>.
									<p>
										El producto puede ser enviado por paqueter&iacute;a privada con un costo adicional. Necesitamos tu domicilio y c&oacute;digo postal para realizar el env&iacute;o.
									</p>
									<p class="text-danger">
										Una vez seleccionada esta opci&oacute;n, deber&aacute;s realizar el pago del env&iacute;o en el siguiente paso.
									</p>
									<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'">
										<div class="form-group">
											<label for="address">Direcci&oacute;n <span class="required">*</span></label>
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-map-o"></i></span>
												<input class="form-control" type="text" id="address" name="address" value="'.$this->get_address().'" placeholder="Direcci&oacute;n" required>
											</div><!-- /.input-group -->
											'.$this->get_address_error().'
										</div>
										<div class="form-group">
											<label for="postal-code">C&oacute;digo postal <span class="required">*</span></label>
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
												<input class="form-control" type="text" id="postal-code" name="postal" value="'.$this->get_postal().'" placeholder="C&oacute;digo postal" required>
											</div><!-- /.input-group -->
											'.$this->get_postal_error().'
										</div><!-- /.form-group -->
										<input type="hidden" name="type" value="2">
										<button type="submit" class="btn btn-primary buy-product" name="buy" value="'.$this->product['id'].'">Comprar y pedir env&iacute;o por paqueter&iacute;a</button>
									</form>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
							</div>
						</div>
					</div>
				</div>';
			}elseif($this->product['category_id'] == 2){
				$html =
				'<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'">
					<input type="hidden" name="type" value="3">
					<button class="btn btn-primary buy-product" type="submit" name="buy" value="'.$this->product['id'].'">¡Comprar!</button>
				</form>';
			}
		}else{
			$html = 
				'<a class="btn btn-primary" href="'.HOST.'/login">Debes iniciar sesi&oacute;n para comprar un producto.</a>';
		}
		return $html;
	}

	private function email_template($content,$contentingles){
		$fecha = date('Y');
		$html = 
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Compra realizada exitosamente</title>
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
					<td class="tablepadding" style="color: #444; padding:20px; font-size:14px; line-height:20px; border-top-width:1px; border-top-style:solid; border-top-color:#ececec;">
						<table border="0" cellpadding="0" cellspacing="0" width="100%">
							<tbody>
								<tr>
									<td align="center" class="tablepadding" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
										<strong>Compra realizada exitosamente |  Purchase made successfully</strong>
									</td>
								</tr>
								<tr>
									<td class="tablepadding" align="center" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
										'.$content.'<br>
										Para cualquier aclaraci&oacute;n contacta a nuestro equipo de soporte.<br>
										<a style="outline:none; color:#0082b7; text-decoration:none;" href="mailto:soporte@infochannel.si">
											soporte@infochannel.si
										</a>
									</td>
								</tr>
								<tr>
									<td class="tablepadding" align="center" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
										'.$contentingles.'<br>
										For any clarification please contact our support team.<br>
										<a style="outline:none; color:#0082b7; text-decoration:none;" href="mailto:soporte@infochannel.si">
											soporte@infochannel.si
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
										Travel Points <br>
										Marina Vallarta Business Center, Oficina 204, Interior Plaza Marina.<br>
										Fraccionamiento Marina Vallarta, Puerto Vallarta Mexico<br>
										
										Tel:(322) 225 9635<br>
										Lunes a Viernes de 9AM a 6PM y Sabados de 9AM a 2PM.<br>
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
							&copy; Travel Points '.$fecha.' Todos los derechos reservados.
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

	private function email_template_2(){

		$fecha = date('Y');
		$html = 
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nueva compra en la tienda</title>
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
											<img alt="Travel Points" src="'.HOST.'/assets/img/LOGOV.png" style="padding-bottom: 0; display: inline !important;width:250px; height:auto;">
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
										<strong>Se realiz&oacute; una nueva compra en la tienda de Travel Points</strong>
									</td>
								</tr>
								<tr>
									<td class="tablepadding" align="center" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
										Puedes revisar la venta <a style="outline:none; color:#0082b7; text-decoration:none;" href="'.HOST.'/admin/tienda/ventas">
											haciendo clic aqu&iacute;
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
							&copy; Travel Points '.$fecha.' Todos los derechos reservados.
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


	public function recoger(){


		$sql = "select max(id_venta) as id from venta_tienda where id_usuario = :usuario";

		try {
			$stm = $this->con->prepare($sql);
			$stm->bindParam(':usuario',$_SESSION['user']['id_usuario']);
			$stm->execute();
		} catch (PDOException $e) {
			$this->error_log(__METHOD__,__LINE__,$e->getMessage());
		}


		$idventa = $stm->fetch(PDO::FETCH_ASSOC)['id'];


		$sql2 = "update venta_tienda set entrega = :entrega where id_venta=:venta";

		try {
			$stm = $this->con->prepare($sql2);

			$stm->execute(array(':entrega'=>1,
								':venta'=>$idventa));


		} catch (PDOException $ex) {
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
		}

		$content = '!Has decidido recogerlo en tienda!, Puedes pasar a recoger su producto en tienda a partir de ma&ntilde;ana y durante los próximos 7 d&iacute;as naturales.';

		$contentingles = '! You have decided to pick it up in store! You can pick up your product in the store after the publication of the information for the next 7 days.';


		$content = 'Usted ha realizado una compra por concepto de <strong>'.$this->get_name().'</strong> con un cargo de <strong>Tp$'.$this->get_price().'</strong> Travel Points.<br>'.$content;

		$contentingles = 'You have made a purchase for the concept of <strong>'.$this->get_name().'</strong> with a charge of <strong>Tp$'.$this->get_price().'</strong> Travel Points.<br>'.$contentingles;
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
			$mail->addAddress($this->user['email']);
			// Hacerlo formato HTML
			$mail->isHTML(true);
			// Formato del correo
			$mail->Subject = 'Compra realizada exitosamente';
			$mail->Body    = $this->email_template($content,$contentingles);
			if($this->product['category_id'] && $this->product['coupon']){
				$path = ROOT.'\assets\img\store\coupon\.'.$this->product['coupon'];
				$mail->addAttachment($path);
			}

			if(!$mail->send()){
				$this->error['error'] = 'Ha ocurrido un error en el proceso de compra. Intentalo nuevamente.';
				return;
			}


			$_SESSION['notification']['success'] = '!Felicidades! Has comprado <strong>'.$this->product['name'].'</strong> con exito y podrás recogerlo en nuestra tienda ubicada en Marina Vallarta Business Center, Oficina 204. Interior Plaza Marina, en el fraccionamiento Marina Vallarta, Puerto Vallarta. Teléfono (322) 2259635. de lunes a viernes de 9AM a 6PM y los Sabados de 9AM a 2PM. Guarda este comprobante para futuras referencias.
				<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'"> 
				<br>
				<button type="submit" data-path="'._safe($_SERVER['REQUEST_URI']).'" name="emitircomprobante" class="emit-comprobante btn btn-danger"><i></i>Descargar Comprobante</button>
				</form>
				';

			header('Location: '._safe($_SERVER['REQUEST_URI']));
			die();
			return;

	}

	private function set_address($string = null){
		if($string){
			$string = trim($string);
			$this->user['address'] = $string;
			return true;
		}
		$this->error['address'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_postal($string = null){
		if($string){
			$string = trim($string);
			$this->user['postal'] = $string;
			return true;
		}
		$this->error['postal'] = 'Este campo es obligatorio.';
		return false;
	}

	public function get_notification(){
		$html = null;

		if(isset($_SESSION['notification']['success'])){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-success" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'.$_SESSION['notification']['success'].'
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
		file_put_contents(ROOT.'\assets\error_logs\product_detail.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>