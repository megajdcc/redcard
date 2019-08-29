<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 
$con = new assets\libs\connection();

if(isset($_SESSION['user'])){ header('Location: '.HOST.'/socio/perfil/'); die(); }

$recover = new assets\libs\recover_account($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['recover_password'])){
		$recover->recover_password($_POST);
	}
	if(isset($_POST['send_email'])){
		$recover->send_email($_POST);
	}
}

$includes = new assets\libs\includes($con);
$properties['title'] = 'Recuperar cuenta | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
	<div class="main">
		<div class="main-inner">
			<div class="container">
				<div class="content">
					<?php echo $con->get_notify();?>
					<?php echo $recover->get_notification(); ?>
					<div class="page-title">
						<h1>Recuperar cuenta | Recover account</h1>
					</div><!-- /.page-title -->
					<div class="row">
						<div class="col-sm-6">
							<h2>Recuperar contrase&ntilde;a | Recover password</h2>
							<p>Si no puedes ingresar a tu cuenta porque has olvidado tu contrase&ntilde;a, puedes pedir una solicitud para reestablecer tu contrase&ntilde;a. | If you cannot access to your account because you forgot your password, you can reestablish it here.</p>
							<form method="post" action="<?php echo _safe(HOST.'/recuperar-cuenta');?>">
								<div class="form-group" data-toggle="tooltip" title="Escribe tu nombre de usuario (username) o correo electrónico vinculados con la cuenta que deseas recuperar">
									<label for="recover-password">Nombre de usuario o correo electr&oacute;nico | Username or email <i class="fa fa-question-circle text-secondary"></i></label>
									<input type="text" class="form-control" name="input" id="recover-password" value="<?php echo $recover->get_input_pass();?>" placeholder="Nombre de usuario o correo electr&oacute;nico" required />
									<?php echo $recover->get_input_pass_error();?>
								</div><!-- /.form-group -->
								<button type="submit" name="recover_password" class="btn btn-success mb50">Recuperar contrase&ntilde;a</button>
							</form>
						</div><!-- /.col-sm-4 -->
						<div class="col-sm-6">
							<h2>Enviar correo de verificaci&oacute;n | Send verifiaction email.</h2>
							<p>Si tu problema es que no has verificado tu cuenta porque no te lleg&oacute; el correo de verificaci&oacute;n, puedes pedir un nuevo correo para verificar tu correo electr&oacute;nico.</p>
							<form method="post" action="<?php echo _safe(HOST.'/recuperar-cuenta');?>">
								<div class="form-group" data-toggle="tooltip" title="Escribe tu nombre de usuario (username) o correo electrónico vinculados con la cuenta que deseas validar">
									<label for="send-email">Nombre de usuario o correo electr&oacute;nico | Username or email <i class="fa fa-question-circle text-secondary"></i></label>
									<input type="text" class="form-control" name="input" id="send-email" value="<?php echo $recover->get_input_email();?>" placeholder="Nombre de usuario o correo electr&oacute;nico" required />
									<?php echo $recover->get_input_email_error();?>
								</div><!-- /.form-group -->
								<button type="submit" name="send_email" class="btn btn-info mb30">Enviar otro correo de verificaci&oacute;n | Send other verification email.</button>
							</form>
						</div><!-- /.col-sm-4 -->
					</div><!-- /.row -->
					<p class="text-danger"><strong>Importante | Important!</strong> Si no encuentras nuestro correo, revisa en tu correo no deseado. Si el correo se encuentra ah&iacute;, por favor, m&aacute;rcalo como correo deseado.  |  If you do not find our email, please check your "junk mail" and if it is there, marc it as "white mail".</p>
				</div><!-- /.content -->
			</div><!-- /.container -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>