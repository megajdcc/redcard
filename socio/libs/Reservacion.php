<?php 

namespace socio\libs;

use PDO;
use CURL;
use assets\libs\connection;

/**
 * @author Crespo Jhonatan
 * @since 30/06/2019
 */
class Reservacion {
	

	private $conexion = null;
	private $idsocio = null;
	private $telefonosocio = null;
	private $telefononegocio = null;

	private $mensaje = '';
	public $catalogo = array();

	function __construct(connection $conec)
	{
		$this->conexion = $conec->con;
		$this->idsocio = $_SESSION['user']['id_usuario'];
		$this->cargar();
	}

	private function cargarDatos(){

		$sql = "SELECT concat(u.nombre,' ', u.apellido) as nombre, u.username, u.telefono, concat(r.fecha,' a las ',r.hora) as fecha, r.numeropersona,n.nombre as negocio
	from usuario as u  left join reservacion as r on u.id_usuario = r.usuario_solicitante join negocio as n on r.id_restaurant = n.id_negocio  
	where u.id_usuario = :socio and r.status = 0 and r.id = (select max(id) from reservacion where usuario_solicitante = :socio1)";
		$stm = $this->conexion->prepare($sql);
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
	}


	private function cargarNegocio(){

		$sql = "SELECT n.id_negocio, n.nombre as negocio, u.username, concat(u.nombre,' ',u.apellido) as nombrecompleto,
		concat(r.fecha,' ',r.hora) as fechareserva,
        r.numeropersona as numeropersona from reservacion as r join negocio as n on r.id_restaurant = n.id_negocio
			join usuario as u on r.usuario_solicitante = u.id_usuario
            join negocio_telefono as nt on n.id_negocio = nt.id_negocio
			where r.usuario_solicitante = :socio
				order by r.id desc limit 1 ";

				$stm = $this->conexion->prepare($sql);
				$stm->bindParam(':socio', $this->idsocio,PDO::PARAM_INT);
				$stm->execute();

				while($row = $stm->fetch(PDO::FETCH_ASSOC)){

					$sql = "SELECT nt.telefono from negocio_telefono as nt where nt.id_negocio =:negocio";

					$stmt = $this->conexion->prepare($sql);
					$stmt->bindParam(':negocio',$row['id_negocio']);

					$stmt->execute();

					$numeronegocio  = $stmt->fetch(PDO::FETCH_ASSOC)['telefono'];

					if(substr($numeronegocio, 0,2) != '52'){
						$numeronegocio = '52'.$row['telefono'];
					}

					$this->telefononegocio  = $numeronegocio;

					$nombre = $row['username'];

					if(!empty($row['nombrecompleto'])){
						$nombre = $row['nombrecompleto'];

					}
					$personas = $row['numeropersona'];
					$this->mensaje = 'TravelPoints: New reservation direct , client '.$nombre.' date '.$row['fechareserva'].'. Personas '.$personas.'. all details in travelpoints.com.mx';

				}
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



	public function enviarmensaje(){

		$this->cargarDatos();

		$url = 'https://api.broadcastermobile.com/brdcstr-endpoint-web/services/messaging/';

		$cuerpo = array('apiKey' => 407,'country'=>"MX",'dial'=>"26262",'message'=>$this->mensaje,
						'msisdns'=>[''.$this->telefonosocio.''],'tag'=>'Travel Points');

	

		$ch= curl_init($url);

		curl_setopt_array($ch, array(
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_RETURNTRANSFER 	=> 1,
			CURLOPT_POSTFIELDS     	=> json_encode($cuerpo),
			CURLOPT_HTTPHEADER 		=> array('Accept:application/json',
										'Content-Type:application/json',
										'Authorization:CjqJxRd+vMYzPvcPuIK4c+3lTyo=') 
		));
		$result = curl_exec($ch);
		$resultado = false;

		if(curl_getinfo($ch,CURLINFO_HTTP_CODE) === 200){
			$resultado  = json_encode(str_replace('\'', '',$result)); 
		}

		$this->sms_negocio();

		return $resultado;
	
	}

	private function sms_negocio(){

		$this->cargarNegocio();


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
										'Authorization:CjqJxRd+vMYzPvcPuIK4c+3lTyo=') 
		));
		$result = curl_exec($ch);
		$resultado = false;

		if(curl_getinfo($ch,CURLINFO_HTTP_CODE) === 200){
			$resultado  = json_encode(str_replace('\'', '',$result)); 
		}

		return $resultado;
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