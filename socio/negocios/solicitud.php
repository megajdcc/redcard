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
if($_SESSION['user']['id_rol']==8) {
	header('Location: '.HOST.'/socio/negocios/siguiendo');
	die();
}
$id = filter_input(INPUT_GET, 'id');

$request = new socio\libs\user_request_detail($con);

if(!$request->load_data($id)){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['resend_request'])){
		$request->resend_data($_POST);
	}
	if(isset($_POST['delete_request'])){
		$request->delete_request();
	}
}

$includes = new assets\libs\includes($con);
$properties['title'] = 'Detalles de solicitud | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
	<div class="main">
		<div class="main-inner">
			<div class="container">
				<?php echo $con->get_notify();?>
				<div class="row">
					<div class="col-sm-4 col-lg-3">
						<div class="sidebar">
							<?php echo $includes->get_user_sidebar();?>
						</div><!-- /.sidebar -->
					</div><!-- /.col-* -->
					<div class="col-sm-8 col-lg-9">
						<div class="content">
							<?php echo $request->get_notification();
							echo $request->get_request();?>
						</div><!-- /.content -->
					</div><!-- /.col-* -->
				</div><!-- /.row -->
			</div><!-- /.container -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>