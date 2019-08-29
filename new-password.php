<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 
$con = new assets\libs\connection();

if(isset($_SESSION['user'])){ header('Location: '.HOST.'/socio/perfil/'); die(); }

$login = new assets\libs\user_login($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$login->registrarpass($_POST,$_SESSION['id_user']);
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
								<h1>Registrar tu Contrase&ntilde;a</h1>
							</div><!-- /.page-title -->
							<?php echo $login->get_login_error(); ?>
							<form method="post" action="<?php echo _safe(HOST.'/new-password');?>">
								<div class="form-group" data-toggle="tooltip" title="La contrase&ntilde;a debe contener al menos 6 caracteres y debe ser distinta de tu nombre de usuario y tu correo electr&oacute;nico.">
									<label for="password">Password | Contrase&ntilde;a <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
									<input type="password" class="form-control" name="password" id="password" placeholder="Contrase&ntilde;a" required />
									<?php echo $login->getPasswordError();?>
								</div><!-- /.form-group -->
								<div class="form-group">
									<label for="password-retype">Rewrite Password | Confirmar contrase&ntilde;a <span class="required">*</span></label>
									<input type="password" class="form-control" name="password-retype" id="password-retype" placeholder="Confirmar contrase&ntilde;a" required />
									<?php echo $login->getRetypePasswordError();?>
								</div>
							
								<button type="submit" class="btn btn-primary pull-right">Registrar!</button>
							</form>
						</div><!-- /.col-sm-4 -->
					</div><!-- /.row -->
				</div><!-- /.content -->
			</div><!-- /.container -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>