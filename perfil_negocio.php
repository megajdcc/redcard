<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!$url = filter_input(INPUT_GET, 'url')){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$url = _safe($url);

$business = new assets\libs\publicBusinessProfile($con);

if(!$business->load_data($url)){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$business->increase_views();

$includes = new assets\libs\includes($con);
$properties['title'] = $business->get_name_unsafe().' | Negocio en Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); 
?>
<div class="main">
	<div class="main-inner">
		<div class="content">
			<div class="mt-80 mb80">
				<div class="detail-banner" style="background-image: url(<?php echo HOST.'/assets/img/business/header/'.$business->get_photo();?>);">
					<div class="container">
						<div class="detail-banner-left">
							<div class="detail-banner-info">
								<div class="detail-label <?php echo $business->get_category_tag();?>"><?php echo $business->get_category();?></div>
								<div class="detail-verified"><?php echo $business->get_commission();?>%</div>
							</div><!-- /.detail-banner-info -->
							<h1 class="detail-title"><?php echo $business->get_name(); ?></h1>
							<h4 class="text-white"><?php echo $business->get_brief();?></h4>
							<div class="detail-banner-address">
								<i class="fa fa-map-o"></i> <?php echo $business->get_location();?>
							</div><!-- /.detail-banner-address -->
							<div class="detail-banner-rating">
								<?php echo $business->get_rating_stars($business->get_average_total_ratings()); ?>
							</div><!-- /.detail-banner-rating -->
							<?php echo $business->get_claims();?>
						</div><!-- /.detail-banner-left -->
					</div><!-- /.container -->
				</div><!-- /.detail-banner -->
			</div>
			<div class="container">
				<?php echo $con->get_notify();?>
				<div class="row detail-content">
					<div class="col-sm-7">
						<?php echo $business->get_gallery();?>
						<!-- <h2>Nos ubicamos en</h2> -->
						<div class="background-white p20">
						<?php echo $business->get_map();?>
						</div>
						<h2>Certificados de regalo</h2>
						<?php echo $business->get_certificates();?>
						<?php echo $business->get_video();?>
						<h2 id="reviews">Opiniones sobre este negocio</h2>
						<?php echo $business->get_reviews();?>
					</div><!-- /.col-sm-7 -->
					<div class="col-sm-5">
						<div class="background-white p20">
							<div class="detail-overview-hearts" id="recommends">
								<i class="fa fa-heart"></i> <strong><?php echo $business->get_recommended();?></strong> personas lo recomiendan
							</div>
							<div class="detail-overview-rating">
								<i class="fa fa-star"></i> <?php echo $business->get_rating_header();?>
							</div>
						</div>
						
						<h2>Sobre <span class="text-secondary"><?php echo $business->get_name();?></span></h2>
						<div class="background-white p20">
							<div class="detail-vcard">
								<?php echo $business->get_logo();?>
								<div class="detail-contact">
									<?php echo $business->get_email();?>
									<?php echo $business->get_phone();?>
									<?php echo $business->get_website();?>
									<?php echo $business->get_address();?>
								</div>
							</div>
							<?php echo $business->get_description();?>
						</div>
						<?php echo $business->get_menu();?>
						<?php echo $business->get_schedule();?>
						<h2>Amenidades</h2>
						<div class="background-white p20">
							<ul class="detail-amenities">
								<?php echo $business->get_amenities();?>
							</ul>
						</div>
						<?php echo $business->get_credit_cards();?>
					</div><!-- /.col-sm-5 -->
				</div><!-- /.row -->
			</div><!-- /.container -->
		</div><!-- /.content -->
	</div><!-- /.main-inner -->
</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>