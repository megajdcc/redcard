<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';
$con = new assets\libs\connection();

use Hotel\models\AfiliarHotel;
use admin\libs\Iata;

use assets\libs\includes as Includes;

$iata = new Iata($con);

if(!isset($_SESSION['user'])){

	$login = new assets\libs\user_login($con);
	
	if($_SERVER["REQUEST_METHOD"] == "POST"){
		$login->set_data($_POST);
	}

}else{
	$affiliate = new AfiliarHotel($con);
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		if(isset($_POST['hotel'])){
			$affiliate->enviarsolicitud($_POST);
		}
	}
}



$includes = new Includes($con);
$properties['title'] = 'Afiliar hotel | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
	

	

	<div class="main">
		<div class="main-inner">
			<div class="container">
				<?php echo $con->get_notify();?>



<?php if(!isset($_SESSION['user'])){ ?>

				<div class="row">
					<div class="col-sm-7 col-md-8 mb50">
						<div class="page-title">
							<h1>¡Afilia tu hotel!</h1>
							<p>Env&iacute;anos una solicitud para publicar tu hotel en nuestro directorio.</p>
						</div>
						<p>Solo los socios pueden afiliar un hotel. <a href="<?php echo HOST;?>/hazte-socio">Hazte socio</a> o inicia sesi&oacute;n.</p>
					</div>

					<div class="col-sm-5 col-md-4">
					<?php echo $login->get_notification(); ?>
						<div class="page-title">
							<h2 class="mb0">Iniciar sesi&oacute;n</h2>
						</div><!-- /.page-title -->
						<?php echo $login->get_login_error(); ?>
						<form method="post" action="<?php echo _safe(HOST.'/login');?>">
							<div class="form-group">
								<label for="email">Correo electr&oacute;nico</label>
								<input type"email" class="form-control" name="email" id="email" value="<?php echo $login->get_email();?>" placeholder="Correo electr&oacute;nico" required />
								<?php echo $login->get_email_error();?>
							</div><!-- /.form-group -->

							<div class="form-group">
								<label for="password">Contrase&ntilde;a</label>
								<input type="password" class="form-control" name="password" id="password" placeholder="Contrase&ntilde;a" required />
								<?php echo $login->get_password_error();?>
							</div><!-- /.form-group -->

							<button type="submit" class="btn btn-primary pull-right">¡Entrar!</button>
						</form>
					</div>
				</div>
<?php }else{ ?>



		<script>

			$(document).ready(function() {
						grecaptcha.ready(function(){

								grecaptcha.execute('6LdeqKYUAAAAAJMjfm51tW7h8O8nx0ymBEBy_NgT',{
														action: 'solicituddehotel'
														}).then(function(token){
															
														$('#token').val(token);
								return true;
															
							});
					})
			});
				
				
			
		</script>



				<div class="row">
					<div class="col-sm-12">

						<div class="content">
							<?php echo $affiliate->get_notification();?>
							<div class="page-title">
								<h1>¡Solicitud de Hotel</h1>
								<p>Env&iacute;anos una solicitud para publicar tu hotel en nuestro directorio.</p>
							</div>



							<form method="post" id="formulario-solicitud" action="<?php echo _safe(HOST.'/afiliar-hotel');?>" enctype="multipart/form-data">
								
								<div class="background-white p30 mb50">
									<h3 class="page-title">Informaci&oacute;n de hotel</h3>

									<div class="row">

										<div class="col-lg-8">
											<div class="form-group" data-toggle="tooltip" title="Los clientes Huespedes de Travel Points pueden afiliarse desde su propio perfil...">
												<label for="business-name">Nombre del hotel deseado<span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>

												<input class="form-control" type="text" id="business-name" name="hotel" value="<?php echo $affiliate->getNombre();?>" placeholder="Nombre del hotel" required />
												<?php echo $affiliate->getNombreError();?>
											</div>
										</div>

										<div class="col-lg-4">
											<div class="row">
												<div class="col-sm-6 col-md-12 form-group" data-toggle="tooltip" title="El codigo Iata es utilizado para ayudar a agilizar los procesos de transporte aereo y turistico.">
													<label for="category">C&oacute;digo IATA mas cercano <span class="required"></span><i class="fa fa-question-circle text-secondary"></i></label>

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

									</div>
								</div>


								<div class="row">
									
									<div class="col-xs-6">
										<p>Los campos marcados son obligatorios <span class="required">*</span></p>
									</div>
									
									<div class="col-xs-6 right">
										<button class="btn btn-success btn-xl" type="submit" name="send"><i class="fa fa-paper-plane"></i>Enviar mi solicitud</button>
									</div>

								</div>

								<input type="hidden" name="token" id="token" >

							</form>

						</div>

					</div>
				</div>


<!-- Modal para Crear el codigo IATA..... -->
	<div class="modal fade " id="new-iata" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content modal-dialog-centered">
					
				<form  action="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" method="post" accept-charset="utf-8" enctype="multipart/form-data">
						<div class="modal-header">
							<h5 class="modal-title" id="exampleModalLabel">Ingresar codigo IATA</h5>
						</div>

						<div class="modal-body">


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

													</div>

												</div>
									         
											</div>

										</section>	

									</section>

							<strong> Si no sabes cual es tu codigo Iata del aeropuerto mas cercano al hotel, Puedes buscarlo <a href="https://es.wikipedia.org/wiki/Anexo:Aeropuertos_seg%C3%BAn_el_c%C3%B3digo_IATA" target="_blank">Aqui.!</a> </strong>
									
						</div>
							
						<div class="modal-footer">
							
							<button style="margin-left: auto;" type="button" data-path="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" name="registrar" class="actualizar btn btn-success">Registrar</button>
							<button  type="button" class="cerrarmodal btn btn-secondary">Cerrar</button>

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


<?php } ?>

		</div><!-- /.container -->
	</div><!-- /.main-inner -->
</div><!-- /.main -->

			












	

<?php echo $footer = $includes->get_main_footer(); ?>

	