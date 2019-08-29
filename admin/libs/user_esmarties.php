<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace admin\libs;
use assets\libs\connection;
use PDO;

class user_esmarties {
	private $con;
	private $user = array(
		'id' => null,
		'username' => null,
		'eSmarties' => null,
		'image' => null,
		'name' => null,
		'last_name' => null,
		'city_id' => null,
		'city' => null,
		'state' => null,
		'country' => null,
		'alias' => null,
		'referrals' => array()
	);
	private $moves = array();
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

	public function load_data($username, $page = null, $rpp = null){
		$query = "SELECT u.id_usuario, u.username, u.esmarties, u.imagen, u.nombre, u.apellido, u.id_ciudad 
			FROM usuario u 
			WHERE u.username = :username";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':username', $username, PDO::PARAM_STR);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->user['id'] = $row['id_usuario'];
			$this->user['username'] = $row['username'];
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
		$query = "SELECT id_nuevo_usuario FROM usuario_referencia WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->user['referrals'][] = $row['id_nuevo_usuario'];
		}
		$ids = implode(',', array_map('intval', $this->user['referrals']));
		if(!empty($ids)){
			$referrals = "SELECT COUNT(id_venta) as a FROM negocio_venta WHERE id_usuario IN ($ids)
			UNION";
		}else{
			$referrals = '';
		}
		$query = "SELECT COUNT(id_venta) as a FROM negocio_venta WHERE id_usuario = :id_usuario1
			UNION
			$referrals
			SELECT COUNT(id_venta) as a FROM venta_tienda WHERE id_usuario = :id_usuario2";
		$params = array(':id_usuario1' => $this->user['id'], ':id_usuario2' => $this->user['id']);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$total = 0;
		while($row = $stmt->fetch()){
			$total += $row['a'];
		}
		if($total > 0){
			$this->pagination['total'] = $total;
			$this->pagination['rpp'] = $rpp;
			$this->pagination['max'] = (int)ceil($this->pagination['total'] / $this->pagination['rpp']);
			$this->pagination['page'] = min($this->pagination['max'], $page);
			$this->pagination['offset'] = ($this->pagination['page'] - 1) * $this->pagination['rpp'];
			// Variables retornables
			$pagination['page'] = $this->pagination['page'];
			$pagination['total'] = $this->pagination['total'];
			// Cargar los certificados
			if(!empty($ids)){
				$referrals = "SELECT bono_referente as esmarties, 2 as tipo, creado FROM negocio_venta WHERE id_usuario IN (1)
					UNION";
			}else{
				$referrals = '';
			}
			$query = "SELECT bono_esmarties as esmarties, 1 as tipo, creado FROM negocio_venta WHERE id_usuario = :id_usuario1
				UNION
				$referrals
				SELECT precio as esmarties, 3 as tipo, creado FROM venta_tienda WHERE id_usuario = :id_usuario2
				ORDER BY creado DESC
				LIMIT :limit OFFSET :offset";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_usuario1', $this->user['id'], PDO::PARAM_INT);
				$stmt->bindValue(':id_usuario2', $this->user['id'], PDO::PARAM_INT);
				$stmt->bindValue(':limit', $this->pagination['rpp'], PDO::PARAM_INT);
				$stmt->bindValue(':offset', $this->pagination['offset'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->moves[] = array(
					'esmarties' => $row['esmarties'],
					'type' => $row['tipo'],
					'created_at' => $row['creado']
				);
			}
			return $pagination;
		}else{
			$pagination['total'] = 0;
			$pagination['page'] = 0;
			return $pagination;
		}
		return false;
	}

	public function get_moves(){
		$moves = null;
		foreach ($this->moves as $key => $value) {
			$esmarties = number_format((float)$value['esmarties'], 2, '.', '');
			switch ($value['type']) {
				case 1:
					$esm = '<strong class="text-primary mr20">+ '.$esmarties.'</strong>';
					$tag = 'Bono por consumo';
					break;
				case 2:
					$esm = '<strong class="text-primary mr20">+ '.$esmarties.'</strong>';
					$tag = 'Bono por referido';
					break;
				case 3:
					$esm = '<strong class="text-danger mr20">- '.$esmarties.'</strong>';
					$tag = 'Gasto en tienda';
					break;
				default:
					$tag = '';
					$esm = '';
					break;
			}
			$date_time = strtotime($value['created_at']);
			$date = date('d/m/Y \a \l\a\s g:i A', $date_time);
			$ago =  $this->time_tag($date_time);
			$moves .= 
				'<tr>
					<td class="text-default">'.$tag.'</td>
					<td>'.$esm.'</td>
					<td>Hace '.$ago.' el '.$date.'</td>
				</tr>';
		}
		if($moves){
			$html = 
				'<div class="table-responsive">
					<table class="table table-hover">
						<thead>
						<tr>
							<th>Tipo</th>
							<th>TravelPoints</th>
							<th>Fecha y hora</th>
						</tr>
						</thead>
						<tbody>
						'.$moves.'
						</tbody>
					</table>
				</div>';
		}else{
			$html = '<br>Este usuario no ha registrado movimientos.';
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
		return $eSmarties;
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
		file_put_contents(ROOT.'\assets\error_logs\user_esmarties.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>