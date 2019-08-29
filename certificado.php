<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!$url = filter_input(INPUT_GET, 'url')){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$certificate = new assets\libs\certificate_detail($con);

if(!$certificate->load_data($url)){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$includes = new assets\libs\includes($con);
$properties['title'] = 'Certificado de regalo | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
<div class="main">
	<div class="main-inner">
		<div class="content">
			<div class="mt-80">
				<div class="document-title">
					<h1 class="text-binary">Certificado de Regalo</h1>
				</div><!-- /.document-title -->
			</div>
			<div class="container">
			<?php echo $con->get_notify();?>
				<div class="row">
					<div class="col-sm-12">
						<div class="background-white p30">
							<div class="row mb30">
								<div class="col-sm-4 detail-gallery-preview">
									<?php echo $certificate->get_image();?>
								</div>
								<div class="col-sm-8">
									<div class="page-title">
										<h1><?php echo $certificate->get_name();?></h1>
									</div>
									<div class="row">
										<div class="col-md-6 col-lg-3">
											<div class="form-group">
												<label><?php echo $certificate->get_business_link();?></label>
											</div>
										</div>
										<div class="col-md-6 col-lg-3">
											<div class="form-group">
												<label><i class="fa fa-map-marker text-primary mr5"></i><?php echo $certificate->get_location();?></label>
											</div>
										</div>
										<div class="col-md-6 col-lg-3">
											<div class="form-group">
												<label><?php echo $certificate->get_business_category();?></label>
											</div>
										</div>
										<div class="col-md-6 col-lg-3">
											<div class="form-group">
												<label>Valuado en: <?php echo $certificate->get_value();?></label>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<?php echo $certificate->get_status();?>
												<label>Disponibles <?php echo $certificate->get_available();?></label>
												
											</div>
										</div>
										<div class="col-lg-8">
											<div class="form-group">
												<div class="cert-date"><?php echo $certificate->get_dates();?></div>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label class="form-group"><?php echo $certificate->get_description();?></label>
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label>Condiciones</label>
												<p><?php echo $certificate->get_condition();?></p>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label>Restricciones</label>
												<p><?php echo $certificate->get_restriction();?></p>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div><!-- /.container -->
		</div><!-- /.content -->
	</div><!-- /.main-inner -->
</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>