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

$esmarties = new admin\libs\user_esmarties($con);

$username = filter_input(INPUT_GET, 'socio');

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));
$rpp = 20;
$options = $esmarties->load_data($username, $page, $rpp);

$paging = new assets\libraries\pagination\pagination($options['page'], $options['total']);
$paging->setRPP($rpp);
$paging->setCrumbs(10);

$includes = new admin\libs\includes($con);
$properties['title'] = 'Historial de movimientos | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $esmarties->get_notification();?>
		<div class="background-white p20 mb50">
			<div class="page-title">
				<h4>Historial de <?php echo $esmarties->get_alias();?></h4>
			</div>
			Travel Points actuales: <strong class="text-primary"><?php echo $esmarties->get_eSmarties();?></strong>
			<?php echo $esmarties->get_moves(); echo $paging->parse(); ?>
		</div>
	</div>
</div>
<?php echo $footer = $includes->get_admin_footer(); ?>