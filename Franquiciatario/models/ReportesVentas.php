<?php 

namespace Franquiciatario\models;
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
		'comision'=>null,
	);

	private $franquiciatario = array(
		'id' => null,
	);

	public $estadocuenta = array();

	function __construct(connection $con){
		$this->con = $con->con;
	
 		$this->hotel['id']  = $_SESSION['id_hotel'];
 		$this->franquiciatario['id']  = $_SESSION['id_franquiciatario'];
		$this->CargarData();
		$this->DatosHotel();
		return;
	}

	//  METHODOS DE CLASS
	
	
	private function DatosHotel(){

		$query = "select h.nombre as nombrehotel, h.comision from hotel  as h join franquiciatario fr on h.id = fr.id_hotel where fr.id = :franquiciatario";

		$stm = $this->con->prepare($query);

		$stm->execute(array(':franquiciatario'=>$this->franquiciatario['id']));

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

	public function CargarData($fecha1 = null, $fecha2 = null){

		$this->setFechainicio($fecha1);
		$this->setFechafin($fecha2);


		

			if(!empty($this->busqueda['fechainicio']) && !empty($this->busqueda['fechafin'])){

				// echo var_dump($this->busqueda);
						$query = "(select ne.nombre as negocio, u.username, CONCAT(u.nombre,' ',u.apellido) as nombre, nv.venta, bf.comision, bf.balance,nv.creado 
						FROM balance as bf 
						LEFT JOIN negocio_venta as nv on bf.id_venta = nv.id_venta
						LEFT JOIN negocio as ne on nv.id_negocio = ne.id_negocio 
						LEFT JOIN usuario as u on nv.id_usuario = u.id_usuario
						where bf.id_franquiciatario = :fr1 and bf.id_venta != 0  and bf.creado between :fecha1 and :fecha2 ORDER BY creado ASC)
						UNION 
						(select  rr.negocio, rr.usuario as username ,  rr.usuario as nombre ,CONCAT('-',r.monto) as venta,CONCAT('-',r.monto) as comision, bf.balance,bf.creado
						from retiro as r join retirocomision as rr on r.id = rr.id_retiro join balance as bf on rr.id = bf.id_retiro
						where bf.id_franquiciatario = :fr2  and rr.condicion = 2 and bf.creado between :fecha3 and :fecha4 ORDER BY creado ASC)
						UNION
						(select 'Reembolso' as negocio, 'Resto pago parcial' as username , 'reembolso', CONCAT('+',r.monto - r.pagado) as venta,
						CONCAT('+',r.monto - r.pagado) as comision, bfr.balance,bfr.creado
						from retirocomision as rr left join retiro as r on rr.id_retiro = r.id left join balance as bfr on rr.id = bfr.id_retiro
						where bfr.id_franquiciatario = :fr3 and r.tipo_pago = 2 and rr.condicion = 2 and bfr.creado between :fecha5 and :fecha6)

							order by creado";
							$stm = $this->con->prepare($query);
							$stm->execute(array(
												':fr1'    => $this->franquiciatario['id'],
												':fr2'    => $this->franquiciatario['id'],
												':fr3'    => $this->franquiciatario['id'],
												':fecha1' => $this->busqueda['fechainicio'],
												':fecha2' => $this->busqueda['fechafin'],
												':fecha3' => $this->busqueda['fechainicio'],
												':fecha4' => $this->busqueda['fechafin'],
												':fecha5' => $this->busqueda['fechainicio'],
												':fecha6' => $this->busqueda['fechafin']
											));

							$this->estadocuenta = $stm->fetchAll(PDO::FETCH_ASSOC);
		}else{
						$query = "(select ne.nombre as negocio, u.username, CONCAT(u.nombre,' ',u.apellido) as nombre, nv.venta, bf.comision, bf.balance,nv.creado 
						FROM balance as bf 
						LEFT JOIN negocio_venta as nv on bf.id_venta = nv.id_venta
						LEFT JOIN negocio as ne on nv.id_negocio = ne.id_negocio 
						LEFT JOIN usuario as u on nv.id_usuario = u.id_usuario
						where bf.id_franquiciatario = :fr1 and bf.id_venta != 0 ORDER BY creado ASC)
						UNION 
						(select  rr.negocio, rr.usuario as username ,  rr.usuario as nombre ,CONCAT('-',r.monto) as venta,CONCAT('-',r.monto) as comision, bf.balance,bf.creado
						from retiro as r join retirocomision as rr on r.id = rr.id_retiro join balance as bf on rr.id = bf.id_retiro
						where bf.id_franquiciatario = :fr2 and rr.condicion =1)
						UNION
						(select 'Reembolso' as negocio, 'Resto pago parcial' as username , 'reembolso', CONCAT('+',r.monto - r.pagado) as venta,
						CONCAT('+',r.monto - r.pagado) as comision, bfr.balance,bfr.creado
						from retirocomision as rr left join retiro as r on rr.id_retiro = r.id left join balance as bfr on rr.id = bfr.id_retiro
						where bfr.id_franquiciatario = :fr3 and r.tipo_pago = 2 and rr.condicion = 2 )
						order by creado";

			$stm = $this->con->prepare($query);
			$stm->execute(array(':fr1'=>$this->franquiciatario['id'],
								':fr2'=>$this->franquiciatario['id'],
								':fr3'=>$this->franquiciatario['id']
							));
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

			$this->setFechainicio($post['date_start']);
			$this->setFechafin($post['date_end']);
			$this->CargarData();

			ob_start();

			require_once($_SERVER['DOCUMENT_ROOT'].'/Franquiciatario/viewreports/estadocuenta.php');

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
		
		$query = "select max(balance) as balance from balance";

		$stm = $this->con->prepare($query);

		$stm->execute();

		$ultimobalance = $stm->fetch(PDO::FETCH_ASSOC)['balance'];
		
		foreach ($this->estadocuenta as $key => $value) {
			
			// $fecha = date('d/m/Y h:m:s', strtotime($value['creado']));
			 $fecha = $value['creado'];
			 // settype($value['venta'],'double');

			 $venta = number_format((float)$value['venta'],2,'.',',');
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

		$fechainicial = $post['date_start'];
		$fechafin = $post['date_end'];

		$this->setFecha1($_POST['date_start']);
		$this->setFecha2($_POST['date_end']);

		$this->CargarData($fechainicial,$fechafin);

	}

	private function ErrorLog($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\reportefranquiciatario.txt', '['.date('d/M/Y h:i:s A').' | '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['notification'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>