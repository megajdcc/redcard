<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 

if(!isset($_SESSION['user']) || !isset($_SESSION['business'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if($_SESSION['business']['id_rol'] != 4 && $_SESSION['business']['id_rol'] != 5){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$balance = new negocio\libs\business_balance($con);

$_SESSION['notification']['success'] = 'El pago fue realizado con éxito.';

$includes = new negocio\libs\includes($con);
$properties['title'] = 'Recarga exitosa | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify(); echo $balance->get_notification();?>
<div class="row">
	<div class="col-sm-12">
		<div class="background-white p30 mb30">
			Sigue las siguientes instrucciones
		</div>
	</div>
</div>
<?php echo $footer = $includes->get_footer(); ?>