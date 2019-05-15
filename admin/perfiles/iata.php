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
use admin\libs\Iata;

$iata = new Iata($con);

if(isset($_REQUEST['registrar'])){

	$iata->registrar($_POST);

}

if(isset($_REQUEST['eliminar'])){
	$iata->eliminar($_POST);
}

$includes = new admin\libs\includes($con);
$properties['title'] = 'Codigo Iata | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>

<div class="row">
	<div class="background-white p20 mb50 col-sm-12">
		<?php  echo $iata->getNotificacion();?>
		<div class="page-title">
			<h1>Codigo Iata Aeroportuaria
		
			</h1>
		</div>
		<div class="background-white p20 mb50">
			<script>
				function ValidarIata(){

					if(elim){
							var result =confirm('Esta Seguro de eliminar este Codigo IATA, Podria afectar a otros hoteles que tengan asociado este codigo...');
							if(result){
								return true;
							}else{
								return false;
							}
					}

					

				
				}
			</script>
				<form class="pull-right" method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>" onsubmit="return ValidarIata()">
					<table  id="iatas" class="display" cellspacing="0" width="98%">
					<thead>
			            <tr>
			            	
			            	<th>#</th>
			            	<th>Iata</th>
			            	<th>Aeropuerto</th>
			                <th>Ciudad</th>
			                <th>Estado</th>
			                <th>Pais</th>
			                <th>Cant Hoteles Cercanos</th>
			                <th></th>
			               
			            </tr>
			        </thead>

			        <tbody>
			   			<?php echo $iata->getIata(); ?>
			        </tbody>
			    </table>
				</form>



	    <script>
	    	$(document).ready(function(){



				   var t = $('#iatas').DataTable( {
					"paging"        :true,
					"scrollY"       :true,
					"scrollX"       :true,
					"scrollCollapse": true,
			         "language": {
			                        "lengthMenu": "Mostrar _MENU_ Registros por pagina",
			                        "info": "",
			                        "infoEmpty": "No se encontro ningún codigo iata",
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
			        "order": [[ 0, 'asc' ]]
			    });

			    	});
	    </script>
		</div>
	</div>
</div>

<div class="background-white p20 mb50">
	<div class="row">
		<section class="col-sm-12">
			<div class="btn-group" role="group" aria-label="Buttongroup">
				<button type="button" class="nuevo btn btn-secondary">Nuevo Iata</button>
			</div>
		</section>
	</div>
</div>

<!-- Modal para adjudicar recibo de pago... -->
		<div class="modal fade " id="iata" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="true">
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
										<div class="row">

								          <div class="col-lg-6 d-flex">
								        
									           <div class="form-group flex" data-toggle="tooltip" title="Insertar codigo Iata." data-placement="bottom">

										            <label for="business-name">Codigo:<span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
										            <div class="input-hotel">
											             <div class="input-group">
												            <span class="input-group-addon"><i class="fa fa-code"></i></span>
												            <input class ="nombrehotel form-control" type="text" id="business-name" name="codigo" value="" placeholder="Codigo iata" required/>
											            </div>
										            </div>
									           </div>

									            <div class="form-group flex" data-toggle="tooltip" title="Nombre con el que quedará registrado su ciudad o territorio." data-placement="bottom" >
										            <label for="business-name">Aeropuerto:<span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
										            <div class="input-hotel">
											             <div class="input-group">
												            <span class="input-group-addon"><i class="fa fa-fighter-jet"></i></span>
												            <input class ="nombrehotel form-control" type="text" id="business-name" name="aeropuerto" value="" placeholder="aeropuerto" required/>
											            </div>
										            </div>
									           </div>

									     
								            
								          </div>

								           <div class="col-lg-6 d-flex">
								        
									
										
											<div class="form-group">
												<label for="country-select">Pa&iacute;s <span class="required">*</span></label>
												<select class="form-control" id="country-select" name="pais" title="Selecciona un pa&iacute;s" data-size="10" data-live-search="true" required>
													<?php echo $iata->get_countries();?>
												</select>
											</div><!-- /.form-group -->
										
										
											<div class="form-group">
												<label for="state-select">Estado <span class="required">*</span></label>
												<select class="form-control" id="state-select" name="estado" title="Luego un estado" data-size="10" data-live-search="true" required>
													<?php echo $iata->get_states();?>
												</select>
											</div><!-- /.form-group -->
										
										
											<div class="form-group">
												<label for="city-select">Ciudad <span class="required"></span></label>
												<select class="form-control" id="city-select" name="ciudad" title="Luego una ciudad" data-size="10" data-live-search="true">
													<?php echo $iata->get_cities();?>
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
						
						<button style="margin-left: auto;" type="submit"  data-path="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" name="registrar" class="actualizar btn btn-success">Registrar</button>
						<button  type="button" class="cerrarmodal btn btn-secondary" >Cerrar</button>
					</div>
				</form>

				</div>
			</div>
		</div>

		<script >
			$(document).ready(function() {

				$('.nuevo').click(function(event) {
					$('#iata').modal('show');
				});	

				$('.cerrarmodal').click(function(){
					$('#iata').modal('hide');
				});
			});
		</script>


<?php echo $footer = $includes->get_admin_footer(); ?>


