<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';
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
$properties['title'] = 'Nuevo usuario referidor | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="">
	<form method="post" action="<?php echo _safe(HOST.'/Hotel/usuarios/nuevousuario');?>" autocomplete="off">
						
					<div class="row">
					
						<?php echo $reg->getMethodError(); ?>
						<div class="col-lg-6 ">
							<div class="page-title">
								<h2>Nuevo Usuario</h2>
							</div><!-- /.page-title -->
						
								<div class="form-group" data-toggle="tooltip" title="Tu nombre de usuario debe ser alfanum&eacute;rico. No puede contener espacios, acentos o caracteres especiales. Debe contener entre 3 y 50 caracteres. Recomendamos 20 o menos caracteres.">
									<label for="username" >Username (use no space)| Nombre de usuario (sin espacios o acentos) <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
									<input type="text" class="form-control" name="username" id="username" value="<?php echo $reg->getUsername();?>" placeholder="Nombre de usuario (sin espacios o acentos)" required minlength="3" maxlength="50" />
									<?php echo $reg->getUsernameError();?>
								</div><!-- /.form-group -->
								<div class="form-group">
									<label for="email">Email | Correo electr&oacute;nico <span class="required">*</span></label>
									<input type="email" class="form-control" name="email" id="email" value="<?php echo $reg->getEmail();?>" placeholder="Correo electr&oacute;nico" required />
									<?php echo $reg->getEmailError();?>
								</div>
						</div>

						<div class="col-lg-6">
							<div class="page-title">
								<h2>Quien te Invit&oacute;</h2>
							</div>


							<div class="form-group" id="user-search-hotel" data-toggle="tooltip" title="Encuentra tu referente al sitio por su nombre o nombre de usuario (username). Este campo es opcional.">
									<label for="user-search-input">Who invited you? | ¿Qui&eacute;n te invit&oacute;? <i class="fa fa-question-circle text-secondary"></i></label>

									
										<div class="search-placeholder" id="user-search-placeholder" style="flex:1 1 auto;">
										<img src="<?php //echo HOST;?>/assets/img/user_profile/default.jpg" class="meta-img img-rounded">
										</div>
										

									<input type="text" class="form-control typeahead" name="referral" id="user-search-input" value="<?php echo $reg->getReferral();?>" placeholder="Nombre de usuario del referente" autocomplete="off" />
									<?php echo $reg->getReferralError();?>
							</div>

						</div>
					
					</div>

					<div class="row">
							<div class="col-sm-6 col-offset-2">
								<div class="checkbox">
									<input type="checkbox" id="tos-check" checked><label for="tos-check">I accept <a href="<?php echo HOST.'/terminos-y-condiciones';?>" target="_blank">Terms and Conditions</a> | Acepto los <a href="<?php echo HOST.'/terminos-y-condiciones';?>" target="_blank">T&eacute;rminos y condiciones</a></label>
								</div>
							</div>
							<div class="col-sm-4">
								<input type="hidden" name="new_usuario">
								<input type="hidden" name="hotel_invitador" value="<?php echo $_SESSION['id_hotel'];?>">
								<button type="submit" class="btn btn-primary pull-right" id="register-btn">Join | ¡Hazme Socio!</button>
							</div>
					</div>
	</form>

<?php echo $footer = $includes->get_admin_footer(); ?>