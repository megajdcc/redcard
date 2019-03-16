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

$events = new negocio\libs\content_events($con);

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));
$rpp = 10;
$options = $events->load_data($page, $rpp);

$paging = new assets\libraries\pagination\pagination($options['page'], $options['total']);
$paging->setRPP($rpp);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['edit_event'])){
		$events->edit_event($_POST, $_FILES);
	}
	if(isset($_POST['delete_event'])){
		$events->delete_event($_POST);
	}
}

$includes = new negocio\libs\includes($con);
$properties['title'] = 'Mis eventos | eSmartClub';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $events->get_notification();?>
		<div class="background-white p20 mb30">
			<a href="<?php echo $events->get_profile_url();?>" target="_blank">Ver perfil de negocio</a>
		</div><!-- /.box -->
		<?php echo $events->get_events(); echo $paging->parse(); ?>
	</div>
</div>
<?php echo $footer = $includes->get_footer(); echo $events->show_modal(); ?>