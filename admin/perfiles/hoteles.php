<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if($_SESSION['user']['id_rol'] != 1 && $_SESSION['user']['id_rol'] != 2 && $_SESSION['user']['id_rol'] != 3){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if(!isset($_SESSION['user']['admin_authorize'])){
	header('Location: '.HOST.'/admin/acceso');
	die();
}

$perfiles = new admin\libs\PerfilesList($con);

$solicitud = new admin\libs\DetallesSolicitud($con);

$search = filter_input(INPUT_GET, 'buscar');

if($_SERVER["REQUEST_METHOD"] == "POST"){

	if(isset($_POST['actualizar'])){
			$perfiles->actualizarcomision($_POST);
	}


}






use Hotel\models\AfiliarHotel;
$affiliate = new AfiliarHotel($con);





$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));
$rpp = 20;
$options = $perfiles->load_data($search, $page, $rpp);

$paging = new assets\libraries\pagination\pagination($options['page'], $options['total']);
$paging->setRPP($rpp);
$paging->setCrumbs(10);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['business_id']) && isset($_POST['suspend_id'])){
		$businesses->change_business_status($_POST);
	}
	
}
$includes = new admin\libs\includes($con);
$properties['title'] = 'Perfiles | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>

<div class="row">
	<div class="col-sm-12">
		<?php echo $perfiles->get_notification();?>

		<div class="page-title">
			<h1>Hoteles
			<form class="pull-right" method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>" target="_blank">
	
			</form>
			</h1>
		</div>
		<div class="background-white p20 mb50">
			
		<table  id="example2" class="display" cellspacing="0" width="100%">
		<thead>
            <tr>
            	
            	
            	<th></th>
            	<th>Hotel</th>
                <th>Direcci&oacute;n</th>
                
                <th>Comisión</th>
               
                
                <th></th>
               

            </tr>
        </thead>

        <tbody>
   			<?php echo $perfiles->ListarHoteles(); ?>
        </tbody>
    </table>




    <script>
    	$(document).ready(function(){

	   var t = $('#example2').DataTable( {
		"paging"        :false,
		"scrollY"       :false,
		"scrollX"       :true,
		"scrollCollapse": true,
         "language": {
                        "lengthMenu": "Mostar _MENU_ "+" "+"Registros por pagina",
                        "info": "",
                        "infoEmpty": "No se encontro ningún perfil de usuario",
                        "infoFiltered": "(filtrada de _MAX_ registros)",
                        "search": "Buscar:",
                        "paginate": {
                            "next":       "Siguiente",
                            "previous":   "Anterior"
                        },
                    },
        "columnDefs": [ {
            "searchable": true,
            "orderable": true,
            "targets": 0
        } ],
        "order": [[ 0, 'desc' ]]
    });

    	});
    </script>


			
		</div>
	</div>
</div>

<!-- Modal para adjudicar Franquiciatario -->
		<div class="modal fade " id="comision" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="false">
			<div class="modal-dialog modal-dialog-centered modal-sm " role="document">
				<div class="modal-content">
					
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">Cambiar Comisión</h5>
							
							
						
		
					</div>

					<div class="modal-body">
						<div class="alert alert-success" role="alert" id="alerta" style="display:none">
							Comisión actualizada. Si desea puede actualizar de nuevo.
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
    						<span aria-hidden="true">&times;</span>
  							</button>
						</div>
							<form  action="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" method="post" accept-charset="utf-8">
								<section class="col-xs-12 acept-solicitud container" >
									<div class="row">
										<div class="comision col-lg-12">
											<div class="form-group">
												<label for="comision" class="form" id="comisionactual">Comisión actual: <strong class="pcomi"></strong></label>
												
												<div  id="sliderdinamico">
													<!-- <input id="ex9" type="text" data-slider-value="" data-slider-min="0"  data-slider-step="1" > -->
												</div>
												
												<span for="comision"  id="val-slider"> </span>
											</div>
										</div>
									</div>
								</section>
							</form>		
					</div>
						
					<div class="modal-footer">
						<button style="margin-left: auto;" type="button"  data-path="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" name="adjudicar" class="actualizar btn btn-success" disabled>Actualizar</button>
						<button  type="button" class="cerrarperfil btn btn-secondary" >Cerrar</button>
					</div>
				</div>
			</div>
		</div>

		<script >
			
			$(document).ready(function(){
				var valorslider;

				

			var solicitud ,perfil,comision;

				$('.cerrarperfil').click(function(){
					location.reload();
				});

				$('.actualizarcomision').click(function(){
					solicitud = $(this).attr('data-solicitud');
					perfil = $(this).attr('data-perfil');
					comision = $(this).attr('data-comision');

					var slider = null;
					if(perfil == "Hotel"){

							if($('#ex9').length){

								var contenedor  = document.getElementById('sliderdinamico');
								var eli = document.getElementById('ex9');
								contenedor.removeChild(eli);

								var slid = document.createElement('input');

								$(slid).attr('data-slider-value',comision);
								$(slid).attr('data-slider-min','0');
								$(slid).attr('data-slider-max','40');
								$(slid).attr('data-slider-step','1');
								$(slid).attr('id','ex9');

								contenedor.appendChild(slid);

					
								 slider = new Slider('#ex9');
								 valorslider = slider.getValue();
								slider.on("slide", function(sliderValue){
									valorslider = sliderValue;
										document.getElementById('val-slider').textContent = sliderValue + " %";
										$('.actualizar').removeAttr('disabled');
										$('.actualizar').attr('data-comision',valorslider);
								});

								
								$('.actualizar').attr('data-perfil','hotel');
								$('.actualizar').attr('data-solicitud',solicitud);
								

								$('.pcomi').text(comision+" %");
								$('#comision').modal('show');


							}else{
							
								
								var contenedor  = document.getElementById('sliderdinamico');

								var slid = document.createElement('input');

								$(slid).attr('data-slider-value',comision);
								$(slid).attr('data-slider-min','0');
								$(slid).attr('data-slider-max','40');
								$(slid).attr('data-slider-step','1');
								$(slid).attr('id','ex9');

								contenedor.appendChild(slid);



								if($('#ex9').length){
								 slider = new Slider('#ex9');
								 valorslider = slider.getValue();
								slider.on("slide", function(sliderValue){
									valorslider = sliderValue;
										document.getElementById('val-slider').textContent = sliderValue + " %";
											$('.actualizar').removeAttr('disabled');
										$('.actualizar').attr('data-comision',valorslider);
								});
								}

								
								$('.actualizar').attr('data-perfil','hotel');
								$('.actualizar').attr('data-solicitud',solicitud);
								$('.pcomi').text(comision+" %");
								$('#comision').modal('show');
							}
							

							

							

					}else if(perfil == 'Franquiciatario'){

						if($('#ex9').length){
							var ele = document.getElementById('sliderdinamico');
							var eli = document.getElementById('ex9');
							ele.removeChild(eli);


							var slid = document.createElement('input');

							$(slid).attr('data-slider-value',comision);
							$(slid).attr('data-slider-min','0');
							$(slid).attr('data-slider-max','8');
							$(slid).attr('data-slider-step','1');
							$(slid).attr('id','ex9');

							ele.appendChild(slid);

							 slider = new Slider('#ex9');
							 valorslider = slider.getValue();
							slider.on("slide", function(sliderValue){
								valorslider = sliderValue;
									document.getElementById('val-slider').textContent = sliderValue + " %";
									$('.actualizar').attr('data-comision',valorslider);
									$('.actualizar').removeAttr('disabled');
							});


							
							$('actualizar').attr('data-perfil','franquiciatario');
							$('actualizar').attr('data-solicitud',solicitud);

							$('.pcomi').text(comision+" %");
							$('.modal').modal('show');
						}else{

							var ele = document.getElementById('sliderdinamico');
							var slid = document.createElement('input');
							$(slid).attr('data-slider-value',comision);
							$(slid).attr('data-slider-min','0');
							$(slid).attr('data-slider-max','8');
							$(slid).attr('data-slider-step','1');
							$(slid).attr('id','ex9');

							ele.appendChild(slid);

							 slider = new Slider('#ex9');
							 valorslider = slider.getValue();
							
							
							slider.on("slide", function(sliderValue){
								valorslider = sliderValue;
									document.getElementById('val-slider').textContent = sliderValue + " %";
									 $('.actualizar').attr('data-comision',valorslider);
									 	$('.actualizar').removeAttr('disabled');
							});
							 
							
						
							$('.actualizar').attr('data-perfil','franquiciatario');
							$('.actualizar').attr('data-solicitud',solicitud);
							$('.pcomi').text(comision+" %");
							$('.modal').modal('show');

						}
						
					}else if(perfil == 'Referidor'){

						if($('#ex9').length){
							var ele = document.getElementById('sliderdinamico');
							var eli = document.getElementById('ex9');
							ele.removeChild(eli);


							var slid = document.createElement('input');

							$(slid).attr('data-slider-value',comision);
							$(slid).attr('data-slider-min','0');
							$(slid).attr('data-slider-max','8');
							$(slid).attr('data-slider-step','1');
							$(slid).attr('id','ex9');

							ele.appendChild(slid);

							 slider = new Slider('#ex9');
							 valorslider = slider.getValue();
							slider.on("slide", function(sliderValue){
								valorslider = sliderValue;
									document.getElementById('val-slider').textContent = sliderValue + " %";
									$('.actualizar').attr('data-comision',valorslider);
									$('.actualizar').removeAttr('disabled');
							});


							
							$('actualizar').attr('data-perfil','franquiciatario');
							$('actualizar').attr('data-solicitud',solicitud);

							$('.pcomi').text(comision+" %");
							$('.modal').modal('show');
						}else{

							var ele = document.getElementById('sliderdinamico');
							var slid = document.createElement('input');
							$(slid).attr('data-slider-value',comision);
							$(slid).attr('data-slider-min','0');
							$(slid).attr('data-slider-max','8');
							$(slid).attr('data-slider-step','1');
							$(slid).attr('id','ex9');

							ele.appendChild(slid);

							 slider = new Slider('#ex9');
							 valorslider = slider.getValue();
							
							
							slider.on("slide", function(sliderValue){
								valorslider = sliderValue;
									document.getElementById('val-slider').textContent = sliderValue + " %";
									 $('.actualizar').attr('data-comision',valorslider);
									 	$('.actualizar').removeAttr('disabled');
							});
							 
							
						
							$('.actualizar').attr('data-perfil','referidor');
							$('.actualizar').attr('data-solicitud',solicitud);
							$('.pcomi').text(comision+" %");
							$('.modal').modal('show');

						}
						
					}

				});




				$('.actualizar').click(function(){

						var comision = $(this).attr('data-comision');
						if(comision == 0){
							comision = $(this).attr('data-comision');
						} 
						var perfil = $(this).attr('data-perfil');
						var solicitud = $(this).attr('data-solicitud');
						var path = $(this).attr('data-path');
						
							$.ajax({
							url: path,
							type: 'POST',
							data: {
							perfil: perfil,
							comision: comision,
							solicitud: solicitud,
							actualizar: true
							},
							cache:false
							})
							
							.done(function(response) {
							
							
							
							$('#alerta').show('400', function() {
							$('#alerta').css('display', 'flex');
							});
							
							$('.pcomi').text(valorslider+" %");
							
							$('actualizar').attr('disabled');
							
							})
							
							.fail(function() {
							console.log("error");
							})

				});


			});

		



		</script>


		<!-- Modal para editar usuario con perfil-->
<div class="modal fade " id="editar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="false">
	<div class="modal-dialog modal-dialog-centered modal-lg " role="document">
		<div class="modal-content">
		<form id="formulario-edicion" method="POS" action="/admin/controller/ControllerRegistro.php" accept-charset="utf-8" enctype="multipart/form-data">

			<div class="modal-header header-actualizacion">
				<h5 class="modal-title title-modal-hotel" id="exampleModalLabel">Hotel</h5>
				<h5 class="codigohoteltitle modal-title" data-container="body" data-toggle="popover" data-placement="bottom" data-content="hola a todoas"></h5>


			</div>

	

			<div class="modal-body">
						
				<!-- REGISTRO DE NUEVO HOTEL CON SU USUARIO>>> -->							

				<style>
									.oculto{
										display: none;
									}
				</style>

				<!-- area de notificaction de la modal... -->
				<div class="oculto notification-reg-hotel alert alert-icon alert-dismissible alert-info" role="alert">
																	
										<button type="button" class="close" data-dismiss="alert" aria-label="Close">
												<i class="fa fa-times" aria-hidden="true"></i>
										</button>
																	
										<strong class="notifi"></strong>
				</div>
						      	
				<style >
						      		.btn-modal{
						      			display: flex;
						      			justify-content: center;
						      			margin-bottom: 3rem;
						      		}
						      		.vtn-new-user{
						      			padding: 1rem 2.5rem;
						      		}

						      		.content-formulario{
						      			max-height: 400px;
						      			overflow-y: auto;
						      			padding: 0px 3rem;
						      		}
				</style>

				<!-- Area de botones de la modal... -->
				<section class="btn-modal">
						      		<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
												
												
												<button type="button" data-toggle="collapse" aria-expanded="true" href="#vtn-date-hotel" aria-controls="vtn-date-hotel" class="date-hotel btn btn-secondary"><i class="fa fa-hotel"></i>Datos de Hotel</button>
												<button type="button"  data-toggle="collapse" aria-expanded="false" href="#vtn-date-pago" aria-controls="vtn-date-pago" class="date-pago btn btn-secondary"><i class="fa fa-money"></i>Datos Para el Pago de Comisiones</button>
									</div>

				</section>

				<section class="content-formulario">

								<div class="collapse " id="vtn-date-hotel" aria-labelledby="vtn-date-hotel" data-parent="#accordion">
								
										<div class="row">
											<h3 class="page-title">Informaci&oacute;n del hotel</h3>

													<div class="col-lg-8">
																
														<div class="form-group" data-toggle="tooltip" title="Los clientes Huespedes de Travel Points pueden afiliarse desde su propio perfil...">
															<label for="business-name">Nombre del hotel <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>

															<input class="form-control" type="text" id="nombrehotel" name="nombre" value="<?php echo $affiliate->getNombre();?>" placeholder="Nombre del hotel" required />
																				<?php echo $affiliate->getNombreError();?>
														</div>
																		
													</div> 

													<div class="col-lg-4">
														<div class="row">
															<div class="col-sm-6 col-md-12 form-group" data-toggle="tooltip" title="El codigo Iata es utilizado para ayudar a agilizar los procesos de transporte aereo y turistico.">
																<label for="category">C&oacute;digo IATA <span class="required">*</span><i class="fa fa-question-circle text-secondary"></i></label>
																<div class="input-group input-iata">
																	<select class="form-control" id="iata" name="iata" title="Seleccionar c&oacute;digo IATA" data-live-search="true" required>
																		<option value="0">Seleccione</option>
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

										<h3 class="page-title">Ubicaci&oacute;n del hotel</h3>

											<div class="row">
												<div class="col-lg-8">
													<div class="form-group">
														<label for="address">Direcci&oacute;n del hotel <span class="required">*</span></label>
														
														<div class="input-group">
																			<span class="input-group-addon"><i class="fa fa-map-o"></i></span>
																			<input class="form-control" type="text" id="address" name="direccion" value="" placeholder="Direcci&oacute;n del hotel" required >
														</div>
																		
													</div>
												</div>

												<div class="col-lg-4">
																	<div class="form-group">
																		<label for="postal-code">C&oacute;digo postal  del hotel <span class="required"></span></label>
																		<div class="input-group">
																			<span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
																			<input class="form-control" type="text" id="postal-code" name="codigopostal" value="<?php echo $affiliate->getCodigoPostal();?>" placeholder="C&oacute;digo postal del hotel">
																		</div><!-- /.input-group -->
																		<?php echo $affiliate->getCodigoPostalError();?>
																	</div><!-- /.form-group -->
												</div>

											</div>

											<div class="row">
												<div class="col-lg-4">

													<div class="form-group">
																			<label for="country-select">Pa&iacute;s <span class="required">*</span></label>
																			<select class="form-control" id="country-select" name="pais" title="Selecciona un pa&iacute;s" data-size="10" data-live-search="true" required>
																				<?php echo $affiliate->get_countries();?>
																			</select>
													</div>

												</div>

												<div class="col-lg-4">
																	<div class="form-group">
																		<label for="state-select">Estado <span class="required">*</span></label>
																		<select class="form-control" id="state-select" name="estado" title="Luego un estado" data-size="10" data-live-search="true" required>
																			<?php echo $affiliate->get_states();?>
																		</select>
																	</div><!-- /.form-group -->
												</div>

												<div class="col-lg-4">
													<div class="form-group">
														<label for="city-select">Ciudad <span class="required"></span></label>
														<select class="form-control" id="city-select" name="ciudad" title="Luego una ciudad" data-size="10" data-live-search="true">
															<?php echo $affiliate->get_cities();?>
														</select>
															<?php echo $affiliate->getCiudadError();?>
													</div>
												</div>

											</div>

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

												<input class="controls form-control mb30" type="text" id="pac-input" placehoder="Escribe una ubicaci&oacute;n" />
												
													<div id="map-canvas"></div>
												
															
												<div class="row">

														<div class="col-sm-6">
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
																<input class="form-control" type="text" id="input-latitude"  name="latitud" value="<?php echo $affiliate->getLatitud();?>" placeholder="Latitud" required>
															</div>
														</div>

														<div class="col-sm-6">
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
																<input class="form-control" type="text" id="input-longitude" name="longitud" value="<?php echo $affiliate->getLongitud();?>" placeholder="Longitud" required>
															</div>
														</div>

												</div>


										<div class="background-white p30 mb30">
													<h3 class="page-title">Responsable del &aacute;rea de promoci&oacute;n</h3>
															
												<div class="row">

													<div class="col-lg-6">
														<div class="form-group">
															<label for="nombre">Nombre<span class="required">*</span></label>
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-address-card-o"></i></span>
																<input class="form-control" type="text" id="nombre_responsable" name="nombre_responsable" value="<?php echo $affiliate->getNombreResponsable();?>" placeholder="Nombre del responsable &aacute;rea de promoci&oacute;n" required>
															</div>
																		
														</div>
													</div>

													<div class="col-lg-6">

														<div class="form-group">
															<label for="apellido">Apellido<span class="required">*</span></label>
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-address-card-o"></i></span>
																<input class="form-control" type="text" id="apellido_responsable" name="apellido_responsable" value="<?php echo $affiliate->getApellidoResponsable();?>" placeholder="Apellido del responsable &aacute;rea de promoci&oacute;n" required >
															</div>
																		
														</div>

													</div>

												</div>
															
												<div class="row">

													<div class="col-lg-6">
														<div class="form-group">
															<label for="email">Email<span class="required">*</span></label>
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
																<input class="form-control" type="email" id="email" name="email" value="" placeholder="Email del responsable" required >
															</div>
																		
														</div>
													</div>
																
													<div class="col-lg-6">
														<div class="form-group">
															<label for="cargo">Cargo<span class="required">*</span></label>
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-black-tie"></i></span>
																<input class="form-control" type="text" id="cargo" name="cargo" value="" placeholder="Cargo" required >
															</div>
														</div>
													</div>

													<div class="col-lg-6">
														<div class="form-group" data-toggle="tooltip" title="El número de teléfono fijo ejemp:+584128505504, 14128505504">
															<label for="phone">T&eacute;lefono fijo <span class="required"></span></label>
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-phone-square"></i></span>
																<input class="form-control" type="text" pattern="[+][0-9]{12,15}[+]?" id="phone" name="telefonofijo" value="" placeholder="N&uacute;mero de t&eacute;lefono fijo">
															</div>
																		
														</div>
													</div>
																
													<div class="col-lg-6">
														<div class="form-group" data-toggle="tooltip" title="El número de teléfono movil ejemp: +584128505504, 14128505504">
															<label for="phone">T&eacute;lefono novil <span class="required">*</span><i class="fa fa-question-circle"></i></label>
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-mobile-phone"></i></span>
																<input class="form-control" type="text" id="movil"  pattern="[+][0-9]{11,15}[+]?" name="movil" value="" placeholder="N&uacute;mero de t&eacute;lefono movil" required>
															</div>
																		
														</div>
													</div>
															
												</div>

												<div class="row">
													<div class="col-xs-6">
														<p>Los campos marcados son obligatorios <span class="required">*</span></p>
													</div>
												</div>
										</div>

								</div>

								<div class="collapse" id="vtn-date-pago">

										<h3 class="page-title">Datos para el pago de comisiones</h3>
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
																			<input class="form-control" type="text" pattern="[a-zA-z]*" id="nombre_banco_targeta" name="nombre_banco_tarjeta" value="<?php //echo $affiliate->getBancoNombreTarjeta();?>" placeholder="Nombre del banco"  >
																		</div>
																		
																	</div>
																	<div class="form-group" data-toggle="tooltip" title="Número de la targeta de Credito, conlleva 16 digitos solo numéricos.">
																		<label for="nombre">N&uacute;mero de tarjeta<span class="required"></span><i class="fa fa-question-circle"></i></label>
																		<div class="input-group">
																			<span class="input-group-addon"><i class="fa fa-cc"></i></span>
																			<input class="form-control" type="text" pattern="[0-9]{16}" maxlength="16" minlength="16" id="numero_targeta" name="numero_targeta" value="<?php //echo $affiliate->getTarjeta();?>" placeholder="N&uacute;mero de Tarjeta" >
																		</div>
																		
																	</div>
														
																
																		<h5 class="page-title">Transferencia PayPal</h5>
																	<div class="form-group">
																		<label for="nombre">Email de Paypal<span class="required"></span></label>
																		<div class="input-group">
																			<span class="input-group-addon"><i class="fa fa-cc-paypal"></i></span>
																			<input class="form-control" type="email" id="email_paypal" name="email_paypal" value="<?php //echo $affiliate->getEmailPaypal();?>" placeholder="Nombre del banco"  >
																		</div>
																		
																	</div>
																		</div>
																						
																	</div>

								</div>
								
				<article class="area-image">
					<figure class="img-hotel">
						<img  class="foto-hotel">
					</figure>
				</article>    
				</section> 

						
						     
		
							
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary btn-usuario"><i class="fa fa-black-tie"></i>Usuario adjudicado</button>
					
					<button style="margin-left: auto;" type="button"  data-path="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" name="elminar" class="eliminar btn btn-danger"><strong class="fa fa-remove"> Eliminar</strong></button>
					<button style="margin-left: auto;" type="submit"  data-path="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" name="adjudicar" class="modificar btn btn-success">Actualizar</button>
					<button  type="button" data-dismiss="modal" class="btn btn-secondary" >Cerrar</button>
				</div>

			</div>
	<input type="hidden" name="actualizar-hotel">
		</form>	
				
		</div>
	</div>
</div>	


<script>
			
			$(document).ready(function() {

				var solicitud = null;

				
				$('#formulario-edicion').bind('submit', function(){
					var datopago = true;

					var resut = false;

					
					var nombrebanco        = $('#nombre_banco').val();
					var cuenta             = $('#cuenta').val();
					var clabe              = $('#clabe').val();
					var emailpaypal        = $('#email_paypal').val();
					var nombrebancotargeta = $('#nombre_banco_targeta').val();
					var numerotargeta      = $('#numero_targeta').val();
					var swift              = $('#swift').val();

				

					if(nombrebanco.length < 1 && cuenta.length < 1 && clabe.length < 1 && swift.length < 1 && nombrebancotargeta.length < 1 && numerotargeta < 1 && emailpaypal.length < 1){

						resut = confirm("Acepta no agregar los datos para el pago de comisiones?");
						if(resut){
							datopago = false;

				
							registrar();
							
						}else{
							return false;
						}
						
					}else{
						registrar();
						
					}
					
					return false;


					function registrar(){
						var btngrabar = $('.modificar');
									
						btngrabar.attr('disabled', 'disabled');
						btngrabar.text("Actualizando Por favor espere");
						var formulario = $('#formulario-edicion');
						if($('.pago')){
							$('.pago').remove();
						}

						if($('.solicitud')){
							$('.solicitud').remove();
						}

						formulario.append('<input type="hidden" class="pago" name="pago" value="'+datopago+'">');

						formulario.append('<input type="hidden" class="solicitud" name="solicitud" value="'+solicitud+'">');

						
									
						$.ajax({
							url: '/admin/controller/ControllerRegistro.php',
							type: 'POST',
							dataType: 'JSON',
							data:formulario.serialize()
									
							})
							.done(function(response) {
							
								if(response.peticion){
									alert(response.mensaje);
									btngrabar.removeAttr('disabled');
									btngrabar.text("Grabar");
									location.reload();

								}else{
									
									btngrabar.removeAttr('disabled');


									btngrabar.fadeIn('10000',function() {
										
										btngrabar.text("Grabar");
									});
									alert(response.mensaje);
									
								}
								return false;
							})
							.fail(function() {

								return false;
								console.log("error");
							})
						return false;
					}

				});

				
				

				$('.actualizarperfil').click(function(){


					solicitud = $(this).attr('data-solicitud');


					$.ajax({
						url: '/admin/controller/ControllerRegistro.php',
						type: 'POST',
						dataType: 'JSON',
						data: {solicitudhotel: solicitud},
					})
					.done(function(response) {

						var codigohotel = '';
							
							$('.eliminar').attr('data-solicitud', solicitud);
							$('.eliminar').change();
							$('#nombrehotel').attr('value', response.datos.hotel);
							
							$("#iata option[value='"+response.datos.id_iata+"']").attr('selected', true);
							$("#iata").change();
							
							$("input[name='website']").val(response.datos.sitio_web);
							$("input[name='direccion']").val(response.datos.direccion);
							$("input[name='codigopostal']").val(response.datos.codigo_postal);
							
							// $("#country-select").append('<option val="'+response.datos.id_pais+'" selected>'+response.datos.pais+'</option>');
							 $("#country-select option[value='"+response.datos.id_pais+"']").attr('selected', true);
							$("#country-select").selectpicker('refresh');

								load_states(response.datos.id_pais);

								function load_states(id){
									var id_pais = id;
									$.ajax({
									type: "POST",
									url: "/ajax.php",
									data: {
									id_pais: id_pais
									},
									dataType: 'json',
									success: function(data){
									$('#state-select').empty();
									for(var i in data){
										if(data[i].id_estado == response.datos.id_estado){
											$('#state-select').append('<option value="' + data[i].id_estado + '" selected>' + data[i].estado + '</option>');
										}else{
											$('#state-select').append('<option value="' + data[i].id_estado + '">' + data[i].estado + '</option>');
										}
										
									}
									$('#state-select').selectpicker('refresh');
									}
									});
								}

								load_cities(response.datos.id_estado);
												function load_cities(id){
												var id_estado = id;
												$.ajax({
												type: "POST",
												url: "/ajax.php",
												data: {
												id_estado: id_estado
												},
												dataType: 'json',
												success: function(data){
												$('#city-select').empty();
												for(var i in data){
												
												if(data[i].id_ciudad == response.datos.id_ciudad){
												$('#city-select').append('<option value="' + data[i].id_ciudad + '" selected>' + data[i].ciudad + '</option>');
												}else{
												$('#city-select').append('<option value="' + data[i].id_ciudad + '">' + data[i].ciudad + '</option>');
												}
												
												}
												$('#city-select').selectpicker('refresh');
												}
												});
												}

							$('#input-latitude').val(response.datos.latitud);
							$('#input-longitude').val(response.datos.longitud);

							// Cargamos los datos del mapa
							// 0
							
							// map.setOptions(mapOptions);
							// 
							// 
							// 
							var latitud = parseFloat(response.datos.latitud);
							var longitud = parseFloat(response.datos.longitud);
							
							var direccion = new google.maps.LatLng({lat:latitud,lng:longitud});



							
							
					          

							$('input[name="nombre_responsable"]').val(response.datos.nombre_responsable);
							$('input[name="apellido_responsable"]').val(response.datos.apellido_responsable);
							$('input[name="email"]').val(response.datos.email);
							$('input[name="cargo"]').val(response.datos.cargo);
							$('input[name="telefonofijo"]').val(response.datos.telefono_fijo);
							$('input[name="movil"]').val(response.datos.telefono_movil);
							$('input[name="numero_targeta"]').val(response.datos.numero_tarjeta);
							$('input[name="nombre_banco"]').val(response.datos.banco);
							$('.foto-hotel').attr('src', '/assets/img/hoteles/'+response.datos.imagen);

							if(response.datos.clabe != 0){
								$('input[name="clabe"]').val(response.datos.clabe);
							}

							if(response.datos.swift != 0){
								$('input[name="swift"]').val(response.datos.swift);
							}
							
							
							$('input[name="cuenta"]').val(response.datos.cuenta);
							$('input[name="nombre_banco_tarjeta"]').val(response.datos.banco_tarjeta);
							$('input[name="email_paypal"]').val(response.datos.email_paypal);
							$('.title-modal-hotel').html('Hotel '+ response.datos.hotel);
							$('.codigohoteltitle').html('Codigo de Hotel: '+response.datos.codigo);

							$('#codigohotel').removeAttr('value');
							$('#codigohotel').attr('value',response.datos.codigo);
							$('#codigohotel').change();

						if(response.datos.codigo == 'ninguna'){
							if($('.generarcodigo')){
								$('.generarcodigo').remove();
							}

							$('.header-actualizacion').append('<button class="generarcodigo" type="button" data-toggle="tooltip" title="Generar Codigo" data-placement="left"><i class="fa fa-cog"></i></button>');
							$('.generarcodigo').tooltip('enable');
							$('.crearcodigo').attr('data-iata', response.datos.iata);
							$('.crearcodigo').attr('data-hotel', response.datos.hotel);

						
							if($('.actualizarcodigo')){
								$('.actualizarcodigo').remove();
							}

								$('.generarcodigo').click(function(){
								
								$('#generarcodigohotel').modal('show');
								
								});


						}else{

							if($('.actualizarcodigo')){
								$('.actualizarcodigo').remove();
							}
							$('.header-actualizacion').append('<button class="actualizarcodigo" type="button" data-toggle="tooltip" title="Modificar codigo" data-placement="left"><i class="fa fa-cog"></i></button>');
							$('.actualizarcodigo').tooltip('enable');
							
							if($('.generarcodigo')){
								$('.generarcodigo').remove();
							}

							$('.crearcodigo').attr('data-iata', response.datos.iata);
							$('.crearcodigo').attr('data-hotel', response.datos.hotel);
							
							
								$('.actualizarcodigo').click(function(event) {
								
								$('#generarcodigohotel').modal('show');
								
								});

						}
					})
					.fail(function() {
						console.log("error");
					})
			
					


					$('#editar').modal('show');
				});

	




								

	$('.establecercodigo').click(function(event) {

								var codigo = $('#codigohotel').val();

								if(codigo.length < 4){
									alert('Genere un codigo valido, que sea mayor o igual que 4 caracteres, sin espacios en blancos');
									return false;
								}

								$.ajax({
									url: '/admin/controller/ControllerRegistro.php',
									type: 'POST',
									dataType: 'JSON',
									data: {solicitudcodigo: 'crearcodigo',codigo:codigo,solicitud:solicitud},
								})
								.done(function(response) {
									if(response.peticion){
										alert(response.mensaje);

										$('.codigohoteltitle').html('Codigo de Hotel: '+codigo);
										$('.generarcodigo').remove();

										if($('.actualizarcodigo')){
											$('.actualizarcodigo').remove();
										}

										$('.header-actualizacion').append('<button class="actualizarcodigo" type="button" data-toggle="tooltip" title="Modificar codigo" data-placement="left"><i class="fa fa-cog"></i></button>');
										
										$('.actualizarcodigo').tooltip('enable');
										codigohotel = codigo;
										$('#generarcodigohotel').modal('hide');

										$('.actualizarcodigo').click(function(event) {
										
										$('#generarcodigohotel').modal('show');
										
										});

									}else{

										alert('El codigo no se pudo generar, intentelo mas tarde...');
										$('#generarcodigohotel').modal('hide');

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


			$('.eliminar').click(function(){
				var solicitud = $(this).attr('data-solicitud');
				var path = $(this).attr('data-path');

				var result = confirm('Esta seguro de eliminar a este hotel?');

				if(result){
						$.ajax({
							url:'/admin/controller/ControllerRegistro.php',
							type: 'POST',
							dataType: 'JSON',
							data: {solicitud: 'eliminar',hotel:solicitud,perfil:'Hotel'},
						})
						.done(function(response) {
							
							if(response.peticion){
								alert(response.mensaje);
								location.reload();
							}else{
							alert(response.mensaje);
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



</script>

<script>

$(document).ready(function() {

	$('.new-iata').click(function(){
		$('#new-iata').modal('show');
	});
	$('.cerrarmodal').click(function(event) {
		/* Act on the event */
		$('#new-iata').modal('hide');

	});
	$('.actualizariata').click(function(){

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
});

</script>





<?php echo $footer = $includes->get_admin_footer(); ?>



<!-- Modal para adjudicar nuevo codigo Iata..... -->
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
								.modal-body{
									/*max-height: 450px;
									overflow-y: auto;*/
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
												            <input class ="codigoiata form-control" type="text" id="codigoiata" name="codigoiata" value="" placeholder="Codigo iata" required/>
											            </div>
										            </div>
									           </div>

									            <div class="form-group flex" data-toggle="tooltip" title="Nombre con el que quedará registrado su ciudad o territorio." data-placement="bottom" >
										            <label for="business-name">Aeropuerto:<span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
										            <div class="input-hotel">
											             <div class="input-group">
												            <span class="input-group-addon"><i class="fa fa-fighter-jet"></i></span>
												            <input class ="aeropuerto form-control" type="text" id="aeropuerto" name="aeropuerto" value="" placeholder="aeropuerto" required/>
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

						<strong> Si no sabes cual es tu codigo Iata del aeropuerto mas cercano al hotel, Puedes buscarlo <a href="https://es.wikipedia.org/wiki/Anexo:Aeropuertos_seg%C3%BAn_el_c%C3%B3digo_IATA" target="_blank">Aqui.!</a>
						</strong>
								
					</div>
						
					<div class="modal-footer">
						<button style="margin-left: auto;" type="button" data-path="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" name="registrar" class="actualizariata btn btn-success">Registrar</button>
						<button  type="button" class="cerrarmodal btn btn-secondary" >Cerrar</button>
					</div>
				</form>
				</div>
			</div>
		</div>

<!-- Modal para Adjudicar codigo-->
<div class="modal fade aceptar modales" id="generarcodigohotel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
					<div class="modal-dialog modal-sm" role="document">
					<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel"><label class="cert-date mr20"><label class="cert-date nombrehotel form"></label></label></h5>
						
						<small class="iata cert-date"></small>
						<button type="button" class="close" >
						<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="alert alert-success alert-aceptada" role="alert">
								Genere el Codigo de Hotel. 
						</div>
							<form  action="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" method="post" accept-charset="utf-8">
								<section class="col-xs-12 acept-solicitud container" >
									<div class="row">
										<div class="codigohotel col-lg-12" data-toggle="tooltip" title="Cree o genere el Codigo de hotel, puedes asociar las siglas del Codigo iata, mas las Siglas del hotel o como desees...">
											<div class="form-group">
												<label for="codigohotel" >Codigo de Hotel * <i class="fa fa-question-circle"></i></label>
												<div class="codigo">
													<input type="text" name="codigohotel" class="form-control" id="codigohotel" placeholder="Ejemp AGUHCN" required>
													<button type="button" name="generarcodigo" class="btn btn-outline-secondary crearcodigo">Generar</button>
												</div>
											</div>
										</div>
									</div>
								</section>
							</form>		
						</div>
							<div class="modal-footer">
						
								<button  style="margin-left: auto;"  id="btncodigo" ype="button" name="adjudicar" class="establecercodigo btn btn-success">Grabar</button>
								<button  type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
						
							</div>
						</div>
					</div>
</div>

<script>
	
	$(document).ready(function() {
		$('.new-user-hotel').click(function(){
			var hotel = $(this).attr('data-hotel');


			 $('#asociaruserhotel').attr('data-hotel',hotel);
			$('#modalnewuserhotel').modal('show');

			

		});
	});
</script>


<!-- --------------------------------------------------------------------------------- -->
<!-- 					ASOCIACION DE USUARIO AL HOTEL                                 -->
<!-- --------------------------------------------------------------------------------- -->

<div class="modal fade" id="modalnewuserhotel" tabindex="-1" role="dialog" aria-labelledby="modalnewfranquiciatario" aria-hidden="true" data-backdrop="false">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalScrollableTitle">Nuevo Usuario con Perfil de hotel</h5>
		
      </div>

      <form method="post" id="formulario-franquiciatario" action="<?php echo _safe(HOST.'/admin/controller/ControllerRegistro.php');?>" autocomplete="off">
	     
	     <div class="modal-body">

			<style>
				.oculto{
					display: none;
				}
			</style>

	      	<div class="oculto notification-reg-hotel alert alert-icon alert-dismissible alert-info" role="alert">
												
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<i class="fa fa-times" aria-hidden="true"></i>
					</button>
												
					<strong class="notifi"></strong>
			</div>

	      	<style >
	      		.btn-modal{
	      			display: flex;
	      			justify-content: center;
	      			margin-bottom: 3rem;
	      		}
	      		.vtn-new-user{
	      			padding: 1rem 2.5rem;
	      		}

	      		.content-formulario{
	      			max-height: 400px;
	      			overflow-y: auto;
	      			padding: 0px 3rem;
	      		}
	      	</style>

	 		<section class="content-formulario">

			      	<!-- DATOS DE USUARIO -->
			      	<div class=" col-lg-12" id="vtn-date-user-franquiciatario">
					<div class="vtn-new-user">
							
						<div class="form-group" id="user-search" data-toggle="tooltip" title="" data-placement="bottom">
									<label for="user-search-input">Usuario que se va asignar</label>
									<div class="search-placeholder" id="user-search-placeholder">
										<img src="<?php echo HOST;?>/assets/img/user_profile/default.jpg" class="meta-img img-rounded">
									</div>
									<input type="text" class="form-control typeahead" name="usuario" id="user-search-input" value="" placeholder="Nombre del usuario asociar." autocomplete="off" required />
								
						</div>

						<div class="alert alert-info">
							<p>Encuentra a tu referente al perfil por su nombre o nombre de usuario (username). Este campo obligatorio</p>
							<p>Despues podras asignar otros datos necesarios.</p>
						</div>							
									
					</div>
					</div>
							
			</section>     		
	     </div>
	      <div class="modal-footer footer-r">
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	        <button type="submit" id="asociaruserhotel" class="grabar btn btn-primary">Grabar</button>
	      </div>

	     
      </form>

    </div>

  </div>
</div>
</div>
