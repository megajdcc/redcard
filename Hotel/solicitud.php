<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 
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

use Hotel\models\DetallesSolicitud;

$solicitud = new DetallesSolicitud($con);

if(!$solicitud->load_data($id)){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['accept_solicitud'])){
		$solicitud->accept_solicitud($_POST);
	}
	if(isset($_POST['check_solicitud'])){
		$solicitud->check_solicitud($_POST);
	}
	if(isset($_POST['reject_solicitud'])){
		$solicitud->reject_solicitud($_POST);
	}
	if(isset($_POST['delete_solicitud'])){
		$solicitud->delete_solicitud();
	}
}

$includes = new admin\libs\includes($con, true);
$properties['title'] = 'Detalles de solicitud | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">


	<div class="col-sm-12">
		<?php echo $solicitud->getNotificacion();?>
		<div class="content">
			<?php echo $solicitud->getSolicitud();?>
		</div><!-- /.content -->
	</div><!-- /.col-* -->
</div><!-- /.row -->
<?php echo $footer = $includes->get_admin_footer(); ?>