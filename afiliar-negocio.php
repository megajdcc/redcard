<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	$login = new assets\libs\user_login($con);
	if($_SERVER["REQUEST_METHOD"] == "POST"){
		$login->set_data($_POST);
	}
}else{
	$affiliate = new assets\libs\affiliate_business($con);
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		if(isset($_POST['send'])){
			$affiliate->set_data($_POST, $_FILES);
		}
	}
}

$includes = new assets\libs\includes($con);
$properties['title'] = 'Afiliar negocio | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
	<div class="main">
		<div class="main-inner">
			<div class="container">
				<?php echo $con->get_notify();?>
<?php if(!isset($_SESSION['user'])){ ?>
				<div class="row">
					<div class="col-sm-7 col-md-8 mb50">
						<div class="page-title">
							<h1>¡Afilia tu negocio!</h1>
							<p>Env&iacute;anos una solicitud para publicar tu negocio en nuestro directorio.</p>
						</div>
						<p>Solo los socios pueden afiliar un negocio. <a href="<?php echo HOST;?>/hazte-socio">Hazte socio</a> o inicia sesi&oacute;n.</p>
					</div>
					<div class="col-sm-5 col-md-4">
					<?php echo $login->get_notification(); ?>
						<div class="page-title">
							<h2 class="mb0">Iniciar sesi&oacute;n</h2>
						</div><!-- /.page-title -->
						<?php echo $login->get_login_error(); ?>
						<form method="post" action="<?php echo _safe(HOST.'/login');?>">
							<div class="form-group">
								<label for="email">Correo electr&oacute;nico</label>
								<input type="text" class="form-control" name="email" id="email" value="<?php echo $login->get_email();?>" placeholder="Correo electr&oacute;nico" required />
								<?php echo $login->get_email_error();?>
							</div><!-- /.form-group -->
							<div class="form-group">
								<label for="password">Contrase&ntilde;a</label>
								<input type="password" class="form-control" name="password" id="password" placeholder="Contrase&ntilde;a" required />
								<?php echo $login->get_password_error();?>
							</div><!-- /.form-group -->
							<button type="submit" class="btn btn-primary pull-right">¡Entrar!</button>
						</form>
					</div>
				</div>
<?php }else{ ?>
				<div class="row">
					<div class="col-sm-12">
						<div class="content">
							<?php echo $affiliate->get_notification();?>
							<div class="page-title">
								<h1>¡Afilia tu negocio!</h1>
								<p>Env&iacute;anos una solicitud para publicar tu negocio en nuestro directorio.</p>
							</div>
							<form method="post" action="<?php echo _safe(HOST.'/afiliar-negocio');?>" enctype="multipart/form-data">
								<div class="background-white p30 mb50">
									<h3 class="page-title">Informaci&oacute;n de negocio</h3>
									<div class="row">

										<div class="col-lg-8">

											<div class="form-group" data-toggle="tooltip" title="Los socios de Travel Points pueden encontrar tu negocio por su nombre.">
												<label for="business-name">Nombre del negocio <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
												<input class="form-control" type="text" id="business-name" name="name" value="<?php echo $affiliate->get_name();?>" placeholder="Nombre del negocio" required />
												<?php echo $affiliate->get_name_error();?>
											</div><!-- /.form-group -->
											<div class="form-group" data-toggle="tooltip" title="Describe tu negocio de manera concisa. M&aacute;ximo 80 caracteres.">
												<label for="brief">Descripci&oacute;n corta <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
												<input class="form-control" type="text" id="brief" name="brief" value="<?php echo $affiliate->get_brief();?>" placeholder="Ejemplo: Restaurante de mariscos" maxlength="80" required />
												<?php echo $affiliate->get_brief_error();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										
										<div class="col-lg-4">
											<div class="row">
												<div class="col-sm-6 col-md-12 form-group">
													<label for="category">Categor&iacute;a del negocio <span class="required">*</span></label>
													<select class="form-control" id="category" name="category_id" title="Seleccionar categor&iacute;a" required>
														<?php echo $affiliate->get_categories();?>
													</select>
													<?php echo $affiliate->get_category_error();?>
												</div><!-- /.form-group -->
												<div class="col-sm-6 col-md-12 form-group" data-toggle="tooltip" title="Se te cobrar&aacute; este porcentaje por cada venta que registres en nuestro sistema. Una mayor comisi&oacute;n significa un mejor posicionamiento.">
													<label for="commission">Comisi&oacute;n <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
													<div class="input-group">
														<input class="form-control" type="number" id="commission" name="commission" value="<?php echo $affiliate->get_commission();?>" min="6" max="100" placeholder="Comisi&oacute;n %" required >
														<span class="input-group-addon"><i class="fa fa-percent"></i></span>
													</div><!-- /.input-group -->
													<?php echo $affiliate->get_commission_error();?>
												</div><!-- /.form-group -->
											</div>
										</div><!-- /.col-* -->

									</div><!-- /.row -->
									<div class="form-group" data-toggle="tooltip" title="Explica con m&aacute;s detalle acerca de tu negocio. Los socios de Travel Points tambi&eacute;n pueden encontrar tu negocio por su descripci&oacute;n. Puedes agregar palabras claves para facilitar la b&uacute;squeda.">
										<label for="description">Descripci&oacute;n del negocio <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
										<textarea class="form-control" id="description" placeholder="Descripci&oacute;n del negocio" name="description" rows="3" required ><?php echo $affiliate->get_description();?></textarea>
										<?php echo $affiliate->get_description_error();?>
									</div><!-- /.form-group -->
									<div class="form-group" data-toggle="tooltip" title="Este ser&aacute; el enlace directo al perfil de tu negocio.">
										<label for="url">Enlace deseado del perfil de negocio <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
										<div class="input-group">
											<span class="input-group-addon">www.travelpoints.com.mx/</span>
											<input class="form-control" type="text" id="url" name="url" value="<?php echo $affiliate->get_url();?>" placeholder="nombre-de-negocio" required >
										</div><!-- /.input-group -->
									</div><!-- /.form-group -->
									<?php echo $affiliate->get_url_error();?>
								</div><!-- /.box -->
								<div class="background-white p30 mb30">
									<h3 class="page-title">Informaci&oacute;n de contacto del negocio</h3>
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="email">Correo electr&oacute;nico del negocio <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-at"></i></span>
													<input class="form-control" type="email" id="email" name="email" value="<?php echo $affiliate->get_email();?>" placeholder="Correo electr&oacute;nico del negocio" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->get_email_error();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-6">
											<div class="form-group">
												<label for="phone">N&uacute;mero telef&oacute;nico del negocio <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-phone"></i></span>
													<input class="form-control" type="text" id="phone" name="phone" value="<?php echo $affiliate->get_phone();?>" placeholder="N&uacute;mero telef&oacute;nico del negocio" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->get_phone_error();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-sm-12">
											<div class="form-group" data-toggle="tooltip" title="Si no tienes sitio web, deja el espacio en blanco.">
												<label for="website">Sitio web del negocio <i class="fa fa-question-circle text-secondary"></i></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-globe"></i></span>
													<input class="form-control" type="text" id="website" name="website" value="<?php echo $affiliate->get_website();?>" placeholder="Sitio web del negocio">
												</div><!-- /.input-group -->
												<?php echo $affiliate->get_website_error();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div><!-- /.row -->
								</div><!-- /.box -->
								<div class="background-white p30 mb30">
									<h3 class="page-title">Ubicaci&oacute;n del negocio</h3>
									<div class="row">
										<div class="col-lg-8">
											<div class="form-group">
												<label for="address">Direcci&oacute;n del negocio <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-map-o"></i></span>
													<input class="form-control" type="text" id="address" name="address" value="<?php echo $affiliate->get_address();?>" placeholder="Direcci&oacute;n del negocio" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->get_address_error();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="postal-code">C&oacute;digo postal  del negocio <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
													<input class="form-control" type="text" id="postal-code" name="postal_code" value="<?php echo $affiliate->get_postal_code();?>" placeholder="C&oacute;digo postal del negocio" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->get_postal_code_error();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div><!-- /.row -->
									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<label for="country-select">Pa&iacute;s <span class="required">*</span></label>
												<select class="form-control" id="country-select" name="country_id" title="Selecciona un pa&iacute;s" data-size="10" data-live-search="true" required>
													<?php echo $affiliate->get_countries();?>
												</select>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="state-select">Estado <span class="required">*</span></label>
												<select class="form-control" id="state-select" name="state_id" title="Luego un estado" data-size="10" data-live-search="true" required>
													<?php echo $affiliate->get_states();?>
												</select>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="city-select">Ciudad <span class="required">*</span></label>
												<select class="form-control" id="city-select" name="city_id" title="Luego una ciudad" data-size="10" data-live-search="true" required>
													<?php echo $affiliate->get_cities();?>
												</select>
												<?php echo $affiliate->get_city_error();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div><!-- /.row -->
									<hr>
									<div class="form-group">
										<label for="map-canvas">Posici&oacute;n en el mapa <span class="required">*</span></label>
										<p>
											<ul>
												<li>Arrastra el marcador hacia la ubicaci&oacute;n de tu negocio.</li>
												<li>Puedes apoyarte escribiendo una ubicaci&oacute;n como una ciudad, municipio, colonia, etc. y seleccionar una de las opciones sugeridas.</li>
											</ul>
											Las coordenadas de la ubicaci&oacute;n se crean automaticamente.
										</p>
										<?php echo $affiliate->get_location_error();?>
									</div>
									<input class="controls form-control mb30" type="text" id="pac-input" placeholder="Escribe una ubicaci&oacute;n" />
									<div id="map-canvas"></div>
									<div class="row">
										<div class="col-sm-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
												<input class="form-control" type="text" id="input-latitude" name="latitude" value="<?php echo $affiliate->get_latitude();?>" placeholder="Latitud" required>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-sm-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
												<input class="form-control" type="text" id="input-longitude" name="longitude" value="<?php echo $affiliate->get_longitude();?>" placeholder="Longitud" required>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div><!-- /.row -->
								</div><!-- /.box -->
								<div class="background-white p30 mb30">
									<h3 class="page-title">Im&aacute;genes del negocio</h3>
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group" data-toggle="tooltip" title="Este logo aparecer&aacute; en tu perfil de negocio. Se recomienda una imagen cuadrada de m&iacute;nimo 300x300 pixeles y un peso inferior a 2MB. La imagen debe ser formato JPG o PNG.">
												<label for="logo">Adjunta el logo de tu negocio <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
												<input type="file" id="affiliate-logo" name="logo" required />
												<?php echo $affiliate->get_logo_error();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-6">
											<div class="form-group" data-toggle="tooltip" title="Esta ser&aacute; la imagen de portada de tu negocio. Se recomienda una imagen horizontal panor&aacute;mica y un peso inferior a 2 MB. La imagen debe ser formato JPG o PNG.">
												<label for="photo">Adjunta una fotograf&iacute;a de tu negocio <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
												<input type="file" id="affiliate-photo" name="photo" required />
												<?php echo $affiliate->get_photo_error();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div><!-- /.row -->
								</div><!-- /.box -->
								<div class="row">
									<div class="col-xs-6">
										<p>Los campos marcados son obligatorios <span class="required">*</span></p>
									</div>
									<div class="col-xs-6 right">
										<button class="btn btn-success btn-xl" type="submit" name="send"><i class="fa fa-paper-plane"></i>Enviar mi solicitud</button>
									</div>
								</div>
							</form>
						</div><!-- /.content -->
					</div><!-- /.col-* -->
				</div><!-- /.row -->
<?php } ?>
			</div><!-- /.container -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>