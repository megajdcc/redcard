<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if($_SESSION['user']['id_rol'] != 1 && $_SESSION['user']['id_rol'] != 2 && $_SESSION['user']['id_rol'] != 3 && $_SESSION['user']['id_rol'] != 9){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if(!isset($_SESSION['user']['admin_authorize'])){
	header('Location: '.HOST.'/admin/acceso');
	die();
}

$security = new admin\libs\security_code($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['change_password'])){
		$security->change_password($_POST);
	}
}

$includes = new admin\libs\includes($con);
$properties['title'] = 'Cambiar código de seguridad';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
		<form method="post" action="<?php echo _safe(HOST.'/admin/preferencias/codigo-seguridad');?>">
			<h1 class="page-title">Cambiar c&oacute;digo de seguridad</h1>
			<?php echo $security->get_password_error();?>
			<div class="form-group">
				<label for="password">C&oacute;digo de seguridad actual <span class="required">*</span></label>
				<input class="form-control" type="password" id="password" name="password" placeholder="Contrase&ntilde;a actual" required/>
			</div>
			<div class="form-group" data-toggle="tooltip" title="El nuevo c&oacute;digo de seguridad debe contener al menos 6 caracteres y debe ser distinto de tu nombre de usuario y tu correo electr&oacute;nico.">
				<label for="new-password">Nuevo c&oacute;digo de seguridad <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
				<input class="form-control" type="password" id="new-password" name="new_password" placeholder="Nueva contrase&ntilde;a" required/>
			</div>
			<div class="form-group">
				<label for="password-confirm">Confirmar c&oacute;digo de seguridad  <span class="required">*</span></label>
				<input class="form-control" type="password" id="password-confirm" name="password_confirm" placeholder="Confirmar contrase&ntilde;a" required/>
			</div>
			<hr>
			<div class="form-group">
				<button class="btn btn-success pull-right" type="submit" name="change_password">Cambiar c&oacute;digo de seguridad</button>
			</div>
		</form>
	</div>
</div><!-- /.background-white p20 mb30 -->
<?php echo $footer = $includes->get_admin_footer(); ?>