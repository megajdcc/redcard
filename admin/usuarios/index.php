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

$members = new admin\libs\member_list($con);

$search = filter_input(INPUT_GET, 'buscar');

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));
$rpp = 20;
$options = $members->load_data($search, $page, $rpp);

$paging = new assets\libraries\pagination\pagination($options['page'], $options['total']);
$paging->setRPP($rpp);
$paging->setCrumbs(10);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['ban_user'])){
		$members->ban_user($_POST);
	}
	if(isset($_POST['unban_user'])){
		$members->unban_user($_POST);
	}
}

$includes = new admin\libs\includes($con);
$properties['title'] = 'Socios | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $members->get_notification();?>
		<div class="background-white p20 mb50">
			<div class="page-title">
				<h4>Socios de Travel Points</h4>
			</div>
			<form method="get" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>">
				<div class="form-group" data-toggle="tooltip" title="Puedes encontrar un socio por su nombre o username">
					<label for="search">Buscar socio <i class="fa fa-question-circle text-secondary"></i></label>
					<div class="input-group">
						<input class="form-control" type="text" id="search" name="buscar" value="<?php echo _safe($search);?>" placeholder="Buscar socio">
						<span class="input-group-btn">
							<button class="btn btn-primary" type="submit"><i class="fa fa-search"></i></button>
						</span>
					</div><!-- /.input-group -->
				</div><!-- /.form-group -->
			</form>
			<?php echo $members->get_members(); echo $paging->parse(); ?>
		</div>
	</div>
</div>
<?php echo $footer = $includes->get_admin_footer(); ?>