<?php 

namespace admin\libs;

use assets\libs\connection;

use PDO;
/**
 * @author Crespo Jhonatan 
 * @since 22/04/2019
 */
class Comprobantes
{
	

	private $con;
	private $comprobantes = array(

	);

	private $solicitudes = array();


	function __construct(connection $conection){
		$this->con = $conection->con;

		$this->cargarData();

	}

	private $error = array('error' =>null);


// METHODOS DE LA CLASE



	private function cargarData(){

			// $query = "(select r.id as solicitud, r.tipo_pago,r.creado,CONCAT(u.nombre,' ',u.apellido) as nombre, u.username,r.monto,r.pagado,r.aprobado, 'Hotel' as perfil,u.imagen,r.recibo
			// 	from retiro as r 
			// 	join usuario as u on r.id_usuario_solicitud  = u.id_usuario 
			// 	join retirocomision as rc on r.id = rc.id_retiro )
			// 	UNION
				
			// 	(select r.id as solicitud,r.tipo_pago, r.creado,CONCAT(u.nombre,' ',u.apellido) as nombre, u.username,r.monto,r.pagado,r.aprobado, 'Referidor' as perfil,u.imagen,r.recibo
			// 	from retiro as r 
			// 	join usuario as u on r.id_usuario_solicitud  = u.id_usuario 
			// 	join retirocomisionreferidor as rc on r.id = rc.id_retiro )
				
			// 	UNION
				
		// UNION
						// (select '2' as perfil, r.id as solicitud, r.tipo_pago,r.creado,CONCAT(u.nombre,' ',u.apellido) as nombre, u.username,r.monto,r.pagado,r.aprobado , 'Franquiciatario' as perfil,u.imagen,r.recibo
						// from retiro as r 
						// join usuario as u on r.id_usuario_solicitud  = u.id_usuario 
						// join retirocomisionfranquiciatario as rc on r.id = rc.id_retiro)
						// ORDER BY creado


			$query = "(select rc.perfil, r.id as solicitud, r.tipo_pago,r.creado,CONCAT(u.nombre,' ',u.apellido) as nombre, 
						u.username,r.monto,r.pagado,r.aprobado,u.imagen,r.recibo
						from retiro as r 
						join usuario as u on r.id_usuario_solicitud  = u.id_usuario 
						join retirocomision as rc on r.id = rc.id_retiro )

						UNION
						(select rc.perfil, r.id as solicitud, r.tipo_pago,r.creado,CONCAT(p.nombre,' ',p.apellido) as nombre, 
						p.username,r.monto,r.pagado,r.aprobado,'' as imagen,r.recibo
						from retiro as r 
						join promotor as p on r.id_promotor = p.id
						join retirocomision as rc on r.id = rc.id_retiro)
				
				";
		$stm = $this->con->prepare($query);
		$stm->execute();
		$this->solicitudes = $stm->fetchALL(PDO::FETCH_ASSOC);

	}

	public function getSolicitudes(){

		$urlimg =  HOST.'/assets/img/user_profile/';


		foreach($this->solicitudes as $key => $value) {


			$this->solicitudes[$key]['monto'] = '$ '.number_format((float)$value['monto'],2,'.',',').' MXN';
		
			

			$pago =$value['monto'];

			if(empty($value['nombre'])){
				$this->solicitudes[$key]['nombre'] = $value["username"];
			}else{
				$this->solicitudes[$key]['nombre'] = $value['nombre'];
 			}


 			switch ($value['perfil']) {
 				case 1:
 					$this->solicitudes[$key]['perfil'] = 'Hotel';
 					break;
 				case 2:
 					$this->solicitudes[$key]['perfil'] = 'Franquiciatario';
 					break;
 				case 3:
 					$this->solicitudes[$key]['perfil'] = 'Referidor';
 					break;
 				case 4:
 					$this->solicitudes[$key]['perfil'] = 'Sistema';
 					break;
 				case 5:
 					$this->solicitudes[$key]['perfil'] = 'Promotor';
 					break;
 				default:
 					$this->solicitudes[$key]['perfil'] = 'Sin establecerse';
 					break;
 			}





			$fecha = date('d/m/Y g:i A', strtotime($value['creado']));

			$this->solicitudes[$key]['fecha'] = $fecha;

			if($value['aprobado'] == 1 ){
				$this->solicitudes[$key]['aprobado'] = 'Aprobada';
			}else{
				$this->solicitudes[$key]['aprobado'] = 'No aprobada';
			}

			$foto         = $value['imagen'];
			if(empty($foto) || is_null($foto)){

				if($this->solicitudes[$key]['perfil'] == 'Promotor'){
					$this->solicitudes[$key]['foto'] = '<div class="user user-md"><img src="'.$urlimg.'default.jpg'.'"></div>';
				}else{
					$this->solicitudes[$key]['foto'] = '<div class="user user-md">
						<a href="'.HOST."/socio/".$value['username'].'" target="_blank"><img src="'.$urlimg.'default.jpg'.'"></a>
					</div>';
				}
				
			}else{
				$this->solicitudes[$key]['foto'] = '<div class="user user-md">
						<a href="'.HOST."/socio/".$value['username'].'" target="_blank"><img src="'.$urlimg.$foto.'"></a>
					</div>';
			}

			$pagado = "Sin pagar";
			if($value['aprobado'] == 1){
				
				$this->solicitudes[$key]['pagado'] =  '$ '.number_format((float)$value['pagado'],2,'.',',').' MXN';

			
			}else{
				$this->solicitudes[$key]['pagado'] =  'Sin pagar';
			}

			$this->solicitudes[$key]['tipopago'] = 'Sin pagar';

			if($this->solicitudes[$key]['aprobado'] == "Aprobada"){
				if($value['tipo_pago'] == 1){
					$this->solicitudes[$key]['tipopago'] = "Total";
				}else if($value['tipo_pago'] == 2){
					$this->solicitudes[$key]['tipopago'] = "Parcial";
				}
			}

			$urlarchivo = HOST.'/assets/recibos/'.$value['recibo'];

			if($this->solicitudes[$key]['aprobado'] == 'No aprobada'){
							$this->solicitudes[$key]['btnaprobado'] = '<button type="button" data-pago="'.$pago.'" class="btn btn-primary aprobar" data-path="'._safe($_SERVER['REQUEST_URI']).'" data-id="'.$value['solicitud'].'" data-perfil="'.$this->solicitudes[$key]['perfil'].'" data-fecha="'.$fecha.'" data-monto="'.$this->solicitudes[$key]['monto'].'"  > <i class="fa fa-check" ></i> Pagar</button>';
						}else{
						$this->solicitudes[$key]['btnaprobado']	= '<button type="button" name="descargar" class="btn btn-warning " style="color:white !important;"><i class="fa fa-file-pdf-o"></i><a href="'.$urlarchivo.'" target="_blank">Descargar</a></button>';
						  } 			
		}

		return $this->solicitudes;
	}



	public function cambiarStatusMensaje(int $idmesaje){

		if($this->con->inTransaction()){
			$this->con->rollBack();
		}

		$this->con->beginTransaction();

		$sql = "UPDATE retiro_mensajes set leido = :leido where id=:id";

		try {
			$stm = $this->con->prepare($sql);
			$stm->execute(array(':leido'=>1,':id'=>$idmesaje));

			$this->con->commit();


		} catch (\PDOExection $e) {
			$this->error_log(__METHOD__,__LINE__,$e->getMessage());
			$this->con->rollBack();
			return false;
		}

		return true;

	}
	public function Aprobar(string $recibo,array $post){

		if($this->con->inTransaction()){
			$this->con->rollBack();
		}

		$this->con->beginTransaction();


		if($post['f-tpago'] == 'total'){

			if(!empty($post['mensaje'])){

				$sql = "INSERT INTO retiro_mensajes(mensaje,id_retiro,id_usuario,leido)values(:mensaje,:retiro,:usuario,0)";
				$datos = array(
								':mensaje' => $post['mensajeregreso'],
								':retiro' =>  $post['idsolicitud'],
								':usuario' => $_SESSION['user']['id_usuario']);

				try {

					$stm = $this->con->prepare($sql);
					$stm->execute($datos);
				
				}catch (\PDOExection $e) {
					$this->error_log(__METHOD__,__LINE__,$e->getMessage());
					$this->con->rollBack();
					return false;
				}

			}

			$query = "UPDATE retiro set recibo=:recibo,id_usuario_aprobacion=:usuario,pagado=:pagado, aprobado =1,tipo_pago=:tpago where id=:solicitud";

				$datos = array(':recibo' => $recibo,':usuario'=>$_SESSION['user']['id_usuario'],
								':pagado'=>$post['f-pago'],':tpago'=>1,':solicitud' => $post['idsolicitud']);

				try {

					$stm = $this->con->prepare($query);
					$stm->execute($datos);
					$this->con->commit();
					return true;

				} catch (\PDOExection $e) {
					
					$this->error_log(__METHOD__,__LINE__,$e->getMessage());
					$this->con->rollBack();
					return false;
					
				}


		}else{

			if(!empty($post['mensaje'])){

				$sql = "INSERT INTO retiro_mensajes(mensaje,id_retiro,id_usuario,leido)values(:mensaje,:retiro,:usuario,0)";
				$datos = array(
								':mensaje' => $post['mensajeregreso'],
								':retiro' =>  $post['idsolicitud'],
								':usuario' => $_SESSION['user']['id_usuario']);

				try {

					$stm = $this->con->prepare($sql);
					$stm->execute($datos);
				
				}catch (\PDOExection $e) {
					$this->error_log(__METHOD__,__LINE__,$e->getMessage());
					$this->con->rollBack();
					return false;
				}

			}


			$query = "UPDATE retiro set recibo=:recibo,id_usuario_aprobacion=:usuario,pagado=:pagado, aprobado =1,tipo_pago=:tpago where id=:solicitud";

				$datos = array(':recibo' => $recibo,':usuario'=>$_SESSION['user']['id_usuario'],
								':pagado'=>$post['f-pago'],':tpago'=>2,':solicitud' => $post['idsolicitud']);

				try {

					$stm = $this->con->prepare($query);
					$stm->execute($datos);
					
					// $ultimoretiro = $this->con->lastInsertId();
				} catch (\PDOExection $e) {
					
					$this->error_log(__METHOD__,__LINE__,$e->getMessage());
					$this->con->rollBack();
					return false;
					
				}

			$montoregresar = $post['f-montopedido'] - $post['f-pago'];


			switch ($post['perfil']) {

				case 'Hotel':

						$sql = "INSERT INTO retirocomision(negocio,usuario,id_retiro,condicion)values('Reembolso de resto por pago parcial','Resto pago parcial',:retiro,2)";
						
						try {
						
						$stm = $this->con->prepare($sql);
						
						$stm->bindParam(':retiro',$post['idsolicitud'],PDO::PARAM_INT);
						
						$stm->execute();

						$ultimoidretirocomision = $this->con->lastInsertId();
						
						} catch (PDOExection $e) {
						
						$this->error_log(__METHOD__,__LINE__,$e->getMessage());
						$this->con->rollBack();
						return false;
						
						}

						$sql = "SELECT id_hotel from retiro where id=:solicitud";

						try {
							$stm = $this->con->prepare($sql);

							$stm->bindParam(':solicitud',$post['idsolicitud'],PDO::PARAM_INT);
							$stm->execute();

							$fila = $stm->fetch(PDO::FETCH_ASSOC);

							$idhotel = $fila['id_hotel'];

						} catch (PDOExection $e) {

							$this->error_log(__METHOD__,__LINE__,$e->getMessage());
							$this->con->rollBack();
							return false;
						}


						$sql = "SELECT balance from balancehotel where id_hotel =:hotel order by id desc limit 1";

						$stm = $this->con->prepare($sql);

						$stm->bindParam(':hotel',$idhotel);

						$stm->execute();

						$fila = $stm->fetch(PDO::FETCH_ASSOC);

						$ultimobalance = $fila['balance'];


						$nuevobalance =  $ultimobalance + $montoregresar;

						$sql = "INSERT into balancehotel(balance,id_hotel,comision,id_retiro)values(:balance,:hotel,:comision,:retiro)";

						$datos = array(':balance' =>$nuevobalance ,':hotel'=>$idhotel,':comision'=>$montoregresar,':retiro'=>$ultimoidretirocomision);

						try {
							$stm = $this->con->prepare($sql);
							$stm->execute($datos);

							$this->con->commit();

							return true;
						} catch (PDOExection $e) {

							$this->error_log(__METHOD__,__LINE__,$e->getMessage());
							$this->con->rollBack();
							return false;
							
						}

					break;

				case 'Promotor':

						$sql = "INSERT INTO retirocomision(negocio,usuario,id_retiro,condicion,perfil)values('Reembolso de resto por pago parcial','Resto pago parcial',:retiro,2,5)";
						
						try {
						
						$stm = $this->con->prepare($sql);
						
						$stm->bindParam(':retiro',$post['idsolicitud'],PDO::PARAM_INT);
						
						$stm->execute();

						$ultimoidretirocomision = $this->con->lastInsertId();
						
						} catch (PDOExection $e) {
						
						$this->error_log(__METHOD__,__LINE__,$e->getMessage());
						$this->con->rollBack();
						return false;
						
						}

						$sql = "SELECT id_promotor from retiro where id=:solicitud";

						try {
							$stm = $this->con->prepare($sql);

							$stm->bindParam(':solicitud',$post['idsolicitud'],PDO::PARAM_INT);
							$stm->execute();

							$fila = $stm->fetch(PDO::FETCH_ASSOC);

							$idpromotor = $fila['id_promotor'];

						} catch (PDOExection $e) {

							$this->error_log(__METHOD__,__LINE__,$e->getMessage());
							$this->con->rollBack();
							return false;
						}


						$sql = "SELECT balance from balance where id_promotor =:promotor order by id desc limit 1";

						$stm = $this->con->prepare($sql);

						$stm->bindParam(':promotor',$idpromotor);

						$stm->execute();

						$fila = $stm->fetch(PDO::FETCH_ASSOC);

						$ultimobalance = $fila['balance'];


						$nuevobalance =  $ultimobalance + $montoregresar;

						$sql = "INSERT into balance(balance,id_promotor,comision,id_retiro,perfil)values(:balance,:promotor,:comision,:retiro,5)";

						$datos = array(':balance' =>$nuevobalance ,':promotor'=>$idpromotor,':comision'=>$montoregresar,':retiro'=>$ultimoidretirocomision);

						try {
							$stm = $this->con->prepare($sql);
							$stm->execute($datos);

							$this->con->commit();

							return true;
						} catch (PDOExection $e) {

							$this->error_log(__METHOD__,__LINE__,$e->getMessage());
							$this->con->rollBack();
							return false;
							
						}

					break;



				case 'Franquiciatario':

					$sql = "INSERT INTO retirocomision(negocio,usuario,id_retiro,condicion,perfil)values('Reembolso de resto por pago parcial','Resto pago parcial',:retiro,2,2)";
						
						try {
						
						$stm = $this->con->prepare($sql);
						
						$stm->bindParam(':retiro',$post['idsolicitud'],PDO::PARAM_INT);
						
						$stm->execute();


						$ultimoidretirocomisionfranquiciatario  = $this->con->lastInsertId();
						
						} catch (PDOExection $e) {
						
						$this->error_log(__METHOD__,__LINE__,$e->getMessage());
						$this->con->rollBack();
						return false;
						
						}

						$sql = "SELECT id_franquiciatario from retiro where id=:solicitud";

						try {
							$stm = $this->con->prepare($sql);

							$stm->bindParam(':solicitud',$post['idsolicitud'],PDO::PARAM_INT);
							$stm->execute();

							$fila = $stm->fetch(PDO::FETCH_ASSOC);

							$idfranquiciatario = $fila['id_franquiciatario'];

						} catch (PDOExection $e) {

							$this->error_log(__METHOD__,__LINE__,$e->getMessage());
							$this->con->rollBack();
							return false;
						}


						$sql = "SELECT balance from balance where id_franquiciatario =:fr order by id desc limit 1";

						$stm = $this->con->prepare($sql);

						$stm->bindParam(':fr',$idfranquiciatario);

						$stm->execute();

						$ultimobalance = $stm->fetch(PDO::FETCH_ASSOC)['balance'];



						$nuevobalance =  $ultimobalance + $montoregresar;


						$sql = "INSERT into balance(balance,id_franquiciatario,comision,id_retiro)values(:balance,:franquiciatario,:comision,:retiro)";

						$datos = array(':balance' =>$nuevobalance ,':franquiciatario'=>$idfranquiciatario,':comision'=>$montoregresar,':retiro'=>$ultimoidretirocomisionfranquiciatario);

						try {
							$stm = $this->con->prepare($sql);
							$stm->execute($datos);

							$this->con->commit();

							return true;
						} catch (PDOExection $e) {

							$this->error_log(__METHOD__,__LINE__,$e->getMessage());
							$this->con->rollBack();
							return false;
							
						}

					break;

				case 'Referidor':

						$sql = "INSERT INTO retirocomisionreferidor(negocio,usuario,id_retiro,condicion)values('Reembolso de resto por pago parcial','Resto pago parcial',:retiro,2)";
						
						try {
						
						$stm = $this->con->prepare($sql);
						
						$stm->bindParam(':retiro',$post['idsolicitud'],PDO::PARAM_INT);
						
						$stm->execute();
						
						$ultimoidretirocomisionreferidor = $this->con->lastInsertId();
						
						} catch (PDOExection $e) {
						
						$this->error_log(__METHOD__,__LINE__,$e->getMessage());
						$this->con->rollBack();
						return false;
						
						}


						$sql = "SELECT id_referidor from retiro where id=:solicitud";

						try {
							$stm = $this->con->prepare($sql);

							$stm->bindParam(':solicitud',$post['idsolicitud'],PDO::PARAM_INT);
							$stm->execute();

							$fila = $stm->fetch(PDO::FETCH_ASSOC);

							$idreferidor = $fila['id_referidor'];

						} catch (PDOExection $e) {

							$this->error_log(__METHOD__,__LINE__,$e->getMessage());
							$this->con->rollBack();
							return false;
						}


						$sql = "SELECT balance from balancereferidor where id_referidor =:referidor order by id desc limit 1";

						$stm = $this->con->prepare($sql);

						$stm->bindParam(':referidor',$idreferidor);

						$stm->execute();

						$ultimobalance = $stm->fetch(PDO::FETCH_ASSOC)['balance'];
						$nuevobalance =  $ultimobalance + $montoregresar;

						$sql = "INSERT into balancereferidor(balance,id_referidor,comision,id_retiro)values(:balance,:referidor,:comision,:retiro)";

						$datos = array(':balance' =>$nuevobalance ,':referidor'=>$idreferidor,':comision'=>$montoregresar,':retiro'=>$ultimoidretirocomisionreferidor);

						try {
							$stm = $this->con->prepare($sql);
							$stm->execute($datos);
							$this->con->commit();
							return true;

						} catch (PDOExection $e) {

							$this->error_log(__METHOD__,__LINE__,$e->getMessage());
							$this->con->rollBack();
							return false;
							
						}
					break;
				
				default:
					
					break;
			}

		}

	}

	public function getNotificacion(){

	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'/assets/error_logs/comprobantepagos.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}

}

 ?>


