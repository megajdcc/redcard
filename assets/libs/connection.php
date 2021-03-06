<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace assets\libs;
use PDO;

class connection {
	private $host = 'localhost';
	private $dbname = 'redcard';
	private $username = 'root';
	private $password = '20464273';
	private $options = array(PDO::ATTR_EMULATE_PREPARES => false, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
	public $con = null;

	function __construct(){
		$this->connect();
		$this->check_block();
		$this->check_role();
		$this->check_requests();
		$this->check_businesses();
		$this->check_business_role();
		return;
	}

	public function connect(){
		try{
			$this->con = new PDO("mysql:host=$this->host;dbname=$this->dbname;charset=utf8", $this->username, $this->password, $this->options);
			$this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->con->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			die('Failed to connect to the database.');
		}
	}

	private function check_block(){
		if(!isset($_SESSION['user']['id_usuario'])){
			return false;
		}
		$query = "SELECT activo FROM usuario WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $_SESSION['user']['id_usuario']);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			if($row['activo'] == 0){
				unset($_SESSION['user']);
				unset($_SESSION['business']);
				unset($_SESSION['notification']);
				header('Location: '.HOST.'/');
				die();
			}
		}
		return;
	}

	private function check_role(){
		if(!isset($_SESSION['user']['id_usuario'])){
			return false;
		}
		$query = "SELECT id_rol FROM usuario WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $_SESSION['user']['id_usuario']);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			if($row['id_rol'] != $_SESSION['user']['id_rol']){
				$_SESSION['user']['id_rol'] = $row['id_rol'];
				switch ($row['id_rol']) {
					case 1:
						$_SESSION['notify']['info'] = 'Ahora tienes acceso al panel administrativo de eSmart Club con permisos de Super Administrador.';
						break;
					case 2:
						$_SESSION['notify']['info'] = 'Ahora tienes acceso al panel administrativo de eSmart Club con permisos de Administrador.';
						break;
					case 3:
						$_SESSION['notify']['info'] = 'Ahora tienes acceso al panel administrativo de eSmart Club con permisos de Operador.';
						break;
					case 9:
						$_SESSION['notify']['info'] = 'Ahora tienes acceso al panel administrativo de eSmart Club con permisos de Encargado de Tienda.';
						break;
					case 8:
						$_SESSION['notify']['info'] = 'Ya no tienes acceso al Panel Administrativo.';
						break;
					default:
						break;
				}
			}
		}
		return;
	}

	private function check_business_role(){
		if(!isset($_SESSION['business']['id_negocio'])){
			return false;
		}
		$query = "SELECT ne.id_rol, n.nombre FROM negocio_empleado ne INNER JOIN negocio n ON ne.id_negocio = n.id_negocio WHERE ne.id_negocio = :id_negocio AND ne.id_empleado = :id_empleado";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $_SESSION['business']['id_negocio']);
			$stmt->bindValue(':id_empleado', $_SESSION['user']['id_usuario']);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			if($row['id_rol'] != $_SESSION['business']['id_rol']){
				$_SESSION['business']['id_rol'] = $row['id_rol'];
				switch ($row['id_rol']) {
					case 4:
						$_SESSION['notify']['info'] = 'Ahora tienes permisos de Administrador en el negocio '._safe($row['nombre']).'.';
						break;
					case 5:
						$_SESSION['notify']['info'] = 'Ahora tienes permisos de Gerente en el negocio '._safe($row['nombre']).'.';
						break;
					case 6:
						$_SESSION['notify']['info'] = 'Ahora tienes permisos de Vendedor en el negocio '._safe($row['nombre']).'.';
						break;
					default:
						break;
				}
			}
		}
		return;
	}

	private function check_businesses(){
		if(!isset($_SESSION['user']['id_usuario'])){
			return false;
		}
		if(isset($_SESSION['business']['id_negocio'])){
			$query = "SELECT n.nombre, ne.id_rol FROM 
				negocio n 
				LEFT JOIN negocio_empleado ne ON n.id_negocio = ne.id_negocio AND ne.id_empleado = :id_empleado
				WHERE n.id_negocio = :id_negocio";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_negocio', $_SESSION['business']['id_negocio']);
				$stmt->bindValue(':id_empleado', $_SESSION['user']['id_usuario']);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				if(!$row['id_rol']){
					$business = $row['nombre'];
					$query = "SELECT ne.id_rol, ne.id_negocio, n.url FROM negocio_empleado ne INNER JOIN negocio n ON ne.id_negocio = n.id_negocio WHERE ne.id_empleado = :id_empleado ORDER BY ne.creado LIMIT 1";
					try{
						$stmt = $this->con->prepare($query);
						$stmt->bindValue(':id_empleado', $_SESSION['user']['id_usuario']);
						$stmt->execute();
					}catch(\PDOException $ex){
						$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
						return false;
					}
					if($row = $stmt->fetch()){
						$_SESSION['business']['id_negocio'] = $row['id_negocio'];
						$_SESSION['business']['id_rol'] = $row['id_rol'];
						$_SESSION['business']['url'] = $row['url'];
					}else{
						unset($_SESSION['business']);
					}
					$_SESSION['notify']['info'] = 'Ya no eres miembro del negocio "'._safe($business).'". Ya no puedes acceder al panel de ese negocio.';
				}
			}
			return;
		}else{
			$query = "SELECT ne.id_rol, ne.id_negocio, n.url, n.nombre FROM negocio_empleado ne INNER JOIN negocio n ON ne.id_negocio = n.id_negocio WHERE ne.id_empleado = :id_empleado ORDER BY ne.creado LIMIT 1";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_empleado', $_SESSION['user']['id_usuario']);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				$_SESSION['business']['id_negocio'] = $row['id_negocio'];
				$_SESSION['business']['id_rol'] = $row['id_rol'];
				$_SESSION['business']['url'] = $row['url'];
				$_SESSION['notify']['info'] = 'Ahora eres miembro del negocio "'._safe($row['nombre']).'". Ya puedes acceder al panel de ese negocio.';
			}
			return;
		}
	}

	public function check_requests(){
		if(!isset($_SESSION['user']['id_usuario'])){
			return false;
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
			if(!array_key_exists($row['id_solicitud'], $_SESSION['pending_request'])){
				$_SESSION['pending_request'][$row['id_solicitud']] = $row['nombre'];
			}
		}
		foreach ($_SESSION['pending_request'] as $key => $value) {
			$query = "SELECT mostrar_usuario FROM solicitud_negocio WHERE id_solicitud = :id_solicitud";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_solicitud', $key);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				if($row['mostrar_usuario'] != 2){
					switch ($row['mostrar_usuario']) {
						case 1:
							$_SESSION['notify']['success'] = 'Tu solicitud del negocio "'._safe($value).'" ha sido aceptada. Puedes entrar al panel de negocio <a href="'.HOST.'/socio/negocios/">aqu&iacute;</a>';
							if(!isset($_SESSION['business'])){
								$query = "SELECT ne.id_negocio, ne.id_rol, n.url
									FROM negocio_empleado ne
									INNER JOIN negocio n ON ne.id_negocio = n.id_negocio
									WHERE n.id_solicitud = :id_solicitud";
								try{
									$stmt = $this->con->prepare($query);
									$stmt->bindValue(':id_solicitud', $key);
									$stmt->execute();
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
							break;
						case 3:
							$_SESSION['notify']['warning'] = 'Tu solicitud del negocio "'._safe($value).'" ha sido regresada para correcciones. Puedes revisar tu solicitud <a href="'.HOST.'/socio/negocios/solicitudes">aqu&iacute;</a>';
							break;
						case 4:
							$_SESSION['notify']['danger'] = 'Tu solicitud del negocio "'._safe($value).'" ha sido rechazada. Puedes revisar tu solicitud <a href="'.HOST.'/socio/negocios/solicitudes">aqu&iacute;</a>';
							break;
						default:
							# code...
							break;
					}
					unset($_SESSION['pending_request'][$key]);
				}
			}
		}
		return;
	}

	public function get_notify(){
		$html = null;
		if(isset($_SESSION['notify']['success'])){
			$html .= 
			'<div class="alert alert-dismissible alert-success" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'.$_SESSION['notify']['success'].'
			</div>';
			unset($_SESSION['notify']['success']);
		}
		if(isset($_SESSION['notify']['info'])){
			$html .= 
			'<div class="alert alert-dismissible alert-info" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'.$_SESSION['notify']['info'].'
			</div>';
			unset($_SESSION['notify']['info']);
		}
		if(isset($_SESSION['notify']['warning'])){
			$html .= 
			'<div class="alert alert-dismissible alert-warning" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'.$_SESSION['notify']['warning'].'
			</div>';
			unset($_SESSION['notify']['warning']);
		}
		if(isset($_SESSION['notify']['danger'])){
			$html .= 
			'<div class="alert alert-dismissible alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'.$_SESSION['notify']['danger'].'
			</div>';
			unset($_SESSION['notify']['danger']);
		}
		return $html;
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\connection.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>