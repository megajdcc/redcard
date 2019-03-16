<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!$url = filter_input(INPUT_GET, 'url')){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$url = _safe($url);

$business = new assets\libs\business_reviews($con);

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));
$rpp = 20;
$options = $business->load_data($url, $page, $rpp);

if(!$options){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$paging = new assets\libraries\pagination\pagination($options['page'], $options['total']);
$paging->setRPP($rpp);
$paging->setCrumbs(10);
$paging->setTarget('/alan-coffee-shop/opiniones/');
$paging->setKey('');

$includes = new assets\libs\includes($con);
$properties['title'] = 'Opiniones | '.$business->get_raw_name().' | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
<div class="main">
	<div class="main-inner">
		<div class="content">
			<div class="mt-80 mb80">
				<div class="detail-banner" style="background-image: url(<?php echo $business->get_header(); ?>">
					<div class="container">
						<div class="detail-banner-left">
							<div class="detail-banner-info">
								<div class="detail-label <?php echo $business->get_category_tag();?>"><?php echo $business->get_category();?></div>
								<div class="detail-verified"><?php echo $business->get_commission();?>%</div>
							</div><!-- /.detail-banner-info -->
							<h1 class="detail-title"><?php echo $business->get_name(); ?></h1>
							<div class="detail-banner-address">
								<i class="fa fa-map-o"></i> <?php echo $business->get_location();?>
							</div><!-- /.detail-banner-address -->
							<div class="detail-banner-rating">
								<?php echo $business->get_score_stars($business->get_average_score()); ?>
							</div><!-- /.detail-banner-rating -->
							<?php echo $business->get_buttons();?>
						</div><!-- /.detail-banner-left -->
					</div><!-- /.container -->
				</div><!-- /.detail-banner -->
			</div>
			<div class="container">
				<?php echo $con->get_notify();?>
				<div class="row detail-content">
					<div class="col-sm-9">
						<?php echo $business->get_reviews(); echo $paging->parse(); ?>
					</div>
					<div class="col-sm-3">
						<?php echo $business->get_menu(); ?>
					</div>
				</div><!-- /.row -->
			</div><!-- /.container -->
		</div><!-- /.content -->
	</div><!-- /.main-inner -->
</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>