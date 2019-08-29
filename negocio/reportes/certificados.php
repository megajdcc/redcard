<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 
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

$reports = new negocio\libs\reports_certificates($con);
$info = new negocio\libs\preference_info($con);
$users = new negocio\libs\get_allusers($con);
if($_SERVER["REQUEST_METHOD"] == "POST"){
	$reports->set_date($_POST);
}else{
	$reports->load_data();
}

$includes = new negocio\libs\includes($con);
$properties['title'] = 'Reporte de certificados redimidos | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $reports->get_notification();?>
		<div class="background-white p20 mb30">
			<h1 class="page-title">Reporte de Certificado</h1>
			<form method="post" action="<?php echo _safe(HOST.'/negocio/reportes/certificados');?>">
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
						<label>Buscar</label>
						<div class="form-group">
							<button class="btn btn-success" type="submit"><i class="fa fa-search"></i></button>
							<a href="<?php echo _safe(HOST.'/negocio/reportes/certificados');?>" class="btn btn-info">Limpiar</a>
						</div>
					</div>
				</div>
			</form>
		</div>
		<div class="background-white p20 mb50">
		<?php echo $reports->get_redeemed();?>
		</div>
	</div>
</div>
<?php echo $footer = $includes->get_footer(); ?>
<script type="text/javascript">
	$('#user').val("<?php echo $reports->get_user_id();?>");
	$('#category').val("<?php echo $reports->get_business_category_id();?>");
</script>