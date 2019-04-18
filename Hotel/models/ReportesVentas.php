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
	);

	public $estadocuenta = array();

	function __construct(connection $con){
		$this->con = $con->con;
 		$this->hotel['id']  = $_SESSION['id_hotel'];
		$this->CargarData();
		return;
	}

	//  METHODOS DE CLASS
	

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
	public function CargarData($fecha1 = null, $fecha2 = null){

		

			if(!empty($fecha1) && !empty($fecha2)){
				$this->setFechainicio($fecha1);
				$this->setFechafin($fecha2);

				$this->setFecha1($fecha1);
				$this->setFecha2($fecha2);



				// echo var_dump($this->busqueda);
				$query = "select ne.nombre as negocio, u.username, CONCAT(u.nombre,' ',u.apellido) as nombre, nv.venta, bh.comision, bh.balance,nv.creado 
							FROM balancehotel as bh 
							LEFT JOIN negocio_venta as nv on bh.id_venta = nv.id_venta
							LEFT JOIN negocio as ne on nv.id_negocio = ne.id_negocio 
							LEFT JOIN usuario as u on nv.id_usuario = u.id_usuario
							where bh.id_hotel = :hotel and nv.creado BETWEEN :fecha1 and :fecha2 ORDER BY creado ASC";
							$stm = $this->con->prepare($query);
							$stm->execute(array(':hotel'=>$this->hotel['id'],
												':fecha1' => $this->busqueda['fechainicio'],
												':fecha2' => $this->busqueda['fechafin']));

							$this->estadocuenta = $stm->fetchAll(PDO::FETCH_ASSOC);
		}else{
				$query = "   select ne.nombre as negocio, u.username, CONCAT(u.nombre,' ',u.apellido) as nombre, nv.venta, bh.comision, bh.balance,nv.creado 
 				FROM balancehotel as bh 
					LEFT JOIN negocio_venta as nv on bh.id_venta = nv.id_venta
 					LEFT JOIN negocio as ne on nv.id_negocio = ne.id_negocio 
 					LEFT JOIN usuario as u on nv.id_usuario = u.id_usuario
 					where bh.id_hotel = :hotel ORDER BY creado ASC";

			$stm = $this->con->prepare($query);
			$stm->execute(array(':hotel'=>$this->hotel['id']));
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



	public function mostrarpdf($fechaini = null, $fechafin = null){

		if($fechaini){

		}else{
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
			$dompdf->setPaper('A4', 'portrait');
			$dompdf->render();
			$dato = array('Attachment' => 0);
			$dompdf->stream("Solicitud.pdf",$dato);
		}

	}

	public function getEstadoCuenta(){
		
		$query = "select max(balance) as balance from balancehotel";

		$stm = $this->con->prepare($query);

		$stm->execute();

		$ultimobalance = $stm->fetch(PDO::FETCH_ASSOC)['balance'];
		
		foreach ($this->estadocuenta as $key => $value) {
			
			// $fecha = date('d/m/Y h:m:s', strtotime($value['creado']));
			 $fecha = $value['creado'];
			 // settype($value['venta'],'double');

			 $venta = number_format((float)$value['venta'],2,',','.');
			  if($venta < 0){
			  	$venta = '<strong class="negativo">$'.$venta.'</strong>';
			  }else{
			  	$venta = '$'.$venta;
			  }
			  if($value['comision'] < 0){
			  	
			  	$comision = '<strong class="negativo">$'.$value["comision"].'</strong>';
			  }else{
			  	$comision = '$'.$value['comision'];
			  }
			if(!empty($value['nombre'])){
				$nombre = $value['nombre'];
			}else{
				$nombre = $value['username'];
			}

			
			?>
			

		 		<tr class="estado">
					<td class="b1"><?php echo $fecha ?></td>
					<td class="b1"><?php echo $value['negocio'] ?></td>
					<td class="b1"><?php echo $nombre ?></td>
					<td class="b1"><?php echo $venta; ?></td>
					<td class="b1"><?php echo $comision; ?></td>
					<td class="b2">$<?php echo $value['balance'] ?></td>
				</tr>
<?php  }
	}


	
	public function getNotificacion(){

	
	}


	public function Buscar($post){

		$fechainicial = $post['f1'];
		$fechafin = $post['f2'];

		$this->CargarData($fechainicial,$fechafin);

	}

	private function ErrorLog($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\reportehotel.txt', '['.date('d/M/Y h:i:s A').' | '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['notification'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>