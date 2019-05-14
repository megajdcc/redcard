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

$certificates = new negocio\libs\certificates_list($con);

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));
$rpp = 10;
$options = $certificates->load_data($page, $rpp);

$paging = new assets\libraries\pagination\pagination($options['page'], $options['total']);
$paging->setRPP($rpp);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['edit_certificate']) && isset($_POST['id'])){
		$certificates->edit_certificate($_POST, $_FILES);
	}
	if(isset($_POST['cancel_certificate']) && isset($_POST['id'])){
		$certificates->cancel_certificate($_POST);
	}
	if(isset($_POST['delete_certificate']) && isset($_POST['id'])){
		$certificates->delete_certificate($_POST);
	}
}

$includes = new negocio\libs\includes($con);
$properties['title'] = 'Certificados de regalo | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $certificates->get_notification();?>
		<div class="background-white p20 mb30">
			<a href="<?php echo $certificates->get_profile_url();?>" target="_blank">Ver perfil de negocio</a>
		</div><!-- /.box -->
		<?php echo $certificates->get_certificates(); echo $paging->parse(); ?>
	</div>
</div>
<?php echo $footer = $includes->get_footer(); echo $certificates->show_modal(); ?>>