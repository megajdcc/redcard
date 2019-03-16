<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace socio\libs;
use assets\libs\connection;
use PDO;

class user_password {
	private $con;
	private $user = array(
		'id' => null,
		'username' => null,
		'email' => null,
		'alias' => null
	);
	private $error = array(
		'password' => null,
		'warning' => null,
		'error' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->user['id'] = $_SESSION['user']['id_usuario'];
		$this->load_data();
		return;
	}

	private function load_data(){
		$query = "SELECT username, email, nombre, apellido FROM usuario WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->user['email'] = $row['email'];
			$this->user['username'] = $row['username'];
			if(!empty($row['nombre']) || !empty($row['apellido'])){
				$this->user['alias'] = $row['nombre'].' '.$row['apellido'];
			}else{
				$this->user['alias'] = $row['username'];
			}
		}
		return;
	}

	public function change_password(array $post){
		if(empty($post['password']) || empty($post['new_password']) || empty($post['password_confirm'])){
			$this->error['password'] = 'Todos los campos son obligatorios.';
			return false;
		}
		if($post['password'] == $post['new_password']){
			return false;
		}
		if($post['new_password'] == $this->user['username'] || $post['new_password'] == $this->user['email']){
			$this->error['password'] = 'Tu nueva contraseña no puede ser igual a tu nombre de usuario o tu correo electrónico.';
			return false;
		}
		if($post['new_password'] != $post['password_confirm']){
			$this->error['password'] = 'La confirmación de contraseña no coincide.';
			return false;
		}
		if(strlen($post['new_password']) < 6){
			$this->error['password'] = 'Tu nueva contraseña debe tener al menos 6 caracteres.';
			return false;
		}
		$query = "SELECT password FROM usuario WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			if(!password_verify($post['password'], $row['password'])){
				$this->error['password'] = 'La contraseña actual es incorrecta.';
				return false;
			}
			$options = ['cost' => 12];
			$hashed_password = password_hash($post['new_password'], PASSWORD_BCRYPT, $options);
			$query = "UPDATE usuario SET password = :password WHERE id_usuario = :id_usuario";
			$params = array(':password' => $hashed_password,':id_usuario' => $this->user['id']);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$_SESSION['notification']['success'] = 'La contraseña ha sido actualizada correctamente. Por favor inicia sesión con tu nueva contraseña.';
			$_SESSION['register_email'] = $this->user['email'];
			unset($_SESSION['user']);
			unset($_SESSION['business']);
			header('Location: '.HOST.'/login');
			die();
			return;
		}
		$this->error['warning'] = 'Uno o más campos tienen errores. Verifícalos cuidadosamente.';
		return false;
	}

	public function get_alias(){
		return _safe($this->user['alias']);
	}

	public function get_password_error(){
		if($this->error['password']){
			$error = '<p class="text-danger">'._safe($this->error['password']).'</p>';
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
		file_put_contents(ROOT.'\assets\error_logs\user_password.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>