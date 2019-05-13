<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';

$con = new assets\libs\connection();

if(!$id = filter_input(INPUT_GET, 'id')){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$product = new assets\libs\product_detail($con);

if(!$product->load_data($id)){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	if(isset($_POST['buy'])){
		$product->buy_item($_POST);
	}

	if(isset($_POST['emitircomprobante'])){
		$product->comprobante(1);
	}

	if(isset($_POST['emitircomprobante2'])){
		$product->comprobante(2);
	}
	if(isset($_POST['emitircomprobante3'])){
		$product->comprobante(3);
	}

	if(isset($_POST['recoger'])){
		$product->recoger();
	}

}

$includes = new assets\libs\includes($con);
$properties['title'] = $product->get_unsafe_name().' | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
<div class="main">
	<div class="main-inner">
		<div class="content">
			<div class="mt-80">
				<div class="document-title">
					<h1 class="text-binary">Gift | Regalo</h1>
				</div>
			</div>
			<div class="container">
		<?php 
				echo $con->get_notify();echo $product->get_notification();
			if(isset($_SESSION['notification']['ventaexitosa'])){
				unset($_SESSION['notification']['ventaexitosa']);?>
					

					<script>
						$(document).ready(function() {
								$('#modalventa').modal('show');
						});
					</script>
					<!-- Modal -->
					<div class="modal" id="modalventa" data-backdrop="false" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenteredLabel" aria-hidden="true">
					  <div class="modal-dialog modal-dialog-centered" role="document">
					    <div class="modal-content">
					      <div class="modal-header">
					        <h5 class="modal-title" id="exampleModalCenteredLabel">Pagar Ahora</h5>
					      </div>
					      <div class="modal-body">

							<div class="alert alert-icon alert-info" role="alert">
							El costo del envio es de: <?php echo $product->getPrecioEnvio();?>. Si deseas puedes pagarlo de una vez utilizando PayPal o Retiralo personalmente en nuestra tienda.
							</div>
					      </div>
					      <div class="modal-footer">

									
									<!-- <button type="button" class="pagopaypal btn btn-success" name="pagopaypal"><i class="fa fa-cc-paypal"></i>Pagar Ahora</button>  -->
									
												<script src="https://www.paypal.com/sdk/js?client-id=sb"></script>
												<script>paypal.Buttons().render('#btn-paypal')</script>
									<div class="row">
												
											<section class="col-lg-6" id="btn-paypal">
									<!-- 			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
												<input type="hidden" name="cmd" value="_s-xclick">
												<input type="hidden" name="hosted_button_id" value="4JYJNMUKMTT8W">
												<input type="image" src="https://www.paypalobjects.com/en_US/MX/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
												<img alt="" border="0" src="https://www.paypalobjects.com/es_XC/i/scr/pixel.gif" width="1" height="1">
												</form>
 -->
											</section>
												
											<section class="col-lg-6">
												
												<form method ="post" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>" >
												<button type ="submit" class="recoger-tienda btn btn-secondary" name="recoger" ><i class="fa fa-handshake-o"></i>Retirar Personalmente</button> 
												</form>
												
											</section>
									</div>

					      </div>
					    </div>
					  </div>
					</div>

			<?php } ?>

				<div class="row">
					<div class="col-sm-12">
						<div class="background-white p30 mb30">
							<div class="row mb30">
								<div class="col-sm-4 detail-gallery-preview">
									<a href="<?php echo $product->get_image();?>">
										<img class="img-thumbnail img-rounded" src="<?php echo $product->get_image();?>">
									</a>
								</div>
								<div class="col-sm-8">
									<div class="page-title">
										<h1 id="product-name"><?php echo $product->get_name();?></h1>
									</div>
									<div class="form-group">
										<label class="form-group"><?php echo $product->get_description();?></label>
									</div>
									<div class="row">
										<div class="col-sm-6">
											<div class="form-group">
												<label>Items left | Solo quedan <?php echo $product->get_available();?></label>
											</div>
										</div>
										<div class="col-sm-6">
											<div class="form-group">
												<label><?php echo $product->get_category();?></label>
											</div>
										</div>
									</div>
									<h3>

										<strong  class="text-info" id="product-price">Tp$ <?php echo $product->get_price();?></strong> <label>Travel Points</label>
									</h3>
									<?php echo $product->get_buy_button();?>
								</div>
							</div>
						</div>
						<a class="btn btn-default" href="<?php echo HOST;?>/tienda/">Back to Store | Volver a la tienda</a>
					</div>
				</div>
			</div><!-- /.container -->
		</div><!-- /.content -->
	</div><!-- /.main-inner -->
</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>