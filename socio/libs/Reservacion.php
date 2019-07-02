<?php 

namespace socio\libs;

use PDO;
use assets\libs\connection;

/**
 * @author Crespo Jhonatan
 * @since 30/06/2019
 */
class Reservacion {
	

	private $conexion = null;
	private $idsocio = null;

	public $catalogo = array();

	function __construct(connection $conec)
	{
		$this->conexion = $conec->con;
		$this->idsocio = $_SESSION['user']['id_usuario'];
		$this->cargar();
	}


	public function cargar(String $busqueda = null){
		
		if(empty($busquedad)){
			$sql = "SELECT np.preferencia as logo,n.url, r.id,n.nombre as negocio,r.fecha,r.hora,r.status,concat(n.direccion,' ',c.ciudad,' ',e.estado,' tel - ',nt.telefono) as localizacion from reservacion as r 
			join negocio as n on r.id_restaurant = n.id_negocio
            join ciudad as c on n.id_ciudad = c.id_ciudad
            join estado as e on c.id_estado = e.id_estado
            left join negocio_telefono as nt on n.id_negocio = nt.id_negocio
            join negocio_preferencia as np on n.id_negocio = np.id_negocio
            join preferencia as p on np.id_preferencia = p.id_preferencia
            where r.usuario_solicitante = :user and p.id_preferencia = 3";

            try {

            	$stm = $this->conexion->prepare($sql);
            	$stm->bindParam(':user',$this->idsocio,PDO::PARAM_INT);
            	$stm->execute();
            
            } catch (\PDOException $e) {
            	
            }

            $this->catalogo = $stm->fetchAll(PDO::FETCH_ASSOC);

		}else{

			$sql = "SELECT np.preferencia as logo,n.url, r.id,n.nombre as negocio,r.fecha,r.hora,r.status,concat(n.direccion,' ',c.ciudad,' ',e.estado,' tel - ',nt.telefono) as localizacion from reservacion as r 
			join negocio as n on r.id_restaurant = n.id_negocio
            join ciudad as c on n.id_ciudad = c.id_ciudad
            join estado as e on c.id_estado = e.id_estado
            left join negocio_telefono as nt on n.id_negocio = nt.id_negocio
            left join negocio_preferencia as np on n.id_negocio = np.id_negocio
            left join preferencia as p on np.id_preferencia = p.id_preferencia
            where r.usuario_solicitante = :user and p.id_preferencia = 3 and (n.nombre like :busqueda1 || r.fecha like :busqueda2 || r.status like :busqueda3 || concat(n.direccion,' ',c.ciudad,' ',e.estado,' tel - ',nt.telefono) like :busqueda4 || r.hora like :busqueda5)";

            try {
            	$stm = $this->conexion->prepare($sql);
            	$datos = array(':user' => $this->idsocio,
            					':busqueda1' => '%'.$busqueda.'%',
            					':busqueda2' => '%'.$busqueda.'%',
            					':busqueda3' => '%'.$busqueda.'%',
            					':busqueda4' => '%'.$busqueda.'%',
            					':busqueda5' => '%'.$busqueda.'%'
            					 );
          
            	$stm->execute($datos);
            } catch (\PDOException $e) {
            	
            }

            $this->catalogo = $stm->fetchAll(PDO::FETCH_ASSOC);

		}

		


	}



	public function get_notification(){

	}


	public function listreservas(){

			foreach ($this->catalogo as $key => $value) {
				$urlimg =  HOST.'/assets/img/business/logo/';
				$urlbusinees = HOST.'/'.$value['url'];
				$status = $value['status'];
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
				?>

				<tr class="content-row" data-id="<?php echo $value['id']; ?>">
						<td class="key-img">
						<?php echo $key;
					


						?>
							<div class="user user-md ">
							<a href="<?php echo $urlbusinees;?>" target="_blank">
							<img class="img-thumbnail img-rounded" src="<?php echo $urlimg.$value['logo'];?>">
							</a>
							</div>

						</td>
						<td class="row-1"><?php echo $value['negocio']; ?></td>
						<td class="row-2"><?php echo $value['fecha']; ?></td>
						<td class="row-3"><strong class="hora-reserva"><?php echo $value['hora']; ?></strong></td>
						<td class="row-4"> <strong class="<?php echo $clas;?>"><?php echo $status; ?></strong></td>
						<td class="row-5 localizacion"><?php echo $value['localizacion']; ?></td>
						<td class="row-6"></td>
						
					</tr>
				
			<?php  }


	}

	
}

 ?>