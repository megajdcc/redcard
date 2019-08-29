<?php 

namespace admin\libs;
use assets\libs\connection;

use PDO;


/**
 * @author Crespo Jhonatan
 * @since 22/04/2019
 */
class Iata
{
	
	private $conection;
	private $iata = array();
	private $error = array('error'=>null,'warning'=>null);

	private $registro = array(
		'country_id' => null,
		'state_id'   =>null,
		'city_id'    =>null
	);
	function __construct(connection $conec){
		$this->conection = $conec->con;

		$this->cargarData();
	}


	private function cargarData(){

		$query = "
(select i.id, i.codigo, i.aeropuerto, c.ciudad,e.estado, p.pais from iata as i 
				join ciudad as c on i.id_ciudad = c.id_ciudad
 				left join estado as e on c.id_estado = e.id_estado 
				left join pais as p on e.id_pais = p.id_pais)
UNION
(select i.id, i.codigo, i.aeropuerto, c.ciudad,e.estado, p.pais from iata as i 
				left join ciudad as c on i.id_ciudad = c.id_ciudad
 				join estado as e on i.id_estado = e.id_estado 
				left join pais as p on e.id_pais = p.id_pais)
";

		$stm = $this->conection->prepare($query);
		$stm->execute();
		$this->iata = $stm->fetchAll(PDO::FETCH_ASSOC);

	}


	private function capturarhoteles(int $idiata){

		$query = "select count(h.id) as hoteles from hotel as h where h.id_iata = :iata";
		$stm = $this->conection->prepare($query);
		$stm->execute(array(':iata'=>$idiata));
		return $stm->fetch(PDO::FETCH_ASSOC)['hoteles'];
	
	}
	
	public function getIata(){

			foreach ($this->iata as $key => $value) {

				$hoteles = $this->capturarhoteles($value['id']);
				
				if(empty($value['ciudad'])){
					$ciudad = 'Sin registro';
				}else{
					$ciudad = $value['ciudad'];
				}
				?>


				<tr id="<?php echo $value['id']?>">
					<td><?php echo $key; ?></td>
					<td><?php echo $value['codigo'];?></td>
					<td><?php echo $value['aeropuerto']; ?></td>
					<td><?php echo $ciudad; ?></td>
					<td><?php echo $value['estado']; ?></td>
					<td><?php echo $value['pais']; ?></td>
					<td><?php echo $hoteles; ?></td>
					<td class="btn-iata">
						<!-- <button class="editar btn btn-secondary" data-id="<?php //echo $value['id'];?>" type="button" data-toggle="tooltip" title="Modificar" data-placement="left" name="editar"><i class="fa fa-edit" onclick="javascript: modif=true; elim=false"></i></button> -->
					<button class="eliminar btn btn-danger" type="submit" value="<?php echo $value['id'];?>" data-toggle="tooltip" title="Eliminar" data-placement="left" name="eliminar" onclick="javascript: modif=false; elim=true;"><i class="fa fa-remove"></i></button>
				</td>
				</tr>
			<?php }
	}


	public function eliminar(array $post){
		$id_iata = $post['eliminar'];
		settype($id_iata,'integer');


		$this->conection->beginTransaction();

		$sql = 'delete from iata where id = :id';

		try {
			$stm  = $this->conection->prepare($sql);
			$stm->bindParam(':id',$id_iata, PDO::PARAM_INT);
			$stm->execute();
			$this->conection->commit();
			$_SESSION['notificacion']['correcto'] = "Se ha eliminado Correctamente el codigo Iata";	

		} catch (PDOException $e) {
			$this->conection->rollBack();
			$this->error['error'] = "No se pudo eliminar este codigo Iata intentelo mas tarde...";	
			return false;
		}


		header('location: '.HOST.'/admin/perfiles/iata');
		die();

	}
	public function getNotificacion(){
		$html = null;

		if(isset($_SESSION['notificacion']['correcto'])){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-success" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'.$_SESSION['notificacion']['correcto'].'
			</div>';
			unset($_SESSION['notificacion']['correcto']);
		}
		if(isset($_SESSION['notificacion']['info'])){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-info" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notificacion']['info']).'
			</div>';
			unset($_SESSION['notificacion']['info']);
		}
		if($this->error['warning']){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-warning" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->error['warning']).'
			</div>';
		}
		if($this->error['error']){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->error['error']).'
			</div>';
		}
		return $html;


	}

	public function get_countries(){
		$countries = null;
		$query = "SELECT id_pais, pais FROM pais";
		try{
			$stmt = $this->conection->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$country = _safe($row['pais']);
			if($this->registro['country_id'] == $row['id_pais']){
				$countries .= '<option value="'.$row['id_pais'].'" selected>'.$country.'</option>';
			}else{
				$countries .= '<option value="'.$row['id_pais'].'">'.$country.'</option>';
			}
		}
		return $countries;
	}

	public function get_states(){
		$states = null;
		if($this->registro['country_id']){
			$query = "SELECT id_estado, estado FROM estado WHERE id_pais = :id_pais";
			try{
				$stmt = $this->conection->prepare($query);
				$stmt->bindValue(':id_pais', $this->register['country_id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$state = _safe($row['estado']);
				if($this->registro['state_id'] == $row['id_estado']){
					$states .= '<option value="'.$row['id_estado'].'" selected>'.$state.'</option>';
				}else{
					$states .= '<option value="'.$row['id_estado'].'">'.$state.'</option>';
				}
			}
		}
		return $states;
	}

		public function get_cities(){
		$cities = null;
		if($this->registro['state_id']){
			$query = "SELECT id_ciudad, ciudad FROM ciudad WHERE id_estado = :id_estado";
			try{
				$stmt = $this->conection->prepare($query);
				$stmt->bindValue(':id_estado', $this->register['state_id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$city = _safe($row['ciudad']);
				if($this->registro['city_id'] == $row['id_ciudad']){
					$cities.= '<option value="'.$row['id_ciudad'].'" selected>'.$city.'</option>';
				}else{
					$cities.= '<option value="'.$row['id_ciudad'].'">'.$city.'</option>';
				}
			}
		}
		return $cities;
	}



	public function registrocliente(array $post){


		$sql = "select * from iata where codigo = :codigo";
		$stm= $this->conection->prepare($sql);

		$stm->bindParam(':codigo', $post['codigo'], PDO::PARAM_STR);

		$stm->execute();

		$response = array(
			"result"  => false,
			"datos"   => array( 'iataexiste' => false,
								'registroexitoso' =>false),
			"iata" => array('id'=>null,
							'codigo'=>null));


		
		if($stm->rowCount() > 0){
			
			$response['result'] = true;
			$response['datos']['iataexiste'] = true;
			echo json_encode($response);
 			
		}else{


			if(empty($post['ciudad']) || $post['ciudad'] == ''){
					$query = "insert into iata(codigo,aeropuerto,id_estado) value(:codigo,:aeropuerto,:estado)";

				try {

					$this->conection->beginTransaction();
					$stm = $this->conection->prepare($query);

					$stm->execute(array(':codigo'=>$post['codigo'],
					                    ':aeropuerto'=>$post['aeropuerto'],
					                	':estado'=>$post['estado']));

					$this->conection->commit();

				} catch (PDOException $e) {

					$this->conection->rollBack();
					
				}
			}else{
					$query = "insert into iata(codigo,aeropuerto,id_ciudad,id_estado) value(:codigo,:aeropuerto,:ciudad,:estado)";

				try {

					$this->conection->beginTransaction();
					$stm = $this->conection->prepare($query);

					$stm->execute(array(':codigo'=>$post['codigo'],
					                    ':aeropuerto'=>$post['aeropuerto'],
					                    ':ciudad'=>$post['ciudad'],
					                	':estado'=>$post['estado']));

					$this->conection->commit();

				} catch (PDOException $e) {

					$this->conection->rollBack();
					
				}
		}

		$sql = "select i.id,i.codigo from iata as i where i.id = (select max(id) from iata)";
		$stm = $this->conection->prepare($sql);
		$stm->execute();

		$fila   = $stm->fetch(PDO::FETCH_ASSOC);
		$id     = $fila['id'];
		$codigo = $fila['codigo'];

		$response['iata']['id']               = $id;
		$response['iata']['codigo']           = $codigo;
		
		$response['result']                   = true;
		$response['datos']['iataexiste']      = false;
		$response['datos']['registroexitoso'] = true;
		echo json_encode($response);
	}
}

	public function registrar(array $post){

		$sql = "select * from iata where codigo = :codigo";
		$stm= $this->conection->prepare($sql);

		$stm->bindParam(':codigo', $post['codigo'], PDO::PARAM_STR);

		$stm->execute();
		
		if($stm->rowCount() > 0){
			$_SESSION['notificacion']['info'] = "No podemos registrar este codigo IATA por que ya existe";
			
		}else{
				if(empty($post['ciudad']) || $post['ciudad'] == ''){
					$query = "insert into iata(codigo,aeropuerto,id_estado) value(:codigo,:aeropuerto,:estado)";

				try {

					$this->conection->beginTransaction();
					$stm = $this->conection->prepare($query);

					$stm->execute(array(':codigo'=>$post['codigo'],
					                    ':aeropuerto'=>$post['aeropuerto'],
					                	':estado'=>$post['estado']));

					$this->conection->commit();

				} catch (PDOException $e) {

					$this->conection->rollBack();
					
				}
			}else{
					$query = "insert into iata(codigo,aeropuerto,id_ciudad,id_estado) value(:codigo,:aeropuerto,:ciudad,:estado)";

				try {

					$this->conection->beginTransaction();
					$stm = $this->conection->prepare($query);

					$stm->execute(array(':codigo'=>$post['codigo'],
					                    ':aeropuerto'=>$post['aeropuerto'],
					                    ':ciudad'=>$post['ciudad'],
					                	':estado'=>$post['estado']));

					$this->conection->commit();

				} catch (PDOException $e) {

					$this->conection->rollBack();
					
				}
			}

			$_SESSION['notificacion']['correcto'] = "Se ha registrado exitosamente el codigo IATA";
			
			header('location: '.HOST.'/admin/perfiles/iata');
			die();
		}

		
	}

}


 ?>