<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace admin\libs;
use assets\libs\connection;
use PDO;

class remove_balance_business {
	private $con;
	private $user = array('id' => null);
	private $new_balance = array(
		'id' => null, 
		'url' => null, 
		'balance' => null
	);
	private $error = array(
		'url' => null,
		'balance' => null,
		'warning' => null,
		'error' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->user['id'] = $_SESSION['user']['id_usuario'];
		return;
	}

	public function remove_balance(array $post){
		$this->set_url($post['url']);
		$this->set_balance($post['balance']);
		if(!array_filter($this->error)){
			$query = "INSERT INTO movimiento_saldo (
				id_negocio,
				id_usuario, 
				cantidad,
				accion
				) VALUES (
				:id_negocio,
				:id_usuario, 
				:cantidad,
				0
			)";
			$params = array(
				':id_negocio' => $this->new_balance['id'],
				':id_usuario' => $this->user['id'],
				':cantidad' => $this->new_balance['balance']
			);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$query = "UPDATE negocio SET saldo = saldo - :balance, ultima_recarga = DEFAULT WHERE id_negocio = :id_negocio";
			$params = array(
				':balance' => $this->new_balance['balance'],
				':id_negocio' => $this->new_balance['id']
			);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$_SESSION['notification']['success'] = 'Se le han quitado $'.$this->new_balance['balance'].' de saldo al negocio exitosamente.';
			header('Location: '.HOST.'/admin/negocios/quitar-saldo');
			die();
			return;
		}
		$this->error['warning'] = 'Uno o más campos tienen errores. Verifícalos cuidadosamente.';
		return false;
	}

	private function set_url($url = null){
		if($url){
			$this->new_balance['url'] = trim($url);
			if(!preg_match('/^[a-z0-9-]+$/ui', $this->new_balance['url'])){
				$this->error['url'] = 'La url solo debe contener letras, números y guiones (-). No se permite acentos.';
				return false;
			}
			$query = "SELECT id_negocio FROM negocio WHERE url = :url";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':url', $this->new_balance['url'], PDO::PARAM_STR);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				$this->new_balance['id'] = $row['id_negocio'];
				return true;
			}
			$this->error['url'] = 'Este negocio no existe.';
			return false;
		}
		$this->error['url'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_balance($balance = null){
		if($balance){
			$balance = str_replace(',','.',$balance);
			$balance = filter_var($balance, FILTER_VALIDATE_FLOAT);
			if(!$balance){
				$this->errors['balance'] = 'Ingresa una cantidad correcta.';
				return false;
			}
			$this->new_balance['balance'] = $balance;
			return;
		}
		$this->error['balance'] = 'Este campo es obligatorio.';
		return false;
	}

	public function get_url(){
		return _safe($this->new_balance['url']);
	}

	public function get_url_error(){
		if($this->error['url']){
			$error = '<p class="text-danger">'._safe($this->error['url']).'</p>';
			return $error;
		}
	}

	public function get_balance(){
		return _safe($this->new_balance['balance']);
	}

	public function get_balance_error(){
		if($this->error['balance']){
			$error = '<p class="text-danger">'._safe($this->error['balance']).'</p>';
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
		file_put_contents(ROOT.'\assets\error_logs\recharge_business.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>