<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if($_SESSION['user']['id_rol'] != 1){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if(!isset($_SESSION['user']['admin_authorize'])){
	header('Location: '.HOST.'/admin/acceso');
	die();
}

$admins = new admin\libs\admin_list($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['role_id']) && isset($_POST['admin_id'])){
		$admins->change_admin_role($_POST);
	}
	if(isset($_POST['delete_admin'])){
		$admins->delete_admin($_POST);
	}
}

$includes = new admin\libs\includes($con);
$properties['title'] = 'Administradores | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $admins->get_notification();?>
		<div class="background-white p20 mb50">
			<div class="page-title">
				<h4>Lista de administradores</h4>
			</div>
			<?php echo $admins->get_admins();?>
		</div>
	</div>
</div>
<?php echo $footer = $includes->get_admin_footer(); ?>