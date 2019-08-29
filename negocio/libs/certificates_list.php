<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace negocio\libs;
use assets\libs\connection;
use PDO;

class certificates_list {
	private $con;
	private $user = array('id' => null);
	private $business = array(
		'id' => null,
		'url' => null,
		'certificates' => array(),
		'cert_id' => null,
		'image' => array('tmp_name' => null, 'file_name' => null, 'path' => null),
		'modal' => null
	);
	private $currencies = array();
	private $error = array(
		'name' => null,
		'description' => null,
		'quantity' => null,
		'price' => null,
		'currency' => null,
		'date_start' => null,
		'date_end' => null,
		'image' => null,
		'modal' => false,
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
		$this->business['id'] = $_SESSION['business']['id_negocio'];
		$this->business['url'] = $_SESSION['business']['url'];
		$this->user['id'] = $_SESSION['user']['id_usuario'];
		return;
	}

	public function load_data($page = null, $rpp = null){
		$query = "SELECT iso FROM divisa";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->currencies[$row['iso']] = $row['iso'];
		}
		$query = "SELECT COUNT(*) FROM negocio_certificado WHERE situacion != 0 AND id_negocio = :id_negocio";
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
			$query = "SELECT ne.id_certificado, ne.url, ne.imagen, ne.nombre, ne.descripcion, ne.precio, ne.iso, ne.fecha_inicio, ne.fecha_fin, ne.condiciones, ne.restricciones, ne.disponibles, ne.situacion, ne.disponibles-(SELECT COUNT(*) FROM usar_certificado uc WHERE uc.id_certificado = ne.id_certificado AND uc.situacion != 0) as restantes
				FROM negocio_certificado ne
				WHERE ne.id_negocio = :id_negocio AND ne.situacion != 0
				ORDER BY ne.creado DESC LIMIT :limit OFFSET :offset";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue('id_negocio', $this->business['id'], PDO::PARAM_INT);
				$stmt->bindValue(':limit', $this->pagination['rpp'], PDO::PARAM_INT);
				$stmt->bindValue(':offset', $this->pagination['offset'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->business['certificates'][$row['id_certificado']] = array(
					'url' => $row['url'],
					'image' => $row['imagen'],
					'name' => $row['nombre'],
					'description' => $row['descripcion'],
					'price' => $row['precio'],
					'currency' => $row['iso'],
					'date_start' => $row['fecha_inicio'],
					'date_end' => $row['fecha_fin'],
					'condition' => $row['condiciones'],
					'restriction' => $row['restricciones'],
					'total' => $row['disponibles'],
					'status' => $row['situacion'],
					'available' => $row['restantes']
				);
			}
			return $pagination;
		}
		return false;
	}

	// public function activate_certificate(array $post){
	// 	if(array_key_exists($post['id'], $this->business['certificates'])){
	// 		$query = "UPDATE negocio_certificado SET situacion = 1 WHERE id_negocio = :id_negocio AND id_certificado = :id_certificado";
	// 		$params = array(':id_negocio' => $this->business['id'], 'id_certificado' => $post['id']);
	// 		try{
	// 			$stmt = $this->con->prepare($query);
	// 			$stmt->execute($params);
	// 		}catch(\PDOException $ex){
	// 			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
	// 			return false;
	// 		}
	// 		$_SESSION['notification']['success'] = 'Certificado activado correctamente.';
	// 		header('Location: '.HOST.'/manage/certificates/list');
	// 		die();
	// 	}
	// 	return;
	// }

	public function cancel_certificate(array $post){
		if(array_key_exists($post['id'], $this->business['certificates'])){
			$query = "UPDATE negocio_certificado SET situacion = 3 WHERE id_negocio = :id_negocio AND id_certificado = :id_certificado";
			$params = array(':id_negocio' => $this->business['id'], 'id_certificado' => $post['id']);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$_SESSION['notification']['success'] = 'Certificado cancelado correctamente.';
			header('Location: '._safe($_SERVER['REQUEST_URI']));
			die();
		}
		return;
	}

	public function delete_certificate(array $post){
		if(array_key_exists($post['id'], $this->business['certificates'])){
			$query = "UPDATE negocio_certificado SET situacion = 0 WHERE id_negocio = :id_negocio AND id_certificado = :id_certificado";
			$params = array(':id_negocio' => $this->business['id'], 'id_certificado' => $post['id']);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$_SESSION['notification']['success'] = 'Certificado eliminado correctamente.';
			header('Location: '._safe($_SERVER['REQUEST_URI']));
			die();
		}
		return;
	}

	public function get_certificates(){
		$html = null;
		foreach ($this->business['certificates'] as $key => $value) {
			$name = _safe($value['name']);
			$description = _safe($value['description']);
			$start = strtotime($value['date_start']);
			$end = strtotime($value['date_end']);
			$date['start'] = date('d/m/Y \a \l\a\s g:i A', $start);
			$date['end'] = date('d/m/Y \a \l\a\s g:i A', $end);
			$image = _safe($value['image']);
			$total = _safe($value['total']);
			$available = _safe($value['available']);
			$cost = number_format((float)$value['price'], 2, '.', '');
			$iso = $value['currency'];
			$price = $cost.' '.$iso;
			$btns = '';
			$now = time();
			$edit_modal = null;
			switch ($value['status']) {
				case 1:
					if($start > $now){
						$status = '<span class="label btn-xs label-info pull-left mr20">Proximamente</span>';
						$btns = 
							'<button class="btn btn-xs btn-danger pull-right delete-certificate" type="submit" name="delete_certificate"><i class="fa fa-trash m0"></i></button>
							<button class="btn btn-xs btn-danger pull-right cancel-certificate mr5" type="submit" name="cancel_certificate"><i class="fa fa-ban m0"></i></button>
							<button class="btn btn-xs btn-info pull-right edit-cert mr5" type="button" data-toggle="modal" data-target="#modal-'.$key.'"><i class="fa fa-pencil m0"></i></button>';
						if($this->error['modal'] && $this->business['cert_id'] == $key){
							$date_start_error = $this->get_date_start_error();
							$date_end_error = $this->get_date_end_error();
							$name_error = $this->get_name_error();
							$description_error = $this->get_description_error();
							$quantity_error = $this->get_quantity_error();
							$cost_error = $this->get_price_error();
							$currency_error = $this->get_currency_error();
							$image_error = $this->get_image_error();
							$this->business['modal'] = 
							'<script>
								$("#modal-'.$key.'").modal("show");
							</script> ';
						}else{
							$name_error = $description_error = $date_start_error = $date_end_error = $quantity_error =
							$cost_error = $currency_error = 
							$image_error = null;
						}
						$condition_form = _safe($value['condition']);
						$restriction_form = _safe($value['restriction']);
						$date_start = date('d/m/Y g:i A', $start);
						$date_end = date('d/m/Y g:i A', $end);
						$edit_modal = 
							'<div class="modal fade" id="modal-'.$key.'" tabindex="-1" role="dialog" aria-labelledby="label-'.$key.'">
								<div class="modal-dialog modal-lg" role="document">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
											<h4 class="modal-title" id="label-'.$key.'">Editar publicaci&oacute;n</h4>
										</div>
										<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'" enctype="multipart/form-data">
											<div class="modal-body">
												<div class="p30">
													<div class="row">
					<div class="col-md-8">
						<div class="form-group" data-toggle="tooltip" title="Mantenga el nombre corto y objetivo">
							<label for="certificate-name">Nombre del certificado <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
							<input class="form-control" type="text" id="certificate-name" name="name" value="'.$name.'" placeholder="Nombre del certificado" maxlength="255" required/>
							'.$name_error.'
						</div>
						<div class="form-group">
							<label for="certificate-description">Descripci&oacute;n del certificado <span class="required">*</span></label>
							<textarea class="form-control" rows="2" id="certificate-description" name="description" placeholder="Descripci&oacute;n del certificado" required>'.$description.'</textarea>
							'.$description_error.'
						</div>
						<div class="form-group" data-toggle="tooltip" title="Opcional: Puede escribir las condiciones para redimir el certificado">
							<label for="certificate-condition">Condiciones <i class="fa fa-question-circle text-secondary"></i></label>
							<textarea class="form-control" rows="2" id="certificate-condition" name="condition" placeholder="Condiciones">'.$condition_form.'</textarea>
						</div>
						<div class="form-group" data-toggle="tooltip" title="Opcional: Puede escribir las restricciones del certificado">
							<label for="certificate-restriction">Restricciones <i class="fa fa-question-circle text-secondary"></i></label>
							<textarea class="form-control" rows="2" id="certificate-restriction" name="restriction" placeholder="Restricciones">'.$restriction_form.'</textarea>
						</div>
					</div>
					<div class="col-md-4">
						<div class="row">
							<div class="col-sm-6 col-md-12">
								<div class="form-group">
									<label for="start">Fecha y hora de inicio <span class="required">*</span></label>
									<div class="input-group date" id="certificate-start">
										<input class="form-control" type="text" id="start" name="date_start" value="'.$date_start.'" placeholder="Fecha y hora de inicio" required/>
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									</div>
									'.$date_start_error.'
								</div>
							</div>
							<div class="col-sm-6 col-md-12">
								<div class="form-group">
									<label for="end">Fecha y hora de fin <span class="required">*</span></label>
									<div class="input-group date" id="certificate-end">
										<input class="form-control" type="text" id="end" name="date_end" value="'.$date_end.'" placeholder="Fecha y hora de fin" required/>
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									</div>
									'.$date_end_error.'
								</div>
							</div>
							<div class="col-sm-4 col-md-12">
								<div class="form-group" data-toggle="tooltip" title="Ingrese el total de certificados que estar&aacute;n disponibles">
									<label for="certificate-quantity">Disponibles <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
									<input class="form-control" type="number" min="1" id="certificate-quantity" name="quantity" value="'.$total.'" placeholder="Total disponibles" required/>
									'.$quantity_error.'
								</div>
							</div>
							<div class="col-xs-6 col-sm-4 col-md-12">
								<div class="form-group" data-toggle="tooltip" title="Se refiere al valor al p&uacute;blico del certificado">
									<label for="certificate-cost">Precio <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
									<input class="form-control" type="text" id="certificate-cost" name="price" value="'.$cost.'" placeholder="Precio" required/>
									'.$cost_error.'
								</div>
							</div>
							<div class="col-xs-6 col-sm-4 col-md-12">
								<div class="form-group" data-toggle="tooltip" title="La divisa en la que est&aacute; valuado el certificado">
									<label for="certificate-currency">Divisa <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
									<select class="form-control" id="certificate-currency" name="currency" title="Selecciona una divisa" required>
									'.$this->get_currencies($iso).'
									</select>
									'.$currency_error.'
								</div>
							</div>
						</div>
					</div>
					<div class="col-xs-12">
						<div class="form-group">
							<label for="certificate-image">Imagen <span class="required">*</span></label>
							<input type="file" id="certificate-image" name="image" />
							'.$image_error.'
						</div>
					</div>
				</div>
												</div>
											</div>
											<div class="modal-footer">
												<input type="hidden" name="id" value="'.$key.'">
												<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
												<button type="submit" class="btn btn-success" name="edit_certificate">Guardar cambios</button>
											</div>
										</form>
									</div>
								</div>
							</div>';
						}elseif($end > $now){
							$status = '<span class="label btn-xs label-success pull-left mr20">En curso</span>';
							$btns = '<button class="btn btn-xs btn-danger pull-right delete-certificate" type="submit" name="delete_certificate"><i class="fa fa-trash m0"></i></button>
							<button class="btn btn-xs btn-danger pull-right cancel-certificate mr5" type="submit" name="cancel_certificate"><i class="fa fa-ban m0"></i></button>';
						}else{
							$status = '<span class="label btn-xs label-secondary pull-left mr20">Expirado</span>';
						}
					break;
				case 2:
					$status = '<span class="label btn-xs label-primary pull-left mr20">Terminados</span>';
					$btns = '<button class="btn btn-xs btn-danger pull-right delete-certificate" type="submit" name="delete_certificate"><i class="fa fa-trash m0"></i></button>';
					break;
				case 3:
					$status = '<span class="label btn-xs label-danger pull-left mr20">Cancelado</span>';
					$btns = '<button class="btn btn-xs btn-danger pull-right delete-certificate" type="submit" name="delete_certificate"><i class="fa fa-trash m0"></i></button>';
					break;
				default:
					$status = '';
					break;
			}
			if(empty($value['condition'])){
				$condition = 'No tiene.';
			}else{
				$condition = _safe($value['condition']);
			}
			if(empty($value['restriction'])){
				$restriction = 'No tiene.';
			}else{
				$restriction = _safe($value['restriction']);
			}
			$html .= 
			'<div class="background-white p20 mb30">
				<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'">
					<div class="page-title text-default">
						<input type="hidden" name="id" value="'.$key.'">
						'.$status.'
						'.$btns.'
						<p>
							<span class="cert-date">Inicia: '.$date['start'].' &amp; Termina: '.$date['end'].'</span> |
							<span class="cert-date">Disponibles: '.$available.' / '.$total.'</span> | <span class="cert-date"> Valor: '.$price.'</span>
						</p>
					</div>
				</form>
				<div class="row">
					<div class="col-sm-2 ">
						<a href="'.HOST.'/certificado/'.$value['url'].'" target="_blank">
							<img class="img-thumbnail img-rounded" src="'.HOST.'/assets/img/business/certificate/'.$image.'">
						</a>
					</div>
					<div class="col-sm-9">
						<strong class="text-default">'.$name.'</strong>
						<p>'.nl2br($description).'</p>
						<div class="row">
							<div class="col-sm-6">
								<strong class="text-default">Condiciones</strong>
								<p>'.nl2br($condition).'</p>
							</div>
							<div class="col-sm-6">
								<strong class="text-default">Restricciones</strong>
								<p>'.nl2br($restriction).'</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			'.$edit_modal;
		}
		if(is_null($html)){
			$html = '<div class="background-white p30"><h4>No hay certificados de regalo.</h4></div>';
		}
		return $html;
	}

	public function edit_certificate(array $post, array $files){
		if(array_key_exists($post['id'], $this->business['certificates'])){
			$this->business['cert_id'] = $id = $post['id'];
			$this->set_name($post['name'], $id);
			$this->set_description($post['description'], $id);
			$this->set_condition($post['condition'], $id);
			$this->set_restriction($post['restriction'], $id);
			$this->set_currency($post['currency'], $id);
			$this->set_price($post['price'], $id);
			$this->set_quantity($post['quantity'], $id);
			$this->set_date_start($post['date_start'], $id);
			$this->set_date_end($post['date_end'], $id);
			$this->set_image($files, $id);
			if(!array_filter($this->error)){
				if($this->business['image']['tmp_name'] && $this->business['image']['path']){
					if(file_exists(ROOT.'/assets/img/business/certificate/'.$this->business['certificates'][$id]['image'])){
						unlink(ROOT.'/assets/img/business/certificate/'.$this->business['certificates'][$id]['image']);
					}
					if(!move_uploaded_file($this->business['image']['tmp_name'], $this->business['image']['path'])){
						$this->error['error'] = 'El certificado no se ha podido editar correctamente.';
						return false;
					}
					$file_name = $this->business['image']['file_name'];
				}else{
					$file_name = $this->business['certificates'][$id]['image'];
				}
				$query = "UPDATE negocio_certificado SET 
					nombre = :nombre,
					descripcion = :descripcion,
					condiciones = :condiciones,
					restricciones = :restricciones,
					fecha_inicio = :fecha_inicio,
					fecha_fin = :fecha_fin,
					precio = :precio,
					iso = :iso,
					disponibles = :disponibles,
					imagen = :imagen
					WHERE id_negocio = :id_negocio AND id_certificado = :id_certificado";
				$params = array(
					':nombre' => $this->business['certificates'][$id]['name'],
					':descripcion' => $this->business['certificates'][$id]['description'],
					':condiciones' => $this->business['certificates'][$id]['condition'],
					':restricciones' => $this->business['certificates'][$id]['restriction'],
					':fecha_inicio' => $this->business['certificates'][$id]['date_start'],
					':fecha_fin' => $this->business['certificates'][$id]['date_end'],
					':precio' => $this->business['certificates'][$id]['price'],
					':iso' => $this->business['certificates'][$id]['currency'],
					':disponibles' => $this->business['certificates'][$id]['quantity'],
					':imagen' => $file_name,
					':id_negocio' => $this->business['id'],
					':id_certificado' => $id
				);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				$_SESSION['notification']['success'] = 'Certificado de regalo editado correctamente.';
				header('Location: '._safe($_SERVER['REQUEST_URI']));
				die();
			}
			$this->error['warning'] = 'Uno o más campos tienen errores. Revísalos cudiadosamente.';
			$this->error['modal'] = true;
			return false;
		}
		return;
	}

	private function set_date_start($datetime = null, $id){
		if($datetime){
			$datetime = str_replace('/', '-', $datetime);
			$datetime = strtotime($datetime);
			if(!$datetime){
				$this->error['date_start'] = 'Formato de fecha y hora incorrecto. Utiliza la herramienta.';
				return false;
			}
			$datetime = date("Y/m/d H:i:s", $datetime);
			$this->business['certificates'][$id]['date_start'] = $datetime;
			return true;
		}
		$this->error['date_start'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_date_end($datetime = null, $id){
		if($datetime){
			$datetime = str_replace('/', '-', $datetime);
			$datetime = strtotime($datetime);
			if(!$datetime){
				$this->error['date_end'] = 'Formato de fecha y hora incorrecto. Utiliza la herramienta.';
				return false;
			}
			$datetime = date("Y/m/d H:i:s", $datetime);
			$this->business['certificates'][$id]['date_end'] = $datetime;
			return true;
		}
		$this->error['date_end'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_name($string = null, $id){
		if($string){
			$string = trim($string);
			$this->business['certificates'][$id]['name'] = $string;
			return true;
		}
		$this->error['name'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_description($string = null, $id){
		if($string){
			$string = trim($string);
			$this->business['certificates'][$id]['description'] = $string;
			return true;
		}
		$this->error['description'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_condition($string = null, $id){
		if($string){
			$string = trim($string);
			$this->business['certificates'][$id]['condition'] = $string;
			return true;
		}
		return false;
	}

	private function set_restriction($string = null, $id){
		if($string){
			$string = trim($string);
			$this->business['certificates'][$id]['restriction'] = $string;
			return true;
		}
		return false;
	}

	private function set_quantity($quantity = null, $id){
		if($quantity){
			$quantity = filter_var($quantity, FILTER_VALIDATE_INT);
			if(!$quantity){
				$this->error['quantity'] = 'Este campo es obligatorio.';
				return false;
			}
			$this->business['certificates'][$id]['quantity'] = $quantity;
			return;
		}
		$this->error['quantity'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_price($cost = null, $id){
		if($cost){
			$cost = filter_var($cost, FILTER_VALIDATE_FLOAT);
			if(!$cost){
				$this->error['price'] = 'Este campo es obligatorio.';
				return false;
			}
			$this->business['certificates'][$id]['price'] = $cost;
			return;
		}
		$this->error['price'] = 'Este campo es obligatorio.';
		return $this;
	}

	private function set_currency($currency = null, $id){
		if($currency){
			if(strlen($currency)!=3){
				$this->error['currency'] = 'Este campo es obligatorio..';
				return $this;
			}
			$this->business['certificates'][$id]['currency'] = $currency;
			return $this;
		}
		$this->error['currency'] = 'Este campo es obligatorio..';
		return $this;
	}

	private function set_image($files = null, $id){
		if(!$this->business['certificates'][$id]['name']){
			return;
		}
		// RECORTAR NOMBRE DE IMAGEN
		$image_prefix = _safe('-'.$this->business['url'].'-certificado-esmart-club');
		$max = 150 - strlen($image_prefix);
		$safe_name = $this->friendly_url($this->business['certificates'][$id]['name']);
		if(strlen($safe_name) > $max){
			$file = substr($safe_name, 0, $max);
		}else{
			$file = $safe_name;
		}
		$file_name = $file.$image_prefix;
		$image = new \assets\libraries\bulletproof\bulletproof($files);
		$image->setName($file_name);
		$image->setLocation(ROOT.'/assets/img/business/certificate');
		if($image['image']){
			if($image->upload()){
				// REVISAR QUE SEA UNICA
				try{
					$query = "SELECT 1 FROM negocio_certificado WHERE imagen = :imagen";
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(':imagen', $image->getName().'.'.$image->getMime(), PDO::PARAM_STR);
					$stmt->execute();
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				if($row = $stmt->fetch()){
					$image->setName($image->getName().'-'.time()); // AGREGAR EL TIEMPO SI NO LO ES
				}
				$this->business['image']['tmp_name'] = $files['image']['tmp_name'];
				$this->business['image']['file_name'] = $image->getName().'.'.$image->getMime();
				$this->business['image']['path'] = $image->getFullPath();
				return true;
			}
			$this->error['image'] = $image['error'];
			return false;
		}
		if($files['image']['error'] == 1){
			$this->error['image'] = 'Has excedido el límite de imagen de 2MB.';
		}
		return;
	}

	public function get_currencies($iso){
		$html = null;
		foreach ($this->currencies as $key => $value) {
			if($iso == $key){
				$html .= '<option value="'.$key.'" selected>'.$value.'</option>';
			}else{
				$html .= '<option value="'.$key.'">'.$value.'</option>';
			}
		}
		return $html;
	}

	public function get_date_start_error(){
		if($this->error['date_start']){
			return '<p class="text-danger">'._safe($this->error['date_start']).'</p>';
		}
	}

	public function get_date_end_error(){
		if($this->error['date_end']){
			return '<p class="text-danger">'._safe($this->error['date_end']).'</p>';
		}
	}

	public function get_name_error(){
		if($this->error['name']){
			return '<p class="text-danger">'._safe($this->error['name']).'</p>';
		}
	}

	public function get_description_error(){
		if($this->error['description']){
			return '<p class="text-danger">'._safe($this->error['description']).'</p>';
		}
	}

	public function get_quantity_error(){
		if($this->error['quantity']){
			return '<p class="text-danger">'._safe($this->error['quantity']).'</p>';
		}
	}

	public function get_price_error(){
		if($this->error['price']){
			return '<p class="text-danger">'._safe($this->error['price']).'</p>';
		}
	}

	public function get_currency_error(){
		if($this->error['currency']){
			return '<p class="text-danger">'._safe($this->error['currency']).'</p>';
		}
	}

	public function get_image_error(){
		if($this->error['image']){
			return '<p class="text-danger">'._safe($this->error['image']).'</p>';
		}
	}

	public function show_modal(){
		return $this->business['modal'];
	}

	public function get_profile_url(){
		return HOST.'/'._safe($this->business['url']);
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
		file_put_contents(ROOT.'\assets\error_logs\certificates_list.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}

	private function friendly_url($url){
		// 1. No spaces to begin or end, replace all special characters and lowercase everything
		$url = strtolower($this->replace_accents(trim($url)));
		// decode html maybe needed if there's html I normally don't use this
		//$url = html_entity_decode($url,ENT_QUOTES,'UTF8');
		// 2. Replacing spaces and union characters with -
		$find = array(' ', '&', '\r\n', '\n', '+',',');
		$url = str_replace($find, '-', $url);
		// 3. Delete and replace the rest of special characters
		$find = array('/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/');
		$repl = array('', ' ', '');
		$url = str_replace(' ','-',trim(preg_replace($find, $repl, $url)));
		return $url;
	}

	private function replace_accents($var){
		$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
		$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
		$var = str_replace($a, $b, $var);
		return $var;
	}
}
?>