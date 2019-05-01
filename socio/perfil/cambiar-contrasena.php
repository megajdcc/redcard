<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	header('Location: '.HOST.'/login');
	die();
}
if(!isset($_SESSION['user']['id_usuario'])){
	header('Location: '.HOST.'/login');
	die();
}

$password = new socio\libs\user_password($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['change_password'])){
		$password->change_password($_POST);
	}
}

$includes = new assets\libs\includes($con);
$properties['title'] = $password->get_alias().' | Editar perfil | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
	<div class="main">
		<div class="main-inner">
			<div class="container">
				<?php echo $con->get_notify();?>
				<div class="row">
					<div class="col-sm-4 col-md-3">
						<div class="sidebar">
							<?php echo $includes->get_user_sidebar();?>
						</div><!-- /.sidebar -->
					</div><!-- /.col-* -->
					<div class="col-sm-8 col-md-9">
						<?php echo $password->get_notification();?>
						<div class="content">
							<div class="row">
								<div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
									<form method="post" action="<?php echo _safe(HOST.'/socio/perfil/cambiar-contrasena');?>">
										<h1 class="page-title">Cambiar contrase&ntilde;a</h1>
										<?php echo $password->get_password_error();?>
										<div class="form-group">
											<label for="password">Contrase&ntilde;a actual <span class="required">*</span></label>
											<input class="form-control" type="password" id="password" name="password" placeholder="Contrase&ntilde;a actual" required/>
										</div>
										<div class="form-group">
											<label for="new-password">Nueva contrase&ntilde;a <span class="required">*</span></label>
											<input class="form-control" type="password" id="new-password" name="new_password" placeholder="Nueva contrase&ntilde;a" required/>
										</div>
										<div class="form-group">
											<label for="password-confirm">Confirmar contrase&ntilde;a <span class="required">*</span></label>
											<input class="form-control" type="password" id="password-confirm" name="password_confirm" placeholder="Confirmar contrase&ntilde;a" required/>
										</div>
										<hr>
										<div class="form-group">
											<label for="password-confirm">Los campos marcados son obligatorios <span class="required">*</span></label>
											<button class="btn btn-success pull-right" type="submit" name="change_password">Cambiar contrase&ntilde;a</button>
										</div>
									</form>
								</div>
							</div><!-- /.background-white p20 mb30 -->
						</div><!-- /.content -->
					</div><!-- /.col-* -->
				</div><!-- /.row -->
			</div><!-- /.container -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>