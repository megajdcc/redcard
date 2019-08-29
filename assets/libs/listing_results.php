<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace assets\libs;
use PDO;

class listing_results {
	private $con;
	private $user = false;
	private $type = array(
		1 => array(
			'name' => 'Negocios',
			'table' => 'negocio n',
			'select' => 'n.id_negocio, n.nombre, n.comision, n.id_categoria, nc.categoria, n.url, np.preferencia as imagen, n.situacion',
			'joins' => 
				"INNER JOIN negocio_categoria nc ON n.id_categoria = nc.id_categoria
				INNER JOIN preferencia p ON p.llave = 'business_header'
				INNER JOIN negocio_preferencia np ON n.id_negocio = np.id_negocio AND np.id_preferencia = p.id_preferencia",
			'where' => '(n.situacion = 1 OR n.situacion = 3)',
			'category_filter' => 'AND n.id_categoria = :category_filter',
			'location_filter' => 'AND n.id_ciudad = :location_filter',
			'keyword_filter' => 'AND (n.nombre LIKE :search1 OR n.breve LIKE :search2 OR n.descripcion LIKE :search3)',
			'order' => 'n.comision DESC'
		),
		2 => array(
			'name' => 'Certificados de Regalo',
			'table' => 'negocio_certificado ne INNER JOIN negocio n ON ne.id_negocio = n.id_negocio',
			'select' => 'ne.id_certificado, ne.nombre, ne.url, ne.disponibles, (SELECT COUNT(id_uso) FROM usar_certificado uc WHERE ne.id_certificado = uc.id_certificado AND uc.situacion != 0) as usados, ne.precio, ne.iso, ne.imagen, n.id_negocio, n.url as b_url, n.id_categoria',
			'joins' => '',
			'where' => 'ne.situacion = 1 AND ne.fecha_inicio < :now1 AND ne.fecha_fin > :now2',
			'category_filter' => 'AND n.id_categoria = :category_filter',
			'location_filter' => 'AND n.id_ciudad = :location_filter',
			'keyword_filter' => 'AND ne.nombre LIKE :search1',
			'order' => 'n.comision DESC'
		),
		3 => array(
			'name' => 'Eventos',
			'table' => 'negocio_evento ne INNER JOIN negocio n ON ne.id_negocio = n.id_negocio',
			'select' => 'ne.titulo, ne.fecha_inicio, ne.fecha_fin, ne.imagen, n.id_negocio, n.url, n.id_categoria',
			'joins' => '',
			'where' => 'ne.situacion = 1 AND ne.fecha_fin > :now1',
			'category_filter' => 'AND n.id_categoria = :category_filter',
			'location_filter' => 'AND n.id_ciudad = :location_filter',
			'keyword_filter' => 'AND (ne.titulo LIKE :search1 OR ne.contenido LIKE :search2)',
			'order' => 'n.comision DESC'
		)
	);
	private $categories = array();
	private $cities = array();
	private $countries = array();
	private $filter = array(
		'type' => null,
		'category' => null,
		'location' => null,
		'keyword' => null,
		'country' => null,
		'state' => null
	);
	private $results = array();
	private $category_classes = array(
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
	private $pagination = array(
		'total' => null,
		'rpp' => null,
		'max' => null,
		'page' => null,
		'offset' => null
	);
	private $error = array(
		'warning' => null,
		'error' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		if(isset($_SESSION['user']['id_usuario'])){
			$this->user = true;
		}
		$this->load_categories();
		$this->load_countries();
		return;
	}

	private function load_categories(){
		$query = "SELECT id_categoria, categoria FROM negocio_categoria";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->categories[$row['id_categoria']] = $row['categoria'];
		}
		return;
	}

	private function load_countries(){
		$query = "SELECT id_pais, pais FROM pais";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->countries[$row['id_pais']] = $row['pais'];
		}
		return;
	}

	public function load_data($page = null, $rpp = null, array $filter){
		// FILTRO DE TIPO (TABLA)
		if($filter['type'] && array_key_exists($filter['type'], $this->type)){
			$this->filter['type'] = $filter['type'];
		}else{
			$this->filter['type'] = 1;
		}
		$table = $this->type[$this->filter['type']]['table'];
		$select = $this->type[$this->filter['type']]['select'];
		$joins = $this->type[$this->filter['type']]['joins'];
		$where = $this->type[$this->filter['type']]['where'];
		$order = $this->type[$this->filter['type']]['order'];
		// FILTRO DE CATEGORIA
		if($filter['category'] && array_key_exists($filter['category'], $this->categories)){
			$this->filter['category'] = $filter['category'];
			$category_filter = $this->type[$this->filter['type']]['category_filter'];
		}else{
			$category_filter = null;
		}
		// FILTRO DE CIUDAD
		if($filter['location']){
			$query = "SELECT e.id_estado, p.id_pais FROM ciudad c INNER JOIN estado e ON c.id_estado = e.id_estado INNER JOIN pais p ON e.id_pais = p.id_pais WHERE c.id_ciudad = :id_ciudad";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_ciudad', $filter['location'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				if(array_key_exists($row['id_pais'], $this->countries)){
					$this->filter['location'] = $filter['location'];
					$location_filter = $this->type[$this->filter['type']]['location_filter'];
					$this->filter['country'] = $row['id_pais'];
					$this->filter['state'] = $row['id_estado'];
				}else{
					$location_filter = null;
				}
			}else{
				$location_filter = null;
			}
		}else{
			$location_filter = null;
		}
		// FILTRO DE PALABRA CLAVE
		if($filter['keyword']){
			$this->filter['keyword'] = $filter['keyword'];
			$keyword_filter = $this->type[$this->filter['type']]['keyword_filter'];
		}else{
			$keyword_filter = null;
		}
		// PAGINACION
		$query = "SELECT COUNT(*) FROM $table WHERE $where $category_filter $location_filter $keyword_filter";
		try{
			$stmt = $this->con->prepare($query);
			if($this->filter['type'] == 2 || $this->filter['type'] == 3){
				$now = date('Y/m/d H:i:s',time());
				$stmt->bindValue(':now1', $now, PDO::PARAM_STR);
				if($this->filter['type'] == 2){
					$stmt->bindValue(':now2', $now, PDO::PARAM_STR);
				}
			}
			if($this->filter['category']){
				$stmt->bindValue(':category_filter', $this->filter['category'], PDO::PARAM_INT);
			}
			if($this->filter['location']){
				$stmt->bindValue(':location_filter', $this->filter['location'], PDO::PARAM_INT);
			}
			if($this->filter['keyword']){
				$stmt->bindValue(':search1', '%'.$this->filter['keyword'].'%', PDO::PARAM_STR);
				if($this->filter['type'] == 1 || $this->filter['type'] == 3){
					$stmt->bindValue(':search2', '%'.$this->filter['keyword'].'%', PDO::PARAM_STR);
				}
				if($this->filter['type'] == 1){
					$stmt->bindValue(':search3', '%'.$this->filter['keyword'].'%', PDO::PARAM_STR);
				}
			}
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
			// VARIABLES RETORNABLES
			$pagination['page'] = $this->pagination['page'];
			$pagination['total'] = $this->pagination['total'];
			$pagination['rpp'] = $this->pagination['rpp'];
			if($this->pagination['total'] > 0){
				$start = $this->pagination['offset'] + 1;
				$end = min(($this->pagination['offset'] + $this->pagination['rpp']), $this->pagination['total']);
				if($this->pagination['max'] == 1){ $pg = 'P&aacutegina'; }else{ $pg = 'P&aacute;ginas';}
				$pagination['count'] = 'Viendo del '.$start.' al '.$end.' de un total de '.$this->pagination['total'].' resultados. ('.$this->pagination['max'].' '.$pg.').';
			}else{
				$pagination['count'] = '';
			}
			// CARGAR LOS RESULTADOS
			$query = "SELECT $select FROM $table $joins WHERE $where $category_filter $location_filter $keyword_filter
				ORDER BY $order
				LIMIT :limit OFFSET :offset";
			try{
				$stmt = $this->con->prepare($query);
				if($this->filter['type'] == 2 || $this->filter['type'] == 3){
					$now = date('Y/m/d H:i:s',time());
					$stmt->bindValue(':now1', $now, PDO::PARAM_STR);
					if($this->filter['type'] == 2){
						$stmt->bindValue(':now2', $now, PDO::PARAM_STR);
					}
				}
				if($this->filter['category']){
					$stmt->bindValue(':category_filter', $this->filter['category'], PDO::PARAM_INT);
				}
				if($this->filter['location']){
					$stmt->bindValue(':location_filter', $this->filter['location'], PDO::PARAM_INT);
				}
				if($this->filter['keyword']){
					$stmt->bindValue(':search1', '%'.$this->filter['keyword'].'%', PDO::PARAM_STR);
					if($this->filter['type'] == 1 || $this->filter['type'] == 3){
						$stmt->bindValue(':search2', '%'.$this->filter['keyword'].'%', PDO::PARAM_STR);
					}
					if($this->filter['type'] == 1){
						$stmt->bindValue(':search3', '%'.$this->filter['keyword'].'%', PDO::PARAM_STR);
					}
				}
				$stmt->bindValue(':limit', $this->pagination['rpp'], PDO::PARAM_INT);
				$stmt->bindValue(':offset', $this->pagination['offset'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->results[] = $row;
			}
			return $pagination;
		}
		return false;
	}

	public function get_results(){
		$html = null;
		switch ($this->filter['type']) {
			case 1:
				foreach ($this->results as $key => $value) {
					$id = $value['id_negocio'];
					$name = _safe($value['nombre']);
					if($value['situacion'] == 3){
						$commission = 'Cerrado por temporada';
						$closed = 'detail-tag';
					}else{
						$commission = _safe($value['comision'].'%');
						$closed = '';
					}
					$category = _safe($value['categoria']);
					$url = HOST.'/'._safe($value['url']);
					$image = HOST.'/assets/img/business/header/'._safe($value['imagen']);
					$class = $this->category_classes[$value['id_categoria']];
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
								<div class="card-simple-price '.$closed.'">'.$commission.'</div>
							</div><!-- /.card-simple-background -->
						</div><!-- /.card-simple -->
					</div><!-- /.col-* -->';
				}
				break;
			case 2:
				foreach ($this->results as $key => $value) {
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
					$class = $this->category_classes[$value['id_categoria']];
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
				break;
			case 3:
				foreach ($this->results as $key => $value) {
					$name = _safe($value['titulo']);
					$date_start = date('d/m/Y', strtotime($value['fecha_inicio']));
					$date_end = date('d/m/Y', strtotime($value['fecha_fin']));
					$url = HOST.'/'._safe($value['url']).'/eventos';
					$b_url = HOST.'/'._safe($value['url']);
					$image = HOST.'/assets/img/business/event/'._safe($value['imagen']);
					$class = $this->category_classes[$value['id_categoria']];
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
				break;
			default:
				# code...
				break;
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

	public function get_keyword(){
		return _safe($this->filter['keyword']);
	}

	public function get_types(){
		$html = null;
		foreach($this->type as $key => $value){
			$value = _safe($value['name']);
			if($this->filter['type'] == $key){
				$html .= '<option value="'.$key.'" selected>'.$value.'</option>';
			}else{
				$html .= '<option value="'.$key.'">'.$value.'</option>';
			}
		}
		return $html;
	}

	public function get_categories(){
		$html = null;
		foreach($this->categories as $key => $value){
			$value = _safe($value);
			if($this->filter['category'] == $key){
				$html .= '<option value="'.$key.'" selected>'.$value.'</option>';
			}else{
				$html .= '<option value="'.$key.'">'.$value.'</option>';
			}
		}
		return $html;
	}

	public function get_countries(){
		$html = null;
		foreach($this->countries as $key => $value){
			$value = _safe($value);
			if($this->filter['country'] == $key){
				$html .= '<option value="'.$key.'" selected>'.$value.'</option>';
			}else{
				$html .= '<option value="'.$key.'">'.$value.'</option>';
			}
		}
		return $html;
	}

	public function get_states(){
		$html = null;
		if($this->filter['country']){
			$query = "SELECT id_estado, estado FROM estado WHERE id_pais = :id_pais";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_pais', $this->filter['country'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$value = _safe($row['estado']);
				if($this->filter['state'] == $row['id_estado']){
					$html .= '<option value="'.$row['id_estado'].'" selected>'.$value.'</option>';
				}else{
					$html .= '<option value="'.$row['id_estado'].'">'.$value.'</option>';
				}
			}
		}
		return $html;
	}

	public function get_cities(){
		$html = null;
		if($this->filter['state']){
			$query = "SELECT id_ciudad, ciudad FROM ciudad WHERE id_estado = :id_estado";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_estado', $this->filter['state'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$value = _safe($row['ciudad']);
				if($this->filter['location'] == $row['id_ciudad']){
					$html .= '<option value="'.$row['id_ciudad'].'" selected>'.$value.'</option>';
				}else{
					$html .= '<option value="'.$row['id_ciudad'].'">'.$value.'</option>';
				}
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
		file_put_contents(ROOT.'\assets\error_logs\listing_results.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>