<?php 
namespace socio\libs;
use assets\libs\connection;
use PDO;

/**
 * @author Crespo Jhonatan
 * @correo jhonatancrespo@megajdcc.com.ve
 */
class user_hotel {

	private $con;
	private $user = array(
		'id' => null,
		'business' => array()
	);
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
		$query = "SELECT COUNT(*) FROM negocio_empleado as ne join negocio as n on ne.id_negocio = n.id_negocio 
					join negocio_categoria as nc on n.id_categoria = nc.id_categoria
					WHERE nc.categoria = 'Hotel' && id_empleado = :id_empleado";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_empleado', $this->user['id'], PDO::PARAM_INT);
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
			$query = "SELECT ne.id_negocio, n.nombre, n.url 
				FROM negocio_empleado ne
				INNER JOIN negocio n ON ne.id_negocio = n.id_negocio
				join negocio_categoria as nc on n.id_categoria = nc.id_categoria
				WHERE ne.id_empleado = :id_empleado && nc.categoria = 'Hotel' 
				ORDER BY ne.creado ASC 
				LIMIT :limit OFFSET :offset";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_empleado', $this->user['id'], PDO::PARAM_INT);
				$stmt->bindValue(':limit', $this->pagination['rpp'], PDO::PARAM_INT);
				$stmt->bindValue(':offset', $this->pagination['offset'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->user['hotel'][$row['id_negocio']] = array(
					'name' => $row['nombre'],
					'url' => $row['url']
				);
			}
			return $pagination;
		}
		return false;
	}

	public function get_hoteles(){
		$html = null;
		foreach ($this->user['hotel'] as $key => $value) {
			$name = _safe($value['name']);
			$url = _safe($value['url']);
			$html .= 
			'<div class="background-white p20 mb30">
				<form method="post" action="'.HOST.'/negocio/">
					<a href="'.HOST.'/'.$url.'" target="_blank">'.$name.'</a>
					<input type="hidden" value="'.$key.'" name="change_business">
					<button class="btn btn-xs btn-primary pull-right" type="submit">Ir al panel de hotel</button>
				</form>
			</div>';
		}
		if(is_null($html)){
			$html = '<div class="background-white p20 text-default">No eres miembro de ning&uacute;n hotel.</div>';
		}
		return $html;
	}

	public function get_count(){
		$i = $this->pagination['total'];
		if($i > 0){
			return 'Soy miembro de '.$i.' hoteles';
		}else{
			return 'Soy miembro de estos hoteles';
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
		file_put_contents(ROOT.'\assets\error_logs\user_businesses.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>