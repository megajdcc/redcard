<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';
$con = new assets\libs\connection();

if(!isset($_SESSION['user']) || !isset($_SESSION['business'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if($_SESSION['business']['id_rol'] != 4){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$info = new negocio\libs\preference_info($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$info->set_information($_POST);
}

$includes = new negocio\libs\includes($con);
$properties['title'] = 'Información, ubicación y contacto del negocio | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $info->get_notification();?>
		<div class="background-white p20 mb30">
			<a href="<?php echo $info->get_profile_url();?>" target="_blank">Ver perfil de negocio</a>
		</div><!-- /.box -->
		<form method="post" action="<?php echo _safe(HOST.'/negocio/preferencias/informacion');?>">
			<div class="background-white p30 mb30">
				<div class="page-title">
					<h4>Informaci&oacute;n del negocio</h4>
				</div>
				<div class="form-group" data-toggle="tooltip" title="Este es el enlace directo al perfil de tu negocio. Travel Points puede cambiar tu enlace en cualquier momento.">
					<label for="url">Enlace al perfil de negocio <i class="fa fa-question-circle text-secondary"></i></label>
					<input class="form-control" type="text" id="url" value="http://www.travelpoints.com.mx/<?php echo $info->get_url();?>" readonly>
				</div><!-- /.form-group -->
				<div class="row">
					<div class="col-lg-8">
						<div class="form-group" data-toggle="tooltip" title="El buscador de Travel Points podr&aacute; encontrar tu negocio con este nombre.">
							<label for="business-name">Nombre de negocio <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
							<input class="form-control" type="text" id="name" name="name" value="<?php echo $info->get_name();?>" placeholder="Nombre de negocio" required/>
							<?php echo $info->get_name_error();?>
						</div><!-- /.form-group -->
						<div class="form-group" data-toggle="tooltip" title="Especifica de manera corta y concisa de qu&eacute; es tu negocio. Por ejemplo: Restaurante de mariscos.">
							<label for="brief">Descripci&oacute;n corta <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
							<input class="form-control" type="text" id="brief" name="brief" value="<?php echo $info->get_brief();?>" placeholder="Descripci&oacute;n corta" maxlength="80" required />
							<?php echo $info->get_brief_error();?>
						</div><!-- /.form-group -->
					</div><!-- /.col-* -->
					<div class="col-lg-4">
						<div class="row">
							<div class="col-sm-6 col-md-12 form-group">
								<label for="category">Categor&iacute;a del negocio<span class="required">*</span></label>
								<select class="form-control" id="category" name="category_id" title="Seleccionar categor&iacute;a" required>
									<?php echo $info->get_category();?>
								</select>
								<?php echo $info->get_category_error();?>
							</div><!-- /.form-group -->
							<div class="col-sm-6 col-md-12 form-group" data-toggle="tooltip" data-placement="bottom" title="Se le cobrar&aacute; este porcentaje por cada venta que registre en nuestro sistema.">
								<label for="commission">Comisi&oacute;n <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
								<div class="input-group">
									<input class="form-control" type="number" id="commission" name="commission" value="<?php echo $info->get_commission();?>" min="6" max="100" placeholder="Comision %" required>
									<span class="input-group-addon"><i class="fa fa-percent"></i></span>
								</div><!-- /.input-group -->
								<?php echo $info->get_commission_error();?>
							</div><!-- /.form-group -->
						</div>
					</div><!-- /.col-* -->
				</div><!-- /.row -->
				<div class="form-group" data-toggle="tooltip" title="Explica con m&aacute;s detalle sobre tu negocio. El buscador de Travel Points tambi&eacute;n puede encontrar tu negocio por su descripci&oacute;n. Agrega palabras clave que creas convenientes.">
					<label for="description">Descripci&oacute;n del negocio <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
					<textarea class="form-control" id="description" placeholder="Breve descripci&oacute;n del negocio" name="description" rows="3" required><?php echo $info->get_description();?></textarea>
					<?php echo $info->get_description_error();?>
				</div><!-- /.form-group -->
			</div>
			<div class="background-white p30 mb30">
				<div class="page-title">
					<h4>Informaci&oacute;n de contacto</h4>
				</div>
				<div class="form-group" data-toggle="tooltip" title="Si no tienes sitio web o deseas borrarlo, deja el espacio en blanco.">
					<label for="website">Sitio web del negocio</label>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-globe"></i></span>
						<input class="form-control" type="text" id="website" name="website" value="<?php echo $info->get_website();?>" placeholder="Ejemplos: minegocio.com | www.minegocio.com | http://minegocio.com | https://www.minegocio.com">
					</div><!-- /.input-group -->
					<?php echo $info->get_website_error();?>
				</div><!-- /.form-group -->
				<hr>
				<div class="form-group">
					<p>Puedes tener hasta 5 correos electr&oacute;nicos y 5 n&uacute;meros telef&oacute;nicos vinculados a tu negocio.</p>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="email">Correo electr&oacute;nico</label>
							<span class="btn btn-success btn-xs right pull-right" id="add-email"><i class="fa fa-plus-circle"></i></span>
						</div>
						<div class="form-group">
							<?php echo $info->get_email();?>
							<?php echo $info->get_email_error();?>
						</div><!-- /.form-group -->
					</div><!-- /.col-* -->
					<div class="col-md-6">
						<div class="form-group">
							<label for="phone">N&uacute;mero telef&oacute;nico</label>
							<span class="btn btn-success btn-xs pull-right" id="add-phone"><i class="fa fa-plus-circle"></i></span>
						</div>
						<div class="form-group">
							<?php echo $info->get_phone();?>
							<?php echo $info->get_phone_error();?>
						</div><!-- /.form-group -->
					</div><!-- /.col-* -->
				</div><!-- /.row -->
			</div>
			<div class="background-white p30 mb30">
				<div class="page-title">
					<h4>Ubicaci&oacute;n del negocio</h4>
				</div>
				<div class="row">
					<div class="col-lg-8">
						<div class="form-group">
							<label for="address">Direcci&oacute;n del negocio <span class="required">*</span></label>
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-map-o"></i></span>
								<input class="form-control" type="text" id="address" name="address" value="<?php echo $info->get_address();?>" placeholder="Direcci&oacute;n del negocio" required>
							</div><!-- /.input-group -->
							<?php echo $info->get_address_error();?>
						</div><!-- /.form-group -->
					</div><!-- /.col-* -->
					<div class="col-lg-4">
						<div class="form-group">
							<label for="postal-code">C&oacute;digo postal  del negocio <span class="required">*</span></label>
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
								<input class="form-control" type="text" id="postal-code" name="postal_code" value="<?php echo $info->get_postal_code();?>" placeholder="C&oacute;digo postal del negocio" required>
							</div><!-- /.input-group -->
							<?php echo $info->get_postal_code_error();?>
						</div><!-- /.form-group -->
					</div><!-- /.col-* -->
				</div><!-- /.row -->
				<div class="row">
					<div class="col-lg-4">
						<div class="form-group">
							<label for="country-select">Pa&iacute;s <span class="required">*</span></label>
							<select class="form-control" id="country-select" name="country_id" title="Selecciona un pa&iacute;s" data-size="10" data-live-search="true" required>
								<?php echo $info->get_country();?>
							</select>
							<?php echo $info->get_country_error();?>
						</div><!-- /.form-group -->
					</div><!-- /.col-* -->
					<div class="col-lg-4">
						<div class="form-group">
							<label for="state-select">Estado <span class="required">*</span></label>
							<select class="form-control" id="state-select" name="state_id" title="Luego un estado" data-size="10" data-live-search="true" required>
								<?php echo $info->get_state();?>
							</select>
							<?php echo $info->get_state_error();?>
						</div><!-- /.form-group -->
					</div><!-- /.col-* -->
					<div class="col-lg-4">
						<div class="form-group">
							<label for="city-select">Ciudad <span class="required">*</span></label>
							<select class="form-control" id="city-select" name="city_id" title="Luego una ciudad" data-size="10" data-live-search="true" required>
								<?php echo $info->get_city();?>
							</select>
							<?php echo $info->get_city_error();?>
						</div><!-- /.form-group -->
					</div><!-- /.col-* -->
				</div><!-- /.row -->
				<hr>
				<div class="form-group">
					<label for="map-canvas">Posici&oacute;n en el mapa <span class="required">*</span></label>
					<?php echo $info->get_coordinates_error();?>
					<p>
						<ul>
							<li>Arrastra el marcador hacia la ubicaci&oacute;n de tu negocio.</li>
							<li>Puedes apoyarte escribiendo una ubicaci&oacute;n como una ciudad, municipio, colonia, etc. y seleccionar una de las opciones sugeridas.</li>
						</ul>
						Las coordenadas de la ubicaci&oacute;n se crean automaticamente.
					</p>
				</div>
				<input class="controls form-control mb30" type="text" id="pac-input" placeholder="Escribe una ubicaci&oacute;n" />
				<div id="map-canvas"></div>
				<div class="row">
					<div class="col-sm-6">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
							<input class="form-control" type="text" id="input-latitude" name="latitude" value="<?php echo $info->get_latitude();?>" placeholder="Latitude" readonly>
						</div><!-- /.form-group -->
					</div><!-- /.col-* -->
					<div class="col-sm-6">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
							<input class="form-control" type="text" id="input-longitude" name="longitude" value="<?php echo $info->get_longitude();?>" placeholder="Longitude" readonly>
						</div><!-- /.form-group -->
					</div><!-- /.col-* -->
				</div><!-- /.row -->
				<hr>
				<div class="form-group center">
					<button class="btn btn-success" type="submit">Guardar todos los cambios</button>
				</div>
			</div><!-- /.box -->
		</form>
	</div>
</div>
<?php echo $footer = $includes->get_footer(); ?>