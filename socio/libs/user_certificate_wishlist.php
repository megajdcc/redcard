<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace socio\libs;
use assets\libs\connection;
use PDO;

class user_certificate_wishlist {
	private $con;
	private $user = array(
		'id' => null,
		'wishlist' => array()
	);
	private $review = array(
		'sale_id' => null,
		'comment' => null,
		'service' => null,
		'product' => null,
		'ambient' => null
	);
	private $error = array(
		'rating' => null,
		'comment' => null,
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
		$query = "SELECT COUNT(*) FROM lista_deseos_certificado WHERE id_usuario = :id_usuario";
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
			$query = "SELECT ldc.actualizado, nc.id_certificado, nc.url as cert_url, nc.imagen, nc.nombre, nc.descripcion, nc.precio, nc.iso, nc.fecha_inicio, nc.fecha_fin, nc.condiciones, nc.restricciones, nc.disponibles, (SELECT COUNT(*) FROM usar_certificado uc WHERE nc.id_certificado = uc.id_certificado AND uc.situacion != 0) as usados, n.id_negocio, n.nombre as n_nombre, n.url, c.ciudad, e.estado, p.pais 
				FROM lista_deseos_certificado ldc 
				INNER JOIN negocio_certificado nc ON ldc.id_certificado = nc.id_certificado 
				INNER JOIN negocio n ON nc.id_negocio = n.id_negocio 
				INNER JOIN ciudad c ON n.id_ciudad = c.id_ciudad 
				INNER JOIN estado e ON c.id_estado = e.id_estado 
				INNER JOIN pais p ON e.id_pais = p.id_pais 
				WHERE ldc.id_usuario = :id_usuario 
				ORDER BY ldc.actualizado DESC
				LIMIT :limit OFFSET :offset";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue('id_usuario', $this->user['id'], PDO::PARAM_INT);
				$stmt->bindValue(':limit', $this->pagination['rpp'], PDO::PARAM_INT);
				$stmt->bindValue(':offset', $this->pagination['offset'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->user['wishlist'][$row['id_certificado']] = array(
					'updated_at' => $row['actualizado'],
					'image' => $row['imagen'],
					'cert_url' => $row['cert_url'],
					'cert_name' => $row['nombre'],
					'description' => $row['descripcion'],
					'price' => $row['precio'],
					'currency' => $row['iso'],
					'date_start' => $row['fecha_inicio'],
					'date_end' => $row['fecha_fin'],
					'condition' => $row['condiciones'],
					'restriction' => $row['restricciones'],
					'available' => $row['disponibles'],
					'used' => $row['usados'],
					'business_id' => $row['id_negocio'],
					'business_name' => $row['n_nombre'],
					'business_url' => $row['url'],
					'business_city' => $row['ciudad'],
					'business_state' => $row['estado'],
					'business_country' => $row['pais']
				);
			}
			return $pagination;
		}
		return false;
	}

	public function discard_wishlist(array $post){
		if(!array_key_exists($post['id'], $this->user['wishlist'])){
			$this->error['error'] = 'Error al tratar de quitar el certificado';
			return false;
		}
		$query = "DELETE FROM lista_deseos_certificado WHERE id_usuario = :id_usuario AND id_certificado = :id_certificado";
		$query_params = array(':id_usuario' => $this->user['id'],':id_certificado' => $post['id']);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($query_params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		unset($_SESSION['user']['certificate_wishlist'][$post['id']]);
		$_SESSION['notification']['success'] = 'Certificado de regalo desechado exitosamente.';
		header('Location: '._safe($_SERVER['REQUEST_URI']));
		die();
		return;
	}

	public function get_wishlist(){
		$html = null;
		$count = $this->pagination['total']-$this->pagination['offset'];
		foreach ($this->user['wishlist'] as $key => $value) {
			$cert_name = _safe($value['cert_name']);
			$business_name = _safe($value['business_name']);
			$location = _safe($value['business_city'].', '.$value['business_state'].', '.$value['business_country']);
			$date = date('d/m/Y \a \l\a\s H:i A', strtotime($value['updated_at']));
			$url = _safe($value['business_url']);
			$description = _safe($value['description']);
			if(empty($value['condition'])){
				$condition = 'No tiene.';
			}else{
				$condition = _safe($value['condition']);
			}
			if(empty($row['restriction'])){
				$restriction = 'No tiene.';
			}else{
				$restriction = _safe($row['restriction']);
			}
			$start = date('d/m/Y', strtotime($value['date_start']));
			$end = date('d/m/Y', strtotime($value['date_end']));
			$price = number_format((float)$value['price'], 2, '.', '');
			$currency = $value['currency'];
			$total = $value['available'];
			$used = $value['used'];
			$available = $total-$used;
			$cert_url = _safe($value['cert_url']);
			$html .= 
			'<div class="background-white p20 mb30">
				<div class="row">
					<div class="col-xs-3 col-lg-2">
						<div class="">
							<a href="'.HOST.'/certificado/'.$cert_url.'" target="_blank">
								<img class="img-thumbnail" src="'.HOST.'/assets/img/business/certificate/'.$value['image'].'" alt="'.$cert_name.'">
							</a>
						</div>
					</div>
					<div class="col-xs-9 col-lg-10">
						<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'">
							<h2 class="page-title">
								<button class="btn btn-xs btn-danger pull-right discard-wishlist" type="submit" name="discard_wishlist"><i class="fa fa-times m0" aria-hidden="true"></i></button>
								<a href="'.HOST.'/certificado/'.$cert_url.'" target="_blank" class="text-default">'.$cert_name.'</a><br>
								<label><a href="'.HOST.'/'.$url.'" target="_blank">'.$business_name.'</a> | <i class="fa fa-map-marker" aria-hidden="true"></i> '.$location.'</label>
							</h2>
							<input type="hidden" name="id" value="'.$key.'">
						</form>
					</div>
				</div>
				<div class="form-group">
					<label>'.$description.'</label>
				</div>
				<div class="row">
					<div class="col-md-5">
						<div class="form-group">
							<label>V&aacute;lido del '.$start.' al '.$end.'</label>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Valuado en: '.$price.' '.$currency.'</label>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label>Disponibilidad: '.$available.' / '.$total.'</label>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label>Condiciones</label>
							<p>'.$condition.'</p>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label>Restricciones</label>
							<p>'.$restriction.'</p>
						</div>
					</div>
				</div>
				<hr>
				<div class="clearfix">
					<label class="pull-right">#'.$count.' - Agregado a mi lista el '.$date.'</label>
				</div>
			</div>';
			$count--;
		}
		if(!$html){
			$html = '<div class="background-white p20 text-default">No tienes certificados de regalo en tu lista de deseos.</div>';
		}
		return $html;
	}

	public function get_count(){
		$i = $this->pagination['total'];
		if($i > 0){
			return 'Tengo '.$i.' certificados en mi lista de deseos';
		}else{
			return 'Mi lista de deseos de certificados de regalo';
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
		file_put_contents(ROOT.'\assets\error_logs\user_certificate_wishlist.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>