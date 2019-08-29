<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

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

$sal = new negocio\libs\manage_sales($con);
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));
$rpp = 10;
// Set the type of pagination 
$sal->setPagination($page, $rpp);
$page = $sal->getPage();
$total = $sal->getTotalPage();
// Set the pagination
$pag = new assets\libraries\pagination\pagination($page, $total);
$pag->setRPP($rpp);

$includes = new negocio\libs\includes($con);
$properties['title'] = 'Historial de ventas | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<div class="page-title">
			<h1>Historial de ventas</h1>
		</div>
		<?php $sal->getSaleHistory(); echo $pag->parse(); ?>
	</div><!-- /.col-* -->
</div><!-- /.row -->
<?php echo $footer = $includes->get_footer(); ?>