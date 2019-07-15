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
			$sql = "SELECT np.preferencia as logo,n.url, r.id,n.nombre as negocio,r.fecha,r.hora,r.status,concat(n.direccion,' ',c.ciudad,' ',e.estado,' tel - ',nt.telefono) as localizacion,r.numeropersona,r.observacion,r.id from reservacion as r 
			join negocio as n on r.id_restaurant = n.id_negocio
            join ciudad as c on n.id_ciudad = c.id_ciudad
            join estado as e on c.id_estado = e.id_estado
            left join negocio_telefono as nt on n.id_negocio = nt.id_negocio
            join negocio_preferencia as np on n.id_negocio = np.id_negocio
            join preferencia as p on np.id_preferencia = p.id_preferencia
            where r.usuario_solicitante = :user and p.id_preferencia = 3 order by r.status asc";

            try {

            	$stm = $this->conexion->prepare($sql);
            	$stm->bindParam(':user',$this->idsocio,PDO::PARAM_INT);
            	$stm->execute();
            
            } catch (\PDOException $e) {
            	
            }

            $this->catalogo = $stm->fetchAll(PDO::FETCH_ASSOC);

		}else{

			$sql = "SELECT np.preferencia as logo,n.url, r.id,n.nombre as negocio,r.fecha,r.hora,r.status,concat(n.direccion,' ',c.ciudad,' ',e.estado,' tel - ',nt.telefono) as localizacion, r.numeropersona,r.observacion,r.id from reservacion as r 
			join negocio as n on r.id_restaurant = n.id_negocio
            join ciudad as c on n.id_ciudad = c.id_ciudad
            join estado as e on c.id_estado = e.id_estado
            left join negocio_telefono as nt on n.id_negocio = nt.id_negocio
            left join negocio_preferencia as np on n.id_negocio = np.id_negocio
            left join preferencia as p on np.id_preferencia = p.id_preferencia
            where r.usuario_solicitante = :user and p.id_preferencia = 3 and (n.nombre like :busqueda1 || r.fecha like :busqueda2 || r.status like :busqueda3 || concat(n.direccion,' ',c.ciudad,' ',e.estado,' tel - ',nt.telefono) like :busqueda4 || r.hora like :busqueda5) order by r.status asc";

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


	public function getDatos(){
		return $this->catalogo;
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

			$observacion = $value['observacion'];

			if(empty($observacion)){
				$observacion = 'Sin Observaciones';
			}else{
				$observacion = '<strong class="observaciones" data-observacion="'._safe($value['observacion']).'">Observaciones</strong>';
			}

			$localizacion = '<strong class="localizacion" data-localizacion="'._safe($value['localizacion']).'">Localizaci&oacute;n</strong>';

				?>

				<tr class="content-row" id="<?php echo $value['id'] ?>" data-id="<?php echo $value['id']; ?>">
						<!-- <td class="key-img"> -->
					
			
							<!-- <div class="user user-md ">
							<a href="<?php //echo $urlbusinees;?>" target="_blank">
							<img class="img-thumbnail img-rounded" src="<?php //echo $urlimg.$value['logo'];?>">
							</a>
							</div> -->

						<!-- </td> -->
						<td class="row-1"><?php echo $value['negocio']; ?></td>
						<td class="row-2"><?php echo $value['fecha']; ?></td>
						<td class="row-2"><?php echo $value['numeropersona']; ?></td>
						<td class="row-3"><strong class="hora-reserva"><?php echo $value['hora']; ?></strong></td>
						<td class="row-4"> <strong class="<?php echo $clas;?>"><?php echo $status; ?></strong></td>
						<td class="row-direc"><button class="location" data-location="<?php echo $value['localizacion']; ?>" data-toggle="tooltip" title="Direcci&oacute;n" data-placement="left"><i class="fa fa-map"></i></button>
							</td>
							<td class="row-loca"><button class="observacion" data-observacion="<?php echo $value['observacion']; ?>" data-toggle="tooltip" title="Observaci&oacute;n" data-placement="left"><i class="fa fa-list-alt"></i></button></td>

						<td>
							<?php if ($value['status'] == 0): ?>
								<button data-toggle="tooltip" title="Cancelar reservaci&oacute;n" data-placement="left" type="button" class="cancelar" data-reserva="<?php echo $value['id'] ?>"><i class="fa fa-remove"></i></button>
							<?php endif ?>
						</td>
							
					</tr>
				
			<?php  }


	}



	public function CancelarReserva(int $idreserva){

		if($this->conexion->inTransaction()){
			$this->conexion->rollBack();
		}

		$sql = "UPDATE reservacion set status = 3  where id = :reserva";
		$this->conexion->beginTransaction();
		try {
			$stm = $this->conexion->prepare($sql);
			
			$stm->execute(array(':reserva'=>$idreserva));

			$this->conexion->commit();

		} catch (\PDOException $e) {
			$this->conexion->rollBack();
			return false;
		}

		return true;
	}

	
}

 ?>