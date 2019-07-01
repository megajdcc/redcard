<?php 
namespace Hotel\models;
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
use \Dompdf\Dompdf as pdf;
use \Dompdf\Options;
use \Dompdf\Positioner;

use assets\libs\connection;

use PDO;
/**
 * @author Crespo Jhonatan
 * @since 09/06/2019
 */

class Reservacion 
{

	private $conec = null;


	//  Propidades de clase 


	private $id = 0;
	private $idsocio = 0 ;
	private $socioname = null;
	private $idrestaurant = 0;
	private $fecha = null;
	private $numpersonas = null;
	private $observaciones;
	private $hora = null;
	private $usuarioregistrante = 0 ;
	private $hotel = 0 ;
	private $status = array(
				'confirmadas'   => 1,
				'consumada'     => 2,
				'sin registrar' => 3,
				'cacelada'      => 4
		);


	private $catalogo = null;
	private $errors = array(
					'referral' => null,
					'username' => null,
					'error'    => null,
					'warning'  => null
						 );

	private $busqueda = array(
								'fechainicio' => null,
								'fechafin'    => null,
								'datestart'   => null,
								'dateend'     => null
							);
	
	private $error = array('notificacion' => null, 'fechainicio' => null, 'fechafin' => null);

	/**
	 * [__construct description]
	 * @param connection $conec Una instancia de la clase connection, para la base de dato ... 
	 */
	function __construct(connection $conec,bool $foruser = false){
		$this->conec = $conec->con;
		$this->usuarioregistrante = $_SESSION['user']['id_usuario'];

		if(!$foruser){
			$this->hotel = $_SESSION['id_hotel'];
			$this->cargar();
		}
	}


	// GETTERS Y SETTERS 





	public function cargar(){

		


		if(!empty($this->busqueda['fechainicio']) && !empty($this->busqueda['fechafin'])){

				$sql = "SELECT r.usuario_registrante,r.id,r.creado,n.nombre as negocio,u.username as usuario,
					r.status,concat(r.fecha,' ',r.hora) as fecha from reservacion as r 
					join negocio as n on r.id_restaurant = n.id_negocio
					join usuario as u on r.usuario_solicitante = u.id_usuario
					where r.id_hotel = :hotel and r.creado between :fecha1 and :fecha2";
					
					
				$sql1 = "SELECT u.username,r.id from usuario as u join reservacion as r on u.id_usuario = r.usuario_registrante where r.id_hotel = :hotel";
					
				$stmt = $this->conec->prepare($sql);
				$stmt->bindParam(':hotel',$this->hotel,PDO::PARAM_INT);
				$stmt->bindParam(':fecha1',$this->busqueda['fechainicio']);
				$stmt->bindParam(':fecha2',$this->busqueda['fechafin']);
				$stmt->execute();
					
				$stm = $this->conec->prepare($sql1);
				$stm->bindParam(':hotel',$this->hotel,PDO::PARAM_INT);
				$stm->execute();
					
				$this->catalogo = $stmt->fetchAll(PDO::FETCH_ASSOC);


		}else{
				$sql = "SELECT r.usuario_registrante,r.id,r.creado,n.nombre as negocio,u.username as usuario,
					r.status,concat(r.fecha,' ',r.hora) as fecha from reservacion as r 
					join negocio as n on r.id_restaurant = n.id_negocio
					join usuario as u on r.usuario_solicitante = u.id_usuario
					where r.id_hotel = :hotel";
					
					
				$sql1 = "SELECT u.username,r.id from usuario as u join reservacion as r on u.id_usuario = r.usuario_registrante where r.id_hotel = :hotel";
					
				$stmt = $this->conec->prepare($sql);
				$stmt->bindParam(':hotel',$this->hotel,PDO::PARAM_INT);
				$stmt->execute();
					
				$stm = $this->conec->prepare($sql1);
				$stm->bindParam(':hotel',$this->hotel,PDO::PARAM_INT);
				$stm->execute();
					
				$this->catalogo = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		
		
	}

	public function reservar(array $datos){

		$this->numpersonas   = $datos['totalperson'];

		settype($this->numpersonas, 'integer');


		if(isset($datos['observacion'])){
			$this->observaciones = $datos['observacion'];
		}
		
		$this->fecha         = $datos['fechaseleccionada'];
		$this->hora          = $datos['horaseleccionada'];
		$this->idrestaurant  = $datos['negocio'];

		if(isset($datos['referral'])){
			$this->setSolicitante($datos['referral']);
		}else{
			$this->setSolicitante();
		}
		

		// echo var_dump($datos);
		
		if(isset($datos['peticion']) && $datos['peticion'] == 'reservar'){


			$sql = "INSERT INTO reservacion(fecha,numeropersona,id_restaurant,usuario_solicitante,hora,observacion)
							values(:fecha,:numeropersona,:restaurant,:solicitante,:hora,:observacion)";
			$this->conec->beginTransaction();
			try {
				$stm = $this->conec->prepare($sql);
				$stm->execute(array(
							':fecha'         => $this->fecha,
							':numeropersona' => $this->numpersonas,
							':restaurant'    => $this->idrestaurant,
							':solicitante'   => $this->idsocio,
							':hora'          => $this->hora,
							':observacion'   => $this->observaciones
							));
							
				$this->conec->commit();
				} catch (\PDOException $e) {
					echo $e->getMessage();
					$this->conec->rollBack();
					return false;
				}

				$_SESSION['notification']['success'] = " La reservación se ha registrado exitosamente";
				return true;


		}else{
			$sql = "INSERT INTO reservacion(fecha,numeropersona,observacion,id_hotel,id_restaurant,usuario_registrante,usuario_solicitante,hora)
							values(:fecha,:numeropersona,:observacion,:hotel,:restaurant,:registrante,:solicitante,:hora)";
			$this->conec->beginTransaction();
							
			try {

				$stm = $this->conec->prepare($sql);
				$stm->execute(array(
					':fecha'         => $this->fecha,
					':numeropersona' => $this->numpersonas,
					':observacion'   => $this->observaciones,
					':hotel'         => $this->hotel,
					':restaurant'    => $this->idrestaurant,
					':registrante'   => $this->usuarioregistrante,
					':solicitante'   => $this->idsocio,
					':hora'          => $this->hora
					));
							
				$this->conec->commit();

				} catch (PDOException $e) {
					$this->conec->rollBack();
					return false;
				}
					
					$_SESSION['notification']['success'] = " La reservación se ha registrado exitosamente";
					header('location: '.HOST.'/Hotel/reservaciones/');
					die();
					return true;

		}
	
	}


	public function getReserva(){
		
		$sql1 = "SELECT u.username,r.id from usuario as u join reservacion as r on u.id_usuario = r.usuario_registrante 
					where r.usuario_registrante = :user";

		foreach($this->catalogo as $key => $valores) {
			
			$creado   = _safe($valores['creado']);
			$negocio  = _safe($valores['negocio']);
			$usuario  = _safe($valores['usuario']);
			$solicita = _safe($valores['usuario']);
			$status   = _safe($valores['status']);
			$fecha    = _safe($valores['fecha']);	




			switch ($status) {
				case 0:
						$status = 'Sin confirmar';
						$clas = 'sinconfirmar';
					break;
				case 1:
						$status = 'Consumada';
						$clas = 'consumada';
					break;
				case 2:
						$status = 'Confirmada';
						$clas = 'confirmada';
					break;
				case 3:
						$status = 'Cancelada';
						$clas = 'cancelada';
					break;
				
				default:
					# code...
					break;
			}

			$stm = $this->conec->prepare($sql1);
			$stm->bindParam(':user',$valores['usuario_registrante'],PDO::PARAM_INT);
			$stm->execute();

			$registrante = $stm->fetch(PDO::FETCH_ASSOC)['username'];	





			?>

			<tr id="<?php echo $valores['id']?>">
				
				<td><?php echo $creado ?></td>
				<td>
					<?php echo $negocio ?>
				</td>
				<td><?php echo $usuario; ?></td>
				<!-- <td><?php// echo $email; ?></td> -->
				<td><?php echo $registrante; ?></td>
				
				<td><strong class="<?php echo $clas ?>">
					<?php echo $status;?>
				</strong>
					</td>
				<td><?php echo $fecha ?></td>
				<td>

					<?php if($status == 'Sin confirmar' || $status == 'Confirmada'){?>
						<button type="button" class="btn btn-danger cancelar" data-toggle="tooltip" title="Cancelar reservación" data-id="<?php echo $valores['id'] ?>" data-placement="left"><i class="fa fa-close"></i></button>
					 <?php  }?>
				</td>
					
				
            </tr>

            	
			<?php
		}

	}


	public function cancelar(int $idreserva){


		$sql = "DELETE FROM reservacion where id = :reserva";

		$this->conec->beginTransaction();

		try {
			$stm = $this->conec->prepare($sql);
			$stm->bindParam(':reserva',$idreserva,PDO::PARAM_INT);
			$stm->execute();

			$this->conec->commit();

		} catch (PDOException $e) {
			$this->connec->rollBack();
			return false;
		}
		return true;
	}

	public function setSolicitante($solicitante  = null){

		if(!is_null($solicitante)){
			$referral = trim($solicitante);
			if(!preg_match('/^[a-zA-Z0-9]+$/ui',$referral)){
				// $this->errors['referral'] = 'The username must only contain letters and numbers. Special characters including accents are not allowed.';
				$this->errors['referral'] = 'El nombre de usuario debe contener solo caracteres alfanuméricos.';
				$this->socioname = $referral;
				return $this;
			}
			$query = "SELECT id_usuario FROM usuario WHERE username = :username";
			try{
				$stmt = $this->conec->prepare($query);
				$stmt->bindValue(':username', $referral, PDO::PARAM_STR);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				$this->idsocio = $row['id_usuario'];
				return $this;
			}
			$this->errors['referral'] = 'El nombre de usuario es incorrecto o no existe.';
			$this->socioname = $referral;
			return $this;
		}else{
			$this->idsocio = $_SESSION['user']['id_usuario']; 
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
		if($this->errors['warning']){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-warning" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->error['warning']).'
			</div>';
		}
		if($this->errors['error']){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->errors['error']).'
			</div>';
		}
		return $html;
	}


	public function report(array $post){

		$this->setFechainicio($post['start']);
		$this->setFechafin($post['end']);

		$this->cargar();
		

		ob_start();
		require_once($_SERVER['DOCUMENT_ROOT'].'/Hotel/viewreports/reservaciones.php');

		$context = stream_context_create([
				'ssl'=>[
					'verify_peer' => FALSE,
					'verify_peer_name' =>FALSE,
					'allow_self_signed' => TRUE
				]
			]);
	
		$html = ob_get_clean();
		$option = new Options();
		$option->isPhpEnabled(true);
		$option->isRemoteEnabled(true);
		$option->setIsHtml5ParserEnabled(true);
			
		$dompdf = new pdf($option);
		$dompdf->setHttpContext($context);
		$dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'landscape');
		$dompdf->render();
		$dato = array('Attachment' => 0);

		$fecha1 = date('M-Y', strtotime($this->busqueda['fechainicio']));
		
		$titulo = "Travel Points: Lista de reservaciones " .$fecha1;
		$dompdf->stream($titulo.'.pdf',$dato);

	}

	private function setFechainicio($datetime = null){

		if($datetime){
			$datetime = str_replace('/', '-', $datetime);
			$datetime = strtotime($datetime);
			if(!$datetime){
				$this->error['fechainicio'] = 'Formato de fecha y hora incorrecto. Utiliza la herramienta.';
				return false;
			}
			$datetime = date("Y-m-d H:i:s", $datetime);
			$this->busqueda['fechainicio'] = $datetime;
			return true;
		}
		$this->error['fechainicio'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setFechafin($datetime = null){
		
		if($datetime){
			$datetime = str_replace('/', '-', $datetime);
		
			$datetime = strtotime($datetime);
			if(!$datetime){
				$this->error['fechafin'] = 'Formato de fecha y hora incorrecto. Utiliza la herramienta.';
				return false;
			}
			$datetime = date("Y-m-d H:i:s", $datetime);
				
			$this->busqueda['fechafin'] = $datetime;
			return true;
		}
		$this->error['fechafin'] = 'Este campo es obligatorio.';
		return false;
	}

	public function Buscar($post){

		$this->setFecha1($post['date_start']);
		$this->setFecha2($post['date_end']);

		$this->setFechainicio($post['date_start']);
		$this->setFechafin($post['date_end']);

		$this->cargar();

	}

	private function setFecha1($fecha){
		 $this->busqueda['datestart'] = $fecha;
	}

	private function setFecha2($fecha){
		 $this->busqueda['dateend'] = $fecha;
	}


	public function getFecha1(){
		return $this->busqueda['datestart'];
	}

	public function getFecha2(){
		return $this->busqueda['dateend'];
	}


}


 ?>