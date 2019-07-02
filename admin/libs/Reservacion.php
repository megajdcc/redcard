<?php 

namespace admin\libs;

use PDO;
use assets\libs\connection;

/**
 * @author Crespo Jhonatan
 * @since 01/07/2019
 */
class Reservacion
{
	private $con = null;

	private $catalogo = array();
	function __construct(connection $conec){
		$this->con = $conec->con;
		$this->cargar();
	}



	// METHOD
	// 
	// 
	// 
	// 
	
	private function cargar(){

		$sql = "SELECT concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
			concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id from reservacion as r 
			join usuario as u on r.usuario_solicitante = u.id_usuario
            left join hotel as h on r.id_hotel = h.id ";

		try {
			$stm = $this->con->prepare($sql);
			$stm->execute();
		} catch (\PDOException $e) {
			// echo $e->getMessage();
		}
		


		$this->catalogo = $stm->fetchAll(PDO::FETCH_ASSOC);

	}
	
	public function report(array $post){

	}

	public function Buscar(array $post){

	}

	public function getNotificacion(){

	}


	public function getFecha1(){

	}

	public function getFecha2(){

	}

	public function getReservaConcierge(){

		$sql = "select count(r.id) as reservas, u.username, concat(u.nombre, ' ', u.apellido) as nombrecompleto from reservacion as r 
			join usuario as u on r.usuario_registrante = u.id_usuario
            group by u.username";

            $stm = $this->con->prepare($sql);

            $stm->execute();
            return $stm;

	}

	public function getReservaNegocio(){

		$sql = "SELECT count(r.id) as reservas, n.nombre as negocio from reservacion as r join negocio as n 
			on r.id_restaurant = n.id_negocio
			group by n.nombre";

            $stm = $this->con->prepare($sql);

            $stm->execute();
            return $stm;

	}

	public function getReservaciones(){

		$sql = "SELECT u.username, concat(u.nombre,' ',u.apellido) as nombrecompleto from usuario as u join reservacion as r on u.id_usuario = r.usuario_registrante
				where u.id_usuario =:registrante";

		foreach ($this->catalogo as $key => $value) {
				if(!empty($value['nombrecompleto'])){
					$user = $value['nombrecompleto'];
				}else{
					$user = $value['username'];
				}

				if(!empty($value['hotel'])){
					$hotel = $value['hotel'];
				}else{
					$hotel = 'directo (sin hotel)';
				}

					switch ($value['status']) {
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


					$stm = $this->con->prepare($sql);
					$stm->bindParam(':registrante',$value['usuario_registrante']);

					$stm->execute();

					if($row = $stm->fetch(PDO::FETCH_ASSOC)){

						if(empty($row['nombrecompleto'])){
							$usuario_registrante = $row['username'];
						}else{
							$usuario_registrante = $row['nombrecompleto'];
						}
					}

			?>
				
				<tr id="<?php echo $value['id']; ?>">
					<th><?php echo $value['id']; ?></th>
					<td><?php echo $value['fecha'] ?></td>
					<td><?php echo $hotel; ?></td>
					<td><?php echo $usuario_registrante; ?></td>
					<td><?php echo $user; ?></td>
					<td><?php echo $value['numeropersona']; ?></td>
					<td>	
									<?php  if($status == 'Agendada'){?>
									<button class="btn-agendada"><strong class="<?php echo $clas ?>">Agendada</strong></button>
									<?php  }else{?>
									<strong  class="<?php echo $clas ?>">
									<?php echo $status;?>
									</strong>
									<?php 	} ?>
				</td>
					
				</tr>


		<?php }

	}


}
 ?>