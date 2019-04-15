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



$hoteles = new socio\libs\user_hotel($con);

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));

$rpp = 10;

$options = $hoteles->load_data($page, $rpp);

$paging = new assets\libraries\pagination\pagination($options['page'], $options['total']);
$paging->setRPP($rpp);

$includes = new assets\libs\includes($con);
$properties['title'] = 'Hoteles | eSmart Club';
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
							<?php echo $hoteles->get_notification();?>
							<div class="row">
								<div class="col-xs-6 col-xs-offset-3 col-sm-offset-0 col-sm-4">
									<div class="widget">
										<div class="user-photo">
											<?php //echo $hoteles->get_image();?>
										</div>
									</div>
								</div>
								<div class="col-xs-12 col-sm-8">
									<div class="p30">
										<div class="page-title">
											<h1><?php //echo $hoteles->get_header_title();?></h1>
											<h4><?php // echo $hoteles->get_location();?></h4>
										</div><!-- /.page-title -->
										<h2><span class="mr20">e$<?php //echo $hoteles->get_eSmarties();?></span><a href="<?php echo HOST;?>/tienda/" class="btn btn-xs btn-primary">Ir a tienda</a><label class="btn-block">eSmartties</label></h2>
									</div>
								</div>
							</div>
							<div class="background-white p30 mb30">
								<div class="page-title">
									<a href="<?php echo HOST;?>/socio/perfil/invitados" class="btn btn-secondary btn-xs pull-right">Ver mis invitados</a>
									<h4>Amigos invitados a eSmart Club: <?php // echo $profile->get_invited();?></h4>
								</div>
								<form method="post" action="<?php echo _safe(HOST.'/hotel/');?>">
									<div class="form-group" data-toggle="tooltip" title="Ingresa el correo electr&oacute;nico de tu amigo">
										<label for="email">Invitar a un amigo <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
										<input class="form-control" type="email" id="email" name="email" value="<?php //echo $profile->get_invite_email();?>" placeholder="Correo electr&oacute;nico" required>
										<?php// echo $profile->get_invite_email_error();?>
									</div>
									<div class="form-group" data-toggle="tooltip" title="Puedes enviarle un mensaje personalizado">
										<label for="message">Puedes mandar un mensaje personalizado <i class="fa fa-question-circle text-secondary"></i></label>
										<textarea class="form-control" id="message" name="message" placeholder="Mensaje personalizado"><?php //echo $profile->get_invite_message();?></textarea>
									</div>
									<button class="btn btn-success" type="submit" name="invite_friend"><i class="fa fa-paper-plane"></i> ¡Enviar invitaci&oacute;n!</button>
								</form>
							</div><!-- /.box -->
						</div><!-- /.content -->
					</div><!-- /.col-* -->
				</div><!-- /.row -->
			</div><!-- /.container -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>