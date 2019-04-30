<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

if(!isset($_SESSION['perfil'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
	}


use Referidor\models\Usuarios;
use Referidor\models\Includes;
use Referidor\models\NuevoUsuario;

$usuario = new Usuarios($con);

$reg = new NuevoUsuario($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	// echo var_dump($_POST);
	if(isset($_POST['new_usuario'])){
		$reg->setData($_POST);
	}
}

if(filter_input(INPUT_GET, 'ref')){
	$reg->setReferral($_GET['ref']);
}

$includes = new Includes($con);
$properties['title'] = 'Nuevo usuario referidor | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="main">
					<div class="row">
					
						<?php echo $reg->getMethodError(); ?>
						<div class="col-sm-12 col-sm-offset-4">
							<div class="page-title">
								<h1>Nuevo Usuario</h1>
							</div><!-- /.page-title -->
							<form method="post" action="<?php echo _safe(HOST.'/Referidor/usuarios/nuevousuario');?>" autocomplete="off">
								<div class="form-group" data-toggle="tooltip" title="Tu nombre de usuario debe ser alfanum&eacute;rico. No puede contener espacios, acentos o caracteres especiales. Debe contener entre 3 y 50 caracteres. Recomendamos 20 o menos caracteres.">
									<label for="username" >Username (use no space)| Nombre de usuario (sin espacios o acentos) <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
									<input type="text" class="form-control" name="username" id="username" value="<?php echo $reg->getUsername();?>" placeholder="Nombre de usuario (sin espacios o acentos)" required minlength="3" maxlength="50" />
									<?php echo $reg->getUsernameError();?>
								</div><!-- /.form-group -->
								<div class="form-group">
									<label for="email">Email | Correo electr&oacute;nico <span class="required">*</span></label>
									<input type="email" class="form-control" name="email" id="email" value="<?php echo $reg->getEmail();?>" placeholder="Correo electr&oacute;nico" required />
									<?php echo $reg->getEmailError();?>
								</div><!-- /.form-group -->
								<div class="form-group" data-toggle="tooltip" title="La contrase&ntilde;a debe contener al menos 6 caracteres y debe ser distinta de tu nombre de usuario y tu correo electr&oacute;nico.">
									<label for="password">Password | Contrase&ntilde;a <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
									<input type="password" class="form-control" name="password" id="password" placeholder="Contrase&ntilde;a" required />
									<?php //echo $reg->getPasswordError();?>
								</div><!-- /.form-group -->
								<div class="form-group">
									<label for="password-retype">Rewrite Password | Confirmar contrase&ntilde;a <span class="required">*</span></label>
									<input type="password" class="form-control" name="password-retype" id="password-retype" placeholder="Confirmar contrase&ntilde;a" required />
									<?php echo $reg->getRetypePasswordError();?>
								</div><!-- /.form-group -->
								<div class="form-group" id="user-search" data-toggle="tooltip" title="Encuentra tu referente al sitio por su nombre o nombre de usuario (username). Este campo es opcional.">
									<label for="user-search-input">Who invited you? (Concierge) | ¿Qui&eacute;n te invit&oacute;? (Concierge) <i class="fa fa-question-circle text-secondary"></i></label>
									<div class="search-placeholder" id="user-search-placeholder">
										<img src="<?php //echo HOST;?>/assets/img/user_profile/default.jpg" class="meta-img img-rounded">
									</div>
									<input type="text" class="form-control typeahead" name="referral" id="user-search-input" value="<?php echo $reg->getReferral();?>" placeholder="Nombre de usuario del referente" autocomplete="off" />
									<?php echo $reg->getReferralError();?>
								</div><!-- /.form-group -->
								<div class="row">
									<div class="col-sm-8">
										<div class="checkbox">
											<input type="checkbox" id="tos-check" checked><label for="tos-check">I accept <a href="<?php echo HOST.'/terminos-y-condiciones';?>" target="_blank">Terms and Conditions</a> | Acepto los <a href="<?php echo HOST.'/terminos-y-condiciones';?>" target="_blank">T&eacute;rminos y condiciones</a></label>
										</div>
									</div>
									<div class="col-sm-4">
										<input type="hidden" name="new_usuario">
										<button type="submit" class="btn btn-primary pull-right" id="register-btn">Join | ¡Hazme Socio!</button>
									</div>
								</div>
							</form>
						</div><!-- /.col-sm-12 -->
					
					</div><!-- /.row -->

<?php echo $footer = $includes->get_admin_footer(); ?>