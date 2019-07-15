<?php 
namespace negocio\libs;
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
use assets\libs\connection as conec ;
use \Dompdf\Dompdf as pdf;
use \Dompdf\Options;
use \Dompdf\Positioner;
use PDO;

/**
 * @author Crespo jhonatan 
 * @since 12/06/2019
 */
class Restaurant {

	private $con;

	private $horario = array(
								'Lunes' =>false, 
								'Martes' =>false, 
								'Miercoels' =>false, 
								'Jueves' =>false, 
								'Viernes' =>false, 
								'Sabado' =>false, 
								'Domingo' =>false 
							);
	
	private $mesas = null;
	private $hora = null;

	private $restaurant = array(
		'id' => null,
		'nombre'=> null,
	);



	private $datos = array(
			'dias' =>null,
			'mesas' => null,
			'hora' =>null
			 );

	private $errors = array(
					'referral' => null,
					'username' => null,
					'error'    => null,
					'warning'  => null
						 );

	private $catalogo = null;
	
	private $busqueda = array(
							'fechainicio' => null,
							'fechafin'    => null,
							'datestart'   => null,
							'dateend'     => null
							);
	public $filtro = 0;
	public $hotel = null;

	private $error = array('notificacion' => null, 'fechainicio' => null, 'fechafin' => null);

	function __construct(conec $conecction){
		
		$this->con = $conecction->con;

		if(isset($_SESSION['business']['id_negocio'])){
			$this->restaurant['id'] = $_SESSION['business']['id_negocio'];
			$this->cargarDatos();
			$this->cargar();
		}
	}

	public function getDatos(array $datos){

		$this->cargar($datos['filtro'],$datos);

		for ($i=0; $i < count($this->catalogo); $i++) { 

			if(empty($this->catalogo[$i]['hotel'])){
				$this->catalogo[$i]['hotel'] = 'directo (Sin hotel)';
			}

			if(!empty($valores['nombre'])){
				$this->catalogo[$i]['username']  =  $this->catalogo[$i]['nombre'];
			}
			
			$this->catalogo[$i]['btncancelar'] = '';
			if($this->catalogo[$i]['status'] == 0){
				$this->catalogo[$i]['btncancelar'] = '<button type="button" class="cancelar-reserva" data-toggle="tooltip" data-placement="left" title="Cancelar reservaci&oacute;n" data-idcancel="'.$this->catalogo[$i]['id'].'"><i class="fa fa-remove"></i></button>';
			}

			switch ($this->catalogo[$i]['status']) {
			
				case 0:
					$this->catalogo[$i]['status'] = "<strong class='sinconfirmar'>Agendada</strong>";
				break;
				
				case 1:
					$this->catalogo[$i]['status'] = "<strong class='consumada'>Consumada</strong>";
				break;
						
				case 2:
					$this->catalogo[$i]['status'] = "<strong class='confirmada'>Confirmada</strong>";
				break;
						
				case 3:
					$this->catalogo[$i]['status'] = "<strong class='cancelada'>Cancelada</strong>";
				break;
						
				case 4:
					$this->catalogo[$i]['status'] = "<strong class='cancelada'>Desfasada</strong>";
				break;
			}

			if(empty($this->catalogo[$i]['observacion'])){
				$this->catalogo[$i]['observacion'] = 'Sin Observaciones';
			}else{
				$this->catalogo[$i]['observacion'] = '<strong class="observaciones" data-observacion="'._safe($this->catalogo[$i]['observacion']).'">Observaciones</strong>';
			}


			

		}

		return $this->catalogo;
	}

	public function desfaseReserv(){

		$sql = "UPDATE reservacion set status = 4 where id_restaurant = :negocio and fecha > ADDDATE(fecha,INTERVAL 5 day)";

		$stm = $this->con->prepare($sql);
		$stm->bindParam(':negocio', $this->restaurant['id'],PDO::PARAM_INT);
		$stm->execute();
	}



	public function getCantidadReservaciones(){

		$sql = "SELECT count(*) as cantidad from reservacion where id_restaurant = :negocio and fecha = :fecha";

		$stm = $this->con->prepare($sql);
		$stm->execute(array(':negocio'=>$this->restaurant['id'],
								':fecha'=>date('Y-m-d')));

		return $stm->fetch(PDO::FETCH_ASSOC)['cantidad'];

	}
	public function cargar(int $filtro = 0,array $datos = null){

		$this->filtro = $filtro;

		$sql2 = "SELECT h.nombre from hotel as h where h.id = :hotel";

		$stm  = $this->con->prepare($sql2);
		$stm->bindParam(':hotel',$datos['hotel']);
		$stm->execute();
		$this->hotel = $stm->fetch(PDO::FETCH_ASSOC)['nombre'];



		switch ($filtro) {
			case 0:
				if($datos['hotel'] != 0){
					$sql = "SELECT concat(r.fecha,' ',r.hora) as fecha, h.nombre as hotel, u.username,concat(u.nombre,' ',u.apellido) as nombre,
					r.id,r.numeropersona,r.status,r.observacion
					from reservacion as r 
					join negocio as n on r.id_restaurant = n.id_negocio
					join usuario as u on r.usuario_solicitante = u.id_usuario
					left join hotel as h on r.id_hotel = h.id 
					where r.id_restaurant = :negocio and h.id = :hotel and  r.fecha = :fecha order by r.id desc";

					$datos = array(	':fecha' => date('Y-m-d'),
									':negocio' => $this->restaurant['id'],
									':hotel'   => $datos['hotel']
									);
					
				}else{
					$sql = "SELECT concat(r.fecha,' ',r.hora) as fecha, h.nombre as hotel, u.username,concat(u.nombre,' ',u.apellido) as nombre,
					r.id,r.numeropersona,r.status,r.observacion
					from reservacion as r 
					join negocio as n on r.id_restaurant = n.id_negocio
					join usuario as u on r.usuario_solicitante = u.id_usuario
					left join hotel as h on r.id_hotel = h.id 
					where r.id_restaurant = :negocio and r.fecha = :fecha order by r.id desc";

					$datos = array(	':fecha' => date('Y-m-d'),
									':negocio' => $this->restaurant['id'],
									
									);
				}

				try {
						$stm = $this->con->prepare($sql);
						$stm->execute($datos);
					} catch (\PDOException $e) {
					// echo $e->getMessage();
					}

				break;

			case 1:

				if($datos['hotel'] != 0){
					$sql = "SELECT concat(r.fecha,' ',r.hora) as fecha, h.nombre as hotel, u.username,concat(u.nombre,' ',u.apellido) as nombre,
					r.id,r.numeropersona,r.status,r.observacion
					from reservacion as r 
					join negocio as n on r.id_restaurant = n.id_negocio
					join usuario as u on r.usuario_solicitante = u.id_usuario
					left join hotel as h on r.id_hotel = h.id 
					where r.id_restaurant = :negocio and h.id = :hotel and  day(r.fecha) = :diaanterior order by r.id desc";

					$datos = array(	':diaanterior' => date('d') -1,
									':negocio' => $this->restaurant['id'],
									':hotel'   => $datos['hotel']
									);
				}else{
					$sql = "SELECT concat(r.fecha,' ',r.hora) as fecha, h.nombre as hotel, u.username,concat(u.nombre,' ',u.apellido) as nombre,
					r.id,r.numeropersona,r.status,r.observacion
					from reservacion as r 
					join negocio as n on r.id_restaurant = n.id_negocio
					join usuario as u on r.usuario_solicitante = u.id_usuario
					left join hotel as h on r.id_hotel = h.id 
					where r.id_restaurant = :negocio and  day(r.fecha) = :diaanterior order by r.id desc";

					$datos = array(	':diaanterior' => date('d') -1,
									':negocio' => $this->restaurant['id']
									
									);
				}

					try {
						$stm = $this->con->prepare($sql);
						$stm->execute($datos);
					} catch (\PDOException $e) {
					// echo $e->getMessage();
					}



				break;
			
			case 2:

				if($datos['hotel'] != 0){

					$sql = "SELECT concat(r.fecha,' ',r.hora) as fecha, h.nombre as hotel, u.username,concat(u.nombre,' ',u.apellido) as nombre,
					r.id,r.numeropersona,r.status,r.observacion
					from reservacion as r 
					join negocio as n on r.id_restaurant = n.id_negocio
					join usuario as u on r.usuario_solicitante = u.id_usuario
					left join hotel as h on r.id_hotel = h.id 
					where r.id_restaurant = :negocio and h.id = :hotel and  month(r.fecha) = :mes order by r.id desc";

					$datos = array(	':mes' => date('m'),
									':negocio' => $this->restaurant['id'],
									':hotel'   => $datos['hotel']
									);
				}else{
					$sql = "SELECT concat(r.fecha,' ',r.hora) as fecha, h.nombre as hotel, u.username,concat(u.nombre,' ',u.apellido) as nombre,
					r.id,r.numeropersona,r.status,r.observacion
					from reservacion as r 
					join negocio as n on r.id_restaurant = n.id_negocio
					join usuario as u on r.usuario_solicitante = u.id_usuario
					left join hotel as h on r.id_hotel = h.id 
					where r.id_restaurant = :negocio and  month(r.fecha) = :mes order by r.id desc";

					$datos = array(	':mes' => date('m'),
									':negocio' => $this->restaurant['id'],
									
									);
				}
					try {
						$stm = $this->con->prepare($sql);
						$stm->execute($datos);
					} catch (\PDOException $e) {
						// echo $e->getMessage();
					}
				break;

			case 3:

				if($datos['hotel'] != 0){

					$sql = "SELECT concat(r.fecha,' ',r.hora) as fecha, h.nombre as hotel, u.username,concat(u.nombre,' ',u.apellido) as nombre,
					r.id,r.numeropersona,r.status,r.observacion
					from reservacion as r 
					join negocio as n on r.id_restaurant = n.id_negocio
					join usuario as u on r.usuario_solicitante = u.id_usuario
					left join hotel as h on r.id_hotel = h.id 
					where r.id_restaurant = :negocio and h.id = :hotel and  month(r.fecha) = :mes order by r.id desc";

					$datos = array(	':mes' => date('m') -1,
									':negocio' => $this->restaurant['id'],
									':hotel'   => $datos['hotel']
									);
				}else{
					$sql = "SELECT concat(r.fecha,' ',r.hora) as fecha, h.nombre as hotel, u.username,concat(u.nombre,' ',u.apellido) as nombre,
					r.id,r.numeropersona,r.status,r.observacion
					from reservacion as r 
					join negocio as n on r.id_restaurant = n.id_negocio
					join usuario as u on r.usuario_solicitante = u.id_usuario
					left join hotel as h on r.id_hotel = h.id 
					where r.id_restaurant = :negocio and  month(r.fecha) = :mes order by r.id desc";

					$datos = array(	':mes' => date('m') -1,
									':negocio' => $this->restaurant['id']
									);
				}

				try {
						$stm = $this->con->prepare($sql);
						$stm->execute($datos);
					} catch (\PDOException $e) {
						// echo $e->getMessage();
					}

				break;

			case 4:
				$this->busqueda['fechainicio'] = $datos['rango1'];
				$this->busqueda['fechafin'] = $datos['rango2'];

					if($datos['hotel'] != 0){

						$sql = "SELECT concat(r.fecha,' ',r.hora) as fecha, h.nombre as hotel, u.username,concat(u.nombre,' ',u.apellido) as nombre,
					r.id,r.numeropersona,r.status,r.observacion
					from reservacion as r 
					join negocio as n on r.id_restaurant = n.id_negocio
					join usuario as u on r.usuario_solicitante = u.id_usuario
					left join hotel as h on r.id_hotel = h.id 
					where r.id_restaurant = :negocio and h.id = :hotel and  (r.fecha between :fecha1 and :fecha2) order by r.id desc";

					$datos = array(	':fecha1' => $this->busqueda['fechainicio'], 
									':fecha2' => $this->busqueda['fechafin'],

									':negocio' => $this->restaurant['id'],
									':hotel'   => $datos['hotel']
									);

					}else{
						$sql = "SELECT concat(r.fecha,' ',r.hora) as fecha, h.nombre as hotel, u.username,concat(u.nombre,' ',u.apellido) as nombre,
					r.id,r.numeropersona,r.status,r.observacion
					from reservacion as r 
					join negocio as n on r.id_restaurant = n.id_negocio
					join usuario as u on r.usuario_solicitante = u.id_usuario
					left join hotel as h on r.id_hotel = h.id 
					where r.id_restaurant = :negocio and  r.fecha between :fecha1 and :fecha2 order by r.id desc";

					$datos = array(	':fecha1' => $this->busqueda['fechainicio'], 
									':fecha2' => $this->busqueda['fechafin'],
									':negocio' => $this->restaurant['id'],
									);

					}
					try {
						$stm = $this->con->prepare($sql);
						$stm->execute($datos);
					} catch (\PDOException $e) {
						// echo $e->getMessage();
					}

				break;
		
		}
					
				$this->catalogo = $stm->fetchAll(PDO::FETCH_ASSOC);
		
	}

	private function cargarDatos(){


		$sql = "SELECT h.dia, rs.mesas,rs.hora from negocio as r join restaurant_reservacion as rs on r.id_negocio = rs.id_restaurant
						join negocio_horario as h on rs.id_horario = h.id_horario where r.id_negocio = :restaurant";


		$stm = $this->con->prepare($sql);
		$stm->bindParam(':restaurant',$this->restaurant['id'],PDO::PARAM_INT);

		$stm->execute();

		$fila = $stm->fetch(PDO::FETCH_ASSOC);


		$datos['dias'] = $fila['dia'];
		$datos['mesas'] = $fila['mesas'];
		$datos['hora'] = $fila['hora'];

	}

		public function getNotificacion(){
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

	public function getDisponibilidad(){

		$sql = "SELECT rs.id_horario,h.dia,rs.mesas,rs.hora,rs.condicion,rs.id FROM restaurant_reservacion as rs join negocio_horario as h on rs.id_horario = h.id_horario 
					where rs.id_restaurant = :restaurant and h.forreserva = 1";

		$stm  = $this->con->prepare($sql);

		$stm->bindParam(':restaurant',$this->restaurant['id'],PDO::PARAM_INT);

		$stm->execute();


		$dias = array(
			'lunes' => array(
				'horas'=>array(
					'hora'  =>null,
					'mesas' =>null,
					'id'=>null
				),
				
			),
			'martes' => array(
				'horas'=>array(
					'hora'  =>null,
					'mesas' =>null,
					'id'    =>null
				),
			), 
			'miercoles' => array(
				'horas'=>array(
					'hora'  =>null,
					'mesas' =>null,
					'id'    =>null
				),
			), 
			'jueves' => array(
				'horas'=>array(
					'hora'  =>null,
					'mesas' =>null,
					'id'    =>null
				),
			), 
			'viernes' => array(
				'horas'=>array(
					'hora'  =>null,
					'mesas' =>null,
					'id'    =>null
				),
			), 
			'sabado' => array(
				'horas'=>array(
					'hora'  =>null,
					'mesas' =>null,
					'id'    =>null
				),
			),
			'domingo' => array(
				'horas'=>array(
					'hora'  =>null,
					'mesas' =>null,
					'id'    =>null
				),
			),   
		);

		

		while($fila = $stm->fetch(PDO::FETCH_ASSOC)) {
			
			switch ($fila['dia']) {
				case '1':	

					$dias["lunes"]['horas']['hora'][] = $fila['hora'];
					settype($fila['mesas'], 'integer');
					$dias["lunes"]['horas']['mesas'][] = $fila['mesas'];
						settype($fila['id'], 'integer');
					$dias["lunes"]['horas']['id'][] = $fila['id'];
					break;

				case '2':
					
					$dias['martes']['horas']['hora'][] = $fila['hora'];
					
					$mesas = $fila['mesas'];
					settype($mesas, 'integer');
					$dias['martes']['horas']['mesas'][] = $mesas;
						settype($fila['id'], 'integer');
					$dias["martes"]['horas']['id'][] = $fila['id'];
					break;
				case '3':
					
					$dias["miercoles"]["horas"]['hora'][] = $fila['hora'];
					settype($fila['mesas'], 'integer');
					$dias["miercoles"]["horas"]["mesas"][] = $fila['mesas'];
						settype($fila['id'], 'integer');
					$dias["miercoles"]['horas']['id'][] = $fila['id'];
					break;

				case '4':
					$dias["jueves"]["horas"]['hora'][] = $fila['hora'];
					settype($fila['mesas'], 'integer');
					$dias["jueves"]['horas']['mesas'][] = $fila['mesas'];
					settype($fila['id'], 'integer');
					$dias["jueves"]['horas']['id'][] = $fila['id'];

					break;
				case '5':
					$dias['viernes']['horas']['hora'][] = $fila['hora'];
					settype($fila['mesas'], 'integer');
					$dias['viernes']['horas']['mesas'][] = $fila['mesas'];
						settype($fila['id'], 'integer');
					$dias["viernes"]['horas']['id'][] = $fila['id'];
					break;

				case '6':
					$dias['sabado']['horas']['hora'][]= $fila['hora'];
					settype($fila['mesas'], 'integer');
					$dias['sabado']['horas']['mesas'][] = $fila['mesas'];
						settype($fila['id'], 'integer');
					$dias["sabado"]['horas']['id'][] = $fila['id'];
					break;

				case '7':
					$dias['domingo']['horas']["hora"][] = $fila['hora'];
					settype($fila['mesas'], 'integer');
					$dias['domingo']['horas']["mesas"][] = $fila['mesas'];
						settype($fila['id'], 'integer');
					$dias["domingo"]['horas']['id'][] = $fila['id'];
					break;
				
				default:
					# code...
					break;
			}
		}

		return $dias;
		
	}




	public function eliminarhora(int $idhora){


		$sql = "DELETE from restaurant_reservacion where id =:idhora";
		$this->con->beginTransaction();
		try {
			

			$stm = $this->con->prepare($sql);
			$stm->execute(array(':idhora'=>$idhora));
			$this->con->commit();

		} catch (PDOException $e) {
			$this->con->rollBack();
			return false;
		}

		return true;


	}


	public function cambiarnumeromesas(int $idhora,int $mesas){


		$sql = "UPDATE restaurant_reservacion set mesas=:mesas where id=:id";
		$this->con->beginTransaction();
		try {
			
			$stm = $this->con->prepare($sql);
			$stm->execute(array(':mesas'=>$mesas,':id'=>$idhora));
			$this->con->commit();
		} catch (PDOException $e) {
			$this->con->rollBack();
			return false;
		}
		return true;
	}

	public function asignarhora($hora,int $mesas, array $dias){


		$this->con->beginTransaction();


		$sql1 = "INSERT INTO negocio_horario(id_negocio,dia,forreserva)values(:negocio,:dia,1)";


		try {

			foreach ($dias as $key => $value) {
					$stm =$this->con->prepare($sql1);
				

				switch ($value) {

					case 'lunes':
				
						$stm->execute(array(':negocio'=>$this->restaurant['id'],':dia'=>1));
						break;
					case 'martes':
							$stm->execute(array(':negocio'=>$this->restaurant['id'],':dia'=>2));
						break;
					case 'miercoles':
							$stm->execute(array(':negocio'=>$this->restaurant['id'],':dia'=>3));
						break;
					case 'jueves':
							$stm->execute(array(':negocio'=>$this->restaurant['id'],':dia'=>4));
						break;
					case 'viernes':
							$stm->execute(array(':negocio'=>$this->restaurant['id'],':dia'=>5));
						break;
					case 'sabado':
							$stm->execute(array(':negocio'=>$this->restaurant['id'],':dia'=>6));
						break;
					case 'domingo':
							$stm->execute(array(':negocio'=>$this->restaurant['id'],':dia'=>7));
						break;
					
					default:
						# code...
						break;
				}

				$stm->execute();

				$idhorario = $this->con->lastInsertId();

				$sql = "INSERT INTO restaurant_reservacion(id_horario,id_restaurant,mesas,hora) values(:horario,:negocio,:mesas,:hora)";

				$stmt = $this->con->prepare($sql);

				$stmt->bindParam(':horario',$idhorario,PDO::PARAM_INT);
				$stmt->bindParam(':negocio',$this->restaurant['id'],PDO::PARAM_INT);
				$stmt->bindParam(':mesas',$mesas,PDO::PARAM_INT);
				$stmt->bindParam(':hora',$hora,PDO::PARAM_STR);

				$stmt->execute();



			}

			$this->con->commit();
			
		} catch (PDOException $e) {

			$this->con->rollBack();

			return false;
			
		}

		return true;

	}


	public function publicar(){


		$sql = "UPDATE restaurant_reservacion set condicion = 1 where id_restaurant=:negocio";
		$this->con->beginTransaction();
		try {
			$stm = $this->con->prepare($sql);

			$stm->bindParam(':negocio',$this->restaurant['id'],PDO::PARAM_INT);
			$stm->execute();
			$this->con->commit();
		} catch (PDOException $e) {
			$this->con->rollBack();
			return false;
		}


		return true;
	}


	public function desactivar(){


		$sql = "UPDATE restaurant_reservacion set condicion = 0 where id_restaurant=:negocio";
		$this->con->beginTransaction();
		try {
			$stm = $this->con->prepare($sql);

			$stm->bindParam(':negocio',$this->restaurant['id'],PDO::PARAM_INT);
			$stm->execute();
			$this->con->commit();
		} catch (PDOException $e) {
			$this->con->rollBack();
			return false;
		}


		return true;
	}


	public function getPublicacionStatus(int $negocio = null){


		if(!is_null($negocio)){
			$this->restaurant['id'] = $negocio;
		}


		$sql = "SELECT condicion from restaurant_reservacion where id_restaurant=:negocio GROUP BY id_restaurant";

		$stm = $this->con->prepare($sql);

		$stm->bindParam(':negocio',$this->restaurant['id'],PDO::PARAM_INT);

		$stm->execute();

		return $stm->fetch(PDO::FETCH_ASSOC);

	}


	public function getDisponibilidadDia($negocio = null){

		if(!is_null($negocio)){


			$sql = "SELECT h.dia FROM negocio_horario as h join restaurant_reservacion as rs on h.id_horario = rs.id_horario
							where h.forreserva = 1 and rs.id_restaurant = (SELECT id_negocio FROM negocio where nombre LIKE :negocio) and rs.condicion =1  GROUP BY h.dia";


			$stm = $this->con->prepare($sql);

			$stm->execute(array(':negocio'=>'%'.$negocio.'%'));

			$dia = array();

			$fila = [];
			
			while($row = $stm->fetch(PDO::FETCH_ASSOC)){

				$fila[] = $row['dia'];

			}

			// echo count($fila);/

			if(!in_array(1, $fila)){
				array_push($dia, 1);
			}
			if(!in_array(2,$fila)){
				array_push($dia, 2);
			}
			if(!in_array(3,$fila)){
				array_push($dia, 3);
			} 
			if(!in_array(4,$fila)){
				array_push($dia, 4);
			} 
			if(!in_array(5,$fila)){
				array_push($dia, 5);
			} 
			if(!in_array(6,$fila)){
				array_push($dia, 6);
			} 
			if(!in_array(7,$fila)){
				array_push($dia, 0);
			}


			 return $dia;
		}

	}





	public function getHoraDisponibles($negocio,$fecha,$dia,bool $viewnegocio = false){

		if($viewnegocio){
				$sql = "SELECT rs.mesas,rs.hora, rs.id FROM restaurant_reservacion as rs join negocio_horario as h on rs.id_horario = h.id_horario 
					where rs.id_restaurant = :negocio and h.dia = :dia and rs.condicion = 1
					order by rs.hora asc";
					
					
					
				$sql1 = "SELECT sum(r.numeropersona) as numeropersona, r.hora from reservacion r where r.id_restaurant = :negocio AND r.fecha = :fecha and r.status  = 0 group by r.hora";


				$stmt = $this->con->prepare($sql1);
				$stm = $this->con->prepare($sql);
					
				$stm->execute(array(':negocio'=>$negocio,':dia'=>$dia));
				$stmt->execute(array(':negocio'=>$negocio,':fecha'=>$fecha));

		}else{
				$sql = "SELECT rs.mesas,rs.hora, rs.id FROM restaurant_reservacion as rs join negocio_horario as h on rs.id_horario = h.id_horario 
					where rs.id_restaurant = (SELECT id_negocio FROM negocio where nombre like :negocio) and h.dia = :dia and rs.condicion = 1
					order by rs.hora asc";
					
					
				$sql1 = "SELECT sum(r.numeropersona) as numeropersona, r.hora from reservacion r where r.id_restaurant = (SELECT id_negocio FROM negocio where nombre like :negocio ) AND r.fecha = :fecha and r.status = 0 GROUP BY r.hora";
					
					
					
				$stmt = $this->con->prepare($sql1);
				$stm = $this->con->prepare($sql);
					
				$stm->execute(array(':negocio'=>$negocio,':dia'=>$dia));
				$stmt->execute(array(':negocio'=>'%'.$negocio.'%',':fecha'=>$fecha));
		}
        $horas = array(
				'hora'   => [],
				'mesas'  => [],
				'idhora' => []
         );         

        $pequeno = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $i = 0;

        while($fila = $stm->fetch(PDO::FETCH_ASSOC)){
        	
        	if($i < count($pequeno)){

        		while ($i < count($pequeno)) { 

        		if($fila['hora'] == $pequeno[$i]['hora']){
        			
					$horas['hora'][]   = $fila['hora'];
					$horas['mesas'][]  = $fila['mesas'] - $pequeno[$i]['numeropersona'];
					$horas['idhora'][] = $fila['id'];
					$i++;
					break;
        		}else{
        			$horas['hora'][]   = $fila['hora'];
					$horas['mesas'][]  = $fila['mesas'];
					$horas['idhora'][] = $fila['id'];
        			break;
        		}

        		}

        	}else{
        		$horas['hora'][]   = $fila['hora'];
				$horas['mesas'][]  = $fila['mesas'];
				$horas['idhora'][] = $fila['id'];
        	}

        	
        }
         return $horas;

	}



	public function Buscar($post){

		$this->setFecha1($post['date_start']);
		$this->setFecha2($post['date_end']);

		// $this->setFechainicio($post['date_start']);
		// $this->setFechafin($post['date_end']);

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

	public function report(array $datos = null,String $title =''){
		
		$this->getDatos($datos);
		ob_start();

		require_once($_SERVER['DOCUMENT_ROOT'].'/negocio/viewreports/reservaciones.php');

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

		$fecha1 = $this->busqueda['datestart'];
		
		$titulo = "Travel Points: Lista de reservaciones " .$fecha1;
		$dompdf->stream($titulo.'.pdf',$dato);

	}
}

 ?>