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

$businesses = new admin\libs\business_dashboard($con);
if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['pdf'])){
		$businesses->get_sales_pdf();
		die();
	}
}
 
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));
$rpp = 20;
$options = $businesses->load_data($page, $rpp);

$paging = new assets\libraries\pagination\pagination($options['page'], $options['total']);
$paging->setRPP($rpp);
$paging->setCrumbs(10);

 
$includes = new admin\libs\includes($con);
$reports = new admin\libs\reports_sales($con);
$info = new negocio\libs\preference_info($con);
$users = new admin\libs\get_allusers($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$reports->set_date($_POST);
}else{
	$reports->load_data();
}

$properties['title'] = 'Negocios | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $reports->get_notification();?>
		<div class="background-white p20 mb30">
			<form method="post">
				<div class="row">
					<div class="col-sm-4">
						<div class="form-group">
							<label for="start">Fecha y hora de inicio</label>
							<div class="input-group date" id="event-start">
								<input class="form-control" type="text" id="start" name="date_start" value="<?php echo $reports->get_date_start();?>" placeholder="Fecha y hora de inicio" required/>
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							</div>
							<?php echo $reports->get_date_start_error();?>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label for="end">Fecha y hora de fin</label>
							<div class="input-group date" id="event-end">
								<input class="form-control" type="text" id="end" name="date_end" value="<?php echo $reports->get_date_end();?>" placeholder="Fecha y hora de fin" required/>
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							</div>
							<?php echo $reports->get_date_end_error();?>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label for="category">Usuario</label>
								<select data-live-search="true" class="form-control" id="user" name="user_id" title="Seleccionar usuario">
									<?php echo $users->get_users();?>
								</select>
								<?php echo $users->get_users_error();?>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label for="category">Categor&iacute;a del negocio</label>
								<select class="form-control" id="category" name="category_id" title="Seleccionar categor&iacute;a">
									<?php echo $info->get_category_for_report();?>
								</select>
								<?php echo $info->get_category_error();?>
						</div>
					</div>
					<div class="col-sm-8">
						<label>Buscar</label>
						<div class="form-group">
							<button class="btn btn-success" type="submit"><i class="fa fa-search"></i></button>
							<a href="<?php echo _safe(HOST.'/admin/reporte-de-ventas/');?>" class="btn btn-info">Limpiar</a>
						</div>
					</div>
				</div>
			</form>
		</div>
		<div class="page-title">
			<h1>Reporte de Ventas
				<form class="pull-right" method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>" target="_blank">
					<button class="btn btn-default text-danger" type="submit" name="pdf"><i class="fa fa-file-pdf-o"></i>PDF</button>
				</form>
			</h1>
		</div>
		<div class="background-white p20 mb50">
		<?php echo $reports->get_sales();?>
		</div>
	</div>
</div>
<?php echo $footer = $includes->get_admin_footer(); ?>
<script type="text/javascript">
	$('#user').val("<?php echo $reports->get_user_id();?>");
	$('#category').val("<?php echo $reports->get_business_category_id();?>");
</script>