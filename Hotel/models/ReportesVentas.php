<?php 

namespace Hotel\models;
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';

use assets\libs\connection;
use \Dompdf\Dompdf as pdf;
use \Dompdf\Options;
use \Dompdf\Positioner;

use PDO;


/**
 * @author Crespo Jhonatan... 
 */
class ReportesVentas{

	//Propiedades... 
	
	public $con; 

	private $negocios = array(
		'id' => null,
		'fecha_inicio' => null,
		'fecha_fin' => null,
		'negocios' => array(),
		'id_usuario' => null
	);

	private $busqueda = array('fechainicio' => null,'fechafin' => null);
	private $venta = array();
	private $error = array('notificacion' => null, 'fechainicio' => null, 'fechafin' => null);
	
	private $hotel = array(
		'id' => null,
		'nombre' => null,
		'comision' => null
	);

	public $estadocuenta = array();

	function __construct(connection $con){

		$this->con = $con->con;

		if(isset($_SESSION['id_hotel'])){
			$this->hotel['id']  = $_SESSION['id_hotel'];
		}
 		
 		if(isset($_SESSION['promotor'])){
 			$this->hotel['id']  = $_SESSION['promotor']['hotel'];
 		}

		$this->CargarData();
		if(!is_null($this->hotel['id'])){
			$this->DatosHotel();
		}
		
		return;

	}

	//  METHODOS DE CLASS
	

	private function DatosHotel(){


		$query = "select h.nombre as nombrehotel, h.comision from hotel  as h where h.id = :hotel";

		$stm = $this->con->prepare($query);

		$stm->execute(array(':hotel'=>$this->hotel['id']));

		$fila = $stm->fetch(PDO::FETCH_ASSOC);

		$this->setNombreHotel($fila['nombrehotel']);
		$this->setComisionHotel($fila['comision']);

	}

	private function setNombreHotel(string $nombrehotel){

		$this->hotel['nombre'] = $nombrehotel;

	}

	private function setComisionHotel(int $comision){

		$this->hotel['comision'] = $comision;

	}


	public function getNombreHotel(){

		return $this->hotel['nombre'];

	}

	public function getComisionHotel(){

		return $this->hotel['comision'];

	}

	public function getFecha1(){

		return $this->negocios['fecha_inicio'];
	}

	public function getFecha2(){

		return $this->negocios['fecha_fin'];

	}

	private function setFecha1($fecha){

		$this->negocios['fecha_inicio'] = $fecha;

	}

	private function setFecha2($fecha){

		$this->negocios['fecha_fin'] = $fecha;

	}

	public function getBalance(){

		if(isset($_SESSION['promotor'])){
				$query  = "SELECT  b.balance as balance
 					from  balance as b 
 				where b.id_promotor = :promotor and b.perfil=5 order by b.id desc limit 1";

 				$datos = array(':promotor'=>$_SESSION['promotor']['id']);
				
			}else{
				$query  = "SELECT  b.balance as balance
 					from  balance as b 
 				where b.id_hotel = :hotel and b.perfil = 1 order by b.id desc limit 1";

 				$datos = array(':hotel'=>$this->hotel['id']);

			}
				$stm = $this->con->prepare($query);
				$stm->execute($datos);
				return  '$'.number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['balance'],2,',','.').' MXN';
	}



	public function getPromotor(){


		$sql = "SELECT concat(nombre,' ',apellido) as promotor from promotor where id=:promotor";

		$stm = $this->con->prepare($sql);
		$stm->bindParam(':promotor',$_SESSION['promotor']['id'],PDO::PARAM_INT);

		$stm->execute();

		return $stm->fetch(PDO::FETCH_ASSOC)['promotor']; 
	}
	public function CargarData(string $fecha1 = null, string $fecha2 = null){

			if(!is_null($fecha1) and !is_null($fecha2) and !empty($fecha1) and !empty($fecha2)){

				$this->setFechainicio($fecha1);
				$this->setFechafin($fecha2);

				// echo var_dump($this->busqueda);
				
				if(isset($_SESSION['promotor'])){
					$query = "(select b.id, ne.nombre as negocio, u.username, CONCAT(u.nombre,' ',u.apellido) as nombre, nv.venta, b.comision, b.balance,nv.creado 
											FROM balance as b 
											LEFT JOIN negocio_venta as nv on b.id_venta = nv.id_venta
											LEFT JOIN negocio as ne on nv.id_negocio = ne.id_negocio 
											LEFT JOIN usuario as u on nv.id_usuario = u.id_usuario
											where b.id_promotor = :promotor1  and nv.creado between :fecha1 and :fecha2 and b.id_venta != 0 and b.perfil = 5 ORDER BY creado ASC)
											UNION 
											(select  b.id, rr.negocio, rr.usuario as username ,  rr.usuario as nombre ,CONCAT('-',r.monto) as venta,CONCAT('-',r.monto) as comision, b.balance,b.creado
											from retiro as r join retirocomision as rr on r.id = rr.id_retiro join balance as b on rr.id = b.id_retiro
											where b.id_promotor = :promotor2 and rr.condicion =1 and b.perfil = 5 and b.creado between :fecha3 and :fecha4 ORDER BY creado ASC)
											UNION
											(select b.id,'Reembolso' as negocio, 'Resto pago parcial' as username , 'reembolso', CONCAT('+',r.monto - r.pagado) as venta,
											CONCAT('+',r.monto - r.pagado) as comision, b.balance,b.creado
											from retirocomision as rr left join retiro as r on rr.id_retiro = r.id left join balance as b on rr.id = b.id_retiro
											where b.id_promotor = :promotor3 and b.perfil = 5 and r.tipo_pago = 2 and rr.condicion = 2 and b.creado between :fecha5 and :fecha6)

											order by creado";

					$datos = array(':promotor1'=>$_SESSION['promotor']['id'],
							        ':promotor2'=>$_SESSION['promotor']['id'],
							        ':promotor3'=>$_SESSION['promotor']['id'],
									':fecha1' => $this->busqueda['fechainicio'],
									':fecha2' => $this->busqueda['fechafin'],
									':fecha3' => $this->busqueda['fechainicio'],
									':fecha4' => $this->busqueda['fechafin'],
									':fecha5' => $this->busqueda['fechainicio'],
									':fecha6' => $this->busqueda['fechafin']
									);	

				}else{

					$query = "(select b.id, ne.nombre as negocio, u.username, CONCAT(u.nombre,' ',u.apellido) as nombre, nv.venta, b.comision, b.balance,nv.creado 
						FROM balance as b 
						LEFT JOIN negocio_venta as nv on b.id_venta = nv.id_venta
						LEFT JOIN negocio as ne on nv.id_negocio = ne.id_negocio 
						LEFT JOIN usuario as u on nv.id_usuario = u.id_usuario
						where b.id_hotel = :hotel1  and nv.creado between :fecha1 and :fecha2 and b.id_venta != 0 ORDER BY creado ASC)
						UNION 
						(select  b.id,rr.negocio, rr.usuario as username ,  rr.usuario as nombre ,CONCAT('-',r.monto) as venta,CONCAT('-',r.monto) as comision, b.balance,b.creado
						from retiro as r join retirocomision as rr on r.id = rr.id_retiro join balance as b on rr.id = b.id_retiro
						where b.id_hotel = :hotel2 and rr.condicion =1 and b.creado between :fecha3 and :fecha4 ORDER BY creado ASC)
						UNION
						(select b.id,'Reembolso' as negocio, 'Resto pago parcial' as username , 'reembolso', CONCAT('+',r.monto - r.pagado) as venta,
						CONCAT('+',r.monto - r.pagado) as comision, b.balance,b.creado
						from retirocomision as rr left join retiro as r on rr.id_retiro = r.id left join balance as b on rr.id = b.id_retiro
						where b.id_hotel = :hotel3 and r.tipo_pago = 2 and rr.condicion = 2 and b.creado between :fecha5 and :fecha6)

						order by creado";

					$datos = array(':hotel1'=>$this->hotel['id'],
							        ':hotel2'=>$this->hotel['id'],
							        ':hotel3'=>$this->hotel['id'],
									':fecha1' => $this->busqueda['fechainicio'],
									':fecha2' => $this->busqueda['fechafin'],
									':fecha3' => $this->busqueda['fechainicio'],
									':fecha4' => $this->busqueda['fechafin'],
									':fecha5' => $this->busqueda['fechainicio'],
									':fecha6' => $this->busqueda['fechafin']
									);	

				}

				$stm = $this->con->prepare($query);
				$stm->execute($datos);

				$this->estadocuenta = $stm->fetchAll(PDO::FETCH_ASSOC);
		}else{

			if(isset($_SESSION['promotor'])){
				$query = "(select b.id,ne.nombre as negocio, u.username, CONCAT(u.nombre,' ',u.apellido) as nombre, nv.venta, b.comision, b.balance,nv.creado 
						FROM balance as b
						LEFT JOIN negocio_venta as nv on b.id_venta = nv.id_venta
						LEFT JOIN negocio as ne on nv.id_negocio = ne.id_negocio 
						LEFT JOIN usuario as u on nv.id_usuario = u.id_usuario
						where b.id_promotor = :promotor1 and b.id_venta != 0)
						UNION 
						(select  b.id, rr.negocio, rr.usuario as username ,  rr.usuario as nombre ,CONCAT('-',r.monto) as venta,CONCAT('-',r.monto) as comision, b.balance,b.creado
						from retiro as r join retirocomision as rr on r.id = rr.id_retiro join balance as b on rr.id = b.id_retiro
						where b.id_promotor = :promotor2 and rr.condicion =1)
						UNION
						(select b.id, 'Reembolso' as negocio, 'Resto pago parcial' as username , 'reembolso', CONCAT('+',r.monto - r.pagado) as venta,
						CONCAT('+',r.monto - r.pagado) as comision, b.balance,b.creado
						from retirocomision as rr left join retiro as r on rr.id_retiro = r.id left join balance as b on rr.id = b.id_retiro
						where b.id_promotor = :promotor3 and r.tipo_pago = 2 and rr.condicion = 2 )
						ORDER BY creado ";


				$datos = array(':promotor1'=>$_SESSION['promotor']['id'],
								':promotor2'=>$_SESSION['promotor']['id'],
								':promotor3'=>$_SESSION['promotor']['id']);

			}else{
				
				$query = "(select b.id, ne.nombre as negocio, u.username, CONCAT(u.nombre,' ',u.apellido) as nombre, nv.venta, b.comision, b.balance,nv.creado 
						FROM balance as b 
						LEFT JOIN negocio_venta as nv on b.id_venta = nv.id_venta
						LEFT JOIN negocio as ne on nv.id_negocio = ne.id_negocio 
						LEFT JOIN usuario as u on nv.id_usuario = u.id_usuario
						where b.id_hotel = :hotel1 and b.id_venta != 0)
						UNION 
						(select  b.id, rr.negocio, rr.usuario as username ,  rr.usuario as nombre ,CONCAT('-',r.monto) as venta,CONCAT('-',r.monto) as comision, b.balance,b.creado
						from retiro as r join retirocomision as rr on r.id = rr.id_retiro join balance as b on rr.id = b.id_retiro
						where b.id_hotel = :hotel2 and rr.condicion =1)
						UNION
						(select b.id, 'Reembolso' as negocio, 'Resto pago parcial' as username , 'reembolso', CONCAT('+',r.monto - r.pagado) as venta,
						CONCAT('+',r.monto - r.pagado) as comision, b.balance,b.creado
						from retirocomision as rr left join retiro as r on rr.id_retiro = r.id left join balance as b on rr.id = b.id_retiro
						where b.id_hotel = :hotel3 and r.tipo_pago = 2 and rr.condicion = 2 )
						ORDER BY creado ";

				$datos = array(':hotel1'=>$this->hotel['id'],
								':hotel2'=>$this->hotel['id'],
								':hotel3'=>$this->hotel['id']);


			}
			
			$stm = $this->con->prepare($query);
			$stm->execute($datos);
			$this->estadocuenta = $stm->fetchAll(PDO::FETCH_ASSOC);
		}
	
	}

	private function setFechainicio($datetime = null){
		if($datetime){
			$datetime = str_replace('/', '-', $datetime);
			$datetime = strtotime($datetime);
			if(!$datetime){
				$this->error['fechainicio'] = 'Formato de fecha y hora incorrecto. Utiliza la herramienta.';
				return false;
			}
			$datetime = date("Y/m/d H:i:s", $datetime);
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
			$datetime = date("Y/m/d H:i:s", $datetime);
			$this->busqueda['fechafin'] = $datetime;
			return true;
		}
		$this->error['fechafin'] = 'Este campo es obligatorio.';
		return false;
	}



	public function mostrarpdf(array $post){

			$this->CargarData($post['date_start'],$post['date_end']);

			ob_start();
			require_once($_SERVER['DOCUMENT_ROOT'].'/Hotel/viewreports/estadocuenta.php');

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
		
			$titulo = "Travel Points: reporte de actividades " .$fecha1;
			$dompdf->stream($titulo.'.pdf',$dato);
		

	}

public function getEstadoCuenta(){
		
		
		foreach ($this->estadocuenta as $key => $value) {
			
			// // $fecha = date('d/m/Y h:m:s', strtotime($value['creado']));
			//  $fecha = $value['creado'];
			//  // settype($value['venta'],'double');

			$venta = number_format((float)$value['venta'],2,'.',',');
	
			  if($venta < 0){
			  	$this->estadocuenta[$key]['venta'] = '<strong class="negativo">$'.$venta.'</strong>';
			  }else{
			  	$this->estadocuenta[$key]['venta']= '$'.$venta;
			  }

			  if($value['comision'] < 0){
			  	
			  	$this->estadocuenta[$key]['comision'] = '<strong class="negativo">$'.$value["comision"].'</strong>';
			  }else{
			  	$this->estadocuenta[$key]['comision'] = '$'.number_format((float)$value['comision'],2,'.',',');
			  }
			
			if(!empty($value['nombre'])){
				$this->estadocuenta[$key]['nombre'] = $value['nombre'];
			}else{
				$this->estadocuenta[$key]['nombre'] = $value['username'];
			}


			$this->estadocuenta[$key]['balance'] = number_format((float)$value['balance'],2,'.',',');
	}

	return $this->estadocuenta;
}


	
	public function getNotificacion(){

	
	}


	public function Buscar($post){

		$this->setFecha1($post['date_start']);
		$this->setFecha2($post['date_end']);

		$this->setFechainicio($post['date_start']);
		$this->setFechafin($post['date_end']);

		$this->CargarData();

	}

	private function ErrorLog($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\reportehotel.txt', '['.date('d/M/Y h:i:s A').' | '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['notification'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>