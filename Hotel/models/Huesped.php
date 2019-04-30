<?php 

namespace Hotel\models;

use assets\libs\connection;


use PDO;



/**
 * @author Crespo Jhonatan ... 
 */
class Huesped
{

	private $con;
	private $user = array('id_usuario' =>null);
	
	private $hotel = null, $telefono = null, $whatsapp = 0,$id = 0;

	function __construct($con = null){		

		$this->con = $con->con;	
		$this->user['id_usuario'] = $_SESSION['user']['id_usuario'];
		$this->cargarDatos();

	}


	public function procesar(array $post){

		$this->setNombreHotel($post['hotel']);
		$this->setTelefonomovil($post['telefono']);
		$this->setWhatsapp($post['whatsapp']);

		

		if($post['idhotel'] > 0){

			// es un hotel registrado en el sistema ... 
			 if($this->con->inTransaction()){$this->con->rollBack();};

			 $this->con->beginTransaction();
			$query = "insert into huesped(id_usuario,telefono_movil,whatsapp) value(:usuario,:telefono,:whatsapp)";


			try {
				$stm = $this->con->prepare($query);

				$stm->execute(array(':usuario' => $this->user['id_usuario'],
								':telefono' => $this->getTelefono(),
								':whatsapp' => $this->whatsapp ));

			} catch (PDOException $e) {
				
				$this->con->rollBack();

				return false;
			}
			
			$last_id = $this->con->lastInsertId();


			$query1 = "insert into huespedhotel(id_hotel,id_huesped) values(:hotel,:huesped)";

			try {
				$stm2 = $this->con->prepare($query1);
				$stm2->execute(array(':hotel' =>$post['idhotel'],
								':huesped'=>$last_id));

				$this->con->commit();
			} catch (PDOException $ex) {
				$this->con->rollBack();
				return false;
			}

			return true;
		}else{

			if($this->con->inTransaction()){$this->con->rollBack();};

			$this->con->beginTransaction();

			$query = "insert into huesped(hotel,telefono_movil,id_usuario,whatsapp) values(:hotel,:telefono,:usuario,:whatsapp)";
			
			try {
				$stm = $this->con->prepare($query);
				$stm->execute(array(':hotel'=>$this->getNombreHotel(),
								':telefono' => $this->getTelefono(),
								':usuario' => $this->user['id_usuario'],
								':whatsapp' => $this->whatsapp));
				$this->con->commit();
				} catch (PDOException $e) {
					$this->con->rollBack();
					return false;}
			

			return true;
		}
	}


	public function quitar(){

			$this->con->beginTransaction();
			$query = "delete from huesped where id = :huesped";
			try {
				$stm = $this->con->prepare($query);
				$stm->execute(array(':huesped'=> $this->id));
				$this->con->commit();
				return true;
			} catch (PDOException $e) {
				$this->con->rollBack();
				return false;
			}

	}
	private function cargarDatos(){


		$query = "(select hu.telefono_movil as telefono, hu.whatsapp, h.nombre as hotel, hu.id from huesped as hu 
					join huespedhotel  huh on hu.id = huh.id_huesped 
					join hotel as h on huh.id_hotel = h.id
					join usuario as u on hu.id_usuario = :usuario)
					UNION
					(select hu.telefono_movil as telefono, hu.whatsapp, hu.hotel, hu.id from huesped as hu
						join usuario as u on hu.id_usuario = u.id_usuario 
								where hu.hotel !='' and u.id_usuario = :usuario1)";

			try {
				
				$stm = $this->con->prepare($query);
				$stm->execute(array(':usuario' => $this->user['id_usuario'],
									':usuario1' => $this->user['id_usuario']));


			} catch (PDOException $e) {
				
			}
					$fila =	$stm->fetch(PDO::FETCH_ASSOC);
			if($fila){
					$this->setNombreHotel($fila['hotel']);
					$this->setTelefonomovil($fila['telefono']);
					$this->whatsapp = $fila['whatsapp'];
					$this->setId($fila['id']);

			}
			
		
	
	}


	private function setId(int $id){

		$this->id = $id;
	}
	private function setNombreHotel(string $hotel){

		$this->hotel = $hotel;
	}

	private function setTelefonomovil(string $telefono){
			$this->telefono = $telefono;
	}

	private function setWhatsapp($whatsapp){

		if($whatsapp === "on"){
			$this->whatsapp = 1;
		}
	
	}

	public function getId(){

	}

	public function getNombreHotel(){
		return $this->hotel;
	}

	public function getTelefono(){
		return $this->telefono;
	}

	public function getWhasapp(){

			$result = null;
			($this->whatsapp) ? $result = 'checked' : $result = '';
			return $result;
	
	}


	public function ListarHoteles(){

		$query = "select h.id as id_hotel , h.nombre, h.direccion, CONCAT(c.ciudad,' ',e.estado,' ',p.pais) as ubicacion from hotel as h 
			inner join ciudad as c on h.id_ciudad = c.id_ciudad 
			inner join estado as e on c.id_estado = e.id_estado
			inner join pais as p on e.id_pais = p.id_pais ";
		try {
			$stm = $this->con->prepare($query);
				$stm->execute();
		} catch (PDOException $e) {
				echo $e;
		}
		


		while ($fila = $stm->fetch(PDO::FETCH_ASSOC)) {?>

			<tr data-hotel="<?php echo $fila['id_hotel'];?>" style="cursor:pointer;" class="btntr"  data-nombre="<?php echo $fila['nombre']; ?>" data-hotel="<?php echo $fila['id_hotel'];?>">
				<td><?php echo $fila['nombre'] ?></td>
				<td><?php echo $fila['direccion'] ?></td>
				<td><?php echo $fila['ubicacion'] ?></td>
			
					<script>
						
						$('.btntr').click(function(){


							var nombre = $(this).attr('data-nombre');
							var idhotel = $(this).attr('data-hotel')
							
							$('.hotel').attr('value', idhotel);
						

							$('#nombre_hotel').val(nombre);
							$('.modal').modal('hide');
						});


					</script>

				
			</tr>
		<?php  }

	}


}
 ?>