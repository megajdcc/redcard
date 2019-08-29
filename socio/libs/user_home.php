<?php 
namespace socio\libs;
use assets\libs\connection;
use PDO;

class user_home {
	private $con;
	private $user = array(
		'id' => null,
		'username' => null,
		'email' => null,
		'eSmarties' => null,
		'image' => null,
		'name' => null,
		'last_name' => null,
		'city_id' => null,
		'city' => null,
		'state' => null,
		'country' => null,
		'alias' => null,
		'invited' => 0,
		'following' => array()
	);
	private $posts = array();
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
		$query = "SELECT u.username, u.email, u.esmarties, u.imagen, u.nombre, u.apellido, u.id_ciudad 
			FROM usuario u 
			WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->user['username'] = $row['username'];
			$this->user['email'] = $row['email'];
			$this->user['eSmarties'] = $row['esmarties'];
			if(!empty($row['imagen'])){
				$this->user['image'] = $row['imagen'];
			}else{
				$this->user['image'] = 'default.jpg';
			}
			$this->user['name'] = $row['nombre'];
			$this->user['last_name'] = $row['apellido'];
			$this->user['city_id'] = $row['id_ciudad'];
			if(!empty($row['nombre']) || !empty($row['apellido'])){
				$this->user['alias'] = _safe($row['nombre'].' '.$row['apellido']);
			}else{
				$this->user['alias'] = _safe($row['username']);
			}
			if($this->user['city_id']){
				$query = "SELECT c.ciudad, e.estado, p.pais 
					FROM ciudad c
					INNER JOIN estado e ON c.id_estado = e.id_estado 
					INNER JOIN pais p ON e.id_pais = p.id_pais
					WHERE c.id_ciudad = :id_ciudad";
				try{
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(':id_ciudad', $this->user['city_id'], PDO::PARAM_INT);
					$stmt->execute();
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				if($row = $stmt->fetch()){
					$this->user['city'] = $row['ciudad'];
					$this->user['state'] = $row['estado'];
					$this->user['country'] = $row['pais'];
				}
			}
		}
		$query = "SELECT COUNT(*) FROM usuario_referencia WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->user['invited'] = $row['COUNT(*)'];
		}
		$query = "SELECT id_negocio FROM seguir_negocio WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->user['following'][] = $row['id_negocio'];
		}
		$ids = implode(',', array_map('intval', $this->user['following']));
		if(empty($ids)){
			$pagination['total'] = 0;
			$pagination['page'] = 0;
			return $pagination;
		}
		$query = "SELECT COUNT(*) FROM negocio_publicacion WHERE id_negocio IN ($ids) AND situacion = 1";
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
			$query = "SELECT np.id_publicacion, np.titulo, np.contenido, np.imagen, np.creado, n.url, n.nombre 
				FROM negocio_publicacion np 
				INNER JOIN negocio n ON np.id_negocio = n.id_negocio
				WHERE np.id_negocio IN ($ids) AND np.situacion = 1
				ORDER BY np.creado DESC LIMIT :limit OFFSET :offset";
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
				$this->posts[$row['id_publicacion']] = array(
					'title' => $row['titulo'],
					'content' => $row['contenido'],
					'image' => $row['imagen'],
					'created_at' => $row['creado'],
					'url' => $row['url'],
					'name' => $row['nombre']
				);
			}
			return $pagination;
		}
		return false;
	}

	public function get_posts(){
		$html = null;
		foreach ($this->posts as $key => $value) {
			$title = _safe($value['title']);
			$content = _safe($value['content']);
			$date_time = strtotime($value['created_at']);
			$date = date('d/m/Y \a \l\a\s g:i A', $date_time);
			$ago =  $this->time_tag($date_time);
			$image = _safe($value['image']);
			$url = _safe($value['url']);
			$name = _safe($value['name']);
			if($image){
				$image_html = 
					'<div class="col-xs-6 col-sm-4 detail-gallery-preview">
						<a href="'.HOST.'/assets/img/business/post/'.$image.'">
							<img class="img-thumbnail img-rounded post-img" src="'.HOST.'/assets/img/business/post/'.$image.'">
						</a>
					</div>
					<div class="col-xs-6 col-sm-8">';
			}else{
				$image_html = '<div class="col-sm-12">';
			}
			$html .= 
			'<div class="background-white p20 mb30">
				<div class="row">
					'.$image_html.'
						<strong class="text-default">'.$title.'</strong>
						<p>'.nl2br($content).'</p>
					</div>
				</div>
				<hr>
				<p><a href="'.HOST.'/'.$url.'" target="_blank">'.$name.'</a> @ hace <span title="'.$date.'">'.$ago.'</span></p>
			</div>';
		}
		if(is_null($html)){
			$html = '<div class="background-white p30"><p class="text-default">Parece que no hay nada por aqu&iacute;. <a href="'.HOST.'/listados">Encuentra m&aacute;s negocios</a> de tu inter&eacute;s.</p></div>';
		}
		return $html;
	}

	public function get_header_title(){
		$html = 
		'<a href="'.HOST.'/socio/'.$this->get_username().'" target="_blank" class="text-default">'.$this->get_alias().'</a>';
		return $html;
	}

	public function get_image(){
		$html = 
			'<a href="'.HOST.'/socio/perfil">
				<img src="'.HOST.'/assets/img/user_profile/'.$this->user['image'].'" alt="Foto de perfil de '.$this->user['alias'].'">
			</a>';
		return $html;
	}

	public function get_alias(){
		return _safe($this->user['alias']);
	}

	public function get_eSmarties(){
		$eSmarties = round($this->user['eSmarties'],2);
		return number_format((float)$eSmarties,2,'.',',');
	}

	public function get_username(){
		return _safe($this->user['username']);
	}

	public function get_email(){
		return _safe($this->user['email']);
	}

	public function get_name(){
		return _safe($this->user['name']);
	}

	public function get_last_name(){
		return _safe($this->user['last_name']);
	}

	public function get_city(){
		return _safe($this->user['city']);
	}

	public function get_state(){
		return _safe($this->user['state']);
	}

	public function get_country(){
		return _safe($this->user['country']);
	}

	public function get_location(){
		if($this->user['city'] && $this->user['country']){
			return _safe($this->user['city'].', '.$this->user['country']);
		}
	}

	public function get_invited(){
		return _safe($this->user['invited']);
	}

	public function time_tag ($time){
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
		file_put_contents(ROOT.'\assets\error_logs\user_home.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>