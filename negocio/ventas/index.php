<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 
$con = new assets\libs\connection();

if(!isset($_SESSION['user']) || !isset($_SESSION['business'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if($_SESSION['business']['id_rol'] != 4 && $_SESSION['business']['id_rol'] != 5 && $_SESSION['business']['id_rol'] != 6){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$sale = new negocio\libs\sales_new_sale($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['new_sale'])){
		$sale->submit_sale($_POST);
	}
}

$includes = new negocio\libs\includes($con);
$properties['title'] = 'Nueva venta | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $sale->get_notification();?>
		<form method="post" action="<?php echo _safe(HOST.'/negocio/ventas/');?>">
			<div class="background-white p30 mb50">
				<div class="page-title">
					<h1>Registrar venta</h1>
					<p class="text-default mt30">Saldo actual: <?php echo $sale->get_balance();?></p>
					<input type="hidden" id="balance" value="<?php echo $sale->get_clean_balance();?>">
					<p id="after">Saldo despu&eacute;s de venta: </p>
				</div>
				<div class="form-group" id="user-search" data-toggle="tooltip" title="Ingrese el nombre propio, nombre de usuario o correo electr&oacute;nico del socio de Travel Points. Verifique su coincidencia cuidadosamente.">
					<label for="user-search-input">Socio de Travel Points <i class="fa fa-question-circle text-secondary"></i></label>
					<div class="search-placeholder" id="user-search-placeholder">
						<img src="<?php echo HOST;?>/assets/img/user_profile/default.jpg" class="meta-img img-rounded">
					</div>
					<input class="form-control typeahead" type="text" id="user-search-input" name="username" value="<?php echo $sale->get_username();?>" placeholder="Nombre de usuario del cliente" required>
					<?php echo $sale->get_username_error(); ?>
				</div>
				<div class="row">
					<div class="col-sm-4 col-lg-2">
						<div class="form-group">
							<label for="sale-currency">Divisa</label>
							<select class="selectpicker" id="sale-currency" name="currency" title="Seleccionar divisa" required>
							<?php echo $sale->get_currencies(); ?>
							</select>
							<?php echo $sale->get_currency_error(); ?>
						</div>
					</div>
					<div class="col-sm-8 col-lg-5">
						<div class="form-group" data-toggle="tooltip" title="Evita cualquier tipo de signo o comas. Utiliza unicamente puntos decimales. Ejemplo: 1499.50">
							<label for="sale-total">Total de venta</label>
							<input class="form-control" type="text" id="sale-total" name="total" value="<?php echo $sale->get_total();?>" placeholder="Total de venta" required />
							<?php echo $sale->get_total_error(); ?>
						</div>
					</div>
					<div class="col-sm-4 col-lg-2">
						<div class="form-group">
							<label for="sale-commission">Comisi&oacute;n</label>
							<input class="form-control" id="sale-commission" value="<?php echo $sale->get_commission(); ?>%" readonly>
							<input type="hidden" id="business-commission" value="<?php echo $sale->get_commission(); ?>">
						</div>
					</div><!-- /.col-* -->
					<div class="col-sm-8 col-lg-3">
						<div class="form-group">
							<label for="sale-esmarties">TravelPoints de esta venta</label>
							<input class="form-control" id="sale-esmarties" value="<?php echo $sale->get_eSmarties(); ?>" readonly>
						</div>
					</div>
				</div><!-- /.row -->
				<hr>
				<h2>Opcional: Canjear un certificado</h2>
				<div class="form-group mb30">
					<label for="certificate">Nombre de certificado</label>
					<select class="form-control" id="certificate" name="certificate" title="Seleccionar certificado" data-show-subtext="true" data-live-search="true">
						<option value="">No seleccionar</option>
						<?php echo $sale->get_certificates();?>
					</select>
					<?php echo $sale->get_certificate_error(); ?>
				</div>
				<hr>
				<h2>Opcional: Canjear certificados reservados</h2>
				<div id="certificate-load">
					<?php echo $sale->load_reserved_certificates().$sale->get_reserved_error();?>
				</div>
				<hr>
				<div class="center">
					<button class="btn btn-success" type="submit" id="submit-sale" name="new_sale">Registrar venta</button>
				</div>
			</div><!-- /.box -->
		</form>
	</div><!-- /.col-* -->
</div><!-- /.row -->
<?php echo $footer = $includes->get_footer(); ?>