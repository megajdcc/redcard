<?php 
namespace Hotel\models;

use assets\libs\connection;
use PDO;

/**
 * 
 * @author Crespo Jhonatan
 * @since 18-04-19
 */
class Comprobantes
{
	
	private $con;
	
	private $hotel = array(
		'id' =>null,
		'nombre'=>null,
		'monto retiro' => null,
		'mensaje' => null,
		'hotel'=>null
	);


	private $promotor = 0 ;
	
	private $comprobantes = array();

	private $error = array('notificacion' => null,
								'fechainicio' => null,
								'fechafin' => null);


	private $preferencias = array(
		'email-notificacion-retiro' =>null,
	);

	function __construct(connection $con){
		$this->con = $con->con;
		if(isset($_SESSION['promotor'])){
			$this->promotor = $_SESSION['promotor']['id'];
			$this->hotel['id'] = $_SESSION['promotor']['hotel'];			
		}else{
			$this->hotel['id'] = $_SESSION['id_hotel'];
		}
	
		$this->cargarComprobantes();
		$this->cargardatoshotel();
		$this->cargarpreferencias();
		return; 

	}

	private function cargarpreferencias(){

		$query = "select * from preferenciasistema";

		$stm=$this->con->prepare($query);
		$stm->execute();

		while ($fila = $stm->fetch(PDO::FETCH_ASSOC)) {
			
			if($fila['preferencia'] == 1){
				$this->preferencias['email-notificacion-retiro'] = $fila['eleccion'];
		}


	}
}
	private function cargardatoshotel(){

		$query = "select h.nombre as hotel from hotel as h 
					where h.id = :hotel";
			$stm = $this->con->prepare($query);
			$stm->execute(array(':hotel'=>$this->hotel['id']));


			$fila = $stm->fetch(PDO::FETCH_ASSOC);
			$this->hotel['nombre'] = $fila['hotel'];
	}

	public function procesarretiro(array $post){

			if($this->con->inTransaction()){
				$this->con->rollBack();
			}

			$this->con->beginTransaction();

			if(isset($_SESSION['promotor'])){
				$query = "INSERT INTO retiro(mensaje,monto,id_promotor) values(:mensaje,:monto,:promotor)";
				
				$datos = array(':mensaje'=>$post['mensaje'],
								':monto'=>number_format((float)$post['monto'],2,'.',','),
								':promotor'=>$this->promotor);
			}else{
				$query = "INSERT INTO retiro(mensaje,id_usuario_solicitud,monto,id_hotel) values(:mensaje,:usuario,:monto,:hotel)";

				$datos = array(':mensaje'=>$post['mensaje'],
								':user'=>$_SESSION['user']['id_usuario'],
								':monto'=>number_format((float)$post['monto'],2,'.',','),
								':promotor'=>$this->hotel['id']);

			}
			
			try {
					$stm = $this->con->prepare($query);
					$stm->execute($datos);
					$last_id = $this->con->lastInsertId();
			} catch (\PDOException $e) {
				$this->error_log(__METHOD__,__LINE__,$e->getMessage());
				$this->con->rollBack();
				return false;
			}

			if(isset($_SESSION['promotor'])){
				$query2 = "insert into retirocomision(negocio,usuario,id_retiro,perfil) value('Retiro de comisión','Retiro de comisión',:idretiro,:perfil)";

				$datos = array(':idretiro'=>$last_id,':perfil'=>5);

			}else{
				$query2 = "insert into retirocomision(negocio,usuario,id_retiro,perfil) value('Retiro de comisión','Retiro de comisión',:idretiro,:perfil)";
					$datos = array(':idretiro'=>$last_id,':perfil'=>1);
			}
			

			try {
				$stm = $this->con->prepare($query2);
				$stm->execute($datos);
				$last_id_retiro = $this->con->lastInsertId();
			} catch (\PDOException $e) {
				$this->error_log(__METHOD__,__LINE__,$e->getMessage());
				$this->con->rollBack();
				return false;
			}
			

			if(isset($_SESSION['promotor'])){
				$query = "SELECT  b.balance as balance
 					from  balance as b 
 				where b.id_promotor = :promotor order by b.id desc limit 1";

 				$datos =array(':promotor'=>$this->promotor);

			}else{
				$query = "SELECT  b.balance as balance
 					from  balance as b 
 				where b.id_hotel = :idhotel order by b.id desc limit 1";

 				$datos =array(':idhotel'=>$this->hotel['id']);

			}
			
				$stm = $this->con->prepare($query);
				$stm->execute($datos);
				$ultimobalance = $stm->fetch(PDO::FETCH_ASSOC)['balance'];
				$balance = $ultimobalance - $post['monto'];

				if(isset($_SESSION['promotor'])){
					$query3 ="insert into balance(balance,id_promotor,comision,id_retiro,perfil) value(:balance,:promotor,:comision,:retiro,:perf)";
					$datos =array(':balance'=>$balance,':promotor'=>$this->promotor,
													':comision'=>'-'.$post['monto'],
													':retiro'=>$last_id_retiro,
													':perf'=>5);
				}else{
					$query3 ="insert into balance(balance,id_hotel,comision,id_retiro,perfil) value(:balance,:hotel,:comision,:retiro,:perf)";
					$datos =array(':balance'=>$balance,':hotel'=>$this->hotel['id'],
													':comision'=>'-'.$post['monto'],
													':retiro'=>$last_id_retiro,
													':perf'=>1);
				}
				
				try {
						$stm = $this->con->prepare($query3);
						$stm->execute($datos);
						$this->con->commit();
				} catch (\PDOException $e) {
						$this->error_log(__METHOD__,__LINE__,$e->getMessage());
						$this->con->rollBack();
						return false;
				}

			if(isset($_SESSION['promotor'])){
				$body_alt ='Has recibido una nueva solicitud de retiro del promotor '.$_SESSION['promotor']['nombre']. ' del Hotel'.$this->getHotel($_SESSION['promotor']['hotel']);
			}else{
				$body_alt ='Has recibido una nueva solicitud de retiro del hotel '.$this->hotel['nombre'];
			}
			
			
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
			$mail->addAddress($this->preferencias['email-notificacion-retiro']);
			// Hacerlo formato HTML
			$mail->isHTML(true);
			// Formato del correo
			
			if(isset($_SESSION['promotor'])){
				$mail->Subject = 'Solicitud de retiro de comisiones de promotor.';
			}else{
				$mail->Subject = 'Solicitud de retiro de comisiones Hotel.';
			}
			
			$mail->Body    = $this->TemplateEmail($post['mensaje'],$monto);
			$mail->AltBody = $body_alt;

			if(!$mail->send()){
				$_SESSION['notificacion']['info'] = 'El correo de aviso no se pudo enviar debido a una falla en el servidor. Intenta solicitando un nuevo correo de confirmación.';
			}

			$_SESSION['notificacion']['success'] = "Se ha realizado la solicitud de retiro con exito. Se le estar&aacute; informando por correo el estado del mismo.";
			header('location: '.HOST.'/Hotel/comprobantes');
			die();
	}


	private function getHotel(int $idhotel){


		$sql ="SELECT nombre from hotel where id = :idhotel";
		$stm  = $this->con->prepare($sql);

		$stm->bindParam(':idhotel',$idhotel,PDO::PARAM_INT);

		$stm->execute();

		return $stm->fetch(PDO::FETCH_ASSOC)['nombre'];

	}

	private function cargarComprobantes(){

		if($this->promotor > 0){

			$query = "select  r.pagado,r.tipo_pago,r.id, r.creado,r.actualizado,aprobado,r.recibo,r.monto,r.id_referidor,
					r.id_franquiciatario,r.id_hotel,CONCAT(p.nombre,' ',p.apellido) as nombre, p.username,r.id_usuario_aprobacion
					from retiro as r join promotor as p on r.id_promotor = p.id
					where p.id = :promotor";

			$datos = array(':promotor'=>$this->promotor);

		}else{

			$query = "select  r.pagado,r.tipo_pago,r.id, r.creado,r.actualizado,aprobado,r.recibo,r.monto,r.id_referidor,
						r.id_franquiciatario,r.id_hotel,CONCAT(u.nombre,' ',u.apellido) as nombre, u.username,r.id_usuario_aprobacion
						from retiro as r join hotel as h on r.id_hotel = h.id
						join usuario as u on  r.id_usuario_solicitud = u.id_usuario
						where h.id = :hotel";
				$datos = array(':hotel'=>$this->hotel['id']);

		}

		$stm = $this->con->prepare($query);
	
		$stm->execute($datos);
		return $this->comprobantes = $stm->fetchAll(PDO::FETCH_ASSOC);

	}

private function getAprobador(int $usuario = null){

	$query = "SELECT u.username, concat(u.nombre,' ',u.apellido) as nombre from usuario as u where u.id_usuario = :usuario";

	$stm = $this->con->prepare($query);
	$stm->execute(array(':usuario'=>$usuario));

	$fila = $stm->fetch(PDO::FETCH_ASSOC);

	if(empty($fila['nombre'])){
		return $fila['username'];
	}else{
		return $fila['nombre'];
	}
}

private function getNombreHotel(){
		return $this->hotel['nombre'];
	}

public function getComprobantes(){


	foreach ($this->comprobantes as $key => $value) {


		if(empty($this->getAprobador($value['id_usuario_aprobacion']))){
			$aprobador = 'Sin aprobar';
		}else{
			$aprobador = $this->getAprobador($value['id_usuario_aprobacion']);
		}



		$this->comprobantes[$key]['aprobador'] =$aprobador;

		$this->comprobantes[$key]['actualizado'] = $this->setFecha($value['actualizado']);
		$this->comprobantes[$key]['monto'] = number_format((float)$value['monto'],2,'.',',');

		//$usuarioaprobador = $this->getUsuario($value['id_usuario_aprobacion']);
		if($value['aprobado']){
			$this->comprobantes[$key]['aprobado'] = "Si";
		}else{
			$this->comprobantes[$key]['aprobado'] = "No";
		}

		$urlrecibo = HOST.'/assets/recibos/'.$value['recibo'];

		$this->comprobantes[$key]['pagado'] = number_format((float)$value['pagado'],2,'.',',');


		$sql = "SELECT * from retiro_mensajes where id_retiro = :retiro";

		$stm = $this->con->prepare($sql);

		$stm->bindParam(':retiro',$value['id']);
		$stm->execute();

		$fila = $stm->fetch(PDO::FETCH_ASSOC);

		$mensaje = false;

		if($stm->rowCount() > 0){
			$mensaje = $fila['mensaje'];
		}

		$btnmensaje = '';

		if($mensaje){

			if($fila['leido'] == 0){
				$classms = 'fa fa-envelope';
			}else{
				$classms = 'fa fa-envelope-open';
			}


			$btnmensaje = '<button class="btn btn-info mensaje" data-idmensaje="'.$fila['id'].'" data-mesaje="'.$mensaje.'" data-toggle="tooltip" title="Mensaje" data-placement="left"><i class="'.$classms.'"></i></button>';

		}

		if($this->comprobantes[$key]['aprobado'] == "Si"){

			$this->comprobantes[$key]['baprobado'] = '<style>.btn-comp{ display: flex;}</style>
								<div class="btn-comp d-flex">
									'.$btnmensaje.'
								<a class=" btn btn-warning archivo" href="'.$urlrecibo.'" target="_blank"><i class="fa fa-file-pdf-o"></i> Descargar</a>
									</div>';
		}else{
			$this->comprobantes[$key]['baprobado'] = '<style>.btn-comp{ display: flex;}</style>
								<div class="btn-comp d-flex">
									'.$btnmensaje.'
									</div>';
		}

	}

	return $this->comprobantes;
}


private function getUsuario(int $usuario = null){

	$query = "select concat(u.nombre,' ',u.apellido) as nombre, u.username from usuario  u 
						where u.id_usuario = :usuario";

	$stm = $this->con->prepare($query);
	$stm->bindParam(':usuario',$usuario,PDO::PARAM_INT);
	$stm->execute();
	$valor = $stm->fetch(PDO::FETCH_ASSOC);

	if(!empty($valor['nombre'])){
		$nombre = $valor['nombre'];
	}else{
		$nombre = $valor['username'];
	}

}

private function setFecha($fecha){

	if($fecha){
		return date('d/m/Y h:i A', strtotime($fecha));
	}else{
		return 'Sin aprobar';
	}
	
}

public function getNotificacion(){

	$notificacion = null;
		if(isset($_SESSION['notificacion']['success'])){
			$notificacion .= 
			'<div class="alert alert-icon alert-dismissible alert-success" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notificacion']['success']).'
			</div>';
			unset($_SESSION['notificacion']['success']);
		}
		if(isset($_SESSION['notificacion']['info'])){
			$notificacion .= 
			'<div class="alert alert-icon alert-dismissible alert-info" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notificacion']['info']).'
			</div>';
			unset($_SESSION['notificacion']['info']);
		}
		if($this->error['notificacion']){
			$notificacion .= 
			'<div class="alert alert-icon alert-dismissible alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->error['notificacion']).'
			</div>';
		}
		return $notificacion;

	
}

public function TemplateEmail($mensaje = null,$monto = null){

  		if($mensaje != null){
  			if(isset($_SESSION['promotor'])){
  				$mensaje = "Mensaje del Promotor de hotel: ".$mensaje ;
  			}else{
  				$mensaje = "Mensaje del Hotel: ".$mensaje ;
  			}
  		}

  		$html = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
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
											<img alt="Travel Points" src="'.HOST.'/assets/img/LOGOV.png" style="padding-bottom: 0; display: inline !important; width:250px;height:auto;">
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
										<strong>Solicitud de Retiro de comisión en Travel Points</strong>
									</td>
								</tr>
								<tr>
									<td class="tablepadding" align="center" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
										Por un monto de $'.$monto.' MXN. Paga el monto correspondiente, aprueba y adjunta recibo de pago en el <a href="'.HOST.'/admin/perfiles/comprobantes" target="_blank">Panel Administrativo</a><br>
											'.$mensaje.'.<br>
										Para cualquier aclaraci&oacute;n contacta a nuestro equipo de soporte.<br>
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
							&copy; Travel Points '.date('Y').' Todos los derechos reservados.
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


	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'/assets/error_logs/comprobanteretirohotel.txt', '['.date('d/M/Y h:i:s A').' on '.$method.' on line '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		return;
	}

}


 ?>