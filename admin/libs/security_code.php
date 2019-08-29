<?php
namespace admin\libs;
use assets\libs\connection;
use PDO;

class security_code {
	private $con;
	private $user = array(
		'id' => null,
		'username' => null,
		'email' => null
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
		$query = "SELECT username, email FROM usuario WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->user['username'] = $row['username'];
			$this->user['email'] = $row['email'];
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
			$this->error['password'] = 'Tu nuevo código de seguridad no puede ser igual a tu nombre de usuario o tu correo electrónico.';
			return false;
		}
		if($post['new_password'] != $post['password_confirm']){
			$this->error['password'] = 'La confirmación del código de seguridad no coincide.';
			return false;
		}
		if(strlen($post['new_password']) < 6){
			$this->error['password'] = 'Tu nuevo código de seguridad debe tener al menos 6 caracteres.';
			return false;
		}
		$query = "SELECT codigo_seguridad FROM codigo_administrador WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			if(!password_verify($post['password'], $row['codigo_seguridad'])){
				$this->error['password'] = 'El código de seguridad actual es incorrecto.';
				return false;
			}
			$options = ['cost' => 12];
			$hashed_password = password_hash($post['new_password'], PASSWORD_BCRYPT, $options);
			$query = "UPDATE codigo_administrador SET codigo_seguridad = :codigo WHERE id_usuario = :id_usuario";
			$params = array(':codigo' => $hashed_password,':id_usuario' => $this->user['id']);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$_SESSION['notification']['success'] = 'El código de seguridad ha sido actualizado correctamente. Por favor vuelve a acceder con tu nuevo código de seguridad.';
			unset($_SESSION['user']['admin_authorize']);
			header('Location: '.HOST.'/admin/acceso');
			die();
			return;
		}
		$this->error['warning'] = 'Uno o más campos tienen errores. Verifícalos cuidadosamente.';
		return false;
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
		file_put_contents(ROOT.'\assets\error_logs\admin_security_code.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>