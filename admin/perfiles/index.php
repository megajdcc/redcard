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

$reg = new assets\libs\user_signup($con);
if($_SERVER["REQUEST_METHOD"] == "POST"){
	$reg->setData($_POST,'admin');
}

if(filter_input(INPUT_GET, 'ref')){
	$reg->setReferral($_GET['ref']);
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


		<div class="background-white p20 mb50">
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
                <th></th>
               

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
								$('.modal').modal('show');


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
								$('.modal').modal('show');
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
	

	$(document).ready(function() {
		$('.new-hotel').click(function(){
			$('#modalnewhotel').modal('show');
		});
	});
</script>
<!-- Modales -->

<div class="modal fade" id="modalnewhotel" tabindex="-1" role="dialog" aria-labelledby="modalnewhotel" aria-hidden="true" data-backdrop="false">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalScrollableTitle">Nuevo Usuario con perfil de hotel</h5>

      </div>
      <div class="modal-body">

      	<style >
      		.btn-modal{
      			display: flex;
      			justify-content: center;
      			margin-bottom: 3rem;
      		}
      		.vtn-new-user{
      			padding: 1rem 2.5rem;
      		}
      	</style>

      	<section class="btn-modal">
      		<div class="btn-group" role="group" aria-label="Basic example">
						<button type="button" data-toggle="collapse" href="#vtn-date-user" aria-expanded="false" aria-controls="collapseExample"class="date-user btn btn-secondary"><i class="fa fa-user"></i>Datos de Usuario</button>
						<button type="button" class="date-hotel btn btn-secondary"><i class="fa fa-hotel"></i>Datos de Hotel</button>
						<button type="button" class="date-pago btn btn-secondary"><i class="fa fa-money"></i>Datos Para el Pago de Comisiones</button>
			</div>


      	</section>
      	<div class="collapse" id="vtn-date-user">
				<div class="vtn-new-user">
				<form method="post" action="<?php echo _safe(HOST.'/admin/perfiles/');?>" autocomplete="off">
								<div class="form-group" data-toggle="tooltip" title="Tu nombre de usuario debe ser alfanum&eacute;rico. No puede contener espacios, acentos o caracteres especiales. Debe contener entre 3 y 50 caracteres. Recomendamos 20 o menos caracteres.">
									<label for="username" >Username (use no space)| Nombre de usuario (sin espacios o acentos) <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
									<input type="text" class="form-control" name="username" id="username" value="<?php echo $reg->getUsername();?>" placeholder="Nombre de usuario (sin espacios o acentos)" required minlength="3" maxlength="50" />
									<?php echo $reg->getUsernameError();?>
								</div><!-- /.form-group -->
								<div class="form-group">
									<label for="email">Email | Correo electr&oacute;nico <span class="required">*</span></label>
									<input type="email" class="form-control" name="email" id="email" value="<?php echo $reg->getEmail();?>" placeholder="Correo electr&oacute;nico" required />
									<?php echo $reg->getEmailError();?>
								</div><!-- /.form-group -->
								<div class="form-group" data-toggle="tooltip" title="La contrase&ntilde;a debe contener al menos 6 caracteres y debe ser distinta de tu nombre de usuario y tu correo electr&oacute;nico.">
									<label for="password">Password | Contrase&ntilde;a <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
									<input type="password" class="form-control" name="password" id="password" placeholder="Contrase&ntilde;a" required />
									<?php echo $reg->getPasswordError();?>
								</div><!-- /.form-group -->
								<div class="form-group">
									<label for="password-retype">Rewrite Password | Confirmar contrase&ntilde;a <span class="required">*</span></label>
									<input type="password" class="form-control" name="password-retype" id="password-retype" placeholder="Confirmar contrase&ntilde;a" required />
									<?php echo $reg->getRetypePasswordError();?>
								</div><!-- /.form-group -->
								<div class="form-group" id="user-search" data-toggle="tooltip" title="Encuentra tu referente al sitio por su nombre o nombre de usuario (username). Este campo es opcional.">
									<label for="user-search-input">Who invited you? (Concierge) | ¿Qui&eacute;n te invit&oacute;? (Concierge) <i class="fa fa-question-circle text-secondary"></i></label>
									<div class="search-placeholder" id="user-search-placeholder">
										<img src="<?php echo HOST;?>/assets/img/user_profile/default.jpg" class="meta-img img-rounded">
									</div>
									<input type="text" class="form-control typeahead" name="referral" id="user-search-input" value="<?php echo $reg->getReferral();?>" placeholder="Nombre de usuario del referente" autocomplete="off" />
									<?php echo $reg->getReferralError();?>
								</div><!-- /.form-group -->
								<div class="row">
									<div class="col-sm-4">
										<button type="submit" class="btn btn-primary pull-right" id="register-btn">Registrar</button>
									</div>
								</div>
							</form>
				</div>
			</div>
       	
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>

<?php echo $footer = $includes->get_admin_footer(); ?>


