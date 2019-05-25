<?php 

require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';
$con = new assets\libs\connection();

use Referidor\models\Includes;
use Referidor\models\Comprobantes;
use Referidor\models\Dashboard;
use Referidor\models\Home;

if(!isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

if(!isset($_SESSION['perfil'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$Dashboard = new Dashboard($con);
$includes = new Includes($con);
$Comprobante = new Comprobantes($con);
$home = new Home($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){

	if(isset($_POST['solicitud']) && $_POST['solicitud'] == 'retirocomision'){
			
			$Comprobante->procesarretiro($_POST);
	}
}

$properties['title'] = 'Comprobantes | Travel Points';
$properties['description'] = '';

echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>

<?php echo $con->get_notify();?>
<div class="row">
		<?php echo $Comprobante->getNotificacion();?>
			<div class="background-white p20 mb30">
				<div class="page-title">
					<h1>Comprobantes de pago</h1>
				</div>


				<div class="row">
					<section class="col-xs-12 table-comprobantes">
						<table  id="comprobante" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th>#</th>
							<th>Fecha de solicitud</th>
							<th>Fecha de aprobación</th>
							<th>Aprobada por</th>
							<th>Aprobado</th>
							<th>Monto a retirar</th>	
							<th></th>
						</tr>
					</thead>
					
					<tbody>
						
						<?php echo $Comprobante->getComprobantes();?>
					</tbody>
				</table>
					</section>
				</div>
			
				
				<div class="row contenedor-solicitud">
		
					<?php 
				
					if($home->getBalance() > 0){ ?>
							<button type="button" class="btn btn-outline-info col-xs-12"  data-toggle="collapse" href="#collapse" role="button" aria-expanded="false" aria-controls="collapse">
							Generar Solicitud
							</button>

				<section class="collapse" id="collapse">
					
								<form action="<?php echo _safe($_SERVER['REQUEST_URI']);?>"  method="POST"  class="was-validated" accept-charset="utf-8">
								<section class=" content-solicitud">
										<div class="row">
											<div class="titulo">
												<h3>Generar Solicitud</h3>
											</div>
										</div>
									
								
									<div class="row">

										<!-- MONTO A RETIRAR>>> -->
											<section class="col-md-6">
												<div class="form-group">
														<label for="monto">Monto a retirar <span class="required">*</span></label>
														<div class="input-group">
												
														<input id="slide" type="text" id="comision" data-slider-id="ex1Slider" data-slider-min="0" data-slider-max="<?php echo $home->getBalance();?>" data-slider-step="1" data-slider-value="<?php echo $home->getBalance();?>">
														
																																
														</div>
												</div>
										</section>

										<section class="col-md-2">
											<div class="form-group">
												<label for="monto">Monto elegido <span class="required">*</span></label>
														<div class="input-group">
																<span class="form" id="val-slider"><?php echo "$ ".$home->getBalance()."MXN";?></span>
														</div>
											</div>
										
										</section>

						

										<?php $balance =  number_format((float)$home->getBalance(),2,',','.')?>
										<!-- Monto  actual del perfil -->
										<section class="col-md-4">
													<div class="form-group">
														<label for="monto">Comisión acumulada<span class="required">*</span></label>
														<div class="input-group">
															<span class="input-group-addon"><i class="fa fa-dollar"></i></span>
															<input class="form-control" type="text" id="monto" name="monto-balance" placeholder="Monto a retirar"  value="<?php echo $balance.'MXN'; ?>" pattern="[0-9]+" readonly>
														
														</div>
													</div>		
										</section>
									</div>


								<!-- MENSAJE DEL SOLICITANTE AL ADMINISTRADORR -->
									<div class="row">
									
											<div class="col-md-12">
													<div class="form-group" data-toggle="tooltip" title="Escriba un mensaje a la contraparte, si tiene algun requerimiento , donde desea que le hagan el pago de la comisión...">
														<label form="mensaje">Mensaje</label>
														<textarea name="mensaje" class="form-control" id="mensaje" placeholder="Mensaje"></textarea> 												
 												</div>
											</div>
									</div>
		

							<div class="row">
								<footer class="footerbtn col-md-12">
										<button type="button" data-comision="" name="enviarsoolicitud" data-path="<?php echo _safe($_SERVER['REQUEST_URI']);?>" class="enviar btn btn-secondary"><i class="fa fa-check"></i> Enviar Solicitud</button>
								</footer>
							</div>
								
								</section>
								</form>
				</section>
			<?php } ?>
				</div>
			
			</div>

<script>


	$(document).ready(function() {
								var slider = new Slider('#slide');
								var valorslider = slider.getValue();
								slider.on("slide", function(sliderValue){
								valorslider = sliderValue;
								document.getElementById('val-slider').textContent = "     $ "+sliderValue + " MXN";
								$('.enviar').attr('data-comision',sliderValue);
								});
															
			$('.enviar').click(function(){
						var montoretiro = slider.getValue();
						var mensaje = document.getElementById('mensaje').value;
						var path = $(this).attr('data-path');
						$.ajax({
							url: path,
							type: 'POST',
							data: {solicitud:'retirocomision',monto:montoretiro,mensaje:mensaje},
						})
						.done(function() {
							 location.reload();
						})
						.fail(function() {
							console.log("error");
						})
			});

			
    });

		 var t = $('#comprobante').DataTable( {
					"paging"        :         false,
					"scrollY"       :        "400px",
					"scrollCollapse": true,
			         "language": {
			                        "lengthMenu": "Mostar _MENU_ registros por pagina",
			                        "info": "",
			                        "infoEmpty": "No se encontro ningún comprobante",
			                        "infoFiltered": "(filtrada de _MAX_ registros)",
			                        "search": "Buscar: ",
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
			        "order": [[ 0, 'asc' ]]
			    } );
</script>
</div>
<?php echo $footer = $includes->get_admin_footer(); ?>