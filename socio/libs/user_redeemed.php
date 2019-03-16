<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace socio\libs;
use assets\libs\connection;
use PDO;

class user_redeemed {
	private $con;
	private $user = array(
		'id' => null,
		'redeemed' => array()
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
		$query = "SELECT COUNT(*) FROM usar_certificado WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
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
			$query = "SELECT uc.id_uso, uc.creado, nc.nombre, nc.url, nc.imagen
				FROM usar_certificado uc
				INNER JOIN negocio_certificado nc ON uc.id_certificado = nc.id_certificado
				WHERE uc.id_usuario = :id_usuario
				ORDER BY uc.creado DESC 
				LIMIT :limit OFFSET :offset";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
				$stmt->bindValue(':limit', $this->pagination['rpp'], PDO::PARAM_INT);
				$stmt->bindValue(':offset', $this->pagination['offset'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->user['redeemed'][$row['id_uso']] = array(
					'created_at' => $row['creado'],
					'name' => $row['nombre'],
					'url' => $row['url'],
					'image' => $row['imagen']
				);
			}
			return $pagination;
		}
		return false;
	}

	public function get_redeemed(){
		$html = null;
		foreach ($this->user['redeemed'] as $key => $value) {
			$date = date('d/m/Y \a \l\a\s g:i A', strtotime($value['created_at']));
			$url = HOST.'/certificado/'._safe($value['url']);
			$name = _safe($value['name']);
			$image = HOST.'/assets/img/business/certificate/'._safe($value['image']);

			$html .= 
			'<div class="background-white p20 mb30 detail-content">
				<a href="'.$url.'" target="_blank" class="text-left">
					<img class="cert-img" src="'.$image.'" alt="'.$name.'" title="'.$name.'">
					'.$name.'
				</a>
				<div class="cert-date pull-right">'.$date.'</div>
			</div>';
		}
		if(is_null($html)){
			$html = '<div class="background-white p20 text-default">No has canjeado ning&uacute;n certificado de regalo.</div>';
		}
		return $html;
	}

	public function get_count(){
		$i = $this->pagination['total'];
		if($i > 0){
			return 'He canjeado '.$i.' certificados de regalo';
		}else{
			return 'Mis certificados de regalo canjeados';
		}
	}

	private function get_average_rating($id){
		$average = $service = $product = $ambient = 0;
		$query = "SELECT calificacion_servicio, calificacion_producto, calificacion_ambiente FROM opinion o 
			INNER JOIN negocio_venta nv ON o.id_venta = nv.id_venta 
			INNER JOIN negocio n ON nv.id_negocio = n.id_negocio
			WHERE n.id_negocio = :id_negocio";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $id, PDO::PARAM_INT);
			$stmt->execute();
			$count = $stmt->rowCount();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$service += $row['calificacion_servicio'];
			$product += $row['calificacion_producto'];
			$ambient += $row['calificacion_ambiente'];
		}
		if($count != 0){
			$average = ($service + $product + $ambient) / ($count * 3);
		}
		return $average;
	}

	private function get_rating_stars($rating){
		$rating = round($rating*2)/2;
		$stars = null;
		for ($i = 0; $i < 5; $i++){
			if($i < $rating){
				if($rating == $i + 0.5){
					$stars .= '<i class="fa fa-star-half-o"></i> ';
				}else{
					$stars .= '<i class="fa fa-star"></i> ';
				}
			}else{
				$stars .= '<i class="fa fa-star-o"></i> ';
			}
		}
		return $stars;
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
		file_put_contents(ROOT.'\assets\error_logs\user_redeemed.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>