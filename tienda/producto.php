<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
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
				</div><!-- /.document-title -->
			</div>
			<div class="container">
			<?php echo $con->get_notify(); echo $product->get_notification(); ?>
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
										<strong class="text-info" id="product-price">Tp$ <?php echo $product->get_price();?></strong> <label>Travel Points</label>
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