<?php 
	
	require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';
	$con = new assets\libs\connection();

	use Hotel\models\Includes;
	use Hotel\models\Dashboard;
	use assets\libraries\pagination\pagination;
	use Hotel\models\Home;
	use admin\libs\reports_sales;

	$hotel = new Dashboard($con);


	if($_SERVER["REQUEST_METHOD"] == "POST"){
			if(isset($_POST['pdf'])){
				$businesses->get_businesses_pdf();
				die();
			}
	}

	if(!isset($_SESSION['perfil'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
	}
	
	if(!isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
	}



	$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));
	$rpp = 20;
	$options = $hotel->load_data($page, $rpp);

	$paging = new pagination($options['page'], $options['total']);
	$paging->setRPP($rpp);
	$paging->setCrumbs(10);

	// if($_SERVER["REQUEST_METHOD"] == "POST"){
	// 	if(isset($_POST['business_id']) && isset($_POST['suspend_id'])){
	// 	//	$hotel->change_business_status($_POST);
	// 	}
	// } 

	$home = new Home($con);
	 
	if(isset($_POST['change_business'])){
		$home->change_business($_POST['change_business']);
	}

	$includes = new Includes($con);

	$properties['title'] = 'Hotel | Travel Points';
	$properties['description'] = '';
	
	echo $header = $includes->get_no_indexing_header($properties);
	echo $navbar = $includes->get_admin_navbar();

	echo $con->get_notify(); ?>
	<div class="row">
	<div class="col-sm-12 ">
		<?php echo $home->getNotificacion();?>
		<div class="background-white p20 mb30">
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
							<a href="<?php echo _safe(HOST.'/Hotel/');?>" class="btn btn-info">Limpiar</a>
						</div>
					</div>
				</div>
			</form>
		</div>
		<!-- /.box -->

		<div class="row">
			<div class="col-sm-4">
				
				<!-- TRES CUADROS >>> -->
					<div class="statusbox">
						<h2>Total Comisiones Hotel</h2>
							<div class="statusbox-content">
									<?php echo $home->getComisiones();?>
							</div><!-- /.statusbox-content -->
					</div>
			
				
			</div>
				
				<div class="col-sm-4">
						<div class="statusbox">
							<h2>Operaciones</h2>
							<div class="statusbox-content">
								<strong><?php echo $home->getOperaciones(); ?></strong>
							</div>
						</div>
				</div>
				<div class="col-sm-4">
						<div class="statusbox">
							<h2>Negocios</h2>
							<div class="statusbox-content">
								<?php echo $home->getOperacionesNegocios(); ?>
							</div>
						</div>
				</div>

		</div>
				
				

		
		

			
				<div class="row">

					<!-- NEgocios deudores -->
					<div class="col-sm-4 h-100">
						<div class="statusbox">
							<h2>Negocio Deudores</h2>
							<div class="statusbox-content total-adeudo">
								<strong><?php echo $home->getNegociosDeudores();?></strong>
							</div><!-- /.statusbox-content -->
						</div>
					</div>
			
				<!-- Total Adeudo -->
				<div class="col-sm-4 h-100">
					<div class="statusbox">
						<h2>Total Adeudos</h2>
						<div class="statusbox-content total-adeudo">
							<strong><?php echo $home->getTotalComisionAdeudo();?></strong>
						</div><!-- /.statusbox-content -->
					</div>
		
				</div>
			</div>
					
					
				
				

				<div class="row">

				<div class="col-sm-4">
							<div class="statusbox">
								<h2>Consumo Promedio</h2>
								<div class="statusbox-content">
									<strong>$<?php echo $home->getPromedioConsumo();?></strong>
									<strong>MXN</strong>
								</div>
							</div>

							
				</div>

					<div class="col-sm-8">
					<div id="grafica1" class="statusbox">
					<script>
						$(document).ready(function() {
							
							var idhotel = "<?php echo $home->hotel['id'];?>"
							$.ajax({
								url: '/Hotel/controller/grafica.php',
								type: 'POST',
								dataType: 'json',
								data: {grafica: 'consumospromedioporcompra', idhotel,hotel:idhotel},
							})
							.done(function(response) {
								var options = {
											chart: {
												renderTo: 'grafica1',
												plotBackgroundColor: null,
												plotBorderWidth: null,
												plotShadow: false,
												type: 'pie'
											},
											title: {
												text: "Promedio por consumo por Huesped"
											},
											tooltip: {
												pointFormat: '<b>{point.percentage:.1f}%</b>'
											},
											legend: {
												enabled: false
											},
											  plotOptions: {
										        pie: {
										            allowPointSelect: true,
										            cursor: 'pointer',
										            dataLabels: {
										                enabled: true,
										                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
										                style: {
										                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
										                }
										            }
										        }
										    },
											
								    		series: [{}]
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

					<div class="statusbox" id="grafica2">
									<script>
						$(document).ready(function() {
							
							var idhotel = "<?php echo $home->hotel['id'];?>"
							$.ajax({
								url: '/Hotel/controller/grafica.php',
								type: 'POST',
								dataType: 'json',
								data: {grafica: 'consumospromediopornegocio', idhotel,hotel:idhotel},
							})
							.done(function(response) {
								var options = {
											chart: {
												renderTo: 'grafica2',
												plotBackgroundColor: null,
												plotBorderWidth: null,
												plotShadow: false,
												type: 'pie'
											},
											title: {
												text: "Promedio por consumo por Negocio"
											},
											tooltip: {
												pointFormat: '<b>{point.percentage:.1f}%</b>'
											},
											legend: {
												enabled: false
											},
											  plotOptions: {
										        pie: {
										            allowPointSelect: true,
										            cursor: 'pointer',
										            dataLabels: {
										                enabled: true,
										                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
										                style: {
										                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
										                }
										            }
										        }
										    },
											
								    		series: [{}]
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

	
		<div class="row">
			<div class="col-sm-4">
				<div class="statusbox">
					<h2>Usuarios Registrados</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->getUsuarios();?></strong>
						
					</div><!-- /.statusbox-content -->
				</div>

				<div class="statusbox">
					<h2>Usuario participantes</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->getUsuariosParticipantes();?></strong>
				
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-8">
				<div class="statusbox ttconsumosusuarios" id="grafica3">
					<script>
						$(document).ready(function() {
							
							var idhotel = "<?php echo $home->hotel['id'];?>"
							$.ajax({
								url: '/Hotel/controller/grafica.php',
								type: 'POST',
								dataType: 'json',
								data: {grafica: 'totalconsumohuesped', idhotel,hotel:idhotel},
							})
							.done(function(response) {

								// Grafica total consumo por usuario...
								var options = {
											 chart: {
											 				renderTo: 'grafica3',
											        type: 'column'
											    },
											   lang:{
															decimalPoint: ',',
								   						thousandsSep: '.'
													},
											    title: {
											        text: 'Total Consumos por Usuarios Huepedes'
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
											        series: {
																	borderWidth: 0,
											            dataLabels: {
											               enabled: true,
											               format: '$ {point.y:.2f} MXN'
											            }
											        }
											    },

											    tooltip: {
											        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b> ${point.y:.2f}</b>MXN<br/>'
											    },
											    series: [ {
											    	name: "Huespedes",
            								colorByPoint: true,
											    } ],
								   				}; 

								   				options.series[0].data = response;
								   			
													var grafica = new Highcharts.Chart(options);
									 	
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
					<h2>Puntos generados</h2>
					<div class="statusbox-content">
						<strong>$<?php echo $home->getPuntosGenerados();?></strong>
						<strong>MXN</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>puntos canjeados</h2>
					<div class="statusbox-content">
						<strong>$<?php echo $home->getPuntosCanjeados();?></strong>
						<strong>MXN</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>regalos entregados</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->getRegalosEntregados();?></strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
		</div>


		<div class="row">

				<div class="col-sm-8">
					<div class="statusbox ttconsumosusuarios" id="grafica4">
							<script>
						$(document).ready(function() {
							
							var idhotel = "<?php echo $home->hotel['id'];?>"
							$.ajax({
								url: '/Hotel/controller/grafica.php',
								type: 'POST',
								dataType: 'json',
								data: {grafica: 'totalregalosusuarios', idhotel,hotel:idhotel},
							})
							.done(function(response) {

								// Grafica total consumo por usuario...
								var options = {
											chart: {
														renderTo: 'grafica4',
	       										type: 'column'
												    },
												    title: {
												        text: 'Cantidad de regalos entregados a usuarios Huespedes'
												    },
												    xAxis: {
												        type: 'category',
												        labels: {
												            rotation: -45,
												            style: {
												                fontSize: '13px',
												                fontFamily: 'Myriad'
												            }
												        }
												    },
												    yAxis: {
												        min: 0,
												        title: {
												            text: 'Cantidad de regalos'
												        }
												    },
												    legend: {
												        enabled: false
												    },
												    tooltip: {
												        pointFormat: 'Regalos entregados: <b>{point.y:.0f}</b>'
												    },
												    series: [{
												        name: 'Population',
												        data: [],
												        dataLabels: {
												            enabled: true,
												            rotation: -90,
												            color: '#FFFFFF',
												            align: 'right',
												            format: '{point.y:.0f}', // one decimal
												            y: 10, // 10 pixels down from the top
												            style: {
												                fontSize: '13px',
												                fontFamily: 'Verdana, sans-serif'
												            }
												        },
												        colorByPoint: true
												    }]
												   }
								   				options.series[0].data = response;
								   			
													var grafica = new Highcharts.Chart(options);									 	
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
			
				
				

				<div class="col-sm-4">
					<div class="statusbox">
						<h2>Valor Regalos Entregados</h2>
						<div class="statusbox-content">
							<strong>$<?php echo $home->getTotalValorRegalos();?></strong>
							<strong>MXN</strong>
						</div><!-- /.statusbox-content -->
					</div>

				<div class="statusbox">
			
							<h2>Valor Regalo Promedio</h2>
								<div class="statusbox-content">
									<strong>$<?php echo $home->getValorRegaloPromedio();?></strong>
									<strong>MXN</strong>
								</div><!-- /.statusbox-content -->
					
				</div>

				</div>
		</div>
	

			
		
	</div>
</div>
<?php echo $footer = $includes->get_admin_footer(); ?>



