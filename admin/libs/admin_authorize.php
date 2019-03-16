<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace admin\libs;
use assets\libs\connection;
use PDO;

class admin_authorize {
	private $con;
	private $admin = array('id' => null, 'security_code' => null);
	private $error = array(
		'authorize' => null,
		'security_code' => null,
		'warning' => null,
		'error' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->admin['id'] = $_SESSION['user']['id_usuario'];
		return;
	}

	public function set_data(array $post){
		if($_SESSION['user']['id_rol'] == 1 || $_SESSION['user']['id_rol'] == 2 || $_SESSION['user']['id_rol'] == 3){
			$this->set_security_code($post['security_code']);
			if(!array_filter($this->error)){
				$this->authorize();
				return true;
			}
		}
		return false;
	}

	private function set_security_code($code = null){
		if($code){
			$this->admin['security_code'] = $code;
			return true;
		}
		// $this->error['security_code'] = 'Please enter your authorization code.';
		$this->error['security_code'] = 'Escribe tu código de seguridad.';
		return false;
	}

	private function authorize(){
		$query = "SELECT codigo_seguridad FROM codigo_administrador WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $this->admin['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			if(password_verify($this->admin['security_code'],$row['codigo_seguridad'])){
				$_SESSION['user']['admin_authorize'] = true;
				header('Location: '.HOST.'/admin/');
				die();
				return true;
			}
			$this->error['authorize'] = 'Código de seguridad incorrecto.';
			return false;
		}
		// $this->error['authorize'] = 'Authorization code not defined. Please contact an administrator.';
		$this->error['authorize'] = 'No se ha definido un código de autorización.';
		return false;
	}

	public function get_authorize_error(){
		if($this->error['authorize']){
			$error = '<p class="text-danger">'._safe($this->error['authorize']).'</p>';
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
		file_put_contents(ROOT.'\assets\error_logs\admin_authorize.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>