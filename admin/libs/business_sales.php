<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace admin\libs;
use assets\libs\connection;
use PDO;

class business_sales {
	private $con;
	private $business = array(
		'id' => null,
		'name' => null,
		'url' => null
	);
	private $dates = array(
		'start' => null,
		'end' => null
	);
	private $sales = array();
	private $error = array(
		'warning' => null,
		'error' => null
	);
	private $pagination = array(
		'total' => null,
		'rpp' => null,
		'max' => null,
		'page' => null,
		'offset' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		return;
	}

	public function set_date(array $post){
		$this->set_date_start($post['date_start']);
		$this->set_date_end($post['date_end']);
		return;
	}

	public function load_data($url, $page = null, $rpp = null){
		if($this->dates['start'] && $this->dates['end']){
			$dates = " AND nv.creado BETWEEN '".$this->dates['start']."' AND '".$this->dates['end']."'";
		}else{
			$dates = '';
		}
		$query = "SELECT n.id_negocio, n.url, n.nombre FROM negocio n WHERE n.url = :url";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':url', $url, PDO::PARAM_STR);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['id'] = $row['id_negocio'];
			$this->business['name'] = $row['nombre'];
			$this->business['url'] = $row['url'];
		}
		$query = "SELECT COUNT(id_venta) as ventas FROM negocio_venta nv WHERE nv.id_negocio = :id_negocio $dates ";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio',  $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->pagination['total'] = $row['ventas'];
			$this->pagination['rpp'] = $rpp;
			$this->pagination['max'] = (int)ceil($this->pagination['total'] / $this->pagination['rpp']);
			$this->pagination['page'] = min($this->pagination['max'], $page);
			$this->pagination['offset'] = ($this->pagination['page'] - 1) * $this->pagination['rpp'];
			// Variables retornables
			$pagination['page'] = $this->pagination['page'];
			$pagination['total'] = $this->pagination['total'];
			// Cargar los certificados
			$query = "SELECT nv.id_venta, u.username, u.nombre, u.apellido, nv.iso, nv.venta, nv.comision, nv.bono_esmarties, nv.bono_referente, nv.creado, e.username as e_username, e.nombre as e_nombre, e.apellido as e_apellido 
				FROM negocio_venta nv
				INNER JOIN usuario u ON nv.id_usuario = u.id_usuario
				INNER JOIN usuario e ON nv.id_empleado = e.id_usuario 
				WHERE nv.id_negocio = :id_negocio $dates 
				ORDER BY nv.creado DESC
				LIMIT :limit OFFSET :offset";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_negocio',  $this->business['id'], PDO::PARAM_INT);
				$stmt->bindValue(':limit', $this->pagination['rpp'], PDO::PARAM_INT);
				$stmt->bindValue(':offset', $this->pagination['offset'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->sales[] = $row;
			}
			return $pagination;
		}else{
			$pagination['total'] = 0;
			$pagination['page'] = 0;
			return $pagination;
		}
		return false;
	}

	public function get_sales(){
		$sales = null;
		foreach ($this->sales as $key => $value) {
			$id = $value['id_venta'];
			$customer_username = _safe($value['username']);
			if($value['nombre'] || $value['apellido']){
				$customer = _safe(trim($value['nombre'].' '.$value['apellido']));
			}else{
				$customer = $customer_username;
			}
			$customer = '<a href="'.HOST.'/socio/'.$customer_username.'" target="_blank">'.$customer.'</a>';
			$employee_username = _safe($value['e_username']);
			if($value['e_nombre'] || $value['e_apellido']){
				$employee = _safe(trim($value['e_nombre'].' '.$value['e_apellido']));
			}else{
				$employee = $employee_username;
			}
			$employee = '<a href="'.HOST.'/socio/'.$employee_username.'" target="_blank">'.$employee.'</a>';

			$total = number_format((float)$value['venta'], 2, '.', '');
			$iso = $value['iso'];
			$commission = (int)$value['comision'];
			$esmarties = $value['bono_esmarties'];
			$referral = $value['bono_referente'];
			$date = date('d/m/Y \a \l\a\s g:i A', strtotime($value['creado']));

			$sales .= 
				'<tr>
					<td>'.$id.'</td>
					<td>'.$customer.'</td>
					<td>'.$total.'</td>
					<td>'.$iso.'</td>
					<td>'.$commission.'%</td>
					<td>'.$esmarties.'</td>
					<td>'.$referral.'</td>
					<td>'.$employee.'</td>
					<td>'.$date.'</td>
				</tr>';
		}
		if($sales){
			$html = 
				'<div class="table-responsive">
					<table class="table table-hover">
						<thead>
						<tr>
							<th>#</th>
							<th>Cliente</th>
							<th>Total</th>
							<th>Divisa</th>
							<th>Comisi&oacute;n</th>
							<th>Bono eSmartties</th>
							<th>Bono Referente</th>
							<th>Registrante</th>
							<th>Fecha y Hora</th>
						</tr>
						</thead>
						<tbody>
						'.$sales.'
						</tbody>
					</table>
				</div>';
		}else{
			if($this->dates['start'] && $this->dates['end']){
				$html = '<hr><p class="text-default">Este negocio no ha registrado ventas entre esas fechas.</p>';
			}else{
				$html = '<hr><p class="text-default">Este negocio no ha registrado ventas.</p>';
			}
		}
		return $html;
	}

	private function set_date_start($datetime = null){
		if($datetime){
			$datetime = str_replace('/', '-', $datetime);
			$datetime = strtotime($datetime);
			if(!$datetime){
				return false;
			}
			$datetime = date("Y/m/d H:i:s", $datetime);
			$this->dates['start'] = $datetime;
			return true;
		}
		return false;
	}

	private function set_date_end($datetime = null){
		if($datetime){
			$datetime = str_replace('/', '-', $datetime);
			$datetime = strtotime($datetime);
			if(!$datetime){
				return false;
			}
			$datetime = date("Y/m/d H:i:s", $datetime);
			$this->dates['end'] = $datetime;
			return true;
		}
		return false;
	}

	public function get_business_name(){
		return _safe($this->business['name']);
	}

	public function get_business_url(){
		return _safe($this->business['url']);
	}

	public function get_date_start(){
		if($this->dates['start']){
			return date("d/m/Y", strtotime($this->dates['start']));
		}
	}

	public function get_date_end(){
		if($this->dates['end']){
			return date("d/m/Y", strtotime($this->dates['end']));
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
		file_put_contents(ROOT.'\assets\error_logs\business_sales.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>