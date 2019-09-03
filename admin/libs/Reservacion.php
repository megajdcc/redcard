<?php 

namespace admin\libs;
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
use \Dompdf\Dompdf as pdf;
use \Dompdf\Options;


use assets\libs\Reports;

use PDO;
use assets\libs\connection;

/**
 * @author Crespo Jhonatan
 * @since 01/07/2019
 */
class Reservacion extends Reports
{

	private $catalogo = array('');

	private $busqueda = array(
			'fechainicio' => null,
			'fechafin' => null
		);

	function __construct(connection $conec){
		parent::__construct($conec);
		$this->cargar();
	}

	public $filtro = 0;

	public $hotel = null;
	public $restaurant = null;
	// METHOD
	
	private function cargar(int $filtro = 0,array $datos = null){
		$this->filtro = $filtro;
		

		$sql1 = "SELECT n.nombre from negocio as n where n.id_negocio = :negocio";

		$stm  = $this->conec->prepare($sql1);
		$stm->bindParam(':negocio',$datos['restaurant']);
		$stm->execute();

		$this->restaurant = $stm->fetch(PDO::FETCH_ASSOC)['nombre'];

		$sql2 = "SELECT h.nombre from hotel as h where h.id = :hotel";

		$stm  = $this->conec->prepare($sql2);
		$stm->bindParam(':hotel',$datos['hotel']);
		$stm->execute();

		$this->hotel = $stm->fetch(PDO::FETCH_ASSOC)['nombre'];

		switch ($filtro) {
			case 0:

					if($datos['restaurant'] !=0 and $datos['hotel'] == 0){
						$sql = "SELECT n.nombre as negocio, r.creado, concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
						concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id,r.id_promotor from reservacion as r 
						join usuario as u on r.usuario_solicitante = u.id_usuario
						left join hotel as h on r.id_hotel = h.id
						left join negocio as n on r.id_restaurant = n.id_negocio
							where r.fecha = :fecha and n.id_negocio = :restaurant";

						$datos = array(
										':fecha' =>date('Y-m-d'), 
										':restaurant' =>$datos['restaurant'],
									);
					}else if($datos['restaurant'] !=0 and $datos['hotel'] != 0){

						$sql = "SELECT n.nombre as negocio, r.creado, concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
						concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id,r.id_promotor  from reservacion as r 
						join usuario as u on r.usuario_solicitante = u.id_usuario
						left join hotel as h on r.id_hotel = h.id
						left join negocio as n on r.id_restaurant = n.id_negocio
							where r.fecha = :fecha and n.id_negocio = :restaurant and h.id = :hotel";

						$datos = array(
										':fecha'      =>date('Y-m-d'), 
										':restaurant' =>$datos['restaurant'],
										':hotel'      =>$datos['hotel'],
									);

					}else if($datos['restaurant'] ==0 and $datos['hotel'] != 0){

						$sql = "SELECT n.nombre as negocio, r.creado, concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
						concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id,r.id_promotor  from reservacion as r 
						join usuario as u on r.usuario_solicitante = u.id_usuario
						left join hotel as h on r.id_hotel = h.id
						left join negocio as n on r.id_restaurant = n.id_negocio
							where r.fecha = :fecha and h.id = :hotel";

						$datos = array(
										':fecha'      =>date('Y-m-d'), 
										':hotel'      =>$datos['hotel'],
									);

					}else{
						$sql = "SELECT n.nombre as negocio, r.creado, concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
						concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id,r.id_promotor  from reservacion as r 
						join usuario as u on r.usuario_solicitante = u.id_usuario
						left join hotel as h on r.id_hotel = h.id
						left join negocio as n on r.id_restaurant = n.id_negocio
							where r.fecha = :fecha";

						$datos = array(':fecha'      =>date('Y-m-d'));
					}
					
					
					try {
						$stm = $this->conec->prepare($sql);
						$stm->execute($datos);
					} catch (\PDOException $e) {
					// echo $e->getMessage();
					}
				break;
			case 1:
				if($datos['restaurant'] !=0 and $datos['hotel'] == 0){
						$sql = "SELECT n.nombre as negocio, r.creado, concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
						concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id,r.id_promotor  from reservacion as r 
						join usuario as u on r.usuario_solicitante = u.id_usuario
						left join hotel as h on r.id_hotel = h.id
						left join negocio as n on r.id_restaurant = n.id_negocio
							where day(r.fecha) = :diaanterior and r.id_restaurant = :restaurant";

						$datos = array(
							':diaanterior'=>date('d')-1,
							':restaurant'=>$datos['restaurant']
						);

					}else if($datos['restaurant'] !=0 and $datos['hotel'] != 0){
						$sql = "SELECT n.nombre as negocio, r.creado, concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
						concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id,r.id_promotor  from reservacion as r 
						join usuario as u on r.usuario_solicitante = u.id_usuario
						left join hotel as h on r.id_hotel = h.id
						left join negocio as n on r.id_restaurant = n.id_negocio
							where day(r.fecha) = :diaanterior and r.id_restaurant = :restaurant and h.id = :hotel";

						$datos = array(
							':diaacterior' =>date('d')-1,
							':restaurant'  =>$datos['restaurant'],
							':hotel'       =>$datos['hotel']
						);
					}else if($datos['restaurant'] ==0 and $datos['hotel'] != 0){
						$sql = "SELECT n.nombre as negocio, r.creado, concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
						concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id,r.id_promotor  from reservacion as r 
						join usuario as u on r.usuario_solicitante = u.id_usuario
						left join hotel as h on r.id_hotel = h.id
						left join negocio as n on r.id_restaurant = n.id_negocio
							where day(r.fecha) = :diaanterior and h.id = :hotel";

						$datos = array(
							':diaanterior' =>date('d')-1,
							':hotel'       =>$datos['hotel']);
					}else{
						$sql = "SELECT n.nombre as negocio, r.creado, concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
						concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id,r.id_promotor  from reservacion as r 
						join usuario as u on r.usuario_solicitante = u.id_usuario
						left join hotel as h on r.id_hotel = h.id
						left join negocio as n on r.id_restaurant = n.id_negocio
							where day(r.fecha) = :diaanterior";

						$datos = array(':diaanterior' =>date('d')-1);
					}
				
					
					try {
						$stm = $this->conec->prepare($sql);
			
						$stm->execute($datos);
					} catch (\PDOException $e) {
					// echo $e->getMessage();
					}
				break;
			case 2:

				if($datos['restaurant'] !=0 and $datos['hotel'] == 0){
						$sql = "SELECT n.nombre as negocio, r.creado, concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
						concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id,r.id_promotor  from reservacion as r 
						join usuario as u on r.usuario_solicitante = u.id_usuario
						left join hotel as h on r.id_hotel = h.id
						left join negocio as n on r.id_restaurant = n.id_negocio
							where month(r.fecha) = :mes and r.id_restaurant = :restaurant";

						$datos = array(
							':mes' => date('m'),
							':restaurant' =>$datos['restaurant'] 
						);
				}else if($datos['restaurant'] !=0 and $datos['hotel'] != 0){
					$sql = "SELECT n.nombre as negocio, r.creado, concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
						concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id,r.id_promotor  from reservacion as r 
						join usuario as u on r.usuario_solicitante = u.id_usuario
						left join hotel as h on r.id_hotel = h.id
						left join negocio as n on r.id_restaurant = n.id_negocio
							where month(r.fecha) = :mes and r.id_restaurant = :restaurant and h.id =:hotel";

						$datos = array(
							':mes'        => date('m'),
							':restaurant' =>$datos['restaurant'],
							':hotel'      =>$datos['hotel'] 
						);
					}else if($datos['restaurant'] ==0 and $datos['hotel'] != 0){
						$sql = "SELECT n.nombre as negocio, r.creado, concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
						concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id ,r.id_promotor from reservacion as r 
						join usuario as u on r.usuario_solicitante = u.id_usuario
						left join hotel as h on r.id_hotel = h.id
						left join negocio as n on r.id_restaurant = n.id_negocio
							where month(r.fecha) = :mes and h.id =:hotel";

						$datos = array(
							':mes'        => date('m'),
							':hotel'      =>$datos['hotel'] 
						);
					}else{
						$sql = "SELECT n.nombre as negocio, r.creado, concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
						concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id ,r.id_promotor from reservacion as r 
						join usuario as u on r.usuario_solicitante = u.id_usuario
						left join hotel as h on r.id_hotel = h.id
						left join negocio as n on r.id_restaurant = n.id_negocio
							where month(r.fecha) = :mes";

						$datos = array(
							':mes'        => date('m')
						);
					}


				
					
					try {
						$stm = $this->conec->prepare($sql);
						$stm->execute($datos);
					} catch (\PDOException $e) {
					// echo $e->getMessage();
					}
				break;
				case 3:

					if($datos['restaurant'] !=0 and $datos['hotel'] == 0){
							$sql = "SELECT n.nombre as negocio, r.creado, concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
						concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id,r.id_promotor  from reservacion as r 
						join usuario as u on r.usuario_solicitante = u.id_usuario
						left join hotel as h on r.id_hotel = h.id
						left join negocio as n on r.id_restaurant = n.id_negocio
							where month(r.fecha) = :mesanterio and r.id_restaurant = :restaurant";

							$datos = array(
								':mesanterio' => date('m') -1, 
								':restaurant' => $datos['restaurant'], 
							);
					}else if($datos['restaurant'] !=0 and $datos['hotel'] != 0){
						$sql = "SELECT n.nombre as negocio, r.creado, concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
						concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id ,r.id_promotor from reservacion as r 
						join usuario as u on r.usuario_solicitante = u.id_usuario
						left join hotel as h on r.id_hotel = h.id
						left join negocio as n on r.id_restaurant = n.id_negocio
							where month(r.fecha) = :mesanterio and r.id_restaurant = :restaurant and h.id = :hotel";

							$datos = array(
								':mesanterio' => date('m') -1, 
								':restaurant' => $datos['restaurant'], 
								':hotel' => $datos['hotel'], 
							);
					}else if($datos['restaurant'] ==0 and $datos['hotel'] != 0){
						$sql = "SELECT n.nombre as negocio, r.creado, concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
						concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id,r.id_promotor  from reservacion as r 
						join usuario as u on r.usuario_solicitante = u.id_usuario
						left join hotel as h on r.id_hotel = h.id
						left join negocio as n on r.id_restaurant = n.id_negocio
							where month(r.fecha) = :mesanterio and h.id = :hotel";

							$datos = array(
								':mesanterio' => date('m') -1, 
								':hotel' => $datos['hotel'], 
							);
					}else{
						$sql = "SELECT n.nombre as negocio, r.creado, concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
						concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id,r.id_promotor  from reservacion as r 
						join usuario as u on r.usuario_solicitante = u.id_usuario
						left join hotel as h on r.id_hotel = h.id
						left join negocio as n on r.id_restaurant = n.id_negocio
							where month(r.fecha) = :mesanterio ";
							$datos = array(':mesanterio' => date('m') -1 );
					}


				
					
					try {
						$stm = $this->conec->prepare($sql);
				
						$stm->execute($datos);
					} catch (\PDOException $e) {
					// echo $e->getMessage();
					}
				break;
				case 4:
				$this->busqueda['fechainicio'] = $datos['rango1'];
				$this->busqueda['fechafin'] = $datos['rango2'];

				if($datos['restaurant'] !=0 and $datos['hotel'] == 0){
					$sql = "SELECT n.nombre as negocio, r.creado, concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
						concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id ,r.id_promotor from reservacion as r 
						join usuario as u on r.usuario_solicitante = u.id_usuario
						left join hotel as h on r.id_hotel = h.id
						left join negocio as n on r.id_restaurant = n.id_negocio
							where (r.fecha between :fecha1 and :fecha2) and r.id_restaurant = :restaurant";

					$datos = array(
						':fecha1' => $this->busqueda['fechainicio'], 
								':fecha2' => $this->busqueda['fechafin'],
						':restaurant' => $datos['restaurant'], 
					);
				}else if($datos['restaurant'] !=0 and $datos['hotel'] != 0){
					$sql = "SELECT n.nombre as negocio, r.creado, concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
						concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id ,r.id_promotor from reservacion as r 
						join usuario as u on r.usuario_solicitante = u.id_usuario
						left join hotel as h on r.id_hotel = h.id
						left join negocio as n on r.id_restaurant = n.id_negocio
							where (r.fecha between :fecha1 and :fecha2) and r.id_restaurant = :restaurant and h.id = :hotel";

					$datos = array(
						':fecha1' => $this->busqueda['fechainicio'], 
								':fecha2' => $this->busqueda['fechafin'],
						':restaurant' => $datos['restaurant'], 
						':hotel' => $datos['hotel']);

					}else if($datos['restaurant'] ==0 and $datos['hotel'] != 0){
							$sql = "SELECT n.nombre as negocio, r.creado, concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
							concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id ,r.id_promotor from reservacion as r 
							join usuario as u on r.usuario_solicitante = u.id_usuario
							left join hotel as h on r.id_hotel = h.id
							left join negocio as n on r.id_restaurant = n.id_negocio
							where h.id = :hotel and (r.fecha between :fecha1 and :fecha2)";
							
							$datos = array(
								':fecha1' => $this->busqueda['fechainicio'], 
								':fecha2' => $this->busqueda['fechafin'],
								':hotel' => $datos['hotel']
						);
					}else{
							$sql = "SELECT n.nombre as negocio, r.creado, concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
							concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id,r.id_promotor  from reservacion as r 
							join usuario as u on r.usuario_solicitante = u.id_usuario
							left join hotel as h on r.id_hotel = h.id
							left join negocio as n on r.id_restaurant = n.id_negocio
							where r.fecha between :fecha1 and :fecha2";
							
							$datos = array(
								':fecha1' => $this->busqueda['fechainicio'], 
								':fecha2' => $this->busqueda['fechafin']
							);
					}
					
					try {
						$stm = $this->conec->prepare($sql);
						$stm->execute($datos);
					
					} catch (\PDOException $e) {
					// echo $e->getMessage();
					}
				break;

				case 5:
				$sql = "SELECT n.nombre as negocio, r.creado, concat(r.fecha,' ', r.hora) as fecha, h.nombre as hotel, u.username, 
							concat(u.nombre, ' ', u.apellido) as nombrecompleto,r.usuario_registrante, r.numeropersona,r.status, r.id,r.id_promotor  from reservacion as r 
							join usuario as u on r.usuario_solicitante = u.id_usuario
							left join hotel as h on r.id_hotel = h.id
							left join negocio as n on r.id_restaurant = n.id_negocio
							";
							
					try {
						$stm = $this->conec->prepare($sql);
						$stm->execute();
					
					} catch (\PDOException $e) {
					// echo $e->getMessage();
					}

				break;
			
			default:
				# code...
				break;
		}
		$this->catalogo = $stm->fetchAll(PDO::FETCH_ASSOC);	
	}
	

	public function getRestaurantes(){

		$sql = "SELECT n.nombre as restaurant, n.id_negocio as id  from negocio as n 
			join negocio_categoria as nc on n.id_categoria = nc.id_categoria
			where nc.id_categoria = 1 order by n.nombre";

		$stm = $this->conec->prepare($sql);

		$stm->execute();


		while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
			
			echo "<option value=".$row['id'].">".$row['restaurant']."</option>";
		}

	}
		public function getHoteles(){

		$sql = "SELECT h.nombre as hotel, h.id from hotel as h order by nombre";

		$stm = $this->conec->prepare($sql);

		$stm->execute();


		while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
			
			echo "<option value=".$row['id'].">".$row['hotel']."</option>";
		}

	}
	public function getDatos(array $datos){

		$this->cargar($datos['filtro'],$datos);

		$sql = "SELECT u.username, concat(u.nombre,' ',u.apellido) as nombrecompleto from usuario as u join reservacion as r on u.id_usuario = r.usuario_registrante
				where u.id_usuario =:registrante";


		$sql2 = "SELECT concat('Promotor - ',p.nombre,' ',p.apellido) as nombrecompleto FROM promotor  as p where id = :p";

		for ($i=0; $i < count($this->catalogo); $i++) { 

			if(empty($this->catalogo[$i]['hotel'])){
				$this->catalogo[$i]['hotel'] = 'directo (Sin hotel)';
			}


			if(!is_null($this->catalogo[$i]['id_promotor'])){

					$stm = $this->conec->prepare($sql2);
					$stm->execute(array(':p'=>$this->catalogo[$i]['id_promotor']));

					if($row = $stm->fetch(PDO::FETCH_ASSOC)){
				
						$this->catalogo[$i]['usuario_registrante'] = $row['nombrecompleto'];
					
					}


			}else{

				$stm = $this->conec->prepare($sql);
				$stm->bindParam(':registrante',$this->catalogo[$i]['usuario_registrante'],PDO::PARAM_INT);
				$stm->execute();

			if($row = $stm->fetch(PDO::FETCH_ASSOC)){

				if(empty($row['nombrecompleto'])){

						$this->catalogo[$i]['usuario_registrante'] = $row['username'];

					}else{

						$this->catalogo[$i]['usuario_registrante'] = $row['nombrecompleto'];

					}
					
				}else{

					$this->catalogo[$i]['usuario_registrante'] = 'directo';

				}

			}

			

		




			switch ($this->catalogo[$i]['status']) {
						case 0:
							$this->catalogo[$i]['status'] = "<strong class='sinconfirmar'>Agendada</strong>";
						break;
						case 1:
							$this->catalogo[$i]['status'] = "<strong class='consumada'>Consumada</strong>";
						break;
						case 2:
							$this->catalogo[$i]['status'] = "<strong class='confirmada'>Confirmada</strong>";
						break;
						case 3:
							$this->catalogo[$i]['status'] = "<strong class='cancelada'>Cancelada</strong>";
						break;
						case 4:
							$this->catalogo[$i]['status'] = "<strong class='cancelada'>Desfasada</strong>";
						break;
						
						default:
						# code...
						break;
					}

		}
		return $this->catalogo;
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

			$sql = "(SELECT count(r.id) as reservas, u.username, concat(u.nombre, ' ', u.apellido) as nombrecompleto from reservacion as r 
			JOIN usuario as u on r.usuario_registrante = u.id_usuario
			
			group by u.username)
			UNION
			(SELECT count(r.id) as reservas,p.username, concat('Promotor  - ',p.nombre, ' ', p.apellido) as nombrecompleto
			from reservacion as r join promotor as p on r.id_promotor = p.id
			group by p.username
			) ";

            $stm = $this->conec->prepare($sql);
            $stm->execute();
            return $stm;

	}

	public function getReservaNegocio(){

		$sql = "SELECT count(r.id) as reservas, n.nombre as negocio from reservacion as r join negocio as n 
			on r.id_restaurant = n.id_negocio
			group by n.nombre";

            $stm = $this->conec->prepare($sql);

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


					$stm = $this->conec->prepare($sql);
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
					<td><?php echo $value['negocio']; ?></td>
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

	private function setFechainicio($datetime = null){

		if($datetime){
			$datetime = str_replace('/', '-', $datetime);
			$datetime = strtotime($datetime);
			if(!$datetime){
				$this->error['fechainicio'] = 'Formato de fecha y hora incorrecto. Utiliza la herramienta.';
				return false;
			}
			$datetime = date("Y-m-d H:i:s", $datetime);
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
			$datetime = date("Y-m-d H:i:s", $datetime);
				
			$this->busqueda['fechafin'] = $datetime;
			return true;
		}
		$this->error['fechafin'] = 'Este campo es obligatorio.';
		return false;
	}

	public function getDataReservacacionAnualMensual(){

		$sql = "SELECT count(r.id) as reservaciones,r.status,month(r.fecha) as mes 
					from reservacion r
					where year(r.fecha) = year(now())
						group by r.status, monthname(r.fecha) order by month(r.fecha)";

		$stm = $this->conec->prepare($sql);
		$stm->execute();
		$cancelados = array('name'=>'Cancelados','data'=>array());
		$consumados = array('name'=>'Consumados','data'=>array());
		$agendados = array('name'=>'Agendados','data'=>array());

		$canceladosd = array();
		$consumadosd = array();
		$agendadosd = array();
		$meses = array();
		$datos = $stm->fetchAll(PDO::FETCH_ASSOC);


				foreach ($datos as $key => $value) {
					
						switch ($value['status']) {
							case 0:

							for ($i=1; $i <=12 ; $i++) { 
								if($value['mes'] == $i){

									$reservaciones = $value['reservaciones'];
									settype($reservaciones,'integer');

									$agendadosd[$i] = $reservaciones;
							}
							}
								
							break;

							case 1:
								for ($i=1; $i <=12 ; $i++) { 
								if($value['mes'] == $i){
										$reservaciones = $value['reservaciones'];
										settype($reservaciones,'integer');
									$consumadosd[$i] = $reservaciones;
								}
							}
							break;

							case 3:
								for ($i=1; $i <=12 ; $i++) { 
								if($value['mes'] == $i){
										$reservaciones = $value['reservaciones'];
										settype($reservaciones,'integer');
										$canceladosd[$i] = $reservaciones;
								}
								}
							
							break;
							
							
						}
					}

						for ($i=1; $i <= 12 ; $i++) { 
							foreach ($canceladosd as $key => $value) {
								if($key == $i){
									$canc = $canceladosd[$i];

									settype($canc,'integer');

									$cancelados['data'][$i] = $canc;
								}else{

									if(!array_key_exists($i, $canceladosd)){
										$cancelados['data'][$i] = 0;
									}

								
									
								}
							}

							foreach ($consumadosd as $key => $value) {
								if($key == $i){
									$consu = $consumadosd[$i];
									settype($consu,'integer');
									$consumados['data'][$i] = $consu;
								}else{

									if(!array_key_exists($i, $consumadosd)){
										$consumados['data'][$i] = 0;
									}

								
									
								}
							}

							foreach ($agendadosd as $key => $value) {
								if($key == $i){
									$agend = $agendadosd[$i];

									settype($agend,'integer');

									$agendados['data'][$i] = $agend;
								}else{

									if(!array_key_exists($i, $agendadosd)){
										$agendados['data'][$i] = 0;
									}
								}
							}
									
						}

						// echo var_dump($cancelados);
							$cancelados['data'] = array_values($cancelados['data']);
							$consumados['data'] = array_values($consumados['data']);
							$agendados['data'] = array_values($agendados['data']);

		array_push($meses, $agendados,$consumados,$cancelados);

		return $meses;
	}

	public function report(array $datos =null,String $title =''){

		$this->getDatos($datos);
		ob_start();
		require_once($_SERVER['DOCUMENT_ROOT'].'/admin/viewreports/reservaciones.php');

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
		$dato = array('Attachment' => 0,'compress' => 0);

		$fecha1 = date('M-Y', strtotime($this->busqueda['fechainicio']));
		
		$titulo = "Travel Points: Lista de reservaciones" .$fecha1;
		$dompdf->stream($titulo.'.pdf',$dato);
	}


}
 ?>