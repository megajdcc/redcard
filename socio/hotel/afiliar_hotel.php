<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Info Channel
$con = new assets\libs\connection();

use assets\libs\AfiliarHotel ; 
if(!isset($_SESSION['user'])){
	header('Location: '.HOST.'/login');
	die();
}
if(!isset($_SESSION['user']['id_usuario'])){
	header('Location: '.HOST.'/login');
	die();
}

if($_SESSION['user']['id_rol']==8) {
	header('Location: '.HOST.'/socio/hoteles/siguiendo');
	die();
}
$Afiliar = new AfiliarHotel($con);
if($_SERVER['REQUEST_METHOD'] == 'POST'){
	if(isset($_POST['send'])){
		$Afiliar->set_data($_POST, $_FILES);
	}
}

$includes = new assets\libs\includes($con);
$properties['title'] = 'Afiliar hotel | Travel Points';
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
							<?php echo $Afiliar->get_notification();?>
							<div class="page-title">
								<h1>¡Afilia tu hotel!</h1>
								<p>Env&iacute;anos una solicitud para publicar tu hotel en nuestro directorio.</p>
							</div>
							<form method="post" action="<?php echo _safe(HOST.'/socio/hoteles/afiliar_hotel');?>" enctype="multipart/form-data">
								<div class="background-white p30 mb50">
									<h3 class="page-title">Informaci&oacute;n del hotel</h3>
									<div class="row">
										<div class="col-lg-8">
											<div class="form-group" data-toggle="tooltip" title="Los socios de Travel Points pueden encontrar tu hotel por su nombre.">
												<label for="business-name">Nombre del hotel <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
												<input class="form-control" type="text" id="business-name" name="name" value="<?php echo $Afiliar->get_name();?>" placeholder="Nombre del hotel" required />
												<?php echo $Afiliar->get_name_error();?>
											</div><!-- /.form-group -->
											<div class="form-group" data-toggle="tooltip" title="Describe tu hotel de manera concisa. M&aacute;ximo 80 caracteres.">
												<label for="brief">Descripci&oacute;n corta <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
												<input class="form-control" type="text" id="brief" name="brief" value="<?php echo $Afiliar->get_brief();?>" placeholder="Ejemplo: Restaurante de mariscos" maxlength="80" required />
												<?php echo $Afiliar->get_brief_error();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="row">
											
												<div class="col-sm-6 col-md-12 form-group" data-toggle="tooltip" title="Se te cobrar&aacute; este porcentaje por cada venta que registres en nuestro sistema. Una mayor comisi&oacute;n significa un mejor posicionamiento.">
													<label for="commission">Comisi&oacute;n <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
													<div class="input-group">
														<input class="form-control" type="number" id="commission" name="commission" value="<?php echo $Afiliar->get_commission();?>" min="6" max="100" placeholder="Comisi&oacute;n %" required >
														<span class="input-group-addon"><i class="fa fa-percent"></i></span>
													</div><!-- /.input-group -->
													<?php echo $Afiliar->get_commission_error();?>
												</div><!-- /.form-group -->
											</div>
										</div><!-- /.col-* -->
									</div><!-- /.row -->
									<div class="form-group" data-toggle="tooltip" title="Explica con m&aacute;s detalle acerca de tu hotel. Los socios de Travel Points tambi&eacute;n pueden encontrar tu hotel por su descripci&oacute;n. Puedes agregar palabras claves para facilitar la b&uacute;squeda.">
										<label for="description">Descripci&oacute;n del hotel <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
										<textarea class="form-control" id="description" placeholder="Descripci&oacute;n del hotel" name="description" rows="3" required ><?php echo $Afiliar->get_description();?></textarea>
										<?php echo $Afiliar->get_description_error();?>
									</div><!-- /.form-group -->
									<div class="form-group" data-toggle="tooltip" title="Este ser&aacute; el enlace directo al perfil de tu hotel.">
										<label for="url">Enlace deseado del perfil de hotel <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
										<div class="input-group">
											<span class="input-group-addon">www.redcard.com.mx/</span>
											<input class="form-control" type="text" id="url" name="url" value="<?php echo $Afiliar->get_url();?>" placeholder="nombre-de-hotel" required >
										</div><!-- /.input-group -->
									</div><!-- /.form-group -->
									<?php echo $Afiliar->get_url_error();?>
								</div><!-- /.box -->
								<div class="background-white p30 mb30">
									<h3 class="page-title">Informaci&oacute;n de contacto del hotel</h3>
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="email">Correo electr&oacute;nico del hotel <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-at"></i></span>
													<input class="form-control" type="email" id="email" name="email" value="<?php echo $Afiliar->get_email();?>" placeholder="Correo electr&oacute;nico del hotel" required >
												</div><!-- /.input-group -->
												<?php echo $Afiliar->get_email_error();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-6">
											<div class="form-group">
												<label for="phone">N&uacute;mero telef&oacute;nico del hotel <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-phone"></i></span>
													<input class="form-control" type="text" id="phone" name="phone" value="<?php echo $Afiliar->get_phone();?>" placeholder="N&uacute;mero telef&oacute;nico del hotel" required >
												</div><!-- /.input-group -->
												<?php echo $Afiliar->get_phone_error();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-sm-12">
											<div class="form-group" data-toggle="tooltip" title="Si no tienes sitio web, deja el espacio en blanco.">
												<label for="website">Sitio web del hotel <i class="fa fa-question-circle text-secondary"></i></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-globe"></i></span>
													<input class="form-control" type="text" id="website" name="website" value="<?php echo $Afiliar->get_website();?>" placeholder="Sitio web del hotel">
												</div><!-- /.input-group -->
												<?php echo $Afiliar->get_website_error();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div><!-- /.row -->
								</div><!-- /.box -->
								<div class="background-white p30 mb30">
									<h3 class="page-title">Ubicaci&oacute;n del hotel</h3>
									<div class="row">
										<div class="col-lg-8">
											<div class="form-group">
												<label for="address">Direcci&oacute;n del hotel <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-map-o"></i></span>
													<input class="form-control" type="text" id="address" name="address" value="<?php echo $Afiliar->get_address();?>" placeholder="Direcci&oacute;n del hotel" required >
												</div><!-- /.input-group -->
												<?php echo $Afiliar->get_address_error();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="postal-code">C&oacute;digo postal  del hotel <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
													<input class="form-control" type="text" id="postal-code" name="postal_code" value="<?php echo $Afiliar->get_postal_code();?>" placeholder="C&oacute;digo postal del hotel" required >
												</div><!-- /.input-group -->
												<?php echo $Afiliar->get_postal_code_error();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div><!-- /.row -->
									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<label for="country-select">Pa&iacute;s <span class="required">*</span></label>
												<select class="form-control" id="country-select" name="country_id" title="Selecciona un pa&iacute;s" data-size="10" data-live-search="true" required>
													<?php echo $Afiliar->get_countries();?>
												</select>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="state-select">Estado <span class="required">*</span></label>
												<select class="form-control" id="state-select" name="state_id" title="Luego un estado" data-size="10" data-live-search="true" required>
													<?php echo $Afiliar->get_states();?>
												</select>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="city-select">Ciudad <span class="required">*</span></label>
												<select class="form-control" id="city-select" name="city_id" title="Luego una ciudad" data-size="10" data-live-search="true" required>
													<?php echo $Afiliar->get_cities();?>
												</select>
												<?php echo $Afiliar->get_city_error();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div><!-- /.row -->
									<hr>
									<div class="form-group">
										<label for="map-canvas">Posici&oacute;n en el mapa <span class="required">*</span></label>
										<p>
											<ul>
												<li>Arrastra el marcador hacia la ubicaci&oacute;n de tu hotel.</li>
												<li>Puedes apoyarte escribiendo una ubicaci&oacute;n como una ciudad, municipio, colonia, etc. y seleccionar una de las opciones sugeridas.</li>
											</ul>
											Las coordenadas de la ubicaci&oacute;n se crean automaticamente.
										</p>
										<?php echo $Afiliar->get_location_error();?>
									</div>
									<input class="controls form-control mb30" type="text" id="pac-input" placeholder="Escribe una ubicaci&oacute;n" />
									<div id="map-canvas"></div>
									<div class="row">
										<div class="col-sm-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
												<input class="form-control" type="text" id="input-latitude" name="latitude" value="<?php echo $Afiliar->get_latitude();?>" placeholder="Latitud" required>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-sm-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
												<input class="form-control" type="text" id="input-longitude" name="longitude" value="<?php echo $Afiliar->get_longitude();?>" placeholder="Longitud" required>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div><!-- /.row -->
								</div><!-- /.box -->
								<div class="background-white p30 mb30">
									<h3 class="page-title">Im&aacute;genes del hotel</h3>
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group" data-toggle="tooltip" title="Este logo aparecer&aacute; en tu perfil de hotel. Se recomienda una imagen cuadrada de m&iacute;nimo 300x300 pixeles y un peso inferior a 2MB. La imagen debe ser formato JPG o PNG.">
												<label for="logo">Adjunta el logo de tu hotel <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
												<input type="file" id="Afiliar-logo" name="logo" required />
												<?php echo $Afiliar->get_logo_error();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-6">
											<div class="form-group" data-toggle="tooltip" title="Esta ser&aacute; la imagen de portada de tu hotel. Se recomienda una imagen horizontal panor&aacute;mica y un peso inferior a 2 MB. La imagen debe ser formato JPG o PNG.">
												<label for="photo">Adjunta una fotograf&iacute;a de tu hotel <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
												<input type="file" id="Afiliar-photo" name="photo" required />
												<?php echo $Afiliar->get_photo_error();?>
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
			</div><!-- /.container -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>