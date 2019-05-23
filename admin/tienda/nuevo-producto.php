<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if($_SESSION['user']['id_rol'] != 1 && $_SESSION['user']['id_rol'] != 2 && $_SESSION['user']['id_rol'] != 9){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if(!isset($_SESSION['user']['admin_authorize'])){
	header('Location: '.HOST.'/admin/acceso');
	die();
}

$product = new admin\libs\new_product($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['new_product'])){
		$product->new_product($_POST, $_FILES);
	}
}

$includes = new admin\libs\includes($con);
$properties['title'] = 'Nuevo producto | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $product->get_notification();?>
		<div class="background-white p20 mb30">
			<a href="<?php echo HOST.'/tienda/'; ?>" target="_blank">Ver tienda</a>
		</div><!-- /.box -->
		<form method="post" action="<?php echo _safe(HOST.'/admin/tienda/nuevo-producto');?>" enctype="multipart/form-data">
			<div class="background-white p30 mb30">
				<div class="page-title">
					<h4>Nuevo producto</h4>
				</div>
				<div class="form-group">
					<label for="post-image">Foto</label>
					<input type="file" id="post-image" name="image" />
					<?php echo $product->get_image_error();?>
				</div><!-- /.form-group -->
				<div class="form-group">
					<label for="name">Nombre del producto <span class="required">*</span></label>
					<input class="form-control" type="text" id="name" name="name" value="<?php echo $product->get_name();?>" placeholder="Nombre del producto"/ required>
					<?php echo $product->get_name_error();?>
				</div><!-- /.form-group -->
				<div class="row">
					<div class="col-sm-8 col-md-4">
						<div class="form-group">
							<label for="price">Precio en Travel Points <span class="required">*</span></label>
							<div class="input-group">
								<span class="input-group-addon">Tp$</span>
								<input class="form-control" type="text" id="price" name="price" value="<?php echo $product->get_price();?>" placeholder="Precio en TravelPoints"/ required>
							</div>
							<?php echo $product->get_price_error();?>
						</div><!-- /.form-group -->
					</div>
					<div class="col-sm-4 col-md-2">
						<div class="form-group">
							<label for="quantity">Disponibles <span class="required">*</span></label>
							<input class="form-control" type="number" id="quantity" name="quantity" value="<?php echo $product->get_quantity();?>" placeholder="Disponibles"/ min="1" max="99999" required>
							<?php echo $product->get_quantity_error();?>
						</div><!-- /.form-group -->
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="category">Categor&iacute;a del producto <span class="required">*</span></label>
							<select class="form-control" id="category" name="category" title="Seleccionar categor&iacute;a" required>
								<?php echo $product->get_categories();?>
							</select>
							<?php echo $product->get_category_error();?>
						</div><!-- /.form-group -->
					</div>
				</div>
				<div class="form-group">
					<label for="description">Descripci&oacute;n <span class="required">*</span></label>
					<textarea class="form-control" id="description" name="description" rows="5" placeholder="Descripci&oacute;n&hellip;" required><?php echo $product->get_description();?></textarea>
					<?php echo $product->get_description_error();?>
				</div><!-- /.form-group -->
				<hr>
				<h4>Detalles de Env&iacute;o</h4>
				<div class="row">
					<div class="col-sm-12 col-md-6">
						<div class="form-group">
							<label for="send-price">Precio de env&iacute;o</label>
							<div class="input-group">
								<span class="input-group-addon">$</span>
								<input class="form-control" type="text" id="send-price" name="send_price" value="<?php echo $product->get_send_price();?>" placeholder="Precio de env&iacute;o"/>
							</div>
							<?php echo $product->get_send_price_error();?>
						</div><!-- /.form-group -->
					</div>
				</div>
				<div class="form-group">
					<label for="conditions">Condiciones de env&iacute;o</label>
					<textarea class="form-control" id="conditions" name="conditions" rows="5" placeholder="Condiciones de env&iacute;o"><?php echo $product->get_conditions();?></textarea>
					<?php echo $product->get_conditions_error();?>
				</div><!-- /.form-group -->
				<hr>
				<h4>Certificado del servicio</h4>
				<div class="form-group">
					<label for="event-image">Imagen</label>
					<input type="file" id="event-image" name="coupon" />
					<?php echo $product->get_coupon_error();?>
				</div><!-- /.form-group -->
				<div class="form-group">
					<button class="btn btn-success" type="submit" name="new_product">Agregar producto</button>
				</div>
			</div>
		</form>
	</div>
</div>
<?php echo $footer = $includes->get_admin_footer(); ?>