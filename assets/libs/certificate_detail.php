<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace assets\libs;
use PDO;

class certificate_detail {
	private $con;
	private $certificate = array();
	private $business = array();
	private $error = array(
		'warning' => null,
		'error' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		return;
	}

	public function load_data($url = null){
		if(empty($url)){
			return false;
		}else{
			$url = _safe($url);
		}
		$query = "SELECT ne.fecha_inicio, ne.fecha_fin, ne.disponibles, 
			ne.disponibles-(SELECT COUNT(*) FROM usar_certificado uc WHERE ne.id_certificado = uc.id_certificado AND uc.situacion != 0) as restantes, 
			ne.precio, ne.iso, ne.imagen, ne.nombre, ne.descripcion, ne.condiciones, ne.restricciones, ne.situacion, ne.creado, n.nombre as nombre_n, n.url, nc.categoria, c.ciudad, p.pais
			FROM negocio_certificado ne 
			INNER JOIN negocio n ON ne.id_negocio = n.id_negocio 
			INNER JOIN negocio_categoria nc ON n.id_categoria = nc.id_categoria 
			INNER JOIN ciudad c ON n.id_ciudad = c.id_ciudad 
			INNER JOIN estado e ON c.id_estado = e.id_estado 
			INNER JOIN pais p ON e.id_pais = p.id_pais 
			WHERE ne.url = :url
			ORDER BY ne.creado DESC LIMIT 1";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':url', $url, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->certificate['start_date'] = $row['fecha_inicio'];
			$this->certificate['end_date'] = $row['fecha_fin'];
			$this->certificate['available'] = $row['disponibles'];
			$this->certificate['valued'] = $row['precio'];
			$this->certificate['left'] = $row['restantes'];
			$this->certificate['iso'] = $row['iso'];
			$this->certificate['image'] = $row['imagen'];
			$this->certificate['name'] = $row['nombre'];
			$this->certificate['description'] = $row['descripcion'];
			$this->certificate['condiciones'] = $row['condiciones'];
			$this->certificate['restriction'] = $row['restricciones'];
			$this->certificate['status'] = $row['situacion'];
			$this->certificate['created'] = $row['creado'];
			$this->certificate['city'] = $row['ciudad'];
			$this->certificate['country'] = $row['pais'];
			$this->business['name'] = $row['nombre_n'];
			$this->business['url'] = $row['url'];
			$this->business['category'] = $row['categoria'];
			return true;
		}
		return false;
	}

	public function get_dates(){
		$start = date('d/m/Y h:i A', strtotime($this->certificate['start_date']));
		$end = date('d/m/Y h:i A', strtotime($this->certificate['end_date']));
		$date = 'Inicia: '.$start.' &amp; Termina: '.$end;
		return $date;
	}

	public function get_available(){
		return _safe($this->certificate['left']).' / '._safe($this->certificate['available']);
	}

	public function get_value(){
		$price = number_format((float)$this->certificate['valued'], 2, '.', '');
		return $price.' '.$this->certificate['iso'];
	}

	public function get_image(){
		$image = _safe($this->certificate['image']);
		$html = 
			'<a href="'.HOST.'/assets/img/business/certificate/'.$image.'">
				<img class="img-thumbnail img-rounded" src="'.HOST.'/assets/img/business/certificate/'.$image.'">
			</a>';
		return $html;
	}

	public function get_business_link(){
		return '<a href="'.$this->get_business_url().'" target="_blank">'.$this->get_business_name().'</a>';
	}

	public function get_location(){
		return _safe($this->certificate['city'].', '.$this->certificate['country']);
	}

	public function get_status(){
		$start = strtotime($this->certificate['start_date']);
		$end = strtotime($this->certificate['end_date']);
		$now = time();
		switch ($this->certificate['status']) {
				case 1:
					if($start > $now){
						$status = '<span class="label btn-xs label-info pull-left mr20">Proximamente</span>';
						}elseif($end > $now){
							$status = '<span class="label btn-xs label-success pull-left mr20">Disponible</span>';
						}else{
							$status = '<span class="label btn-xs label-secondary pull-left mr20">Expirado</span>';
						}
					break;
				case 2:
					$status = '<span class="label btn-xs label-primary pull-left mr20">Terminados</span>';
					break;
				case 3:
					$status = '<span class="label btn-xs label-danger pull-left mr20">Cancelado</span>';
					break;
				default:
					$status = '';
					break;
			}
		return $status;
	}

	public function get_name(){
		return _safe($this->certificate['name']);
	}

	public function get_description(){
		return nl2br(_safe($this->certificate['description']));
	}

	public function get_condition(){
		if(!empty($this->certificate['condition'])){
			return nl2br(_safe($this->certificate['condition']));
		}else{
			return 'No tiene.';
		}
	}

	public function get_restriction(){
		if(!empty($this->certificate['restriction'])){
			return nl2br(_safe($this->certificate['restriction']));
		}else{
			return 'No tiene.';
		}
	}

	public function get_business_url(){
		return HOST.'/'._safe($this->business['url']);
	}

	public function get_business_name(){
		return _safe($this->business['name']);
	}

	public function get_business_category(){
		return _safe($this->business['category']);
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
		file_put_contents(ROOT.'\assets\error_logs\certificate_detail.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>