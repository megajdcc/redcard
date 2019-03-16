<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(isset($_SESSION['user'])){ header('Location: '.HOST.'/socio/perfil/'); die(); }

$password = new assets\libs\change_password($con);

$email = filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL);
$hash = filter_input(INPUT_GET, 'codigo');
if($email && strlen($hash) == 32){
	$validation = $password->validate_hash($email, $hash);
	if(!$validation){
		http_response_code(404);
		include(ROOT.'/errores/404.php');
		die();
	}
}else{
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$password->change_password($_POST);
}

$includes = new assets\libs\includes($con);
$properties['title'] = 'Cambiar contraseña | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
	<div class="main">
		<div class="main-inner">
			<div class="container">
				<div class="content">
					<?php echo $con->get_notify();?>
					<?php echo $password->get_notification(); ?>
					<div class="row">
						<?php if($validation === true){ ?>
						<div class="col-sm-4 col-sm-offset-4">
							<div class="page-title">
								<h1>Cambiar contrase&ntilde;a</h1>
							</div><!-- /.page-title -->
							<?php echo $password->get_password_error();?>
							<form method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>">
								<div class="form-group" data-toggle="tooltip" title="La contrase&ntilde;a debe contener al menos 6 caracteres y debe ser distinta de tu nombre de usuario y tu correo electr&oacute;nico.">
									<label for="password">Contrase&ntilde;a <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
									<input type="password" class="form-control" name="password" id="password" placeholder="Contrase&ntilde;a" minlength="6" required/>
								</div><!-- /.form-group -->
								<div class="form-group">
									<label for="password-retype">Confirmar contrase&ntilde;a <span class="required">*</span></label>
									<input type="password" class="form-control" name="retype" id="password-retype" placeholder="Confirmar contrase&ntilde;a" minlength="6" required/>
								</div><!-- /.form-group -->
								<div class="center">
									<button type="submit" class="btn btn-success">Reestablecer mi contrase&ntilde;a</button>
								</div>
							</form>
						</div><!-- /.col-sm-4 -->
						<?php }elseif(is_string($validation)){ ?>
						<div class="col-sm-6 col-sm-offset-3">
							<div class="background-white p30 mb30 text-default">
								<h4 class="text-danger page-title"><?php echo _safe($validation);?></h4>
								<p>Posibles causas:</p>
								<ul>
									<li>Si has enviado m&aacute;s de una solicitud para reestablecer tu contrase&ntilde;a recientemente, utiliza el enlace m&aacute;s reciente enviado a tu correo.</li>
								</ul>
							</div>
						</div>
						<?php } ?>
					</div><!-- /.row -->
				</div><!-- /.content -->
			</div><!-- /.container -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>