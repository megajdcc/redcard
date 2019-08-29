<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 
$con = new assets\libs\connection();


use Hotel\models\Includes;
use Hotel\models\Promotor;
use Hotel\models\Dashboard;


if(!isset($_SESSION['perfil']) && !isset($_SESSION['promotor']) && !isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
	}

$Dashboard = new Dashboard($con);
$includes = new Includes($con);
$promotor = new Promotor($con,$_SESSION['promotor']['id']);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['pdf'])){
		
			$reporte->mostrarpdf($_POST);
			die();
		}

	}

// $info = new negocio\libs\preference_info($con);
// $users = new admin\libs\get_allusers($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){

	if(isset($_POST['date_start'])){
			
		}

	// $reporte->setFechas($_POST);
}

$properties['title'] = 'Perfil de cuenta promotor | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $promotor->getNotificacion();?>
	
		<div class="background-white p20 mb30">

				<div class="page-title">
					<h1>Perfil de cuenta</h1>
				</div>
				



<button class="show-lista btn btn-secondary" type="button"><i class="fa fa-info-circle"></i> Importante Saber!</button>
		
	
		<ul class="list-group lista">
	
			<li class="list-group-item">Solo podr&aacute; modificar todo los datos de cuenta de promotor, excepto el email y el cargo</li>
			<li class="list-group-item">Puedes incluso cambiar tu contrase&ntilde;a actualmente</li>
			<li class="list-group-item">Es importante que agregues tu informaci&oacute;n de pago, esta informaci&oacute;n es necesaria para cuando solicites tu comisi&oacute;n y los operadores sepan a donde te van a realizar y/o transferir los fondos, de lo contrario, soporte se pondr&aacute; en contacto con usted para aclarar estos datos.</li>
		</ul>

		<form action="<?php echo HOST.'/Hotel/perfil' ?>" method="POST" name="form-update-promotor" id="form-update-promotor">
			<div class="row">
				


				<section class="col-xs-12 col-lg-6">
					

					<h3 class="title">Datos personales</h3>


					<div class="form-group">
						<label for="nombre">Nombre: | <span class="required">*</span> </label>
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-user"></i></span>
							<input type="text" name="nombre" class="form-control" value="<?php echo $promotor->getNombre(); ?>" placeholder="Nombre del promotor" required>
						</div>
					</div>


					<div class="form-group">
						<label for="apellido">Apellido: | <span class="required">*</span> </label>
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-user"></i></span>
							<input type="text" name="apellido" class="form-control" value="<?php echo $promotor->getApellido() ?>" placeholder="Apellido del promotor" required>
						</div>
					</div>

					<div class="form-group">
						<label for="telefono">Tel&eacute;fono:</label>
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-phone"></i></span>
							<span class="input-group-addon">+52 - </span>
							<input type="tel" name="telefono" pattern="[0-9]{10}" class="form-control" value="<?php echo $promotor->getTelefono() ?>" placeholder="Tel&eacute;fono del promotor. Example: 3224518789">
						</div>
					</div>


				</section>

				<section class="col-xs-12 col-lg-6">

					<h3 class="title">Datos de usuario</h3>
					
					<div class="form-group" data-toggle="tooltip" title="El nombre de usuario(username), se podr&aacute; utilizar para que el promotor pueda iniciar sesi&oacute;n en el panel del hotel.">
						<label for="username">Username: | <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-user"></i></span>
							<input type="text" name="username" class="form-control" value="<?php echo $promotor->getUsername() ?>" placeholder="Username" required>
						</div>
					</div>
					
					<div class="form-group" data-toggle="tooltip" title="El email al igual que el nombre de usuario(username), se podr&aacute; utilizar para que el promotor pueda iniciar sesi&oacute;n en el panel del hotel.">
						<label for="email">Email:</label>
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-envelope"></i></span>
							<input type="email" name="email" class="form-control" value="<?php echo $promotor->getEmail() ?>" placeholder="Email del promotor example: emailpromotor@example.com" <?php  if($promotor->is_cargo()){
								echo 'disabled';
							}else{
								echo 'required';
							} ?>>
						</div>
					</div>

					<div class="form-group" data-toggle="tooltip" title="El cargo es una representaci&oacute;n de cada hotel, Es utilizada para diferenciar a los departamentos, y para ">
						<label for="email">Cargo:</label>
						<div class="input-group select-cargo">
							<span class="input-group-addon"><i class="fa fa-black-tie"></i></span>
							<select name="cargo" id="cargo" class="form-control" readonly disabled>
								<option value="0">Seleccione</option>
								<?php $promotor->getCargos(); ?> 
							</select>
						</div>

					</div>
				</section>
			
			</div>

		

	<label>Los campos marcados con <span class="required">*</span> son requeridos.</label>
	
			
		<footer class="row">
			<button type="submit" name="grabar" class="btn btn-success" data-toggle="tooltip" title="Se utliza tanto para guardar como para modificar datos"> <i class="fa fa-save"></i> Grabar</button>
			<button type="button" name="updata-password" class="btn btn-info" data-toggle="tooltip" title="Cambia tu contraseña actual"> <i class="fa fa-expeditedssl "></i> Cambiar password</button>
			<button type="button" name="updata-datopagocomision" class="btn btn-secondary" data-toggle="tooltip" title="Establece o cambia tus datos de pago de comisi&oacute;n."><i class="fa fa-bank"></i>Datos pago comisi&oacute;n</button>				
			
		</footer>

		<input type="hidden" name="peticion" value="updatepromotor">
		</form>
		</div>
		</div>
</div>



<div class="modal fade" id="modal-update-password" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-expeditedssl"></i> | Update Password</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      	<form name="new-passsword-form" method="POST">
		      <div class="modal-body">

		      	<div class="row">
		      		<div class="col-lg-12">
						<div class="form-group">
							<label for="contrasena">Actual contrase&ntilde;a</label>
							<div class="input-group categoria">
								<strong class="input-group-addon"><i class="fa fa-expeditedssl"></i></strong>
								<input  type="password" id="contrasena" name="contrasena" class="form-control" placeholder="Contrase&ntilde;a actual" autocomplete="off" required>
							</div>
						</div>
		      		</div>
		      	</div>

		      	<div class="row">
		      		<div class="col-lg-12">
						<div class="form-group">
							<label for="contrasena1">New contrase&ntilde;a</label>
							<div class="input-group categoria">
								<strong class="input-group-addon"><i class="fa fa-expeditedssl"></i></strong>
								<input  type="password" id="contrasena1" name="contrasena1" class="form-control" placeholder="Contrase&ntilde;a nueva" autocomplete="off" required>
							</div>
						</div>
		      		</div>
		      	</div>
		      
			</div>
	
		      <div class="modal-footer">
		        <button type="submit" class="btn btn-primary grabar-categories"><i class="fa fa-save"></i>Grabar</button>
		      </div>
		      <input type="hidden" name="peticion" value="updatepassword">
  		</form>
    
    </div>
  </div>
</div>



<!-- MODAL DATOS DE PAGO DE COMISION -->

<div class="modal fade" id="modal-dato-pago-comision" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-bank"></i> | Datos pago comisi&oacute;n</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      	<form name="new-datos-comision" method="POST">
		     <div class="modal-body">
		     	<div class="row">
												
												<div class="col-lg-6 col-sm-4">
														<h5 class="page-title">Transferencia Bancaria</h5>
															<div class="form-group">
																<label for="nombre">Nombre del banco<span class="required"></span></label>
																<div class="input-group">
																		<span class="input-group-addon"><i class="fa fa-bank"></i></span>
																			<input class="form-control" type="text"  pattern="[a-zA-z]+" id="nombre_banco" name="nombre_banco" value="" placeholder="Nombre del banco"  >
																</div>
																		
															</div>

																	<div class="form-group">
																		<label for="cuenta">Cuenta<span class="required"></span></label>
																		<div class="input-group">
																			<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
																			<input class="form-control" type="text" pattern="[0-9a-zA-z]+" id="cuenta" name="cuenta" value="" placeholder="Cuenta."  >
																		</div>
																		
																	</div>

																	<div class="form-group" data-toggle="tooltip" title="Solo se permiten digitos númericos, correspondientes a su clabe.">
																		<label for="clabe">Clabe<span class="required"></span><i class="fa fa-question-circle"></i></label>
																		<div class="input-group">
																			<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
																			<input class="form-control" type="text" maxlength="18" id="clabe" pattern="[0-9]{18}" name="clabe" value="" placeholder="Clabe"  >
																		</div>
																	
																	</div>

																	<div class="form-group" data-toggle="tooltip" title="Una serie alfanuméricas de 8 u 11 digitos, que sirve para identificar al banco receptor cuando se realiza una transferencia">
																		<label for="swift">Swift / Bic<span class="required"></span><i class="fa fa-question-circle"></i></label>
																		<div class="input-group">
																			<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
																			<input class="form-control" type="text" id="swift" maxlength="11" pattern="[A-Za-z0-9]{8,11}" name="swift" value="" placeholder="Swift"  >
																		</div>
																	
																	</div>

												</div>



												<div class="col-lg-6 col-sm-4">
																	<h5 class="page-title">Deposito a tarjeta</h5>
																	<div class="form-group">
																		<label for="nombre">Nombre del banco<span class="required"></span></label>
																		<div class="input-group">
																			<span class="input-group-addon"><i class="fa fa-bank"></i></span>
																			<input class="form-control" type="text" pattern="[a-zA-z]*" id="nombre_banco_targeta" name="nombre_banco_tarjeta" value="" placeholder="Nombre del banco"  >
																		</div>
																		
																	</div>
																	<div class="form-group" data-toggle="tooltip" title="Número de la targeta de Credito, conlleva 16 digitos solo numéricos.">
																		<label for="nombre">N&uacute;mero de tarjeta<span class="required"></span><i class="fa fa-question-circle"></i></label>
																		<div class="input-group">
																			<span class="input-group-addon"><i class="fa fa-cc"></i></span>
																			<input class="form-control" type="text" pattern="[0-9]{16}" maxlength="16" minlength="16" id="numero_targeta" name="numero_targeta" value="" placeholder="N&uacute;mero de Tarjeta" >
																		</div>
																		
																	</div>
														
																
																		<h5 class="page-title">Transferencia PayPal</h5>
																	<div class="form-group">
																		<label for="nombre">Email de Paypal<span class="required"></span></label>
																		<div class="input-group">
																			<span class="input-group-addon"><i class="fa fa-cc-paypal"></i></span>
																			<input class="form-control" type="email" id="email_paypal" name="email_paypal" value="" placeholder="Nombre del banco"  >
																		</div>
																		
																	</div>
												</div>
																						
											</div>


		      
			</div>
	
		      <div class="modal-footer">
		        <button type="submit" class="btn btn-primary grabar-datocomision"><i class="fa fa-save"></i>Grabar</button>
		      </div>
		      <input type="hidden" name="peticion" value="datoscomision">
		        <input type="hidden" name="id_pago" value="0">
  		</form>
    
    </div>
  </div>
</div>


	

<script>
	
	$(document).ready(function() {


		$('button[name="updata-datopagocomision"]').on('click',function(e){
			$('#modal-dato-pago-comision').modal('show');

			cargarDatosComision();
			
		});

		function cargarDatosComision(){

			$.ajax({
				url: '/Hotel/controller/peticiones.php',
				type: 'POST',
				dataType: 'JSON',
				data: {peticion: 'getDatosPagoComision'},
			})
			.done(function(response) {
				if(response.peticion){
					$('input[name="nombre_banco"]').val(response.data.banco);
					$('input[name="cuenta"]').val(response.data.cuenta);
					$('input[name="clabe"]').val(response.data.clabe);
					$('input[name="swift"]').val(response.data.swift);
					$('input[name="nombre_banco_tarjeta"]').val(response.data.banco_tarjeta);
					$('input[name="numero_targeta"]').val(response.data.numero_tarjeta);
					$('input[name="email_paypal"]').val(response.data.email_paypal);

					$('input[name="id_pago"]').val(response.data.id);
				}
			});

		}


		$('form[name="new-datos-comision"]').bind('submit',function(e){
			e.preventDefault();

				$.confirm({
					title:'Confirm process!',
					content:'Esta seguro de cambiar los datos de pago de comisi&oacute;n',
					buttons:{
						Si:function(){

							$('.grabar-datocomision').text('Grabando por favor espere...');

							$.ajax({
								url: '/Hotel/controller/peticiones.php',
								type: 'POST',
								dataType: 'JSON',
								data: $('form[name="new-datos-comision"]').serialize(),
								cache:false
							})
							.done(function(response) {
								if(response.peticion){
									$('.grabar-datocomision').text('');
									$('.grabar-datocomision').append('<i class="fa fa-save"></i>Grabar');

									$.alert({title:'Felicidades!',content:response.mensaje});
									cargarDatosComision();
								}else{

									$('.grabar-datocomision').text('');
									$('.grabar-datocomision').append('<i class="fa fa-save"></i>Grabar');

									$.alert({title:'Perdon!',content:response.mensaje});

									$('#modal-dato-pago-comision').modal('hide');

								}
							})
							.fail(function() {
								$('.grabar-datocomision').text('');
									$('.grabar-datocomision').append('<i class="fa fa-save"></i>Grabar');

								$.alert('Error en el servidor, Estamos trabajando para resolverlo, intentelo mas tarde!');
							});
						},
						No:function(){
							$.alert('Ok! lo puedes hacer despues.');
						}
					}
				});

			return false;
		});

		$('form[name="new-passsword-form"]').bind('submit',function(e){
			e.preventDefault();

			$.confirm({
				title:'Confirm process!',
				content:'Esta seguro de cambiar la contrase&ntilde;a',
				buttons:{
					Si:function(){

						$.ajax({
							url: '/Hotel/controller/peticiones.php',
							type: 'POST',
							dataType: 'JSON',
							data:$('form[name="new-passsword-form"]').serialize(),
							cache:false
						})
						.done(function(response) {
							if(response.peticion){

								$('input[name="contrasena"]').val('');
								$('input[name="contrasena1"]').val('');

								$('#modal-update-password').modal('hide');

								$.alert(response.mensaje);

							}else{
								$.alert(response.mensaje);
							}
						})
						.fail(function() {
							$('input[name="contrasena"]').val('');
							$('input[name="contrasena1"]').val('');

							$('#modal-update-password').modal('hide');
							$.alert('Error en el servidor, estamos trabajando para solucionarlo.')
						});
						
					},
					No:function(){
						$.alert('Ok si te sabes la contrase&ntilde;a no hay necesidad de que la cambies.');
					}
				}
			})


			return false;

		});

		$('button[name="updata-password"]').on('click',function(e){
			$('#modal-update-password').modal('show');
		
		});


		$('form[name="form-update-promotor"]').bind('submit',function(e){

			e.preventDefault();

				// var formulario = new FormData(document.getElementById('form-update-promotor'));

				// formulario.append('peticion','');

				$.confirm({
					title:'Confirm!',
					content:"Esta seguro de actualizar tus datos?",
					buttons:{
						Si:function(){
								$.ajax({
									url: '/Hotel/controller/peticiones.php',
									type: 'POST',
									dataType: 'JSON',
									data: $('#form-update-promotor').serialize(),
									cache:false,
									processData:false

								})
								.done(function(response) {
									if(response.peticion){

										$.alert('Se ha actualizado correctamente tus datos!');
									}else{
										$.alert('Problemas con el servidor, estamos trabajando para solucionarlo, vuelve mas tarde');
									}

								})
								.fail(function() {
									$.alert('Problemas con el servidor, intentalo mas tarde.');
								});
						},
						No:function(){
							$.alert('Ok lo puedes intentar despues!');
						}
					}
				});


			return false;

		});

		var listaoculta = false;

		$('.show-lista').on('click',function(){

			if(listaoculta == false){
				$('.lista').addClass('lista-show');
				listaoculta = true;
			}else{
				$('.lista').removeClass('lista-show');
				listaoculta = false;
			}
			
		});


		$('.new-cargo').on('click',function(){
			$('#modal-afiliar-cargo').modal('show');
		});

		$('form[name="form-newpromotor"]').bind('submit',function(e){

			e.preventDefault();
			
	
			var formu = new FormData(document.getElementById('form-newpromotor'));

			// formu.append('peticion','grabarpromotor');

			$('button[name="grabar"]').attr('disabled', 'disabled');
			$('button[name="delete"]').attr('disabled', 'disabled');
			$('button[name="de-alta"]').attr('disabled', 'disabled');
			$('button[name="grabar"]').text('Grabando por favor espere...');


			$.ajax({
				url: '/Hotel/controller/peticiones.php',
				type: 'POST',
				dataType: 'JSON',
				data: $(this).serialize(),
				processData:false
			})
			.done(function(response) {
				if(response.peticion){

					$('.panel-newpromotor').slideUp('slow',function(){
						location.reload();
					})
					
				}else{
					$.alert(response.mensaje);
					$('button[name="grabar"]').removeAttr('disabled');
					$('button[name="delete"]').removeAttr('disabled');
					$('button[name="de-alta"]').removeAttr('disabled');
					$('button[name="grabar"]').text('');
					$('button[name="grabar"]').append('<i class="fa fa-save"></i> Grabar');

				}
			});
			

			return false;

		});

	});

</script>

<?php echo $footer = $includes->get_admin_footer(); ?>