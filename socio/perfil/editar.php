<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	header('Location: '.HOST.'/login');
	die();
}
if(!isset($_SESSION['user']['id_usuario'])){
	header('Location: '.HOST.'/login');
	die();
}

$edit = new socio\libs\user_edit($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_FILES['image'])){
		$edit->set_image($_FILES);
	}
	if(isset($_POST['change_email'])){
		$edit->set_email($_POST);
	}
	if(isset($_POST['update_information'])){
		$edit->set_information($_POST);
	}
}

$includes = new assets\libs\includes($con);
$properties['title'] = 'Editar perfil | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
	<div class="main">
		<div class="main-inner">
			<div class="container">
				<?php echo $con->get_notify();?>
				<div class="row">
					<div class="col-sm-4 col-md-3">
						<div class="sidebar">
							<?php echo $includes->get_user_sidebar();?>
						</div><!-- /.sidebar -->
					</div><!-- /.col-* -->
					<div class="col-sm-8 col-md-9">
						<?php echo $edit->get_notification();?>
						<div class="content">
							<div class="row">
								<div class="col-xs-6 col-xs-offset-3 col-sm-offset-0 col-sm-4">
									<div class="widget">
										<div class="user-photo">
											<form method="post" action="<?php echo _safe(HOST.'/socio/perfil/editar');?>" enctype="multipart/form-data">
												<a href="#" id="profile-picture-click">
													<?php echo $edit->get_image();?>
													<span class="user-photo-action">Click para cambiar imagen</span>
												</a>
												<input class="sr-only" type="file" id="profile-photo-input" name="image" required/>
											</form>
										</div>
									</div>
								</div>
								<div class="col-xs-12 col-sm-8">
									<div class="p30">
										<div class="page-title">
											<h1><?php echo $edit->get_header_title();?></h1>
											<h4><?php echo $edit->get_location();?></h4>
										</div><!-- /.page-title -->
										<h2><span class="mr20">e$<?php echo $edit->get_eSmarties();?></span><a href="<?php echo HOST;?>/tienda/" class="btn btn-xs btn-primary">Ir a tienda</a><label class="btn-block">eSmartties</label></h2>
									</div>
								</div>
							</div>
							<div class="background-white p30 mb30">
								<div class="row">
									<div class="col-lg-6">
										<div class="form-group">
											<label for="username">Nombre de usuario</label>
											<input class="form-control" type="text" id="username" name="username" value="<?php echo $edit->get_username();?>" placeholder="Nombre de usuario" readonly>
										</div><!-- /.form-group -->
									</div><!-- /.col-* -->
									<div class="col-lg-6">
										<form method="post" action="<?php echo _safe(HOST.'/socio/perfil/editar');?>">
											<div class="form-group" data-toggle="tooltip" title="Deber&aacute;s validar tu nuevo correo electr&oacute;nico">
												<label for="email">Cambiar correo electr&oacute;nico <i class="fa fa-question-circle text-secondary"></i></label>
												<div class="input-group">
													<input class="form-control" type="email" id="email" name="email" value="<?php echo $edit->get_email();?>" placeholder="Correo electr&oacute;nico" required>
													<span class="input-group-btn">
														<button class="btn btn-success" type="submit" id="change-email" name="change_email"><i class="fa fa-pencil"></i></button>
													</span>
												</div><!-- /.input-group -->
												<?php echo $edit->get_email_error();?>
											</div><!-- /.form-group -->
										</form><!-- /form -->
									</div><!-- /.col-* -->
								</div><!-- /.row -->
							</div><!-- /.box -->
							<div class="background-white p30 mb30" id="info">
								<form method="post" action="<?php echo _safe(HOST.'/socio/perfil/editar');?>">
									<h3 class="page-title">Informaci&oacute;n Personal</h3>
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="name">Nombre(s)</label>
												<input class="form-control" type="text" id="name" name="name" value="<?php echo $edit->get_name();?>" placeholder="Nombre(s)"/>
												<?php echo $edit->get_name_error();?>
											</div><!-- /.form-group -->
										</div>
										<div class="col-lg-6">
											<div class="form-group">
												<label for="last-name">Apellido(s)</label>
												<input class="form-control" type="text" id="last-name" name="last_name" value="<?php echo $edit->get_last_name();?>" placeholder="Apellido(s)"/>
												<?php echo $edit->get_last_name_error();?>
											</div><!-- /.form-group -->
										</div>
									</div>
									<hr>
									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<label>Sexo</label>
												<div class="radio sideways">
													<?php echo $edit->get_gender();?>
												</div>
												<?php echo $edit->get_gender_error();?>
											</div><!-- /.form-group -->
										</div>
										<div class="col-lg-4">
											<div class="form-group">
												<label for="birthdate">Fecha de nacimiento</label>
												<div class="input-group date" id="user-birthdate">
													<input class="form-control" type="text" id="birthdate" name="birthdate" value="<?php echo $edit->get_birthdate();?>" placeholder="Fecha de nacimiento" />
													<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
												</div>
												<?php echo $edit->get_birthdate_error();?>
											</div>
										</div>
										<div class="col-lg-4">
											<div class="form-group">
												<label for="phone">N&uacute;mero Telef&oacute;nico</label>
												<input class="form-control" type="text" id="phone" name="phone" value="<?php echo $edit->get_phone();?>" placeholder="N&uacute;mero Telef&oacute;nico" />
												<?php echo $edit->get_phone_error();?>
											</div><!-- /.form-group -->
										</div>
									</div><!-- /.row -->
									<hr>
									<h3 class="page-title">Vivo en</h3>
									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<label for="country-select">Pa&iacute;s</label>
												<select class="form-control" id="country-select" name="country" title="Selecciona un pa&iacute;s" data-size="10" data-live-search="true">
													<?php echo $edit->get_country();?>
												</select>
											</div><!-- /.form-group -->
											<?php echo $edit->get_country_error();?>
										</div>
										<div class="col-lg-4">
											<div class="form-group">
												<label for="state-select">Estado</label>
												<select class="form-control" id="state-select" name="state" title="Luego un estado" data-size="10" data-live-search="true">
													<?php echo $edit->get_state();?>
												</select>
											</div><!-- /.form-group -->
											<?php echo $edit->get_state_error();?>
										</div>
										<div class="col-lg-4">
											<div class="form-group">
												<label for="city-select">Ciudad</label>
												<select class="form-control" id="city-select" name="city" title="Luego una ciudad" data-size="10" data-live-search="true">
													<?php echo $edit->get_city();?>
												</select>
												<?php echo $edit->get_city_error();?>
											</div><!-- /.form-group -->
										</div>
									</div>
									<hr>
									<div class="form-group center">
										<button class="btn btn-success" type="submit" name="update_information">Guardar cambios</button>
									</div>
								</form>
							</div><!-- /.background-white p20 mb30 -->
						</div><!-- /.content -->
					</div><!-- /.col-* -->
				</div><!-- /.row -->
			</div><!-- /.container -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>