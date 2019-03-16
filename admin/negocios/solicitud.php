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
if(!isset($_SESSION['user']['admin_authorize'])){
	header('Location: '.HOST.'/admin/acceso');
	die();
}

$id = filter_input(INPUT_GET, 'id');

$request = new admin\libs\request_detail($con);

if(!$request->load_data($id)){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['accept_request'])){
		$request->accept_request($_POST);
	}
	if(isset($_POST['check_request'])){
		$request->check_request($_POST);
	}
	if(isset($_POST['reject_request'])){
		$request->reject_request($_POST);
	}
	if(isset($_POST['delete_request'])){
		$request->delete_request();
	}
}

$includes = new admin\libs\includes($con);
$properties['title'] = 'Detalles de solicitud | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $request->get_notification();?>
		<div class="content">
			<?php echo $request->get_request();?>
		</div><!-- /.content -->
	</div><!-- /.col-* -->
</div><!-- /.row -->
<?php echo $footer = $includes->get_admin_footer(); ?>