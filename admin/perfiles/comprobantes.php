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
use admin\libs\Comprobantes;

$comprobante = new Comprobantes($con);


$search = filter_input(INPUT_GET, 'buscar');

if(isset($_REQUEST['aprobar'])){

		// $archivoo = null;
		// $nombrefile = 'recibo nro '.' - '.$_POST['idsolicitud'].' -';
		// if(!empty($_FILES['recibo']['name'])){
		
		// 	$file             = $_FILES['recibo'];
		// 	$nombrefile       .= $file['name'];
		// 	$tipofile         = $file['type'];
		// 	$ruta_provisional = $file["tmp_name"];
		// 	$size             = $file["size"];
		// 	$carpeta = $_SERVER['DOCUMENT_ROOT'].'/assets/recibos/';
		// 	$archivoo = $nombrefile;
		// 	$src = $carpeta.$nombrefile;
		// 	$result = move_uploaded_file($ruta_provisional, $src);
		// 	if($result){
		// 		 $comprobante->Aprobar($nombrefile, $_POST['idsolicitud']);
		// 	}
		// }
}

$includes = new admin\libs\includes($con);
$properties['title'] = 'comprobante | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>

<div class="row">
	<div class="col-sm-12">
		<?php echo $comprobante->getNotificacion();?>
		<div class="page-title">
			<h1>Solicitud de pago de comisiones 
			<form class="pull-right" method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>" target="_blank">
				
			</form>
			</h1>
		</div>
		<div class="background-white p20 mb50">
			
		<table  id="comprobantes" class="display" cellspacing="0" width="100%">
		<thead>
            <tr>
            	
            	
            	<th>#</th>
            	<th></th>
            	<th>Solicitante</th>
                <th>Perfil</th>
                <th>Monto Solicitud</th>
                <th>Mondo Pagado</th>
                <th>Tipo Pago</th>
                <th>Aprobada</th>
                <th>Fecha solicitud</th>
                <th></th>
               

            </tr>
        </thead>

        <tbody>
   			<?php echo $comprobante->ListarSolicitudes(); ?>
        </tbody>
    </table>




    <script>
    	$(document).ready(function(){



	   var t = $('#comprobantes').DataTable( {
		"paging"        :false,
		"scrollY"       :400,
		"scrollX"       :true,
		"scrollCollapse": true,
         "language": {
                        "lengthMenu": "Mostrar _MENU_ Registros por pagina",
                        "info": "",
                        "infoEmpty": "No se encontro ningúna solicitud",
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

<!-- Modal para adjudicar recibo de pago... -->
		<div class="modal fade " id="solicitud" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="true">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<form id="formulario-pago" action="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" method="post" accept-charset="utf-8" enctype="multipart/form-data">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">Pagar solicitud  de retiro #<span class="nrosolicitud"></span></h5>
						<h5 class="modal-title">Monto solicitado: <span class="monto"></span></h5>
						<h5 class="modal-title">De fecha <span class="fechasolicitud"></span></h5>
					</div>

					<script>
						$(document).ready(function() {
							$('.total').click(function(){

								$('#pago-parcial').css({
									display: 'none',
									
									
								});


							});

							$('.parcial').click(function(){


								$('#pago-parcial').css({
									display: 'block',
									
									
								});

							});
						});
					</script>

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
										.pagos{
											display: flex;
										}
										.pagos div:nth-child(1){
											margin-right: 1rem;
										}
									</style>

									<section class="row">
										
										<div class="col-lg-4 pagos">
											<div class="custom-control custom-radio custom-control-inline">
											<input class="total custom-control-input" type="radio" name="tpago" id="inlineRadio1" value="total" checked>
											<label class="custom-control-label" for="inlineRadio1">Total</label>
											</div>
											<div class="custom-control custom-radio custom-control-inline">
											<input class="parcial custom-control-input" type="radio" name="tpago" id="inlineRadio2" value="parcial">
											<label class="custom-control-label" for="inlineRadio2">Parcial</label>
											</div>
										</div>
										
										<div class="col-lg-8 btn-toolbar" role="toolbar" aria-label="Botones de pagos">
											
												
											

											<div class="btn-group btn-group-sm mr-2" role="group" aria-label="Datos solicitante">
											
												<button type="button" class="btn btn-secondary" data-toggle="collapse" data-target="#datossolicitante" aria-expanded="false" aria-controls="datoshotel">Datos del solicitante.</button>
												<button type="button" class="btn btn-secondary" data-toggle="collapse" data-target="#datoshotel" aria-expanded="false" aria-controls="datospago">Datos del hotel.</button>
												
												<button type="button" class="btn btn-secondary" data-toggle="collapse" data-target="#datospago" aria-expanded="false" aria-controls="datospago">Datos para el pago de comisión.</button>
											</div>
										</div>
									
									</section>

									<div class="row" id="pagos">
										<style>
											#pago-parcial{
												display: none;
											}
										</style>
										<div class="col-lg-6 col-sm-6">
											<div class="form-group" id="pago-parcial">
												<label for="monto">Monto a pagar:<span class="required"></span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-bank"></i></span>
													<input class="montopagar form-control" type="number" id="montopagar" min="0" name="montopagar" value="0" placeholder="Monto a pagar">
												</div>
											</div>
										</div>
										
									</div>

									<div class="row">
										<div class=" col-lg-6 custom-file" data-toggle="tooltip" title="Adjunte archivo de recibo de pago correspondiente">
													<label class="custom-file-label" for="recibo">Recibo de pago:</label>
													<input type="file" name="recibo" id="recibo" class="custom-file-input" placeholder='recibo de pago' required>

											</div>
										<div class="form-group col-lg-6">
											<label class="" for="mensaje">Env&iacute;e un mensaje a su contraparte.</label>
											<textarea class="form-control" name="mensajeregreso" id="mensaje" class="mensaje" placeholder="Mensaje a su contraparte."></textarea>
										</div>
									</div>

									<div class="botoneras btn-group" role="group" aria-label="example">
									
									</div>
									
									<section class="collapse" id="datoshotel">
										<div class="row">

								          <div class="col-lg-8 d-flex">
								        
									           <div class="form-group flex" >
										            <label for="business-name">Nombre del Hotel:<span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
										            <div class="input-hotel">
											             <div class="input-group">
												            <span class="input-group-addon"><i class="fa fa-hotel"></i></span>
												            <input class ="nombrehotel form-control" type="text" id="business-name" name="nombre" value="" placeholder="Nombre del hotel" readonly/>
											            </div>
										            </div>
									           </div>

									            <div class="form-group">
										            <label for="website">Sitio web del hotel</label>
										            <div class="input-group">
											             <span class="input-group-addon"><i class="fa fa-globe"></i></span>
											             <input class="sitioweb form-control" type="text" id="website" name="website" placeholder="Sitio web del hotel" value="" readonly>
										            </div>
									           </div>

									           <div class="form-group">
										            <label for="address">Direcci&oacute;n del hotel <span class="required"></span></label>
										            <div class="input-group">
											             <span class="input-group-addon"><i class="fa fa-map-o"></i></span>
											             <input class="direccion form-control" type="text" id="address" name="direccion" value="" placeholder="Direcci&oacute;n del hotel" readonly >
										            </div>
									           </div>
								            
								          </div>
								          
								          <div class="col-lg-4">
								           
								              	<div class="form-group">
									              <label for="country-select">Pa&iacute;s <span class="required"></span></label>
									              <input  type="text" class="pais form-control" value="" id="country-select" placeholder="Pais" name="pais" data-size="10" readonly>
									              </input>
								              	</div>

								               	<div class="form-group">
									                <label for="state-select">Estado <span class="required"></span></label>
									                <input  type="text" class="estado form-control" id="state-select" value="" placeholder="Estado" name="estado" data-size="10"  readonly>
									                </input>
								               	</div>

								               	<div class="form-group">
									                <label for="city-select">Ciudad <span class="required"></span></label>
									                <input type="text" class="ciudad form-control" id="city-select" value="" placeholder="Ciudad" name="ciudad"  data-size="10" readonly>
									                </input>
								                </div>
								          
								           </div>
								         
										</div>
									</section>

									<section class="collapse" id="datossolicitante">
										<div class="row">
											<div class="col-lg-6 col-sm-4">
												<div class="form-group">
													<label for="nombre">Nombre:</label>
													<input type="text" name="nombre" value="" class="nombre form-control" placeholder='No ha registrado ningun nombre' readonly>
												</div>
												<div class="form-group">
													<label for="apellido">Apellido:</label>
													<input type="text" name="nombre" value="" class="apellido form-control" placeholder='No ha registrado ningun nombre' readonly>
												</div>
												
											</div>

											<div class="col-lg-6 col-sm-4">
												 <div class="form-group">
													<label for="Telefono">Teléfono:</label>
													<div class="input-group">
														<span class="input-group-addon"><i class="fa fa-phone-square"></i></span>
														<input type="text" name="telefono" value="" class="telefonofijo form-control" placeholder='' readonly>
													</div>
													
												</div>

												 <div class="form-group">
													<label for="Telefono">Teléfono Movil:</label>
													<div class="input-group">
														<span class="input-group-addon"><i class="fa fa-phone-square"></i></span>
														<input type="text" name="telefono-movil" value="" class="telefonomovil form-control" placeholder='' readonly>
													</div>
													
												</div>
											</div>	
										</div>
										<div class="row">
											<div class="col-lg-12 col-sm-12">
												<div class="form-group">
													<label for="nombre">Mensaje del solicitante:</label>
													<textarea class="mensaje form-control" name="mensaje" readonly></textarea>
												</div>
											</div>
											
										</div>
									</section>

									<section class="collapse" id="datospago">
										<div class="row">
										
										<div class="col-lg-6 col-sm-4">
											<h5 class="page-title">Transferencia Bancaria</h5>
											<div class="form-group">
												<label for="nombre">Nombre del banco<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-bank"></i></span>
													<input class="banco form-control" type="text" id="nombre_banco" name="nombre_banco" value="" placeholder="Nombre del banco" readonly >
												</div>
											</div>

											<div class="form-group">
												<label for="cuenta">Cuenta<span class="required">*</span></label>
												<div class="input-group">
													<span class="cuenta input-group-addon"><i class="fa fa-wpforms"></i></span>
													<input class="cuenta form-control" type="text" id="cuenta" name="cuenta" value="" placeholder="Cuenta." readonly >
												</div>
											</div>

											<div class="form-group">
												<label for="clabe">Clabe<span class="required">*</span></label>
												<div class="input-group">
													<span class="clabe input-group-addon"><i class="fa fa-wpforms"></i></span>
													<input class="clabe form-control" type="text" id="clabe" name="clabe" value="" placeholder="Clabe" readonly >
												</div>
											</div>

											<div class="form-group">
												<label for="swift">Swift / Bic<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
													<input class="swift form-control" type="text" id="swift" name="swift" value="" placeholder="Swift" readonly >
												</div>
											</div>
										</div>

										<div class="col-lg-6 col-sm-4">
											<h5 class="page-title">Deposito a tarjeta</h5>
											<div class="form-group">
												<label for="nombre">Nombre del banco<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-bank"></i></span>
													<input class="bancotarjeta form-control" type="text" id="nombre_banco_targeta" name="nombre_banco_tarjeta" value="" placeholder="Nombre del banco" readonly >
												</div>
											</div>

											<div class="form-group">
												<label for="nombre">N&uacute;mero de tarjeta<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-cc"></i></span>
													<input class="numerotarjeta form-control" type="text" id="numero_targeta" name="numero_targeta" value="" placeholder="N&uacute;mero de Tarjeta" readonly>
												</div>
											</div>
								
										
											<h5 class="page-title">Transferencia PayPal</h5>
											<div class="form-group">
												<label for="nombre">Email de Paypal<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-cc-paypal"></i></span>
													<input class="emailpaypal form-control" type="email" id="email_paypal" name="email_paypal" value="" placeholder="Nombre del banco" readonly >
												</div>
											</div>
										</div>
									</div>
									</section>
									
								</section>
								
					</div>
						
					<div class="modal-footer">
						<input type="hidden" name="idsolicitud" id="nrosolicitud" >
						<button style="margin-left: auto;" type="submit"  data-path="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" name="cancelar" class="cancelar btn btn-danger"><i class="fa fa-cancel"></i>Cancelar Retiro</button>
						<button type="submit"  data-path="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" name="aprobar" class="actualizar btn btn-success">Aprobar</button>
						<button  type="button" class="cerrarmodal btn btn-secondary" >Cerrar</button>
					</div>]

					<input type="hidden" name="montototal">
				</form>

				</div>
			</div>
		</div>

		<script >

		
		$(document).ready(function() {


		$('#formulario-pago').bind('submit',function(e){

			e.preventDefault();

			$('.cancelar').attr('disableb','disabled');
			$('.actualizar').attr('disableb','disabled');
			$('.actualizar').text('Enviando por favor espere');

			$('.cerrarmodal').attr('disableb','disabled');




			var tpago = null;
			tpago = $('input[name="tpago"]:checked').val();
		

			var pago  = 0;

			if(tpago == 'parcial'){
				pago = $('input[name="montopagar"]').val();

				if(pago == 0){
					alert('El monto debe ser mayor que cero.');
					return false;
				}
			}else{
				pago = $('#montopagar').attr('max');

			}

			var formData = new FormData(document.getElementById('formulario-pago'));

			formData.append('f-pago',pago);
			formData.append('f-tpago',tpago);
			formData.append('f-montopedido',$('#montopagar').attr('max'));
			formData.append('solicitud','pagocomprobando');

			$.ajax({
				url: '/admin/controller/ControllerRegistro.php',
				type: 'POST',
				dataType: 'JSON',
				data: formData,
				cache:false,
				contentType:false,
				processData:false
			})
			.done(function(response) {

				alert(response.mensaje);


				 location.reload();
				
			})
			.fail(function() {
				$('.cancelar').removeAttr('disabled');
				$('.actualizar').removeAttr('disabled');
				$('.actualizar').text('Enviando por favor espere');
				$('.cerrarmodal').removeAttr('disabled');

			})
			.always(function() {
				console.log("complete");
			});
			





			return false;

		});



			$('.aprobar').click(function(){

				var solicitud = $(this).attr('data-id');
				var path = "/admin/libs/responsecontroller.php";
				var perfil = $(this).attr('data-perfil');
				var fecha = $(this).attr('data-fecha');
				var monto = $(this).attr('data-monto');

				$('#formulario-pago').append('<input type="hidden" name="perfil" value="'+perfil+'">');

				$('#montopagar').attr('max',$(this).attr('data-pago'));
				$('#montopagar').change();
				
				$.ajax({
					url: path,
					type: 'POST',
					dataType: 'json',
					data: {solicitud: solicitud,perfil:perfil},
				})
				.done(function(response) {

					var nombre        = response.solicitante[0].nombre;
					var apellido      = response.solicitante[0].apellido;
					var telefonofijo  = response.solicitante[0].telefonofijo;
					var telefonomovil = response.solicitante[0].telefonomovil;
					var mensaje = response.solicitante[0].mensaje;


					if(response.hotel[0].nombrehotel !=null){
						var nombrehotel = response.hotel[0].nombrehotel;
					}


					if(response.hotel[0].sitio_web !=null){
						var sitioweb    = response.hotel[0].sitio_web;
					}

					if(response.hotel[0].direccion !=null){
							var direccion   = response.hotel[0].direccion;
					}

					if(response.hotel[0].pais !=null){
						var pais        = response.hotel[0].pais;
					}

					if(response.hotel[0].estado !=null){
						var estado      = response.hotel[0].estado;
					}
				
					
					if(response.hotel[0].ciudad !=null){
						var ciudad      = response.hotel[0].ciudad;
					}
				
					
					

					if(response.hotel[0].banco !=null){
						var banco         = response.pagocomision[0].banco;

					}


					if(response.hotel[0].cuenta !=null){
						var cuenta        = response.pagocomision[0].cuenta;

					}
					
					if(response.hotel[0].clabe !=null){
							var clabe         = response.pagocomision[0].clabe;
					}
					

					if(response.hotel[0].swift !=null){
						var swift         = response.pagocomision[0].swift;
					}


					if(response.hotel[0].bancotarjeta !=null){
						var bancotarjeta  = response.pagocomision[0].bancotarjeta;
					}
					
					if(response.hotel[0].numerotarjeta !=null){
						var numerotarjeta = response.pagocomision[0].numerotarjeta;
					}

					if(response.hotel[0].email_paypal !=null){
						var emailpaypal   = response.pagocomision[0].email_paypal;
					}


					
					
					




						$('#nrosolicitud').attr('value',solicitud);
						$('.nombre').attr('value',nombre);
						$('.apellido').attr('value',apellido);
						$('.telefonomovil').attr('value',telefonomovil);
						$('.telefonofijo').attr('value',telefonofijo);
						$('.nrosolicitud').text(solicitud);
						$('.mensaje').text(mensaje);
						$('.fechasolicitud').text(fecha);
						$('.monto').text(monto);

						$('.nombrehotel').attr('value',nombrehotel);
						$('.sitioweb').attr('value',sitioweb);
						$('.direccion').attr('value',direccion);
						$('.pais').attr('value',pais);
						$('.estado').attr('value',estado);
						$('.ciudad').attr('value',ciudad);

						$('.banco').attr('value',banco);
						$('.cuenta').attr('value',cuenta);
						$('.clabe').attr('value',clabe);
						$('.swift').attr('value',swift);
						$('.bancotarjeta').attr('value',bancotarjeta);
						$('.numerotarjeta').attr('value',numerotarjeta);
						$('.emailpaypal').attr('value',emailpaypal);
						
						$('#solicitud').modal('show');
				})
				.fail(function() {
					console.log("error");
				})
				
    			
    		});

    		$('.cerrarmodal').click(function(){
    			$('#solicitud').modal('hide');
    		});
				
		});	
		</script>


<?php echo $footer = $includes->get_admin_footer(); ?>


