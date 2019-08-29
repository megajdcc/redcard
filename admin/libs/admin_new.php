<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace admin\libs;
use assets\libs\connection;
use PDO;

class admin_new {
	private $con;
	private $admin = array(
		'roles' => null
		);
	private $new_admin = array(
		'id' => null, 
		'username' => null, 
		'role' => null, 
		'security_code' => null
	);
	private $error = array(
		'username' => null,
		'role' => null,
		'security_code' => null,
		'warning' => null,
		'error' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->load_data();
		return;
	}

	private function load_data(){
		$query = "SELECT id_rol, rol FROM roles WHERE llave = 'admin'";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->admin['roles'][$row['id_rol']] = $row['rol'];
		}
		return;
	}

	public function new_admin(array $post){
		$this->set_username($post['username']);
		$this->set_role($post['role']);
		// $this->check_security_code($post['security_code']);
		if(!array_filter($this->error)){
			$query = "UPDATE usuario SET id_rol = :id_rol WHERE id_usuario = :id_usuario";
			$params = array(
				':id_rol' => $this->new_admin['role'],
				':id_usuario' => $this->new_admin['id']
			);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$query = "SELECT id_codigo FROM codigo_administrador WHERE id_usuario = :id_usuario";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_usuario', $this->new_admin['id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				$query = "UPDATE codigo_administrador SET 
					codigo_seguridad = :codigo_seguridad,
					situacion = 1
					WHERE id_usuario = :id_usuario";
				$params = array(
					':codigo_seguridad' => $this->new_admin['security_code'],
					':id_usuario' => $this->new_admin['id']
				);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
			}else{
				$query = "INSERT INTO codigo_administrador (
					id_usuario, 
					codigo_seguridad
					) VALUES (
					:id_usuario, 
					:codigo_seguridad
				)";
				$params = array(
					':id_usuario' => $this->new_admin['id'],
					':codigo_seguridad' => $this->new_admin['security_code']
				);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
			}
			$_SESSION['notification']['success'] = 'Nuevo administrador registrado exitosamente.';
			header('Location: '.HOST.'/admin/usuarios/nuevo-administrador');
			die();
			return;
		}
		$this->error['warning'] = 'Uno o más campos tienen errores. Verifícalos cuidadosamente.';
		return false;
	}

	private function set_username($username = null){
		if($username){
			$this->new_admin['username'] = trim($username);
			if(!preg_match('/^[a-zA-Z0-9]+$/ui',$this->new_admin['username'])){
				$this->error['username'] = 'El nombre de usuario solo debe contener letras y números. No se permite acentos.';
				return false;
			}
			$query = "SELECT u.id_usuario, u.password, u.id_rol FROM usuario u WHERE username = :username";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':username', $this->new_admin['username'], PDO::PARAM_STR);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				if($row['id_rol'] != 1 && $row['id_rol'] != 2){
					$this->new_admin['id'] = $row['id_usuario'];
					$this->new_admin['security_code'] = $row['password'];
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
		if(array_key_exists($role, $this->admin['roles'])){
			$this->new_admin['role'] = $role;
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
		return _safe($this->new_admin['username']);
	}

	public function get_username_error(){
		if($this->error['username']){
			$error = '<p class="text-danger">'._safe($this->error['username']).'</p>';
			return $error;
		}
	}

	public function get_roles(){
		$html = null;
		foreach ($this->admin['roles'] as $key => $value) {
			if($this->new_admin['role'] == $key){
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
		file_put_contents(ROOT.'\assets\error_logs\admin_new.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>