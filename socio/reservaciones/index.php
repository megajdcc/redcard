<?php 

require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	header('Location: '.HOST.'/login');
	die();
}
if(!isset($_SESSION['user']['id_usuario'])){
	header('Location: '.HOST.'/login');
	die();
}

use socio\libs\Reservacion;

$reservaciones = new Reservacion($con);

$includes = new assets\libs\includes($con);
$properties['title'] = 'Reservaciones | Travel Points';
$properties['description'] = 'Mis reservaciones';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
	<div class="main">
		<div class="main-inner">
			<div class="container">
				<?php echo $con->get_notify();?>
				
				<div class="row">

					<div class="col-sm-4 col-lg-3">
						<div class="sidebar">
							<?php echo $includes->get_user_sidebar();?>
						</div>
					</div>


					<div class="col-sm-8 col-lg-9">
						<div class="content">

							<h1 class="page-title"> Mis reservaciones</h1>
							
							

							<div class="background-white p30">


								<div class="row">
									
									<div class="form-group" data-toggle="tooltip" title="Haga su busqueda inteligente, si desea ser especifico encierre su busqueda entre comillas dobles." >
										<label for="busqueda">Buscar negocio | </span><i class="fa fa-question-circle text-secondary"></i></label>
										<div class="input-group">
											<strong class="input-group-addon"><i class="fa fa-search"></i></strong>
											<input type="text" class="form-control busqueda" name="buscar" autocomplete="false" placeholder="Busque su reservaci&oacute;n ...">
											
										</div>
									
										
										
									</div>
								</div>


								<div class="row">
									<table id="listareservacionesusuario" class="display" cellspacing="0" width="100%">
										<thead>
											<tr>
												
												<th scope="col">Negocio</th>
												<th scope="col">Fecha</th>
												<th>Personas</th>
												<th scope="col">Hora</th>
												<th scope="col">Status</th>
												<th>Direcci&oacute;n</th>
												<th>Observaci&oacute;n</th>
												<th></th>
												
												
											</tr>
										</thead>
										<tbody class="parent-content-table">

											<!-- <?php //echo $reservaciones->listreservas();?> -->
										
										</tbody>
									</table>
									
								</div>

							</div>
						</div>
						
					</div>
				
				</div>
			</div><!-- /.container -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->

	<script>
	


		

		$(document).ready(function() {
			
		
				 var t = $('#listareservacionesusuario').DataTable({
					paging        	:false,
					lengthChange	:false,
					scrollY      	:300,
					scrollCollapse	:true,
					ordering		:false,
					
					dom:'lrtip',
					ajax:{
						url:'/socio/controller/peticiones.php',
						type:'POST',
						dataType:'JSON',
						data:function(d){
							d.peticion ='cargarreservaciones';
						}
					},
					rowId:'data.id',
					columns:[
						 		{data:'negocio',
						 		width:'140px',
						 		searchable:true},
						 		{data:'fecha',
						 		width:'120px'
						 			},
						 		{data:'numeropersona',width:'auto'},
						 		{data:'hora'},
						 		{data:'status'},
						 		{data:'localizacion'},
						 		{data:'observacion'},
						 		{data:'cancelar',width:'10px'}
					 		],
			         language:{
			                        "lengthMenu": "Mostar _MENU_ registros por pagina",
			                        "info": "",
			                        "infoEmpty": "No se encontro ninguna reservación",
			                        "infoFiltered": "(filtrada de _MAX_ registros)",
			                        "search": "Buscar: ",
			                        "paginate": {
			                            "next":       "Siguiente",
			                            "previous":   "Anterior"
			                        },
			                    }
			    });


			$('input[name="buscar"]').on('keyup',function(e){

					t.search(this.value).draw(false);
			});

		// 	t.on('init',function(e,obj){


		// 		if($('.observacion').length){

		// 			$('.observacion').tooltip('enable');
					
		// 			$('.observacion').click(function(e){
		// 				$.alert({
		// 					title:'Observación',
		// 					content:$(this).attr('data-observacion')
		// 				});
		// 			});
		// 		}
					
		// 		if($('.location').length){
		// 				$('.location').tooltip('enable');
		// 			$('.location').click(function(e){
		// 				$.alert({
		// 					title:'Dirección',
		// 					content:$(this).attr('data-location')
		// 					});
		// 			});
		// 		};


		// 		$('.cancelar').tooltip('enable');
		// 		$('.cancelar').on('click',function(){
		// 			var reserva = $(this).attr('data-reserva');


		// 			$.confirm({
		// 				title:'Confirmar!',
		// 				content:'Esta seguro de cancelar la reservación?',
		// 				buttons:{
		// 					Si:function(){
		// 							$.ajax({
		// 							url: '/socio/controller/peticiones.php',
		// 							type: 'POST',
		// 							dataType: 'JSON',
		// 							data: {peticion: 'cancelarreservacion',idreserva:reserva},
		// 							})
		// 							.done(function(response) {
		// 								if(response.peticion){
		// 									$.alert('Reserva cancelada exitosamente.');
										
		// 									t.ajax.reload(null,false);
										
										
		// 								}
		// 							})
		// 					},
		// 					No:function(){
		// 						$.alert('Has decidido no cancelar!');
		// 					}
		// 				}
		// 			});
		// 	});
			



		// });

			t.on('draw',function(e,obj){


				if($('.observacion').length){

					$('.observacion').tooltip('enable');
					
					$('.observacion').click(function(e){
						$.alert({
							title:'Observación',
							content:$(this).attr('data-observacion')
						});
					});
				}
					
				if($('.location').length){
						$('.location').tooltip('enable');
					$('.location').click(function(e){
						$.alert({
							title:'Dirección',
							content:$(this).attr('data-location')
							});
					});
				};


				$('.cancelar').tooltip('enable');
				$('.cancelar').on('click',function(){
					var reserva = $(this).attr('data-reserva');


					$.confirm({
						title:'Confirmar!',
						content:'Esta seguro de cancelar la reservación?',
						buttons:{
							Si:function(){
									$.ajax({
									url: '/socio/controller/peticiones.php',
									type: 'POST',
									dataType: 'JSON',
									data: {peticion: 'cancelarreservacion',idreserva:reserva},
									})
									.done(function(response) {
										if(response.peticion){
											$.alert('Reserva cancelada exitosamente.');
										
											t.ajax.reload(null,false);
										
										
										}
									})
							},
							No:function(){
								$.alert('Has decidido no cancelar!');
							}
						}
					});
			});
			



		});
	});

			

	</script>
<?php echo $footer = $includes->get_main_footer(); ?>