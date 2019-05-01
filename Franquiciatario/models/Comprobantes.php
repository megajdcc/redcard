<?php 
namespace Franquiciatario\models;

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
	);

	private $franquiciatario = array(
		'id' =>null,
		'nombre'=>null,
		'hotel'=>null,
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
		$this->franquiciatario['id'] = $_SESSION['id_franquiciatario'];
		$this->cargarComprobantes();
		$this->cargardatosfranquiciatario();
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
	private function getNombreFranquiciatario(){
		return $this->franquiciatario['nombre'];
	}
	private function cargardatosfranquiciatario(){

		$query = " select concat(u.nombre,' ',u.apellido) as nombre, u.username, h.nombre as hotel
 					from usuario as u 
 					left join solicitudfr as sfr on u.id_usuario = sfr.id_usuario 
 					join franquiciatario as f on sfr.id_franquiciatario = f.id
 					join hotel as h on f.codigo_hotel = h.codigo
 					where f.id = :franquiciatario";
			$stm = $this->con->prepare($query);
			$stm->execute(array(':franquiciatario'=>$this->franquiciatario['id']));


			$fila = $stm->fetch(PDO::FETCH_ASSOC);

			if(empty($fila['nombre'])){
				$this->franquiciatario['nombre'] = $fila['username'];
			}else{
				$this->franquiciatario['nombre'] = $fila['nombre'];
			}

			$this->franquiciatario['hotel'] = $fila['hotel'];
			
	}

	public function procesarretiro(array $post){

			$this->con->beginTransaction();
			$query = "insert into retiro(mensaje,id_usuario_solicitud,monto,id_franquiciatario) values(:mensaje,:usuario,:monto,:franquiciatario)";
			$monto = number_format((float)$post['monto'],2,',','.');
			try {

					$stm = $this->con->prepare($query);
					$stm->execute(array(':mensaje'=>$post['mensaje'],
									':usuario'=>$_SESSION['user']['id_usuario'],
									':monto'=>$post['monto'],
									':franquiciatario'=>$this->franquiciatario['id']));
					$last_id = $this->con->lastInsertId();

			} catch (PDOException $e) {
				
			}

			$query2 = "insert into retirocomisionfranquiciatario(negocio,usuario,id_retiro) value('Retiro de comisión','Retiro de comisión',:idretiro)";

			try {
					$stm = $this->con->prepare($query2);
					$stm->bindParam(':idretiro',$last_id,PDO::PARAM_INT);
					$stm->execute();
					$last_id_retiro = $this->con->lastInsertId();
			} catch (PDOException $e) {
				
			}
			
			$query = "SELECT  bf.balance as balance
 					from  balancefranquiciatario as bf 
 				where bf.id = (select max(id) from balancefranquiciatario)";
				$stm = $this->con->prepare($query);
				$stm->execute();
				$ultimobalance = $stm->fetch(PDO::FETCH_ASSOC)['balance'];
				$balance = $ultimobalance - $post['monto'];

				$query3 ="insert into balancefranquiciatario(balance,id_franquiciatario,comision,id_retiro) value(:balance,:franquiciatario,:comision,:retiro)";
				
				try {
						$stm = $this->con->prepare($query3);
						$stm->execute(array(':balance'=>$balance,':franquiciatario'=>$this->franquiciatario['id'],
													':comision'=>'-'.$post['monto'],
													':retiro'=>$last_id_retiro));
						$this->con->commit();
				} catch (PDOException $e) {
						
				}

				$body_alt ='Has recibido una nueva solicitud de retiro del franquiciatario '.$this->franquiciatario['nombre'];
			
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
			$mail->setFrom('notificacion@esmartclub.com', 'Travel Points');
			// El correo al que se enviará
			$mail->addAddress($this->preferencias['email-notificacion-retiro']);
			// Hacerlo formato HTML
			$mail->isHTML(true);
			// Formato del correo
			$mail->Subject = 'Solicitud de retiro de comisiones del Franquiciatario '.$this->franquiciatario['nombre'];
			$mail->Body    = $this->TemplateEmail($post['mensaje'],$monto);
			$mail->AltBody = $body_alt;

			if(!$mail->send()){
				$_SESSION['notification']['info'] = 'El correo de aviso no se pudo enviar debido a una falla en el servidor. Intenta solicitando un nuevo correo de confirmación.';
			}
			header('location: '.HOST.'/Franquiciatario/comprobantes');
			die();
		
	}


	private function cargarComprobantes(){
		$query = "select  r.id, r.creado,r.actualizado,aprobado,r.recibo,r.monto,r.id_referidor,
					r.id_franquiciatario,r.id_hotel,CONCAT(u.nombre,' ',u.apellido) as nombre, u.username,r.id_usuario_aprobacion
				from retiro as r join franquiciatario as fr on r.id_franquiciatario = fr.id
							join usuario as u on  r.id_usuario_solicitud = u.id_usuario
						where fr.id = :fr";
		$stm = $this->con->prepare($query);
		$stm->bindParam(':fr',$this->franquiciatario['id'],PDO::PARAM_INT);
		$stm->execute();
		return $this->comprobantes = $stm->fetchAll(PDO::FETCH_ASSOC);

	}

public function getComprobantes(){

	foreach ($this->comprobantes as $key => $value) {
		$creado = $this->setFecha($value['creado']);

		$actualizado = $this->setFecha($value['actualizado']);
		$monto = number_format((float)$value['monto'],2,',','.');

		$usuarioaprobador = $this->getUsuario($value['id_usuario_aprobacion']);
		if($value['aprobado']){
			$aprobado = "Si";
		}else{
				$aprobado = "No";
		}
		$urlrecibo = HOST.'/assets/recibos/'.$value['recibo'];
		?>

			
			<tr id="<?php echo $value['id'];?>">
				<td><?php echo '# '.$value['id'];?></td>
				<td><?php echo $creado; ?></td>
				<td><?php echo $actualizado; ?></td>
				<td><?php echo $usuarioaprobador; ?></td>
				<td><?php echo $aprobado; ?></td>
				<td><?php echo $monto; ?></td>
				<td><?php 
						if($aprobado == 'Si'){?>
						<button type="button" data-retiro="<?php echo $value['id']; ?>" class=" btn btn-warning archivo"><i class="fa fa-file-pdf-o"></i> 	<a href="<?php echo $urlrecibo; ?>" target="_blank">Descargar</a></button>
				<?php  }?>
				</td>
			</tr>
		<?php }
}


private function getUsuario(int $usuario= null){

	$query = "select concat(u.nombre,' ',u.apellido) as nombre, u.username from usuario as u 
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

	return $nombre;

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
											<img alt="Travel Points" src="'.HOST.'/assets/img/logo.svg" style="padding-bottom: 0; display: inline !important; width:200px">
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
										<img src="" width="32" height="32" alt="Facebook">
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

}


 ?>