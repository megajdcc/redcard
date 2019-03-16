<?php # Desarrollado por Mario Sacramento. info@infochannel.si
namespace assets\libs;
use PDO;

class index_load {
	private $con;
	private $user = false;
	private $categories = array(
		1 => 'restaurant',
		2 => 'bar',
		3 => 'shopping',
		4 => 'health',
		5 => 'leizure',
		6 => 'hotel',
		7 => 'tour',
		8 => 'specialist',
		9 => 'others'
	);
	private $businesses = array();
	private $certificates = array();
	private $events = array();

	public function __construct(connection $con){
		$this->con = $con->con;
		if(isset($_SESSION['user']['id_usuario'])){
			$this->user = true;
		}
		$this->load_businesses();
		$this->load_certificates();
		$this->load_events();
		return;
	}

	private function load_businesses(){
		$query = "SELECT n.id_negocio, n.nombre, n.comision, n.id_categoria, nc.categoria, n.url, np.preferencia as imagen
			FROM negocio n 
			INNER JOIN negocio_categoria nc ON n.id_categoria = nc.id_categoria
			INNER JOIN preferencia p ON p.llave = 'business_header'
			INNER JOIN negocio_preferencia np ON n.id_negocio = np.id_negocio AND np.id_preferencia = p.id_preferencia
			WHERE n.situacion = 1
			ORDER BY n.comision DESC LIMIT 12";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->businesses[] = $row;
		}
		return;
	}

	private function load_certificates(){
		$now = date('Y/m/d H:i:s',time());
		$query = "SELECT ne.id_certificado, ne.nombre, ne.url, ne.disponibles, (SELECT COUNT(id_uso) FROM usar_certificado uc WHERE ne.id_certificado = uc.id_certificado AND uc.situacion != 0) as usados, ne.precio, ne.iso, ne.imagen, n.id_negocio, n.url as b_url, n.id_categoria
			FROM negocio_certificado ne 
			INNER JOIN negocio n ON ne.id_negocio = n.id_negocio
			WHERE ne.situacion = 1 AND ne.fecha_inicio < :now1 AND ne.fecha_fin > :now2
			ORDER BY ne.fecha_inicio ASC LIMIT 12";
		$params = array(':now1' => $now, ':now2' => $now);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->certificates[] = $row;
		}
		return;
	}

	private function load_events(){
		$now = date('Y/m/d H:i:s', time());
		$elapsed = date('Y/m/d H:i:s', strtotime('-3 day'));
		$query = "SELECT ne.titulo, ne.fecha_inicio, ne.fecha_fin, ne.imagen, n.id_negocio, n.url, n.id_categoria 
			FROM negocio_evento ne
			INNER JOIN negocio n ON ne.id_negocio = n.id_negocio
			WHERE ne.situacion = 1 AND ne.fecha_inicio > :last_week AND ne.fecha_fin > :now
			ORDER BY ne.fecha_inicio ASC LIMIT 12";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':now', $now, PDO::PARAM_STR);
			$stmt->bindValue(':last_week', $elapsed, PDO::PARAM_STR);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->events[] = $row;
		}
		return;
	}

	public function get_businesses(){
		$html = null;
		foreach ($this->businesses as $key => $value) {
			$id = $value['id_negocio'];
			$name = _safe($value['nombre']);
			$commission = _safe($value['comision'].'%');
			$category = _safe($value['categoria']);
			$url = HOST.'/'._safe($value['url']);
			$image = HOST.'/assets/img/business/header/'._safe($value['imagen']);
			$class = $this->categories[$value['id_categoria']];
			$score = $this->get_average_score($id);
			$stars = $this->get_rating_stars($score);
			if($this->user){
				if(array_key_exists($id,$_SESSION['user']['follow_business'])){
					$buttons = '<div class="fa fa-bookmark-o marked following-btn" data-id="'.$id.'" data-function="del"></div>';
				}else{
					$buttons = '<div class="fa fa-bookmark-o following-btn" data-id="'.$id.'" data-function="add"></div>';
				}
				$buttons .= '<a href="'.$url.'" class="fa fa-search"></a>';
				if(array_key_exists($id,$_SESSION['user']['recommend_business'])){
					$buttons .= '<div class="fa fa-heart-o marked recommend-btn" data-id="'.$id.'" data-function="del"></div>';
				}else{
					$buttons .= '<div class="fa fa-heart-o recommend-btn" data-id="'.$id.'" data-function="add"></div>';
				}
			}else{
				$buttons = 
				'<a href="'.HOST.'/login" class="fa fa-bookmark-o"></a>
				<a href="'.$url.'" class="fa fa-search"></a>
				<a href="'.HOST.'/login" class="fa fa-heart-o"></a>';
			}
			$html .= 
			'<div class="col-sm-6 col-md-3">
				<div class="card-simple" data-background-image="'.$image.'">
					<div class="card-simple-background">
						<div class="card-simple-content">
							<h2><a href="'.$url.'">'.$name.'</a></h2>
							<div class="card-simple-rating">
								'.$stars.'
							</div><!-- /.card-rating -->
							<div class="card-simple-actions">
								'.$buttons.'
							</div><!-- /.card-simple-actions -->
						</div><!-- /.card-simple-content -->
						<div class="card-simple-label '.$class.'">'.$category.'</div>
						<div class="card-simple-price">'.$commission.'</div>
					</div><!-- /.card-simple-background -->
				</div><!-- /.card-simple -->
			</div><!-- /.col-* -->';
		}
		return $html;
	}

	public function get_certificates(){
		$html = null;
		foreach ($this->certificates as $key => $value) {
			$id = $value['id_certificado'];
			$name = _safe($value['nombre']);
			$url = HOST.'/certificado/'._safe($value['url']);
			$available = _safe($value['disponibles']);
			$used = _safe($value['usados']);
			$left = $available - $used;
			$left = 'Quedan '.$left;
			$price = number_format((float)$value['precio'], 2, '.', '').' '.$value['iso'];
			$image = HOST.'/assets/img/business/certificate/'._safe($value['imagen']);
			$b_url = HOST.'/'._safe($value['b_url']);
			$class = $this->categories[$value['id_categoria']];
			$score = $this->get_average_score($value['id_negocio']);
			$stars = $this->get_rating_stars($score);
			if($this->user){
				if(array_key_exists($id,$_SESSION['user']['certificate_wishlist'])){
					$wishlist = '<div class="fa fa-heart-o marked cert-wishlist" data-id="'.$id.'" data-function="del"></div>';
				}else{
					$wishlist = '<div class="fa fa-heart-o cert-wishlist" data-id="'.$id.'" data-function="add"></div>';
				}
			}else{
				$wishlist = '<a href="'.HOST.'/login" class="fa fa-heart-o"></a>';
			}
			$html .= 
			'<div class="col-sm-6 col-md-3">
				<div class="card-simple" data-background-image="'.$image.'">
					<div class="card-simple-background">
						<div class="card-simple-content">
							<h2><a href="'.$url.'">'.$name.'</a></h2>
							<div class="card-simple-rating">
								'.$stars.'
							</div><!-- /.card-rating -->
							<div class="card-simple-actions">
								<a href="'.$b_url.'" class="fa fa-briefcase"></a>
								<a href="'.$url.'" class="fa fa-search"></a>
								'.$wishlist.'
							</div><!-- /.card-simple-actions -->
						</div><!-- /.card-simple-content -->
						<div class="card-simple-label '.$class.'">'.$left.'</div>
						<div class="card-simple-price">'.$price.'</div>
					</div><!-- /.card-simple-background -->
				</div><!-- /.card-simple -->
			</div><!-- /.col-* -->';
		}
		return $html;
	}

	public function get_events(){
		$html = null;
		foreach ($this->events as $key => $value) {
			$name = _safe($value['titulo']);
			$date_start = date('d/m/Y', strtotime($value['fecha_inicio']));
			$date_end = date('d/m/Y', strtotime($value['fecha_fin']));
			$url = HOST.'/'._safe($value['url']).'/eventos';
			$b_url = HOST.'/'._safe($value['url']);
			$image = HOST.'/assets/img/business/event/'._safe($value['imagen']);
			$class = $this->categories[$value['id_categoria']];
			$score = $this->get_average_score($value['id_negocio']);
			$stars = $this->get_rating_stars($score);
			$html .= 
			'<div class="col-sm-6 col-md-3">
				<div class="card-simple" data-background-image="'.$image.'">
					<div class="card-simple-background">
						<div class="card-simple-content">
							<h2><a href="'.$url.'">'.$name.'</a></h2>
							<div class="card-simple-rating">
								'.$stars.'
							</div><!-- /.card-rating -->
							<div class="card-simple-actions">
								<a href="'.$url.'" class="fa fa-search"></a>
							</div><!-- /.card-simple-actions -->
						</div><!-- /.card-simple-content -->
						<div class="card-simple-label '.$class.'">Del '.$date_start.'</div>
						<div class="card-simple-price '.$class.'">Al '.$date_end.'</div>
					</div><!-- /.card-simple-background -->
				</div><!-- /.card-simple -->
			</div><!-- /.col-* -->';
		}
		return $html;
	}

	public function load_categories(){
		$html = null;
		$icons = array(
			1 => 'fa-cutlery',
			2 => 'fa-beer',
			3 => 'fa-shopping-bag',
			4 => 'fa-heart-o',
			5 => 'fa-futbol-o',
			6 => 'fa-bed',
			7 => 'fa-map-o',
			8 => 'fa-briefcase',
			9 => 'fa-flag'
		);
		$query = "SELECT id_categoria, categoria FROM negocio_categoria";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$html .= 
			'<div class="col-sm-4">
				<div class="box">
					<div class="box-icon">
						<i class="fa '.$icons[$row['id_categoria']].'"></i>
					</div><!-- /.box-icon -->
					<div class="box-content">
						<h2>'._safe($row['categoria']).'</h2>
						<a href="'.HOST.'/listados?tipo=1&categoria='.$row['id_categoria'].'">Ver negocios <i class="fa fa-chevron-right"></i></a>
					</div><!-- /.box-content -->
				</div>
			</div><!-- /.col-sm-4 -->';
		}
		return $html;
	}

	private function get_average_score($id){
		$average = $service = $product = $ambient = 0;
		$query = "SELECT o.calificacion_servicio, o.calificacion_producto, o.calificacion_ambiente 
			FROM opinion o 
			INNER JOIN negocio_venta nv ON o.id_venta = nv.id_venta
			WHERE nv.id_negocio = :id_negocio";
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
		if($count > 0){
			$average = ($service + $product + $ambient) / ($count * 3);
		}
		return $average;
	}

	private function get_rating_stars($score){
		$score = round($score * 2) / 2;
		$html = null;
		for ($i = 0; $i < 5; $i++){
			if($i < $score){
				if($score == $i + 0.5){
					$html .= '<i class="fa fa-star-half-o"></i> ';
				}else{
					$html .= '<i class="fa fa-star"></i> ';
				}
			}else{
				$html .= '<i class="fa fa-star-o"></i> ';
			}
		}
		return $html;
	}

	public function get_notification(){
		$html = null;
		if(isset($_SESSION['notification']['success'])){
			$html .= 
			'<div class="alert alert-success alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<i class="fa fa-check-circle"></i>
				'._safe($_SESSION['notification']['success']).'
			</div>';
			unset($_SESSION['notification']['success']);
		}
		if(isset($_SESSION['notification']['info'])){
			$html .= 
			'<div class="alert alert-info alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<i class="fa fa-exclamation-circle"></i>
				'._safe($_SESSION['notification']['info']).'
			</div>';
			unset($_SESSION['notification']['info']);
		}
		if($this->error['warning']){
			$html .= 
			'<div class="alert alert-warning alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<i class="fa fa-exclamation-triangle"></i>
				'._safe($this->error['warning']).'
			</div>';
		}
		if($this->error['error']){
			$html .= 
			'<div class="alert alert-danger alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<i class="fa fa-exclamation-circle"></i>
				'._safe($this->error['error']).'
			</div>';
		}
		return $html;
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\index_load.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>