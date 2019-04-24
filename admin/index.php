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

use admin\libs\Home;
$home = new Home($con);
// $reports = new admin\libs\reports_sales($con);
 
if(isset($_POST['change_business'])){
	$home->change_business($_POST['change_business']);
}

$includes = new admin\libs\includes($con);
$properties['title'] = 'Travel Points';
$properties['description'] = '';

echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); 

?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $home->getNotificacion();?>
<!-- 		<div class="background-white p20 mb30">
			<form method="post">
				<div class="row">
					<div class="col-sm-4">
						<div class="form-group">
							<label for="start">Fecha y hora de inicio</label>
							<div class="input-group date" id="event-start">
								<input class="form-control" type="text" id="start" name="date_start" value="" placeholder="Fecha y hora de inicio" required/>
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							</div>
							
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label for="end">Fecha y hora de fin</label>
							<div class="input-group date" id="event-end">
								<input class="form-control" type="text" id="end" name="date_end" value="" placeholder="Fecha y hora de fin" required/>
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							</div>
							
						</div>
					</div>
					<div class="col-sm-4">
						<label>Buscar</label>
						<div class="form-group">
							<button class="btn btn-success" type="submit"><i class="fa fa-search"></i></button>
							<a href="" class="btn btn-info">Limpiar</a>
						</div>
					</div>
				</div>
			</form>
		</div> -->
		<!-- /.box -->




		<div class="row">
			
			<div class="col-sm-3 col-lg-3">

				<?php echo $home->getVentas();?>
				<div class="statusbox">
					<h2>Operaciones</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->getOperaciones();?></strong>
					</div><!-- /.statusbox-content -->
				</div>

				<div class="statusbox">
					<h2>Negocios</h2>
					<div class="statusbox-content">
						<strong>AFILIADOS: <?php echo $home->getAfiliados();?></strong>
						<strong>OPERADOS: <?php echo $home->getOperados();?></strong>
						<strong><?php echo $home->getPorcentaje();?>%</strong>
					</div><!-- /.statusbox-content -->
				</div>

			</div>

			
			<style>
			#grafica1{
				height: 720px !important;
			}
			#grafica2{
				height: 470px !important;
			}
				
			</style>
			<div class="col-sm-9">
				<!-- Ventas promedio por negocios -->
				<div class="statusbox" id="grafica1">
					<script>
						$(document).ready(function() {
						
							$.ajax({
								url: '/admin/controller/grafica.php',
								type: 'POST',
								dataType: 'json',
								data: {grafica: 'ventaspromediopornegocios'},
							})
							.done(function(response) {
								var options = {
											 chart: {
											 		renderTo: 'grafica1',
											        type: 'pie'
											    },
											   lang:{
															decimalPoint: ',',
								   						thousandsSep: '.'
													},
											    title: {
											        text: 'Ventas promedio por negocios'
											    },
											    xAxis: {
											        type: 'category'
											    },
											    yAxis: {
										        title: {
										            text: 'Total miles de $'
										          }
										        },
											    
											    plotOptions: {
											        pie: {
														allowPointSelect:true,
														cursor:'pointer',
														borderWidth: 0,
											            dataLabels: {
											               enabled: true,
											               format: '$ {point.y:.2f} MXN'
											            },
											            showInLegend:true,
											        }},

											    tooltip: {
											        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b> ${point.y:.2f}</b>MXN<br/>'
											    },
											    series: [ {
											    	name: "Huespedes",
            										colorByPoint: true,
											    } ],
								   				}; 
									 options.series[0].data = response;
									
									var grafica = Highcharts.chart(options);
									 	
									})	
							.fail(function() {
								console.log("error");
							})
							.always(function() {
								console.log("complete");
							});
						
							});

						</script>
				</div>
			</div>

		</div>




		<div class="row">

			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Negocio Deudores</h2>
					<div class="statusbox-content total-adeudo">
						<strong><?php echo $home->getNegociosDeudores();?></strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>

			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Total Adeudos</h2>
					<div class="statusbox-content total-adeudo">
						<strong>$<?php echo $home->getTotalDeuda();?></strong>
						<strong>MXN</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>

			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Saldo A Favor</h2>
					<div class="statusbox-content">
						<strong>$<?php  echo $home->getSaldoFavor();?></strong>
						<strong>MXN</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Total Comisiones</h2>
					<div class="statusbox-content">
						<strong>$<?php  echo $home->getTotalComision();?></strong>
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
						<strong><?php  echo $home->getComisionPromedio();?> %</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Consumo Promedio</h2>
					<div class="statusbox-content">
						<strong>$<?php echo $home->getConsumoPromedio();?></strong>
						<strong>MXN</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Utilidad Bruta</h2>
					<div class="statusbox-content">
						<strong>$<?php echo $home->getUtilidadBruta();?></strong>
						<strong>MXN</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>

		</div>



		<div class="row">

			<div class="col-sm-9">
				<div class="statusbox" id="grafica2">
			<script>
						$(document).ready(function() {
						
							$.ajax({
								url: '/admin/controller/grafica.php',
								type: 'POST',
								dataType: 'json',
								data: {grafica: 'comisionperfiles'},
							})
							.done(function(response) {
								var options = {
											 chart: {
											 		renderTo: 'grafica2',
											        type: 'pie'
											    },
											   lang:{
															decimalPoint: ',',
								   						thousandsSep: '.'
													},
											    title: {
											        text: 'Total Comisiones de Hotel,Franquiciatario y Referidor'
											    },
											    xAxis: {
											        type: 'category'
											    },
											    yAxis: {
										        title: {
										            text: 'Total miles de $'
										          }
										        },
											    
											    plotOptions: {
											        pie: {
														allowPointSelect:true,
														cursor:'pointer',
														borderWidth: 0,
											            dataLabels: {
											               enabled: true,
											               format: '$ {point.y:.2f} MXN'
											            },
											            showInLegend:true,
											        }},

											    tooltip: {
											        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b> ${point.y:.2f}</b>MXN<br/>'
											    },
											    series: [ {
											    	name: "Huespedes",
            										colorByPoint: true,
											    } ],
								   				}; 
									 options.series[0].data = response;
									
									var grafica = Highcharts.chart(options);
									 	
									})	
							.fail(function() {
								console.log("error");
							})
							.always(function() {
								console.log("complete");
							});
						
							});

						</script>
				</div>
			</div>
		</div>


		<div class="row">

			<div class="col-sm-3">

				<div class="statusbox">
					<h2>Usuarios Registrados</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->getTotalUsuario();?></strong>
					</div><!-- /.statusbox-content -->
				</div>

				
			</div>

			<div class="col-sm-3">

				<div class="statusbox">
					<h2>Usuarios Participantes</h2>
					<div class="statusbox-content">
						<?php echo $home->getUsuariosParticipantes(); ?>
					</div><!-- /.statusbox-content -->
				</div>
				
			</div>

			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Consumo Usuario Promedio</h2>
					<div class="statusbox-content">
						<strong>$<?php echo $home->getConsumoUsuarioPromedio();?></strong>
						<strong>MXN</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>

			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Registros Por Usuario</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->getRegistroPorUsuario();?></strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>



		</div>

		<div class="row">


			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Puntos Generados</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->getTotalPuntos();?></strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>

			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Puntos Canjeados</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->getPuntosCanjeados();?></strong>
						<!-- <strong>80%</strong> -->
					</div><!-- /.statusbox-content -->
				</div>
			</div>

		
		<div class="row">

			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Valor Regalos Entregados</h2>
					<div class="statusbox-content">
						<strong>$<?php echo $home->getValorRegalosEntregado();?></strong>
						<strong>MXN</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>

			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Regalos Entregados</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->getCantidadRegalosEntregado();?></strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			</div>

			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Valor Regalo Promedio</h2>
					<div class="statusbox-content">
						<strong>$<?php echo $home->getValorRegaloPromedio();?></strong>
						<strong>MXN</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>

			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Regalos Por Usuario</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->getRegalosPorUsuarioDeseo()?></strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>

		</div><!-- /.row -->
		
	</div>
</div>
<?php echo $footer = $includes->get_admin_footer(); ?>