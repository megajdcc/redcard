<?php
/**
 * @author Crespo jhonatan 
 * @since 04/05/2019
 */

namespace Franquiciatario\models;

use PDO;

class AfiliarFranquiciatario {
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
		'Afiliar-franquiciatario',
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
	private $registrar = array(
		'id_usuario'           =>null,
		'codigohotel'          =>null,
		'banco'                => null,
		'cuenta'               => null,
		'clabe'                => null,
		'swift'                => null,
		'banco_tarjeta'        => null,
		'numero_tarjeta'       => null,
		'email_paypal'         => null,
		'id_datospagocomision' => null,
		'id_franquiciatario'   => null,
		'email'                => null,
		'telefonofijo'         => null,
		'telefonomovil'        => null,
		'comision'             => null,
		'aprobada'             => null,
		'nrosolicitud'         => null,
		'condicion'            => null,
		'comentario'           => null,


	
		//Datos del hotel
		'sitio_web'            => null,
		'nombrehotel'          => null,
		'direccion'            => null,
		'codigopostal'         => null,
		'id_ciudad'            => null,
		'id_iata'              => null,
		'id_estado'            => null,
		
		//Datos de contacto
		
		'nombre'               =>null,
		'apellido'             =>null,
		'emailfranquiciatario' =>null


	);
	private $error = array(
		'codigohotel'          =>null,
		'banco'                => null,
		'cuenta'               => null,
		'clabe'                => null,
		'swift'                => null,
		
		'banco_tarjeta'        => null,
		'numero_tarjeta'       => null,
		'email_paypal'         => null,
		'id_datospagocomision' => null,
		'id_franquiciatario'   => null,
		'email'                => null,
		'telefonofijo'         => null,
		'telefonomovil'        => 0,
		'comision'             => 0,
		'aprobada'             => null,
		'nrosolicitud'         => null,
		'condicion'            => null,
		'comentario'           => null,
		'warning'              => null,
		'error'                => null,
		'nombre'               => null,
		'apellido'             => null,
		
		//Datos del hotel
		'sitio_web'            => null,
		'nombrehotel'          => null,
		'direccion'            => null,
		'codigopostal'         => null,
		'id_ciudad'            => null,
		'id_iata'              => null,
		'id_estado'            => null,
		
		//Datos de contacto
		
		'nombre'               =>null,
		'apellido'             =>null,
		'emailfranquiciatario' =>null,


	);


	private $solicitante = 0;
	private $registropago = false;
	public $ultimohotel = 0;

	public function __construct($con){
		$this->con = $con->con;
		$this->registrar['id_usuario'] = $_SESSION['user']['id_usuario'];
	
	}

	public function set_data(array $post,array $datospagos = null,$iduser =null, array $files = null){


		//datos de hotel 
		//
		
		$this->setNombreHotel($post['nombrehotel']);
		$this->setIata($post['iata']);
		$this->setWebsite($post['website']);
		$this->setDireccion($post['direccion']);
		$this->setCodigoPostal($post['codigopostal']);
		$this->setEstado($post['estado']);
		$this->setCiudad($post['ciudad']);


		//datos del solicitante
		$this->setNombre($post['nombre']);
		$this->setApellido($post['apellido']);
		$this->setEmailFranquiciatario($post['emailfranquiciatario']);
		//Franquiciatario
		 $this->setTelefono($post['telefonofijo']);
		 $this->setMovil($post['telefonomovil']);

		
		$this->solicitante = $iduser;
		if($datospagos != null){

			$this->registropago = true;
			
			// Datos para el pago de comision no van en el formulario de solicitud
			 $this->setBanco($datospagos['nombre_banco']);
			 $this->setCuenta($datospagos['cuenta']);
			 $this->setClabe($datospagos['clabe']);
			 $this->setSwift($datospagos['swift']);

			 $this->setNombreBancoTarjeta($datospagos['nombre_banco_targeta']);
			 $this->setNumeroTarjeta($datospagos['numero_targeta']);

			 $this->setEmailPaypal($datospagos['email_paypal']);
		}
		if($iduser > 0){


			$this->RegistrarSolicitudPago();
			return true;
		}else{

			$this->RegistrarSolicitud();
		 	return true;
		}
	

	}


	public function capturarultimo($idhotel = null){


					$sql = "SELECT sfr.id_franquiciatario as idfranquiciatario, sfr.id as solicitud,i.codigo,h.nombre as nombrehotel,h.id as idhotel  from solicitudfr as sfr join hotel as h on h.id = :hotel join iata as i on h.id_iata = i.id  where sfr.id = (select max(id) from solicitudfr)";
					
					$stm = $this->con->prepare($sql);
					$stm->execute(array(':hotel'=>$idhotel));

					$array = $stm->fetchAll(PDO::FETCH_ASSOC);
					return $array;


	}


	private function RegistrarSolicitudPago(){


		if($this->con->inTransaction()){
			$this->con->rollBack();
		}

		$this->con->beginTransaction();

		// registro de franquiciatario //
		

		if($this->registropago){
			$querydatospagocomision = "INSERT INTO datospagocomision(banco,cuenta,clabe,swift,numero_tarjeta,email_paypal,banco_tarjeta)
																values(:banco,:cuenta,:clabe,:swift,:numero_tarjeta,:email_paypal,:banco_tarjeta)";

							$stm3 = $this->con->prepare($querydatospagocomision);

							$resultdatospagocomsion = $stm3->execute(array(	':banco' => $this->getBanco(), 
							                                              	':cuenta' => $this->getCuenta(),
							                                              	':clabe' => $this->getClabe(),
							                                              	':swift' => $this->getSwift(),
							                                              	':numero_tarjeta' => $this->getTarjeta(),
							                                              	':email_paypal' => $this->getEmailPaypal(),
							                                          		':banco_tarjeta' => $this->getBancoTarjeta())); 

				
							$iddatos                    = $this->con->lastInsertId();


			$query = "INSERT INTO franquiciatario(telefonofijo,telefonomovil,codigo_hotel,nombre,apellido,email,id_datospagocomision) values(:telefonofijo,:telefonomovil,:codigohotel,:nombre,:apellido,:email,:pago)";

			$param = array(':telefonofijo'=>$this->registrar['telefonofijo'],
								':telefonomovil' =>$this->registrar['telefonomovil'],
								':codigohotel'   =>'Ninguna',
								':nombre'        =>$this->registrar['nombre'],
								':apellido'      =>$this->registrar['apellido'],
								':email'         =>$this->registrar['emailfranquiciatario'],
								':pago'          =>$iddatos
							);
		}else{
			$query = "INSERT INTO franquiciatario(telefonofijo,telefonomovil,codigo_hotel,nombre,apellido,email) values(:telefonofijo,:telefonomovil,:codigohotel,:nombre,:apellido,:email)";
			$param = array(':telefonofijo'=>$this->registrar['telefonofijo'],
								':telefonomovil' =>$this->registrar['telefonomovil'],
								':codigohotel'   =>'Ninguna',
								':nombre'        =>$this->registrar['nombre'],
								':apellido'      =>$this->registrar['apellido'],
								':email'         =>$this->registrar['emailfranquiciatario']
							
							);
		}
		

		
				
				try {
					$stm = $this->con->prepare($query);
					$stm->execute($param);
				
				} catch (PDOException $e) {
					$this->error_log(__METHOD__,__LINE__,$e->getMessage());
					$this->con->rollBack();
					return false;
				}

			$idfranquiciatario = $this->con->lastInsertId();

			$sql1 = "INSERT INTO hotel(nombre,codigo,direccion,sitio_web,id_ciudad,codigo_postal,comision,aprobada,id_iata,id_estado)
							values(:nombre,:codigo,:direccion,:sitioweb,:ciudad,:codigopostal,:comision,:aprobada,:iata,:estado)";


				try {
						$stm = $this->con->prepare($sql1);
						
						$datos = array(

						':nombre'       =>	$this->getNombreHotel(),
						':codigo'       =>	'Ninguna',
						':direccion'    =>	$this->getDireccion(),
						':sitioweb'     =>	$this->getSitioWeb(),
						':ciudad'       =>	$this->registrar['id_ciudad'],
						':codigopostal' =>	$this->getCodigoPostal(),
						':comision'     =>	0,
						':aprobada'     =>  1,
						':iata'         =>	$this->registrar['id_iata'],
						':estado'       =>	$this->registrar['id_estado']
						);
						
						$stm->execute($datos);

				$this->ultimohotel = $this->con->lastInsertId();
				} catch (PDOException $e) {
					$this->con->rollBack();
					return false;
				}
			


			$query1 = "INSERT INTO solicitudfr(id_usuario,id_franquiciatario,condicion)
						values(:usuario,:referidor,:condicion)";


			try {

				$stm = $this->con->prepare($query1);
				$datos = array(
								':usuario'      => $this->solicitante,
								':referidor'    => $idfranquiciatario,
								':condicion'    => 1
							);

				$stm->execute($datos);
				$this->con->commit();

			} catch (PDOException $e) {
				$this->error_log(__METHOD__,__LINE__,$e->getMessage());
				$this->con->rollBack();
				return false;
			}
	}

	private function RegistrarSolicitud(){


		if($this->con->inTransaction()){
			$this->con->rollBack();
		}

		$this->con->beginTransaction();

		// registro de franquiciatario //
		 
		

		$query = "INSERT INTO franquiciatario(telefonofijo,telefonomovil,codigo_hotel,nombre,apellido,email) values(:telefonofijo,:telefonomovil,:codigohotel,:nombre,:apellido,:email)";

		try {
			$stm = $this->con->prepare($query);
			$datos = array(':telefonofijo'=>$this->registrar['telefonofijo'],
							':telefonomovil'=>$this->registrar['telefonomovil'],
							':codigohotel'=>'Ninguna',
							':nombre'=>$this->registrar['nombre'],
							':apellido'=>$this->registrar['apellido'],
							':email'=>$this->registrar['emailfranquiciatario']);

			$stm->execute($datos);

		} catch (PDOException $e) {
				$this->error_log(__METHOD__,__LINE__,$e->getMessage());
				$this->con->rollBack();
				return false;
		}

			$lasid = $this->con->lastInsertId();


			$query1 = "INSERT INTO solicitudfr(id_usuario,id_franquiciatario,condicion,hotel,sitioweb,direccion,codigopostal,id_estado,id_ciudad,id_iata)
						values(:usuario,:referidor,:condicion,:hotel,:sitioweb,:direccion,:codigopostal,:estado,:ciudad,:iata)";


			try {

				$stm = $this->con->prepare($query1);
				$datos = array(
								':usuario'      => $this->registrar['id_usuario'],
								':referidor'    => $lasid,
								':condicion'    => 0,
								':hotel'        => $this->registrar['nombrehotel'],
								':sitioweb'     => $this->registrar['sitioweb'],
								':direccion'    => $this->registrar['direccion'],
								':codigopostal' => $this->registrar['codigopostal'],
								':estado'       => $this->registrar['id_estado'],
								':ciudad'       => $this->registrar['id_ciudad'],
								':iata'         => $this->registrar['id_iata']
							);

				$stm->execute($datos);
				$this->con->commit();

			} catch (PDOException $e) {
				$this->error_log(__METHOD__,__LINE__,$e->getMessage());
				$this->con->rollBack();
				return false;
			}

			$idsolicitud = $this->con->lastInsertId();

			$content = 'Se ha recibido una nueva solicitud para afiliar un franquiciatario. <a style="outline:none; color:#0082b7; text-decoration:none;" href="'.HOST.'/admin/perfiles/solicitud.php?solicitud='.$idsolicitud.'&perfil=franquiciatario">Haz clic aqu&iacute; para verla</a>.';

			$contentingles = 'A new application has been received to affiliate a franchisee. <a style="outline:none; color:#0082b7; text-decoration:none;" href="'.HOST.'/admin/perfiles/solicitud.php?solicitud='.$idsolicitud.'&perfil=franquiciatario">Click here to se her</a>.';

						$body_alt =
							'Se ha recibido una nueva solicitud para afiliar un franquiciatario. Sigue este enlace para verla: '.HOST.'/admin/perfiles/solicitud.php?solicitud='.$idsolicitud.'&perfil=franquiciatario';
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
						$mail->addAddress('corporativo@infochannel.si');
						// Hacerlo formato HTML
						$mail->isHTML(true);
						// Formato del correo
						$mail->Subject = 'Nueva solicitud de franquiciatario';
						$mail->Body    = $this->email_template($content,$contentingles);
						$mail->AltBody = $body_alt;

						$mail->send();

						$_SESSION['notification']['success'] = 'Se ha enviado la solicitud para afiliarte como franquiciatario exitosamente. Te mantendremos informado de cualquier avance.';
						header('Location: '.HOST.'/Referidor/solicitudes');
						die();
				

	}

	private function RegistrarFranquiciatario(){

		if($this->con->inTransaction()){
			$this->con->rollBack();
		}

		$this->con->beginTransaction();

		
			$querydatospagocomision = "INSERT INTO datospagocomision(banco,cuenta,clabe,swift,numero_tarjeta,email_paypal,banco_tarjeta)
														values(:banco,:cuenta,:clabe,:swift,:numero_tarjeta,:email_paypal,:banco_tarjeta)";

			try {
				$stm3 = $this->con->prepare($querydatospagocomision);
					$resultdatospagocomsion = $stm3->execute(array(':banco' => $this->getBanco(), 
					                                              	':cuenta' => $this->getCuenta(),
					                                              	':clabe' => $this->getClabe(),
					                                              	':swift' => $this->getSwift(),
					                                              	':numero_tarjeta' => $this->getTarjeta(),
					                                              	':email_paypal' => $this->getEmailPaypal(),
					                                          		':banco_tarjeta' => $this->getBancoNombreTarjeta()));	
			} catch (PDOException $e) {
				$this->error_log(__METHOD__,__LINE__,$e->getMessage());
				$this->con->rollBack();
				
				return false;
			}

			$id_datospagocomision = $this->con->lastInsertId();
					
			$query = "insert into franquiciatario(telefonofijo,telefonomovil,id_datospagocomision,codigo_hotel) values(:telefonofijo,:telefonomovil,:id_datospagocomision,:codigo_hotel)";
			
				try {
					$stm = $this->con->prepare($query);
					$stm->execute(array(':telefonofijo' => $this->getTelefono(), ':telefonomovil' => $this->getMovil(),':id_datospagocomision' => $id_datospagocomision,':codigo_hotel'=>$this->registrar['codigohotel']));
				} catch (PDOException $ex) {
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					$this->con->rollBack();
					
					return false;	
				}

				$last_id = $this->con->lastInsertId();

				$querysolicitud = "INSERT INTO solicitudfr(id_franquiciatario,id_usuario,condicion)values(:id_franquiciatario,:id_usuario,:condicion)";
						
						try {
							$stm1 = $this->con->prepare($querysolicitud);			
						$stm1->execute(array(':id_franquiciatario' => $last_id,
											':id_usuario' => $this->registrar['id_usuario'],
											':condicion' => 0
											));
						
						$this->con->commit();
						$idsolicitud = $this->con->lastInsertId();


						$content = 'Se ha recibido una nueva solicitud para afiliar un franquiciatario. <a style="outline:none; color:#0082b7; text-decoration:none;" href="'.HOST.'/admin/perfiles/solicitud.php?solicitud='.$idsolicitud.'&perfil=franquiciatario">Haz clic aqu&iacute; para verla</a>.';
						$body_alt =
							'Se ha recibido una nueva solicitud para afiliar un franquiciatario. Sigue este enlace para verla: '.HOST.'/admin/perfiles/solicitud.php?solicitud='.$idsolicitud.'&perfil=franquiciatario';
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
						$mail->addAddress('corporativo@infochannel.si');
						// Hacerlo formato HTML
						$mail->isHTML(true);
						// Formato del correos
						$mail->Subject = 'Nueva solicitud de franquiciatario';
						$mail->Body    = $this->email_template($content);
						$mail->AltBody = $body_alt;

						$mail->send();

						$_SESSION['notification']['success'] = 'Se ha enviado la solicitud para afiliarte como franquiciatario exitosamente. Te mantendremos informado de cualquier avance.';
						header('Location: '.HOST.'/Franquiciatario/solicitudes');
						die();
						} catch (PDOException $exc) {
							$this->error_log(__METHOD__,__LINE__,$exc->getMessage());
							$this->con->rollBack();
							return false;
						}
}

	private function email_template($content,$contentingles = null){
		$html = 
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nueva solicitud de Hotel</title>
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
										<strong>Nueva solicitud de franquiciatario | New franchisee application</strong>
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
										For any clarification, contact our support team.<br>
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


	public function getHoteles(){

		$query = "select h.direccion as direccionhotel, e.estado,p.pais,c.ciudad, h.id, h.nombre as hotel, h.codigo, CONCAT(h.direccion,' ',c.ciudad,' ',e.estado,' ',p.pais) as direccion, h.sitio_web
		from hotel as h join ciudad as c on h.id_ciudad = c.id_ciudad 
						join estado as e on c.id_estado = e.id_estado	
						join pais as p on e.id_pais = p.id_pais";

		$stm = $this->con->prepare($query);
		$stm->execute();

		while($value = $stm->fetch(PDO::FETCH_ASSOC)) {?>

			<tr class="capt" style="cursor:pointer;" data-hotel="<?php echo $value['id'];?>">
			<td class="codigohotel" data-codigo="<?php echo $value['codigo'] ?>"><?php echo $value['codigo'] ?></td>
			<td class="nombrehotel" data-nombre="<?php echo $value['hotel']?>"><?php echo $value['hotel']?></td>
			<td class="direccionhotel" data-direccion="<?php echo $value['direccionhotel']?>" data-pais="<?php echo $value['pais'] ?>" data-estado="<?php echo $value['estado']; ?>" data-ciudad="<?php echo $value['ciudad']; ?>"><?php echo $value['direccion'] ?></td>
			<td class="sitiowebhotel" data-sitio="<?php echo $value['sitio_web']?>"><?php echo $value['sitio_web'] ?></td>			
			</tr>

		<?php  }


	}
	public function getHotel(){
		return "";
	}
	public function getBancoError(){
		if($this->error['banco']){
			$error = '<p class="text-danger">'._safe($this->error['banco']).'</p>';
			return $error;
		}

	}
	private function setBanco($string = null){
		if($string){
			$string = trim($string);
			$this->registrar['banco'] = $string;
			return true;
		}
		$this->error['banco'] = 'Este campo es obligatorio.';
		return false;
	}

	public function getCuentaError(){
		if($this->error['cuenta']){
			$error = '<p class="text-danger">'._safe($this->error['cuenta']).'</p>';
			return $error;
		}

	}
	private function setCuenta($string = null){
		if($string){
			$string = trim($string);
			$this->registrar['cuenta'] = $string;
			return true;
		}
		$this->error['cuenta'] = 'Este campo es obligatorio.';
		return false;
	}

	public function getClabeError(){
		if($this->error['clabe']){
			$error = '<p class="text-danger">'._safe($this->error['clabe']).'</p>';
			return $error;
		}

	}

	private function setClabe($string = null){
		if($string){
			$string = trim($string);
			$this->registrar['clabe'] = $string;
			return true;
		}
		$this->error['clabe'] = 'Este campo es obligatorio.';
		return false;
	}

	public function getSwiftError(){
		if($this->error['clabe']){
			$error = '<p class="text-danger">'._safe($this->error['clabe']).'</p>';
			return $error;
		}

	}

	private function setSwift($string = null){
		if($string){
			$string = trim($string);
			$this->registrar['swift'] = $string;
			return true;
		}
		$this->error['swift'] = 'Este campo es obligatorio.';
		return false;
	}

	public function getNombreBancoTarjetaError(){
		if($this->error['banco_tarjeta']){
			$error = '<p class="text-danger">'._safe($this->error['banco_nombre_tarjeta']).'</p>';
			return $error;
		}
	}

	private function setNombreBancoTarjeta($string = null){
		if($string){
			$string = trim($string);
			$this->registrar['banco_tarjeta'] = $string;
			return true;
		}
		$this->error['banco_tarjeta'] = 'Este campo es obligatorio.';
		return false;
	}

	public function getNumeroTarjetaError(){
		if($this->error['numero_tarjeta']){
			$error = '<p class="text-danger">'._safe($this->error['numero_tarjeta']).'</p>';
			return $error;
		}
	}

	private function setNumeroTarjeta($string = null){
		if($string){
			$string = trim($string);
			$this->registrar['numero_tarjeta'] = $string;
			return true;
		}
		$this->error['numero_tarjeta'] = 'Este campo es obligatorio.';
		return false;
	}

	public function getTarjeta(){
		return $this->registrar['numero_tarjeta'];
	}

	private function setCodigoHotel($string = null){

			$this->registrar['codigohotel'] = $string;

	}

	private function setIata($entero = null){
		if($entero > 0){
			$this->registrar['id_iata'] = $entero;
			return true;
		}
		$this->error['id_iata'] = 'Este campo es obligatorio.';
		return false;
	}
	private function setCargo($string = null){
		if($string){
			$this->registrar['cargo'] = trim($string);
			if(strlen($string) < 1){
				$this->error['cargo'] = 'Este es un campo obligatorio.';
				return false;
			}
			return true;
		}
		$this->error['cargo'] = 'Este campo es obligatorio.';
		return false;
	}
	private function setNombre($string = null){
		if($string){
			$string = trim($string);
			$this->registrar['nombre'] = $string;
			return true;
		}
		$this->error['nombre'] = 'Este es un campo Obligatorio';
		return false;

	}

	private function setApellido($string = null){
		if($string){
			$string = trim($string);
			$this->registrar['apellido'] = $string;
			return true;
		}
		$this->error['apellido'] = 'Este es un campo Obligatorio';
		return false;

	}
	private function setNombreHotel($string = null){
		if($string){
			$string = trim($string);
			$this->registrar['nombrehotel'] = $string;
			return true;
		}
		$this->error['nombrehotel'] = 'Este campo es obligatorio.';
		return false;
	}
	private function setNombreResponsable($string = null){
		if($string){
			$this->registrar['nombre_responsable'] = trim($string);
			if(strlen($string) < 1){
				$this->error['nombre_responsable'] = 'Este es un campo obligatorio.';
				return false;
			}
			return true;
		}
		$this->error['nombre_responsable'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setApellidoResponsable($string = null){
		if($string){
			$this->registrar['apellido_responsable'] = trim($string);
			if(strlen($string) < 1){
				$this->error['apellido_responsable'] = 'Este es un campo obligatorio.';
				return false;
			}
			return true;
		}
		$this->error['apellido_responsable'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_category_id($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				$this->error['category'] = 'Selecciona una categoría.';
				return false;
			}
			$this->registrar['category_id'] = $string;
			return true;
		}
		$this->error['category'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setComision($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 6 || $string > 100){
				$this->error['comision'] = 'Ingresa un número entero entre 0 y 8.';
				return false;
			}
			$this->registrar['comision'] = $string;
			return true;
		}
		$this->error['comision'] = 'Este campo es obligatorio.';
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
			$this->registrar['url'] = $string;
			return;
		}
		$this->error['url'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setEmail($string = null){
		if($string){
			$email = filter_var($string, FILTER_VALIDATE_EMAIL);
			if(!$email){
				$this->error['email'] = 'Escribe una dirección de correo electrónico correcta. Ejemplo: usuario@ejemplo.com.';
				$this->registrar['email'] = $string;
				return false;
			}
			$this->registrar['email'] = $email;
			return true;
		}
		$this->error['email'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setEmailFranquiciatario($string = null){
		if($string){
			$email = filter_var($string, FILTER_VALIDATE_EMAIL);
			if(!$email){
				$this->error['emailfranquiciatario'] = 'Escribe una dirección de correo electrónico correcta. Ejemplo: usuario@ejemplo.com.';
				$this->registrar['emailfranquiciatario'] = $string;
				return false;
			}
			$this->registrar['emailfranquiciatario'] = $email;
			return true;
		}
		$this->error['emailfranquiciatario'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setEmailPaypal($string = null){
		if($string){
			$email = filter_var($string, FILTER_VALIDATE_EMAIL);
			if(!$email){
				$this->error['email_paypal'] = 'Escribe una dirección de correo electrónico correcta. Ejemplo: usuario@ejemplo.com.';
				$this->registrar['email_paypal'] = $string;
				return false;
			}
			$this->registrar['email_paypal'] = $email;
			return true;
		}
		$this->error['email_paypal'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setTelefono($string = null){
		if($string){
			$string = trim($string);
			if(!preg_match('/^[0-9() +-]+$/ui',$string)){
				$this->error['telefonofijo'] = 'Escribe un número telefónico correcto. Ejemplo: (123) 456-78-90.';
				$this->registrar['telefonofijo'] = $string;
				return false;
			}
			$this->registrar['telefonofijo'] = $string;
			return true;
		}
		$this->error['telefonofijo'] = 'Este campo es obligatorio.';
		return false;
	}
	private function setMovil($string = null){
		if($string){
			$string = trim($string);
			if(!preg_match('/^[0-9() +-]+$/ui',$string)){
				$this->error['movil'] = 'Escribe un número telefóno movil correcto. Ejemplo: (123) 456-78-90.';
				$this->registrar['telefonomovil'] = $string;
				return false;
			}
			$this->registrar['telefonomovil'] = $string;
			return true;
		}
		$this->error['telefonomovil'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setWebsite($string = null){
		if($string){
			if(!preg_match('_^(?:(?:https?|ftp)://)?(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,}))\.?)(?::\d{2,5})?(?:[/?#]\S*)?$_iuS',$string)){
				$this->error['sitio_web'] = 'Escribe un enlace correcto. Ejemplo: www.travelpoints.com o http://travelpoints.com';
				$this->registrar['sitio_web'] = $string;
				return false;
			}
			if(!preg_match("@^https?://@", $string)){
				$this->registrar['sitio_web'] = 'http://'.$string;
			}else{
				$this->registrar['sitio_web'] = $string;
			}
		}
		return true;
	}

	private function setDireccion($string = null){
		if($string){
			$string = trim($string);
			$this->registrar['direccion'] = $string;
			return true;
		}
		$this->error['direccion'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setCodigoPostal($string = null){
		if($string){
			$string = trim($string);
			$this->registrar['codigopostal'] = $string;
			return true;
		}
		$this->error['codigopostal'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setCiudad($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				$this->error['id_ciudad'] = 'Selecciona una ciudad.';
				return false;
			}
			$this->registrar['id_ciudad'] = $string;
			return true;
		}
		$this->error['id_ciudad'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setEstado($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				return false;
			}
			$this->registrar['id_estado'] = $string;
			return true;
		}
		return false;
	}

	private function setPais($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				return false;
			}
			$this->registrar['id_pais'] = $string;
			return true;
		}
		return false;
	}

	private function setLocation($lat = null, $lon = null){
		if($lat & $lon){
			if(!filter_var($lat, FILTER_VALIDATE_FLOAT) || !filter_var($lon, FILTER_VALIDATE_FLOAT)){
				$this->error['location'] = 'Utiliza el marcador del mapa para ubicar tu negocio.';
				return false;
			}else{
				$this->registrar['latitud'] = trim($lat);
				$this->registrar['longitud'] = trim($lon);
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
				$this->registrar['logo']['tmp'] = $files['logo']['tmp_name'];
				$this->registrar['logo']['name'] = $image->getName().'.'.$image->getMime();
				$this->registrar['logo']['path'] = $image->getFullPath();
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
				$this->registrar['photo']['tmp'] = $files['photo']['tmp_name'];
				$this->registrar['photo']['name'] = $image->getName().'.'.$image->getMime();
				$this->registrar['photo']['path'] = $image->getFullPath();
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



	public function getNombreHotel(){

		return _safe($this->registrar['nombrehotel']);

	}

	public function getNombreHotelError(){
		if($this->error['nombre']){
			$error = '<p class="text-danger">'._safe($this->error['nombrehotel']).'</p>';
			return $error;
		}
	}

	public function getNombre(){

		return _safe($this->registrar['nombre']);

	}

	public function getApellido(){

		return _safe($this->registrar['apellido']);

	}





	public function getNombreError(){
		if($this->error['nombre']){
			$error = '<p class="text-danger">'._safe($this->error['nombre']).'</p>';
			return $error;
		}
	}
	public function getApellidoError(){
		if($this->error['nombre']){
			$error = '<p class="text-danger">'._safe($this->error['apellido']).'</p>';
			return $error;
		}
	}

	public function get_Iata(){
		return _safe($this->registrar['iata']);
	}

	
	public function getIataError(){
		if($this->error['id_iata']){
			$error = '<p class="text-danger">'._safe($this->error['id_iata']).'</p>';
			return $error;
		}
	}
	public function getCargoError(){
		if($this->error['cargo']){
			$error = '<p class="text-danger">'._safe($this->error['cargo']).'</p>';
			return $error;
		}
	}

	public function getNombreResponsable(){
		return _safe($this->registrar['nombre_responsable']);
	}

	public function getApellidoResponsable(){
		return _safe($this->registrar['apellido_responsable']);
	}

	public function getNombreResponsableError(){
		if($this->error['nombre_responsable']){
			$error = '<p class="text-danger">'._safe($this->error['nombre_responsable']).'</p>';
			return $error;
		}
	}

	public function getApellidoResponsableError(){
		if($this->error['apellido_responsable']){
			$error = '<p class="text-danger">'._safe($this->error['apellido_responsable']).'</p>';
			return $error;
		}
	}

	public function getIata(){
		$iatas = null;
		$query = "SELECT i.id,i.codigo,c.ciudad FROM iata  as i join ciudad as c on i.id_ciudad = c.id_ciudad";

		$query = "(select i.id, i.codigo from iata as i 
				join ciudad as c on i.id_ciudad = c.id_ciudad
 				left join estado as e on c.id_estado = e.id_estado 
				left join pais as p on e.id_pais = p.id_pais)
			UNION
			(select i.id, i.codigo from iata as i 
							left join ciudad as c on i.id_ciudad = c.id_ciudad
			 				join estado as e on i.id_estado = e.id_estado 
							left join pais as p on e.id_pais = p.id_pais)";
					try{

						$stmt = $this->con->prepare($query);
						$stmt->execute();

					}catch(\PDOException $ex){
						$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
						return false;
					}
					while($row = $stmt->fetch()){
						$iatas = _safe($row['codigo']);
						if($this->register['id_iata'] == $row['id']){
							$iata .= '<option value="'.$row['id'].'">'.$iatas.' '.$row['ciudad'].'</option>';
						}else{
							$iata .= '<option value="'.$row['id'].'">'.$iatas.'</option>';
						}
					}
					return $iata;
	}

	public function get_category_error(){
		if($this->error['category']){
			$error = '<p class="text-danger">'._safe($this->error['category']).'</p>';
			return $error;
		}
	}

	public function get_commission(){
		return _safe($this->registrar['commission']);
	}

	public function get_commission_error(){
		if($this->error['commission']){
			$error = '<p class="text-danger">'._safe($this->error['commission']).'</p>';
			return $error;
		}
	}

	public function get_url(){
		return _safe($this->registrar['url']);
	}

	public function get_url_error(){
		if($this->error['url']){
			$error = '<p class="text-danger">'._safe($this->error['url']).'</p>';
			return $error;
		}
	}

	public function getEmail(){
		return _safe($this->registrar['email']);
	}
	public function getEmailFranquiciatario(){
		return _safe($this->registrar['emailfranquiciatario']);
	}
	public function getEmailFranquiciatarioError(){
		if($this->error['emailfranquiciatario']){
			$error = '<p class="text-danger">'._safe($this->error['emailfranquiciatario']).'</p>';
			return $error;
		}
	}
	public function getCargo(){
		return _safe($this->registrar['cargo']);
	}

	public function getTelefono(){
		return _safe($this->registrar['telefonofijo']);
	}

	public function getMovil(){
		return _safe($this->registrar['telefonomovil']);
	}

	public function getSitioWeb(){
		return _safe($this->registrar['sitio_web']);
	}

	public function getBanco(){
		return _safe($this->registrar['banco']);
	}
	public function getBancoTarjeta(){
		return _safe($this->registrar['banco_tarjeta']);
	}
	public function getBancoNombreTarjeta(){
		return _safe($this->registrar['banco_tarjeta']);
	}
	public function getEmailPaypal(){
		return _safe($this->registrar['email_paypal']);
	}
	public function getCuenta(){
		return _safe($this->registrar['cuenta']);
	}

	public function getClabe(){
		return _safe($this->registrar['clabe']);
	}

	public function getSwift(){
		return _safe($this->registrar['swift']);
	}


	public function getEmailError(){
		if($this->error['email']){
			$error = '<p class="text-danger">'._safe($this->error['email']).'</p>';
			return $error;
		}
	}

	public function getEmailPaypalError(){
		if($this->error['email_paypal']){
			$error = '<p class="text-danger">'._safe($this->error['email_paypal']).'</p>';
			return $error;
		}
	}
	


	public function getTelefonoError(){
		if($this->error['telefonofijo']){
			$error = '<p class="text-danger">'._safe($this->error['telefonofijo']).'</p>';
			return $error;
		}
	}

	public function getMovilError(){
		if($this->error['telefonomovil']){
			$error = '<p class="text-danger">'._safe($this->error['telefonomovil']).'</p>';
			return $error;
		}

	}

	public function getWebsite(){
		return _safe($this->registrar['website']);
	}

	public function getWebsiteError(){
		if($this->error['sitio_web']){
			$error = '<p class="text-danger">'._safe($this->error['sitio_web']).'</p>';
			return $error;
		}
	}

	public function getDireccion(){
		return _safe($this->registrar['direccion']);
	}

	public function getDirecccionError(){
		if($this->error['direccion']){
			$error = '<p class="text-danger">'._safe($this->error['direccion']).'</p>';
			return $error;
		}
	}

	public function getCodigoPostal(){
		return _safe($this->registrar['codigopostal']);
	}

	public function getCodigoPostalError(){
		if($this->error['codigopostal']){
			$error = '<p class="text-danger">'._safe($this->error['codigopostal']).'</p>';
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
			if($this->registrar['country_id'] == $row['id_pais']){
				$countries .= '<option value="'.$row['id_pais'].'" selected>'.$country.'</option>';
			}else{
				$countries .= '<option value="'.$row['id_pais'].'">'.$country.'</option>';
			}
		}
		return $countries;
	}

	public function get_states(){
		$states = null;
		if($this->registrar['country_id']){
			$query = "SELECT id_estado, estado FROM estado WHERE id_pais = :id_pais";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_pais', $this->registrar['country_id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$state = _safe($row['estado']);
				if($this->registrar['state_id'] == $row['id_estado']){
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
		if($this->registrar['state_id']){
			$query = "SELECT id_ciudad, ciudad FROM ciudad WHERE id_estado = :id_estado";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_estado', $this->registrar['state_id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$city = _safe($row['ciudad']);
				if($this->registrar['city_id'] == $row['id_ciudad']){
					$cities.= '<option value="'.$row['id_ciudad'].'" selected>'.$city.'</option>';
				}else{
					$cities.= '<option value="'.$row['id_ciudad'].'">'.$city.'</option>';
				}
			}
		}
		return $cities;
	}

	public function getCiudadError(){
		if($this->error['id_ciudad']){
			$error = '<p class="text-danger">'._safe($this->error['id_ciudad']).'</p>';
			return $error;
		}
	}

	public function getLatitud(){
		return _safe($this->registrar['latitud']);
	}

	public function getLongitud(){
		return _safe($this->registrar['longitud']);
	}

	public function getLocationError(){
		if($this->error['location']){
			return '<p class="text-danger">'._safe($this->error['location']).'</p>';
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
		file_put_contents(ROOT.'\assets\error_logs\afiliar-franquiciatario.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
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