<?php 
	
	require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';
	$con = new assets\libs\connection();

	use Hotel\models\Includes;
	use Hotel\models\Dashboard;
	use Hotel\models\Home;
	use Hotel\models\Reservacion;

	$hotel = new Dashboard($con);
	$reservacion = new Reservacion($con);

	if($_SERVER["REQUEST_METHOD"] == "POST"){
		if(isset($_POST['pdf'])){
			$businesses->get_businesses_pdf();
			die();
		}

		if(isset($_POST['reservar'])){
			$reservacion->reservar($_POST);

		}

		if(isset($_POST['imprimir'])){
			$reservacion->imprimir();
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

	$home = new Home($con);

	$includes = new Includes($con);

	$properties['title'] = 'Hotel | Travel Points | Reservaciones';
	$properties['description'] = 'Reservaciones de TravelPoints';
	
	echo $header = $includes->get_no_indexing_header($properties);
	echo $navbar = $includes->get_admin_navbar();

	echo $con->get_notify(); ?>
	<div class="row">
		<div class="col-sm-12 ">
			<?php echo $home->getNotificacion();?>


			<script >
				$(document).ready(function() {
					
					$('input[name="fecha"]').attr('disabled', 'disabled');
				});
			</script>
			<div class="background-white p20 mb30">
				<form  action="<?php echo _safe(HOST.'/Hotel/reservaciones/');?>" method="POST" target="_blank">
					<?php echo $reservacion->get_notification(); ?>
				</form>
				<form name="new-reservacion" id="reservacion" action="<?php echo _safe(HOST.'/Hotel/reservaciones/');?>" method="POST">
					
					<script >
						
						$(document).ready(function() {
							$('#user-search-reservacion').bind('submit',function(e){
								$('.reservar').attr('disabled', 'disabled');
								return true;
							});
						});
					</script>


				
					<h2 class="page-title">Reservar</h2>
					<div class="row">
						<div class="col-lg-6 ">
							<div class="form-group" id="user-search-reservacion" data-toggle="tooltip" title="Encuentra al cliente (socio) del sitio por su nombre o nombre de usuario (username).Este campo es obligatorio.">
								<label for="user-search-input">Socio de Travel Points   | <i class="fa fa-question-circle text-secondary"></i></label>
									<div class="search-placeholder" id="user-search-placeholder" style="flex:1 1 auto;">
										<img src="<?php echo HOST;?>/assets/img/user_profile/default.jpg" class="meta-img img-rounded">
									</div>
												
								<input type="text" class="form-control typeahead" name="referral"  value="" placeholder="Nombre de usuario del cliente" autocomplete="off">
							</div>
							<div class="form-group" id="user-search-reservacion-negocios" data-toggle="tooltip" title="Busca y selecciona el restaurantes deseado.">
									<label for="restaurantes">Negocios de Travel Points   | <i class="fa fa-question-circle text-secondary"></i></label>
											<div class="search-placeholder reserva-content-img" id="user-search-placeholder-reservacion" style="flex:1 1 auto;">
											<img class="img-reservacion-default" src="<?php echo HOST;?>/assets/img/business/restaurant.png" class="meta-img img-rounded">
											</div>
											
											<input type="text" class="form-control complete" name="restaurantes" id="restaurantes" value="" placeholder="Nombre del Restaurante (negocio)" disabled>
							</div>
							</div>

						<div class="col-lg-6">


							<div class="form-group" id="fechadelareserva" data-toggle="tooltip" title="La fecha se habilita automaticamente una vez seleccione el restaurant." data-placement="top">
								<label for="start">Fecha | <i class="fa fa-question-circle text-secondary"></i></label>
								
								<div class="input-group date" id="fechareservacion">

									<input class="form-control" type="text" id="" name="fecha" value="" placeholder="Fecha de la reservaci&oacute;n" required/>

									<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
								</div>
							</div>

							<script>
								$(document).ready(function() {
									
								$('#fechareservacion').datetimepicker({
													format:'LL',
													locale:'es',
													minDate:new Date()-1,
													useCurrent:false,
													// daysOfWeekDisabled:diasdisponibles(),
													});

								});
							</script>

							<div class="horas-reserva">
							</div>


							<div class="form-group" data-toggle="tooltip" title="Seleccione el n&uacute;mero de personas, el maximo permitido lo delimita la cantidad disponible en la hora seleccionada.">
								<label for="cantidad">Total personas| <i class="fa fa-question-circle text-secondary"></i></label>
								<div class="input-group cant-personas">
									<input type="number" min="1" name="totalperson" step="1" id="cantidad" class="form-control" placeholder="N&uacute;mero de personas" disabled>
								</div>
							</div>

							
							<div class="form-group">
								<label for="observaciones">Observaciones</label>
								<div class="input-group observacion">
									<textarea name="observacion" class="form-control" placeholder="Observaci&oacute;n..."></textarea>
								</div>
							</div>
						</div>

					
					</div>
					<div class="row">
						<section class=" col-lg-6 botoneras-footer-reservacion">
							<button class="reservar btn btn-success" type="submit" name="reservar" disabled><i class="fa fa-save"></i>Reservar</button>
						</section>
						
					</div>

					

					<input type="hidden" name="fechaseleccionada">
					<input type="hidden" name="negocio">
					
					<input type="hidden" name="horaseleccionada">
					</form>
					
			</div>
		</div>
	</div>


<div class="modal" id="modal-datos-restaurant" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-cutlery"></i> | Datos del restaurant</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>


      	<form name="new-user-form" action="<?php echo HOST.'/Hotel/reservaciones/'; ?>" method="POST">
		      <div class="modal-body">
		       <div class="form-group">
					<label for="email">Nombre </label>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-glass"></i></span>
						<input  type="text" id="restaurant" name="restaurant" class="form-control" placeholder="Email del usuario" autocomplete="off" readonly>
					</div>
				</div>

				<div class="form-group">
					<label for="phone">Tel&eacute;fono</label>
					<div class="input-group phone">
						<span class="input-group-addon"><i class="fa fa-phone"></i></span>
						<input  type="tel" id="phone" name="telefonorestaurant" class="form-control" placeholder="Tel&eacute;fono del restaurant" autocomplete="off" readonly>
					</div>
				</div>

					<div class="form-group">
					<label for="direccion">Direcci&oacute;n</label>
					<div class="input-group direccion">
						<span class="input-group-addon"><i class="fa fa-map-o"></i></span>
						<textarea name="direccion" class="form-control" readonly></textarea>
					</div>
				</div>


		      </div>
		     
  		</form>
    
    </div>
  </div>
</div>


<div class="modal" id="modal-afiliar-new-usuario-reservacion" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-address-book"></i> | Nuevo registro de usuario</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>


      	<form name="new-user-form" action="<?php echo HOST.'/Hotel/reservaciones/'; ?>" method="POST">
		      <div class="modal-body">
		       <div class="form-group">
					<label for="email">Email</label>
					<div class="input-group email">
						<span class="input-group-addon"><i class="fa fa-at"></i></span>
						<input  type="email" id="email" name="email" class="form-control" placeholder="Email del usuario" autocomplete="off" required>
					</div>
				</div>


				<div class="form-group">
					<label for="name">Nombres</label>
					<div class="input-group name">
						<span class="input-group-addon"><i class="fa fa-vcard"></i></span>
						<input  type="text" id="name" name="nombre" class="form-control" placeholder="Nombres del usuario" autocomplete="off" required>
					</div>
				</div>


				<div class="form-group">
					<label for="lastname">Apellidos</label>
					<div class="input-group apellido">
						<span class="input-group-addon"><i class="fa fa-vcard"></i></span>
						<input  type="text" id="lastname" name="apellido" class="form-control" placeholder="Apellidos del usuario" autocomplete="off" required>
					</div>
				</div>

				<div class="form-group">
					<label for="phone">Tel&eacute;fono</label>
					<div class="input-group phone">
						<span class="input-group-addon"><i class="fa fa-phone"></i></span>
						<input  type="tel" id="phone" name="telefono" class="form-control" placeholder="Tel&eacute;fono del usuario" autocomplete="off" required>
					</div>
				</div>

		      </div>
		      <div class="modal-footer">
		        <button type="submit" class="btn btn-primary guardar-users"><i class="fa fa-save"></i>Guardar</button>
		        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-close"></i>Cerrar</button>
		      </div>
  		</form>
    
    </div>
  </div>
</div>


<script>
	
	$(document).ready(function() {

		$('form[name="new-user-form"]').bind('submit', function(event) {
				event.preventDefault();

				$('.guardar-users').attr('disabled', 'disabled');
				$('.guardar-users').text('Guardando por favor espere...');
				var email    = $('input[name="email"]').val();
				var nombre   = $('input[name="nombre"]').val();
				var apellido = $('input[name="apellido"]').val();
				var telefono = $('input[name="telefono"]').val();
			


			var formulario = new FormData();


			formulario.append('email',email);
			formulario.append('nombre',nombre);
			formulario.append('apellido',apellido);
			formulario.append('telefono',telefono);
			formulario.append('username',nombre.apellido);
			formulario.append('peticion','newUser');

			$.ajax({
				url: '/Hotel/controller/peticiones.php',
				type: 'POST',
				dataType: 'JSON',
				data:formulario,
				processData:false,
				contentType:false,
				cache:false,
			})
			.done(function(response) {
				if(response.peticion){
					$('.guardar-users').removeAttr('disabled');
					$('.guardar-users').text('');
					$('.guardar-users').append('<i class="fa fa-save"></i>Guardar');

					
					$('input[name="email"]').val('');
					$('input[name="nombre"]').val('');
					$('input[name="apellido"]').val('');
					$('input[name="telefono"]').val('');

					$.alert('Usuario registrado exitosamente');
					$('#modal-afiliar-new-usuario-reservacion').modal('hide');
					$('input[name="referral"]').val(nombre+apellido);
				}else{
					
				}
			});
			
			
			
		});
		
		



	});

</script>



<?php echo $footer = $includes->get_admin_footer(); ?>



