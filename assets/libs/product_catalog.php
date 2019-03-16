<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace assets\libs;
use PDO;

class product_catalog {
	private $con;

	private $products = array();
	private $categories = array();
	private $sorting = array(
		'nuevos' => 'M&aacute;s nuevos',
		'asc' => 'Precios bajos',
		'desc' => 'Precios altos'
	);
	private $show = array(
		12,
		24,
		48,
		100
	);
	private $filter = array(
		'category' => null,
		'sorting' => null,
		'show' => null
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
		$this->load_categories();
		return;
	}

	public function load_categories(){
		$query = "SELECT id_categoria, categoria FROM producto_categoria";
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

	public function load_data($search = null, $page = null, array $filter){
		// TERMINO DE BUSQUEDA
		if(!empty($search)){
			$search_query = "AND (p.nombre LIKE :term1 OR p.descripcion LIKE :term2)";
		}else{
			$search_query = null;
		}
		// FILTROS
		if($filter['category'] && array_key_exists($filter['category'], $this->categories)){
			$this->filter['category'] = $filter['category'];
			$query_filter = 'AND p.id_categoria = '.$filter['category'];
		}else{
			$query_filter = '';
		}
		if($filter['sorting'] && array_key_exists($filter['sorting'], $this->sorting)){
			$this->filter['sorting'] = $filter['sorting'];
		}else{
			$this->filter['sorting'] = 'nuevos';
		}
		switch ($this->filter['sorting']) {
			case 'asc':
				$order = 'p.precio ASC';
				break;
			case 'desc':
				$order = 'p.precio DESC';
				break;
			default:
				$order = 'p.creado DESC, p.id_producto DESC';
				break;
		}
		if($filter['show'] && in_array($filter['show'], $this->show)){
			$this->filter['show'] = $filter['show'];
		}else{
			$this->filter['show'] = 12;
		}
		// PAGINACION
		$query = "SELECT COUNT(*) FROM producto p WHERE situacion = 1 $query_filter $search_query";
		try{
			$stmt = $this->con->prepare($query);
			if($search){
				$stmt->bindValue(':term1', '%'.$search.'%', PDO::PARAM_STR);
				$stmt->bindValue(':term2', '%'.$search.'%', PDO::PARAM_STR);
			}
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->pagination['total'] = $row['COUNT(*)'];
			$this->pagination['rpp'] = $this->filter['show'];
			$this->pagination['max'] = (int)ceil($this->pagination['total'] / $this->pagination['rpp']);
			$this->pagination['page'] = min($this->pagination['max'], $page);
			$this->pagination['offset'] = ($this->pagination['page'] - 1) * $this->pagination['rpp'];
				// Variables retornables
			$pagination['page'] = $this->pagination['page'];
			$pagination['total'] = $this->pagination['total'];
			$pagination['rpp'] = $this->pagination['rpp'];
			if($this->pagination['total'] > 0){
				$start = $this->pagination['offset'] + 1;
				$end = min(($this->pagination['offset'] + $this->pagination['rpp']), $this->pagination['total']);
				if($this->pagination['max'] == 1){ $pg = 'P&aacutegina'; }else{ $pg = 'P&aacute;ginas';}
				$pagination['count'] = 'Viendo del '.$start.' al '.$end.' de un total de '.$this->pagination['total'].' productos. ('.$this->pagination['max'].' '.$pg.').';
			}else{
				$pagination['count'] = '';
			}
			// Cargar los productos
			$query = "SELECT p.id_producto, p.nombre, p.descripcion, pc.categoria, p.precio, p.disponibles, p.imagen 
				FROM producto p
				INNER JOIN producto_categoria pc ON p.id_categoria = pc.id_categoria
				WHERE situacion = 1 $query_filter $search_query
				ORDER BY $order
				LIMIT :limit OFFSET :offset";
			try{
				$stmt = $this->con->prepare($query);
				if($search){
					$stmt->bindValue(':term1', '%'.$search.'%', PDO::PARAM_STR);
					$stmt->bindValue(':term2', '%'.$search.'%', PDO::PARAM_STR);
				}
				$stmt->bindValue(':limit', $this->pagination['rpp'], PDO::PARAM_INT);
				$stmt->bindValue(':offset', $this->pagination['offset'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->products[$row['id_producto']] = array(
					'name' => $row['nombre'],
					'description' => $row['descripcion'],
					'category' => $row['categoria'],
					'price' => $row['precio'],
					'available' => $row['disponibles'],
					'image' => $row['imagen']
				);
			}
			return $pagination;
		}
		return false;
	}

	public function get_products(){
		$html = null;
		foreach ($this->products as $key => $value) {
			$image = HOST.'/assets/img/store/'._safe($value['image']);
			$name = _safe($value['name']);
			$url = HOST.'/tienda/producto/'.$key;
			$price = number_format((float)$value['price'], 2, '.', '');
			$description = _safe($value['description']);
			$category = _safe($value['category']);
			$html .=
			'<div class="col-sm-6 col-md-3">
				<div class="card-simple" data-background-image="'.$image.'">
					<div class="card-simple-background">
						<div class="card-simple-content">
							<h2><a href="'.$url.'">'.$name.'</a></h2>
							<div class="card-simple-actions">
								<a href="'.$url.'" class="fa fa-search"></a>
							</div><!-- /.card-simple-actions -->
						</div><!-- /.card-simple-content -->
						<div class="card-simple-label">'.$category.'</div>
						<div class="card-simple-price">e$ '.$price.'</div>
					</div><!-- /.card-simple-background -->
				</div><!-- /.card-simple -->
			</div><!-- /.col-* -->';
		}
		if(!$html){
			$html = '<div class="col-sm-12 text-default">No se encontraron productos.</div>';
		}
		return $html;
	}

	public function get_categories(){
		$html = '<option value="">Todas las categor&iacute;as</option>';
		foreach ($this->categories as $key => $value) {
			$category = _safe($value);
			if($key == $this->filter['category']){
				$html .= '<option value="'.$key.'" selected>'.$category.'</option>';
			}else{
				$html .= '<option value="'.$key.'">'.$category.'</option>';
			}
		}
		return $html;
	}

	public function get_sorting(){
		$html = null;
		foreach ($this->sorting as $key => $value) {
			if($key == $this->filter['sorting']){
				$html .= '<option value="'.$key.'" selected>'.$value.'</option>';
			}else{
				$html .= '<option value="'.$key.'">'.$value.'</option>';
			}
		}
		return $html;
	}

	public function get_show(){
		$html = null;
		foreach ($this->show as $key => $value) {
			if($value == $this->filter['show']){
				$html .= '<option value="'.$value.'" selected>'.$value.'</option>';
			}else{
				$html .= '<option value="'.$value.'">'.$value.'</option>';
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
		file_put_contents(ROOT.'\assets\error_logs\product_catalog.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>