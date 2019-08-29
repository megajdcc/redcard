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
use admin\libs\Reservacion;

$restaurant = new Restaurant($con);
$reservacion = new Reservacion($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){

 	if(isset($_POST['pdf'])){
 		$restaurant->report($_POST,'Lista de reservaciones');
 	}

}

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
					<div class="col-sm-12 filtros">
					<h3 class="form form-control">Filtre por</h3>

					<script>
						

						$(document).ready(function() {
							$('.btn-selec').on('click',function(e){

								$('#collapseDos').collapse('hide');
							

							
								var exp = $(this).attr('aria-controls');
								$('#'+exp).collapse('toggle');

								$('.btn-selec').removeClass('active');
								$(this).addClass('active');

							});
						});
					</script>
							<div class="btn-group btn-group-toggle filtro" data-toggle="buttons"  aria-label="decision filtro">
								
								<select class="btn-group form-control d-inline-flex" id="del" name="del">
									<option value="0" selected>Dia</option>
									<option value="1">Dia Anterior</option>
									<option value="2">Mes</option>
									<option value="3">Mes Anterior</option>
									<option value="4">Por Rango</option>
								</select>
								<select class="btn-group form-control d-inline-flex" id="sel-hotel" name="hoteles" data-live-search="true">
									<option value="0" selected>Todos los hoteles</option>
										<?php echo $reservacion->getHoteles();?>
								</select>
								
						
								<form class="col-lg-6" method="post" name="imprimir" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>" target="_blank">
							
							<div class="form-group">				
								<input type="hidden" name="rango1" value="">
								<input type="hidden" name="rango2" value="">
								<input type="hidden" name="filtro" value="">
								<input type="hidden" name="rango" value="">
								<input type="hidden" name="hotel" value="">
								
								<button class="btn btn-default text-danger" type="submit" name="pdf"><i class="fa fa-file-pdf-o"></i>PDF</button>
								<a href="<?php echo _safe(HOST.'/negocio/reservacion/');?>" class="btn btn-info">Limpiar</a>
							</div>
						</form>

							</div>
				</div>
				<div class=" col-lg-12">


					<section class="collapse lapso-fecha" id="collapseUno" >
						<div class="row">
							<form class="col-lg-10" method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>">
							
							<div class="col-sm-4">
								<div class="form-group">
									<label for="start">Fecha de inicio</label>
									<div class="input-group date" id="fechastart">
										<input class="form-control" type="text" id="star" name="date_start" value="" placeholder="Fecha inicial" />
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									</div>
								</div>
							</div>

							<div class="col-sm-4">
								<div class="form-group">
									<label for="end">Fecha de fin</label>
									<div class="input-group date" id="fechaend">

										<input class="form-control" type="text" id="en" name="date_end" value="" placeholder="Fecha final" />
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
										<button type="button" class="btn btn-success buscar" type="submit"><i class="fa fa-search"></i></button>
									</div>			
							</div>
						</form>
						</div>
					</section>
				</div>

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

		cargarCantidad();
		
		var count = 0 ;
		var filtro = $('select[name="del"]').val();
		var boolrango = false;
		var hotel = $('select[name="hoteles"]').val();

		$('select[name="del"]').on('change',function(){
					filtro = $(this).val();

					if(filtro == 4){
						$('.btn-selec').removeAttr('disabled');
						$('#collapseUno').collapse('show');
						boolrango = true;
					}else{
						boolrango = false;
						$('.btn-selec').attr('disabled', 'disabled');
						$('#collapseUno').collapse('hide');
						reservaciones.ajax.reload();


					
					}
		});

		$('form[name="imprimir"]').on('submit',function(){
			$('input[name="rango1"]').val(getRango1());
			$('input[name="rango2"]').val(getRango2());
			$('input[name="filtro"]').val(getFiltro());
			$('input[name="hotel"]').val(getHotel());
			$('input[name="rango"]').val(isRango());
			return true;
		});

		function getFiltro(){
				return $('select[name="del"]').val();
		}

		function isRango(){
			return boolrango;
		}

		function getRango1(){
			return $('input[name="date_start"]').val();
		}
		function getRango2(){
			return $('input[name="date_end"]').val();
		}
		function getHotel(){
			return hotel;
		}

	


		// tabla dinamica de reservaciones Negocios...
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
							d.filtro     =getFiltro();
							d.rango      =isRango();
							d.rango1     =getRango1();
							d.rango2     =getRango2(); 
							d.hotel      =getHotel();
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

		 				var soundnotificatio = '<audio id="soundnotification" src="<?php echo HOST.'/assets/sound_notification/Popcorn.ogg'?>" type="audio/ogg" preload="auto" controls>';
		 						if($('#soundnotification').length){
		 							$('#soundnotification').remove();
		 						}

								reservaciones.ajax.reload(null,true);
								
								if(cantidad < reservaciones.data().count()){
									$(soundnotificatio).appendTo('body');
									$('#soundnotification')[0].play();
									cantidad = reservaciones.data().count();
								}
			},5000);
	

			function cargarCantidad(){

				$.ajax({
					url: '/negocio/Controller/peticiones.php',
					type: 'POST',
					dataType: 'JSON',
					data: {peticion: 'capturarcantidadreservaciones'},
				})
				.done(function(response) {
					if(response.peticion){
						cantidad = response.cantidad;
						return cantidad;
					}
				})
				.fail(function() {
					return cantidad;
				})
				.always(function() {
					return cantidad;
				});
				
			}
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


		   	$('select[name="hoteles"]').change(function(){
				hotel = $(this).val();
				reservaciones.ajax.reload();
		});

		$('.buscar').on('click',function(){
			reservaciones.ajax.reload(null,true);
		});





	});

		

    </script>
<?php echo $footer = $includes->get_footer(); ?>