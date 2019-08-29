<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!isset($_SESSION['user']) || !isset($_SESSION['business'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if($_SESSION['business']['id_rol'] != 4){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$personnel = new negocio\libs\personnel_list($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['role_id']) && isset($_POST['employee_id'])){
		$personnel->change_employee_role($_POST);
	}
	if(isset($_POST['delete_employee'])){
		$personnel->delete_employee($_POST);
	}
}

$includes = new negocio\libs\includes($con);
$properties['title'] = 'Personal del negocio | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $personnel->get_notification();?>
		<div class="background-white p20 mb50">
			<div class="page-title">
				<h4>Lista de empleados</h4>
			</div>
			<?php echo $personnel->get_personnel();?>
		</div>
	</div>
</div>
<?php echo $footer = $includes->get_footer(); ?>