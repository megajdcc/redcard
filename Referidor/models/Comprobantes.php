<?php 
namespace Referidor\models;

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

	private $referidor = array(
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
		$this->referidor['id'] = $_SESSION['id_referidor'];
		$this->cargarComprobantes();
		return; 

	}


	public function procesarretiro(array $post){

			$this->con->beginTransaction();
			$query = "insert into retiro(mensaje,id_usuario_solicitud,monto,id_referidor) values(:mensaje,:usuario,:monto,:referidor)";

			try {

					$stm = $this->con->prepare($query);
					$stm->execute(array(':mensaje'=>$post['mensaje'],
									':usuario'=>$_SESSION['user']['id_usuario'],
									':monto'=>$post['monto'],
									':referidor'=>$this->referidor['id']));
					$last_id = $this->con->lastInsertId();

			} catch (PDOException $e) {
				
			}

			$query2 = "insert into retirocomisionreferidor(negocio,usuario,id_retiro) value('Retiro de comisión','Retiro de comisión',:idretiro)";

			try {
					$stm = $this->con->prepare($query2);
					$stm->bindParam(':idretiro',$last_id,PDO::PARAM_INT);
					$stm->execute();
					$last_id_retiro = $this->con->lastInsertId();
			} catch (PDOException $e) {
				
			}
			
			$query = "SELECT  brf.balance as balance
 					from  balancereferidor as brf 
 				where brf.id = (select max(id) from balancereferidor)";
				$stm = $this->con->prepare($query);
				$stm->execute();
				$ultimobalance = $stm->fetch(PDO::FETCH_ASSOC)['balance'];
				$balance = $ultimobalance - $post['monto'];

				$query3 ="insert into balancereferidor(balance,id_referidor,comision,id_retiro) value(:balance,:referidor,:comision,:retiro)";
				
				try {
						$stm = $this->con->prepare($query3);
						$stm->execute(array(':balance'=>$balance,
						                    ':referidor'=>$this->referidor['id'],
											':comision'=>'-'.$post['monto'],
											':retiro'=>$last_id_retiro));
						$this->con->commit();
				} catch (PDOException $e) {
						
				}
	}

	private function cargarComprobantes(){
							$query = "select  r.id, r.creado,r.actualizado,aprobado,r.recibo,r.monto,r.id_referidor,
							r.id_referidor,r.id_hotel,CONCAT(u.nombre,' ',u.apellido) as nombre, u.username,r.id_usuario_aprobacion
							from retiro as r join referidor as rf on r.id_referidor = rf.id
							join usuario as u on  r.id_usuario_solicitud = u.id_usuario
							where rf.id = :rf";
		$stm = $this->con->prepare($query);
		$stm->bindParam(':rf',$this->referidor['id'],PDO::PARAM_INT);
		$stm->execute();
		return $this->comprobantes = $stm->fetchAll(PDO::FETCH_ASSOC);
fs
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
		?>

			
			<tr id="<?php echo $value['id'];?>">
				<td><?php echo '# '.$value['id'];?></td>
				<td><?php echo $creado; ?></td>
				<td><?php echo $actualizado; ?></td>
				<td><?php echo $usuarioaprobador; ?></td>
				<td><?php echo $aprobado; ?></td>
				<td><?php echo $monto; ?></td>
				<td><?php 
						if($aprobado == 'Si'){
							echo '<button type="button" data-retiro="'. $value['id'].'" class="archivo"><i class="fa fa-file-pdf-o"></i> Descargar</button>';
				}?>
				</td>
			</tr>
		<?php }
}


private function getUsuario(int $usuario= null){

	$query = "select concat(u.nombre,' ',u.apellido) as nombre, u.username from usuario  u 
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