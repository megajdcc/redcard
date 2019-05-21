<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace admin\libs;
use assets\libs\connection;
use PDO;

class product_list {
	private $con;
	private $products = array();
	private $categories = array();
	private $edit = array(
		'id' => null,
		'image' => array('tmp_name' => null, 'file_name' => null, 'path' => null),
		'coupon' => array('tmp_name' => null, 'file_name' => null, 'path' => null),
		'modal' => null
	);
	private $error = array(
		'warning' => null,
		'error' => null,
		'modal' => false,
		'image' => null,
		'coupon' => null,
		'name' => null,
		'description' => null,
		'price' => null,
		'quantity' => null,
		'send_price' => null,
		'conditions' => null,
		'category' => null
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

	public function load_data($page = null, $rpp = null){
		$query = "SELECT id_categoria, categoria FROM producto_categoria";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row =  $stmt->fetch()){
			$this->categories[$row['id_categoria']] = $row['categoria'];
		}
		$query = "SELECT COUNT(*) FROM producto WHERE situacion != 0";
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
			$query = "SELECT id_producto, nombre, descripcion, p.id_categoria, categoria, precio, disponibles, envio, condiciones, imagen, cupon, situacion, p.creado, (SELECT COUNT(*) FROM venta_tienda vt WHERE vt.id_producto = p.id_producto) as usados
				FROM producto p
				INNER JOIN producto_categoria pc ON p.id_categoria = pc.id_categoria
				WHERE situacion != 0
				ORDER BY p.creado ASC
				LIMIT :limit OFFSET :offset";
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
				$this->products[$row['id_producto']] = array(
					'name' => $row['nombre'],
					'description' => $row['descripcion'],
					'category' => $row['categoria'],
					'category_id' => $row['id_categoria'],
					'price' => $row['precio'],
					'available' => $row['disponibles'],
					'image' => $row['imagen'],
					'coupon' => $row['cupon'],
					'send_price' => $row['envio'],
					'conditions' => $row['condiciones'],
					'status' => $row['situacion'],
					'created_at' => $row['creado'],
					'used' => $row['usados']
				);
			}
		return $pagination;
		}
		return false;
	}

	public function get_products_pdf(){
		$query = "SELECT id_producto, nombre, categoria, precio, disponibles, envio, situacion, p.creado, (SELECT COUNT(*) FROM venta_tienda vt WHERE vt.id_producto = p.id_producto) as usados
				FROM producto p
				INNER JOIN producto_categoria pc ON p.id_categoria = pc.id_categoria
				WHERE situacion != 0
				ORDER BY p.creado ASC";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$rows = null;
		while($row = $stmt->fetch()){
			$id = $row['id_producto'];
			if($row['situacion'] == 1){
				$status = 'Disponibles';
			}elseif($row['situacion'] == 2){
				$status = 'Terminados';
			}elseif($row['situacion'] == 3){
				$status = 'Suspendido';
			}else{
				$status = '';
			}
			$name = _safe($row['nombre']);
			$category = _safe($row['categoria']);
			$price = number_format((float)$row['precio'], 2, '.', '');
			$available = _safe($row['disponibles']);
			$used = _safe($row['usados']);
			$left = $available-$used;
			$send = number_format((float)$row['envio'], 2, '.', '');
			$date = date('d/m/Y g:i A', strtotime($row['creado']));

			$rows .= 
				'<tr>
					<td>'.$id.'</td>
					<td>'.$status.'</td>
					<td>'.$name.'</td>
					<td>'.$category.'</td>
					<td>e$'.$price.'</td>
					<td>'.$left.' / '.$available.'</td>
					<td>'.$send.'</td>
					<td>'.$date.'</td>
				</tr>';
		}
		$html = 
'<style type="text/css">
	#cabecera{
		background:#f7f8f9;
		padding:10px 20px;
		border-radius: 6px;
	}
	h1,h2{
		float:left;
		margin: 5px;
	}
	table {
		width: 100%;
		border-spacing: 0;
		border-collapse: collapse;
		padding: 8px;
	}
	.table-bordered, th, td{
		border: 1px solid #ddd;
		padding: 5px;
	}

</style>
<page style="font-size: 12px">
	<div id="cabecera">
		<h1>Travel Points</h1>
		<h2>Reporte de productos en tienda</h2>
	</div>
	<table class="table-bordered">
		<thead>
			<tr>
				<th>#</th>
				<th>Situacion</th>
				<th>Nombre</th>
				<th>Categor&iacute;a</th>
				<th>Precio</th>
				<th>Disponibles</th>
				<th>Env&iacute;o</th>
				<th>Registrado</th>
			</tr>
		</thead>
		<tbody>
		'.$rows.'
		</tbody>
	</table>
</page>';

		require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/vendor/autoload.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/vendor/spipu/html2pdf/html2pdf.class.php';
		$html2pdf = new \HTML2PDF('P','A4','es');
		$html2pdf->WriteHTML($html);
		$html2pdf->Output('reporte.pdf');
		return;
	}

	public function get_products(){
		$products = $modals = null;
		foreach ($this->products as $key => $value) {
			$image = HOST.'/assets/img/store/'._safe($value['image']);
			if($value['coupon']){
				$coupon = HOST.'/assets/img/store/coupon/'._safe($value['coupon']);
				$coupon = '<div class="user user-md detail-gallery-preview">
							<a href="'.$coupon.'"><img class="low-border" src="'.$coupon.'"></a>
						</div>';
			}else{
				$coupon = '';
			}
			
			if($value['status'] == 1){
				$status = '<span class="label label-sm label-success">Disponibles</span>';
				$btn = 
				'<button class="btn btn-xs btn-info mr5" type="button" data-toggle="modal" data-target="#modal-'.$key.'"><i class="fa fa-pencil m0"></i></button><button class="btn btn-xs btn-danger mr5" type="submit" name="cancel_product" value="'.$key.'"><i class="fa fa-ban m0"></i></button>';
			}elseif($value['status'] == 2){
				$status = '<span class="label label-sm label-primary">Terminados</span>';
				$btn = '<button class="btn btn-xs btn-info mr5" type="button" data-toggle="modal" data-target="#modal-'.$key.'"><i class="fa fa-pencil m0"></i>';
			}elseif($value['status'] == 3){
				$status = '<span class="label label-sm label-danger">Suspendido</span>';
				$btn = '<button class="btn btn-xs btn-info mr5" type="button" data-toggle="modal" data-target="#modal-'.$key.'"><i class="fa fa-pencil m0"></i></button><button class="btn btn-xs btn-success mr5" type="submit" name="activate_product" value="'.$key.'"><i class="fa fa-arrow-circle-up m0"></i></button>';
			}else{
				$status = '';
			}
			$btn .= '<button class="btn btn-xs btn-default delete-product" type="submit" name="delete_product" value="'.$key.'"><i class="fa fa-trash m0"></i></button>';
			$name = _safe($value['name']);
			$category = _safe($value['category']);
			$description = _safe($value['description']);
			$available = _safe($value['available']);
			$used = _safe($value['used']);
			$left = $available-$used;
			$balance = number_format((float)$value['price'], 2, '.', '');
			$date = date('d/m/Y', strtotime($value['created_at']));
			if($value['send_price']){
				$send_price = number_format((float)$value['send_price'], 2, '.', '');
			}else{
				$send_price = '';
			}
			$conditions = _safe($value['conditions']);
			if($this->error['modal'] && $this->edit['id'] == $key){
				$image_error = $this->get_image_error();
				$name_error = $this->get_name_error();
				$description_error = $this->get_description_error();
				$category_error = $this->get_category_error();
				$quantity_error = $this->get_quantity_error();
				$price_error = $this->get_price_error();
				$send_price_error = $this->get_send_price_error();
				$conditions_error = $this->get_conditions_error();
				$coupon_error = $this->get_coupon_error();
				$this->edit['modal'] = 
				'<script>
					$("#modal-'.$key.'").modal("show");
				</script> ';
			}else{
				$image_error = $name_error = $description_error = $category_error = $quantity_error = $price_error = $send_price_error = $conditions_error = $coupon_error = null;
			}
			$products .= 
				'<tr>
					<td><form method="post" action="'._safe($_SERVER['REQUEST_URI']).'">'.$btn.'</form></td>
					<td>'.$key.'</td>
					<td>'.$status.'</td>
					<td>
						<div class="user user-md">
							<a href="'.HOST.'/tienda/producto/'.$key.'" target="_blank"><img class="low-border" src="'.$image.'"></a>
						</div>
					</td>
					<td>'.$name.'</td>
					<td>'.$category.'</td>
					<td>Tp$ '.$balance.'</td>
					<td>'.$left.' / '.$available.'</td>
					<td>'.$send_price.'</td>
					<td><button class="btn btn-xs btn-info mr5" type="button" data-toggle="modal" data-target="#modal-'.$key.'">Ver</button></td>
					<td>
						'.$coupon.'
					</td>
					<td>'.$date.'</td>
				</tr>';
			$modals .= 
				'<div class="modal fade" id="modal-'.$key.'" tabindex="-1" role="dialog" aria-labelledby="label-'.$key.'">
					<div class="modal-dialog modal-lg" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title" id="label-'.$key.'">Editar producto</h4>
							</div>
							<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'" enctype="multipart/form-data">
								<div class="modal-body">
									<div class="p30">
										<div class="form-group" data-toggle="tooltip" title="Puedes actualizar la imagen o dejar el campo en blanco para conservar la actual">
											<label for="post-image">Foto <i class="fa fa-question-circle text-secondary"></i></label>
											<input type="file" id="post-image" name="image">
											'.$image_error.'
										</div><!-- /.form-group -->
										<div class="form-group">
											<label for="name">Nombre del producto <span class="required">*</span></label>
											<input class="form-control" type="text" id="name" name="name" value="'.$name.'" placeholder="Nombre del producto"/ required>
											'.$name_error.'
										</div><!-- /.form-group -->
										<div class="row">
											<div class="col-sm-8 col-md-4">
												<div class="form-group">
													<label for="price">Precio en Travel Points <span class="required">*</span></label>
													<div class="input-group">
														<span class="input-group-addon">Tp$</span>
														<input class="form-control" type="text" id="price" name="price" value="'.$balance.'" placeholder="Precio en TravelPoints" required>
													</div>
													'.$price_error.'
												</div><!-- /.form-group -->
											</div>
											<div class="col-sm-4 col-md-2">
												<div class="form-group">
													<label for="quantity">Disponibles <span class="required">*</span></label>
													<input class="form-control" type="number" id="quantity" name="quantity" value="'.$available.'" placeholder="Disponibles" min="'.$used.'" max="99999" required>
													'.$quantity_error.'
												</div><!-- /.form-group -->
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="category">Categor&iacute;a del producto <span class="required">*</span></label>
													<select class="form-control" id="category" name="category" title="Selecciona una categor&iacute;a" required>
														'.$this->get_categories($key).'
													</select>
													'.$category_error.'
												</div><!-- /.form-group -->
											</div>
										</div>
										<div class="form-group">
											<label for="description">Descripci&oacute;n <span class="required">*</span></label>
											<textarea class="form-control" id="description" name="description" rows="5" placeholder="Descripci&oacute;n&hellip;" required>'.$description.'</textarea>
											'.$description_error.'
										</div><!-- /.form-group -->
										<hr>
										<h4>Detalles de Env&iacute;o</h4>
										<div class="row">
											<div class="col-sm-12 col-md-6">
												<div class="form-group">
													<label for="send-price">Precio de env&iacute;o</label>
													<div class="input-group">
														<span class="input-group-addon">$</span>
														<input class="form-control" type="text" id="send-price" name="send_price" value="'.$send_price.'" placeholder="Precio de env&iacute;o"/>
													</div>
													'.$send_price_error.'
												</div><!-- /.form-group -->
											</div>
										</div>
										<div class="form-group">
											<label for="conditions">Condiciones de env&iacute;o</label>
											<textarea class="form-control" id="conditions" name="conditions" rows="5" placeholder="Condiciones de env&iacute;o">'.$conditions.'</textarea>
											'.$conditions_error.'
										</div><!-- /.form-group -->
										<hr>
										<h4>Certificado del servicio</h4>
										<div class="form-group">
											<label for="event-image">Imagen</label>
											<input type="file" id="event-image" name="coupon" />
											'.$coupon_error.'
										</div><!-- /.form-group -->
									</div>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
									<button type="submit" class="btn btn-success" name="edit_product" value="'.$key.'">Guardar cambios</button>
								</div>
							</form>
						</div>
					</div>
				</div>';
		}
		$html = 
			'<div class="table-responsive">
				<table class="table table-hover">
					<thead>
					<tr>
						<th></th>
						<th>#</th>
						<th>Situacion</th>
						<th>Imagen</th>
						<th>Nombre</th>
						<th>Categor&iacute;a</th>
						<th>Precio</th>
						<th>Disponibles</th>
						<th>Env&iacute;o</th>
						<th>Condiciones</th>
						<th>Cup&oacute;n</th>
						<th>Registrado</th>
					</tr>
					</thead>
					<tbody>
					'.$products.'
					</tbody>
				</table>
			</div>'.$modals;
		return $html;
	}

	public function edit_product(array $post, array $files){
		if(array_key_exists($post['edit_product'], $this->products)){
			$id = $this->edit['id'] = $post['edit_product'];
			$this->set_name($id, $post['name']);
			$this->set_description($id, $post['description']);
			$this->set_category($id, $post['category']);
			$this->set_price($id, $post['price']);
			$this->set_quantity($id, $post['quantity']);
			$this->set_send_price($id, $post['send_price']);
			$this->set_conditions($id, $post['conditions']);
			$this->set_image($id, $files);
			$this->set_coupon($id, $files);
			if(!array_filter($this->error)){
				if($this->edit['image']['tmp_name'] && $this->edit['image']['path']){
					if(file_exists(ROOT.'/assets/img/store/'.$this->products[$id]['image'])){
						unlink(ROOT.'/assets/img/store/'.$this->products[$id]['image']);
					}
					if(!move_uploaded_file($this->edit['image']['tmp_name'], $this->edit['image']['path'])){
						$this->error['error'] = 'El producto no se ha podido editar correctamente.';
						return false;
					}
					$file_name = $this->edit['image']['file_name'];
				}else{
					$file_name = $this->products[$id]['image'];
				}
				if($this->products[$id]['category_id'] == 2 && $this->edit['coupon']['tmp_name'] && $this->edit['coupon']['path']){
					if(file_exists(ROOT.'/assets/img/store/coupon/'.$this->products[$id]['coupon'])){
						unlink(ROOT.'/assets/img/store/coupon/'.$this->products[$id]['coupon']);
					}
					if(!move_uploaded_file($this->edit['coupon']['tmp_name'], $this->edit['coupon']['path'])){
						$this->error['error'] = 'El producto no se ha podido editar correctamente.';
						return false;
					}
					$coupon_name = $this->edit['coupon']['file_name'];
				}else{
					$coupon_name = $this->products[$id]['coupon'];
				}
				if($this->products[$id]['status'] == 3){
					$status = 3;
				}elseif($this->products[$id]['available'] > $this->products[$id]['used']){
					$status = 1;
				}else{
					$status = 2;
				}
				$query = "UPDATE producto SET 
					nombre = :nombre,
					descripcion = :descripcion,
					id_categoria = :id_categoria,
					precio = :precio,
					disponibles = :disponibles,
					envio = :envio,
					condiciones = :condiciones,
					imagen = :imagen,
					cupon = :cupon,
					situacion = :situacion
					WHERE id_producto = :id_producto";
				$params = array(
					':nombre' => $this->products[$id]['name'],
					':descripcion' => $this->products[$id]['description'],
					':id_categoria' => $this->products[$id]['category_id'],
					':precio' => $this->products[$id]['price'],
					':disponibles' => $this->products[$id]['available'],
					':envio' => $this->products[$id]['send_price'],
					':condiciones' => $this->products[$id]['conditions'],
					':imagen' => $file_name,
					':cupon' => $coupon_name,
					':situacion' => $status,
					':id_producto' => $id
				);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				$_SESSION['notification']['success'] = 'Producto editado correctamente.';
				header('Location: '._safe($_SERVER['REQUEST_URI']));
				die();
			}
			$this->error['warning'] = 'Uno o más campos tienen errores. Revísalos cudiadosamente.';
			$this->error['modal'] = true;
			return false;
		}
		return;
	}

	public function delete_product(array $post){
		if(!array_key_exists($post['delete_product'], $this->products)){
			$this->error['error'] = 'Error al tratar de eliminar el producto.';
			return false;
		}else{
			$id = (int)$post['delete_product'];
		}
		$query = "UPDATE producto SET situacion = 0 WHERE id_producto = :id_producto";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_producto', $id, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$_SESSION['notification']['success'] = 'Producto eliminado exitosamente.';
		header('Location: '.HOST.'/admin/tienda/');
		die();
		return;
	}

	public function cancel_product(array $post){
		if(!array_key_exists($post['cancel_product'], $this->products)){
			$this->error['error'] = 'Error al tratar de suspender el producto.';
			return false;
		}else{
			$id = (int)$post['cancel_product'];
		}
		$query = "UPDATE producto SET situacion = 3 WHERE id_producto = :id_producto";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_producto', $id, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$_SESSION['notification']['success'] = 'Producto suspendido exitosamente.';
		header('Location: '.HOST.'/admin/tienda/');
		die();
		return;
	}

	public function activate_product(array $post){
		if(!array_key_exists($post['activate_product'], $this->products)){
			$this->error['error'] = 'Error al tratar de activar el producto.';
			return false;
		}else{
			$id = (int)$post['activate_product'];
		}
		$query = "SELECT COUNT(id_venta) as ventas FROM venta_tienda WHERE id_producto = :id_producto";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_producto', $id, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			if($row['ventas'] >= $this->products[$id]['available']){
				$status = 2;
			}else{
				$status = 1;
			}
		}
		$query = "UPDATE producto SET situacion = $status WHERE id_producto = :id_producto";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_producto', $id, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$_SESSION['notification']['success'] = 'Producto activado exitosamente.';
		header('Location: '.HOST.'/admin/tienda/');
		die();
		return;
	}

	private function set_image($id, $files = null){
		if(!$this->products[$id]['name']){
			return;
		}
		// RECORTAR NOMBRE DE IMAGEN
		$image_prefix = _safe('-producto-esmart-club');
		$max = 150 - strlen($image_prefix);
		$safe_name = $this->friendly_url($this->products[$id]['name']);
		if(strlen($safe_name) > $max){
			$file = substr($safe_name, 0, $max);
		}else{
			$file = $safe_name;
		}
		$file_name = $file.$image_prefix;
		$image = new \assets\libraries\bulletproof\bulletproof($files);
		$image->setName($file_name);
		$image->setLocation(ROOT.'/assets/img/store');
		if($image['image']){
			if($image->upload()){
				// REVISAR QUE SEA UNICA
				try{
					$query = "SELECT 1 FROM producto WHERE imagen = :imagen";
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
				$this->edit['image']['tmp_name'] = $files['image']['tmp_name'];
				$this->edit['image']['file_name'] = $image->getName().'.'.$image->getMime();
				$this->edit['image']['path'] = $image->getFullPath();
				return true;
			}
			$this->error['image'] = $image['error'];
			return false;
		}
		if($files['image']['error'] == 1){
			$this->error['image'] = 'Has excedido el límite de imagen de 2MB.';
		}
		// $this->error['image'] = 'Este campo es obligatorio.';
		return;
	}

	private function set_coupon($id, $files = null){
		if(!$this->products[$id]['name']){
			return;
		}
		// RECORTAR NOMBRE DE IMAGEN
		$image_prefix = _safe('-cupon-esmart-club');
		$max = 150 - strlen($image_prefix);
		$safe_name = $this->friendly_url($this->products[$id]['name']);
		if(strlen($safe_name) > $max){
			$file = substr($safe_name, 0, $max);
		}else{
			$file = $safe_name;
		}
		$file_name = $file.$image_prefix;
		$image = new \assets\libraries\bulletproof\bulletproof($files);
		$image->setName($file_name);
		$image->setLocation(ROOT.'/assets/img/store/coupon');
		if($image['coupon']){
			if($image->upload()){
				// REVISAR QUE SEA UNICA
				try{
					$query = "SELECT 1 FROM producto WHERE cupon = :cupon";
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(':cupon', $image->getName().'.'.$image->getMime(), PDO::PARAM_STR);
					$stmt->execute();
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				if($row = $stmt->fetch()){
					$image->setName($image->getName().'-'.time()); // AGREGAR EL TIEMPO SI NO LO ES
				}
				$this->edit['coupon']['tmp_name'] = $files['coupon']['tmp_name'];
				$this->edit['coupon']['file_name'] = $image->getName().'.'.$image->getMime();
				$this->edit['coupon']['path'] = $image->getFullPath();
				return true;
			}
			$this->error['coupon'] = $image['error'];
			return false;
		}
		if($files['coupon']['error'] == 1){
			$this->error['coupon'] = 'Has excedido el límite de imagen de 2MB.';
		}
		// $this->error['image'] = 'Este campo es obligatorio.';
		return;
	}

	private function set_name($id, $string = null){
		if($string){
			$string = trim($string);
			$this->products[$id]['name'] = $string;
			return true;
		}
		$this->error['name'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_description($id, $string = null){
		if($string){
			$string = trim($string);
			$this->products[$id]['description'] = $string;
			return true;
		}
		$this->error['description'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_category($id, $string = null){
		if(array_key_exists($string, $this->categories)){
			$this->products[$id]['category_id'] = $string;
			return true;
		}
		$this->error['category'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_price($id, $string = null){
		if($string){
			$price = filter_var($string, FILTER_VALIDATE_FLOAT);
			if(!$price){
				$this->error['price'] = 'El formato debe ser un número sin signos o comas. Ejemplo: 25.95';
				return false;
			}
			$this->products[$id]['price'] = $price;
			return true;
		}
		$this->error['price'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_quantity($id, $string = null){
		if($string){
			$qty = filter_var($string, FILTER_VALIDATE_INT);
			if(!$qty){
				$this->error['quantity'] = 'El formato debe ser un número entero sin signos, puntos o comas. Ejemplo: 15.';
				return false;
			}
			$this->products[$id]['available'] = $qty;
			return true;
		}
		$this->error['quantity'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_send_price($id, $string = null){
		if($string){
			$price = filter_var($string, FILTER_VALIDATE_FLOAT);
			if(!$price){
				$this->error['send_price'] = 'El formato debe ser un número sin signos o comas. Ejemplo: 25.95';
				return false;
			}
			$this->products[$id]['send_price'] = $price;
			return true;
		}
		$this->products[$id]['send_price'] = null;
		return false;
	}

	private function set_conditions($id, $string = null){
		if($string){
			$string = trim($string);
			$this->products[$id]['conditions'] = $string;
			return true;
		}
		$this->products[$id]['conditions'] = null;
		return false;
	}

	public function get_name_error(){
		if($this->error['name']){
			$error = '<p class="text-danger">'.$this->error['name'].'</p>';
			return $error;
		}
	}

	public function get_description_error(){
		if($this->error['description']){
			$error = '<p class="text-danger">'.$this->error['description'].'</p>';
			return $error;
		}
	}

	public function get_image_error(){
		if($this->error['image']){
			$error = '<p class="text-danger">'.$this->error['image'].'</p>';
			return $error;
		}
	}

	public function get_categories($id){
		$html = null;
		foreach ($this->categories as $key => $value) {
			if($this->products[$id]['category_id'] == $key){
				$html .= '<option value="'.$key.'" selected>'.$value.'</option>';
			}else{
				$html .= '<option value="'.$key.'">'.$value.'</option>';
			}
		}
		return $html;
	}

	public function get_category_error(){
		if($this->error['category']){
			$error = '<p class="text-danger">'.$this->error['category'].'</p>';
			return $error;
		}
	}

	public function get_price_error(){
		if($this->error['price']){
			$error = '<p class="text-danger">'.$this->error['price'].'</p>';
			return $error;
		}
	}

	public function get_quantity_error(){
		if($this->error['quantity']){
			$error = '<p class="text-danger">'.$this->error['quantity'].'</p>';
			return $error;
		}
	}

	public function get_send_price_error(){
		if($this->error['send_price']){
			$error = '<p class="text-danger">'.$this->error['send_price'].'</p>';
			return $error;
		}
	}

	public function get_conditions_error(){
		if($this->error['conditions']){
			$error = '<p class="text-danger">'.$this->error['conditions'].'</p>';
			return $error;
		}
	}

	public function get_coupon_error(){
		if($this->error['coupon']){
			$error = '<p class="text-danger">'.$this->error['coupon'].'</p>';
			return $error;
		}
	}

	public function show_modal(){
		return $this->edit['modal'];
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
		file_put_contents(ROOT.'\assets\error_logs\product_list.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
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