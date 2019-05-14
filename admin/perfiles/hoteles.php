<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
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
			<h1>Usuarios con perfil de Hotel
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


<?php echo $footer = $includes->get_admin_footer(); ?>


