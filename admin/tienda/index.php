<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if($_SESSION['user']['id_rol'] != 1 && $_SESSION['user']['id_rol'] != 2 && $_SESSION['user']['id_rol'] != 3 && $_SESSION['user']['id_rol'] != 9){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if(!isset($_SESSION['user']['admin_authorize'])){
	header('Location: '.HOST.'/admin/acceso');
	die();
}

$products = new admin\libs\product_list($con);
if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['pdf'])){
		$products->get_products_pdf();
		die();
	}
}

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));
$rpp = 20;
$options = $products->load_data($page, $rpp);

$paging = new assets\libraries\pagination\pagination($options['page'], $options['total']);
$paging->setRPP($rpp);
$paging->setCrumbs(10);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['edit_product'])){
		$products->edit_product($_POST, $_FILES);
	}
	if(isset($_POST['activate_product'])){
		$products->activate_product($_POST);
	}
	if(isset($_POST['cancel_product'])){
		$products->cancel_product($_POST);
	}
	if(isset($_POST['delete_product'])){
		$products->delete_product($_POST);
	}
}

$includes = new admin\libs\includes($con);
$properties['title'] = 'Productos en tienda | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $products->get_notification();?>
		<div class="page-title">
			<h1>Productos en Tienda
				<form class="pull-right" method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>" target="_blank">
					<button class="btn btn-default text-danger" type="submit" name="pdf"><i class="fa fa-file-pdf-o"></i>PDF</button>
				</form>
			</h1>
		</div>
		<div class="background-white p20 mb50">
			<?php echo $products->get_products(); echo $paging->parse(); ?>
		</div>
	</div>
</div>
<?php echo $footer = $includes->get_admin_footer(); echo $products->show_modal(); ?>