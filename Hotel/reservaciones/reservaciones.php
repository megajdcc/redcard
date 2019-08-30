<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';
$con = new assets\libs\connection();

if(!isset($_SESSION['perfil']) && !isset($_SESSION['promotor']) && !isset($_SESSION['user'])){
		http_response_code(404);
		include(ROOT.'/errores/404.php');
		die();
}


use Hotel\models\Usuarios;
use Hotel\models\Includes;
use Hotel\models\Reservacion;
use admin\libs\Reservacion as reservacionadmin;

$reserva = new Reservacion($con);
$usuarios = new Usuarios($con);
$adminreservacion = new reservacionadmin($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
 	if(isset($_POST['imprimir'])){
 		
 		$reserva->imprimir($_POST['imprimir']);
 	}

 	if(isset($_POST['pdf'])){
 		$reserva->report($_POST);
 	}
}


$includes = new Includes($con);

$properties['title'] = 'Reservaciones | Travel Points';
$properties['description'] = 'Reservaciones en travel points';


echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>


<?php echo $con->get_notify();?>

<div class="row">
	<div class="col-sm-12">
		<?php echo $usuarios->getNotificacion();?>
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
								<select class="btn-group form-control d-inline-flex" id="sel-restaurant" name="restaurantes" data-live-search="true">
									<option value="0" selected>Todos los restaurantes</option>
									<?php echo $adminreservacion->getRestaurantes();?>
								</select>

						
								<form class="col-lg-12 col-xs-12" method="post" name="imprimir" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>" target="_blank">
							
							<div class="form-group">				
								<input type="hidden" name="rango1" value="">
								<input type="hidden" name="rango2" value="">
								<input type="hidden" name="filtro" value="">
								<input type="hidden" name="rango" value="">
								<input type="hidden" name="restaurant" value="">
								
								<button class="btn btn-default text-danger" type="submit" name="pdf"><i class="fa fa-file-pdf-o"></i>PDF</button>
								<a href="<?php echo _safe(HOST.'/Hotel/reservaciones/reservaciones');?>" class="btn btn-info">Limpiar</a>
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

			<div class="row">
					<table  id="listareservaciones" class="display" cellspacing="0" width="100%">

						<thead>
							<tr>
								
								<th>Negocio</th>
								<th>Usuario</th>
								<th>Registrante</th>
								<th>Status</th>
								<th>Fecha reserva</th>
								<th>Personas</th>
								<th>Observaciones</th>
								<th></th>
								<th></th>
								
							</tr>
						</thead>
						
						<tbody>
						
						</tbody>

					</table>
			</div>

				<button type="button" class="btn btn-success reservar-url" data-url="<?php echo HOST.'/Hotel/reservaciones/'?>">Reservar</button>
		</div>

		<div id="grafica-reservaciones-mensuales">

		</div>
	</div>
</div>

<?php echo $usuarios->Modal(); ?>
<script>

	$(document).ready(function() {
		var grafica = null;
		cargarGrafica();



		$('.reservar-url').on('click',function(e){
			var url = $(this).attr('data-url');
			$('.content-admin').slideUp({
				duration:500,
				
				done:function(){
					location.replace(url);
				}
			});
			
		});

		var options  = {
							title: {
								text: 'Reservaciones Mensuales de este año'
							},

							yAxis: { // left y axis
								title: {
								text: 'Número de reservaciones'
								}
							},
							xAxis:{
								categories:['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Optubre','Noviembre','Diciembre']
							},
							legend:{
								layout:'horizontal',
								align:'center',
								verticalAlign:'bottom'
							},
							plotOptions:{
								series:{
									label:{
										connectorAlowed:false
									},

								},
								line:{
									dataLabels:{
										enabled:false
									},
								allowPointSelect:true,
								},
								enableMouseTracking:false
							},
							chart:{
								panning:true
							},
							tooltip:{
								formatter:function(tooltip){
									if(this.y == 0 ){
										return null;
									}else{
										return 'Month of '+this.x+'<br/><strong>'+this.series.name+'</strong>: '+this.y+' Reservaciones<br/>';
									}

									
								},
								crosshairs:[true,true]
							},
							credits:{
								enabled:true,
								href:'https://travelpoints.com.mx',
								text:'Travel Points in Puerto Vallarta'
							},
							series:[{}],
							responsive:{
								rules:[{
									condition:{
										maxWidth:500
									},
									charOptions:{
										legend:{
											layout:'horizontal',
											align:'center',
											verticalAlign:'bottom'
										}
									}
								}]
							}};

		function cargarGrafica(){
				$.ajax({
				url: '/Hotel/controller/peticiones.php',
			 	type: 'POST',
			 	dataType: 'JSON',
			 	data: {peticion: 'grafica-reservaciones-mensuales'},

			})
			.done(function(response) {
				options.series = response;
				grafica = 	Highcharts.chart('grafica-reservaciones-mensuales',options
					);
				
			});

		}

		var datosTabla = null;

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

		var filtro = $('select[name="del"]').val();


		var boolrango = false;
		var restaurant = $('select[name="restaurantes"]').val();
		

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
						t.ajax.reload();


							setInterval(function(){
								t.ajax.reload(null,true);
							},30000);
					}
		});

		$('select[name="restaurantes"]').change(function(){
				restaurant = $(this).val();
				t.ajax.reload();
		});

		$('select[name="hoteles"]').change(function(){
				hotel = $(this).val();
				t.ajax.reload();
		});

		$('.buscar').on('click',function(){
			t.ajax.reload(null,true);
		});

		function getRestaurant(){
			return restaurant;
		}

		function isRango(){
			return boolrango;
		}
		function getFiltro(){
				return $('select[name="del"]').val();
		}

		function getPeticion(){
			return 'cargarreservaciones';
		}

		function getRango1(){
			return $('input[name="date_start"]').val();
		}
		function getRango2(){
			return $('input[name="date_end"]').val();
		}


		$('form[name="imprimir"]').on('submit',function(){
			$('input[name="rango1"]').val(getRango1());
			$('input[name="rango2"]').val(getRango2());
			$('input[name="filtro"]').val(getFiltro());
			$('input[name="restaurant"]').val(getRestaurant());
			$('input[name="rango"]').val(isRango());

			return true;
		});
		

		 var t = $('#listareservaciones').DataTable({
					paging        	:true,
					lengthChange	:false,
					scrollY      	:400,
					scrollCollapse	:true,
					ordering		:true,
					
					dom:'lrtip',
					ajax:{
						url:'/Hotel/controller/peticiones.php',
						type:'POST',
						dataType:'JSON',
						data:function(d){
							d.peticion ='cargarreservaciones';
							d.filtro   =getFiltro();
							d.rango = isRango();
							d.rango1 = getRango1();
							d.rango2 = getRango2();
							d.restaurant = getRestaurant();
						
						}
					},
					
					columns:[
						 		
						 		{data:'negocio'},
						 		{data:'nombrecompleto'},
						 		{data:'usuario_registrante'},
						 		{data:'status'},
						 		{data:'fecha'},
						 		{data:'numeropersona'},
						 		
						 		{data:'observacion'},
						 		{data:'impresion'},
						 		{data:'cancelar'}
					 		],
			         language:{
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
			        columnsDefs:[{
			        	orderable:true,targets:0
			        },
			        {
			        	orderable:false,targets:0
			        },
			        {
			        	orderable:false,targets:0
			        },
			        {
			        	orderable:false,targets:0
			        },
			        {
			        	orderable:false,targets:0
			        },
			        {
			        	orderable:false,targets:0
			        },
			        {
			        	orderable:false,
			        	targets:0,
			        	width:'50px'
			        },
			        {
			        	orderable:false,targets:0
			        }],
			        order:[[4,'asc']]
			    });

		    $('input[name="buscar"]').on('keyup',function(e){

					t.search(this.value).draw();
			   });
		 

		  t.on('draw',function(e,obj){

			  		$('.cancelar').tooltip('enable');
			  		$('.impresion').tooltip('enable');

			 		$('.cancelar').on('click',function(e){
					var idcancel = $(this).attr('data-id');

					$.confirm({
						title:'Confirmar!',
						content:'Esta seguro de cancelar la reserva?',
						buttons:{
							Si:function(){
										$.ajax({
										url: '/negocio/Controller/peticiones.php',
										type: 'POST',
										dataType: 'JSON',
										data: {peticion: 'cancelarreserva',idreserva:idcancel},
										})
											.done(function(response) {
											if(response.peticion){
												t.ajax.reload(null,false);
												$.alert('Reservación cancelada.');
											}
										});
									
								},
							No:function(){
									$.alert('Reservación no cancelada.');
							}
						}

					})
	

				});

		 	$('.observaciones').click(function(){
				$.alert({
					title:'Observaciones',
					content:$(this).attr('data-observacion')
				});
			});		

		 });
	

    	
    	if($('.observaciones').length){
    		$('.observaciones').click(function(e){

    			$.alert({
					title:'Observaciones',
					content:$(this).attr('data-observacion')
				});
    		})
    	}

    	$('.cancelar').on('click',function(e){



    		var id = $(this).attr('data-id');

    		var result = confirm('¿Realmente desea cancelar esta reservación ?');


    		if(result){
					$.ajax({
					url: '/Hotel/controller/peticiones.php',
					type: 'POST',
					dataType: 'JSON',
					data: {peticion: 'cancelarreservacion',idreserva:id},
					})
					.done(function(response) {
					if(response.peticion){
					$('#'+id).hide('slow', function() {
						
					});
					}
					})
					.fail(function() {
					console.log("error");
					})
					.always(function() {
					console.log("complete");
					});
    		}else{
    			return false;
    		}
    		
    		
    	});
});

</script>



<?php echo $footer = $includes->get_admin_footer(); ?>