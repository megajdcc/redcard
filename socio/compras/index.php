<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	header('Location: '.HOST.'/login');
	die();
}
if(!isset($_SESSION['user']['id_usuario'])){
	header('Location: '.HOST.'/login');
	die();
}

$expenses = new socio\libs\user_purchases($con);

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));
$rpp = 10;
$options = $expenses->load_data($page, $rpp);

$paging = new assets\libraries\pagination\pagination($options['page'], $options['total']);
$paging->setRPP($rpp);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	// $expenses->submit_review($_POST);
}

$includes = new assets\libs\includes($con);
$properties['title'] = 'Mis compras | Travel Points';
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
							<?php echo $expenses->get_notification();?>
							<div class="page-title"><?php echo $expenses->get_count();?></div>
							<?php echo $expenses->get_purchases(); echo $paging->parse();?>
						</div><!-- /.content -->
					</div><!-- /.col-* -->
				</div><!-- /.row -->
			</div><!-- /.container -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->


<script>
	$(document).ready(function() {

		if($('.btn-pagar').length){
				$('.btn-pagar').click(function(){
					var precioenvio = $(this).attr('data-precioenvio');
					var idventa = $(this).attr('data-idventa');

					$('#btn-pagar').attr('data-precioenvio', precioenvio);
					$('#btn-pagar').attr('data-idventa', idventa);

				$('#modalventa').modal('show');
		
		});
		}
	
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
							



							Paga Ahora el precio del envio 
							</div>

							<script>

								
									
									paypal.Buttons({

												createOrder:function(data, actions){
													
													return actions.order.create({
														purchase_units:[{
															amount:{
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
													data: {solicitud: 'productopagado',id:$('#btn-pagar').attr('data-idventa')},
												})
												.done(function(response) {

													if(response.peticion){
														location.reload();
													}else{
														alert('Tu pago ha sido procesado exitosamente, pero no se pudo guardar en nuestra, si es tan amable notficalo a nuestro correo: soporte@infochannel.si');
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
<?php echo $footer = $includes->get_main_footer(); ?>