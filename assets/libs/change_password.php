<?php
namespace assets\libs;
use PDO;

class change_password {
	private $con;
	private $user = array(
		'id' => null,
		'email' => null,
		'username' => null
	);
	private $error = array(
		'password' => null,
		'warning' => null,
		'error' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		return;
	}

	public function validate_hash($email, $hash){
		$query = "SELECT id_usuario, username, email, hash_activacion, verificado FROM usuario WHERE email = :email";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':email', $email, PDO::PARAM_STR);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			if(!is_null($row['hash_activacion'])){
				if($hash == $row['hash_activacion']){
					$this->user['id'] = $row['id_usuario'];
					$this->user['email'] = $row['email'];
					$this->user['username'] = $row['username'];
					if($row['verificado'] == 0){
						$query = "UPDATE usuario SET verificado = 1 WHERE id_usuario = :id_usuario";
						try{
							$stmt = $this->con->prepare($query);
							$stmt->bindValue(':id_usuario', $row['id_usuario'], PDO::PARAM_INT);
							$stmt->execute();
						}catch(\PDOException $ex){
							$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
							return false;
						}
					}
					return true;
				}else{
					$msg = 'Tu código de validación es incorrecto.';
					return $msg;
				}
			}else{
				return false;
			}
		}
		return false;
	}

	public function change_password(array $post){
		$password = $post['password'];
		$retype = $post['retype'];
		if($password && $retype){
			if($password == $this->user['username'] || $password == $this->user['email']){
				$this->error['password'] = 'Tu contraseña debe ser diferente de tu nombre de usuario o correo electrónico.';
				return false;
			}
			if(strlen($password) < 6){
				$this->error['password'] = 'Tu contraseña debe tener al menos 6 caracteres.';
				return false;
			}
			if($password == $retype){
				$options = ['cost' => 12];
				$hashed_password = password_hash($password, PASSWORD_BCRYPT, $options);
				$query = "UPDATE usuario SET password = :password, hash_activacion = NULL WHERE id_usuario = :id_usuario";
				try{
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(':password', $hashed_password, PDO::PARAM_INT);
					$stmt->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
					$stmt->execute();
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				$_SESSION['notification']['success'] = 'Tu contraseña ha sido reestablecida exitosamente. Ya puedes iniciar sesión.';
				$_SESSION['register_email'] = $this->user['email'];
				header('Location: '.HOST.'/login');
				die();
				return;
			}else{
				$this->error['password'] = 'Las contraseñas no coinciden.';
				return false;
			}
		}
		$this->error['password'] = 'Ambos campos son obligatorios.';
		return false;
	}

	public function get_password_error(){
		if($this->error['password']){
			return '<p class="text-danger">'._safe($this->error['password']).'</p>';
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
		file_put_contents(ROOT.'\assets\error_logs\change_password.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>