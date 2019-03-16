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

$history = new admin\libs\business_sales($con);

$username = filter_input(INPUT_GET, 'negocio');

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));
$rpp = 20;
if($_SERVER["REQUEST_METHOD"] == "POST"){
	$history->set_date($_POST);
}
$options = $history->load_data($username, $page, $rpp);

$paging = new assets\libraries\pagination\pagination($options['page'], $options['total']);
$paging->setRPP($rpp);
$paging->setCrumbs(10);

$includes = new admin\libs\includes($con);
$properties['title'] = 'Historial de ventas | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $history->get_notification();?>
		<div class="background-white p20 mb50">
			<div class="page-title">
				<h4>Historial del negocio <a href="<?php echo HOST.'/'.$history->get_business_url();?>" target="_blank"><?php echo $history->get_business_name();?></a></h4>
			</div>
				<form method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>">
					<div class="row">
						<div class="col-sm-3">
							<div class="form-group">
								<label for="start">Fecha y hora de inicio</label>
								<div class="input-group date" id="report-start">
									<input class="form-control" type="text" id="start" name="date_start" value="<?php echo $history->get_date_start();?>" placeholder="Fecha y hora de inicio" required/>
									<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
								</div>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group">
								<label for="end">Fecha y hora de fin</label>
								<div class="input-group date" id="report-end">
									<input class="form-control" type="text" id="end" name="date_end" value="<?php echo $history->get_date_end();?>" placeholder="Fecha y hora de fin" required/>
									<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
								</div>
							</div>
						</div>
						<div class="col-sm-6">
							<label>Buscar</label>
							<div class="form-group">
								<button class="btn btn-success mr20" type="submit"><i class="fa fa-search m0"></i></button>
								<a href="<?php echo _safe($_SERVER['REQUEST_URI']);?>" class="btn btn-info">Limpiar</a>
							</div>
						</div>
					</div>
				</form>
			<?php echo $history->get_sales();?>
		</div>
		<?php echo $paging->parse(); ?>
	</div>
</div>
<?php echo $footer = $includes->get_admin_footer(); ?>