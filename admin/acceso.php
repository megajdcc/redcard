<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if($_SESSION['user']['id_rol'] != 1 && $_SESSION['user']['id_rol'] != 2 && $_SESSION['user']['id_rol'] != 3){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if(isset($_SESSION['user']['admin_authorize'])){
	header('Location: '.HOST.'/admin/');
	die();
}

$landing = new admin\libs\admin_authorize($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$landing->set_data($_POST);
}

$includes = new assets\libs\includes($con);
$properties['title'] = 'Autorizar Acceso | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
	<div class="main">
		<div class="main-inner">
			<div class="container">
				<div class="content">
					<?php echo $con->get_notify();?>
					<?php echo $landing->get_notification(); ?>
					<div class="row">
						<div class="col-sm-4 col-sm-offset-4">
							<div class="page-title">
								<h1>Autorizar Acceso</h1>
							</div><!-- /.page-title -->
							<?php echo $landing->get_authorize_error(); ?>
							<form method="post" action="<?php echo _safe(HOST.'/admin/acceso');?>">
								<div class="form-group">
									<label for="security_code">C&oacute;digo de Seguridad</label>
									<input type="password" class="form-control" name="security_code" id="security_code" placeholder="C&oacute;digo de Seguridad" />
									<?php echo $landing->get_security_code_error();?>
								</div><!-- /.form-group -->
								<button type="submit" class="btn btn-primary pull-right">Verificar</button>
							</form>
						</div><!-- /.col-sm-4 -->
					</div><!-- /.row -->
				</div><!-- /.content -->
			</div><!-- /.container -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>