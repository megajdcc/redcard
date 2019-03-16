<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace negocio\libs;
use assets\libs\connection;
use PDO;

class reports_certificates {
	private $con;
	private $business = array(
		'id' => null,
		'date_start' => null,
		'date_end' => null,
		'redeemed' => array()
	);
	private $error = array('notification' => null, 'date_start' => null, 'date_end' => null);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->business['id'] = $_SESSION['business']['id_negocio'];
		return;
	}

	public function load_data(){
		if($this->business['date_start'] && $this->business['date_end']){
			$dates = " AND uc.actualizado BETWEEN '".$this->business['date_start']."' AND '".$this->business['date_end']."'";
		}else{
			$dates = '';
		}
		$query = "SELECT uc.id_uso, ne.nombre as certificado, u.username, u.nombre, u.apellido, u.email, uc.situacion, uc.actualizado
			FROM usar_certificado uc
			INNER JOIN negocio_certificado ne ON uc.id_certificado = ne.id_certificado
			INNER JOIN usuario u ON uc.id_usuario = u.id_usuario
			WHERE ne.id_negocio = :id_negocio $dates
			ORDER BY uc.id_uso ASC";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue('id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->business['redeemed'][$row['id_uso']] = array(
				'certificate' => $row['certificado'],
				'name' => $row['nombre'],
				'last_name' => $row['apellido'],
				'username' => $row['username'],
				'email' => $row['email'],
				'status' => $row['situacion'],
				'updated_at' => $row['actualizado']
			);
		}
		return;
	}

	public function get_redeemed(){
		$certs = null;
		foreach ($this->business['redeemed'] as $key => $value) {
			$certificate = _safe($value['certificate']);
			$name = _safe($value['name'].' '.$value['last_name']);
			$username = _safe($value['username']);
			$email = _safe($value['email']);
			$date = date('d/m/Y g:i A', strtotime($value['updated_at']));
			switch ($value['status']) {
				case 1:
					$status = 'Redimido';
					break;
				case 2:
					$status = 'Apartado';
					break;
				case 0:
					$status = 'Cancelado';
					break;
				default:
					$status = '';
					break;
			}
			$certs .= 
				'<tr>
					<td>'.$key.'</td>
					<td>'.$certificate.'</td>
					<td>'.$name.'</td>
					<td>'.$username.'</td>
					<td>'.$email.'</td>
					<td>'.$date.'</td>
					<td>'.$status.'</td>
				</tr>';
		}
		$html = 
			'<div class="table-responsive">
				<table class="table table-hover">
					<thead>
					<tr>
						<th>#</th>
						<th>Certificado</th>
						<th>Socio</th>
						<th>Username</th>
						<th>E-mail</th>
						<th>Fecha</th>
						<th>Estado</th>
					</tr>
					</thead>
					<tbody>
					'.$certs.'
					</tbody>
				</table>
			</div>';
		return $html;
	}

	public function set_date(array $post){
		$this->set_date_start($post['date_start']);
		$this->set_date_end($post['date_end']);
		if(!array_filter($this->error)){
			$this->load_data();
			return;
		}
		return false;
	}

	private function set_date_start($datetime = null){
		if($datetime){
			$datetime = str_replace('/', '-', $datetime);
			$datetime = strtotime($datetime);
			if(!$datetime){
				$this->error['date_start'] = 'Formato de fecha y hora incorrecto. Utiliza la herramienta.';
				return false;
			}
			$datetime = date("Y/m/d H:i:s", $datetime);
			$this->business['date_start'] = $datetime;
			return true;
		}
		$this->error['date_start'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_date_end($datetime = null){
		if($datetime){
			$datetime = str_replace('/', '-', $datetime);
			$datetime = strtotime($datetime);
			if(!$datetime){
				$this->error['date_end'] = 'Formato de fecha y hora incorrecto. Utiliza la herramienta.';
				return false;
			}
			$datetime = date("Y/m/d H:i:s", $datetime);
			$this->business['date_end'] = $datetime;
			return true;
		}
		$this->error['date_end'] = 'Este campo es obligatorio.';
		return false;
	}

	public function get_date_start(){
		if($this->business['date_start']){
			$datetime = date("d/m/Y h:i A", strtotime($this->business['date_start']));
			return $datetime;
		}
	}

	public function get_date_start_error(){
		if($this->error['date_start']){
			$error = '<p class="text-danger">'.$this->error['date_start'].'</p>';
			return $error;
		}
	}

	public function get_date_end(){
		if($this->business['date_end']){
			$datetime = date("d/m/Y h:i A", strtotime($this->business['date_end']));
			return $datetime;
		}
	}

	public function get_date_end_error(){
		if($this->error['date_end']){
			$error = '<p class="text-danger">'.$this->error['date_end'].'</p>';
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

	public function get_profile_url(){
		return HOST.'/'._safe($this->business['url']);
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\reports_certificates.txt', '['.date('d/M/Y h:i:s A').' | '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['notification'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>