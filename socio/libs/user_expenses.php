<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace socio\libs;
use assets\libs\connection;
use PDO;

class user_expenses {
	private $con;
	private $user = array(
		'id' => null,
		'expenses' => array()
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
		$query = "SELECT COUNT(*) FROM negocio_venta WHERE id_usuario = :id_usuario";
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
			$query = "SELECT v.id_venta, v.iso, v.venta, v.comision, v.bono_esmarties, v.creado, n.url, n.nombre, o.id_opinion
				FROM negocio_venta v
				INNER JOIN negocio n ON v.id_negocio = n.id_negocio 
				LEFT JOIN opinion o ON o.id_venta = v.id_venta
				WHERE v.id_usuario = :id_usuario
				ORDER BY v.creado DESC
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
				$this->user['expenses'][$row['id_venta']] = array(
					'currency' => $row['iso'],
					'sale' => $row['venta'],
					'commission' => $row['comision'],
					'eSmarties_bonus' => $row['bono_esmarties'],
					'created_at' => $row['creado'],
					'business_url' => $row['url'],
					'business_name' => $row['nombre'],
					'review' => $row['id_opinion']
				);
			}
			return $pagination;
		}
		return false;
	}

	public function submit_review(array $post){
		if(!array_key_exists($post['id'], $this->user['expenses'])){
			$this->error['error'] = 'Error al tratar de enviar la calificación. Intentalo otra vez.';
			return false;
		}else{
			$this->review['sale_id'] = $post['id'];
		}
		if(empty($post['comment'])){
			$this->error['comment'] = 'Este campo es obligatorio.';
		}else{
			$this->review['comment'] = trim($post['comment']);
		}
		if(!isset($post['service']) || !isset($post['product']) || !isset($post['ambient'])){
			$this->error['rating'] = 'Todas las calificaciones son obligatorias.';
		}else{
			$this->set_service_rating($post['service']);
			$this->set_product_rating($post['product']);
			$this->set_ambient_rating($post['ambient']);
		}
		if(!array_filter($this->error)){
			$query = "INSERT INTO opinion (
				id_venta, 
				opinion, 
				calificacion_servicio, 
				calificacion_producto, 
				calificacion_ambiente 
				) VALUES (
				:id_venta, 
				:opinion, 
				:calificacion_servicio, 
				:calificacion_producto, 
				:calificacion_ambiente
			)";
			$params = array(
				':id_venta' => $this->review['sale_id'],
				':opinion' => $this->review['comment'],
				':calificacion_servicio' => $this->review['service'],
				':calificacion_producto' => $this->review['product'],
				':calificacion_ambiente' => $this->review['ambient']
			);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$_SESSION['notification']['success'] = 'Calificación enviada correctamente.';
			header('Location: '._safe($_SERVER['REQUEST_URI']));
			die();
			return;
		}
		$this->error['warning'] = 'Uno o más campos tienen errores. Verifícalos cuidadosamente.';
		return false;
	}

	private function set_service_rating($rate = null){
		if($rate){
			$rate = filter_var($rate, FILTER_VALIDATE_INT);
			if(!$rate){
				$this->error['rating'] = 'Error al validar la calificación.';
				return false;
			}
			if($rate < 1 || $rate > 5){
				$this->error['rating'] = 'Error al validar la calificación.';
				return false;
			}
			$this->review['service'] = $rate;
			return;
		}
		$this->error['rating'] = 'Error al validar la calificación.';
		return false;
	}

	private function set_product_rating($rate = null){
		if($rate){
			$rate = filter_var($rate, FILTER_VALIDATE_INT);
			if(!$rate){
				$this->error['rating'] = 'Error al validar la calificación.';
				return false;
			}
			if($rate < 1 || $rate > 5){
				$this->error['rating'] = 'Error al validar la calificación.';
				return false;
			}
			$this->review['product'] = $rate;
			return;
		}
		$this->error['rating'] = 'Error al validar la calificación.';
		return false;
	}

	private function set_ambient_rating($rate = null){
		if($rate){
			$rate = filter_var($rate, FILTER_VALIDATE_INT);
			if(!$rate){
				$this->error['rating'] = 'Error al validar la calificación.';
				return false;
			}
			if($rate < 1 || $rate > 5){
				$this->error['rating'] = 'Error al validar la calificación.';
				return false;
			}
			$this->review['ambient'] = $rate;
			return;
		}
		$this->error['rating'] = 'Error al validar la calificación.';
		return false;
	}

	public function get_expenses(){
		$html = null;
		$count = $this->pagination['total']-$this->pagination['offset'];
		foreach ($this->user['expenses'] as $key => $value) {
			$sale = number_format((float)$value['sale'], 2, '.', '');
			$commission = _safe($value['commission']);
			$eSmarties = number_format((float)$value['eSmarties_bonus'], 2, '.', '');
			$url = _safe($value['business_url']);
			$name = _safe($value['business_name']);
			$date = date('d/m/Y \a \l\a\s g:i A', strtotime($value['created_at']));
			if($key == $this->review['sale_id']){
				$comment = $this->get_comment();
				$comment_error = $this->get_comment_error();
				$rating_error = $this->get_rating_error();
			}else{
				$comment = $comment_error = $rating_error = null;
			}
			$review = null;
			if(!$value['review']){
				$review = 
					'<hr>
					<h4>Tu opinión es muy importante. Comparte tu experiencia con nosotros.</h4>
					<div class="detail-content">
						<form class="add-review" method="post" action="'._safe($_SERVER['REQUEST_URI']).'">
							<div class="row">
								<div class="col-md-4">
									<div class="form-group input-rating">
										<div class="rating-title">Servicio <span class="required">*</span></div>
										<input type="radio" value="1" name="service" id="'.$key.'-service-1" required>
										<label for="'.$key.'-service-1"></label>
										<input type="radio" value="2" name="service" id="'.$key.'-service-2">
										<label for="'.$key.'-service-2"></label>
										<input type="radio" value="3" name="service" id="'.$key.'-service-3">
										<label for="'.$key.'-service-3"></label>
										<input type="radio" value="4" name="service" id="'.$key.'-service-4">
										<label for="'.$key.'-service-4"></label>
										<input type="radio" value="5" name="service" id="'.$key.'-service-5">
										<label for="'.$key.'-service-5"></label>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group input-rating">
										<div class="rating-title">Producto <span class="required">*</span></div>
										<input type="radio" value="1" name="product" id="'.$key.'-product-1" required>
										<label for="'.$key.'-product-1"></label>
										<input type="radio" value="2" name="product" id="'.$key.'-product-2">
										<label for="'.$key.'-product-2"></label>
										<input type="radio" value="3" name="product" id="'.$key.'-product-3">
										<label for="'.$key.'-product-3"></label>
										<input type="radio" value="4" name="product" id="'.$key.'-product-4">
										<label for="'.$key.'-product-4"></label>
										<input type="radio" value="5" name="product" id="'.$key.'-product-5">
										<label for="'.$key.'-product-5"></label>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group input-rating">
										<div class="rating-title">Ambiente <span class="required">*</span></div>
										<input type="radio" value="1" name="ambient" id="'.$key.'-ambient-1" required>
										<label for="'.$key.'-ambient-1"></label>
										<input type="radio" value="2" name="ambient" id="'.$key.'-ambient-2">
										<label for="'.$key.'-ambient-2"></label>
										<input type="radio" value="3" name="ambient" id="'.$key.'-ambient-3">
										<label for="'.$key.'-ambient-3"></label>
										<input type="radio" value="4" name="ambient" id="'.$key.'-ambient-4">
										<label for="'.$key.'-ambient-4"></label>
										<input type="radio" value="5" name="ambient" id="'.$key.'-ambient-5">
										<label for="'.$key.'-ambient-5"></label>
									</div>
								</div>
							</div><!-- /.row -->
							'.$rating_error.'
							<div class="form-group">
								<label for="'.$key.'-review">Tu opini&oacute;n <span class="required">*</span></label>
								<textarea class="form-control" rows="3" id="'.$key.'-review" name="comment" placeholder="Opini&oacute;n sobre el servicio, producto, ambiente, etc." required>'.$comment.'</textarea>
							</div>
							'.$comment_error.'
							<input type="hidden" name="id" value="'.$key.'">
							<button class="btn btn-secondary pull-right" type="submit"><i class="fa fa-star"></i>Calificar</button>
							<p>Los campos marcados son obligatorios <span class="required">*</span></p>
							<div class="clearfix"></div>
						</form>
					</div>';
			}
			$html .=
				'<div class="background-white p30 mb30">
					<a href="'.HOST.'/'.$url.'" target="_blank">'.$name.'</a> ha registrado este consumo por la cantidad de <strong>'.$sale.' '.$value['currency'].'</strong>. El '.$date.'.<span class="pull-right">#'.$count.'</span>
					<hr>
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label>Total del consumo</label>
								<p>'.$sale.' '.$value['currency'].'</p>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label>Comisi&oacute;n</label>
								<p>'.$commission.'%</p>
							</div>
						</div><!-- /.col-* -->
						<div class="col-md-4">
							<div class="form-group">
								<label>eSmartties Bonificados</label>
								<p>'.$eSmarties.'</p>
							</div>
						</div>
					</div><!-- /.row -->
					'.$review.'
				</div><!-- box -->';
			$count--;
		}
		if(!$html){
			$html = '<div class="background-white p20 text-default">No has hecho ning&uacute;n consumo.</div>';
		}
		return $html;
	}

	public function get_count(){
		$i = $this->pagination['total'];
		if($i > 0){
			return 'He hecho '.$i.' consumos';
		}else{
			return 'Mis consumos';
		}
	}

	public function get_rating_error(){
		if($this->error['rating']){
			$error = '<p class="text-danger">'._safe($this->error['rating']).'</p>';
			return $error;
		}
	}

	public function get_comment(){
		return _safe($this->review['comment']);
	}

	public function get_comment_error(){
		if($this->error['comment']){
			$error = '<p class="text-danger">'._safe($this->error['comment']).'</p>';
			return $error;
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
		file_put_contents(ROOT.'\assets\error_logs\user_expenses.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>