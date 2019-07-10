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

	private $error = array(
		'error'=>null,
	);

	private $idhotel = null;


	function __construct($con = null){		

		$this->con = $con->con;	
		$this->user['id_usuario'] = $_SESSION['user']['id_usuario'];
		$this->cargarDatos();

	}


	public function procesar(array $post){


		$this->idhotel = $post['hotel'];


			// es un hotel registrado en el sistema ... 
			 if($this->con->inTransaction()){$this->con->rollBack();};

			 $this->con->beginTransaction();
			$query = "insert into huesped(id_usuario) value(:usuario)";


			try {
				$stm = $this->con->prepare($query);

				$stm->execute(array(':usuario' => $this->user['id_usuario']));

			} catch (\PDOException $e) {
				$this->error_log(__METHOD__,__LINE__,$e->getMessage());
				$this->con->rollBack();

				return false;
			}
			
			$last_id = $this->con->lastInsertId();


			$query1 = "insert into huespedhotel(id_hotel,id_huesped) values(:hotel,:huesped)";

			try {
				$stm2 = $this->con->prepare($query1);
				$stm2->execute(array(':hotel' =>$this->idhotel,
								':huesped'=>$last_id));

				$this->con->commit();
			} catch (\PDOException $ex) {
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				$this->con->rollBack();
				return false;
			}

			return true;
			
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

	private function setTelefonomovil(string $telefono = null){
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

	public function getHoteles(){

		$sql = "SELECT h.id, h.nombre as hotel from hotel as h order by h.nombre";

		$stm = $this->con->prepare($sql);
		$stm->execute();

			$opciones = '';
			if(!empty($this->getNombreHotel())){
				$opciones .='<option value="" selected>'.$this->getNombreHotel().'<opction>';
			}


		while($row = $stm->fetch(PDO::FETCH_ASSOC)){
			$opciones .= '<option value="'.$row['id'].'">'.$row['hotel'].'<opction>';
			echo $opciones;
		}

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



	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\hotelhuesped.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}


}
 ?>