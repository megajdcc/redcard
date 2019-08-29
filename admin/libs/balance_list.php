<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace admin\libs;
use assets\libs\connection;
use PDO;

class balance_list {
	private $con;
	private $moves = array();
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

	public function load_data($page = null, $rpp = null){
		$query = "SELECT COUNT(*) FROM movimiento_saldo";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->pagination['total'] = $row['COUNT(*)'];
			$this->pagination['rpp'] = $rpp;
			$this->pagination['max'] = (int)ceil($this->pagination['total'] / $this->pagination['rpp']);
			$this->pagination['page'] = min($this->pagination['max'], $page);
			$this->pagination['offset'] = ($this->pagination['page'] - 1) * $this->pagination['rpp'];
			// Variables retornables
			$pagination['page'] = $this->pagination['page'];
			$pagination['total'] = $this->pagination['total'];
			// Cargar los certificados
			$query = "SELECT m.id_movimiento, n.url, n.nombre as negocio, u.username, u.nombre, u.apellido, m.cantidad, m.accion, m.creado 
				FROM movimiento_saldo m
				INNER JOIN negocio n ON m.id_negocio = n.id_negocio
				INNER JOIN usuario u ON m.id_usuario = u.id_usuario
				ORDER BY m.creado DESC
				LIMIT :limit OFFSET :offset";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':limit', $this->pagination['rpp'], PDO::PARAM_INT);
				$stmt->bindValue(':offset', $this->pagination['offset'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->moves[$row['id_movimiento']] = array(
					'url' => $row['url'],
					'business' => $row['negocio'],
					'username' => $row['username'],
					'name' => $row['nombre'],
					'last_name' => $row['apellido'],
					'amount' => $row['cantidad'],
					'action' => $row['accion'],
					'created_at' => $row['creado']
				);
			}
		return $pagination;
		}
		return false;
	}

	public function get_moves(){
		$moves = null;
		foreach ($this->moves as $key => $value) {
			$url = _safe($value['url']);
			$business = '<a href="'.HOST.'/'.$url.'" target="_blank">'._safe($value['business']).'</a>';
			$username = _safe($value['username']);
			if($value['name'] || $value['last_name']){
				$alias = _safe(trim($value['name'].' '.$value['last_name']));
			}else{
				$alias = $username;
			}
			$alias = '<a href="'.HOST.'/socio/'.$username.'" target="_blank">'.$alias.'</a>';
			$amount = number_format((float)$value['amount'], 2, '.', '');
			if($value['action'] == 1){
				$amount = '<strong class="text-primary">+'.$amount.'</strong>';
			}else{
				$amount = '<strong class="text-danger">-'.$amount.'</strong>';
			}
			$date = date('d/m/Y', strtotime($value['created_at']));
			$moves .= 
				'<tr>
					<td>'.$key.'</td>
					<td>'.$amount.'</td>
					<td>'.$business.'</td>
					<td>'.$alias.'</td>
					<td>'.$date.'</td>
				</tr>';
		}
		$html = 
			'<div class="table-responsive">
				<table class="table table-hover">
					<thead>
					<tr>
						<th>#</th>
						<th>Cantidad</th>
						<th>Negocio</th>
						<th>Registrante</th>
						<th>Fecha y Hora</th>
					</tr>
					</thead>
					<tbody>
					'.$moves.'
					</tbody>
				</table>
			</div>';
		return $html;
	}

	public function get_moves_pdf(){
		$query = "SELECT m.id_movimiento, n.nombre as negocio, u.username, u.nombre, u.apellido, m.cantidad, m.accion, m.creado 
			FROM movimiento_saldo m
			INNER JOIN negocio n ON m.id_negocio = n.id_negocio
			INNER JOIN usuario u ON m.id_usuario = u.id_usuario
			ORDER BY m.creado DESC";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$rows = null;
		while($row = $stmt->fetch()){
			$id = $row['id_movimiento'];
			$business = _safe($row['negocio']);
			if($row['nombre'] || $row['apellido']){
				$alias = _safe(trim($row['nombre'].' '.$row['apellido']));
			}else{
				$alias = _safe($row['username']);
			}
			$amount = number_format((float)$row['cantidad'], 2, '.', '');
			if($row['accion'] == 1){
				$amount = '+'.$amount;
			}else{
				$amount = '-'.$amount;
			}
			$date = date('d/m/Y', strtotime($row['creado']));
			$rows .= 
				'<tr>
					<td>'.$id.'</td>
					<td>'.$amount.'</td>
					<td>'.$business.'</td>
					<td>'.$alias.'</td>
					<td>'.$date.'</td>
				</tr>';
		}
		$html = 
'<style type="text/css">
	#cabecera{
		background:#f7f8f9;
		padding:10px 20px;
		border-radius: 6px;
		margin-bottom: 10px;
	}
	h1,h2{
		float:left;
		margin: 0;
	}
	table {
		width: 100%;
		border-spacing: 0;
		border-collapse: collapse;
		padding: 8px;
	}
	.table-bordered, th, td{
		border: 1px solid #ddd;
		padding: 5px;
	}

</style>
<page style="font-size: 12px">
	<div id="cabecera">
		<h1>Travel Points</h1>
		<h2>Reporte de movimientos de saldos</h2>
	</div>
	<table class="table-bordered">
		<thead>
			<tr>
				<th>#</th>
				<th>Cantidad</th>
				<th>Negocio</th>
				<th>Registrante</th>
				<th>Fecha y Hora</th>
			</tr>
		</thead>
		<tbody>
		'.$rows.'
		</tbody>
	</table>
</page>';

		require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/vendor/autoload.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/vendor/spipu/html2pdf/html2pdf.class.php';
		$html2pdf = new \HTML2PDF('P','A4','es');
		$html2pdf->WriteHTML($html);
		$html2pdf->Output('reporte.pdf');
		return;
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
		file_put_contents(ROOT.'\assets\error_logs\balance_list.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>