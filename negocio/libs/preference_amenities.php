<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace negocio\libs;
use assets\libs\connection;
use PDO;

class preference_amenities {
	private $con;
	private $business = array('id'> null, 'url' => null, 'amenities' => array(), 'payments' => array());
	private $error = array('notification' => null);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->business['id'] = $_SESSION['business']['id_negocio'];
		$this->business['url'] = $_SESSION['business']['url'];
		$this->load_data();
		return;
	}

	private function load_data(){
		$query = "SELECT p.id_preferencia, p.preferencia as amenidad, np.preferencia 
			FROM preferencia p 
			LEFT JOIN negocio_preferencia np ON p.id_preferencia = np.id_preferencia AND np.id_negocio = :id_negocio 
			WHERE p.llave = 'amenity'";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->business['amenities'][$row['id_preferencia']] = array('amenity' => $row['amenidad'], 'preference' => $row['preferencia']);
		}
		$query = "SELECT p.id_preferencia, p.preferencia as pago, np.preferencia 
			FROM preferencia p 
			LEFT JOIN negocio_preferencia np ON p.id_preferencia = np.id_preferencia AND np.id_negocio = :id_negocio 
			WHERE p.llave = 'payment'";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->business['payments'][$row['id_preferencia']] = array('payment' => $row['pago'], 'preference' => $row['preferencia']);
		}
		return;
	}

	public function set_amenities_payments(array $post){
		$current_amenities = $this->business['amenities'];
		$current_payments = $this->business['payments'];
		$amenities = $post['amenity'];
		$payments = $post['payment'];
		if(isset($post['amenity']) && !empty($post['amenity'])){
			foreach ($amenities as $key => $value){
				if($current_amenities[$key]['preference'] == NULL){
					$query = "INSERT INTO negocio_preferencia (id_negocio, id_preferencia, preferencia) 
						VALUES (:id_negocio, :id_preferencia, :preferencia)";
					$params = array(':id_negocio' => $this->business['id'], ':id_preferencia' => $key, ':preferencia' => 'on');
					try{
						$stmt = $this->con->prepare($query);
						$stmt->execute($params);
					}catch(\PDOException $ex){
						$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
						return false;
					}
				}
				unset($current_amenities[$key]);
			}
		}
		if(isset($post['payment']) && !empty($post['payment'])){
			foreach ($payments as $key => $value){
				if($current_payments[$key]['preference'] == NULL){
					$query = "INSERT INTO negocio_preferencia (id_negocio, id_preferencia, preferencia) 
						VALUES (:id_negocio, :id_preferencia, :preferencia)";
					$params = array(':id_negocio' => $this->business['id'], ':id_preferencia' => $key, ':preferencia' => 'on');
					try{
						$stmt = $this->con->prepare($query);
						$stmt->execute($params);
					}catch(\PDOException $ex){
						$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
						return false;
					}
				}
				unset($current_payments[$key]);
			}
		}
		foreach ($current_amenities as $key => $value){
			$query = "DELETE FROM negocio_preferencia WHERE id_negocio = :id_negocio AND id_preferencia = :id_preferencia";
			$params = array(':id_negocio' => $this->business['id'], ':id_preferencia' => $key);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
		}
		foreach ($current_payments as $key => $value){
			$query = "DELETE FROM negocio_preferencia WHERE id_negocio = :id_negocio AND id_preferencia = :id_preferencia";
			$params = array(':id_negocio' => $this->business['id'], ':id_preferencia' => $key);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
		}
		$_SESSION['notification']['success'] = 'Amenidades y Formas de Pago actualizados correctamente.';
		header('Location: '.HOST.'/negocio/preferencias/amenidades-y-pagos');
		die();
		return;
	}

	public function get_url(){
		return HOST.'/'._safe($this->business['url']);
	}

	public function get_amenities(){
		$html = null;
		foreach ($this->business['amenities'] as $key => $value) {
			$amenity = _safe($value['amenity']);
			if($value['preference']){
				$html .= 
			'<div class="checkbox"><input id="amenity-'.$key.'" type="checkbox" name="amenity['.$key.']" checked><label for="amenity-'.$key.'">'.$amenity.'</label></div>';
			}else{
				$html.= 
			'<div class="checkbox"><input id="amenity-'.$key.'" type="checkbox" name="amenity['.$key.']"><label for="amenity-'.$key.'">'.$amenity.'</label></div>';
			}
		}
		return $html;
	}

	public function get_payment_methods(){
		$html = null;
		foreach ($this->business['payments'] as $key => $value) {
			$amenity = _safe($value['payment']);
			if($value['preference']){
				$html .= 
			'<div class="checkbox"><input id="amenity-'.$key.'" type="checkbox" name="payment['.$key.']" checked><label for="amenity-'.$key.'">'.$amenity.'</label></div>';
			}else{
				$html.= 
			'<div class="checkbox"><input id="amenity-'.$key.'" type="checkbox" name="payment['.$key.']"><label for="amenity-'.$key.'">'.$amenity.'</label></div>';
			}
		}
		return $html;
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
		file_put_contents(ROOT.'\assets\error_logs\preference_amenities.txt', '['.date('d/M/Y h:i:s A').' | '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['notification'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>