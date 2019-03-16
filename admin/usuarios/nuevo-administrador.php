<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if($_SESSION['user']['id_rol'] != 1){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if(!isset($_SESSION['user']['admin_authorize'])){
	header('Location: '.HOST.'/admin/acceso');
	die();
}

$admin = new admin\libs\admin_new($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['new_admin'])){
		$admin->new_admin($_POST);
	}
}

$includes = new admin\libs\includes($con);
$properties['title'] = 'Nuevo administrador | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $admin->get_notification();?>
		<form method="post" action="<?php echo _safe(HOST.'/admin/usuarios/nuevo-administrador');?>">
			<div class="background-white p30 mb50">
				<div class="page-title">
					<h4>Nuevo administrador</h4>
				</div>
				<div class="row">
					<div class="col-md-8">
						<div class="form-group" id="user-search" data-toggle="tooltip" title="Ingrese el nombre propio, nombre de usuario o correo electr&oacute;nico del socio de eSmart Club al que desea darle privilegios de administrador. Verifique su coincidencia cuidadosamente.">
							<label for="user-search-input">Socio de eSmart Club <i class="fa fa-question-circle text-secondary"></i></label>
							<div class="search-placeholder" id="user-search-placeholder">
										<img src="<?php echo HOST;?>/assets/img/user_profile/default.jpg" class="meta-img img-rounded">
									</div>
							<input class="form-control typeahead" type="text" id="user-search-input" name="username" value="<?php echo $admin->get_username();?>" placeholder="Socio de eSmart Club" autocomplete="off" required />
							<?php echo $admin->get_username_error();?>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group" data-toggle="tooltip" title="El rol define los privilegios que tendr&aacute; el nuevo administrador.">
							<label for="role">Rol de administrador</label>
							<select class="form-control" id="role" name="role" title="Seleccionar rol" required>
								<?php echo $admin->get_roles();?>
							</select>
							<?php echo $admin->get_role_error();?>
						</div>
					</div>
				</div>
				<p>
					El código de seguridad del nuevo administrador será identico a la contraseña actual de su cuenta. Posteriormente puede ser cambiada.
				</p>
				<hr>
				<button class="btn btn-success" type="submit" name="new_admin">Registrar administrador</button>
			</div>
		</form>
	</div>
</div>
<?php echo $footer = $includes->get_admin_footer(); ?>