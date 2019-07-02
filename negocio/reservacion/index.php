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

use negocio\libs\includes;
use negocio\libs\Restaurant;


$restaurant = new Restaurant($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){

 	if(isset($_POST['pdf'])){
 		$restaurant->report($_POST);
 		
 	}else if(isset($_POST['date_start'])){
		$restaurant->Buscar($_POST);
	} 
 	
}

	

							// echo var_dump(getdate())
							

$includes = new Includes($con);

$properties['title'] = 'Reservaciones | Travel Points';
$properties['description'] = 'Reservaciones en travel points';


echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>


<?php echo $con->get_notify();?>

<div class="row">
	<div class="col-sm-12">
		<?php echo $restaurant->getNotificacion();?>
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
										<input class="form-control" type="text" id="star" name="date_start" value="<?php echo $restaurant->getFecha1(); ?>" placeholder="Fecha inicial" />
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									</div>
								</div>
							</div>

							<div class="col-sm-4">
								<div class="form-group">
									<label for="end">Fecha de fin</label>
									<div class="input-group date" id="fechaend">

										<input class="form-control" type="text" id="en" name="date_end" value="<?php echo $restaurant->getFecha2(); ?>" placeholder="Fecha final" />
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
											<a href="<?php echo _safe(HOST.'/negocio/reservacion/');?>" class="btn btn-info">Limpiar</a>
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
							<th>Solicita</th>
							<th>Personas</th>
							<th>Status</th>
							<th>Observaciones</th>
						</tr>
					</thead>
					
					<tbody>
						<?php echo $restaurant->getReserva();?>
					</tbody>
	
				</table>
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
			        "order": [[ 0, 'asc' ]]
			    } );

	$(document).ready(function() {
		$('.observaciones').click(function(){
			alert($(this).attr('data-observacion'));
		});		 	
	});

    </script>
<?php echo $footer = $includes->get_footer(); ?>