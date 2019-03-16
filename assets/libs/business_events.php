<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace assets\libs;
use PDO;

class business_events {
	private $con;
	private $business = array(
		'id' => null,
		'url' => null,
		'name' => null,
		'category_id' => null,
		'category' => null,
		'commission' => null,
		'city' => null,
		'state' => null,
		'country' => null,
		'logo' => null,
		'header' => null,
		'score' => null
	);
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
	private $events = array();
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

	public function load_data($url, $page = null, $rpp = null){
		if(!$this->load_business($url)){
			return false;
		}
		$query = "SELECT COUNT(*) FROM negocio_evento WHERE id_negocio = :id_negocio";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
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
			$query = "SELECT ne.id_evento, ne.titulo, ne.contenido, ne.fecha_inicio, ne.fecha_fin, ne.imagen, ne.creado
				FROM negocio_evento ne
				WHERE ne.id_negocio = :id_negocio AND situacion != 0
				ORDER BY ne.creado DESC LIMIT :limit OFFSET :offset";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
				$stmt->bindValue(':limit', $this->pagination['rpp'], PDO::PARAM_INT);
				$stmt->bindValue(':offset', $this->pagination['offset'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->events[$row['id_evento']] = array(
					'title' => $row['titulo'],
					'content' => $row['contenido'],
					'date_start' => $row['fecha_inicio'],
					'date_end' => $row['fecha_fin'],
					'image' => $row['imagen'],
					'created_at' => $row['creado']
				);
			}
			return $pagination;
		}
		return false;
	}

	private function load_business($url = null){
		if($url){
			$url = strtolower(trim($url));
			if(!preg_match('/^[a-z0-9-]+$/ui',$url)){
				return false;
			}
			$query = "SELECT n.id_negocio, n.nombre, n.comision, n.id_categoria, nc.categoria, c.ciudad, e.estado, p.pais, l.preferencia as logo, h.preferencia as portada
				FROM negocio n
				INNER JOIN negocio_categoria nc ON n.id_categoria = nc.id_categoria
				INNER JOIN ciudad c ON n.id_ciudad = c.id_ciudad
				INNER JOIN estado e ON c.id_estado = e.id_estado
				INNER JOIN pais p ON e.id_pais = p.id_pais
				INNER JOIN preferencia pr
				INNER JOIN negocio_preferencia l ON l.id_negocio = n.id_negocio AND l.id_preferencia = pr.id_preferencia AND pr.llave = 'business_logo'
				INNER JOIN preferencia pre
				INNER JOIN negocio_preferencia h ON h.id_negocio = n.id_negocio AND h.id_preferencia = pre.id_preferencia AND pre.llave = 'business_header'
				WHERE n.url = :url AND n.situacion != 0";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':url', $url, PDO::PARAM_STR);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				$this->business['id'] = $row['id_negocio'];
				$this->business['url'] = $url;
				$this->business['name'] = $row['nombre'];
				$this->business['category_id'] = $row['id_categoria'];
				$this->business['category'] = $row['categoria'];
				$this->business['commission'] = $row['comision'];
				$this->business['city'] = $row['ciudad'];
				$this->business['state'] = $row['estado'];
				$this->business['country'] = $row['pais'];
				$this->business['logo'] = $row['logo'];
				$this->business['header'] = $row['portada'];
				$service = $product = $ambient = 0;
				$query = "SELECT o.calificacion_servicio, o.calificacion_producto, o.calificacion_ambiente FROM opinion o
					INNER JOIN negocio_venta nv ON o.id_venta = nv.id_venta 
					WHERE nv.id_negocio = :id_negocio";
				try{
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
					$stmt->execute();
					$count = $stmt->rowCount();
				}catch(\PDOException $ex){
					$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				while($row = $stmt->fetch()){
					$service += $row['calificacion_servicio'];
					$product += $row['calificacion_producto'];
					$ambient += $row['calificacion_ambiente'];
				}
				if($count == 0){
					$this->business['score'] = 0;
				}else{
					$this->business['score'] = ($service + $product + $ambient) / ($count * 3);
				}
				return true;
			}
		}
		return false;
	}

	public function get_menu(){
		$html = 
			'<div class="widget">
				<ul class="menu-advanced">
					<li'.$this->set_active_sidebar_tab('perfil_negocio.php').'><a href="'.HOST.'/'.$this->business['url'].'"><i class="fa fa-home"></i> '.$this->get_name().'</a></li>
					<li'.$this->set_active_sidebar_tab('negocio_certificados.php').'><a href="'.HOST.'/'.$this->business['url'].'/certificados"><i class="fa fa-gift"></i> Certificados de regalo</a></li>
					<li'.$this->set_active_sidebar_tab('negocio_publicaciones.php').'><a href="'.HOST.'/'.$this->business['url'].'/publicaciones"><i class="fa fa-flag"></i> Publicaciones</a></li>
					<li'.$this->set_active_sidebar_tab('negocio_eventos.php').'><a href="'.HOST.'/'.$this->business['url'].'/eventos"><i class="fa fa-calendar"></i> Eventos</a></li>
					<li'.$this->set_active_sidebar_tab('negocio_opiniones.php').'><a href="'.HOST.'/'.$this->business['url'].'/opiniones"><i class="fa fa-comments-o"></i> Opiniones</a></li>
				</ul>
			</div>';
		return $html;
	}

	private function set_active_sidebar_tab($tab = null){
		if(basename($_SERVER['SCRIPT_NAME']) == $tab){
			$class = ' class="active"';
		}else{
			$class= '';
		}
		return $class;
	}

	public function get_events(){
		$html = null;
		foreach ($this->events as $key => $value) {
			$title = _safe($value['title']);
			$content = _safe($value['content']);
			$time = strtotime($value['created_at']);
			$posted = date('d/m/Y \a \l\a\s g:i A', $time);
			$date_start = date('d/m/Y \a \l\a\s g:i A', strtotime($value['date_start']));
			$date_end = date('d/m/Y \a \l\a\s g:i A', strtotime($value['date_end']));
			$ago = $this->time_tag($time);
			$image = HOST.'/assets/img/business/event/'._safe($value['image']);
			$url = _safe($this->business['url']);
			$name = _safe($this->business['name']);
			$html .= 
			'<div class="background-white p20 mb30">
				<div class="row">
					<div class="col-xs-6 col-sm-4 detail-gallery-preview">
						<a href="'.$image.'">
							<img class="img-thumbnail img-rounded post-img" src="'.$image.'">
						</a>
					</div>
					<div class="col-xs-6 col-sm-8">
						<div class="cert-date">Inicia: '.$date_start.' | Termina: '.$date_end.'</div>
						<hr>
						<strong class="text-default">'.$title.'</strong>
						<p>'.nl2br($content).'</p>
					</div>
				</div>
				<p class="right">Publicado hace <span title="'.$posted.'">'.$ago.'</span></p>
			</div>';
		}
		if(is_null($html)){
			$html = '<div class="background-white p30"><p class="text-default">Este negocio no ha publicado ning&uacute;n evento.</p></div>';
		}
		return $html;
	}

	private function time_tag($time){
		$time = time() - $time; // to get the time since that moment
		$time = ($time<1)? 1 : $time;
		$tokens = array (
			31536000 => 'año',
			2592000 => 'mes',
			604800 => 'semana',
			86400 => 'día',
			3600 => 'hora',
			60 => 'minuto',
			1 => 'segundo'
		);
		foreach ($tokens as $unit => $text) {
			if ($time < $unit) continue;
			$numberOfUnits = floor($time / $unit);
			return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
		}
	}

	public function get_raw_name(){
		return $this->business['name'];
	}

	public function get_header(){
		return HOST.'/assets/img/business/header/'._safe($this->business['header']);
	}

	public function get_name(){
		return _safe($this->business['name']);
	}

	public function get_category(){
		return _safe($this->business['category']);
	}

	public function get_category_tag(){
		return $this->categories[$this->business['category_id']];
	}

	public function get_commission(){
		return _safe($this->business['commission']);
	}

	public function get_location(){
		return _safe($this->business['city'].', '.$this->business['state'].', '.$this->business['country']);
	}

	public function get_score_stars($score){
		$score = round($score*2)/2;
		$html = null;
		for ($i=0; $i < 5; $i++){
			if($i < $score){
				if($score == $i+0.5){
					$html .= '<i class="fa fa-star-half-o"></i> ';
				}else{
					$html .= '<i class="fa fa-star"></i> ';
				}
			}else{
				$html .= '<i class="fa fa-star-o"></i>';
			}
		}
		return $html;
	}

	public function get_average_score(){
		return round($this->business['score'],2);
	}

	public function get_buttons(){
		$html = null;
		if(isset($_SESSION['user']['id_usuario'])){
			if(array_key_exists($this->business['id'], $_SESSION['user']['follow_business'])){
				$html .= '<div class="detail-banner-btn bookmark marked" id="bookmark" data-id="'.$this->business['id'].'" data-function="del"><i class="fa fa-bookmark-o"></i> <span data-toggle="Seguir">Siguiendo</span></div><!-- /.detail-claim -->';
			}else{
				$html .= '<div class="detail-banner-btn bookmark" id="bookmark" data-id="'.$this->business['id'].'" data-function="add"><i class="fa fa-bookmark-o"></i> <span data-toggle="Siguiendo">Seguir</span></div><!-- /.detail-claim -->';
			}
			if(array_key_exists($this->business['id'], $_SESSION['user']['recommend_business'])){
				$html .= '<div class="detail-banner-btn heart marked" id="recommend" data-id="'.$this->business['id'].'" data-function="del">
				<i class="fa fa-heart-o"></i> <span data-toggle="Lo recomiendo">Recomendado</span></div><!-- /.detail-claim -->';
				
			}else{
				$html .= '<div class="detail-banner-btn heart" id="recommend" data-id="'.$this->business['id'].'" data-function="add">
				<i class="fa fa-heart-o"></i> <span data-toggle="Recomendado">Lo recomiendo</span></div><!-- /.detail-claim -->';
			}
		}else{
			$html .= '<a href="'.HOST.'/login" class="detail-banner-btn"><i class="fa fa-bookmark-o"></i>Seguir</a><!-- /.detail-claim -->
			<a href="'.HOST.'/login" class="detail-banner-btn"><i class="fa fa-heart-o"></i>Lo recomiendo</a><!-- /.detail-claim -->';
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
		file_put_contents(ROOT.'\assets\error_logs\business_events.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>