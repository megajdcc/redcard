<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace assets\libs;
use PDO;

class user_login {
	private $con;
	private $login = array('email' => null, 'password' => null);
	private $error = array('login' => null, 'email' => null, 'password' => null, 'warning' => null, 'error' => null);

	public function __construct(connection $con){
		$this->con = $con->con;
		return;
	}

	public function validate_account($email, $hash){
		$query = "SELECT id_usuario, hash_activacion, verificado FROM usuario WHERE email = :email";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':email', $email, PDO::PARAM_STR);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$_SESSION['register_email'] = $email;
			if($row['verificado'] == 0){
				if(!is_null($row['hash_activacion']) && $hash == $row['hash_activacion']){
					$query = "UPDATE usuario SET verificado = 1, hash_activacion = NULL WHERE id_usuario = :id_usuario";
					try{
						$stmt = $this->con->prepare($query);
						$stmt->bindValue(':id_usuario', $row['id_usuario'], PDO::PARAM_INT);
						$stmt->execute();
					}catch(\PDOException $ex){
						$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
						return false;
					}
					$_SESSION['notification']['success'] = 'Tu cuenta ha sido verificada correctamente. Ya puedes iniciar sesión.';
					header('Location: '.HOST.'/login');
					die();
					return;
				}else{
					$this->error['error'] = 'El c&oacute;digo de seguridad no coincide. Si has pedido multiples verificaciones recientemente es posible que debas utilizar el enlace enviado en el correo m&aacute;s reciente. Si a&uacute;n as&iacute; no funciona, prueba enviando otro correo de verificaci&oacute;n. <a href="'.HOST.'/recuperar-cuenta">aqu&iacute;</a>.';
					return;
				}
			}else{
				$_SESSION['notification']['info'] = 'Tu cuenta ya está verificada correctamente.';
				header('Location: '.HOST.'/login');
				die();
				return;
			}
		}
		$this->error['error'] = _safe('No existe ninguna cuenta asociada a ese correo electrónico.');
		return false;
	}

	public function set_data(array $post){
		if(!isset($_SESSION['user'])){
			$this->set_email($post['email']);
			$this->set_password($post['password']);
			if(!array_filter($this->error)){
				$this->login();
				return true;
			}
		}
		return false;
	}

	private function login(){
		$query = "SELECT 
				id_usuario, 
				password, 
				id_rol, 
				verificado, 
				activo 
			FROM usuario WHERE email = :email";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':email', $this->login['email'], PDO::PARAM_STR);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			if($row['activo'] == 0){
				// $this->error['login'] = 'This account has been terminated.';
				$this->error['login'] = 'Esta cuenta ha sido bloqueada.';
				return false;
			}
			if($row['activo'] == 2){
				// Metodo para recuperar cuenta <_<
				// $this->error['login'] = 'This account has been deactivated.';
				$this->error['login'] = 'Esta cuenta ha sido desactivada';
				return false;
			}
			if($row['verificado'] == 0){
				$this->error['login'] = 'Debes verificar tu correo electr&oacute;nico para acceder con tu cuenta. Enviar otro correo de verificaci&oacute;n <a href="'.HOST.'/recuperar-cuenta">aqu&iacute;</a>.';
				return false;
			}
			if(password_verify($this->login['password'],$row['password'])){ // Si el login es exitoso
				unset($row['password']); // Quitamos la contraseña
				$row['follow_user'] = $row['follow_business'] = $row['recommend_business'] = $row['certificate_wishlist'] = array();
				$_SESSION['user'] = $row; // Creamos la sesion con la información
				$_SESSION['pending_request'] = array();
				$query = "UPDATE usuario SET ultimo_login = DEFAULT, hash_activacion = NULL WHERE id_usuario = :id_usuario"; // Actualizamos el ultimo logeo
				try{
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(':id_usuario', $_SESSION['user']['id_usuario'], PDO::PARAM_INT);
					$stmt->execute();
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				$query = "SELECT id_negocio FROM seguir_negocio WHERE id_usuario = :id_usuario"; // Obtenemos todos los negocios seguidos
				try{
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(':id_usuario', $_SESSION['user']['id_usuario'], PDO::PARAM_INT);
					$stmt->execute();
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				while($row = $stmt->fetch()){
					$_SESSION['user']['follow_business'][$row['id_negocio']] = true;
				}
				$query = "SELECT id_negocio FROM recomendar_negocio WHERE id_usuario = :id_usuario"; // Obtener todos los negocios recomendados
				try{
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(':id_usuario', $_SESSION['user']['id_usuario'], PDO::PARAM_INT);
					$stmt->execute();
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				while($row = $stmt->fetch()){
					$_SESSION['user']['recommend_business'][$row['id_negocio']] = true;
				}
				$query = "SELECT id_certificado FROM lista_deseos_certificado WHERE id_usuario = :id_usuario"; // Obtener los certificados en wishlist
				try{
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(':id_usuario', $_SESSION['user']['id_usuario'], PDO::PARAM_INT);
					$stmt->execute();
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				while($row = $stmt->fetch()){
					$_SESSION['user']['certificate_wishlist'][$row['id_certificado']] = true;
				}
				$query = "SELECT up.preferencia 
					FROM usuario_preferencia up 
					INNER JOIN preferencia p ON up.id_preferencia = p.id_preferencia 
					WHERE up.id_usuario = :id_usuario AND p.llave = 'default_business'"; // Obtener la preferencia de su negocio
				try{
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(':id_usuario', $_SESSION['user']['id_usuario'], PDO::PARAM_INT);
					$stmt->execute();
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				if($row = $stmt->fetch()){
					$business_id = (int)$row['preferencia'];
					$query = "SELECT ne.id_negocio, ne.id_rol, n.url 
						FROM negocio_empleado ne 
						INNER JOIN negocio n on ne.id_negocio = n.id_negocio 
						WHERE ne.id_empleado = :id_empleado AND ne.id_negocio = :id_negocio"; // Si tiene negocio
					$params = array(
						'id_empleado' => $_SESSION['user']['id_usuario'],
						'id_negocio' => $business_id
					);
					try{
						$stmt = $this->con->prepare($query);
						$stmt->execute($params);
					}catch(\PDOException $ex){
						$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
						return false;
					}
					if($row = $stmt->fetch()){
						$_SESSION['business']['id_negocio'] = $row['id_negocio'];
						$_SESSION['business']['id_rol'] = $row['id_rol'];
						$_SESSION['business']['url'] = $row['url'];
					}
				}
				// solicitudes pendientes
				$query = "SELECT id_solicitud, nombre FROM solicitud_negocio WHERE id_usuario = :id_usuario AND mostrar_usuario = 2";
				try{
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(':id_usuario', $_SESSION['user']['id_usuario']);
					$stmt->execute();
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				while($row = $stmt->fetch()){
					$_SESSION['pending_request'][$row['id_solicitud']] = $row['nombre'];
				}
				if($_SERVER['SCRIPT_NAME'] == '/afiliar-negocio.php'){
					header('Location: '.HOST.'/afiliar-negocio');
				}else{
					header('Location: '.HOST.'/socio/');
				}
				die();
			}
			// $this->errors['login'] = 'Incorrect e-mail or password.';
			$this->error['login'] = _safe('Correo electrónico o contraseña incorrectos.');
			return $this;
		}
		// $this->errors['login'] = 'Incorrect e-mail or password.';
		$this->error['login'] = _safe('Correo electrónico o contraseña incorrectos.');
		return $this;
	}

	private function set_email($email = null){
		if($email){
			$_email = filter_var($email, FILTER_VALIDATE_EMAIL);
			if($_email){
				$this->login['email'] = $_email;
				return $_email;
			}
			$this->login['email'] = $email;
			// $this->error['email'] = 'Please enter a correct e-mail address. Example: user@example.com.';
			$this->error['email'] = 'Escribe una dirección de correo electrónico correcta. Ejemplo: usuario@ejemplo.com.';
			return false;
		}
		// $this->error['email'] = 'You must enter your e-mail.';
		$this->error['email'] = 'Escribe tu dirección de correo electrónico.';
		return false;
	}

	private function set_password($password = null){
		if($password){
			$this->login['password'] = $password;
			return $password;
		}
		// $this->error['password'] = 'You must enter your password.';
		$this->error['password'] = 'Escribe tu contraseña.';
		return false;
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
				'.$this->error['error'].'
			</div>';
		}
		return $html;
	}

	public function get_login_error(){
		if($this->error['login']){
			$error = '<p class="text-danger">'.$this->error['login'].'</p>';
			return $error;
		}
	}

	public function get_email(){
		if(isset($_SESSION['register_email'])){
			$email = _safe($_SESSION['register_email']);
			unset($_SESSION['register_email']);
			return $email;
		}
		return _safe($this->login['email']);
	}

	public function get_email_error(){
		if($this->error['email']){
			$error = '<p class="text-danger">'._safe($this->error['email']).'</p>';
			return $error;
		}
	}

	public function get_password_error(){
		if($this->error['password']){
			$error = '<p class="text-danger">'._safe($this->error['password']).'</p>';
			return $error;
		}
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\user_login.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = _safe('Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.');
		return;
	}
}
?>