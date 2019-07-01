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
									<div class="form-group" data-toggle="tooltip" title="Haga su busqueda y luego presione el botón Buscar" >
										<label for="busqueda">Buscar negocio | </span><i class="fa fa-question-circle text-secondary"></i></label>
										<div class="input-group bus-misreserva">
											<input type="text" class="form-control busqueda" name="busqueda">
											<button class="input-group-addon btn btn-primary" name="buscar"><i class="fa fa-search"></i></button>
										</div>
										
										
									</div>
								</div>


								<div class="row">
									<table class="table table-hover table-mis-reservaciones">
										<thead>
											<tr>
												<th scope="col">#</th>
												<th scope="col">Negocio</th>
												<th scope="col">Fecha</th>
												<th scope="col">Hora</th>
												<th scope="col">Status</th>
												<th scope="col">Localizaci&oacute;n</th>
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
			$('button[name="buscar"]').on('click',function(e){
				var busqueda = $('input[name="busqueda"]').val();

				$.ajax({
					url: '/socio/controller/peticiones.php',
					type: 'POST',
					dataType: 'JSON',
					data: {peticion: 'buscarreserva',busqueda:busqueda},
				})
				.done(function(response) {

					if(response.peticion){

						$('.content-row').remove();
						var datos = response.datos;
							var urlimg = "<?php echo HOST.'/assets/img/business/logo/'?>";
							var urlbusinees = "<?php echo HOST.'/'?>";
						for(clave in datos){


							if(datos[clave]['status'] == 0){
								var status = 'Agendada';
								var clas = 'sinconfirmar';
							}else if(datos[clave]['status'] == 1){
								var status = 'Consumada';
								var clas = 'consumada';
							}else if(datos[clave]['status'] == 2){
								var status = 'Confirmada';
								var clas = 'confirmada';
							}else if(datos[clave]['status'] == 3){
								var status = 'Cancelada';
								var clas = 'cancelada';
							}
							

							$('.parent-content-table').append('<tr class="content-row" data-id="'+datos[clave]['id']+'"><td class="key-img">'+clave+'<div class="user user-md "><a href="'+urlbusinees+datos[clave]['url']+'" target="_blank"><img class="img-thumbnail img-rounded" src="'+urlimg+datos[clave]['logo']+'"></a></div></td><td>'+datos[clave]['negocio']+'</td><td>'+datos[clave]['fecha']+'</td><td><strong class="hora-reserva">'+datos[clave]['hora']+'</strong></td><td><strong class="'+clas+'">'+status+'</strong></td><td class="localizacion">'+datos[clave]['localizacion']+'</td></tr>');

						}


					}
				})
				.fail(function() {
					console.log("error");
				})
				.always(function() {
					console.log("complete");
				});
				

			});
		});
	</script>
<?php echo $footer = $includes->get_main_footer(); ?>