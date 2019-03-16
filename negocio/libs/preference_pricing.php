<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace negocio\libs;
use assets\libs\connection;
use PDO;

class preference_pricing {
	private $con;
	private $business = array(
		'id'> null, 
		'url' => null, 
		'iso' => array('id' => null, 'preference' => null), 
		'min_price' => array('id' => null, 'preference' => null), 
		'max_price' => array('id' => null, 'preference' => null)
	);
	private $error = array('notification' => null, 'min_price' => null, 'max_price' => null);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->business['id'] = $_SESSION['business']['id_negocio'];
		$this->business['url'] = $_SESSION['business']['url'];
		$this->load_data();
		return;
	}

	private function load_data(){
		$query = "SELECT p.id_preferencia, np.preferencia
			FROM preferencia p 
			LEFT JOIN negocio_preferencia np ON p.id_preferencia = np.id_preferencia AND np.id_negocio = ?
			WHERE p.llave = 'default_currency'";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(1, $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['iso'] = array('id' => $row['id_preferencia'], 'preference' => $row['preferencia']);
		}
		$query = "SELECT p.id_preferencia, np.preferencia
			FROM preferencia p 
			LEFT JOIN negocio_preferencia np ON p.id_preferencia = np.id_preferencia AND np.id_negocio = ?
			WHERE p.llave = 'business_min_price'";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(1, $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['min_price'] = array('id' => $row['id_preferencia'], 'preference' => $row['preferencia']);
		}
		$query = "SELECT p.id_preferencia, np.preferencia
			FROM preferencia p 
			LEFT JOIN negocio_preferencia np ON p.id_preferencia = np.id_preferencia AND np.id_negocio = ?
			WHERE p.llave = 'business_max_price'";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(1, $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['max_price'] = array('id' => $row['id_preferencia'], 'preference' => $row['preferencia']);
		}
		return;
	}

	public function set_price_range(array $post){
		$min_price = filter_var($post['min'], FILTER_VALIDATE_FLOAT);
		$max_price = filter_var($post['max'], FILTER_VALIDATE_FLOAT);
		if(strlen($post['iso']) != 3){
			$iso = null;
		}else{
			$iso = strtoupper($post['iso']);
		}
		if($min_price){
			$min_price = round($min_price, 2);
			if(is_null($this->business['min_price']['preference'])){
				$query = "INSERT INTO negocio_preferencia (id_negocio, id_preferencia, preferencia) VALUES (:id_negocio, :id_preferencia, :preferencia)";
				$params = array(
					':id_negocio' => $this->business['id'],
					':id_preferencia' => $this->business['min_price']['id'],
					':preferencia' => $min_price
				);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
			}elseif($min_price != $this->business['min_price']['preference']){
				$query = "UPDATE negocio_preferencia SET preferencia = :preferencia WHERE id_negocio = :id_negocio AND id_preferencia = :id_preferencia";
				$params = array(
					':preferencia' => $min_price,
					':id_negocio' => $this->business['id'],
					':id_preferencia' => $this->business['min_price']['id']
				);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
			}
		}else{
			$query = "DELETE FROM negocio_preferencia WHERE id_negocio = :id_negocio AND id_preferencia = :id_preferencia";
			$params = array(':id_negocio' => $this->business['id'], ':id_preferencia' => $this->business['min_price']['id']);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
		}
		if($max_price){
			$max_price = round($max_price, 2);
			if(is_null($this->business['max_price']['preference'])){
				$query = "INSERT INTO negocio_preferencia (id_negocio, id_preferencia, preferencia) VALUES (:id_negocio, :id_preferencia, :preferencia)";
				$params = array(
					':id_negocio' => $this->business['id'],
					':id_preferencia' => $this->business['max_price']['id'],
					':preferencia' => $max_price
				);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
			}elseif($max_price != $this->business['max_price']['preference']){
				$query = "UPDATE negocio_preferencia SET preferencia = :preferencia WHERE id_negocio = :id_negocio AND id_preferencia = :id_preferencia";
				$params = array(
					':preferencia' => $max_price,
					':id_negocio' => $this->business['id'],
					':id_preferencia' => $this->business['max_price']['id']
				);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
			}
		}else{
			$query = "DELETE FROM negocio_preferencia WHERE id_negocio = :id_negocio AND id_preferencia = :id_preferencia";
			$params = array(':id_negocio' => $this->business['id'], ':id_preferencia' => $this->business['max_price']['id']);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
		}
		if($iso){
			if(is_null($this->business['iso']['preference'])){
				$query = "INSERT INTO negocio_preferencia (id_negocio, id_preferencia, preferencia) VALUES (:id_negocio, :id_preferencia, :preferencia)";
				$params = array(
					':id_negocio' => $this->business['id'],
					':id_preferencia' => $this->business['iso']['id'],
					':preferencia' => $iso
				);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
			}elseif($iso != $this->business['iso']['preference']){
				$query = "UPDATE negocio_preferencia SET preferencia = :preferencia WHERE id_negocio = :id_negocio AND id_preferencia = :id_preferencia";
				$params = array(
					':preferencia' => $iso,
					':id_negocio' => $this->business['id'],
					':id_preferencia' => $this->business['iso']['id']
				);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
			}
		}else{
			$query = "DELETE FROM negocio_preferencia WHERE id_negocio = :id_negocio AND id_preferencia = :id_preferencia";
			$params = array(':id_negocio' => $this->business['id'], ':id_preferencia' => $this->business['iso']['id']);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
		}
		$_SESSION['notification']['success'] = 'Datos actualizados correctamente';
		header('Location: '.HOST.'/negocio/preferencias/divisa-y-precios');
		die();
		return;
	}

	public function get_min_price(){
		return _safe($this->business['min_price']['preference']);
	}

	public function get_max_price(){
		return _safe($this->business['max_price']['preference']);
	}

	public function get_currencies(){
		$currencies = null;
		$query = "SELECT iso FROM divisa";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			if($this->business['iso']['preference'] == $row['iso']){
				$currencies .= '<option value="'.$row['iso'].'" selected>'.$row['iso'].'</option>';
			}else{
				$currencies .= '<option value="'.$row['iso'].'">'.$row['iso'].'</option>';
			}
		}
		return $currencies;
	}

	public function get_url(){
		return HOST.'/'._safe($this->business['url']);
	}

	public function get_notification(){
		if(isset($_SESSION['notification']['success'])){
			$notification = 
			'<div class="alert alert-icon alert-dismissible alert-success mb50" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notification']['success']).'
			</div>';
			unset($_SESSION['notification']['success']);
			return $notification;
		}
		if($this->error['notification']){
			$error = 
			'<div class="alert alert-icon alert-dismissible alert-danger mb50" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->error['notification']).'
			</div>';
			return $error;
		}
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\preference_pricing.txt', '['.date('d/M/Y h:i:s A').' | '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['notification'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>