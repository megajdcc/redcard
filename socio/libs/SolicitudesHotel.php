<?php 

namespace socio\libs;
use assets\libs\connection;
use PDO;
/**
 * @author Crespo Jhonatan
 * @since 15/05/2019
 * 
 */
class SolicitudesHotel {
	private $con;
	private $user = array(
		'id' => null
	);
	private $request = array();
	private $error = array(
		'warning' => null,
		'error' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->user['id'] = $_SESSION['user']['id_usuario'];
		return;
	}

	public function load_data($page = null, $rpp = null){
		$query = "SELECT COUNT(*) FROM solicitudhotel WHERE id_usuario = :id_usuario AND mostrar_usuario != 0";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue('id_usuario', $this->user['id'], PDO::PARAM_INT);
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
			// Cargar las solicitudes
			$query = "SELECT s.id, h.nombre, s.mostrar_usuario, s.creado 
				FROM solicitudhotel s
				join hotel as h on s.id_hotel = h.id 
				WHERE mostrar_usuario != 0 AND id_usuario = :id_usuario
				ORDER BY s.creado DESC
				LIMIT :limit OFFSET :offset";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue('id_usuario', $this->user['id'], PDO::PARAM_INT);
				$stmt->bindValue(':limit', $this->pagination['rpp'], PDO::PARAM_INT);
				$stmt->bindValue(':offset', $this->pagination['offset'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->request[$row['id']] = array(
					'name' => $row['nombre'],
					'status' => $row['mostrar_usuario'],
					'created_at' => $row['creado']
				);
			}
			return $pagination;
		}
		return false;
	}

	public function get_requests(){
		$requests = null;
		$count = $this->pagination['total']-$this->pagination['offset'];
		foreach ($this->request as $key => $value) {
			switch ($value['status']) {
				case 1:
					$status = '<span class="label label-success mr5">Aceptada</span>';
					break;
				case 2:
					$status = '<span class="label label-warning mr5">Pendiente</span>';
					break;
				case 3:
					$status = '<span class="label label-info mr5">Corregir</span>';
					break;
				case 4:
					$status = '<span class="label label-danger mr5">Rechazada</span>';
					break;
				default:
					$status = '';
					break;
			}
			$date = date('d/m/Y g:i A', strtotime($value['created_at']));
			$name = _safe($value['name']);
			if($value['status'] == 3){
				$btn = '<button class="btn btn-danger btn-xs pull-right">Debe corregir la solicitud</button>';
			}else{
				$btn = '<button class="btn btn-primary btn-xs pull-right">Ver detalles</button>';
			}
			$requests .= 
				'<div class="background-white p20 mb30">
					<a href="'.HOST.'/socio/hotel/solicitud/'.$key.'">
						<label class="cert-date mr20">#'.$count.'</label>
						<span class="mr20">'.$status.'</span>
						<label class="cert-date mr20">'.$date.'</label>
						<label class="mr20">'.$name.'</label>
						'.$btn.'
					</a>
				</div>';
			$count--;
		}
		if($requests){
			$html = $requests;
		}else{
			$html = 
				'<div class="background-white p20 text-default">
					No has enviado ninguna solicitud. <a href="'.HOST.'/socio/hotel/afiliar-hotel">Enviar una</a>.
				</div>';
		}
		return $html;
	}

	public function get_count(){
		$i = $this->pagination['total'];
		if($i > 0){
			return 'He enviado '.$i.' solicitudes';
		}else{
			return 'Solicitudes enviadas para afiliar un hotel';
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
		file_put_contents(ROOT.'\assets\error_logs\user_business_requests.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>