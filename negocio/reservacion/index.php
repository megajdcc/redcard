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
							
// $fecha = new DateTime();
// $fechaactual = $fecha->format('Y-m-d g:m A');

// echo $fechaactual;
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
						<audio id="soundnotification" src="<?php echo HOST.'/assets/sound_notification/Duet.ogg'?>" type="audio/ogg" preload="auto" controls>
							<style>
								#soundnotification{
									display: none;
								}
							</style>

				</div>
				<div class="row">
					<div class=" col-lg-12 form-group" data-toggle="tooltip" title="Haga su busqueda inteligente, si desea ser especifico encierre su busqueda entre comillas dobles." >
										<label for="busqueda">Buscar Reservaci&oacute;n | </span><i class="fa fa-question-circle text-secondary"></i></label>
										<div class="input-group">
											<strong class="input-group-addon"><i class="fa fa-search"></i></strong>
											<input type="text" class="form-control busqueda" name="buscar" autocomplete="false" placeholder="Busqueda inteligente...">
											
										</div>
									
										
										
									</div>
				</div>
				<table  id="listareservaciones" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th>#</th>
							<th>Fecha</th>
							<th>Hotel</th>
							<th>Solicita</th>
							<th>Personas</th>
							<th>Status</th>
							<th>Observaciones</th>
							<th></th>
						</tr>
					</thead>
					
					<tbody>
						
					</tbody>
	
				</table>
		</div>
	</div>
</div>


<script>


	$(document).ready(function() {

		var cantidad = 0;
		var count = 0 ;

		// tabla dinamica de reservaciones Negocios...
		// 
		 var reservaciones = $('#listareservaciones').DataTable( {
					paging        	:true,
					lengthChange	:false,
					scrollY      	:400,
					scrollCollapse	:true,
					ordering		:true,
					dom:'lrtip',
					ajax:{
						url:'/negocio/Controller/peticiones.php',
						type:'POST',
						dataType:'JSON',
						data:function(d){
							d.peticion   ='cargarreservaciones';
							d.filtro     ='';
							d.rango      = '';
							d.rango1     = '';
							d.rango2     =''; 
							d.restaurant = '';
							d.hotel      = '';
						}
					},
					columns:[
						 		{data:'id'},
						 		{data:'fecha'},
						 		{data:'hotel'},
						 		{data:'username'},
						 		{data:'numeropersona'},
						 		{data:'status'},
						 		{data:'observacion'},
						 		{data:'btncancelar'}

					 		],
			        language: {
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
					initComplete:function(setm,json){
						count = json.lenght;
					},
			         columnsDefs:[{
								"orderable":true,
								"targets":0,
								"visible":false,
								"searchable":false
					        },
					        {
					        	orderable:false,targets:1,
					        	width:700
					        },
					        {
					        	orderable:false,targets:2
					        },
					        {
					        	orderable:false,targets:3
					        },
					        {
					        	orderable:false,targets:4
					        },
					        {
					        	orderable:false,targets:5
					        },
					        {
					        	orderable:false,
					        	targets:6,
					        	width:'50px'
					        },
					        {
					        	orderable:false,
					        	targets:7,


					        }],
			       	 	order:[[0,'desc']]
			    });
		


		 setInterval(function(){

								reservaciones.ajax.reload(null,true);
								if( cantidad > count){
									$('#soundnotification')[0].play();
								}
							},10000);
		 reservaciones.on('draw',function(e,obj){

		

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
											reservaciones.ajax.reload();
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

		 	$('.observaciones').click(function(){
				alert($(this).attr('data-observacion'));
			});		

		 });

		   $('input[name="buscar"]').on('keyup',function(e){

					reservaciones.search(this.value).draw();
			   });
	});

		

    </script>
<?php echo $footer = $includes->get_footer(); ?>