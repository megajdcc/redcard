<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace negocio\libs;
use assets\libs\connection;
use PDO;

class preference_images {
	private $con;
	private $business = array(
		'id' => null, 
		'url' => null,
		'logo' => array(),
		'header' => array()
	);
	private $error = array(
		'notification' => null,
		'logo' => null,
		'header' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->business['id'] = $_SESSION['business']['id_negocio'];
		$this->business['url'] = $_SESSION['business']['url'];
		$this->load_data();
		return;
	}

	private function load_data(){
		$query = "SELECT p.id_preferencia, np.preferencia
			FROM preferencia p 
			LEFT JOIN negocio_preferencia np ON p.id_preferencia = np.id_preferencia AND np.id_negocio = ?
			WHERE p.llave = 'business_logo'";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(1, $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['logo'] = array('id' => $row['id_preferencia'], 'preference' => $row['preferencia']);
		}
		$query = "SELECT p.id_preferencia, np.preferencia
			FROM preferencia p 
			LEFT JOIN negocio_preferencia np ON p.id_preferencia = np.id_preferencia AND np.id_negocio = ?
			WHERE p.llave = 'business_header'";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(1, $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['header'] = array('id' => $row['id_preferencia'], 'preference' => $row['preferencia']);
		}
		return;
	}

	public function set_logo($files = null){
		$image = new \assets\libraries\bulletproof\bulletproof($files);
		$image->setName($this->get_url().'-logo-esmart-club');
		$image->setLocation(ROOT.'/assets/img/business/logo');
		if($image['logo']){
			if($image->upload()){
				$file_name = $image->getName().'.'.$image->getMime();
				if(!move_uploaded_file($files['logo']['tmp_name'], $image->getFullPath())){
					$this->error['notification'] = 'Error al tratar de subir la imagen.';
					return false;
				}
				if($this->business['logo']['preference'] != $file_name){
					if(file_exists(ROOT.'/assets/img/business/logo/'.$this->business['logo']['preference'])){
						unlink(ROOT.'/assets/img/business/logo/'.$this->business['logo']['preference']);
					}
					$query = "UPDATE negocio_preferencia 
						SET preferencia = :preferencia 
						WHERE id_negocio = :id_negocio AND id_preferencia = :id_preferencia";
					$params = array(
						':preferencia' => $file_name,
						':id_negocio' => $this->business['id'], 
						'id_preferencia' => $this->business['logo']['id']);
					try{
						$stmt = $this->con->prepare($query);
						$stmt->execute($params);
					}catch(\PDOException $ex){
						$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
						return false;
					}
				}
				$_SESSION['notification']['success'] = 'Logo del negocio actualizado correctamente';
				header('Location: '.HOST.'/negocio/preferencias/logo-y-portada');
				die();
				return;
			}
			$this->error['logo'] = $image['error'];
			return false;
		}
		if($files['logo']['error'] == 1){
			$this->error['logo'] = 'Has excedido el límite de imagen de 2MB.';
		}else{
			$this->error['logo'] = 'Este campo es obligatorio.';
		}
		return false;
	}

	public function set_header($files = null){
		$image = new \assets\libraries\bulletproof\bulletproof($files);
		$image->setName($this->get_url().'-portada-esmart-club');
		$image->setLocation(ROOT.'/assets/img/business/header');
		if($image['header']){
			if($image->upload()){
				$file_name = $image->getName().'.'.$image->getMime();
				if(!move_uploaded_file($files['header']['tmp_name'], $image->getFullPath())){
					$this->error['notification'] = 'Error al tratar de subir la imagen.';
					return false;
				}
				if($this->business['header']['preference'] != $file_name){
					if(file_exists(ROOT.'/assets/img/business/header/'.$this->business['header']['preference'])){
						unlink(ROOT.'/assets/img/business/header/'.$this->business['header']['preference']);
					}
					$query = "UPDATE negocio_preferencia 
						SET preferencia = :preferencia 
						WHERE id_negocio = :id_negocio AND id_preferencia = :id_preferencia";
					$params = array(
						':preferencia' => $file_name,
						':id_negocio' => $this->business['id'], 
						'id_preferencia' => $this->business['header']['id']);
					try{
						$stmt = $this->con->prepare($query);
						$stmt->execute($params);
					}catch(\PDOException $ex){
						$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
						return false;
					}
				}
				$_SESSION['notification']['success'] = 'Foto de portada actualizada correctamente';
				header('Location: '.HOST.'/negocio/preferencias/logo-y-portada');
				die();
				return;
			}
			$this->error['header'] = $image['error'];
			return false;
		}
		if($files['header']['error'] == 1){
			$this->error['header'] = 'Has excedido el límite de imagen de 2MB.';
		}else{
			$this->error['header'] = 'Este campo es obligatorio.';
		}
		return false;
	}

	public function get_logo_url(){
		return HOST.'/assets/img/business/logo/'._safe($this->business['logo']['preference']);
	}

	public function get_logo_error(){
		if($this->error['logo']){
			$error = '<p class="text-danger">'.$this->error['logo'].'</p>';
			return $error;
		}
	}

	public function get_header_url(){
		return HOST.'/assets/img/business/header/'._safe($this->business['header']['preference']);
	}

	public function get_header_error(){
		if($this->error['header']){
			$error = '<p class="text-danger">'.$this->error['header'].'</p>';
			return $error;
		}
	}

	public function get_url(){
		return _safe($this->business['url']);
	}

	public function get_profile_url(){
		return HOST.'/'._safe($this->business['url']);
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
		file_put_contents(ROOT.'\assets\error_logs\preference_images.txt', '['.date('d/M/Y h:i:s A').' | '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['notification'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>