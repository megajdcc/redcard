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

$businesses = new admin\libs\business_list($con);

$search = filter_input(INPUT_GET, 'buscar');

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['pdf'])){
		$businesses->get_businesses_pdf($search);
		die();
	}
}

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));
$rpp = 20;
$options = $businesses->load_data($search, $page, $rpp);

$paging = new assets\libraries\pagination\pagination($options['page'], $options['total']);
$paging->setRPP($rpp);
$paging->setCrumbs(10);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['business_id']) && isset($_POST['suspend_id'])){
		$businesses->change_business_status($_POST);
	}

	if(isset($_POST['negocioid'])){
		$businesses->EliminarNegocio($_POST['negocioid']);
	}
	
}
$includes = new admin\libs\includes($con);
$properties['title'] = 'Negocios | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $businesses->get_notification();?>
		<div class="page-title">
			<h1>Negocios en Travel Points
			<form class="pull-right" method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>" target="_blank">
				<button class="btn btn-default text-danger" type="submit" name="pdf"><i class="fa fa-file-pdf-o"></i>PDF</button>
			</form>
			</h1>
		</div>
		<div class="background-white p20 mb50">
			<form method="get" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>">
				<div class="form-group" data-toggle="tooltip" title="Puedes encontrar un negocio por su nombre o descripci&oacute;n">
					<label for="search">Buscar negocio <i class="fa fa-question-circle text-secondary"></i></label>
					<div class="input-group">
						<input class="form-control" type="text" id="search" name="buscar" value="<?php echo _safe($search);?>" placeholder="Buscar negocio&hellip;">
						<span class="input-group-btn">
							<button class="btn btn-primary" type="submit"><i class="fa fa-search"></i></button>
						</span>
					</div><!-- /.input-group -->
				</div><!-- /.form-group -->
			</form>
			<?php echo $businesses->get_businesses(); echo $paging->parse(); ?>
		</div>
	</div>
</div>
<?php echo $footer = $includes->get_admin_footer(); ?>