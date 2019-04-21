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

$id = filter_input(INPUT_GET, 'solicitud',FILTER_VALIDATE_INT);

$perfil = filter_input(INPUT_GET, 'perfil');

$solicitud = new admin\libs\DetallesSolicitud($con);

if(!$solicitud->load_data($id,$perfil)){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}


if(isset($_POST['action']) && $_POST['action'] == "adjudicar" && isset($_POST['perfil'])){

	if($_POST['perfil'] == "hotel"){
		
		$codigohotel = $_POST['codigohotel'];
		$comision = $_POST['comision'];
		$solicitud->adjudicar($perfil,$comision,$codigohotel);
		unset($_POST['action']);
		return;
	}else if($_POST['perfil'] == "franquiciatario"){
		$comision = $_POST['comision'];
		$solicitud->adjudicar($perfil,$comision);
		unset($_POST['action']);
		return;
	}else if($_POST['perfil'] == "referidor"){
		$comision = $_POST['comision'];
		$solicitud->adjudicar($perfil,$comision);
		unset($_POST['action']);
		return;
	}
}
	


if(isset($_POST['perfil']) && !isset($_POST['action'])){

	$solicitud->aceptarSolicitud($_POST,$perfil);


}


if($_SERVER["REQUEST_METHOD"] == "POST"){

	if(isset($_POST['corregirsolicitud'])){
		$solicitud->check_solicitud($_POST);
	}

	if(isset($_POST['rechazarsolicitud'])){
		$solicitud->reject_solicitud($_POST);
	}

	if(isset($_POST['eliminarsolicitud'])){
		$solicitud->EliminarSolicitud();
	}
}

$includes = new admin\libs\includes($con);
$properties['title'] = 'Detalles de solicitud | Travel points';
$properties['description'] = '';

echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<!-- <?php //echo $con->ChequeoNotificacion();?> -->
<div class="row">
	<div class="col-sm-12">
		<?php echo $solicitud->getNotificacion();?>
		<div class="content">
			<?php echo $solicitud->Mostrar($perfil);?>
		</div><!-- /.content -->
	</div><!-- /.col-* -->
	<?php echo $solicitud->getModal($perfil); ?>
</div><!-- /.row -->
<?php echo $footer = $includes->get_admin_footer(); ?>