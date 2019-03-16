<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace negocio\libs;
use assets\libs\connection;
use PDO;

class preference_networks {
	private $con;
	private $business = array('id'> null, 'url' => null, 'networks' => array());
	private $error = array('notification' => null);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->business['id'] = $_SESSION['business']['id_negocio'];
		$this->business['url'] = $_SESSION['business']['url'];
		$this->load_data();
		return;
	}

	private function load_data(){
		$query = "SELECT p.id_preferencia, p.preferencia as red_social, np.preferencia 
			FROM preferencia p 
			LEFT JOIN negocio_preferencia np ON p.id_preferencia = np.id_preferencia AND np.id_negocio = :id_negocio 
			WHERE p.llave = 'network'";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->business['networks'][$row['id_preferencia']] = array('network' => $row['red_social'], 'preference' => $row['preferencia']);
		}
		return;
	}

	public function set_networks(array $post){
		$networks = $post['network'];
		foreach ($this->business['networks'] as $key => $value){
			$network = $this->set_network_url($networks[$key]);
			if($network === false){
				$_SESSION['notification']['error'] = 'Algunos enlaces tuvieron problemas. Revísalos cuidadosamente.';
				continue;
			}
			if(!empty($network)){
				if(is_null($value['preference'])){
					$query = "INSERT INTO negocio_preferencia (id_negocio, id_preferencia, preferencia) VALUES (:id_negocio, :id_preferencia, :preferencia)";
					$params = array(
						':id_negocio' => $this->business['id'],
						':id_preferencia' => $key,
						':preferencia' => $network
					);
					try{
						$stmt = $this->con->prepare($query);
						$stmt->execute($params);
					}catch(\PDOException $ex){
						$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
						return false;
					}
				}elseif($networks[$key] != $value['preference']){
					$query = "UPDATE negocio_preferencia SET preferencia = :preferencia WHERE id_negocio = :id_negocio AND id_preferencia = :id_preferencia";
					$params = array(
						':preferencia' => $network,
						':id_negocio' => $this->business['id'],
						':id_preferencia' => $key
					);
					try{
						$stmt = $this->con->prepare($query);
						$stmt->execute($params);
					}catch(\PDOException $ex){
						$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
						return false;
					}
				}
			}elseif(!is_null($value['preference'])){
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
		}
		$_SESSION['notification']['success'] = 'Redes Sociales actualizadas correctamente.';
		header('Location: '.HOST.'/negocio/preferencias/redes-sociales');
		die();
		return;
	}

	private function set_network_url($string = null){
		if(!empty($string)){
			if(!preg_match('_^(?:(?:https?|ftp)://)?(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,}))\.?)(?::\d{2,5})?(?:[/?#]\S*)?$_iuS',$string)){
				return false;
			}
			if(!preg_match("@^https?://@", $string)){
				$string = 'http://'.$string;
			}
		}
		return $string;
	}

	public function get_url(){
		return HOST.'/'._safe($this->business['url']);
	}

	public function get_networks(){
		$right_col = $left_col = null;
		$x = 1;
		foreach ($this->business['networks'] as $key => $value) {
			$network = _safe($value['network']);
			if($value['preference']){
				$preference = _safe($value['preference']);
			}else{
				$preference = null;
			}
			if($x % 2 == 0){
				$right_col .= 
				'<div class="form-group">
					<label for="'.$network.'">'.$network.'</label>
					<input class="form-control" type="text" id="'.$network.'" name="network['.$key.']" value="'.$preference.'" placeholder="'.$network.'" />
				</div>';
			}else{
				$left_col .= 
				'<div class="form-group">
					<label for="'.$network.'">'.$network.'</label>
					<input class="form-control" type="text" id="'.$network.'" name="network['.$key.']" value="'.$preference.'" placeholder="'.$network.'" />
				</div>';
			}
			$x++;
		}
		$html = 
		'<div class="row">
			<div class="col-sm-6">
				'.$left_col.'
			</div>
			<div class="col-sm-6">
				'.$right_col.'
			</div>
		</div>';
		return $html;
	}

	public function get_notification(){
		$success = $error = null;
		if(isset($_SESSION['notification']['success'])){
			$success = 
			'<div class="alert alert-icon alert-dismissible alert-success" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notification']['success']).'
			</div>';
			unset($_SESSION['notification']['success']);
		}
		if(isset($_SESSION['notification']['error'])){
			$error = 
			'<div class="alert alert-icon alert-dismissible alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notification']['error']).'
			</div>';
			unset($_SESSION['notification']['error']);
		}
		return $success.$error;
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\preference_networks.txt', '['.date('d/M/Y h:i:s A').' | '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['notification'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>