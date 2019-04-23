<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

use Hotel\models\AfiliarHotel;
use assets\libs\includes as Includes;

if(!isset($_SESSION['user'])){
	$login = new assets\libs\user_login($con);
	if($_SERVER["REQUEST_METHOD"] == "POST"){
		$login->set_data($_POST);
	}
}else{
	$affiliate = new AfiliarHotel($con);
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		if(isset($_POST['send'])){
			$affiliate->set_data($_POST);
		}
	}
}

$includes = new Includes($con);
$properties['title'] = 'Afiliar hotel | Travel Points';
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
							<h1>¡Afilia tu hotel!</h1>
							<p>Env&iacute;anos una solicitud para publicar tu hotel en nuestro directorio.</p>
						</div>
						<p>Solo los socios pueden afiliar un hotel. <a href="<?php echo HOST;?>/hazte-socio">Hazte socio</a> o inicia sesi&oacute;n.</p>
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
								<input type"email" class="form-control" name="email" id="email" value="<?php echo $login->get_email();?>" placeholder="Correo electr&oacute;nico" required />
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
								<h1>¡Afilia tu hotel!</h1>
								<p>Env&iacute;anos una solicitud para publicar tu hotel en nuestro directorio.</p>
							</div>
							<form method="post" action="<?php echo _safe(HOST.'/afiliar-hotel');?>" enctype="multipart/form-data">
								<div class="background-white p30 mb50">
									<h3 class="page-title">Informaci&oacute;n de hotel</h3>
									<div class="row">

										<div class="col-lg-8">
								
											<div class="form-group" data-toggle="tooltip" title="Los clientes Huespedes de Travel Points pueden afiliarse desde su propio perfil...">
												<label for="business-name">Nombre del hotel <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>

												<input class="form-control" type="text" id="business-name" name="nombre" value="<?php echo $affiliate->getNombre();?>" placeholder="Nombre del hotel" required />
												<?php echo $affiliate->getNombreError();?>
											</div><!-- /.form-group -->
										
										</div><!-- /.col-* -->
										
										<div class="col-lg-4">
											<div class="row">
												<div class="col-sm-6 col-md-12 form-group" data-toggle="tooltip" title="El codigo Iata es utilizado para ayudar a agilizar los procesos de transporte aereo y turistico.">
													<label for="category">C&oacute;digo IATA <span class="required">*</span><i class="fa fa-question-circle text-secondary"></i></label>
													<select class="form-control" id="category" name="iata" title="Seleccionar c&oacute;digo IATA" required>
														<option value="null" selected>Seleccione</option>
														
														<?php echo $affiliate->getIata();?>
													</select>
													<?php echo $affiliate->getIataError();?>
												</div><!-- /.form-group -->
											</div>
										</div>
											<div class="col-sm-12">
											<div class="form-group" data-toggle="tooltip" title="Si no tienes sitio web, deja el espacio en blanco.">
												<label for="website">Sitio web del hotel <i class="fa fa-question-circle text-secondary"></i></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-globe"></i></span>
													<input class="form-control" pattern="([--:\w?@%&+~#=]*\.[a-z]{2,4}\/{0,2})((?:[?&](?:\w+)=(?:\w+))+|[--:\w?@%&+~#=]+)?" type="text" id="website" name="website" value="<?php echo $affiliate->getSitioWeb();?>" placeholder="Sitio web del hotel">
												</div><!-- /.input-group -->
												<?php echo $affiliate->getWebsiteError();?>
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
													<input class="form-control" type="text" id="address" name="direccion" value="<?php echo $affiliate->getDireccion();?>" placeholder="Direcci&oacute;n del hotel" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->getDirecccionError();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="postal-code">C&oacute;digo postal  del hotel <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
													<input class="form-control" type="text" id="postal-code" name="codigopostal" value="<?php echo $affiliate->getCodigoPostal();?>" placeholder="C&oacute;digo postal del hotel" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->getCodigoPostalError();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div><!-- /.row -->
									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<label for="country-select">Pa&iacute;s <span class="required">*</span></label>
												<select class="form-control" id="country-select" name="pais" title="Selecciona un pa&iacute;s" data-size="10" data-live-search="true" required>
													<?php echo $affiliate->get_countries();?>
												</select>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="state-select">Estado <span class="required">*</span></label>
												<select class="form-control" id="state-select" name="estado" title="Luego un estado" data-size="10" data-live-search="true" required>
													<?php echo $affiliate->get_states();?>
												</select>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="city-select">Ciudad <span class="required">*</span></label>
												<select class="form-control" id="city-select" name="ciudad" title="Luego una ciudad" data-size="10" data-live-search="true" required>
													<?php echo $affiliate->get_cities();?>
												</select>
												<?php echo $affiliate->getCiudadError();?>
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
										<?php echo $affiliate->getLocationError();?>
									</div>
									<input class="controls form-control mb30" type="text" id="pac-input" placeholder="Escribe una ubicaci&oacute;n" />
									<div id="map-canvas"></div>
									<div class="row">
										<div class="col-sm-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
												<input class="form-control" type="text" id="input-latitude"  name="latitud" value="<?php echo $affiliate->getLatitud();?>" placeholder="Latitud" required>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-sm-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
												<input class="form-control" type="text" id="input-longitude" name="longitud" value="<?php echo $affiliate->getLongitud();?>" placeholder="Longitud" required>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div><!-- /.row -->
								</div><!-- /.box -->


								<div class="background-white p30 mb30">
									<h3 class="page-title">Responsable del &aacute;rea de promoci&oacute;n</h3>
									
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="nombre">Nombre<span class="required">*</span></label>
												<div class="input-group">
														<span class="input-group-addon"><i class="fa fa-address-card-o"></i></span>
													<input class="form-control" type="text" id="nombre_responsable" name="nombre_responsable" value="<?php echo $affiliate->getNombreResponsable();?>" placeholder="Nombre del responsable &aacute;rea de promoci&oacute;n" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->getNombreResponsableError();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->

										<div class="col-lg-6">
											<div class="form-group">
												<label for="apellido">Apellido<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-address-card-o"></i></span>
													<input class="form-control" type="text" id="apellido_responsable" name="apellido_responsable" value="<?php echo $affiliate->getApellidoResponsable();?>" placeholder="Apellido del responsable &aacute;rea de promoci&oacute;n" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->getApellidoResponsableError();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div>
									
									
										
									
										<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="email">Email<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
													<input class="form-control" type="email" id="email" name="email" value="<?php echo $affiliate->getEmail();?>" placeholder="Email del responsable" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->getEmailError();?>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										

										<div class="col-lg-6">
											<div class="form-group">
												<label for="cargo">Cargo<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-black-tie"></i></span>
													<input class="form-control" type="text" id="cargo" name="cargo" value="<?php echo $affiliate->getCargo();?>" placeholder="Cargo" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->getCargoError();?>
											</div><!-- /.form-group -->

											

										</div><!-- /.col-* -->
										<div class="col-lg-6">
										<div class="form-group" data-toggle="tooltip" title="El número de teléfono fijo ejemp:+584128505504, 14128505504">
												<label for="phone">T&eacute;lefono fijo <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-phone-square"></i></span>
													<input class="form-control" type="text" pattern="[+][0-9]{12,15}[+]?" id="phone" name="telefonofijo" value="<?php echo $affiliate->getTelefono();?>" placeholder="N&uacute;mero de t&eacute;lefono fijo" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->getTelefonoError();?>
											</div><!-- /.form-group -->
										</div>
										<div class="col-lg-6">
										<div class="form-group" data-toggle="tooltip" title="El número de teléfono movil ejemp: +584128505504, 14128505504">
												<label for="phone">T&eacute;lefono novil <span class="required">*</span><i class="fa fa-question-circle"></i></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-mobile-phone"></i></span>
													<input class="form-control" type="text" id="movil"  pattern="[+][0-9]{11,15}[+]?" name="movil" value="<?php echo $affiliate->getMovil();?>" placeholder="N&uacute;mero de t&eacute;lefono movil" required>
												</div><!-- /.input-group -->
												<?php echo $affiliate->getMovilError();?>
											</div><!-- /.form-group -->
										</div>
									
									</div><!-- /.row -->
								
								</div><!-- /.box -->
								
								<div class="background-white p30 mb30">
									<h3 class="page-title">Datos para el pago de comisiones</h3>
									
								
									<div class="row">

										<div class="col-lg-6 col-sm-4">
										<h5 class="page-title">Transferencia Bancaria</h5>
											<div class="form-group">
												<label for="nombre">Nombre del banco<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-bank"></i></span>
													<input class="form-control" type="text"  pattern="[a-zA-z]+" id="nombre_banco" name="nombre_banco" value="<?php echo $affiliate->getBanco();?>" placeholder="Nombre del banco" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->getBancoError();?>
											</div><!-- /.form-group -->

											<div class="form-group">
												<label for="cuenta">Cuenta<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
													<input class="form-control" type="text" pattern="[0-9a-zA-z]+" id="cuenta" name="cuenta" value="<?php echo $affiliate->getCuenta();?>" placeholder="Cuenta." required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->getCuentaError();?>
											</div><!-- /.form-group -->

											<div class="form-group" data-toggle="tooltip" title="Solo se permiten digitos númericos, correspondientes a su clabe.">
												<label for="clabe">Clabe<span class="required">*</span><i class="fa fa-question-circle"></i></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
													<input class="form-control" type="text" maxlength="18" id="clabe" pattern="[0-9]{18}" name="clabe" value="<?php echo $affiliate->getClabe();?>" placeholder="Clabe" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->getClabeError();?>
											</div><!-- /.form-group -->

											<div class="form-group" data-toggle="tooltip" title="Una serie alfanuméricas de 8 u 11 digitos, que sirve para identificar al banco receptor cuando se realiza una transferencia">
												<label for="swift">Swift / Bic<span class="required">*</span><i class="fa fa-question-circle"></i></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
													<input class="form-control" type="text" id="swift" maxlength="11" pattern="[A-Za-z0-9]{8,11}" name="swift" value="<?php echo $affiliate->getSwift();?>" placeholder="Swift" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->getSwiftError();?>
											</div><!-- /.form-group -->

										</div><!-- /.col-* -->



										<div class="col-lg-6 col-sm-4">
											<h5 class="page-title">Deposito a tarjeta</h5>
											<div class="form-group">
												<label for="nombre">Nombre del banco<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-bank"></i></span>
													<input class="form-control" type="text" pattern="[a-zA-z]*" id="nombre_banco_targeta" name="nombre_banco_tarjeta" value="<?php echo $affiliate->getBancoNombreTarjeta();?>" placeholder="Nombre del banco" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->getNombreBancoTarjetaError();?>
											</div><!-- /.form-group -->
											<div class="form-group" data-toggle="tooltip" title="Número de la targeta de Credito, conlleva 16 digitos solo numéricos.">
												<label for="nombre">N&uacute;mero de tarjeta<span class="required">*</span><i class="fa fa-question-circle"></i></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-cc"></i></span>
													<input class="form-control" type="text" pattern="[0-9]{16}" maxlength="16" minlength="16" id="numero_targeta" name="numero_targeta" value="<?php echo $affiliate->getTarjeta();?>" placeholder="N&uacute;mero de Tarjeta" required>
												</div><!-- /.input-group -->
												<?php echo $affiliate->getNumeroTarjetaError();?>
											</div><!-- /.form-group -->
								
										
												<h5 class="page-title">Transferencia PayPal</h5>
											<div class="form-group">
												<label for="nombre">Email de Paypal<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-cc-paypal"></i></span>
													<input class="form-control" type="email" id="email_paypal" name="email_paypal" value="<?php echo $affiliate->getEmailPaypal();?>" placeholder="Nombre del banco" required >
												</div><!-- /.input-group -->
												<?php echo $affiliate->getEmailPaypalError();?>
											</div><!-- /.form-group -->
										</div>
										
									</div>
									
								
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