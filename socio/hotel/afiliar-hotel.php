<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Info Channel
$con = new assets\libs\connection();
 
use Hotel\models\AfiliarHotel;
use admin\libs\Iata;

if(!isset($_SESSION['user'])){
	header('Location: '.HOST.'/login');
	die();
}
if(!isset($_SESSION['user']['id_usuario'])){
	header('Location: '.HOST.'/login');
	die();
}

if($_SESSION['user']['id_rol']==8) {
	header('Location: '.HOST.'/socio/hoteles/siguiendo');
	die();
}
$iata = new Iata($con);

if(!isset($_SESSION['user'])){

	$login = new assets\libs\user_login($con);
	
	if($_SERVER["REQUEST_METHOD"] == "POST"){
		$login->set_data($_POST);
	}

}else{
	$affiliate = new AfiliarHotel($con);
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		if(isset($_POST['send'])){
			$affiliate->set_data($_POST);
		}
	}
}


$includes = new assets\libs\includes($con);
$properties['title'] = 'Afiliar hotel | Travel Points';
$properties['description'] = '';
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
						</div><!-- /.sidebar -->
					</div><!-- /.col-* -->
					<div class="col-sm-8 col-lg-9">
						<div class="content">
							<?php echo $affiliate->get_notification();?>
							<div class="page-title">
								<h1>¡Afilia tu hotel!</h1>
								<p>Env&iacute;anos una solicitud para publicar tu hotel en nuestro directorio.</p>
							</div>
							<form method="post" action="<?php echo _safe(HOST.'/afiliar-hotel');?>" enctype="multipart/form-data">
								<div class="background-white p30 mb50">
									<h3 class="page-title">Informaci&oacute;n de hotel</h3>
									<div class="row">

										<div class="col-lg-8">
								
											<div class="form-group" data-toggle="tooltip" title="Los clientes Huespedes de Travel Points pueden afiliarse desde su propio perfil...">
												<label for="business-name">Nombre del hotel <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>

												<input class="form-control" type="text" id="business-name" name="nombre" value="<?php echo $affiliate->getNombre();?>" placeholder="Nombre del hotel" required />
												<?php echo $affiliate->getNombreError();?>
											</div><!-- /.form-group -->
										
										</div><!-- /.col-* -->
										
										<div class="col-lg-4">
											<div class="row">
												<div class="col-sm-6 col-md-12 form-group" data-toggle="tooltip" title="El codigo Iata es utilizado para ayudar a agilizar los procesos de transporte aereo y turistico.">
													<label for="category">C&oacute;digo IATA <span class="required"></span><i class="fa fa-question-circle text-secondary"></i></label>

													<div class="input-group input-iata">
														<select class="form-control" id="iata" name="iata" title="Seleccionar c&oacute;digo IATA" data-live-search="true">
														<option value="null" selected>Seleccione</option>
															
															<?php echo $affiliate->getIata();?>
														</select>
														<?php echo $affiliate->getIataError();?>
														<button type="button" class="input-group-addon new-iata btn btn-secondary" name="new-iata" data-toggle="tooltip" title="Agrega tu codigo IATA" data-placement="bottom"><i class="fa fa-pencil"></i></button>
													</div>
													
												</div>
											</div>
										</div>
											<div class="col-sm-12">
											<div class="form-group" data-toggle="tooltip" title="Si no tienes sitio web, deja el espacio en blanco.">
												<label for="website">Sitio web del hotel <i class="fa fa-question-circle text-secondary"></i></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-globe"></i></span>
													<input class="form-control" pattern="([--:\w?@%&+~#=]*\.[a-z]{2,4}\/{0,2})((?:[?&](?:\w+)=(?:\w+))+|[--:\w?@%&+~#=]+)?" type="text" id="website" name="website" value="<?php echo $affiliate->getSitioWeb();?>" placeholder="Sitio web del hotel">
												</div><!-- /.input-group -->
												<?php echo $affiliate->getWebsiteError();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->

									</div><!-- /.row -->
									
								
									
								</div><!-- /.box -->

								<div class="background-white p30 mb30">
									<h3 class="page-title">Ubicaci&oacute;n del hotel</h3>
									<div class="row">
										<div class="col-lg-8">
											<div class="form-group">
												<label for="address">Direcci&oacute;n del hotel <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-map-o"></i></span>
													<input class="form-control" type="text" id="address" name="direccion" value="<?php echo $affiliate->getDireccion();?>" placeholder="Direcci&oacute;n del hotel" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->getDirecccionError();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="postal-code">C&oacute;digo postal  del hotel <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
													<input class="form-control" type="text" id="postal-code" name="codigopostal" value="<?php echo $affiliate->getCodigoPostal();?>" placeholder="C&oacute;digo postal del hotel" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->getCodigoPostalError();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div><!-- /.row -->
									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<label for="country-select">Pa&iacute;s <span class="required">*</span></label>
												<select class="form-control" id="country-select" name="pais" title="Selecciona un pa&iacute;s" data-size="10" data-live-search="true" required>
													<?php echo $affiliate->get_countries();?>
												</select>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="state-select">Estado <span class="required">*</span></label>
												<select class="form-control" id="state-select" name="estado" title="Luego un estado" data-size="10" data-live-search="true" required>
													<?php echo $affiliate->get_states();?>
												</select>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="city-select">Ciudad <span class="required">*</span></label>
												<select class="form-control" id="city-select" name="ciudad" title="Luego una ciudad" data-size="10" data-live-search="true" required>
													<?php echo $affiliate->get_cities();?>
												</select>
												<?php echo $affiliate->getCiudadError();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div><!-- /.row -->
									<hr>

									<div class="form-group">
										<label for="map-canvas">Posici&oacute;n en el mapa <span class="required">*</span></label>
										<p>
											<ul>
												<li>Arrastra el marcador hacia la ubicaci&oacute;n de tu hotel.</li>
												<li>Puedes apoyarte escribiendo una ubicaci&oacute;n como una ciudad, municipio, colonia, etc. y seleccionar una de las opciones sugeridas.</li>
											</ul>
											Las coordenadas de la ubicaci&oacute;n se crean automaticamente.
										</p>
										<?php echo $affiliate->getLocationError();?>
									</div>

									<input class="controls form-control mb30" type="text" id="pac-input" placeholder="Escribe una ubicaci&oacute;n" />
									<div id="map-canvas"></div>
									<div class="row">
										<div class="col-sm-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
												<input class="form-control" type="text" id="input-latitude"  name="latitud" value="<?php echo $affiliate->getLatitud();?>" placeholder="Latitud" required>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-sm-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
												<input class="form-control" type="text" id="input-longitude" name="longitud" value="<?php echo $affiliate->getLongitud();?>" placeholder="Longitud" required>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div><!-- /.row -->
								</div><!-- /.box -->


								<div class="background-white p30 mb30">
									<h3 class="page-title">Responsable del &aacute;rea de promoci&oacute;n</h3>
									
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="nombre">Nombre<span class="required">*</span></label>
												<div class="input-group">
														<span class="input-group-addon"><i class="fa fa-address-card-o"></i></span>
													<input class="form-control" type="text" id="nombre_responsable" name="nombre_responsable" value="<?php echo $affiliate->getNombreResponsable();?>" placeholder="Nombre del responsable &aacute;rea de promoci&oacute;n" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->getNombreResponsableError();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->

										<div class="col-lg-6">
											<div class="form-group">
												<label for="apellido">Apellido<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-address-card-o"></i></span>
													<input class="form-control" type="text" id="apellido_responsable" name="apellido_responsable" value="<?php echo $affiliate->getApellidoResponsable();?>" placeholder="Apellido del responsable &aacute;rea de promoci&oacute;n" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->getApellidoResponsableError();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div>
									
									
										
									
										<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="email">Email<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
													<input class="form-control" type="email" id="email" name="email" value="<?php echo $affiliate->getEmail();?>" placeholder="Email del responsable" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->getEmailError();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										

										<div class="col-lg-6">
											<div class="form-group">
												<label for="cargo">Cargo<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-black-tie"></i></span>
													<input class="form-control" type="text" id="cargo" name="cargo" value="<?php echo $affiliate->getCargo();?>" placeholder="Cargo" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->getCargoError();?>
											</div><!-- /.form-group -->

											

										</div><!-- /.col-* -->
										<div class="col-lg-6">
										<div class="form-group" data-toggle="tooltip" title="El número de teléfono fijo ejemp:+584128505504, 14128505504">
												<label for="phone">T&eacute;lefono fijo <span class="required"></span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-phone-square"></i></span>
													<input class="form-control" type="text" pattern="[+][0-9]{12,15}[+]?" id="phone" name="telefonofijo" value="<?php echo $affiliate->getTelefono();?>" placeholder="N&uacute;mero de t&eacute;lefono fijo">
												</div><!-- /.input-group -->
												<?php echo $affiliate->getTelefonoError();?>
											</div><!-- /.form-group -->
										</div>
										<div class="col-lg-6">
										<div class="form-group" data-toggle="tooltip" title="El número de teléfono movil ejemp: +584128505504, 14128505504">
												<label for="phone">T&eacute;lefono novil <span class="required">*</span><i class="fa fa-question-circle"></i></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-mobile-phone"></i></span>
													<input class="form-control" type="text" id="movil"  pattern="[+][0-9]{11,15}[+]?" name="movil" value="<?php echo $affiliate->getMovil();?>" placeholder="N&uacute;mero de t&eacute;lefono movil" required>
												</div><!-- /.input-group -->
												<?php echo $affiliate->getMovilError();?>
											</div><!-- /.form-group -->
										</div>
									
									</div><!-- /.row -->
								
								</div><!-- /.box -->
								
								<!-- <div class="background-white p30 mb30">
									<h3 class="page-title">Datos para el pago de comisiones</h3>
									
								
									<div class="row">

										<div class="col-lg-6 col-sm-4">
										<h5 class="page-title">Transferencia Bancaria</h5>
											<div class="form-group">
												<label for="nombre">Nombre del banco<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-bank"></i></span>
													<input class="form-control" type="text"  pattern="[a-zA-z]+" id="nombre_banco" name="nombre_banco" value="<?php //echo $affiliate->getBanco();?>" placeholder="Nombre del banco" required >
												</div>
												<?php //echo $affiliate->getBancoError();?>
											</div>

											<div class="form-group">
												<label for="cuenta">Cuenta<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
													<input class="form-control" type="text" pattern="[0-9a-zA-z]+" id="cuenta" name="cuenta" value="<?php //echo $affiliate->getCuenta();?>" placeholder="Cuenta." required >
												</div>
												<?php //echo $affiliate->getCuentaError();?>
											</div>

											<div class="form-group" data-toggle="tooltip" title="Solo se permiten digitos númericos, correspondientes a su clabe.">
												<label for="clabe">Clabe<span class="required">*</span><i class="fa fa-question-circle"></i></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
													<input class="form-control" type="text" maxlength="18" id="clabe" pattern="[0-9]{18}" name="clabe" value="<?php //echo $affiliate->getClabe();?>" placeholder="Clabe" required >
												</div>
												<?php// echo $affiliate->getClabeError();?>
											</div>

											<div class="form-group" data-toggle="tooltip" title="Una serie alfanuméricas de 8 u 11 digitos, que sirve para identificar al banco receptor cuando se realiza una transferencia">
												<label for="swift">Swift / Bic<span class="required">*</span><i class="fa fa-question-circle"></i></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
													<input class="form-control" type="text" id="swift" maxlength="11" pattern="[A-Za-z0-9]{8,11}" name="swift" value="<?php //echo $affiliate->getSwift();?>" placeholder="Swift" required >
												</div>
												<?php //echo $affiliate->getSwiftError();?>
											</div>

										</div>



										<div class="col-lg-6 col-sm-4">
											<h5 class="page-title">Deposito a tarjeta</h5>
											<div class="form-group">
												<label for="nombre">Nombre del banco<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-bank"></i></span>
													<input class="form-control" type="text" pattern="[a-zA-z]*" id="nombre_banco_targeta" name="nombre_banco_tarjeta" value="<?php //echo $affiliate->getBancoNombreTarjeta();?>" placeholder="Nombre del banco" required >
												</div>
												<?php //echo $affiliate->getNombreBancoTarjetaError();?>
											</div>
											<div class="form-group" data-toggle="tooltip" title="Número de la targeta de Credito, conlleva 16 digitos solo numéricos.">
												<label for="nombre">N&uacute;mero de tarjeta<span class="required">*</span><i class="fa fa-question-circle"></i></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-cc"></i></span>
													<input class="form-control" type="text" pattern="[0-9]{16}" maxlength="16" minlength="16" id="numero_targeta" name="numero_targeta" value="<?php //echo $affiliate->getTarjeta();?>" placeholder="N&uacute;mero de Tarjeta" required>
												</div>
												<?php //echo $affiliate->getNumeroTarjetaError();?>
											</div>
								
										
												<h5 class="page-title">Transferencia PayPal</h5>
											<div class="form-group">
												<label for="nombre">Email de Paypal<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-cc-paypal"></i></span>
													<input class="form-control" type="email" id="email_paypal" name="email_paypal" value="<?php //echo $affiliate->getEmailPaypal();?>" placeholder="Nombre del banco" required >
												</div>
												<?php //echo $affiliate->getEmailPaypalError();?>
											</div>
										</div>
										
									</div>
									
								
								</div>-->


								<div class="row">
									<div class="col-xs-6">
										<p>Los campos marcados son obligatorios <span class="required">*</span></p>
									</div>
									<div class="col-xs-6 right">
										<button class="btn btn-success btn-xl" type="submit" name="send"><i class="fa fa-paper-plane"></i>Enviar mi solicitud</button>
									</div>
								</div>
							</form>
								<!-- Modal para adjudicar recibo de pago... -->
		<div class="modal fade " id="new-iata" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="true">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content modal-dialog-centered">
					<form  action="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" method="post" accept-charset="utf-8" enctype="multipart/form-data">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">Ingresar codigo IATA</h5>
					</div>

					<div class="modal-body">
<!-- 						<div class="alert alert-success" role="alert" id="alerta" style="display:none">
							Comisión actualizada. Si desea puede actualizar de nuevo.
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
   							 <span aria-hidden="true">&times;</span>
 							 </button>
						</div> -->


							<style>
								.acept-solicitud{
									display: flex;
									justify-content: center;
									flex-direction: column;
									width: 100%;
								}
								.botoneras{
									width: 100%;
									display: flex;
									justify-content: center;
								}
							</style>
						
								<section class="col-xs-12 acept-solicitud container" >
									<style>
										.page-title{
												margin:0px 0px 5px 0px !important;
										}
										.recibopago{
											margin-bottom: 2rem;
										}
									</style>

									<section class="iata" id="datoshotel">
										<style>
											.notification{
												display: none !important;
											}
										</style>
										<div class="notification alert alert-icon alert-dismissible alert-info" role="alert">
											
											<button type="button" class="close" data-dismiss="alert" aria-label="Close">
												<i class="fa fa-times" aria-hidden="true"></i>
											</button>
											
											<label class="notifi"></label>
										</div>
										<div class="row">

								          <div class="col-lg-6 d-flex">
								        
									           <div class="form-group flex" data-toggle="tooltip" title="Insertar codigo Iata." data-placement="bottom">

										            <label for="business-name">Codigo:<span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
										            <div class="input-hotel">
											             <div class="input-group">
												            <span class="input-group-addon"><i class="fa fa-code"></i></span>
												            <input class ="codigoiata form-control" type="text" id="business-name" name="codigoiata" value="" placeholder="Codigo iata" required/>
											            </div>
										            </div>
									           </div>

									            <div class="form-group flex" data-toggle="tooltip" title="Nombre con el que quedará registrado su ciudad o territorio." data-placement="bottom" >
										            <label for="business-name">Aeropuerto:<span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
										            <div class="input-hotel">
											             <div class="input-group">
												            <span class="input-group-addon"><i class="fa fa-fighter-jet"></i></span>
												            <input class ="aeropuerto form-control" type="text" id="business-name" name="aeropuerto" value="" placeholder="aeropuerto" required/>
											            </div>
										            </div>
									           </div>

									     
								            
								          </div>

								           <div class="col-lg-6 d-flex">
								        
									
										
											<div class="form-group">
												<label for="country-select">Pa&iacute;s <span class="required">*</span></label>

												<select class="form-control paisiata" id="country-select-affiliate" name="paisiata" title="Selecciona un pa&iacute;s" data-size="10" data-live-search="true" required>
													<?php echo $affiliate->get_countries();?>
												</select>

											</div>
										
										
											<div class="form-group">

												<label for="state-select">Estado <span class="required">*</span></label>

												<select class="form-control estadoiata"  id="state-select-affiliate" name="estadoiata" title="Luego un estado" data-size="10" data-live-search="true" required>
													<?php echo $affiliate->get_states();?>
												</select>

											</div>										
										
											<div class="form-group">

												<label for="city-select">Ciudad <span class="required"></span></label>
												<select class="form-control ciudadiata" id="city-select-affiliate"  name="ciudadiata" title="Luego una ciudad" data-size="10" data-live-search="true">
													<?php echo $affiliate->get_cities();?>
												</select>
												<?php //echo $iata->getCiudadError();?>

											</div>
										</div>
									
								          
								        
								         
										</div>
									</section>									
								</section>

					<strong> Si no sabes cual es tu codigo Iata del aeropuerto mas cercano al hotel, Puedes buscarlo <a href="https://es.wikipedia.org/wiki/Anexo:Aeropuertos_seg%C3%BAn_el_c%C3%B3digo_IATA" target="_blank">Aqui.!</a> </strong>
								
					</div>
						
					<div class="modal-footer">
						
						<button style="margin-left: auto;" type="button" data-path="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" name="registrar" class="actualizar btn btn-success">Registrar</button>
						<button  type="button" class="cerrarmodal btn btn-secondary" >Cerrar</button>
					</div>
				</form>

				</div>
			</div>
		</div>

							<script>

	$('.actualizar').click(function(){

		var path = $(this).attr('data-path');
		var codigo     = $('.codigoiata').val();
		var aeropuerto = $('.aeropuerto').val();
		var id_estado  = $('select[name="estadoiata"]').val();
		var id_ciudad  = $('select[name="ciudadiata"]').val();		

		$.ajax({
			url: '/admin/controller/grafica.php',
			type: 'POST',
			dataType: 'JSON',
			data: {newiata: true,codigo:codigo,aeropuerto:aeropuerto,estado:id_estado,ciudad:id_ciudad},
		})
		.done(function(data) {
			
			if(data.datos.iataexiste){
				
				$('.notifi').text("No puede registrar un codigo Iata que ya existe, Verifique.");
				$('.notifi').css({
					color: 'white',
				});
				
				$('.alert').show('slow', function() {
					$('.alert').removeClass('notification');
				});
			}

			if(data.datos.registroexitoso){

				$('.notifi').text("Se ha registrado exitosamente el codigo Iata, Ya lo puedes encontrar en el listado");
				$('.notifi').css({
					color: 'white',
				});
				$('.alert').removeClass('alert-info');
				
				$('.alert').addClass('alert-success');


				// var o = new Option(data.iata.id,data.iata.codigo);

				// $('#iata').append(o);

				$('#iata').append('<option value="' + data.iata.id + '">'+data.iata.codigo+'</option>');
				$('#iata').selectpicker('refresh');
				$('.alert').show('slow', function() {
					$('.alert').removeClass('notification');
				});

			}
				
		})
		.fail(function() {
			console.log("error");
		})
		.always(function() {
			console.log("complete");
		});
	});
	
	$('.new-iata').click(function(){
		$('#new-iata').modal('show');
	});

	$('.cerrarmodal').click(function(event) {
		$('#new-iata').modal('hide');
	});

</script>
						
						</div><!-- /.content -->
					</div><!-- /.col-* -->
				</div><!-- /.row -->
			</div><!-- /.container -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>