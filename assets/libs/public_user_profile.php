<?php 
namespace assets\libs;
use assets\libs\connection;
use PDO;

class public_user_profile {
	private $con;
	private $user = array(
		'id' => null,
		'username' => null,
		'email' => null,
		'image' => null,
		'name' => null,
		'last_name' => null,
		'city_id' => null,
		'city' => null,
		'state' => null,
		'country' => null
	);
	private $error = array(
		'warning' => null,
		'error' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		return;
	}

	public function load_data($username = null){
		if(empty($username)){
			return false;
		}
		$query = "SELECT u.id_usuario, u.username, u.email, u.imagen, u.nombre, u.apellido, u.id_ciudad 
			FROM usuario u 
			WHERE username = :username";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':username', $username, PDO::PARAM_STR);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->user['id'] = $row['id_usuario'];
			$this->user['username'] = $row['username'];
			$this->user['email'] = $row['email'];
			if(!empty($row['imagen'])){
				$this->user['image'] = $row['imagen'];
			}else{
				$this->user['image'] = 'default.jpg';
			}
			$this->user['name'] = $row['nombre'];
			$this->user['last_name'] = $row['apellido'];
			$this->user['city_id'] = $row['id_ciudad'];
			if(!empty($row['nombre']) || !empty($row['apellido'])){
				$this->user['alias'] = _safe($row['nombre'].' '.$row['apellido']);
			}else{
				$this->user['alias'] = _safe($row['username']);
			}
			if($this->user['city_id']){
				$query = "SELECT c.ciudad, e.estado, p.pais 
					FROM ciudad c
					INNER JOIN estado e ON c.id_estado = e.id_estado 
					INNER JOIN pais p ON e.id_pais = p.id_pais
					WHERE c.id_ciudad = :id_ciudad";
				try{
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(':id_ciudad', $this->user['city_id'], PDO::PARAM_INT);
					$stmt->execute();
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				if($row = $stmt->fetch()){
					$this->user['city'] = $row['ciudad'];
					$this->user['state'] = $row['estado'];
					$this->user['country'] = $row['pais'];
				}
			}
			return true;
		}
		return false;
	}

	public function get_image(){
		$html = 
			'<img src="'.HOST.'/assets/img/user_profile/'.$this->user['image'].'" alt="Foto de perfil de '.$this->user['alias'].'">';
		return $html;
	}

	public function get_alias(){
		return _safe($this->user['alias']);
	}

	public function get_username(){
		return _safe($this->user['username']);
	}

	public function get_email(){
		return _safe($this->user['email']);
	}

	public function get_location(){
		if($this->user['city'] && $this->user['country']){
			return _safe($this->user['city'].', '.$this->user['country']);
		}
	}

	public function get_notification(){
		$html = null;
		if(isset($_SESSION['notification']['success'])){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-success" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notification']['success']).'
			</div>';
			unset($_SESSION['notification']['success']);
		}
		if(isset($_SESSION['notification']['info'])){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-info" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notification']['info']).'
			</div>';
			unset($_SESSION['notification']['info']);
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

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\public_user_profile.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>