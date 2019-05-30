<?php 

namespace admin\libs;

use assets\libs\connection;

use PDO;
/**
 * @author Crespo Jhonatan 
 * @since 22/04/2019
 */
class Comprobantes
{
	

	private $con;
	private $comprobantes = array(

	);

	private $solicitudes = array();


	function __construct(connection $conection){
		$this->con = $conection->con;

		$this->cargarData();

	}




// METHODOS DE LA CLASE



	private function cargarData(){

			$query = "(select r.id as solicitud, r.creado,CONCAT(u.nombre,' ',u.apellido) as nombre, u.username,r.monto,r.aprobado, 'Hotel' as perfil,u.imagen,r.recibo
				from retiro as r 
				join usuario as u on r.id_usuario_solicitud  = u.id_usuario 
				join retirocomision as rc on r.id = rc.id_retiro )
				UNION
				
				(select r.id as solicitud, r.creado,CONCAT(u.nombre,' ',u.apellido) as nombre, u.username,r.monto,r.aprobado, 'Referidor' as perfil,u.imagen,r.recibo
				from retiro as r 
				join usuario as u on r.id_usuario_solicitud  = u.id_usuario 
				join retirocomisionreferidor as rc on r.id = rc.id_retiro )
				
				UNION
				
				(select r.id as solicitud, r.creado,CONCAT(u.nombre,' ',u.apellido) as nombre, u.username,r.monto,r.aprobado , 'Franquiciatario' as perfil,u.imagen,r.recibo
				from retiro as r 
				join usuario as u on r.id_usuario_solicitud  = u.id_usuario 
				join retirocomisionfranquiciatario as rc on r.id = rc.id_retiro)
				ORDER BY creado";

		$stm = $this->con->prepare($query);
		$stm->execute();
		$this->solicitudes = $stm->fetchALL(PDO::FETCH_ASSOC);

	}

	public function ListarSolicitudes(){

		$urlimg =  HOST.'/assets/img/user_profile/';
		foreach($this->solicitudes as $key => $value) {


			$monto = number_format((float)$value['monto'],2,',','.');

			$pago =$value['monto'];

			if(empty($value['nombre'])){
				$nombre = $value["username"];
			}else{
				$nombre = $value['nombre'];
 			}

			$fecha = date('d/m/Y g:i A', strtotime($value['creado']));

			if($value['aprobado'] == 1 ){
				$aprobado = 'Aprobada';
			}else{
				$aprobado = 'No aprobada';
			}
			$foto         = $value['imagen'];
			if(empty($foto) || is_null($foto)){
				$foto = 'default.jpg';
			}
			$perfil = $value['perfil'];
			$urlarchivo = HOST.'/assets/recibos/'.$value['recibo'];
			?>
				<tr id="<?php echo $value['solicitud'] ?>">
					<td><?php echo $key; ?></td>
					<td>
					<div class="user user-md">
						<a href="<?php echo HOST."/socio/".$value['username']; ?>" target="_blank"><img src="<?php echo $urlimg.$foto;?>"></a>
					</div>
					</td>
					<td><?php echo $nombre; ?></td>
					<td><?php echo $perfil ?></td>
					<td><?php echo '$'.$monto.' MXN'; ?></td>
					<td><?php echo $aprobado; ?></td>
					<td><?php echo $fecha ?></td>
					<td>
						<?php 
						if($aprobado == 'No aprobada'){	 ?>
							<button type="button" data-pago="<?php echo $pago; ?>" class="btn btn-primary aprobar" data-path="<?php echo  _safe($_SERVER['REQUEST_URI']); ?>" data-id="<?php echo $value['solicitud']?>" data-perfil="<?php echo $perfil; ?>" data-fecha="<?php echo $fecha; ?>" data-monto="<?php echo '$ '.$monto.' MXN'; ?>" data-toggle="tooltip" title="En mantenimiento" data-placement="left"  disabled> <i class="fa fa-check" ></i> Pagar</button>
						<?php }else{?>
								<button type="button" name='descargar' class="btn btn-warning " style="color:white !important;"><i class="fa fa-file-pdf-o"></i><a href="<?php echo $urlarchivo; ?>" target="_blank">Descargar</a></button>
						<?php  } ?>						
					</td>
				</tr>
			<?php  

		}
	}


	public function Aprobar(string $nombre, int $solicitud){

		$query = "UPDATE retiro set recibo=:recibo,id_usuario_aprobacion=:usuario, aprobado =1 where id=:solicitud";
		$stm = $this->con->prepare($query);
		$stm->bindParam(':recibo',$nombre,PDO::PARAM_STR);
		$stm->bindParam(':usuario',$_SESSION['user']['id_usuario']);
		$stm->bindParam(':solicitud',$solicitud,PDO::PARAM_INT);

		$stm->execute();

		header('location: '.HOST.'/admin/perfiles/comprobantes');
		die();

	}

	public function getNotificacion(){

	}
}

 ?>


