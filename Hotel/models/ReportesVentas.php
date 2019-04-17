<?php 

namespace Hotel\models;
use assets\libs\connection;
use PDO;


/**
 * @author Crespo Jhonatan... 
 */
class ReportesVentas{

	//Propiedades... 
	
	private $con; 

	private $negocios = array(
		'id' => null,
		'fecha_inicio' => null,
		'fecha_fin' => null,
		'negocios' => array(),
		'id_usuario' => null
	);



	private $venta = array();

	private $error = array('notificacion' => null, 'fecha_inicio' => null, 'fecha_fin' => null);

	private $hotel = array(
		'id' => null,
	);

	private $estadocuenta = array();

	function __construct(connection $con){
		$this->con = $con->con;
		$this->hotel['id'] = $_SESSION['id_hotel'];
		return;
	}

	//  METHODOS DE CLASS
	
	public function CargarData(){
		
		$query = "select ne.nombre as negocio, u.username, CONCAT(u.nombre,' ',u.apellido) as nombre, nv.venta, bh.comision, bh.balance,nv.creado 
				FROM negocio_venta as nv
					JOIN negocio as ne on nv.id_negocio = ne.id_negocio
					JOIN balancehotel as bh on nv.id_venta = bh.id_venta
					JOIN hotel as h on bh.id_hotel = h.id	
					JOIN huespedhotel hh on h.id = hh.id_hotel
					JOIN huesped hu on hh.id_huesped = hu.id
					JOIN usuario as u on hu.id_usuario = u.id_usuario
					where h.id =:hotel ORDER BY creado ASC";

			$stm = $this->con->prepare($query);
			$stm->execute(array(':hotel'=>$this->hotel['id']));
			$this->estadocuenta = $stm->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getEstadoCuenta(){

		foreach ($this->estadocuenta as $key => $value) {
			$fecha = date('d/m/Y H:m:s', strtotime($value['creado']));

			if(!empty($value['nombre'])){
				$nombre = $value['nombre'];
			}else{
				$nombre = $value['username'];
			}
			?>
		 		<tr>
					<td><?php echo $fecha ?></td>
					<td><?php echo $value['negocio'] ?></td>
					<td><?php echo $nombre ?></td>
					<td><?php echo $value['venta']; ?></td>
					<td><?php echo $value['comision']; ?></td>
					<td><?php echo $value['balance'] ?></td>
				</tr>
	<?php   }

	}

	public function getNotificacion(){

	
	}

	private function ErrorLog($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\reportehotel.txt', '['.date('d/M/Y h:i:s A').' | '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['notification'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>