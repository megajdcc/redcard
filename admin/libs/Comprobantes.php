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

			$query = "(select r.id as solicitud, r.tipo_pago,r.creado,CONCAT(u.nombre,' ',u.apellido) as nombre, u.username,r.monto,r.pagado,r.aprobado, 'Hotel' as perfil,u.imagen,r.recibo
				from retiro as r 
				join usuario as u on r.id_usuario_solicitud  = u.id_usuario 
				join retirocomision as rc on r.id = rc.id_retiro )
				UNION
				
				(select r.id as solicitud,r.tipo_pago, r.creado,CONCAT(u.nombre,' ',u.apellido) as nombre, u.username,r.monto,r.pagado,r.aprobado, 'Referidor' as perfil,u.imagen,r.recibo
				from retiro as r 
				join usuario as u on r.id_usuario_solicitud  = u.id_usuario 
				join retirocomisionreferidor as rc on r.id = rc.id_retiro )
				
				UNION
				
				(select r.id as solicitud, r.tipo_pago,r.creado,CONCAT(u.nombre,' ',u.apellido) as nombre, u.username,r.monto,r.pagado,r.aprobado , 'Franquiciatario' as perfil,u.imagen,r.recibo
				from retiro as r 
				join usuario as u on r.id_usuario_solicitud  = u.id_usuario 
				join retirocomisionfranquiciatario as rc on r.id = rc.id_retiro)
				ORDER BY creado";

		$stm = $this->con->prepare($query);
		$stm->execute();
		$this->solicitudes = $stm->fetchALL(PDO::FETCH_ASSOC);

	}

	public function ListarSolicitudes(){

		$urlimg =  HOST.'/assets/img/user_profile/';
		foreach($this->solicitudes as $key => $value) {


			$monto = number_format((float)$value['monto'],2,',','.');

			$pago =$value['monto'];

			if(empty($value['nombre'])){
				$nombre = $value["username"];
			}else{
				$nombre = $value['nombre'];
 			}

			$fecha = date('d/m/Y g:i A', strtotime($value['creado']));

			if($value['aprobado'] == 1 ){
				$aprobado = 'Aprobada';
			}else{
				$aprobado = 'No aprobada';
			}
			$foto         = $value['imagen'];
			if(empty($foto) || is_null($foto)){
				$foto = 'default.jpg';
			}

				$pagado = "Sin pagar";
			if($aprobado == 'Aprobada'){
				
				$pagado = '$ '.number_format((float)$value['pagado'],2,'.',',').' MXN';
			
			}


			$tipopago = 'Sin pagar';


			if($aprobado == "Aprobada"){
				if($value['tipo_pago'] == 1){
				$tipopago = "Total";
			}else if($value['tipo_pago'] == 2){
				$tipopago = "Parcial";
			}
			}
			



			
			$perfil = $value['perfil'];
			$urlarchivo = HOST.'/assets/recibos/'.$value['recibo'];
			?>
				<tr id="<?php echo $value['solicitud'] ?>">
					<td><?php echo $key; ?></td>
					<td>
					<div class="user user-md">
						<a href="<?php echo HOST."/socio/".$value['username']; ?>" target="_blank"><img src="<?php echo $urlimg.$foto;?>"></a>
					</div>
					</td>
					<td><?php echo $nombre; ?></td>
					<td><?php echo $perfil ?></td>
					<td><?php echo '$'.$monto.' MXN'; ?></td>
					<td><?php echo $pagado; ?></td>
					<td><?php echo $tipopago; ?></td>
					<td><?php echo $aprobado; ?></td>
					<td><?php echo $fecha ?></td>
					<td>
						<?php 
						if($aprobado == 'No aprobada'){	 ?>
							<button type="button" data-pago="<?php echo $pago; ?>" class="btn btn-primary aprobar" data-path="<?php echo  _safe($_SERVER['REQUEST_URI']); ?>" data-id="<?php echo $value['solicitud']?>" data-perfil="<?php echo $perfil; ?>" data-fecha="<?php echo $fecha; ?>" data-monto="<?php echo '$ '.$monto.' MXN'; ?>"  > <i class="fa fa-check" ></i> Pagar</button>
						<?php }else{?>
								<button type="button" name='descargar' class="btn btn-warning " style="color:white !important;"><i class="fa fa-file-pdf-o"></i><a href="<?php echo $urlarchivo; ?>" target="_blank">Descargar</a></button>
						<?php  } ?>						
					</td>
				</tr>
			<?php  

		}
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

				case 'Franquiciatario':

					$sql = "INSERT INTO retirocomisionfranquiciatario(negocio,usuario,id_retiro,condicion)values('Reembolso de resto por pago parcial','Resto pago parcial',:retiro,2)";
						
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


						$sql = "SELECT balance from balancefranquiciatario where id_franquiciatario =:fr order by id desc limit 1";

						$stm = $this->con->prepare($sql);

						$stm->bindParam(':fr',$idfranquiciatario);

						$stm->execute();

						$ultimobalance = $stm->fetch(PDO::FETCH_ASSOC)['balance'];



						$nuevobalance =  $ultimobalance + $montoregresar;


						$sql = "INSERT into balancefranquiciatario(balance,id_franquiciatario,comision,id_retiro)values(:balance,:franquiciatario,:comision,:retiro)";

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


