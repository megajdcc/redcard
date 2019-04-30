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
	function __construct(connection $conec){
		$this->conection = $conec->con;

		$this->cargarData();
	}


	private function cargarData(){

		$query = " select i.id, i.codigo, i.aeropuerto, c.ciudad,e.estado, p.pais from iata as i 
				join ciudad as c on i.id_ciudad = c.id_ciudad
 				join estado as e on c.id_estado = e.id_estado 
				join pais as p on e.id_pais = p.id_pais";

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
				
				?>
				<tr id="<?php echo $value['id']?>">
					<td><?php echo $key; ?></td>
					<td><?php echo $value['codigo'];?></td>
					<td><?php echo $value['aeropuerto']; ?></td>
					<td><?php echo $value['ciudad']; ?></td>
					<td><?php echo $value['estado']; ?></td>
					<td><?php echo $value['pais']; ?></td>
					<td><?php echo $hoteles; ?></td>
				</tr>
			<?php }
	}


	public function getNotificacion(){

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
			if($this->register['country_id'] == $row['id_pais']){
				$countries .= '<option value="'.$row['id_pais'].'" selected>'.$country.'</option>';
			}else{
				$countries .= '<option value="'.$row['id_pais'].'">'.$country.'</option>';
			}
		}
		return $countries;
	}

	public function get_states(){
		$states = null;
		if($this->register['country_id']){
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
				if($this->register['state_id'] == $row['id_estado']){
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
		if($this->register['state_id']){
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
				if($this->register['city_id'] == $row['id_ciudad']){
					$cities.= '<option value="'.$row['id_ciudad'].'" selected>'.$city.'</option>';
				}else{
					$cities.= '<option value="'.$row['id_ciudad'].'">'.$city.'</option>';
				}
			}
		}
		return $cities;
	}


	public function registrar(array $post){


		$query = "insert into iata(codigo,aeropuerto,id_ciudad) value(:codigo,:aeropuerto,:ciudad)";

		try {

			$this->conection->beginTransaction();
			$stm = $this->conection->prepare($query);

			$stm->execute(array(':codigo'=>$post['codigo'],
			                    ':aeropuerto'=>$post['aeropuerto'],
			                    ':ciudad'=>$post['ciudad']));

			$this->conection->commit();

		} catch (PDOException $e) {

			$this->conection->rollBack();
			
		}
		header('location: '.HOST.'/admin/perfiles/iata');
		die();
	}

}


 ?>