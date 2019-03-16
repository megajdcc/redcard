<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace negocio\libs;
use assets\libs\connection;
use PDO;

class reports_sales {
	private $con;
	private $business = array(
		'id' => null,
		'date_start' => null,
		'date_end' => null,
		'sales' => array(),
		'user_id' => null,
		'business_category' => null,
	);
	private $sales = array();
	private $bonus = 0;
	private $error = array('notification' => null, 'date_start' => null, 'date_end' => null);


	public function __construct(connection $con){
		$this->con = $con->con;
		$this->business['id'] = $_SESSION['business']['id_negocio'];
		return;
	}

	public function load_data(){
		if($this->business['date_start'] && $this->business['date_end']){
			$dates = " AND nv.creado BETWEEN '".$this->business['date_start']."' AND '".$this->business['date_end']."'";
		}else{
			$dates = '';
		}
		$query = "SELECT iso FROM divisa";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->sales[$row['iso']] = 0;
		}
		$query = "SELECT nv.id_venta, u.username, u.nombre, u.apellido, p.pais, nv.iso, nv.venta, nv.comision, nv.bono_esmarties, nv.creado, r.username as e_username, r.nombre as e_nombre, r.apellido as e_apellido 
			FROM negocio_venta nv
			INNER JOIN usuario u ON nv.id_usuario = u.id_usuario
			LEFT JOIN ciudad c ON u.id_ciudad = c.id_ciudad
			LEFT JOIN estado e ON c.id_estado = e.id_estado
			LEFT JOIN pais p ON e.id_pais = p.id_pais
			INNER JOIN usuario r ON nv.id_empleado = r.id_usuario 
			WHERE id_negocio = :id_negocio $dates 
			ORDER BY nv.id_venta ASC";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue('id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->business['sales'][$row['id_venta']] = array(
				'username' => $row['username'],
				'name' => $row['nombre'],
				'last_name' => $row['apellido'],
				'country' => $row['pais'],
				'iso' => $row['iso'],
				'total' => $row['venta'],
				'commission' => $row['comision'],
				'esmarties' => $row['bono_esmarties'],
				'created_at' => $row['creado'],
				'e_username' => $row['e_username'],
				'e_name' => $row['e_nombre'],
				'e_last_name' => $row['e_apellido']
			);
			$this->sales[$row['iso']] += $row['venta'];
			$this->bonus += $row['bono_esmarties'];
		}
		return;
	}

	public function set_date(array $post){
		$this->business['user_id'] = $post['user_id'];
		$this->business['business_category_id'] = $post['category_id'];
		$this->set_date_start($post['date_start']);
		$this->set_date_end($post['date_end']);
		if(!array_filter($this->error)){
			$this->load_data();
			return;
		}
		return false;
	}

	public function get_sales(){
		$sales = null;
		foreach ($this->business['sales'] as $key => $value) {
			$date = date('d/m/Y g:i A', strtotime($value['created_at']));
			$total = number_format((float)$value['total'], 2, '.', '').' '.$value['iso'];
			$commission = _safe($value['commission']);
			$esmarties = number_format((float)$value['esmarties'], 2, '.', '');
			if(empty($value['name']) && empty($value['last_name'])){
				$customer = _safe($value['username']);
			}else{
				$customer = _safe($value['name'].' '.$value['last_name']);
			}
			$origin = _safe($value['country']);
			if(empty($value['e_name']) && empty($value['e_last_name'])){
				$registrar = _safe($value['e_username']);
			}else{
				$registrar = _safe($value['e_name'].' '.$value['e_last_name']);
			}
			$sales .= 
				'<tr>
					<td>'.$key.'</td>
					<td>'.$date.'</td>
					<td>'.$total.'</td>
					<td>'.$commission.'%</td>
					<td>'.$esmarties.'</td>
					<td>'.$customer.'</td>
					<td>'.$origin.'</td>
					<td>'.$registrar.'</td>
				</tr>';
		}
		$html = 
			'<div class="table-responsive">
				<table class="table table-bordered table-hover">
					<thead>
					<tr>
						<th>#</th>
						<th>Fecha y hora</th>
						<th>Venta</th>
						<th>Comisi&oacute;n</th>
						<th>eSmartties</th>
						<th>Cliente</th>
						<th>Origen</th>
						<th>Registrante</th>
					</tr>
					</thead>
					<tbody>
					'.$sales.'
					</tbody>
				</table>
			</div>';
		return $html;
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

	public function get_total_sales(){
		$html = null;
		foreach ($this->sales as $key => $value) {
			if($value != 0){
				$html .= 
				'<div class="col-sm-3">
					<div class="form-group">
						<label for="'.$key.'">Total de venta: '.$key.'</label>
						<input class="form-control" type="text" id="'.$key.'" value="'.$value.'" readonly />
					</div>
				</div>';
			}
		}
		return $html;
	}

	public function get_total_esmarties(){
		return _safe($this->bonus);
	}

	public function get_date_start(){
		if($this->business['date_start']){
			$datetime = date("d/m/Y h:i A", strtotime($this->business['date_start']));
			return $datetime;
		}
	}

	public function get_user_id(){
		if($this->business['user_id']){
			return $this->business['user_id'];
		}
	}

	public function get_business_category_id(){
		if($this->business['business_category_id']){
			return $this->business['business_category_id'];
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
		file_put_contents(ROOT.'\assets\error_logs\reports_sales.txt', '['.date('d/M/Y h:i:s A').' | '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['notification'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>