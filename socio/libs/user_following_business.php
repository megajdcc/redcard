<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace socio\libs;
use assets\libs\connection;
use PDO;

class user_following_business {
	private $con;
	private $user = array(
		'id' => null,
		'following' => array()
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
		$query = "SELECT COUNT(*) FROM seguir_negocio WHERE id_usuario = :id_usuario";
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
			$query = "SELECT sn.id_negocio, n.nombre, n.comision, n.url, nc.categoria, np.preferencia
				FROM seguir_negocio sn 
				INNER JOIN negocio n ON sn.id_negocio = n.id_negocio 
				INNER JOIN negocio_categoria nc ON n.id_categoria = nc.id_categoria 
				INNER JOIN negocio_preferencia np ON n.id_negocio = np.id_negocio
				INNER JOIN preferencia p ON np.id_preferencia = p.id_preferencia AND p.llave = 'business_header'
				WHERE id_usuario = :id_usuario
				ORDER BY sn.creado DESC 
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
				$this->user['following'][$row['id_negocio']] = array(
					'name' => $row['nombre'],
					'url' => $row['url'],
					'commission' => $row['comision'],
					'category' => $row['categoria'],
					'image' => $row['preferencia']
				);
			}
			return $pagination;
		}
		return false;
	}

	public function get_businesses(){
		$html = null;
		foreach ($this->user['following'] as $key => $value) {
			$name = _safe($value['name']);
			$url = HOST.'/'._safe($value['url']);
			$commission = _safe($value['commission']).'%';
			$category = _safe($value['category']);
			$image = HOST.'/assets/img/business/header/'._safe($value['image']);
			if(array_key_exists($key,$_SESSION['user']['follow_business'])){
				$buttons = '<div class="fa fa-bookmark-o marked following-btn" data-id="'.$key.'" data-function="del"></div>';
			}else{
				$buttons = '<div class="fa fa-bookmark-o following-btn" data-id="'.$key.'" data-function="add"></div>';
			}
			$buttons .= '<a href="'.$url.'" class="fa fa-search"></a>';
			if(array_key_exists($key,$_SESSION['user']['recommend_business'])){
				$buttons .= '<div class="fa fa-heart-o marked recommend-btn" data-id="'.$key.'" data-function="del"></div>';
			}else{
				$buttons .= '<div class="fa fa-heart-o recommend-btn" data-id="'.$key.'" data-function="add"></div>';
			}
			$html .= 
			'<div class="col-sm-6 col-md-4">
				<div class="card-simple" data-background-image="'.$image.'">
					<div class="card-simple-background">
						<div class="card-simple-content">
							<h2><a href="'.$url.'">'.$name.'</a></h2>
							<div class="card-simple-rating">
								'.$this->get_rating_stars($this->get_average_rating($key)).'
							</div><!-- /.card-rating -->
							<div class="card-simple-actions">
								'.$buttons.'
							</div><!-- /.card-simple-actions -->
						</div><!-- /.card-simple-content -->
						<div class="card-simple-label">'.$category.'</div>
						<div class="card-simple-price">'.$commission.'</div>
					</div><!-- /.card-simple-background -->
				</div><!-- /.card-simple -->
			</div><!-- /.col-* -->';
		}
		if(is_null($html)){
			$html = '<div class="background-white p20 text-default">No estoy siguiendo a ning&uacute;n negocio.</div>';
		}
		return $html;
	}

	public function get_count(){
		$i = $this->pagination['total'];
		if($i > 0){
			return 'Sigo a '.$i.' negocios';
		}else{
			return 'Negocios que sigo';
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
		file_put_contents(ROOT.'\assets\error_logs\user_following_business.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>