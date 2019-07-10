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

											<?php echo $reservaciones->listreservas();?>
										
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



			var datos = null;

			$.ajax({
				url: '/socio/controller/peticiones.php',
				type: 'POST',
				dataType: 'JSON',
				data: {peticion: 'listarreservacionesusuario'},
			})
			.done(function(response) {
				if(response.peticion){
					datos = response.datos;
				}
			})
			.fail(function() {
				console.log("error");
			})
			.always(function() {
				console.log("complete");
			});
			
			$('.cancelar').on('click',function(){
					var reserva = $(this).attr('data-reserva');
					var result = confirm('Esta seguro de cancelar la reservación?');

					if(result){
						$.ajax({
							url: '/socio/controller/peticiones.php',
							type: 'POST',
							dataType: 'JSON',
							data: {peticion: 'cancelarreservacion',idreserva:reserva},
						})
						.done(function(response) {
							if(response.peticion){
								alert('Reserva cancelada exitosamente.');

								$('#'+reserva).hide('slow', function() {
									
								});

							
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

				var formData = new FormData();

				formData.append('peticion','listarreservacionesusuario');

				var t = $('#listareservacionesusuario').DataTable( {
					paging        : true,
					lengthChange:false,
					scrollY       : 400,
					scrollCollapse: true,
					ordering:false,
					dom:'lrtip',
			        language:{
							lengthMenu: "Mostar _MENU_ registros por pagina",
							info: "",
							infoEmpty: "No se encontr&oacute; ninguna reservación",
							infoFiltered: " de _MAX_ registros",
							emptyTable:"No hay reservaciones",
							zeroRecords:" No se encontr&oacute; lo que buscabas",
			               	paginate: {
			                        "next":       "Siguiente",
			                        "previous":   "Anterior"
			                        },
		                    },
		               
		                "columnDefs": [ 
			                {
			                width:"300px",
				            "searchable": true,
				            "orderable": true,
				            "targets": 0
				        	},
				        	{
			                width:"200px",
				            searchable: true,
				            orderable: true,
				            targets: 1
				        	},
				        	{
			                width:"auto",
				            searchable: true,
				            orderable: true,
				            targets: 2
				        	},
				        	{
			                width:"auto",
				            searchable: true,
				            orderable: true,
				            targets: 3
				        	},
				        	{
			                width:"200px",
				            searchable: true,
				            orderable: true,
				            targets: 4
				        	}
				        	,{
			                width:"30px",
				            searchable: true,
				            orderable: true,
				            targets: 5
				        	}
				        	,{
			                width:"30px",
				            searchable: true,
				            orderable: true,
				            targets: 6
				        	}
				        	,{
			                width:"30px",
				            searchable: true,
				            orderable: true,
				            targets: 7
				        	}

			        ],
			       
			    });
			   $('input[name="buscar"]').on('keyup',function(e){

					t.search(this.value).draw();
			   });
			


			$('.content-row').click(function(event) {
				


			});
			if($('.observacion').length){
				$('.observacion').click(function(e){
					alert($(this).attr('data-observacion'));
				})
			}

			if($('.location').length){
				$('.location').click(function(e){
					alert($(this).attr('data-location'));
				})
			}


			if($('.localizacion').length){
				$('.localizacion').click(function(e){
					alert($(this).attr('data-localizacion'));
				})
			}
		
		});
	</script>
<?php echo $footer = $includes->get_main_footer(); ?>