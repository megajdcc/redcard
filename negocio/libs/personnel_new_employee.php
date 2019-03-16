<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace negocio\libs;
use assets\libs\connection;
use PDO;

class personnel_new_employee {
	private $con;
	private $user = array('id' => null);
	private $business = array(
		'id' => null,
		'url' => null,
		'roles' => null
		);
	private $new_employee = array('id' => null, 'username' => null, 'role' => null, 'security_code' => null);
	private $error = array('username' => null, 'role' => null, 'security_code' => null, 'notification' => null);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->business['id'] = $_SESSION['business']['id_negocio'];
		$this->business['url'] = $_SESSION['business']['url'];
		// $this->user['id'] = $_SESSION['user']['id_usuario'];
		$this->load_data();
		return;
	}

	private function load_data(){
		$query = "SELECT id_rol, rol FROM roles WHERE llave = 'business'";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->business['roles'][$row['id_rol']] = $row['rol'];
		}
		return;
	}

	public function new_employee(array $post){
		$this->set_username($post['username']);
		$this->set_role($post['role']);
		// $this->check_security_code($post['security_code']);
		if(!array_filter($this->error)){
			$query = "INSERT INTO negocio_empleado (
				id_negocio, 
				id_empleado, 
				id_rol, 
				codigo_seguridad
				) VALUES (
				:id_negocio, 
				:id_empleado, 
				:id_rol, 
				:codigo_seguridad
			)";
			$query_params = array(
				':id_negocio' => $this->business['id'],
				':id_empleado' => $this->new_employee['id'],
				':id_rol' => $this->new_employee['role'],
				':codigo_seguridad' => $this->new_employee['security_code']
			);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($query_params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$query = "SELECT p.id_preferencia, (SELECT COUNT(*) FROM usuario_preferencia up WHERE up.id_preferencia = p.id_preferencia AND up.id_usuario = :id_usuario) as tiene FROM preferencia p WHERE p.llave = 'default_business'";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue('id_usuario', $this->new_employee['id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				if($row['tiene'] == 0){
					$query = "INSERT INTO usuario_preferencia (id_usuario, id_preferencia, preferencia) VALUES (:id_usuario, 1, :preferencia)";
					$query_params = array(':id_usuario' => $this->new_employee['id'], ':preferencia' =>  $this->business['id']);
					try{
						$stmt = $this->con->prepare($query);
						$stmt->execute($query_params);
					}catch(\PDOException $ex){
						$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
						return false;
					}
				}
			}
			$_SESSION['notification']['success'] = 'Nuevo empleado registrado exitosamente.';
			header('Location: '.HOST.'/negocio/personal/');
			die();
			return;
		}
		return false;
	}

	private function set_username($username = null){
		if($username){
			$this->new_employee ['username'] = trim($username);
			if(!preg_match('/^[a-zA-Z0-9]+$/ui',$this->new_employee['username'])){
				$this->error['username'] = 'El nombre de usuario solo debe contener letras y números. No se permite acentos.';
				return false;
			}
			$query = "SELECT u.id_usuario, u.password, ne.id_empleado FROM usuario u 
				LEFT JOIN negocio_empleado ne ON u.id_usuario = ne.id_empleado AND ne.id_negocio = :id_negocio
				WHERE username = :username";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_STR);
				$stmt->bindValue(':username', $this->new_employee['username'], PDO::PARAM_STR);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				if(is_null($row['id_empleado'])){
					$this->new_employee['id'] = $row['id_usuario'];
					$this->new_employee['security_code'] = $row['password'];
					return true;
				}
				$this->error['username'] = 'Esta persona ya está registrada.';
				return false;
			}
			$this->error['username'] = 'Esta persona no existe.';
			return false;
		}
		$this->error['username'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_role($role = null){
		if(array_key_exists($role, $this->business['roles'])){
			$this->new_employee['role'] = $role;
			return;
		}
		$this->error['role'] = 'Selecciona un rol.';
		return false;
	}

	private function check_security_code($security_code = null){
		if($security_code){
			$query = "SELECT codigo_seguridad FROM negocio_empleado WHERE id_negocio = :id_negocio AND id_empleado = :id_empleado";
			$query_params = array(':id_negocio' => $this->business['id'], ':id_empleado' => $this->user['id']);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($query_params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				if(password_verify($security_code,$row['codigo_seguridad'])){
					return true;
				}
				$this->error['security_code'] = 'El código de seguridad no coincide.';
				return false;
			}
			$this->error['security_code'] = 'El código de seguridad no ha sido definido.';
			return false;
		}
		$this->error['security_code'] = 'Este campo es obligatorio.';
		return false;
	}

	public function get_username(){
		return _safe($this->new_employee['username']);
	}

	public function get_username_error(){
		if($this->error['username']){
			$error = '<p class="text-danger">'._safe($this->error['username']).'</p>';
			return $error;
		}
	}

	public function get_roles(){
		$html = null;
		foreach ($this->business['roles'] as $key => $value) {
			if($this->new_employee['role'] == $key){
				$html .= '<option value="'.$key.'" selected>'.$value.'</option>';
			}else{
				$html .= '<option value="'.$key.'">'.$value.'</option>';
			}
		}
		return $html;
	}

	public function get_role_error(){
		if($this->error['role']){
			$error = '<p class="text-danger">'._safe($this->error['role']).'</p>';
			return $error;
		}
	}

	public function get_security_code_error(){
		if($this->error['security_code']){
			$error = '<p class="text-danger">'._safe($this->error['security_code']).'</p>';
			return $error;
		}
	}

	public function get_notification(){
		if(isset($_SESSION['notification']['success'])){
			$notification = 
			'<div class="alert alert-icon alert-dismissible alert-success" role="alert">
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
			'<div class="alert alert-icon alert-dismissible alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->error['notification']).'
			</div>';
			return $error;
		}
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\personnel_new_employee.txt', '['.date('d/M/Y h:i:s A').' | '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['notification'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>