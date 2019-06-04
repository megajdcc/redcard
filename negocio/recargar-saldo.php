<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!isset($_SESSION['user']) || !isset($_SESSION['business'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if($_SESSION['business']['id_rol'] != 4 && $_SESSION['business']['id_rol'] != 5){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$balance = new negocio\libs\business_balance($con);

$includes = new negocio\libs\includes($con);
$properties['title'] = 'Recargar saldo | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-6 col-sm-offset-3">
		<?php echo $balance->get_notification();?>
		<h1 class="page-title">Recargar saldo</h1>
		
		<div class="background-white p30 mb30">

			<div class="alert alert-info">
				<label>Estimado/a, solo debe ingresar n&uacute;meros, sin comas ni puntos.</label>
			</div>


			<div class="form-group row">
				<label class="for-label" for="pago">Monto a recargar:</label>
				<input placeholder="Monto a recargar." type="number" name="monto"  id="pago" class="form-control" min='100'>
			</div>

				<button class="btn btn-success pagarahora" id="pagarahora"><i class="fa fa-paypal"></i>Recargar Ahora.</button>		
			

		</div>
	</div>
</div>

<script>

	jQuery(document).ready(function($) {
		
				$('#pagarahora').click(function(){
					var monto = $('input[name="monto"]').val();

					var idnegocio = "<?php echo $includes->getIdnegocio();?>";


					$('#montopag').text('$ '+monto+' MXN');
				

					$('#btn-pagar').attr('data-precioenvio', monto);
					$('#btn-pagar').attr('data-idventa', idnegocio);

				$('#modalventa').modal('show');
		
		});
	});


</script>





<div class="modal" id="modalventa" data-backdrop="true" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenteredLabel" aria-hidden="true">
					  <div class="modal-dialog modal-dialog-sm" role="document">
					    <div class="modal-content">
					      <div class="modal-header">
					        <h5 class="modal-title" id="exampleModalCenteredLabel">Pagar Ahora</h5>
					      </div>
					      <div class="modal-body">

							<div class="alert alert-icon alert-info" role="alert">

							Pagar el monto de <span id="montopag"></span>	
							</div>

							<script>

								
									
									paypal.Buttons({

												createOrder:function(data, actions){
													
													return actions.order.create({
														purchase_units:[{
															amount:{
																currency:'MXN',
																value: $('#btn-pagar').attr('data-precioenvio')

															}
														}]
												});
												},
											onApprove: function(data, actions){

												$.ajax({
													url: '/admin/controller/ControllerRegistro.php',
													type: 'POST',
													dataType: 'JSON',
													data: {solicitud: 'recargarnegocio',id:$('#btn-pagar').attr('data-idventa'),monto:$('#btn-pagar').attr('data-precioenvio')},
												})
												.done(function(response) {

													if(response.peticion){
														 
														// 
														// 
														return actions.order.capture().then(function(detalles){
															 location.reload();

														
														});
														
													}else{

														alert(response.mensaje);
														
													}
													
												})
												.fail(function() {
													
												})
												.always(function() {
													console.log("complete");
												});
											
											}
											}).render('#btn-pagar');
								
								
							</script>

							<section class="content-pagar" id="btn-pagar">
								
							</section>
					      </div>


					      <div class="modal-footer">

					      	<button class="btn btn-secondary datos-venta cerrar-modal-pagar"><i class="fa fa-close"></i>Cerrar</button>

					      	<script>
					      		
					      		$('.cerrar-modal-pagar').click(function(event) {
					      			/* Act on the event */
					      			$('#modalventa').modal('hide');
					      		});
					      	</script>

					      </div>
					    </div>
					  </div>
					</div>






<?php echo $footer = $includes->get_footer(); ?>

