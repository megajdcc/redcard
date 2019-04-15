<?php 


require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 
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
		$businesses->get_businesses_pdf();
		die();
	}
}

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));
$rpp = 20;
$options = $businesses->load_data($page, $rpp);

$paging = new assets\libraries\pagination\pagination($options['page'], $options['total']);
$paging->setRPP($rpp);
$paging->setCrumbs(10);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['business_id']) && isset($_POST['suspend_id'])){
		$businesses->change_business_status($_POST);
	}
}  
$home = new admin\hotel\Models\EstadisticasHome($con);
$reports = new admin\libs\reports_sales($con);
 
if(isset($_POST['change_business'])){
	$home->change_business($_POST['change_business']);
}

$includes = new admin\hotel\Models\Includes($con);

$properties['title'] = 'Hotel | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); 

?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $home->get_notification();?>
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
						<label>Buscar</label>
						<div class="form-group">
							<button class="btn btn-success" type="submit"><i class="fa fa-search"></i></button>
							<a href="<?php echo _safe(HOST.'/admin/');?>" class="btn btn-info">Limpiar</a>
						</div>
					</div>
				</div>
			</form>
		</div>
		<!-- /.box -->

		<div class="row">
			<?php echo $home->get_sales();?>
			<div class="col-sm-3 col-lg-3">
				<div class="statusbox">
					<h2>Operaciones</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->get_operations();?></strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Negocios</h2>
					<div class="statusbox-content">
						<strong>AFILIADOS: <?php echo $home->get_businesses();?></strong>
						<strong>OPERADOS: <?php echo $home->get_operations();?></strong>
						<strong><?php echo $home->get_negocios();?>%</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Ventas promedio por negocio</h2>
					<div class="statusbox-content">
						<strong>$<?php echo $home->get_usd_sales();?> USD</strong>
						<strong>$<?php echo $home->get_mxn_sales();?> MXN</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Negocio Deudores</h2>
					<div class="statusbox-content total-adeudo">
						<strong><?php echo $home->get_business_debt();?></strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Total Adeudos</h2>
					<div class="statusbox-content total-adeudo">
						<strong>$<?php echo $home->get_toatl_debt();?></strong>
						<strong>MXN</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Saldo A Favor</h2>
					<div class="statusbox-content">
						<strong>$<?php echo $home->get_toatl_commision();?></strong>
						<strong>MXN</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Total Comisiones</h2>
					<div class="statusbox-content">
						<strong>$<?php echo $home->get_toatl_commision();?></strong>
						<strong>MXN</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			
		</div>
		<div class="row">
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Comision Promedio</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->get_average_commision();?> %</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Consumo Promedio</h2>
					<div class="statusbox-content">
						<strong>$<?php echo $home->get_average_consuption();?></strong>
						<strong>MXN</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Utilidad Bruta</h2>
					<div class="statusbox-content">
						<strong>$<?php echo $home->get_raw_utility();?></strong>
						<strong>MXN</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Total Comisiones Hotel</h2>
					<div class="statusbox-content">
						<strong>$<?php echo $home->getPorcentageComisionHotel();?></strong>
						<strong>MXN</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Total Comisiones Referidor</h2>
					<div class="statusbox-content">
						<strong>$<?php echo $home->get_commision_referral();?></strong>
						<strong>MXN</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Total Com.Franquiciatario</h2>
					<div class="statusbox-content">
						<strong>$<?php echo $home->get_commision_franchiser();?></strong>
						<strong>MXN</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Total Fondos Tienda</h2>
					<div class="statusbox-content">
						<strong>$<?php echo $home->get_total_amount_store();?></strong>
						<strong>MXN</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Usuarios Registrados</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->get_total_users();?></strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Usuarios Participantes</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->get_total_participant_users();?></strong>
						<!-- <p style="color: black;">40%</p> -->
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Consumo Usuario Promedio</h2>
					<div class="statusbox-content">
						<strong>$<?php echo $home->get_user_spent();?></strong>
						<strong>MXN</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Registros Por Usuario</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->get_registration_per_user();?></strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Puntos Generados</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->get_toatl_commision();?></strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Puntos Canjeados</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->get_user_total_points();?></strong>
						<!-- <strong>80%</strong> -->
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Puntos Caducados</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->get_user_total_old_points();?></strong>
						<!-- <strong>1%</strong> -->
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Valor Regalos Entregados</h2>
					<div class="statusbox-content">
						<strong>$<?php echo $home->get_total_amount_store();?></strong>
						<strong>MXN</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Regalos Entregados</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->get_total_gifts();?></strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Valor Regalo Promedio</h2>
					<div class="statusbox-content">
						<strong>$<?php echo $home->get_average_amount_store();?></strong>
						<!-- <strong>MXN</strong> -->
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Regalos Por Usuario</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->get_total_requested_gifts();?></strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
		</div><!-- /.row -->
		
	</div>
</div>
<?php echo $footer = $includes->get_admin_footer(); ?>