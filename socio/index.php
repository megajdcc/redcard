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



$home = new socio\libs\user_home($con);

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));
$rpp = 20;
$options = $home->load_data($page, $rpp);

$paging = new assets\libraries\pagination\pagination($options['page'], $options['total']);
$paging->setRPP($rpp);
$paging->setCrumbs(10);
if(isset($_SESSION['perfil']) && !empty($_SESSION['perfil'])){
	switch ($_SESSION['perfil']) {
		case 'Hotel':
			
			header('location:'.HOST.'/Hotel/');
			
			die();
		break;

		case 'Franquiciatario':	
			
			header('location:'.HOST.'/Franquiciatario/');
			
			die();
		break;

		case 'Referidor':	
		
			header('location:'.HOST.'/Referidor/');
		
			die();
		break;
		
		default:
			# code...
			break;
	}
}
$includes = new assets\libs\includes($con);
$properties['title'] = 'Novedades de negocios | Travel Points';
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
							<div class="row">

								<div class="col-xs-6 col-xs-offset-3 col-sm-offset-0 col-sm-4">
									<div class="widget">
										<div class="user-photo">
											<?php echo $home->get_image();?>
										</div>
									</div>
								</div>

								<div class="col-xs-12 col-sm-8">
									<div class="p30">
										<div class="page-title">
											<h1><?php echo $home->get_header_title();?></h1>
											<h4><?php echo $home->get_location();?></h4>
										</div><!-- /.page-title -->

										<?php 

										if(!isset($_SESSION['perfil'])){?>
											<h2><span class="mr20">e$<?php echo $home->get_eSmarties();?></span><a href="<?php echo HOST;?>/tienda/" class="btn btn-xs btn-primary">Ir a tienda</a><label class="btn-block">eSmartties</label></h2>
										
										<?php }else{?>
											
										<?php } ?>
										<h5>Amigos invitados a Travel Points: <span class="mr20"><?php echo $home->get_invited();?></span>
											<a href="<?php echo HOST;?>/socio/perfil/" class="btn btn-success btn-xs">Invitar</a>
											<a href="<?php echo HOST;?>/socio/perfil/invitados" class="btn btn-secondary btn-xs">Ver</a>
										</h5>
									</div>
								</div>
							</div>
							
							<div class="page-title">
								<p>Novedades de los negocios que estoy siguiendo. <a href="'.HOST.'/listados">Encuentra m&aacute;s negocios</a> de tu inter&eacute;s.</p>
							</div>
							<?php echo $home->get_posts(); echo $paging->parse(); ?>
						</div><!-- /.content -->
					</div><!-- /.col-* -->
				</div><!-- /.row -->
			</div><!-- /.container -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>