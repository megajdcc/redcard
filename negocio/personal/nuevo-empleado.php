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

$employee = new negocio\libs\personnel_new_employee($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['new_employee'])){
		$employee->new_employee($_POST);
	}
}

$includes = new negocio\libs\includes($con);
$properties['title'] = 'Nuevo empleado | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $employee->get_notification();?>
		<form method="post" action="<?php echo _safe(HOST.'/negocio/personal/nuevo-empleado');?>">
			<div class="background-white p30 mb50">
				<div class="page-title">
					<h4>Nuevo empleado</h4>
				</div>
				<div class="row">
					<div class="col-md-8">
						<div class="form-group" id="user-search" data-toggle="tooltip" title="Ingrese el nombre propio, nombre de usuario o correo electr&oacute;nico del socio de Travel Points que desea vincular con este negocio. Verifique su coincidencia cuidadosamente.">
							<label for="user-search-input">Socio de Travel Points <i class="fa fa-question-circle text-secondary"></i></label>
							<div class="search-placeholder" id="user-search-placeholder">
								<img src="<?php echo HOST;?>/assets/img/user_profile/default.jpg" class="meta-img img-rounded">
							</div>
							<input class="form-control typeahead" type="text" id="user-search-input" name="username" value="<?php echo $employee->get_username();?>" placeholder="Socio de Travel Points" autocomplete="off" required />
							<?php echo $employee->get_username_error();?>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group" data-toggle="tooltip" title="El rol define los privilegios que tendr&aacute; el nuevo empleado.">
							<label for="role">Rol de empleado</label>
							<select class="form-control" id="role" name="role" title="Seleccionar rol" required>
								<?php echo $employee->get_roles();?>
							</select>
							<?php echo $employee->get_role_error();?>
						</div>
					</div>
				</div>
				<p>El código de seguridad del nuevo empleado será identico a la contraseña actual de su cuenta. Posteriormente puede ser cambiada.</p>
				<hr>
				<button class="btn btn-success" type="submit" name="new_employee">Registrar empleado</button>
				</div>
			</div>
		</form>
	</div>
</div>
<?php echo $footer = $includes->get_footer(); ?>