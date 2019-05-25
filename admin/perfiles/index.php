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

$search = filter_input(INPUT_GET, 'buscar');

if($_SERVER["REQUEST_METHOD"] == "POST"){

	if(isset($_POST['actualizar'])){
			$perfiles->actualizarcomision($_POST);
	}
}

use admin\libs\DetallesSolicitud;
use admin\libs\DetallesSolicitudFranquiciatario;
use admin\libs\DetallesSolicitudReferidor;

$solicitud = new DetallesSolicitud($con);
$solicitudfr = new DetallesSolicitudFranquiciatario($con->con);
$solicitudrf = new DetallesSolicitudReferidor($con->con);

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
	if(isset($_POST['action']) && $_POST['action'] == 'adjudicar'){


		if($_POST['perfil'] == 'Hotel'){
			$solicitud->adjudicar($_POST['perfil'],$_POST['comision'],$_POST['codigohotel'],$_POST['hotel']);
		}else if($_POST['perfil'] == 'Franquiciatario'){
			$solicitudfr->adjudicaradmin($_POST['comision'],$_POST['codigohotel'],$_POST['hotel'], $_POST['franquiciatario']);
		}else if($_POST['perfil'] == 'Referidor'){
			$solicitudrf->adjudicaradmin($_POST['comision'],$_POST['codigohotel'],$_POST['hotel'], $_POST['referidor']);
		}
		
	
	}
}

$reg = new assets\libs\user_signup($con);
if($_SERVER["REQUEST_METHOD"] == "POST"){
	$reg->setData($_POST,'admin');
}

if(filter_input(INPUT_GET, 'ref')){
	$reg->setReferral($_GET['ref']);
}
use Hotel\models\AfiliarHotel;
use admin\libs\Iata;




$iata = new Iata($con);

$affiliate = new AfiliarHotel($con);

$includes = new admin\libs\includes($con);
$properties['title'] = 'Perfiles | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>



<div class="row">
	<div class="col-sm-12">
		<?php echo $perfiles->get_notification();?>


		<div class="background-white p20 mb30">

			<?php if(isset($_SESSION['notification']['registro_hotel'])){?>
						
				<div class="alert alert-icon alert-dismissible alert-success" role="alert">
												
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<i class="fa fa-times" aria-hidden="true"></i>
						</button>
												
					<label class="notifi"><?php echo $_SESSION['notification']['registro_hotel'];?></label>
				</div>
				

			<?php unset($_SESSION['notification']['registro_hotel']);
		} ?>
			<div class="row  page-title">
				<div class="d-flex justify-content-between">
						<div class="col-lg-4">
						<h1 class="">Nuevos Perfiles</h1>
						</div>
						<div class="col-lg-8 d-flex justify-content-end">
						<!-- Botones para agregar nuevos perfiles... -->
						
						<div class="btn-group" role="group" aria-label="Basic example">
						<button type="button" class="new-hotel btn btn-secondary"><i class="fa fa-hotel"></i>Nuevo Hotel</button>
						<button type="button" data-toggle="tooltip" title="Nuevo Usuario con perfil de Hotel..." data-placement="bottom" class="new-franquiciatario btn btn-secondary"><i class="fa fa-black-tie"></i>Nuevo Franquiciatario</button>
						<button type="button" class="new-referidor btn btn-secondary"><i class="fa fa-black-tie"></i>Nuevo Referidor</button>
						</div>
						
						
						
						</div>

				</div>


				
			</div>


			
			

			<div class="row">
				<div class="col-lg-12" id="perfilesnew">
					
				</div>


				<script>
					
					$(document).ready(function() {
						$(document).ready(function() {
						
							$.ajax({
								url: '/admin/controller/grafica.php',
								type: 'POST',
								dataType: 'json',
								data: {grafica: 'perfilesnuevos'},
							})
							.done(function(response) {
								var options = {
											 chart: {
											 		renderTo: 'perfilesnew',
											        type: 'pie'
											    },
											   lang:{
															decimalPoint: ',',
								   						thousandsSep: '.'
													},
											    title: {
											        text: 'Perfiles Nuevos'
											    },
											    xAxis: {
											        type: 'category'
											    },
											  
											    
											    plotOptions: {
											        pie: {
														allowPointSelect:true,
														cursor:'pointer',
														borderWidth: 0,
											            dataLabels: {
											               enabled: true,
											               format: '{point.y:.0f}'
											            },
											            showInLegend:true,
											        }},

											    tooltip: {
											        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b> {point.y:.0f}</b>'
											    },
											    series: [ {
											    	name: "perfiles",
            										colorByPoint: true,
											    } ],
								   				}; 
									 options.series[0].data = response;
									
									var grafica = Highcharts.chart(options);
									 	
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
			</div>

		</div>

		<div class="page-title">
			<h1>Usuarios con adjudicación de perfil
			<form class="pull-right" method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>" target="_blank">
	
			</form>
			</h1>
		</div>
		<div class="background-white p20 mb50">
			
		<table  id="example" class="display" cellspacing="0" width="100%">
		<thead>
            <tr>
            	
            	
            	<th>Foto</th>
            	<th>Email</th>
                <th>Nombre y apellido</th>
                <th>Perfil</th>
                <th>Comisión</th>
               
                <th>Ultimo Logín</th>
               <!--  <th></th> -->
               

            </tr>
        </thead>

        <tbody>
   			<?php echo $perfiles->ListarPerfiles(); ?>


        </tbody>
    </table>




    <script>
    	$(document).ready(function(){

	   var t = $('#example').DataTable( {
		"paging"        :false,
		"scrollY"       :"400px",
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
							$('#comision').modal('show');

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
							$('#comision').modal('show');
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
							$('#comision').modal('show');

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
					
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">Editar Usuarios</h5>
							
							
						
		
					</div>

					<div class="modal-body">
						<div class="alert alert-success" role="alert" id="alerta" style="display:none">
							Comisión actualizada. Si desea puede actualizar de nuevo.
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
		    <span aria-hidden="true">&times;</span>
		  </button>
						</div>
							
					</div>
						
					<div class="modal-footer">
						<button style="margin-left: auto;" type="button"  data-path="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" name="adjudicar" class="actualizar btn btn-success" disabled>Actualizar</button>
						<button  type="button" class="cerrarperfil btn btn-secondary" >Cerrar</button>
					</div>
				</div>
			</div>
		</div>


		<script>
			
			$(document).ready(function() {
				$('.actualizarperfil').click(function(){
					$('#editar').modal('show');
				});
			});
		</script>

<!-- Script de activaciones de Modales -->
<script>
	

	$(document).ready(function(){
		
		// Enviamos el Formulario
		// 
	


		// Captura de formulario para registrar Hotel
		$('#formulario').bind("submit",function(){
					
					var nombrehotel = $('#nombrehotel').val();
					if(nombrehotel.length < 2 ){
						verificado = false;
						alert('El Nombre de hotel debe ser un nombre Valido, mayor a 2 caracteres y no estar vacio');
						return false;
					}

					var iata = $('#iata').val();
					
					if(iata == null || iata == 0){
						verificado = false;
						alert('Seleccione un codigo IATA, si no conoce el Codigo IATA de tu zona, puedes ingresar uno en el panel de edicion de IATA alli tienes toda la info...');
						return false;
					}


					var estado = $('#state-select').val();
					
					if(estado == null || estado == 0){
						verificado = false;
						alert('Seleccione un estado donde esta ubicado el Hotel');
						return false;
					}

					var nombrebanco        = $('#nombre_banco').val();
					var cuenta             = $('#cuenta').val();
					var clabe              = $('#clabe').val();
					var emailpaypal        = $('#email_paypal').val();
					var nombrebancotargeta = $('#nombre_banco_targeta').val();
					var numerotargeta      = $('#numero_targeta').val();
					var swift              = $('#swift').val();

					var datopago = true;

					var resut = false;

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
						return false;
					}

					function registrar(){
						var btngrabar = $('.grabar');
									
									 btngrabar.attr('disabled', 'disabled');
									 btngrabar.text("Guardando Por favor espere");
									 var formulario = $('#formulario');
									// formulario.append('<input type="hidden" name="pago" value="'+datopago+'">');

									var form = new FormData(document.getElementById("formulario"));
								 	form.append("pago",datopago);
									
									$.ajax({
									url: formulario.attr('action'),
									type: 'POST',
									dataType: 'JSON',
									data: form,
									cache:false,
									contentType:false,
									processData:false
								
									})
									.done(function(response) {
									
									if(response.hotel_registrado){
									
									
									$('.notification-reg-hotel').removeClass('oculto');
									
									$('.notifi').text(response.mensaje);
									
									$('.notification-reg-hotel').removeClass('alert-info');
									$('.notification-reg-hotel').addClass('alert-success');
									btngrabar.css({
									display: 'none',
									
									});
									
									
									$('.footer-r').html('<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button type="button" class="adjudicar-codigo btn btn-primary" data-toggle="modal" data-target="#exampleModal">Adjudicar Ahora</button>')
									// $('.modal-footer').html('<button type="button" class="adjudicar-codigo btn btn-primary" >Adjudicar Ahora</button>')
									
									// $('.adjudicar-codigo').attr('data-toggle', 'tooltip');
									$('.adjudicar-codigo').attr('title', 'Si desea puede Asignar el codigo de hotel y la comisión ahora mismo...');
									// $('.adjudicar-codigo').attr('data-placement', 'left');
									
									
									$('.nombrehotel').text('Hotel '+response.nombrehotel);
									$('.iata').text('Codigo Iata '+response.codigoiata);
									
									
									$('.generarcodigo').attr('data-iata', response.codigoiata);
									$('.generarcodigo').attr('data-hotel', response.nombrehotel);
									
									
									$('.adjudicar').attr('data-path', '/admin/perfiles/');
									$('.adjudicar').attr('data-perfil', 'Hotel');
									$('.adjudicar').attr('data-hotel', response.id_hotel);
									
									
									
									}else{
									$('.notification-reg-hotel').css({
									display: 'flex !important',
									color: 'Black'
									});
									
									$('.notifi').text(response.mensaje);
									
									$('.notification-reg-hotel').removeClass('alert-info');
									$('.notification-reg-hotel').addClass('alert-danger');
									btngrabar.text('Reenviar Datos');
									btngrabar.removeAttr('disabled');
									btngrabar.attr({
									title: 'Si desea puede reenviar los datos ahora mismo o intentarlo despues.',
									});
									
									btngrabar.attr('data-toggle', 'tooltip');
									btngrabar.attr('data-placement', 'left');
									
									
									}
									
									return false;
									})
									.fail(function() {

										return false;
										console.log("error");
									})
									return false;
					}
				return false;

		});

		//Captura de formulario para registrar Franquiciatario
		
		$('#formulario-franquiciatario').bind("submit",function(){


					var contra1 = $('#password-franquiciatario').val();
					var contra2 = $('#password-retype-franquiciatario').val();

					var verificado = true;
					if(contra1 != contra2){
						verificado = false;
						alert('Las contrasenas no son iguales por favor verifique...');
						return false;

					}

					if(contra1.length < 7){
						verificado = false;
						alert('La contrasena debe tener al menos 7 caracteres...');
						return false;
					}

					if(contra1 == null){
						verificado = false;
						alert('La contrasena no debe estar vacia o simplemente tener datos vacios... ');
						return false;
					}

					var nombrehotel = $('#nombrehotel-franquiciatario').val();
					if(nombrehotel.length < 2 ){
						verificado = false;
						alert('El Nombre de hotel debe ser un nombre Valido, mayor a 2 caracteres y no estar vacio');
						return false;
					}

					var iata = $('#iata-franquiciatario').val();
					
					if(iata == null || iata == 0){
						verificado = false;
						alert('Seleccione un codigo IATA, si no conoce el Codigo IATA de tu zona, puedes ingresar uno en el panel de edicion de IATA alli tienes toda la info...');
						return false;
					}


					var estado = $('#state-select-franquiciatario').val();
					
					
					if(estado == null || estado == 0){
						verificado = false;
						alert('Seleccione un estado donde esta ubicado el Hotel');
						return false;
					}

					var nombrebanco        = $('#nombre_banco_franquiciatario').val();
					var cuenta             = $('#cuenta_franquiciatario').val();
					var clabe              = $('#clabe_franquiciatario').val();
					var emailpaypal        = $('#email_paypal_franquiciatario').val();
					var nombrebancotargeta = $('#nombre_banco_targeta_franquiciatario').val();
					var numerotargeta      = $('#numero_targeta_franquiciatario').val();
					var swift              = $('#swift_franquiciatario').val();

					var datopago = true;

					var resut = false;

					if(nombrebanco.length < 1 && cuenta.length < 1 && clabe.length < 1 && swift.length < 1 && nombrebancotargeta.length < 1 && numerotargeta < 1 && emailpaypal.length < 1){

						resut = confirm("Acepta no agregar los datos para el pago de comisiones?");
						if(resut){
							datopago = false;
							registrarfranquiciatario();
							return false;
						}else{
							return false;
						}
						
					}else{
						
						registrarfranquiciatario();
						return false;
					}

					function registrarfranquiciatario(){

			
						
						var btngrabar = $('.grabar');
									
									btngrabar.attr('disabled', 'disabled');
									btngrabar.text("Guardando Por favor espere");
									var formulario = $('#formulario-franquiciatario');
									formulario.append('<input type="hidden" name="pago" value="'+datopago+'">');
									
									$.ajax({
									url: formulario.attr('action'),
									type: 'POST',
									dataType: 'JSON',
									data:formulario.serialize()
									//data: ,
									})
									.done(function(response) {

									
									if(response.hotel_registrado){
									
									
									$('.notification-reg-hotel').removeClass('oculto');
									
									$('.notifi').text(response.mensaje);
									
									$('.notification-reg-hotel').removeClass('alert-info');
									$('.notification-reg-hotel').addClass('alert-success');
									btngrabar.css({
									display: 'none',
									
									});
									
									
									$('.footer-r').html('<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button type="button" class="adjudicar-codigo btn btn-primary" data-toggle="modal" data-target="#exampleModal">Adjudicar Ahora</button>')
									// $('.modal-footer').html('<button type="button" class="adjudicar-codigo btn btn-primary" >Adjudicar Ahora</button>')
									
									// $('.adjudicar-codigo').attr('data-toggle', 'tooltip');
									$('.adjudicar-codigo').attr('title', 'Si desea puede Asignar el codigo de hotel y la comisión ahora mismo...');
									// $('.adjudicar-codigo').attr('data-placement', 'left');
									
									
									$('.nombrehotel').text('Hotel '+response.nombrehotel);
									$('.iata').text('Codigo Iata '+response.codigoiata);
									
									
									$('.generarcodigo').attr('data-iata', response.codigoiata);
									$('.generarcodigo').attr('data-hotel', response.nombrehotel);
									
									
									$('.adjudicar').attr('data-path', '/admin/perfiles/');
									$('.adjudicar').attr('data-perfil', 'Franquiciatario');
									$('.adjudicar').attr('data-hotel', response.id_hotel);
									$('.adjudicar').attr('data-franquiciatario', response.id_franquiciatario);
									$('#ex8').removeAttr('data-slider-max');

									$('#ex8').attr('data-slider-max','8');
									
									}else{
										$('.notification-reg-hotel').css({
										display: 'flex !important',
										color: 'Black'
										});
										
										$('.notifi').text(response.mensaje);
										
										$('.notification-reg-hotel').removeClass('alert-info');
										$('.notification-reg-hotel').addClass('alert-danger');
										btngrabar.text('Reenviar Datos');
										btngrabar.removeAttr('disabled');
										btngrabar.attr({
										title: 'Si desea puede reenviar los datos ahora mismo o intentarlo despues.',
										});
										
										btngrabar.attr('data-toggle', 'tooltip');
										btngrabar.attr('data-placement', 'left');
										return false;
									}
									
									return false;
									})
									.fail(function() {

										return false;
										console.log("error");
									})
									.always(function(){
										return false;
									})
									return false;
					}

				return false;

		});
		

		//Captura de formulario para registrar Referidor
		
		$('#formulario-referidor').bind("submit",function(){


					var contra1 = $('#password-referidor').val();
					var contra2 = $('#password-retype-referidor').val();

					var verificado = true;
					if(contra1 != contra2){
						verificado = false;
						alert('Las contrasenas no son iguales por favor verifique...');
						return false;

					}

					if(contra1.length < 7){
						verificado = false;
						alert('La contrasena debe tener al menos 7 caracteres...');
						return false;
					}

					if(contra1 == null){
						verificado = false;
						alert('La contrasena no debe estar vacia o simplemente tener datos vacios... ');
						return false;
					}

					var nombrehotel = $('#nombrehotel-referidor').val();
					if(nombrehotel.length < 2 ){
						verificado = false;
						alert('El Nombre de hotel debe ser un nombre Valido, mayor a 2 caracteres y no estar vacio');
						return false;
					}

					var iata = $('#iata-referidor').val();
					
					if(iata == null || iata == 0){
						verificado = false;
						alert('Seleccione un codigo IATA, si no conoce el Codigo IATA de tu zona, puedes ingresar uno en el panel de edicion de IATA alli tienes toda la info...');
						return false;
					}


					var estado = $('#state-select-referidor').val();
					
					
					if(estado == null || estado == 0){
						verificado = false;
						alert('Seleccione un estado donde esta ubicado el Hotel');
						return false;
					}

					var nombrebanco        = $('#nombre_banco_referidor').val();
					var cuenta             = $('#cuenta_referidor').val();
					var clabe              = $('#clabe_referidor').val();
					var emailpaypal        = $('#email_paypal_referidor').val();
					var nombrebancotargeta = $('#nombre_banco_targeta_referidor').val();
					var numerotargeta      = $('#numero_targeta_referidor').val();
					var swift              = $('#swift_referidor').val();

					var datopago = true;

					var resut = false;

					if(nombrebanco.length < 1 && cuenta.length < 1 && clabe.length < 1 && swift.length < 1 && nombrebancotargeta.length < 1 && numerotargeta < 1 && emailpaypal.length < 1){

						resut = confirm("Acepta no agregar los datos para el pago de comisiones?");
						if(resut){
							datopago = false;
							registrarreferidor();
							return false;
						}else{
							return false;
						}
						
					}else{
						
						registrarreferidor();
						return false;
					}

					function registrarreferidor(){

			
						
						var btngrabar = $('.grabar');
									
									btngrabar.attr('disabled', 'disabled');
									btngrabar.text("Guardando Por favor espere");
									var formulario = $('#formulario-referidor');
									formulario.append('<input type="hidden" name="pago" value="'+datopago+'">');
									
									$.ajax({
									url: formulario.attr('action'),
									type: 'POST',
									dataType: 'JSON',
									data:formulario.serialize()
									//data: ,
									})
									.done(function(response) {

									
									if(response.hotel_registrado){
									
									
									$('.notification-reg-hotel').removeClass('oculto');
									
									$('.notifi').text(response.mensaje);
									
									$('.notification-reg-hotel').removeClass('alert-info');
									$('.notification-reg-hotel').addClass('alert-success');
									btngrabar.css({
									display: 'none',
									
									});
									
									
									$('.footer-r').html('<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button type="button" class="adjudicar-codigo btn btn-primary" data-toggle="modal" data-target="#exampleModal">Adjudicar Ahora</button>')
									// $('.modal-footer').html('<button type="button" class="adjudicar-codigo btn btn-primary" >Adjudicar Ahora</button>')
									
									// $('.adjudicar-codigo').attr('data-toggle', 'tooltip');
									$('.adjudicar-codigo').attr('title', 'Si desea puede Asignar el codigo de hotel y la comisión ahora mismo...');
									// $('.adjudicar-codigo').attr('data-placement', 'left');
									
									
									$('.nombrehotel').text('Hotel '+response.nombrehotel);
									$('.iata').text('Codigo Iata '+response.codigoiata);
									
									
									$('.generarcodigo').attr('data-iata', response.codigoiata);
									$('.generarcodigo').attr('data-hotel', response.nombrehotel);
									
									
									$('.adjudicar').attr('data-path', '/admin/perfiles/');
									$('.adjudicar').attr('data-perfil', 'Referidor');
									$('.adjudicar').attr('data-hotel', response.id_hotel);
									$('.adjudicar').attr('data-referidor', response.id_referidor);
									$('#ex8').removeAttr('data-slider-max');

									$('#ex8').attr('data-slider-max','8');
																	
									}else{
										$('.notification-reg-hotel').css({
										display: 'flex !important',
										color: 'Black'
										});
										
										$('.notifi').text(response.mensaje);
										
										$('.notification-reg-hotel').removeClass('alert-info');
										$('.notification-reg-hotel').addClass('alert-danger');
										btngrabar.text('Reenviar Datos');
										btngrabar.removeAttr('disabled');
										btngrabar.attr({
										title: 'Si desea puede reenviar los datos ahora mismo o intentarlo despues.',
										});
										
										btngrabar.attr('data-toggle', 'tooltip');
										btngrabar.attr('data-placement', 'left');
										return false;
									}
									
									return false;
									})
									.fail(function() {

										return false;
										console.log("error");
									})
									.always(function(){
										return false;
									})
									return false;
					}

				return false;

		});
		
		// $('.adjudicar-codigo').click(function(){
		// 	$('#exampleModal').modal('show');
		// });
	
	
		$('.new-hotel').click(function(){
			$('#modalnewhotel').modal('show');
		});

		$('.new-franquiciatario').click(function(){
			$('#modalnewfranquiciatario').modal('show');
		});
		$('.new-referidor').click(function(){
			$('#modalnewreferidor').modal('show');
		});

	});
</script>
<!-- Modales -->


<!-- REGISTRO DE NUEVO HOTEL CON SU USUARIO>>> -->
<div class="modal fade" id="modalnewhotel" tabindex="-1" role="dialog" aria-labelledby="modalnewhotel" aria-hidden="true" data-backdrop="false">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalScrollableTitle">Nuevo Usuario con perfil de hotel</h5>
		
      </div>
      <form method="post" id="formulario" action="<?php echo _safe(HOST.'/admin/controller/ControllerRegistro.php');?>" enctype="multipart/form-data">
      <div class="modal-body">
			<div class="alert alert-icon alert-dismissible alert-info" role="alert">
									<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<i class="fa fa-times" aria-hidden="true"></i>
									</button>
				<small>Puedes alternar entre los distintos botones con formularios solicitados, El requerimiento es que llenes todos los campos a excepción del formulario del pago de comisión que los puedes omitir si desea, esto lo puedes añadir desde el panel de hotel en el botón añadir datos de pago, de todo lo demás, al enviar los datos se pedirá confirmación de esto. Los Campos necesarios al enviarlos se validaran y se te pedirá corrección de ser necesario.</small>
			</div>

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

      	<section class="btn-modal">
      		<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
						<!-- <button type="button" data-toggle="collapse" href="#vtn-date-user" aria-expanded="true" aria-controls="collapseExample"class="date-user btn btn-secondary"><i class="fa fa-user"></i>Datos de Usuario</button> -->

						<button type="button" data-toggle="collapse" aria-expanded="false" href="#vtn-date-hotel" aria-controls="vtn-date-hotel" class="date-hotel btn btn-secondary"><i class="fa fa-hotel"></i>Datos de Hotel</button>
						<button type="button"  data-toggle="collapse" aria-expanded="false" href="#vtn-date-pago" aria-controls="vtn-date-pago" class="date-pago btn btn-secondary"><i class="fa fa-money"></i>Datos Para el Pago de Comisiones</button>
			</div>

      	</section>

      	<section class="content-formulario">
      		
    
		<div class="collapse" id="vtn-date-hotel">
		
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
												<option value="0" selected>Seleccione</option>
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
													<input class="form-control" type="text" id="address" name="direccion" value="<?php echo $affiliate->getDireccion();?>" placeholder="Direcci&oacute;n del hotel" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->getDirecccionError();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="postal-code">C&oacute;digo postal  del hotel <span class="required"></span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
													<input class="form-control" type="text" id="postal-code" name="codigopostal" value="<?php echo $affiliate->getCodigoPostal();?>" placeholder="C&oacute;digo postal del hotel">
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
												<label for="city-select">Ciudad <span class="required"></span></label>
												<select class="form-control" id="city-select" name="ciudad" title="Luego una ciudad" data-size="10" data-live-search="true">
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


										<div class="background-white p30 mb30">
									<h3 class="page-title">Responsable del &aacute;rea de promoci&oacute;n</h3>
									
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="nombre">Nombre<span class="required">*</span></label>
												<div class="input-group">
														<span class="input-group-addon"><i class="fa fa-address-card-o"></i></span>
													<input class="form-control" type="text" id="nombre_responsable" name="nombre_responsable" value="<?php echo $affiliate->getNombreResponsable();?>" placeholder="Nombre del responsable &aacute;rea de promoci&oacute;n" required>
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
									
									</div>
									<div class="row">
										<div class="col-lg-12">
											<div class="form-group" data-toggle="tooltip" title="Esta ser&aacute; la imagen de perfil de tu Hotel. Se recomienda una imagen horizontal panor&aacute;mica y un peso inferior a 2 MB. La imagen debe ser formato JPG o PNG.">
												<label for="photo">Adjunta una fotograf&iacute;a de tu hotel <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
												<input type="file" id="affiliate-hotel" name="foto" required />
											
											</div><!-- /.form-group -->
										
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
													<input class="form-control" type="text"  pattern="[a-zA-z]+" id="nombre_banco" name="nombre_banco" value="<?php //echo $affiliate->getBanco();?>" placeholder="Nombre del banco"  >
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
												<?php //echo $affiliate->getNombreBancoTarjetaError();?>
											</div>
											<div class="form-group" data-toggle="tooltip" title="Número de la targeta de Credito, conlleva 16 digitos solo numéricos.">
												<label for="nombre">N&uacute;mero de tarjeta<span class="required"></span><i class="fa fa-question-circle"></i></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-cc"></i></span>
													<input class="form-control" type="text" pattern="[0-9]{16}" maxlength="16" minlength="16" id="numero_targeta" name="numero_targeta" value="<?php //echo $affiliate->getTarjeta();?>" placeholder="N&uacute;mero de Tarjeta" >
												</div>
												<?php //echo $affiliate->getNumeroTarjetaError();?>
											</div>
								
										
												<h5 class="page-title">Transferencia PayPal</h5>
											<div class="form-group">
												<label for="nombre">Email de Paypal<span class="required"></span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-cc-paypal"></i></span>
													<input class="form-control" type="email" id="email_paypal" name="email_paypal" value="<?php //echo $affiliate->getEmailPaypal();?>" placeholder="Nombre del banco"  >
												</div>
												<?php //echo $affiliate->getEmailPaypalError();?>
											</div>
												</div>
																
											</div>

								</div>
						</div>  
						</section>     		
     
      <div class="modal-footer footer-r">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="grabar btn btn-primary">Grabar</button>
      </div>

      <input type="hidden" name="form-hotel" value="true">
      </form>

    </div>
  </div>
</div>




<!-- REGISTRO DE NUEVO FRANQUICIATARIO CON SU USUARIO>>>> -->

<div class="modal fade" id="modalnewfranquiciatario" tabindex="-1" role="dialog" aria-labelledby="modalnewfranquiciatario" aria-hidden="true" data-backdrop="false">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalScrollableTitle">Nuevo Usuario con perfil de Franquiciatario</h5>
		
      </div>

      <form method="post" id="formulario-franquiciatario" action="<?php echo _safe(HOST.'/admin/controller/ControllerRegistro.php');?>" autocomplete="off">
	      <div class="modal-body">


				<div class="alert alert-icon alert-dismissible alert-info" role="alert">
										<button type="button" class="close" data-dismiss="alert" aria-label="Close">
										<i class="fa fa-times" aria-hidden="true"></i>
										</button>
					<small>Puedes alternar entre los distintos botones con formularios solicitados, El requerimiento es que llenes todos los campos a excepción del formulario del pago de comisión que los puedes omitir si desea, esto lo puedes añadir desde el panel de hotel en el botón añadir datos de pago, de todo lo demás, al enviar los datos se pedirá confirmación de esto. Los Campos necesarios al enviarlos se validaran y se te pedirá corrección de ser necesario. </small>
				</div>

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

	      	<section class="btn-modal">
	      		<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
							<button type="button" data-toggle="collapse" href="#vtn-date-user-franquiciatario" aria-expanded="true" aria-controls="collapseExample"class="date-user btn btn-secondary"><i class="fa fa-user"></i>Datos de Usuario</button>
							<button type="button" data-toggle="collapse" aria-expanded="false" href="#vtn-date-hotel-franquiciatario" aria-controls="vtn-date-hotel" class="date-hotel btn btn-secondary"><i class="fa fa-hotel"></i>Datos de Hotel</button>
							<button type="button"  data-toggle="collapse" aria-expanded="false" href="#vtn-date-pago-franquiciatario" aria-controls="vtn-date-pago" class="date-pago btn btn-secondary"><i class="fa fa-money"></i>Datos Para el Pago de Comisiones</button>
				</div>
	      	</section>

	 		<section class="content-formulario">

			      	<!-- DATOS DE USUARIO -->
			      	<div class="collapse" id="vtn-date-user-franquiciatario">
							<div class="vtn-new-user">
							
											<div class="form-group" data-toggle="tooltip" title="Tu nombre de usuario debe ser alfanum&eacute;rico. No puede contener espacios, acentos o caracteres especiales. Debe contener entre 3 y 50 caracteres. Recomendamos 20 o menos caracteres.">
												<label for="username" >Username (use no space)| Nombre de usuario (sin espacios o acentos) <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
												<input type="text" class="form-control" name="username" id="username-franquiciatario" value="<?php echo $reg->getUsername();?>" placeholder="Nombre de usuario (sin espacios o acentos)" required minlength="3" maxlength="50" />
												<?php echo $reg->getUsernameError();?>
											</div><!-- /.form-group -->
											<div class="form-group">
												<label for="email">Email | Correo electr&oacute;nico <span class="required">*</span></label>
												<input type="email" class="form-control" name="emailuser" value="<?php echo $reg->getEmail();?>" placeholder="Correo electr&oacute;nico" required />
												<?php echo $reg->getEmailError();?>
											</div><!-- /.form-group -->
											<div class="form-group" data-toggle="tooltip" title="La contrase&ntilde;a debe contener al menos 6 caracteres y debe ser distinta de tu nombre de usuario y tu correo electr&oacute;nico.">
												<label for="password">Password | Contrase&ntilde;a <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
												<input type="password" class="form-control" name="password" id="password-franquiciatario" placeholder="Contrase&ntilde;a" required />
												<?php echo $reg->getPasswordError();?>
											</div><!-- /.form-group -->
											<div class="form-group">
												<label for="password-retype">Rewrite Password | Confirmar contrase&ntilde;a <span class="required">*</span></label>
												<input type="password" class="form-control" name="password-retype" id="password-retype-franquiciatario" placeholder="Confirmar contrase&ntilde;a" required />
												<?php echo $reg->getRetypePasswordError();?>
											</div><!-- /.form-group -->
											
									
							</div>
					</div>
				


				<!-- DATOS DE HOTEL -->
					<div class="collapse" id="vtn-date-hotel-franquiciatario">
					
							<div class="row">
								<h3 class="page-title">Informaci&oacute;n del hotel</h3>
										<div class="col-lg-8">
													
											<div class="form-group" data-toggle="tooltip" title="Los clientes Huespedes de Travel Points pueden afiliarse desde su propio perfil...">
												<label for="business-name">Nombre del hotel <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>

												<input class="form-control" type="text" id="nombrehotel-franquiciatario" name="nombrehotel" value="<?php echo $affiliate->getNombre();?>" placeholder="Nombre del hotel" required />
																	<?php echo $affiliate->getNombreError();?>
											</div>
															
										</div>
															
										<div class="col-lg-4">
											<div class="row">
												<div class="col-sm-6 col-md-12 form-group" data-toggle="tooltip" title="El codigo Iata es utilizado para ayudar a agilizar los procesos de transporte aereo y turistico.">
													<label for="category">C&oacute;digo IATA <span class="required">*</span><i class="fa fa-question-circle text-secondary"></i></label>
													<div class="input-group input-iata">
														<select class="form-control" id="iata-franquiciatario" name="iata" title="Seleccionar c&oacute;digo IATA" data-live-search="true" required>
															<option value="0" selected>Seleccione</option>
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
													<input class="form-control" pattern="([--:\w?@%&+~#=]*\.[a-z]{2,4}\/{0,2})((?:[?&](?:\w+)=(?:\w+))+|[--:\w?@%&+~#=]+)?" type="text" id="website-franquiciatario" name="website" value="<?php echo $affiliate->getSitioWeb();?>" placeholder="Sitio web del hotel">
												</div><!-- /.input-group -->
													<?php echo $affiliate->getWebsiteError();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->

							</div>

							<h3 class="page-title">Ubicaci&oacute;n del hotel</h3>
								<div class="row">
									<div class="col-lg-8">
										<div class="form-group">
											<label for="address">Direcci&oacute;n del hotel <span class="required">*</span></label>
											<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-map-o"></i></span>
																<input class="form-control" type="text" id="address-franquiciatario" name="direccion" value="<?php echo $affiliate->getDireccion();?>" placeholder="Direcci&oacute;n del hotel" required >
											</div><!-- /.input-group -->
															<?php echo $affiliate->getDirecccionError();?>
										</div><!-- /.form-group -->
									</div><!-- /.col-* -->
									
									<div class="col-lg-4">
										<div class="form-group">
											<label for="postal-code">C&oacute;digo postal  del hotel <span class="required"></span></label>
												<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
																<input class="form-control" type="text" id="postal-code-franquiciatario" name="codigopostal" value="<?php echo $affiliate->getCodigoPostal();?>" placeholder="C&oacute;digo postal del hotel">
												</div><!-- /.input-group -->
															<?php echo $affiliate->getCodigoPostalError();?>
										</div><!-- /.form-group -->
									</div><!-- /.col-* -->
								</div><!-- /.row -->

								<div class="row">
													<div class="col-lg-4">
														<div class="form-group">
															<label for="country-select">Pa&iacute;s <span class="required">*</span></label>
															<select class="form-control" id="country-select-franquiciatario" name="pais" title="Selecciona un pa&iacute;s" data-size="10" data-live-search="true" required>
																<?php echo $affiliate->get_countries();?>
															</select>
														</div><!-- /.form-group -->
													</div><!-- /.col-* -->
													<div class="col-lg-4">
														<div class="form-group">
															<label for="state-select">Estado <span class="required">*</span></label>
															<select class="form-control" id="state-select-franquiciatario" name="estado" title="Luego un estado" data-size="10" data-live-search="true" required>
																<?php echo $affiliate->get_states();?>
															</select>
														</div><!-- /.form-group -->
													</div><!-- /.col-* -->
													<div class="col-lg-4">
														<div class="form-group">
															<label for="city-select">Ciudad <span class="required"></span></label>
															<select class="form-control" id="city-select-franquiciatario" name="ciudad" title="Luego una ciudad" data-size="10" data-live-search="true">
																<?php echo $affiliate->get_cities();?>
															</select>
															<?php echo $affiliate->getCiudadError();?>
														</div><!-- /.form-group -->
													</div><!-- /.col-* -->
								</div><!-- /.row -->

									<hr>

								         <h3 class="page-title">Tus Datos de contacto.</h3>
								          <small class="">Ya tenemos tus datos personales solo confirmanos tus números de contacto.</small>
								         <div class="row">
								         
								         
								        <div class="col-lg-6">
								            <div class="form-group" data-toggle="tooltip" title="Tu nombre">
								               <label for="phone">Nombre:<span class="required">*</span></label>
								               <div class="input-group">
								                 <span class="input-group-addon"><i class="fa fa-file"></i></span>
								                 <input class="form-control" type="text" id="nombre" name="nombre" value="" placeholder="Nombre" required>
								               </div>
								               
								             
								            </div>
								            <div class="form-group" data-toggle="tooltip" title="Tu Apellido">
								               <label for="phone">Apellido:<span class="required">*</span></label>
								               <div class="input-group">
								                 <span class="input-group-addon"><i class="fa fa-file"></i></span>
								                 <input class="form-control" type="text"  id="apellido" name="apellido" value="" placeholder="Apellido" required>
								               </div>
								               
								               
								            </div>

								            <div class="form-group" data-toggle="tooltip" title="Confirmanos tu Email">
								               <label for="emailfranquiciatario">Email:<span class="required"></span></label>
								               <div class="input-group">
								                 <span class="input-group-addon"><i class="fa fa-file"></i></span>
								                 <input class="form-control" type="email"  id="emailfranquiciatario" name="emailfranquiciatario" value="" placeholder="Email Franquiciatario" >
								               </div>
								               
								            
								            </div>
								         
								         </div>


								    <div class="col-lg-6">
								        <div class="form-group" data-toggle="tooltip" title="El número de teléfono fijo ejemp:+584128505504, 14128505504">
								            <label for="phone">T&eacute;lefono fijo <span class="required"></span><i class="fa fa-question-circle"></i></label>
								            <div class="input-group">
								             <span class="input-group-addon"><i class="fa fa-phone-square"></i></span>
								             <input class="form-control" type="text" pattern="[+][0-9]{12,15}[+]?" id="phone-franquiciatario" name="telefonofijo" value="" placeholder="N&uacute;mero de t&eacute;lefono fijo">
								          	</div>

								        </div>
								          <div class="form-group" data-toggle="tooltip" title="El número de teléfono movil ejemp: +584128505504, 14128505504">
								            <label for="phone">T&eacute;lefono novil <span class="required">*</span><i class="fa fa-question-circle"></i></label>
								            <div class="input-group">
								             <span class="input-group-addon"><i class="fa fa-mobile-phone"></i></span>
								             <input class="form-control" type="text" id="movil-franquiciatario"  pattern="[+][0-9]{11,15}[+]?" name="movil" value="" placeholder="N&uacute;mero de t&eacute;lefono movil" required>
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
				


				<!-- DATO DE PAGO DE COMISIONES -->
					<div class="collapse" id="vtn-date-pago-franquiciatario">

							<h3 class="page-title">Datos para el pago de comisiones</h3>
								<div class="row">
									
									<div class="col-lg-6 col-sm-4">
											<h5 class="page-title">Transferencia Bancaria</h5>
												<div class="form-group">
													<label for="nombre">Nombre del banco<span class="required"></span></label>
													<div class="input-group">
															<span class="input-group-addon"><i class="fa fa-bank"></i></span>
																<input class="form-control" type="text"  pattern="[a-zA-z]+" id="nombre_banco_franquiciatario" name="nombre_banco" value="" placeholder="Nombre del banco"  >
													</div>
															
												</div>

														<div class="form-group">
															<label for="cuenta">Cuenta<span class="required"></span></label>
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
																<input class="form-control" type="text" pattern="[0-9a-zA-z]+" id="cuenta_franquiciatario" name="cuenta" value="" placeholder="Cuenta."  >
															</div>
															<?php //echo $affiliate->getCuentaError();?>
														</div>

														<div class="form-group" data-toggle="tooltip" title="Solo se permiten digitos númericos, correspondientes a su clabe.">
															<label for="clabe">Clabe<span class="required"></span><i class="fa fa-question-circle"></i></label>
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
																<input class="form-control" type="text" maxlength="18" id="clabe_franquiciatario" pattern="[0-9]{18}" name="clabe" value="" placeholder="Clabe"  >
															</div>
														
														</div>

														<div class="form-group" data-toggle="tooltip" title="Una serie alfanuméricas de 8 u 11 digitos, que sirve para identificar al banco receptor cuando se realiza una transferencia">
															<label for="swift">Swift / Bic<span class="required"></span><i class="fa fa-question-circle"></i></label>
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
																<input class="form-control" type="text" id="swift_franquiciatario" maxlength="11" pattern="[A-Za-z0-9]{8,11}" name="swift" value="" placeholder="Swift"  >
															</div>
															
														</div>

									</div>



									<div class="col-lg-6 col-sm-4">
														<h5 class="page-title">Deposito a tarjeta</h5>
														<div class="form-group">
															<label for="nombre">Nombre del banco<span class="required"></span></label>
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-bank"></i></span>
																<input class="form-control" type="text" pattern="[a-zA-z]*" id="nombre_banco_targeta_franquiciatario" name="nombre_banco_tarjeta" value="" placeholder="Nombre del banco"  >
															</div>
															
														</div>
														<div class="form-group" data-toggle="tooltip" title="Número de la targeta de Credito, conlleva 16 digitos solo numéricos.">
															<label for="nombre">N&uacute;mero de tarjeta<span class="required"></span><i class="fa fa-question-circle"></i></label>
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-cc"></i></span>
																<input class="form-control" type="text" pattern="[0-9]{16}" maxlength="16" minlength="16" id="numero_targeta_franquiciatario" name="numero_targeta" value="" placeholder="N&uacute;mero de Tarjeta" >
															</div>
														
														</div>
											
													
															<h5 class="page-title">Transferencia PayPal</h5>
														<div class="form-group">
															<label for="nombre">Email de Paypal<span class="required"></span></label>
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-cc-paypal"></i></span>
																<input class="form-control" type="email" id="email_paypal_franquiciatario" name="email_paypal" value="" placeholder="Nombre del banco"  >
															</div>
															
														</div>
									</div>
																			
								</div>

					</div>

			</section>     		
	     
	      <div class="modal-footer footer-r">
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	        <button type="submit" class="grabar btn btn-primary">Grabar</button>
	      </div>

	      <input type="hidden" name="form-franquiciatario" value="true">
      </form>

    </div>

  </div>
</div>
</div>


<!-- REGISTRO DE NUEVO REFERIDOR CON SU USUARIO>>>> -->

<div class="modal fade" id="modalnewreferidor" tabindex="-1" role="dialog" aria-labelledby="modalnewreferidor" aria-hidden="true" data-backdrop="false">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalScrollableTitle">Nuevo Usuario con perfil de Referidor</h5>
		
      </div>

      <form method="post" id="formulario-referidor" action="<?php echo _safe(HOST.'/admin/controller/ControllerRegistro.php');?>" autocomplete="off">
	      <div class="modal-body">


				<div class="alert alert-icon alert-dismissible alert-info" role="alert">
										<button type="button" class="close" data-dismiss="alert" aria-label="Close">
										<i class="fa fa-times" aria-hidden="true"></i>
										</button>
					<small>Puedes alternar entre los distintos botones con formularios solicitados, El requerimiento es que llenes todos los campos a excepción del formulario del pago de comisión que los puedes omitir si desea, esto lo puedes añadir desde el panel de hotel en el botón añadir datos de pago, de todo lo demás, al enviar los datos se pedirá confirmación de esto. Los Campos necesarios al enviarlos se validaran y se te pedirá corrección de ser necesario. </small>
				</div>

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

	      	<section class="btn-modal">
	      		<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
							<button type="button" data-toggle="collapse" href="#vtn-date-user-referidor" aria-expanded="true" aria-controls="collapseExample"class="date-user btn btn-secondary"><i class="fa fa-user"></i>Datos de Usuario</button>
							<button type="button" data-toggle="collapse" aria-expanded="false" href="#vtn-date-hotel-referidor" aria-controls="vtn-date-hotel" class="date-hotel btn btn-secondary"><i class="fa fa-hotel"></i>Datos de Hotel</button>
							<button type="button"  data-toggle="collapse" aria-expanded="false" href="#vtn-date-pago-referidor" aria-controls="vtn-date-pago" class="date-pago btn btn-secondary"><i class="fa fa-money"></i>Datos Para el Pago de Comisiones</button>
				</div>
	      	</section>

	 		<section class="content-formulario">

			      	<!-- DATOS DE USUARIO -->
			      	<div class="collapse" id="vtn-date-user-referidor">
							<div class="vtn-new-user">
							
											<div class="form-group" data-toggle="tooltip" title="Tu nombre de usuario debe ser alfanum&eacute;rico. No puede contener espacios, acentos o caracteres especiales. Debe contener entre 3 y 50 caracteres. Recomendamos 20 o menos caracteres.">
												<label for="username" >Username (use no space)| Nombre de usuario (sin espacios o acentos) <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
												<input type="text" class="form-control" name="username" id="username-referidor" value="<?php echo $reg->getUsername();?>" placeholder="Nombre de usuario (sin espacios o acentos)" required minlength="3" maxlength="50" />
												<?php echo $reg->getUsernameError();?>
											</div><!-- /.form-group -->
											<div class="form-group">
												<label for="email">Email | Correo electr&oacute;nico <span class="required">*</span></label>
												<input type="email" class="form-control" name="emailuser" value="<?php echo $reg->getEmail();?>" placeholder="Correo electr&oacute;nico" required />
												<?php echo $reg->getEmailError();?>
											</div><!-- /.form-group -->
											<div class="form-group" data-toggle="tooltip" title="La contrase&ntilde;a debe contener al menos 6 caracteres y debe ser distinta de tu nombre de usuario y tu correo electr&oacute;nico.">
												<label for="password">Password | Contrase&ntilde;a <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
												<input type="password" class="form-control" name="password" id="password-referidor" placeholder="Contrase&ntilde;a" required />
												<?php echo $reg->getPasswordError();?>
											</div><!-- /.form-group -->
											<div class="form-group">
												<label for="password-retype">Rewrite Password | Confirmar contrase&ntilde;a <span class="required">*</span></label>
												<input type="password" class="form-control" name="password-retype" id="password-retype-referidor" placeholder="Confirmar contrase&ntilde;a" required />
												<?php echo $reg->getRetypePasswordError();?>
											</div><!-- /.form-group -->
											
									
							</div>
					</div>
				


				<!-- DATOS DE HOTEL -->
					<div class="collapse" id="vtn-date-hotel-referidor">
					
							<div class="row">
								<h3 class="page-title">Informaci&oacute;n del hotel</h3>
										<div class="col-lg-8">
													
											<div class="form-group" data-toggle="tooltip" title="Los clientes Huespedes de Travel Points pueden afiliarse desde su propio perfil...">
												<label for="business-name">Nombre del hotel <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>

												<input class="form-control" type="text" id="nombrehotel-referidor" name="nombrehotel" value="<?php echo $affiliate->getNombre();?>" placeholder="Nombre del hotel" required />
																	<?php echo $affiliate->getNombreError();?>
											</div>
															
										</div>
															
										<div class="col-lg-4">
											<div class="row">
												<div class="col-sm-6 col-md-12 form-group" data-toggle="tooltip" title="El codigo Iata es utilizado para ayudar a agilizar los procesos de transporte aereo y turistico.">
													<label for="category">C&oacute;digo IATA <span class="required">*</span><i class="fa fa-question-circle text-secondary"></i></label>
													<div class="input-group input-iata">
														<select class="form-control" id="iata-referidor" name="iata" title="Seleccionar c&oacute;digo IATA" data-live-search="true" required>
															<option value="0" selected>Seleccione</option>
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
													<input class="form-control" pattern="([--:\w?@%&+~#=]*\.[a-z]{2,4}\/{0,2})((?:[?&](?:\w+)=(?:\w+))+|[--:\w?@%&+~#=]+)?" type="text" id="website-referidor" name="website" value="<?php echo $affiliate->getSitioWeb();?>" placeholder="Sitio web del hotel">
												</div><!-- /.input-group -->
													<?php echo $affiliate->getWebsiteError();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->

							</div>

							<h3 class="page-title">Ubicaci&oacute;n del hotel</h3>
								<div class="row">
									<div class="col-lg-8">
										<div class="form-group">
											<label for="address">Direcci&oacute;n del hotel <span class="required">*</span></label>
											<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-map-o"></i></span>
																<input class="form-control" type="text" id="address-referidor" name="direccion" value="<?php echo $affiliate->getDireccion();?>" placeholder="Direcci&oacute;n del hotel" required >
											</div><!-- /.input-group -->
															<?php echo $affiliate->getDirecccionError();?>
										</div><!-- /.form-group -->
									</div><!-- /.col-* -->
									
									<div class="col-lg-4">
										<div class="form-group">
											<label for="postal-code">C&oacute;digo postal  del hotel <span class="required"></span></label>
												<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
																<input class="form-control" type="text" id="postal-code-referidor" name="codigopostal" value="<?php echo $affiliate->getCodigoPostal();?>" placeholder="C&oacute;digo postal del hotel">
												</div><!-- /.input-group -->
															<?php echo $affiliate->getCodigoPostalError();?>
										</div><!-- /.form-group -->
									</div><!-- /.col-* -->
								</div><!-- /.row -->

								<div class="row">
													<div class="col-lg-4">
														<div class="form-group">
															<label for="country-select">Pa&iacute;s <span class="required">*</span></label>
															<select class="form-control" id="country-select-referidor" name="pais" title="Selecciona un pa&iacute;s" data-size="10" data-live-search="true" required>
																<?php echo $affiliate->get_countries();?>
															</select>
														</div><!-- /.form-group -->
													</div><!-- /.col-* -->
													<div class="col-lg-4">
														<div class="form-group">
															<label for="state-select">Estado <span class="required">*</span></label>
															<select class="form-control" id="state-select-referidor" name="estado" title="Luego un estado" data-size="10" data-live-search="true" required>
																<?php echo $affiliate->get_states();?>
															</select>
														</div><!-- /.form-group -->
													</div><!-- /.col-* -->
													<div class="col-lg-4">
														<div class="form-group">
															<label for="city-select">Ciudad <span class="required"></span></label>
															<select class="form-control" id="city-select-referidor" name="ciudad" title="Luego una ciudad" data-size="10" data-live-search="true">
																<?php echo $affiliate->get_cities();?>
															</select>
															<?php echo $affiliate->getCiudadError();?>
														</div><!-- /.form-group -->
													</div><!-- /.col-* -->
								</div><!-- /.row -->

									<hr>

								         <h3 class="page-title">Tus Datos de contacto.</h3>
								          <small class="">Ya tenemos tus datos personales solo confirmanos tus números de contacto.</small>
								<div class="row">
								         
								         
								        <div class="col-lg-6">
								            <div class="form-group" data-toggle="tooltip" title="Tu nombre">
								               <label for="phone">Nombre:<span class="required">*</span></label>
								               <div class="input-group">
								                 <span class="input-group-addon"><i class="fa fa-file"></i></span>
								                 <input class="form-control" type="text" id="nombrereferidor" name="nombre" value="" placeholder="Nombre" required>
								               </div>
								               
								             
								            </div>
								            <div class="form-group" data-toggle="tooltip" title="Tu Apellido">
								               <label for="phone">Apellido:<span class="required">*</span></label>
								               <div class="input-group">
								                 <span class="input-group-addon"><i class="fa fa-file"></i></span>
								                 <input class="form-control" type="text"  id="apellidoreferidor" name="apellido" value="" placeholder="Apellido" required>
								               </div>
								               
								               
								            </div>
								         
								        </div>


									    <div class="col-lg-6">
									        <div class="form-group" data-toggle="tooltip" title="El número de teléfono fijo ejemp:+584128505504, 14128505504">
									            <label for="phone">T&eacute;lefono fijo <span class="required"></span><i class="fa fa-question-circle"></i></label>
									            <div class="input-group">
									             <span class="input-group-addon"><i class="fa fa-phone-square"></i></span>
									             <input class="form-control" type="text" pattern="[+][0-9]{12,15}[+]?" id="phone-referdor" name="telefonofijo" value="" placeholder="N&uacute;mero de t&eacute;lefono fijo">
									          	</div>

									        </div>
									          <div class="form-group" data-toggle="tooltip" title="El número de teléfono movil ejemp: +584128505504, 14128505504">
									            <label for="phone">T&eacute;lefono novil <span class="required">*</span><i class="fa fa-question-circle"></i></label>
									            <div class="input-group">
									             <span class="input-group-addon"><i class="fa fa-mobile-phone"></i></span>
									             <input class="form-control" type="text" id="movil-referidor"  pattern="[+][0-9]{11,15}[+]?" name="movil" value="" placeholder="N&uacute;mero de t&eacute;lefono movil" required>
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

				


				<!-- DATO DE PAGO DE COMISIONES -->
					<div class="collapse" id="vtn-date-pago-referidor">

							<h3 class="page-title">Datos para el pago de comisiones</h3>
								<div class="row">
									
									<div class="col-lg-6 col-sm-4">
											<h5 class="page-title">Transferencia Bancaria</h5>
												<div class="form-group">
													<label for="nombre">Nombre del banco<span class="required"></span></label>
													<div class="input-group">
															<span class="input-group-addon"><i class="fa fa-bank"></i></span>
																<input class="form-control" type="text"  pattern="[a-zA-z]+" id="nombre_banco_referidor" name="nombre_banco" value="" placeholder="Nombre del banco"  >
													</div>
															
												</div>

														<div class="form-group">
															<label for="cuenta">Cuenta<span class="required"></span></label>
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
																<input class="form-control" type="text" pattern="[0-9a-zA-z]+" id="cuenta_referidor" name="cuenta" value="" placeholder="Cuenta."  >
															</div>
															<?php //echo $affiliate->getCuentaError();?>
														</div>

														<div class="form-group" data-toggle="tooltip" title="Solo se permiten digitos númericos, correspondientes a su clabe.">
															<label for="clabe">Clabe<span class="required"></span><i class="fa fa-question-circle"></i></label>
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
																<input class="form-control" type="text" maxlength="18" id="clabe_referidor" pattern="[0-9]{18}" name="clabe" value="" placeholder="Clabe"  >
															</div>
														
														</div>

														<div class="form-group" data-toggle="tooltip" title="Una serie alfanuméricas de 8 u 11 digitos, que sirve para identificar al banco receptor cuando se realiza una transferencia">
															<label for="swift">Swift / Bic<span class="required"></span><i class="fa fa-question-circle"></i></label>
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
																<input class="form-control" type="text" id="swift_referidor" maxlength="11" pattern="[A-Za-z0-9]{8,11}" name="swift" value="" placeholder="Swift"  >
															</div>
															
														</div>

									</div>



									<div class="col-lg-6 col-sm-4">
														<h5 class="page-title">Deposito a tarjeta</h5>
														<div class="form-group">
															<label for="nombre">Nombre del banco<span class="required"></span></label>
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-bank"></i></span>
																<input class="form-control" type="text" pattern="[a-zA-z]*" id="nombre_banco_targeta_referidor" name="nombre_banco_tarjeta" value="" placeholder="Nombre del banco"  >
															</div>
															
														</div>
														<div class="form-group" data-toggle="tooltip" title="Número de la targeta de Credito, conlleva 16 digitos solo numéricos.">
															<label for="nombre">N&uacute;mero de tarjeta<span class="required"></span><i class="fa fa-question-circle"></i></label>
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-cc"></i></span>
																<input class="form-control" type="text" pattern="[0-9]{16}" maxlength="16" minlength="16" id="numero_targeta_referidor" name="numero_targeta" value="" placeholder="N&uacute;mero de Tarjeta" >
															</div>
														
														</div>
											
													
															<h5 class="page-title">Transferencia PayPal</h5>
														<div class="form-group">
															<label for="nombre">Email de Paypal<span class="required"></span></label>
															<div class="input-group">
																<span class="input-group-addon"><i class="fa fa-cc-paypal"></i></span>
																<input class="form-control" type="email" id="email_paypal_referidor" name="email_paypal" value="" placeholder="Nombre del banco"  >
															</div>
															
														</div>
									</div>
																			
								</div>

					</div>

			</section>     		
	     
	      <div class="modal-footer footer-r">
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	        <button type="submit" class="grabar btn btn-primary">Grabar</button>
	      </div>

	      <input type="hidden" name="form-referidor" value="true">
      </form>

    </div>

  </div>
</div>

</div>

</div>











<!-- Modal para adjudicar recibo de pago... -->
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

					<strong> Si no sabes cual es tu codigo Iata del aeropuerto mas cercano al hotel, Puedes buscarlo <a href="https://es.wikipedia.org/wiki/Anexo:Aeropuertos_seg%C3%BAn_el_c%C3%B3digo_IATA" target="_blank">Aqui.!</a> </strong>
								
					</div>
						
					<div class="modal-footer">
						
						<button style="margin-left: auto;" type="button" data-path="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" name="registrar" class="actualizar btn btn-success">Registrar</button>
						<button  type="button" class="cerrarmodal btn btn-secondary" >Cerrar</button>
					</div>
				</form>

				</div>
			</div>
	







<?php echo $footer = $includes->get_admin_footer(); ?>

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
<!-- Modal para Adjudicar codigo y comision -->
<div class="modal fade aceptar modales" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
					<div class="modal-dialog modal-lg" role="document">
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
								Genere el Codigo de Hotel y la Comision... 
						</div>
							<form  action="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" method="post" accept-charset="utf-8">
								<section class="col-xs-12 acept-solicitud container" >
									<div class="row">
										<div class="codigohotel col-lg-5" data-toggle="tooltip" title="Cree o genere el Codigo de hotel, puedes asociar las siglas del Codigo iata, mas las Siglas del hotel o como desees...">
											<div class="form-group">
												<label for="codigohotel" >Codigo de Hotel * <i class="fa fa-question-circle"></i></label>
												<div class="codigo">
													<input type="text" name="codigohotel" class="form-control" id="codigohotel" placeholder="Ejemp AGUHCN" required>
													<button type="button" name="generarcodigo" class="btn btn-outline-secondary generarcodigo">Generar</button>
												</div>
											</div>
										</div>

										<div class="comision col-lg-7">
											<div class="form-group">
												<label for="comision">Comisión a adjudicar.</label>
												
												<input id="ex8" type="text" id="comision" data-slider-id="ex1Slider" data-slider-min="0" data-slider-max="40" data-slider-step="1" data-slider-value="0">
												<span class="form" id="val-slider">0 %</span>
											</div>
										</div>
									</div>
								</section>
							</form>		
						</div>
							<div class="modal-footer">
						
								<button  style="margin-left: auto;"  type="button" name="adjudicar" class="adjudicar btn btn-success">Registrar</button>
								<button  type="button" class="cerrar btn btn-secondary">Cerrar</button>
						
							</div>
						</div>
					</div>
</div>


