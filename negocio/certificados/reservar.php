<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
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

$cert = new negocio\libs\manage_certificates($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$cert->useCertificate($_POST);
}

$includes = new negocio\libs\includes($con);
$properties['title'] = 'Reservar un certificado de regalo | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $cert->get_notification(); ?>
		<form method="post" action="<?php echo _safe(HOST.'/negocio/certificados/reservar');?>">
			<div class="background-white p30 mb50">
				<div class="page-title">
					<h1>Reservar un certificado de regalo</h1>
				</div>
				<div class="form-group">
					<p class="text-success">Paso 1. Reservar el certificado de regalo</p>
					<p>Paso 2. Canjearlo al registrar una venta</p>
				</div>
				<hr>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group" id="user-search" data-toggle="tooltip" title="Ingrese el nombre propio, nombre de usuario o correo electr&oacute;nico del socio de eSmart Club. Verifique su coincidencia cuidadosamente.">
							<label for="user-search-input">Socio de eSmart Club <i class="fa fa-question-circle text-secondary"></i></label>
							<div class="search-placeholder" id="user-search-placeholder">
								<img src="<?php echo HOST;?>/assets/img/user_profile/default.jpg" class="meta-img img-rounded">
							</div>
							<input class="form-control typeahead" type="text" id="user-search-input" name="username" value="<?php echo $cert->getUser();?>" placeholder="Nombre de usuario del cliente" autocomplete="off" required />
							<?php echo $cert->getUserError(); ?>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="certificate">Nombre de certificado</label>
							<select class="selectpicker" id="certificate" name="certificate" title="Seleccionar certificado" data-show-subtext="true" data-live-search="true" required>
							<?php echo $cert->get_certificates();?>
							</select>
							<?php echo $cert->getCodeError(); ?>
						</div>
					</div>
				</div><!-- /.row -->
				<hr>
				<button class="btn btn-success" type="submit">Reservar certificado</button>
			</div><!-- /.box -->
		</form>
	</div><!-- /.col-* -->
</div><!-- /.row -->
<?php echo $footer = $includes->get_footer(); ?>