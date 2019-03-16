<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!$username = filter_input(INPUT_GET, 'username')){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$username = _safe($username);

$profile = new assets\libs\public_user_profile($con);

if(!$profile->load_data($username)){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$includes = new assets\libs\includes($con);
$properties['title'] = $profile->get_alias().' | Perfil de socio | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
	<div class="main">
		<div class="main-inner">
			<div class="container">
				<?php echo $con->get_notify();?>
				<div class="row">
					<div class="col-sm-12">
						<div class="content">
							<div class="row">
								<div class="col-xs-6 col-xs-offset-3 col-sm-offset-0 col-sm-4">
									<div class="widget">
										<div class="user-photo">
											<?php echo $profile->get_image();?>
										</div>
									</div>
								</div>
								<div class="col-xs-12 col-sm-8">
									<div class="p30">
										<div class="page-title">
											<h1><?php echo $profile->get_alias();?></h1>
											<h4><?php echo $profile->get_location();?></h4>
										</div><!-- /.page-title -->
										<div class="row">
											<div class="col-sm-12">
												<p><label>Nombre de usuario</label></p>
												<p class="text-primary"><?php echo $profile->get_username();?></p>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div><!-- /.content -->
					</div><!-- /.col-* -->
				</div><!-- /.row -->
			</div><!-- /.container -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>