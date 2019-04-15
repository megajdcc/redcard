<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(isset($_SESSION['user'])){ header('Location: '.HOST.'/socio/perfil/'); die(); }

$login = new assets\libs\user_login($con);

$email = filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL);
$hash = filter_input(INPUT_GET, 'codigo');
if($email && strlen($hash) == 32){
	$login->validate_account($email, $hash);
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$login->set_data($_POST);
}

$includes = new assets\libs\includes($con);
$properties['title'] = 'Iniciar sesión | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
	<div class="main">
		<div class="main-inner">
			<div class="container">
				<div class="content">
					<?php echo $con->get_notify();?>
					<?php echo $login->get_notification(); ?>
					<div class="row">
						<div class="col-sm-4 col-sm-offset-4">
							<div class="page-title">
								<h1>Iniciar sesi&oacute;n</h1>
							</div><!-- /.page-title -->
							<?php echo $login->get_login_error(); ?>
							<form method="post" action="<?php echo _safe(HOST.'/login');?>">
								<div class="form-group">
									<label for="email">Correo electr&oacute;nico</label>
									<input type="text" class="form-control" name="email" id="email" value="<?php echo $login->get_email();?>" placeholder="Correo electr&oacute;nico" required />
									<?php echo $login->get_email_error();?>
								</div><!-- /.form-group -->
								<div class="form-group">
									<label for="password">Contrase&ntilde;a</label>
									<input type="password" class="form-control" name="password" id="password" placeholder="Contrase&ntilde;a" required />
									<?php echo $login->get_password_error();?>
								</div><!-- /.form-group -->
								<a href="<?php echo HOST;?>/recuperar-cuenta">No puedo entrar a mi cuenta</a>
								<button type="submit" class="btn btn-primary pull-right">¡Entrar!</button>
							</form>
						</div><!-- /.col-sm-4 -->
					</div><!-- /.row -->
				</div><!-- /.content -->
			</div><!-- /.container -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>