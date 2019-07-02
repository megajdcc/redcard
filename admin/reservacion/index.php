<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

if(!isset($_SESSION['perfil'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
	}

use admin\libs\includes;
use admin\libs\Reservacion;


$reservacion = new Reservacion($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){

 	if(isset($_POST['pdf'])){
 		$reservacion->report($_POST);
 		
 	}else if(isset($_POST['date_start'])){
		$resercacion->Buscar($_POST);
	} 
 	
}

	

							// echo var_dump(getdate())
							

$includes = new Includes($con);

$properties['title'] = 'Reservaciones | Travel Points';
$properties['description'] = 'Reservaciones en travel points';


echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>


<?php echo $con->get_notify();?>

<div class="row">
	<div class="col-sm-12">
		<?php echo $reservacion->getNotificacion();?>
		<div class="background-white p20 mb50">
			<div class="page-title">
				<h1>Lista de Reservaciones</h1>
			</div>
			<div class="row">
					<div class=" col-sm-10">

						<form class="pull-right" method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>">
							
							<div class="col-sm-4">
								<div class="form-group">
									<label for="start">Fecha de inicio</label>
									<div class="input-group date" id="fechastart">
										<input class="form-control" type="text" id="star" name="date_start" value="<?php echo $reservacion->getFecha1(); ?>" placeholder="Fecha inicial" />
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									</div>
								</div>
							</div>

							<div class="col-sm-4">
								<div class="form-group">
									<label for="end">Fecha de fin</label>
									<div class="input-group date" id="fechaend">

										<input class="form-control" type="text" id="en" name="date_end" value="<?php echo $reservacion->getFecha2(); ?>" placeholder="Fecha final" />
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									</div>
									
								</div>
							</div>
								<script>
									$(document).ready(function() {

											$('#fechastart').datetimepicker({
												icons:{
													time: "fa fa-clock-o",
													date: "fa fa-calendar",
													up: "fa fa-chevron-up",
													down: "fa fa-chevron-down"
												},
												locale:'es',
												format:'YYYY-MM-DD',
											});

											$('#fechaend').datetimepicker({
												icons:{
													time: "fa fa-clock-o",
													date: "fa fa-calendar",
													up: "fa fa-chevron-up",
													down: "fa fa-chevron-down"
												},
												useCurrent:false,
												locale:'es',
												format:'YYYY-MM-DD',
												allowInputToggle:true
											});

											$('#fechastart').on('dp.change',function(e){
												$('#fechaend').data("DateTimePicker").minDate(e.date);
											});

											$('#fechaend').on('dp.change',function(e){
												$('#fechastart').data("DateTimePicker").maxDate(e.date);
											});


									});
								</script>
							<div class="col-sm-4">
									<label>Buscar</label>
									<div class="form-group">
											<button class="btn btn-success buscar" type="submit"><i class="fa fa-search"></i></button>
											<a href="<?php echo _safe(HOST.'/admin/reservacion/');?>" class="btn btn-info">Limpiar</a>
									</div>			
							</div>

						</form>
					</div>
					
					<div class="col-sm-2">
						<form class="pull-right" method="post" name="imprimir" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>" target="_blank">
					
							<label>Descargar</label>

							
							<div class="form-group">				
								<input type="hidden" name="start" value="">
								<input type="hidden" name="end" value="">
								
								<button class="btn btn-default text-danger" type="submit" name="pdf"><i class="fa fa-file-pdf-o"></i>PDF</button>
							</div>
							<script>
								
								$(document).ready(function() {
									
									$('form[name="imprimir"]').bind('submit',function(e){

										var start  = $('input[name="date_start"]').val();
										var end = $('input[name="date_end"]').val();

										$('input[name="start"]').val(start);
										$('input[name="end"]').val(end);

										return true;

									});


								});
							</script>
						</form>
					</div>
						

				</div>
				<div class="row">
						<table  id="listareservaciones" class="display" cellspacing="0" width="100%">
						<script>
							
							$(document).ready(function() {
								$('.cancelar-reserva').on('click',function(e){
									var idcancel = $(this).attr('data-idcancel');

								

									var result = confirm('Esta seguro de cancelar la reserva?');
									if(result){

										$.ajax({
											url: '/negocio/Controller/peticiones.php',
											type: 'POST',
											dataType: 'JSON',
											data: {peticion: 'cancelarreserva',idreserva:idcancel},
											})
										.done(function(response) {
											if(response.peticion){
											location.reload();
											}
											})
										.fail(function() {
											console.log("error");
											})
										.always(function() {
											console.log("complete");
											});

									}
									
									
								});
							});

						</script>
						<thead>
							<tr>
								<th></th>
								<th>Fecha</th>
								<th>Hotel</th>
								<th>Registrante</th>
								<th>Solicita</th>
								<th>Personas</th>
								<th>Status</th>
								
							</tr>
						</thead>
						
						<tbody>
							<?php echo $reservacion->getReservaciones();?>
						</tbody>
		
					</table>
				</div>
		
			
				
		</div>
		<div class="row">
				<section class="col-lg-6">
					<article id="grafica1">
						
					</article>

					<script>
						$(document).ready(function() {
							var fecha1 = "";
							var fecha2 = "";
						
							$.ajax({
								url: '/admin/controller/grafica.php',
								type: 'POST',
								dataType: 'json',
								data: {grafica: 'reservasporconcierge', f1:fecha1,f2:fecha2},
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
											        text: 'Reservas por registrante'
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
											               format: '{point.y:.0f}'
											            },
											            showInLegend:true,
											        }},

											    tooltip: {

											        pointFormat: '<span style="color:{point.color}">{point.name}</span>:{point.y:.0f}'
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
					
				</section>
				<section class="col-lg-6">
					<article id="grafica2">
						
					</article>
					

					<script>
						$(document).ready(function() {
							var fecha1 = "";
							var fecha2 = "";
						
							$.ajax({
								url: '/admin/controller/grafica.php',
								type: 'POST',
								dataType: 'json',
								data: {grafica: 'reservaspornegocio', f1:fecha1,f2:fecha2},
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
											        text: 'Reservas por Restaurant'
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
											               format: '{point.y:.0f}'
											            },
											            showInLegend:true,
											        }},

											    tooltip: {

											        pointFormat: '<span style="color:{point.color}">{point.name}</span>:{point.y:.0f}'
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
				</section>
			</div>
	</div>
</div>


<script>

				 var t = $('#listareservaciones').DataTable( {
					"paging"        :         false,
					"scrollY"       :        "400px",
					"scrollCollapse": true,
					"ordering": true,
					"lengthChange":false,
			         "language": {
			                        "lengthMenu": "Mostar _MENU_ registros por pagina",
			                        "info": "",
			                        "infoEmpty": "No se encontro Ninguna reservación",
			                        "infoFiltered": "(filtrada de _MAX_ registros)",
			                        "search": "Buscar: ",
			                        "paginate": {
			                            "next":       "Siguiente",
			                            "previous":   "Anterior"
			                        },
			                    },
			        "columnDefs": [ {
			            "searchable": true,
			            "orderable": true,
			            "targets": 0
			        } ],
			        "order": [[ 0, 'desc' ]]
			    } );

	$(document).ready(function() {
		$('.observaciones').click(function(){
			alert($(this).attr('data-observacion'));
		});		 	
	});

    </script>
<?php echo $footer = $includes->get_admin_footer(); ?>