<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!isset($_SESSION['user']) || !isset($_SESSION['business'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if($_SESSION['business']['id_rol'] != 4){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$amenities = new negocio\libs\preference_amenities($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$amenities->set_amenities_payments($_POST);
}

$includes = new negocio\libs\includes($con);
$properties['title'] = 'Amenidades y formas de pago del negocio | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $amenities->get_notification();?>
		
		<div class="background-white p20 mb30">
			<a href="<?php echo $amenities->get_url();?>" target="_blank">Ver perfil de negocio</a>
		</div><!-- /.box -->
		<form method="post" action="<?php echo _safe(HOST.'/negocio/preferencias/amenidades-y-pagos');?>">
			<div class="background-white p20 mb50">
				<div class="row">
					<div class="col-sm-6">
						<h4 class="page-title">Amenidades</h4>
						<p>Selecciona las amenidades que ofrece tu negocio, estas ser&aacute;n visibles en el perfil.</p>
						<div class="form-group">
							<?php echo $amenities->get_amenities();?>
						</div><!-- /.form-group -->
					</div>
					<div class="col-sm-6">
						<h4 class="page-title">Formas de pago</h4>
						<p>Selecciona las formas de pago que admite tu negocio, tambi&eacute;n ser&aacute;n visibles en el perfil.</p>
						<div class="form-group">
							<?php echo $amenities->get_payment_methods();?>
						</div><!-- /.form-group -->
					</div>
				</div>
				<hr>
				<div class="form-group">
					<button class="btn btn-success" type="submit">Guardar</button>
				</div>
			</div><!-- /.box -->
		</form>
	</div>
</div>
<?php echo $footer = $includes->get_footer(); ?>