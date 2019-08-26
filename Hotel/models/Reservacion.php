<?php 
namespace Hotel\models;
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
use \Dompdf\Dompdf as pdf;
use \Dompdf\Options;
use \Dompdf\Positioner;

use assets\libs\connection;
use CURL;

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
	private $telefonosocio = null;
	private $telefononegocio = null;
	private $mensaje = '';
	private $status = array(
				'confirmadas'   => 1,
				'consumada'     => 2,
				'sin registrar' => 3,
				'cacelada'      => 4
		);


	private $catalogo = array('');

	private $errors = array(
					'referral' => null,
					'username' => null,
					'error'    => null,
					'warning'  => null
						 );

	public $buttonimpresion = '';

	private $busqueda = array(
								'fechainicio' => null,
								'fechafin'    => null,
								'datestart'   => null,
								'dateend'     => null
							);
	
	public $ticket = array(
		'nombrecompleto'    => '',
		'username'          => '',
		'negocio'           => '',
		'direccion-negocio' => '',
		'fecha'             => '',
		'hora'              => '',
		'concierge'         => '',
		'ticket'            => '',
		'hotel'             =>'',
		'numeropersona'     => 0
	);

	public $filtro = 0;
	public $restaurant = null;

	private $error = array('notificacion' => null, 'fechainicio' => null, 'fechafin' => null);

	/**
	 * [__construct description]
	 * @param connection $conec Una instancia de la clase connection, para la base de dato ... 
	 */
	function __construct(connection $conec,bool $foruser = false, bool $impresion = false){
		$this->conec = $conec->con;

		if(isset($_SESSION['user']['id_usuario'])){
				$this->usuarioregistrante = $_SESSION['user']['id_usuario'];
		}else{
			return false;
		}
		
		if(!$foruser){

			if(isset($_SESSION['id_hotel'])){

				$this->hotel = $_SESSION['id_hotel'];

			}else if(isset($_SESSION['promotor'])){

				$this->hotel = $_SESSION['promotor']['hotel'];

			}
			
			$this->cargar();
		}


		if(!defined('PRINTNODE_APIKEY')){
			define('PRINTNODE_APIKEY','wJsRsXri4oSXWD_5IzT9XEGE-3rfw5fEZz0pmg4uXhI');
		}
		


		if($impresion){
			$this->cargarUltimo();
		}

		
	}


	// GETTERS Y SETTERS 





	public function cargar(int $filtro = 0 ,array $datos = null){

		$this->filtro = $filtro;

		$sql1 = "SELECT n.nombre from negocio as n where n.id_negocio = :negocio";

		$stm  = $this->conec->prepare($sql1);
		$stm->bindParam(':negocio',$datos['restaurant']);
		$stm->execute();

		$this->restaurant = $stm->fetch(PDO::FETCH_ASSOC)['nombre'];


		switch ($this->filtro) {
			case 0:
				
				if($datos['restaurant'] !=0 ){


					if(isset($_SESSION['promotor'])){
						$sql = "SELECT r.id_promotor as usuario_registrante,r.id,r.creado,n.nombre as negocio,u.username as username,concat(u.nombre,' ',u.apellido) as nombrecompleto,
						r.status,concat(r.fecha,' ',r.hora) as fecha,r.observacion,r.numeropersona from reservacion as r 
						join negocio as n on r.id_restaurant = n.id_negocio
						join usuario as u on r.usuario_solicitante = u.id_usuario
						where r.id_hotel = :hotel and r.fecha = :fecha and n.id_negocio  = :restaurant and r.id_promotor = :promotor";
							$datos = array( 
								':fecha'      => date('Y-m-d'),
								':hotel'      => $_SESSION['promotor']['hotel'],
								':restaurant' => $datos['restaurant'],
								':promotor'   => $_SESSION['promotor']['id']
							);
					}else{
						$sql = "SELECT r.usuario_registrante,r.id,r.creado,n.nombre as negocio,u.username as username,concat(u.nombre,' ',u.apellido) as nombrecompleto,
					r.status,concat(r.fecha,' ',r.hora) as fecha,r.observacion,r.numeropersona, r.id_promotor from reservacion as r 
					join negocio as n on r.id_restaurant = n.id_negocio
					join usuario as u on r.usuario_solicitante = u.id_usuario
					where r.id_hotel = :hotel and r.fecha = :fecha and n.id_negocio  = :restaurant";

					$datos = array(
								':fecha' =>date('Y-m-d'),
								':hotel' =>  $this->hotel,
								':restaurant' =>$datos['restaurant']
							);
					}


				}else{

					if(isset($_SESSION['promotor'])){
							$sql = "SELECT r.id_promotor as usuario_registrante,r.id,r.creado,n.nombre as negocio,u.username as username,concat(u.nombre,' ',u.apellido) as nombrecompleto,
						r.status,concat(r.fecha,' ',r.hora) as fecha,r.observacion,r.numeropersona 
						from reservacion as r 
						join negocio as n on r.id_restaurant = n.id_negocio
						join usuario as u on r.usuario_solicitante = u.id_usuario
						where r.id_hotel = :hotel and r.fecha = :fecha and r.id_promotor = :promotor";

						$datos = array(':fecha'      =>date('Y-m-d'),
							':hotel'    => $_SESSION['promotor']['hotel'],
							':promotor' => $_SESSION['promotor']['id']
							);
					}else{
						$sql = "SELECT r.usuario_registrante,r.id,r.creado,n.nombre as negocio,u.username as username,concat(u.nombre,' ',u.apellido) as nombrecompleto,
					r.status,concat(r.fecha,' ',r.hora) as fecha,r.observacion,r.numeropersona,r.id_promotor from reservacion as r 
					join negocio as n on r.id_restaurant = n.id_negocio
					join usuario as u on r.usuario_solicitante = u.id_usuario
					where r.id_hotel = :hotel and r.fecha = :fecha";
					
						$datos = array(':fecha'      =>date('Y-m-d'),':hotel' =>  $this->hotel);
					}
					
				
				}

					try {
						$stm = $this->conec->prepare($sql);
						$stm->execute($datos);
					} catch (\PDOException $e) {
					// echo $e->getMessage();
					}
					

				break;


			case 1:

				if($datos['restaurant'] !=0){


					if(isset($_SESSION['promotor'])){

						$sql = "SELECT r.id_promotor as usuario_registrante,r.id,r.creado,r.id_promotor, n.nombre as negocio,u.username as username,concat(u.nombre,' ',u.apellido) as nombrecompleto,
						r.status,concat(r.fecha,' ',r.hora) as fecha,r.observacion,r.numeropersona from reservacion as r 
						join negocio as n on r.id_restaurant = n.id_negocio
						join usuario as u on r.usuario_solicitante = u.id_usuario
						where r.id_hotel = :hotel and day(r.fecha) = :diaanterior and n.id_negocio  = :restaurant and r.id_promotor = :promotor";

							$datos = array(
							':diaanterior' =>date('d')-1,
							':restaurant'  =>$datos['restaurant'],
							':hotel'       =>  $_SESSION['promotor']['hotel'],
							':promotor'    => $_SESSION['promotor']['id']);
					}else{

							$sql = "SELECT r.usuario_registrante,r.id,r.creado,n.nombre as negocio,u.username as username,concat(u.nombre,' ',u.apellido) as nombrecompleto,
						r.status,concat(r.fecha,' ',r.hora) as fecha,r.observacion,r.numeropersona, r.id_promotor from reservacion as r 
						join negocio as n on r.id_restaurant = n.id_negocio
						join usuario as u on r.usuario_solicitante = u.id_usuario
						where r.id_hotel = :hotel and day(r.fecha) = :diaanterior and n.id_negocio  = :restaurant";

							$datos = array(
							':diaanterior'=>date('d')-1,
							':restaurant'=>$datos['restaurant'],
							':hotel' =>  $this->hotel
						);

					}
					

					

				}else{

					if(isset($_SESSION['promotor'])){

						$sql = "SELECT r.id_promotor as usuario_registrante,r.id,r.creado,n.nombre as negocio,u.username as username,concat(u.nombre,' ',u.apellido) as nombrecompleto,
						r.status,concat(r.fecha,' ',r.hora) as fecha,r.observacion,r.numeropersona, r.id_promotor from reservacion as r 
						join negocio as n on r.id_restaurant = n.id_negocio
						join usuario as u on r.usuario_solicitante = u.id_usuario
						where r.id_hotel = :hotel and day(r.fecha) = :diaanterior and r.id_promotor = :promotor";

							$datos = array(
								':diaanterior' =>date('d')-1,
								':hotel'       =>  $_SESSION['promotor']['hotel'],
								':promotor'    => $_SESSION['promotor']['id']);

					}else{

						$sql = "SELECT r.usuario_registrante,r.id,r.creado,n.nombre as negocio,u.username as username,concat(u.nombre,' ',u.apellido) as nombrecompleto,
						r.status,concat(r.fecha,' ',r.hora) as fecha,r.observacion,r.numeropersona,r.id_promotor from reservacion as r 
						join negocio as n on r.id_restaurant = n.id_negocio
						join usuario as u on r.usuario_solicitante = u.id_usuario
						where r.id_hotel = :hotel and day(r.fecha) = :diaanterior";
							
							$datos = array(
							':diaanterior'=>date('d')-1,
							':hotel' =>  $this->hotel);

					}
					
				}

				try {
						$stm = $this->conec->prepare($sql);
						$stm->execute($datos);
					} catch (\PDOException $e) {
					// echo $e->getMessage();
					}

				break;
			
			case 2:
				if($datos['restaurant'] !=0){

					if(isset($_SESSION['promotor'])){

						$sql = "SELECT r.id_promotor as usuario_registrante,r.id,r.creado,n.nombre as negocio,u.username as username,concat(u.nombre,' ',u.apellido) as nombrecompleto,
						r.status,concat(r.fecha,' ',r.hora) as fecha,r.observacion,r.numeropersona, r.id_promotor from reservacion as r 
						join negocio as n on r.id_restaurant = n.id_negocio
						join usuario as u on r.usuario_solicitante = u.id_usuario
						where r.id_hotel = :hotel and month(r.fecha) = :mes and n.id_negocio  = :restaurant and r.id_promotor =:promotor";

						$datos = array(
							':mes' => date('m'),
							':restaurant'=>$datos['restaurant'],
							':hotel' =>  $_SESSION['promotor']['hotel'],
							':promotor'=>$_SESSION['promotor']['id']
						);

					}else{

						$sql = "SELECT r.usuario_registrante,r.id,r.creado,n.nombre as negocio,u.username as username,concat(u.nombre,' ',u.apellido) as nombrecompleto,
					r.status,concat(r.fecha,' ',r.hora) as fecha,r.observacion,r.numeropersona,r.id_promotor from reservacion as r 
					join negocio as n on r.id_restaurant = n.id_negocio
					join usuario as u on r.usuario_solicitante = u.id_usuario
					where r.id_hotel = :hotel and month(r.fecha) = :mes and n.id_negocio  = :restaurant";

					$datos = array(
							':mes' => date('m'),
							':restaurant'=>$datos['restaurant'],
							':hotel' =>  $this->hotel
						);

					}
				}else{

						if(isset($_SESSION['promotor'])){

							$sql = "SELECT r.id_promotor as usuario_registrante,r.id,r.creado,n.nombre as negocio,u.username as username,concat(u.nombre,' ',u.apellido) as nombrecompleto,
							r.status,concat(r.fecha,' ',r.hora) as fecha,r.observacion,r.numeropersona,r.id_promotor from reservacion as r 
							join negocio as n on r.id_restaurant = n.id_negocio
							join usuario as u on r.usuario_solicitante = u.id_usuario
							where r.id_hotel = :hotel and month(r.fecha) = :mes and r.id_promotor = :promotor";

							$datos = array(
								':mes'      => date('m'),
								':hotel'    => $_SESSION['promotor']['hotel'],
								':promotor' => $_SESSION['promotor']['id']
							);

						}else{

							$sql = "SELECT r.usuario_registrante,r.id,r.creado,n.nombre as negocio,u.username as username,concat(u.nombre,' ',u.apellido) as nombrecompleto,
							r.status,concat(r.fecha,' ',r.hora) as fecha,r.observacion,r.numeropersona,r.id_promotor from reservacion as r 
							join negocio as n on r.id_restaurant = n.id_negocio
							join usuario as u on r.usuario_solicitante = u.id_usuario
							where r.id_hotel = :hotel and month(r.fecha) = :mes";

							$datos = array(
								':mes' => date('m'),
								':hotel' =>  $this->hotel
							);

						}
				}

				try {
						$stm = $this->conec->prepare($sql);
						$stm->execute($datos);
					} catch (\PDOException $e) {
					// echo $e->getMessage();
					}
				break;

			case 3:
				if($datos['restaurant'] !=0){


					if(isset($_SESSION['promotor'])){
						$sql = "SELECT r.id_promotor as usuario_registrante,r.id,r.creado,n.nombre as negocio,u.username as username,concat(u.nombre,' ',u.apellido) as nombrecompleto,
							r.status,concat(r.fecha,' ',r.hora) as fecha,r.observacion,r.numeropersona,r.id_promotor from reservacion as r 
							join negocio as n on r.id_restaurant = n.id_negocio
							join usuario as u on r.usuario_solicitante = u.id_usuario
							where r.id_hotel = :hotel and month(r.fecha) = :mesanterior and n.id_negocio  = :restaurant and r.id_promotor =:promotor";

						$datos = array(
							':mesanterior' => date('m') -1,
							':restaurant'  => $datos['restaurant'],
							':hotel'       => $_SESSION['promotor']['hotel'],
							':promtor'     => $_SESSION['promotor']['id']
							);

					}else{

						$sql = "SELECT r.usuario_registrante,r.id,r.creado,n.nombre as negocio,u.username as username,concat(u.nombre,' ',u.apellido) as nombrecompleto,
							r.status,concat(r.fecha,' ',r.hora) as fecha,r.observacion,r.numeropersonam,r.id_promotor from reservacion as r 
							join negocio as n on r.id_restaurant = n.id_negocio
							join usuario as u on r.usuario_solicitante = u.id_usuario
							where r.id_hotel = :hotel and month(r.fecha) = :mesanterior and n.id_negocio  = :restaurant";

						$datos = array(
							':mesanterior' => date('m') -1,
							':restaurant'=>$datos['restaurant'],
							':hotel' =>  $this->hotel
							);
					}

				}else{


					if(isset($_SESSION['promotor'])){
						$sql = "SELECT r.id_promotor as usuario_registrante,r.id,r.creado,n.nombre as negocio,u.username as username,concat(u.nombre,' ',u.apellido) as nombrecompleto,
						r.status,concat(r.fecha,' ',r.hora) as fecha,r.observacion,r.numeropersona,r.id_promotor from reservacion as r 
						join negocio as n on r.id_restaurant = n.id_negocio
						join usuario as u on r.usuario_solicitante = u.id_usuario
						where r.id_hotel = :hotel and month(r.fecha) = :mesanterior and r.id_promotor = :promotor";

						$datos = array(
							':mesanterior' => date('m') -1,
							':hotel'       => $_SESSION['promotor']['hotel'],
							':promotor'    => $_SESSION['promotor']['id']
						);

					}else{
						$sql = "SELECT r.usuario_registrante,r.id,r.creado,n.nombre as negocio,u.username as username,concat(u.nombre,' ',u.apellido) as nombrecompleto,
						r.status,concat(r.fecha,' ',r.hora) as fecha,r.observacion,r.numeropersona,r.id_promotor from reservacion as r 
						join negocio as n on r.id_restaurant = n.id_negocio
						join usuario as u on r.usuario_solicitante = u.id_usuario
						where r.id_hotel = :hotel and month(r.fecha) = :mesanterior";

						$datos = array(
							':mesanterior' => date('m') -1,
							':hotel' =>  $this->hotel
						);

					}
				}

				try {
						$stm = $this->conec->prepare($sql);
						$stm->execute($datos);
					} catch (\PDOException $e) {
					// echo $e->getMessage();
					}

				break;

			case 4:

				$this->busqueda['fechainicio'] = $datos['rango1'];
				$this->busqueda['fechafin'] = $datos['rango2'];

				if($datos['restaurant'] !=0){

					if(isset($_SESSION['promotor'])){
						$sql = "SELECT r.id_promotor as usuario_registrante,r.id,r.creado,n.nombre as negocio,u.username as username,concat(u.nombre,' ',u.apellido) as nombrecompleto,
						r.status,concat(r.fecha,' ',r.hora) as fecha,r.observacion,r.numeropersona,r.id_promotor from reservacion as r 
						join negocio as n on r.id_restaurant = n.id_negocio
						join usuario as u on r.usuario_solicitante = u.id_usuario
						where r.id_hotel = :hotel and (r.fecha between :fecha1 and :fecha2) and n.id_negocio  = :restaurant and r.id_promotor = :promotor";

							$datos = array(
							':fecha1'     => $this->busqueda['fechainicio'], 
							':fecha2'     => $this->busqueda['fechafin'],
							':restaurant' => $datos['restaurant'],
							':hotel'      => $_SESSION['promotor']['hotel'],
							':promotor'    => $_SESSION['promotor']['id']

						);


					}else{

						$sql = "SELECT r.usuario_registrante,r.id,r.creado,n.nombre as negocio,u.username as username,concat(u.nombre,' ',u.apellido) as nombrecompleto,
						r.status,concat(r.fecha,' ',r.hora) as fecha,r.observacion,r.numeropersona,r.id_promotor from reservacion as r 
						join negocio as n on r.id_restaurant = n.id_negocio
						join usuario as u on r.usuario_solicitante = u.id_usuario
						where r.id_hotel = :hotel and (r.fecha between :fecha1 and :fecha2) and n.id_negocio  = :restaurant";

							$datos = array(
							':fecha1' => $this->busqueda['fechainicio'], 
							':fecha2' => $this->busqueda['fechafin'],
							':restaurant'=>$datos['restaurant'],
							':hotel' =>  $this->hotel
						);

					}
					

					

				}else{

					if(isset($_SESSION['promotor'])){

						$sql = "SELECT r.id_promotor as usuario_registrante,r.id,r.creado,n.nombre as negocio,u.username as username,concat(u.nombre,' ',u.apellido) as nombrecompleto,
						r.status,concat(r.fecha,' ',r.hora) as fecha,r.observacion,r.numeropersona,r.id_promotor from reservacion as r 
						join negocio as n on r.id_restaurant = n.id_negocio
						join usuario as u on r.usuario_solicitante = u.id_usuario
						where r.id_hotel = :hotel and (r.fecha between :fecha1 and :fecha2) and r.id_promotor = :promotor";

						$datos = array(
							':fecha1'   => $this->busqueda['fechainicio'], 
							':fecha2'   => $this->busqueda['fechafin'],
							':hotel'    => $_SESSION['promotor']['hotel'],
							':promotor' => $_SESSION['promotor']['id']
						);

					}else{

						$sql = "SELECT r.usuario_registrante,r.id,r.creado,n.nombre as negocio,u.username as username,concat(u.nombre,' ',u.apellido) as nombrecompleto,
						r.status,concat(r.fecha,' ',r.hora) as fecha,r.observacion,r.numeropersona,r.id_promotor from reservacion as r 
						join negocio as n on r.id_restaurant = n.id_negocio
						join usuario as u on r.usuario_solicitante = u.id_usuario
						where r.id_hotel = :hotel and (r.fecha between :fecha1 and :fecha2)";

						$datos = array(
							':fecha1' => $this->busqueda['fechainicio'], 
							':fecha2' => $this->busqueda['fechafin'],
							':hotel' =>  $this->hotel
						);
					}
				}

				try {
						$stm = $this->conec->prepare($sql);
						$stm->execute($datos);
					} catch (\PDOException $e) {
					// echo $e->getMessage();
					}


				break;

				
		}
		$this->catalogo = $stm->fetchAll(PDO::FETCH_ASSOC);	
		
	}



	public function getDataReservacacionAnualMensual(){


		if(isset($_SESSION['promotor'])){
			$sql = "SELECT count(r.id) as reservaciones,r.status,month(r.fecha) as mes 
					from reservacion r
					where r.id_promotor = :promotor and year(r.fecha) = year(now())
					group by r.status, monthname(r.fecha) order by month(r.fecha)";

				$datos = array(':promotor'=>$_SESSION['promotor']['id']);
		}else{
			$sql = "SELECT count(r.id) as reservaciones,r.status,month(r.fecha) as mes 
					from reservacion r
					where r.id_hotel = :hotel and year(r.fecha) = year(now())
						group by r.status, monthname(r.fecha) order by month(r.fecha)";

						$datos = array(':hotel'=>$this->hotel);
		}
		

		$stm = $this->conec->prepare($sql);

		$stm->execute($datos);


		$cancelados = array('name'=>'Cancelados','description'=>'Reservaciones Canceladas en el mes','color'=>'#652770','data'=>array());
		$consumados = array('name'=>'Consumados','color'=>'green','data'=>array());
		$agendados = array('name'=>'Agendados','color'=>'#4DDC00','data'=>array());

		$canceladosd = array();
		$consumadosd = array();
		$agendadosd = array();
		$meses = array();
		$datos = $stm->fetchAll(PDO::FETCH_ASSOC);


				foreach ($datos as $key => $value) {
					
						switch ($value['status']) {
							case 0:

							for ($i=1; $i <=12 ; $i++) { 
								if($value['mes'] == $i){
									$agendadosd[$i] = $value['reservaciones'];
							}
							}
								
							break;

							case 1:
								for ($i=1; $i <=12 ; $i++) { 
								if($value['mes'] == $i){
									$consumadosd[$i] = $value['reservaciones'];
								}
							}
							break;

							case 3:
								for ($i=1; $i <=12 ; $i++) { 
								if($value['mes'] == $i){
										$canceladosd[$i] = $value['reservaciones'];
								}
								}
							
							break;
							
							
						}
					}

						for ($i=1; $i <= 12 ; $i++) { 
							foreach ($canceladosd as $key => $value) {
								if($key == $i){
									settype($canceladosd[$i],'integer');
									$cancelados['data'][$i] = $canceladosd[$i];
								}else{

									if(!array_key_exists($i, $canceladosd)){
										$cancelados['data'][$i] = 0;
									}

								
									
								}
							}

							foreach ($consumadosd as $key => $value) {
								if($key == $i){
									settype($consumadosd[$i],'integer');
									$consumados['data'][$i] = $consumadosd[$i];
								}else{

									if(!array_key_exists($i, $consumadosd)){
										$consumados['data'][$i] = 0;
									}

								
									
								}
							}

							foreach ($agendadosd as $key => $value) {
								if($key == $i){
									settype($agendadosd[$i],'integer');
									$agendados['data'][$i] = $agendadosd[$i];
								}else{

									if(!array_key_exists($i, $agendadosd)){
										$agendados['data'][$i] = 0;
									}
								}
							}
									
						}

						// echo var_dump($cancelados);
							$cancelados['data'] = array_values($cancelados['data']);
							$consumados['data'] = array_values($consumados['data']);
							$agendados['data'] = array_values($agendados['data']);

		array_push($meses, $agendados,$consumados,$cancelados);

		return $meses;
	}

	public function getDatos(array $datos){

		$this->cargar($datos['filtro'],$datos);


		
		

		for ($i=0; $i < count($this->catalogo); $i++) { 

			if(isset($_SESSION['promotor'])){
				$sql = "SELECT p.username, concat(p.nombre,' ',p.apellido) as nombrecompleto from promotor as p join reservacion as r on p.id = r.id_promotor
				where p.id =:registrante";
					$datos = array(':registrante'=>$this->catalogo[$i]['usuario_registrante']);
			}else{


				if($this->catalogo[$i]['id_promotor']  != 0 || $this->catalogo[$i]['id_promotor'] != null){
					$sql = "SELECT p.username, concat('Promotor ',p.nombre,' ',p.apellido) as nombrecompleto from promotor as p join reservacion as r on p.id = r.id_promotor
					where p.id =:registrante";
					$datos = array(':registrante'=>$this->catalogo[$i]['id_promotor']);
				}else{
				$sql = "SELECT u.username, concat(u.nombre,' ',u.apellido) as nombrecompleto from usuario as u join reservacion as r on u.id_usuario = r.usuario_registrante
					where u.id_usuario =:registrante";
					$datos = array(':registrante'=>$this->catalogo[$i]['usuario_registrante']);
				}
			}

			if(empty($this->catalogo[$i]['nombrecompleto'])){
				$this->catalogo[$i]['nombrecompleto'] = $this->catalogo[$i]['username'];
			}

			$stm = $this->conec->prepare($sql);
			
			$stm->execute($datos);

			if($row = $stm->fetch(PDO::FETCH_ASSOC)){

				if(empty($row['nombrecompleto'])){
					$this->catalogo[$i]['usuario_registrante'] = $row['username'];
				}else{
					$this->catalogo[$i]['usuario_registrante'] = $row['nombrecompleto'];
				}
			}else{
				$this->catalogo[$i]['usuario_registrante'] = 'directo';
			}
			if($this->catalogo[$i]['status'] == 0 || $this->catalogo[$i]['status'] == 2){

				$this->catalogo[$i]['impresion'] = '<form action="'.HOST.'/Hotel/reservaciones/reservaciones.php" method="POST" target="_blank"><button type="submit" name="imprimir" data-toggle="tooltip" title="Descargar ticket para su impresión" data-placement="left" value="'.$this->catalogo[$i]['id'].'" class="btn btn-info impresion"><i class="fa fa-print"></i></button></form>';
							$this->catalogo[$i]['cancelar']	 = '<button type="button" class="btn btn-danger cancelar" data-toggle="tooltip" title="Cancelar reservación" data-id="'.$this->catalogo[$i]['id'] .'" data-placement="left"><i class="fa fa-close"></i></button>';
							}else{
							$this->catalogo[$i]['cancelar']	 = '';
							$this->catalogo[$i]['impresion'] = '';
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
						
						default:
						# code...
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

	public function reservar(array $datos){


		if($this->conec->inTransaction()){
			$this->conec->rollBack();

		}

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
		// 
		if($this->conec->inTransaction()){
			$this->conec->rollBack();
		}
		
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
					
					$this->conec->rollBack();
					return false;

				}

				$_SESSION['notification']['success'] = " La reservación se ha registrado exitosamente";
				return true;

		}else{


			if(isset($_SESSION['promotor'])){
				$sql = "INSERT INTO reservacion(fecha,numeropersona,observacion,id_hotel,id_restaurant,id_promotor,usuario_solicitante,hora)
							values(:fecha,:numeropersona,:observacion,:hotel,:restaurant,:promotor,:solicitante,:hora)";
							$datos = array(
											':fecha'         => $this->fecha,
											':numeropersona' => $this->numpersonas,
											':observacion'   => $this->observaciones,
											':hotel'         => $_SESSION['promotor']['hotel'],
											':restaurant'    => $this->idrestaurant,
											':promotor'   => $_SESSION['promotor']['id'],
											':solicitante'   => $this->idsocio,
											':hora'          => $this->hora
											);
			}else{
				
				$sql = "INSERT INTO reservacion(fecha,numeropersona,observacion,id_hotel,id_restaurant,usuario_registrante,usuario_solicitante,hora)
							values(:fecha,:numeropersona,:observacion,:hotel,:restaurant,:registrante,:solicitante,:hora)";
							$datos = array(
											':fecha'         => $this->fecha,
											':numeropersona' => $this->numpersonas,
											':observacion'   => $this->observaciones,
											':hotel'         => $this->hotel,
											':restaurant'    => $this->idrestaurant,
											':registrante'   => $this->usuarioregistrante,
											':solicitante'   => $this->idsocio,
											':hora'          => $this->hora
											);

			}
			
			$this->conec->beginTransaction();
							
			try {

				$stm = $this->conec->prepare($sql);
				$stm->execute($datos);
				
				$last_id = $this->conec->lastInsertId();
				$this->conec->commit();
				
				} catch (\PDOException $e) {
					$this->error_log(__METHOD__,__LINE__,$e->getMessage());
					$this->conec->rollBack();
					return false;
				}
			
			
				$_SESSION['notification']['impresion'] = true;


				try {
					$this->imprimir($last_id);
				} catch (\RuntimeException $e) {
					$this->error_log(__METHOD__,__LINE__,$e->getMessage());

				}
				
				try {
					$this->enviarmensaje($last_id);
				} catch (\Exception $e) {
					$this->error_log(__METHOD__,__LINE__,$e->getMessage());
				}
				$_SESSION['notification']['success'] = " La reservación se ha registrado exitosamente.";
				header('location: '.HOST.'/Hotel/reservaciones/');
				die();
				return true;

		}	
	
	}

	private function enviarmensaje(int $reservacion){

		$sql = "SELECT concat(u.nombre,' ', u.apellido) as nombre, u.username, u.telefono, concat(r.fecha,' a las ',r.hora) as fecha, r.numeropersona,n.nombre as negocio
	from usuario as u  left join reservacion as r on u.id_usuario = r.usuario_solicitante join negocio as n on r.id_restaurant = n.id_negocio  
	where u.id_usuario = :socio and r.status = 0 and r.id = (select max(id) from reservacion where usuario_solicitante = :socio1)";
		$stm = $this->conec->prepare($sql);
		$stm->bindParam(':socio',$this->idsocio,PDO::PARAM_INT);
		$stm->bindParam(':socio1',$this->idsocio,PDO::PARAM_INT);
		$stm->execute();

		while($row = $stm->fetch(PDO::FETCH_ASSOC)){
			if(substr($row['telefono'], 0,2) != '52'){
				$row['telefono'] = '52'.$row['telefono'];
			}




				$this->telefonosocio = $row['telefono'];
				$this->mensaje = 'TravelPoints: New reservation, date '.$row['fecha'].'. Personas '.$row['numeropersona'].',restaurant '.$row['negocio'].'. all details in travelpoints.com.mx';
		}
	

		$this->sms_cliente();


		$sql = "SELECT h.nombre as hotel,
				(select telefono from negocio_telefono where id_negocio = :negocio order by id_telefono asc limit 1) as telefononegocio,
				concat(r.fecha,' ',r.hora) as fechareserva,
			    u.username, concat(u.nombre,' ',u.apellido) as nombrecompleto,r.numeropersona
			    from reservacion as r join hotel as h on r.id_hotel = h.id
			    join usuario as u on r.usuario_solicitante = u.id_usuario
			    where r.id = :reservacion";

		$stm = $this->conec->prepare($sql);
		$stm->execute(array(':negocio'=>$this->idrestaurant,
							':reservacion'=>$reservacion));

		while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
			if(substr($row['telefononegocio'], 0,2) != '52'){
				$row['telefononegocio'] = '52'.$row['telefononegocio'];

			}
				$nombre = $row['username'];
				if(!empty($row['nombrecompleto'])){
					$nombre = $row['nombrecompleto'];
				}
				$this->telefononegocio = $row['telefononegocio'];

				$this->mensaje = 'TravelPoints: New reservation, client '.$nombre.' date '.$row['fechareserva'].'. Personas '.$row['numeropersona'].',hotel '.$row['hotel'].'. all details in travelpoints.com.mx';
			}

			$this->sms_negocio();

	}

	private function sms_cliente(){

		$url = 'https://api.broadcastermobile.com/brdcstr-endpoint-web/services/messaging/';

		$cuerpo = array(
						'apiKey'  => 407,
						'country' => "MX",
						'dial'    => "26262",
						'message' => $this->mensaje,
						'msisdns' => [''.$this->telefonosocio.''],
						'tag'     => 'TravelPoints'
						);

	

		$ch= curl_init($url);

		curl_setopt_array($ch, array(
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_RETURNTRANSFER 	=> 1,
			CURLOPT_POSTFIELDS     	=> json_encode($cuerpo),
			CURLOPT_HTTPHEADER 		=> array('Accept:application/json',
										'Content-Type:application/json',
										'Authorization:CjqJxRd+vMYzPvcPuIK4c+3lTyo='),
			CURLOPT_CONNECTTIMEOUT => 3 
		));
		$result = curl_exec($ch);
		$resultado = false;

		if(curl_getinfo($ch,CURLINFO_HTTP_CODE) === 200){
			$resultado  = json_encode(str_replace('\'', '',$result)); 
		}

		return $resultado;
	
	}

	private function sms_negocio(){

		$url = 'https://api.broadcastermobile.com/brdcstr-endpoint-web/services/messaging/';

		$cuerpo = array('apiKey' => 407,'country'=>"MX",'dial'=>"26262",'message'=>$this->mensaje,
						'msisdns'=>[''.$this->telefononegocio.''],'tag'=>'Travel Points');

	

		$ch= curl_init($url);

		curl_setopt_array($ch, array(
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_RETURNTRANSFER 	=> 1,
			CURLOPT_POSTFIELDS     	=> json_encode($cuerpo),
			CURLOPT_HTTPHEADER 		=> array('Accept:application/json',
										'Content-Type:application/json',
										'Authorization:CjqJxRd+vMYzPvcPuIK4c+3lTyo='),
			CURLOPT_CONNECTTIMEOUT => 3  
		));
		$result = curl_exec($ch);
		$resultado = false;

		if(curl_getinfo($ch,CURLINFO_HTTP_CODE) === 200){
			$resultado  = json_encode(str_replace('\'', '',$result)); 
		}

		return $resultado;


	} 

	private function cargarUltimo(){
		$sql = "SELECT r.numeropersona, r.id, u.username, concat(u.nombre,' ',u.apellido) as nombrecompleto, n.nombre as negocio,n.direccion, date_format(r.fecha,'%d/%M/%Y') as fecha, 
			r.hora, h.nombre as hotel, r.usuario_registrante from reservacion as r 
					join usuario as u on r.usuario_solicitante = u.id_usuario 
					join negocio as n on r.id_restaurant = n.id_negocio
					join hotel as h on r.id_hotel = h.id 

					where r.usuario_registrante = :concierge
                    ORDER BY r.id desc limit 1 ";


                    $stm = $this->conec->prepare($sql);
                    $stm->bindParam(':concierge',$_SESSION['user']['id_usuario'],PDO::PARAM_INT);
                    $stm->execute();


                    $sql1 = "SELECT u.username,concat(u.nombre,' ',u.apellido) as concierge from usuario u 
                    			where u.id_usuario = :concierge";

                    	

                    foreach ($stm->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
                    	$stmt = $this->conec->prepare($sql1);
                    	 $stmt->bindParam(':concierge',$_SESSION['user']['id_usuario'],PDO::PARAM_INT);
                    	 $stmt->execute();

                    	 if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    	 	if(empty($row['concierge'])){
                    	 		 $this->ticket['concierge'] = $row['username'];
                    	 	}else{
                    	 		 $this->ticket['concierge'] = $row['concierge'];
                    	 	}
                    	 }

							$this->ticket['ticket']            = $value['id'];
							$this->ticket['username']          = $value['username'];
							$this->ticket['nombrecompleto']    = $value['nombrecompleto'];
							$this->ticket['negocio']           = $value['negocio'];
							$this->ticket['direccion-negocio'] = $value['direccion'];
							$this->ticket['fecha']             = $value['fecha'];
							$this->ticket['hora']              = $value['hora'];
							$this->ticket['hotel']             = $value['hotel'];
							$this->ticket['numeropersona']     = $value['numeropersona'];

                    }

                  
	}


	private function cargarReservar(int $reserva){

		$sql = "SELECT r.numeropersona, r.id, u.username, concat(u.nombre,' ',u.apellido) as nombrecompleto, n.nombre as negocio,n.direccion, date_format(r.fecha,'%d/%M/%Y') as fecha, 
			r.hora, h.nombre as hotel, r.usuario_registrante from reservacion as r 
					join usuario as u on r.usuario_solicitante = u.id_usuario 
					join negocio as n on r.id_restaurant = n.id_negocio
					join hotel as h on r.id_hotel = h.id 
					where r.id = :reserva";


                    $stm = $this->conec->prepare($sql);
                    $stm->bindParam(':reserva',$reserva,PDO::PARAM_INT);
                    $stm->execute();


                    $sql1 = "SELECT u.username,concat(u.nombre,' ',u.apellido) as concierge from usuario u 
                    			where u.id_usuario = :concierge";

                    	

                    foreach ($stm->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
                    	$stmt = $this->conec->prepare($sql1);
                    	 $stmt->bindParam(':concierge',$value['usuario_registrante'],PDO::PARAM_INT);
                    	 $stmt->execute();

                    	 if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    	 	if(empty($row['concierge'])){
                    	 		 $this->ticket['concierge'] = $row['username'];
                    	 	}else{
                    	 		 $this->ticket['concierge'] = $row['concierge'];
                    	 	}
                    	 }

							$this->ticket['ticket']            = $value['id'];
							$this->ticket['username']          = $value['username'];
							$this->ticket['nombrecompleto']    = $value['nombrecompleto'];
							$this->ticket['negocio']           = $value['negocio'];
							$this->ticket['direccion-negocio'] = $value['direccion'];
							$this->ticket['fecha']             = $value['fecha'];
							$this->ticket['hora']              = $value['hora'];
							$this->ticket['hotel']             = $value['hotel'];
							$this->ticket['numeropersona']     = $value['numeropersona'];

                    }

	}



	public function getTicket(){
		return $this->ticket;
	}

	public function imprimir(int $reservacion = 0){

		ob_start();

		require_once($_SERVER['DOCUMENT_ROOT'].'/Hotel/viewreports/comprobante-reservacion.php');

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
		$dompdf->setPaper('mia');
		$dompdf->render();
		$dato = array('Attachment' => 0);
		$titulo = "Travel Points: Ticket Reservacion.pdf";
		// $dompdf->stream($titulo.'.pdf',$dato);
		$pdf = $dompdf->output();
		file_put_contents(ROOT.'/assets/reports/reservaciones/'.$titulo, $pdf);


		$credenciales = new \PrintNode\Credentials();
		$credenciales->setApiKey(PRINTNODE_APIKEY);
		// $credenciales->setEmailPassword('megajdcc2009@gmail.com','20464273jd');

		 // $credenciales = new \PrintNode\ApiKey(PRINTNODE_APIKEY);
		// 
		$request = new \PrintNode\Request($credenciales);

		$computers = $request->getComputers();
		$printers = $request->getPrinters();
		$printJobs = $request->getPrintJobs();



		$printJob = new \PrintNode\PrintJob();

		// echo var_dump($printers[2]);

		$printJob->printer = $printers[2];

		$printJob->contentType = "pdf_base64";
		$url = file_get_contents(ROOT.'/assets/reports/reservaciones/'.$titulo);

		// $url = preg_replace("/ /","%20",$url);
		// 
		// $url = str_replace("https","http",$url);
		$printJob->content = base64_encode($url);
		$printJob->options = array(
					'pages'=>'',
					);
		$printJob->source = "Travel Points";
		$printJob->title = 'TravelPointsreservacion.pdf';

		$response = $request->post($printJob);


		$statusCode = $response->getStatusCode();


		$statusMessage = $response->getStatusMessage();


		// echo $statusCode . '<br>' . $statusMessage; 

		return true;
	
	}

	public function getRestaurant($negocio){
		$sql = "SELECT n.nombre as negocio, concat(n.direccion,' ',c.ciudad,' ',e.estado,' ',p.pais) as direccion, nt.telefono  from negocio as n join negocio_telefono as nt
						on n.id_negocio = nt.id_negocio 
						join ciudad as c on n.id_ciudad = c.id_ciudad
						join estado as e on c.id_estado = e.id_estado
						join pais as p on e.id_pais = p.id_pais


						where n.id_negocio = :id";

		$stm = $this->conec->prepare($sql);
		$stm->bindParam(':id',$negocio,PDO::FETCH_ASSOC);

		$stm->execute();
		return $stm;
	}

	public function getReserva(){
		
		$sql1 = "SELECT u.username,r.id from usuario as u join reservacion as r on u.id_usuario = r.usuario_registrante 
					where r.usuario_registrante = :user";

		foreach($this->catalogo as $key => $valores) {
			
			$creado   = _safe($valores['creado']);
			$negocio  = _safe($valores['negocio']);
			$usuario  = _safe($valores['username']);
			$status   = _safe($valores['status']);
			$fecha    = _safe($valores['fecha']);
			$personas = $valores['numeropersona'];	




			switch ($status) {
				case 0:
						$status = 'Agendada';
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
				case 4:
						$status = 'Desfasada';
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

			if(empty($observacion)){
				$observacion = 'Sin Observaciones';
			}else{
				$observacion = '<strong class="observaciones" data-observacion="'._safe($valores['observacion']).'">Observaciones</strong>';
			}



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
					<td><?php echo $personas ?></td>
				<td><?php echo $observacion ?></td>
				<td>

					<?php if($status == 'Agendada' || $status == 'Confirmada'){?>
						<button type="button" class="btn btn-danger cancelar" data-toggle="tooltip" title="Cancelar reservación" data-id="<?php echo $valores['id'] ?>" data-placement="left"><i class="fa fa-close"></i></button>
					 <?php  }?>
				</td>

				
            </tr>

            	
			<?php
		}

	}


	public function cancelar(int $idreserva){


		$sql = "UPDATE reservacion  set status = 3 where id = :reserva";

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


	

	private function getButtonImpresion(){
		if(isset($_SESSION['notification']['impresion'])){
			return '<button type="submit" name="imprimir" class="imprimir btn btn-info"><i class="fa fa-print"></i>Descargar para su impresión</button>';
		}else{
			return '';
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
			unset($_SESSION['notification']['impresion']);
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


	public function report(array $datos){
		
		$this->cargar($datos['filtro'],$datos);

		ob_start();
		require_once($_SERVER['DOCUMENT_ROOT'].'/Hotel/viewreports/reservaciones.php');

		$context = stream_context_create([
				'ssl'=>[
					'verify_peer'       => FALSE,
					'verify_peer_name'  => FALSE,
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
		
		$titulo = "Travel Points: Lista de reservaciones ";
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


	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'/assets/error_logs/error_panel_hotel.txt', '['.date('d/M/Y h:i:s A').' on '.$method.' on line '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		return;
	}

}


 ?>