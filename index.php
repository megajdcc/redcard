<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';

$con = new assets\libs\connection();
$index = new assets\libs\index_load($con);

$includes = new assets\libs\includes($con);
$properties['title'] = 'Free gifts in Puerto Vallarta';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); 
?>
<div class="main">
	<div class="main-inner">
		<div class="content">
			<div class="mt-80">
				<div class="hero-video">
					<video preload="auto" autoplay="autoplay" muted="" loop="loop">
						<source src="<?php echo HOST.'/assets/img/travelpoints/sky.mp4';?>" type="video/mp4">
					</video>
					<div class="video-overlay"></div>
					<div class="container centro">
						<div class="row">
							<div class="col-sm-8 col-sm-offset-2 col-xs-12 col-xs-offset-0">
								<!-- <h1 class="logo-esmart">Club Regina<br><span class="logo-club">Red CARD</span></h1> -->
								<!-- <figure class="logotipo">	
								</figure> -->
								<p>Discover Vallarta's Top & gain Free Gifts</p>
								<form method="get" action="<?php echo htmlspecialchars(HOST.'/listados');?>">
									<div class="input-group">
										<input type="text" class="form-control" name="buscar" placeholder="Find what you want | Encuentra lo que quieres &hellip; ">
										<span class="input-group-btn">
											<button class="btn btn-primary" type="submit">Find | Encuentra</button>
										</span>
									</div><!-- /.input-group -->
								</form>
							</div><!-- /.col-* -->
						</div><!-- /.row -->
					</div><!-- /.container -->
				</div><!-- /.hero-video -->
			</div>
			<div class="container">
				<?php echo $con->get_notify();?>
				<div class="cards-simple-wrapper">
					<div class="page-header">
						<h2>Vallarta's TOPS   |   Lo más Destacado</h2>
					</div><!-- /.page-header -->
					<div class="row">
					<?php echo $index->get_businesses();?>
					</div><!-- /.row -->
				</div><!-- /.cards-simple-wrapper -->
				<div class="clearfix"></div>
				<div class="center">
					<a class="btn btn-secondary" href="<?php echo HOST;?>/listados?tipo=1">See All | Ver todos</a>
				</div>
				<div class="block background-white fullwidth mt80">
					<div class="cards-simple-wrapper">
						<div class="page-header">
							<h2>Gift Certificates | Certificados de Regalo</h2>
						</div><!-- /.page-header -->
						<div class="row">
						<?php echo $index->get_certificates();?>
						</div><!-- /.row -->
					</div><!-- /.cards-simple-wrapper -->
					<div class="clearfix"></div>
					<div class="center">
						<a class="btn btn-secondary" href="<?php echo HOST;?>/listados?tipo=2">See All | Ver todos</a>
					</div>
				</div>

				<div class="cards-simple-wrapper">
					<div class="page-header">
						<h2>Events & Activities | Actividades &amp; Eventos</h2>
					</div><!-- /.page-header -->
					<div class="posts">
						<div class="row">
							<?php echo $index->get_events();?>
						</div><!-- /.row -->
					</div><!-- /.posts -->
				</div><!-- /.cards-simple-wrapper -->
				<div class="clearfix"></div>
				<div class="center">
					<a class="btn btn-secondary" href="<?php echo HOST;?>/listados?tipo=3">See All | Ver todos</a>
				</div>

				<div class="block background-white fullwidth mt80">
					<div class="row">
						<?php echo $index->load_categories();?>
					</div><!-- /.row -->
				</div><!-- /.block -->

			</div><!-- /.container -->
		</div><!-- /.content -->
	</div><!-- /.main-inner -->
</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>