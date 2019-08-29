<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace admin\libs;
use assets\libs\connection;
use PDO;

class request_listing {
	private $con;
	private $user = array(
		'id' => null
	);
	private $request = array();
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
		$this->user['id'] = $_SESSION['user']['id_usuario'];
		return;
	}

	public function load_data($page = null, $rpp = null){
		$query = "SELECT COUNT(*) FROM solicitud_negocio WHERE situacion != 0";
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
			$query = "SELECT s.id_solicitud, u.username, u.nombre, u.apellido, s.nombre as negocio, s.id_categoria, nc.categoria, s.situacion, s.creado 
				FROM solicitud_negocio s
				INNER JOIN usuario u ON s.id_usuario = u.id_usuario
				INNER JOIN negocio_categoria nc ON s.id_categoria = nc.id_categoria 
				WHERE situacion != 0
				ORDER BY s.creado DESC
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
				$this->request[$row['id_solicitud']] = array(
					'username' => $row['username'],
					'name' => $row['nombre'],
					'last_name' => $row['apellido'],
					'business' => $row['negocio'],
					'category_id' => $row['id_categoria'],
					'category' => $row['categoria'],
					'status' => $row['situacion'],
					'created_at' => $row['creado']
				);
			}
			return $pagination;
		}
		return false;
	}

	public function get_requests(){
		$html = null;
		foreach ($this->request as $key => $value) {
			switch ($value['status']) {
				case 1:
					$status = '<span class="label label-success mr5">Aceptado</span>';
					break;
				case 2:
					$status = '<span class="label label-warning mr5">Pendiente</span>';
					break;
				case 3:
					$status = '<span class="label label-info mr5">Revisi&oacute;n</span>';
					break;
				case 4:
					$status = '<span class="label label-danger mr5">Rechazada</span>';
					break;
				default:
					$status = '';
					break;
			}
			$date = date('d/m/Y g:i A', strtotime($value['created_at']));
			$username = _safe($value['username']);
			if($value['name'] && $value['last_name']){
				$alias = _safe($value['name'].' '.$value['last_name']);
			}else{
				$alias = '<em>'.$username.'</em>';
			}
			$name = _safe($value['business']);
			$html .= 
			'<div class="background-white p20 mb30">
				<a href="'.HOST.'/admin/negocios/solicitud/'.$key.'">
					<label class="cert-date mr20">#'.$key.'</label>
					<span class="mr20">'.$status.'</span>
					<label class="cert-date mr20">'.$date.'</label>
					<label class="mr20">'.$name.'</label>
					<button class="btn btn-primary btn-xs pull-right">Ver detalles</button>
				</a>
			</div>';
		}
		if(!$html){
			$html = '<div class="background-white p20 mb30">A&uacute;n no hay solicitudes de negocio.</div>';
		}
		return $html;
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
		file_put_contents(ROOT.'\assets\error_logs\request_listing.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>