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


	
	private $comprobantes = array();

	private $preferencias = array(
		'email-notificacion-retiro' =>null,
	);

	function __construct(connection $con){
		$this->con = $con->con;
		$this->hotel['id'] = $_SESSION['id_hotel'];
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
			$query = "insert into retiro(mensaje,id_usuario_solicitud,monto,id_hotel) values(:mensaje,:usuario,:monto,:hotel)";
			$monto = number_format((float)$post['monto'],2,'.',',');

			try {
					$stm = $this->con->prepare($query);
					$stm->execute(array(':mensaje'=>$post['mensaje'],
									':usuario'=>$_SESSION['user']['id_usuario'],
									':monto'=>$post['monto'],
									':hotel'=>$this->hotel['id']));
					$last_id = $this->con->lastInsertId();
			} catch (\PDOException $e) {
				$this->error_log(__METHOD__,__LINE__,$e->getMessage());
				$this->con->rollBack();
				return false;
			}

			$query2 = "insert into retirocomision(negocio,usuario,id_retiro) value('Retiro de comisión','Retiro de comisión',:idretiro)";

			try {
				$stm = $this->con->prepare($query2);
				$stm->bindParam(':idretiro',$last_id,PDO::PARAM_INT);
				$stm->execute();
				$last_id_retiro = $this->con->lastInsertId();
			} catch (\PDOException $e) {
				$this->error_log(__METHOD__,__LINE__,$e->getMessage());
				$this->con->rollBack();
				return false;
			}
			
			$query = "SELECT  bh.balance as balance
 					from  balancehotel as bh 
 				where bh.id_hotel = :idhotel order by bh.id desc limit 1";
				$stm = $this->con->prepare($query);
				$stm->execute(array(':idhotel'=>$this->hotel['id']));
				$ultimobalance = $stm->fetch(PDO::FETCH_ASSOC)['balance'];
				$balance = $ultimobalance - $post['monto'];

				$query3 ="insert into balancehotel(balance,id_hotel,comision,id_retiro) value(:balance,:hotel,:comision,:retiro)";
				
				try {
						$stm = $this->con->prepare($query3);
						$stm->execute(array(':balance'=>$balance,':hotel'=>$this->hotel['id'],
													':comision'=>'-'.$post['monto'],
													':retiro'=>$last_id_retiro));
						$this->con->commit();
				} catch (\PDOException $e) {
						$this->error_log(__METHOD__,__LINE__,$e->getMessage());
						$this->con->rollBack();
						return false;
				}

			$body_alt ='Has recibido una nueva solicitud de retiro del hotel '.$this->hotel['nombre'];
			
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
			$mail->Subject = 'Solicitud de retiro de comisiones Hotel.';
			$mail->Body    = $this->TemplateEmail($post['mensaje'],$monto);
			$mail->AltBody = $body_alt;

			if(!$mail->send()){
				$_SESSION['notification']['info'] = 'El correo de aviso no se pudo enviar debido a una falla en el servidor. Intenta solicitando un nuevo correo de confirmación.';
			}
			header('location: '.HOST.'/Hotel/comprobantes');
			die();
	}


	private function cargarComprobantes(){
		$query = "select  r.pagado,r.tipo_pago,r.id, r.creado,r.actualizado,aprobado,r.recibo,r.monto,r.id_referidor,
					r.id_franquiciatario,r.id_hotel,CONCAT(u.nombre,' ',u.apellido) as nombre, u.username,r.id_usuario_aprobacion
				from retiro as r join hotel as h on r.id_hotel = h.id
							join usuario as u on  r.id_usuario_solicitud = u.id_usuario
						where h.id = :hotel";
		$stm = $this->con->prepare($query);
		$stm->bindParam(':hotel',$this->hotel['id'],PDO::PARAM_INT);
		$stm->execute();
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
		$creado = $this->setFecha($value['creado']);

		$usuarioaprobador = $this->getAprobador($value['id_usuario_aprobacion']);
		$actualizado = $this->setFecha($value['actualizado']);
		$monto = number_format((float)$value['monto'],2,'.',',');

		//$usuarioaprobador = $this->getUsuario($value['id_usuario_aprobacion']);
		if($value['aprobado']){
			$aprobado = "Si";
		}else{
				$aprobado = "No";
		}
		$urlrecibo = HOST.'/assets/recibos/'.$value['recibo'];

		$pagado = number_format((float)$value['pagado'],2,'.',',');


			$sql = "SELECT * from retiro_mensajes where id_retiro = :retiro";

			$stm = $this->con->prepare($sql);

			$stm->bindParam(':retiro',$value['id']);
			$stm->execute();

			$fila = $stm->fetch(PDO::FETCH_ASSOC);

			$mensaje = false;
			if($stm->rowCount()){
				$mensaje = $fila['mensaje'];
			}

		?>

			<tr id="<?php echo $value['id'];?>">
				<td><?php echo '# '.$value['id'];?></td>
				<td><?php echo $creado; ?></td>
				<td><?php echo $actualizado; ?></td>
				<td><?php echo $usuarioaprobador; ?></td>
				<td><?php echo $aprobado; ?></td>
				<td><?php echo $monto; ?></td>
				<td><?php echo $pagado;  ?></td>
				<td><?php 
						if($aprobado == 'Si'){?>
								<style >
									.btn-comp{
										display: flex;
									}
								</style>
									<div class="btn-comp d-flex">
											<?php if($mensaje){ ?>
											<button class="btn btn-info mensaje" data-idmesage="<?php echo $fila['id']; ?>" data-mesaje="<?php echo $mensaje;?>" data-toggle="tooltip" title="Mensaje" data-placement="left"><i class="<?php 
											if($fila['leido'] == 0){ echo 'fa fa-envelope';}else{echo 'fa fa-envelope-open';}
											?>"></i></button>
											<?php } ?>
											<button type="button" data-retiro="<?php echo $value['id']; ?>" class=" btn btn-warning archivo"><i class="fa fa-file-pdf-o"></i> 	<a href="<?php echo $urlrecibo; ?>" target="_blank">Descargar</a></button>
									</div>
				<?php  }?>
				</td>
			</tr>
		<?php }
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

	
	}

public function TemplateEmail($mensaje = null,$monto = null){

  		if($mensaje != null){
  			$mensaje = "Mensaje del Hotel: ".$mensaje ;
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