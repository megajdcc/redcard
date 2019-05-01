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

$leave = new socio\libs\user_deactivate($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['deactivate_account'])){
		$leave->deactivate_account($_POST);
	}
}

$includes = new assets\libs\includes($con);
$properties['title'] = 'Desactivar cuenta | Travel Points';
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
						<?php echo $leave->get_notification();?>
						<div class="content">
							<div class="row">
								<div class="col-md-8 col-md-offset-2">
									<form method="post" action="<?php echo _safe(HOST.'/socio/perfil/desactivar-cuenta');?>">
										<h1 class="page-title">Desactivar cuenta</h1>
										<p>
											Al desactivar tu cuenta, se desactivar&aacute; tu perfil y se borrar&aacute; tu nombre y tu foto de la mayor parte de Travel Points.
										</p>
										<div class="form-group">
											<label for="message">Nos interesa saber por qué nos dejas.</label>
											<textarea class="form-control" rows="3" id="message" name="message" placeholder="Un breve mensaje&hellip;"><?php echo $leave->get_message();?></textarea>
										</div>
										<hr>
										<div class="form-group">
											<label for="password">Contrase&ntilde;a actual <span class="required">*</span></label>
											<input class="form-control" type="password" id="password" name="password" placeholder="Contrase&ntilde;a actual" required/>
											<?php echo $leave->get_password_error();?>
										</div>
										<hr>
										<div class="form-group">
											<label for="password-confirm">Los campos marcados son obligatorios <span class="required">*</span></label>
											<button class="btn btn-default pull-right" type="submit" name="deactivate_account">Desactivar cuenta</button>
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