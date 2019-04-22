<?php 
namespace Franquiciatario\models;

use assets\libs\connection;
use PDO;

/**
 * 
 * @author Crespo Jhonatan
 * @since 18-04-19
 */
class Comprobantes
{
	
	private $con;
	private $hotel = array(
		'id' =>null,
	);

	private $franquiciatario = array(
		'id' =>null,
	);
	// private $comprobantes = array(
	// 	'id' =>null,
	// 	'creado'=>null,
	// 	'actualizado'=>null,
	// 	'aprobado'=>false,
	// 	'mensaje'=>null,
	// 	'id_usuario'=>null,
	// 	'id_usuario_aprobacion'=>null,
	// 	'recibo'=>null,
	// 	'monto'=>null
	// 	);
	// 	
	private $comprobantes = array();

	function __construct(connection $con){
		$this->con = $con->con;
		$this->hotel['id'] = $_SESSION['id_hotel'];
		$this->franquiciatario['id'] = $_SESSION['id_franquiciatario'];
		$this->cargarComprobantes();
		return; 

	}


	public function procesarretiro(array $post){

			$this->con->beginTransaction();
			$query = "insert into retiro(mensaje,id_usuario_solicitud,monto,id_franquiciatario) values(:mensaje,:usuario,:monto,:franquiciatario)";

			try {

					$stm = $this->con->prepare($query);
					$stm->execute(array(':mensaje'=>$post['mensaje'],
									':usuario'=>$_SESSION['user']['id_usuario'],
									':monto'=>$post['monto'],
									':franquiciatario'=>$this->franquiciatario['id']));
					$last_id = $this->con->lastInsertId();

			} catch (PDOException $e) {
				
			}

			$query2 = "insert into retirocomisionfranquiciatario(negocio,usuario,id_retiro) value('Retiro de comisión','Retiro de comisión',:idretiro)";

			try {
					$stm = $this->con->prepare($query2);
					$stm->bindParam(':idretiro',$last_id,PDO::PARAM_INT);
					$stm->execute();
					$last_id_retiro = $this->con->lastInsertId();
			} catch (PDOException $e) {
				
			}
			
			$query = "SELECT  bf.balance as balance
 					from  balancefranquiciatario as bf 
 				where bf.id = (select max(id) from balancefranquiciatario)";
				$stm = $this->con->prepare($query);
				$stm->execute();
				$ultimobalance = $stm->fetch(PDO::FETCH_ASSOC)['balance'];
				$balance = $ultimobalance - $post['monto'];

				$query3 ="insert into balancefranquiciatario(balance,id_franquiciatario,comision,id_retiro) value(:balance,:franquiciatario,:comision,:retiro)";
				
				try {
						$stm = $this->con->prepare($query3);
						$stm->execute(array(':balance'=>$balance,':franquiciatario'=>$this->franquiciatario['id'],
													':comision'=>'-'.$post['monto'],
													':retiro'=>$last_id_retiro));
						$this->con->commit();
				} catch (PDOException $e) {
						
				}
		
	}


	private function cargarComprobantes(){
		$query = "select  r.id, r.creado,r.actualizado,aprobado,r.recibo,r.monto,r.id_referidor,
					r.id_franquiciatario,r.id_hotel,CONCAT(u.nombre,' ',u.apellido) as nombre, u.username,r.id_usuario_aprobacion
				from retiro as r join franquiciatario as fr on r.id_franquiciatario = fr.id
							join usuario as u on  r.id_usuario_solicitud = u.id_usuario
						where fr.id = :fr";
		$stm = $this->con->prepare($query);
		$stm->bindParam(':fr',$this->franquiciatario['id'],PDO::PARAM_INT);
		$stm->execute();
		return $this->comprobantes = $stm->fetchAll(PDO::FETCH_ASSOC);

	}

public function getComprobantes(){

	foreach ($this->comprobantes as $key => $value) {
		$creado = $this->setFecha($value['creado']);

		$actualizado = $this->setFecha($value['actualizado']);
		$monto = number_format((float)$value['monto'],2,',','.');

		$usuarioaprobador = $this->getUsuario($value['id_usuario_aprobacion']);
		if($value['aprobado']){
			$aprobado = "Si";
		}else{
				$aprobado = "No";
		}
		$urlrecibo = HOST.'/assets/recibos/'.$value['recibo'];
		?>

			
			<tr id="<?php echo $value['id'];?>">
				<td><?php echo '# '.$value['id'];?></td>
				<td><?php echo $creado; ?></td>
				<td><?php echo $actualizado; ?></td>
				<td><?php echo $usuarioaprobador; ?></td>
				<td><?php echo $aprobado; ?></td>
				<td><?php echo $monto; ?></td>
				<td><?php 
						if($aprobado == 'Si'){?>
						<button type="button" data-retiro="<?php echo $value['id']; ?>" class=" btn btn-warning archivo"><i class="fa fa-file-pdf-o"></i> 	<a href="<?php echo $urlrecibo; ?>" target="_blank">Descargar</a></button>
				<?php  }?>
				</td>
			</tr>
		<?php }
}


private function getUsuario(int $usuario= null){

	$query = "select concat(u.nombre,' ',u.apellido) as nombre, u.username from usuario as u 
						where u.id_usuario = :usuario";

	$stm = $this->con->prepare($query);
	$stm->bindParam(':usuario',$usuario,PDO::PARAM_INT);
	$stm->execute();
	$valor = $stm->fetch(PDO::FETCH_ASSOC);

	if(!empty($valor['nombre'])){
		$nombre = $valor['nombre'];
	}else{
		$nombre = $valor['username'];
	}

	return $nombre;

}

private function setFecha($fecha){

	if($fecha){
		return date('d/m/Y h:i A', strtotime($fecha));
	}else{
		return 'Sin aprobar';
	}
	
}



public function getNotificacion(){

	
	}

}


 ?>