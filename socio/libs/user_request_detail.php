<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace socio\libs;
use assets\libs\connection;
use PDO;

class user_request_detail {
	private $con;
	private $reserved_words = array(
		'admin',
		'assets',
		'errors',
		'errores',
		'business',
		'negocio',
		'member',
		'socio',
		'tienda',
		'store',
		'affiliate-business',
		'afiliar-negocio',
		'ajax',
		'change-password',
		'cambiar-contrasena',
		'certificate',
		'certificado',
		'contact-us',
		'contacto',
		'hazte-socio',
		'signup',
		'index',
		'home',
		'listings',
		'listados',
		'login',
		'logout',
		'negocio_certificados',
		'negocio_eventos',
		'negocio_opiniones',
		'negocio_publicaciones',
		'about-us',
		'nosotros',
		'perfil_negocio',
		'perfil_socio',
		'faq',
		'preguntas-frecuentes',
		'what-is-esmart-club',
		'que-es-esmart-club',
		'recover-account',
		'recuperar-cuenta',
		'terms-of-service',
		'terminos-y-condiciones'
	);
	private $user = array(
		'id' => null
	);
	private $request = array();
	private $images = array(
		'logo' => array('tmp' => null, 'name' => null, 'path' => null),
		'photo' => array('tmp' => null, 'name' => null, 'path' => null)
	);
	private $categories = array();
	private $error = array(
		'name' => null,
		'description' => null,
		'brief' => null,
		'category' => null,
		'commission' => null,
		'url' => null,
		'email' => null,
		'phone' => null,
		'website' => null,
		'address' => null,
		'postal_code' => null,
		'city' => null,
		'state' => null,
		'country' => null,
		'location' => null,
		'logo' => null,
		'photo' => null,
		'warning' => null,
		'error' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->user['id'] = $_SESSION['user']['id_usuario'];
		return;
	}

	public function load_data($id = null){
		$query = "SELECT s.id_solicitud, s.nombre, s.descripcion, s.breve, s.id_categoria, nc.categoria, s.comision, s.url, s.email, s.telefono, s.sitio_web, s.direccion, s.codigo_postal, s.id_ciudad, c.ciudad, e.id_estado, e.estado, p.id_pais, p.pais, s.latitud, s.longitud, s.logo, s.foto, s.mostrar_usuario, s.comentario, s.creado
			FROM solicitud_negocio s 
			INNER JOIN negocio_categoria nc ON s.id_categoria = nc.id_categoria
			INNER JOIN ciudad c ON s.id_ciudad = c.id_ciudad 
			INNER JOIN estado e ON c.id_estado = e.id_estado 
			INNER JOIN pais p ON e.id_pais = p.id_pais
			WHERE s.id_solicitud = :id_solicitud AND s.id_usuario = :id_usuario AND mostrar_usuario != 0";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_solicitud', $id, PDO::PARAM_INT);
			$stmt->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->request = array(
				'id' => $row['id_solicitud'],
				'name' => $row['nombre'],
				'description' => $row['descripcion'],
				'brief' => $row['breve'],
				'category_id' => $row['id_categoria'],
				'category' => $row['categoria'],
				'commission' => $row['comision'],
				'url' => $row['url'],
				'email' => $row['email'],
				'phone' => $row['telefono'],
				'website' => $row['sitio_web'],
				'address' => $row['direccion'],
				'postal_code' => $row['codigo_postal'],
				'city_id' => $row['id_ciudad'],
				'city' => $row['ciudad'],
				'state_id' => $row['id_estado'],
				'state' => $row['estado'],
				'country_id' => $row['id_pais'],
				'country' => $row['pais'],
				'latitude' => $row['latitud'],
				'longitude' => $row['longitud'],
				'logo' => $row['logo'],
				'header' => $row['foto'],
				'status' => $row['mostrar_usuario'],
				'comment' => $row['comentario'],
				'created_at' => $row['creado']
			);
			$query = "SELECT id_categoria, categoria FROM negocio_categoria";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->categories[$row['id_categoria']] = $row['categoria'];
			}
			return true;
		}else{
			return false;
		}
	}

	public function get_request(){
		if($this->request['status'] == 3){
			$html = 
				'<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'" enctype="multipart/form-data">
					<div class="background-white p30 mb30">
						'.$this->get_comment().'
					</div>
					<div class="background-white p30 mb50">
						<h3 class="page-title">Informaci&oacute;n de negocio</h3>
						<div class="row">
							<div class="col-lg-8">
								<div class="form-group" data-toggle="tooltip" title="Los socios de Travel Points pueden encontrar tu negocio por su nombre.">
									<label for="business-name">Nombre del negocio <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
									<input class="form-control" type="text" id="business-name" name="name" value="'.$this->get_name().'" placeholder="Nombre del negocio" required>
									'.$this->get_name_error().'
								</div><!-- /.form-group -->
								<div class="form-group" data-toggle="tooltip" title="Describe tu negocio de manera concisa. M&aacute;ximo 80 caracteres.">
									<label for="brief">Descripci&oacute;n corta <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
									<input class="form-control" type="text" id="brief" name="brief" value="'.$this->get_brief().'" placeholder="Ejemplo: Restaurante de mariscos" maxlength="80" required />
									'.$this->get_brief_error().'
								</div><!-- /.form-group -->
							</div><!-- /.col-* -->
							<div class="col-lg-4">
								<div class="row">
									<div class="col-sm-6 col-md-12 form-group">
										<label for="category">Categor&iacute;a del negocio <span class="required">*</span></label>
										<select class="form-control" id="category" name="category_id" title="Seleccionar categor&iacute;a" required>
											'.$this->get_categories().'
										</select>
										'.$this->get_category_error().'
									</div><!-- /.form-group -->
									<div class="col-sm-6 col-md-12 form-group" data-toggle="tooltip" title="Se te cobrar&aacute; este porcentaje por cada venta que registres en nuestro sistema. Una mayor comisi&oacute;n significa un mejor posicionamiento.">
										<label for="commission">Comisi&oacute;n <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
										<div class="input-group">
											<input class="form-control" type="number" id="commission" name="commission" value="'.$this->get_commission().'" min="6" max="100" placeholder="Comisi&oacute;n %" required>
											<span class="input-group-addon"><i class="fa fa-percent"></i></span>
										</div><!-- /.input-group -->
										'.$this->get_commission_error().'
									</div><!-- /.form-group -->
								</div>
							</div><!-- /.col-* -->
						</div><!-- /.row -->
						<div class="form-group" data-toggle="tooltip" title="Explica con m&aacute;s detalle acerca de tu negocio. Los socios de Travel Points tambi&eacute;n pueden encontrar tu negocio por su descripci&oacute;n. Puedes agregar palabras claves para facilitar la b&uacute;squeda.">
							<label for="description">Descripci&oacute;n del negocio <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
							<textarea class="form-control" id="description" placeholder="Descripci&oacute;n del negocio" name="description" rows="3" required>'.$this->get_description().'</textarea>
							'.$this->get_description_error().'
						</div><!-- /.form-group -->
						<div class="form-group" data-toggle="tooltip" title="Este ser&aacute; el enlace directo al perfil de tu negocio.">
							<label for="url">Enlace deseado del perfil de negocio <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
							<div class="input-group">
								<span class="input-group-addon">www.esmartclub.com/</span>
								<input class="form-control" type="text" id="url" name="url" value="'.$this->get_url().'" placeholder="nombre-de-negocio" required>
							</div><!-- /.input-group -->
						</div><!-- /.form-group -->
						'.$this->get_url_error().'
					</div><!-- /.box -->
					<div class="background-white p30 mb30">
						<h3 class="page-title">Informaci&oacute;n de contacto del negocio</h3>
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label for="email">Correo electr&oacute;nico del negocio <span class="required">*</span></label>
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-at"></i></span>
										<input class="form-control" type="email" id="email" name="email" value="'.$this->get_email().'" placeholder="Correo electr&oacute;nico del negocio" required>
									</div><!-- /.input-group -->
									'.$this->get_email_error().'
								</div><!-- /.form-group -->
							</div><!-- /.col-* -->
							<div class="col-lg-6">
								<div class="form-group">
									<label for="phone">N&uacute;mero telef&oacute;nico del negocio <span class="required">*</span></label>
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-phone"></i></span>
										<input class="form-control" type="text" id="phone" name="phone" value="'.$this->get_phone().'" placeholder="N&uacute;mero telef&oacute;nico del negocio" required >
									</div><!-- /.input-group -->
									'.$this->get_phone_error().'
								</div><!-- /.form-group -->
							</div><!-- /.col-* -->
							<div class="col-sm-12">
								<div class="form-group" data-toggle="tooltip" title="Si no tienes sitio web, deja el espacio en blanco.">
									<label for="website">Sitio web del negocio <i class="fa fa-question-circle text-secondary"></i></label>
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-globe"></i></span>
										<input class="form-control" type="text" id="website" name="website" value="'.$this->get_website().'" placeholder="Sitio web del negocio">
									</div><!-- /.input-group -->
									'.$this->get_website_error().'
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
										<input class="form-control" type="text" id="address" name="address" value="'.$this->get_address().'" placeholder="Direcci&oacute;n del negocio" required >
									</div><!-- /.input-group -->
									'.$this->get_address_error().'
								</div><!-- /.form-group -->
							</div><!-- /.col-* -->
							<div class="col-lg-4">
								<div class="form-group">
									<label for="postal-code">C&oacute;digo postal  del negocio <span class="required">*</span></label>
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
										<input class="form-control" type="text" id="postal-code" name="postal_code" value="'.$this->get_postal_code().'" placeholder="C&oacute;digo postal del negocio" required >
									</div><!-- /.input-group -->
									'.$this->get_postal_code_error().'
								</div><!-- /.form-group -->
							</div><!-- /.col-* -->
						</div><!-- /.row -->
						<div class="row">
							<div class="col-lg-4">
								<div class="form-group">
									<label for="country-select">Pa&iacute;s <span class="required">*</span></label>
									<select class="form-control" id="country-select" name="country_id" title="Selecciona un pa&iacute;s" data-size="10" data-live-search="true" required>
										'.$this->get_countries().'
									</select>
								</div><!-- /.form-group -->
							</div><!-- /.col-* -->
							<div class="col-lg-4">
								<div class="form-group">
									<label for="state-select">Estado <span class="required">*</span></label>
									<select class="form-control" id="state-select" name="state_id" title="Luego un estado" data-size="10" data-live-search="true" required>
										'.$this->get_states().'
									</select>
								</div><!-- /.form-group -->
							</div><!-- /.col-* -->
							<div class="col-lg-4">
								<div class="form-group">
									<label for="city-select">Ciudad <span class="required">*</span></label>
									<select class="form-control" id="city-select" name="city_id" title="Luego una ciudad" data-size="10" data-live-search="true" required>
										'.$this->get_cities().'
									</select>
									'.$this->get_city_error().'
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
							'.$this->get_location_error().'
						</div>
						<input class="controls form-control mb30" type="text" id="pac-input" placeholder="Escribe una ubicaci&oacute;n" />
						<div id="map-canvas"></div>
						<div class="row">
							<div class="col-sm-6">
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
									<input class="form-control" type="text" id="input-latitude" name="latitude" value="'.$this->get_latitude().'" placeholder="Latitud" required>
								</div><!-- /.form-group -->
							</div><!-- /.col-* -->
							<div class="col-sm-6">
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
									<input class="form-control" type="text" id="input-longitude" name="longitude" value="'.$this->get_longitude().'" placeholder="Longitud" required>
								</div><!-- /.form-group -->
							</div><!-- /.col-* -->
						</div><!-- /.row -->
					</div><!-- /.box -->
					<div class="background-white p30 mb30">
						<h3 class="page-title">Im&aacute;genes del negocio</h3>
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label for="logo">Logo del negocio <span class="required">*</span></label>
									<div class="detail-gallery-preview">
										'.$this->get_logo().'
									</div>
								</div><!-- /.form-group -->
								<div class="form-group" data-toggle="tooltip" title="Este logo aparecer&aacute; en tu perfil de negocio. Se recomienda una imagen cuadrada de m&iacute;nimo 300x300 pixeles y un peso inferior a 2MB. La imagen debe ser formato JPG o PNG.">
									<label for="logo">Opcional: cambiar el logo de tu negocio <i class="fa fa-question-circle text-secondary"></i></label>
									<input type="file" id="affiliate-logo" name="logo"/>
									'.$this->get_logo_error().'
								</div><!-- /.form-group -->
							</div><!-- /.col-* -->
							<div class="col-lg-6">
								<div class="form-group">
									<label for="photo">Fotograf&iacute;a del negocio <span class="required">*</span></label>
									<div class="detail-gallery-preview">
										'.$this->get_header_photo().'
									</div>
								</div><!-- /.form-group -->
								<div class="form-group" data-toggle="tooltip" title="Esta ser&aacute; la imagen de portada de tu negocio. Se recomienda una imagen horizontal panor&aacute;mica y un peso inferior a 2 MB. La imagen debe ser formato JPG o PNG.">
									<label for="photo">Opcional: cambiar fotograf&iacute;a de tu negocio <i class="fa fa-question-circle text-secondary"></i></label>
									<input type="file" id="affiliate-photo" name="photo"/>
									'.$this->get_photo_error().'
								</div><!-- /.form-group -->
							</div><!-- /.col-* -->
						</div><!-- /.row -->
					</div><!-- /.box -->
					<div class="row">
						<div class="col-xs-6">
							<p>Los campos marcados son obligatorios <span class="required">*</span></p>
						</div>
						<div class="col-xs-6 right">
							<button class="btn btn-success btn-xl" type="submit" name="resend_request"><i class="fa fa-paper-plane"></i>Reenviar solicitud</button>
						</div>
					</div>
				</form>';
		}else{
			$html = 
				'<div class="background-white p30 mb30">
					'.$this->get_comment().'
				</div>
				<div class="background-white p30 mb50">
					<div class="row">
						<div class="col-lg-8">
							<div class="form-group" data-toggle="tooltip" title="Los socios de Travel Points pueden encontrar tu negocio por su nombre.">
								<label for="name">Nombre del negocio <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
								<input class="form-control" type="text" id="name" value="'.$this->get_name().'" readonly/>
							</div><!-- /.form-group -->
							<div class="form-group" data-toggle="tooltip" title="Describe tu negocio de manera concisa. M&aacute;ximo 80 caracteres.">
								<label for="brief">Descripci&oacute;n corta <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
								<input class="form-control" type="text" id="brief" value="'.$this->get_brief().'" readonly/>
							</div><!-- /.form-group -->
						</div><!-- /.col-* -->
						<div class="col-lg-4">
							<div class="row">
								<div class="col-sm-6 col-md-12 form-group">
									<label for="category">Categor&iacute;a del negocio <span class="required">*</span></label>
									<input class="form-control" type="text" id="category" value="'.$this->get_category().'" readonly/>
								</div><!-- /.form-group -->
								<div class="col-sm-6 col-md-12 form-group" data-toggle="tooltip" title="Se te cobrar&aacute; este porcentaje por cada venta que registres en nuestro sistema. Una mayor comisi&oacute;n significa un mejor posicionamiento.">
									<label for="commission">Comisi&oacute;n <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
									<div class="input-group">
										<input class="form-control" type="text" id="commission" value="'.$this->get_commission().'" readonly>
										<span class="input-group-addon"><i class="fa fa-percent"></i></span>
									</div><!-- /.input-group -->
								</div><!-- /.form-group -->
							</div>
						</div><!-- /.col-* -->
					</div><!-- /.row -->
					<div class="form-group" data-toggle="tooltip" title="Explica con m&aacute;s detalle acerca de tu negocio. Los socios de Travel Points tambi&eacute;n pueden encontrar tu negocio por su descripci&oacute;n. Puedes agregar palabras claves para facilitar la b&uacute;squeda.">
						<label for="description">Descripci&oacute;n del negocio <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
						<textarea class="form-control" id="description" rows="3" readonly>'.$this->get_description().'</textarea>
					</div><!-- /.form-group -->
					<div class="form-group" data-toggle="tooltip" title="Este ser&aacute; el enlace directo al perfil de tu negocio.">
						<label for="biz-url">Enlace deseado del perfil de negocio <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
						<div class="input-group">
							<span class="input-group-addon">www.esmartclub.com/</span>
							<input class="form-control" type="text" id="biz-url" value="'.$this->get_url().'" readonly>
						</div><!-- /.input-group -->
					</div><!-- /.form-group -->
					<hr>
					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label for="email">Correo electr&oacute;nico del negocio <span class="required">*</span></label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-at"></i></span>
									<input class="form-control" type="text" id="email" value="'.$this->get_email().'" readonly>
								</div><!-- /.input-group -->
							</div><!-- /.form-group -->
						</div><!-- /.col-* -->
						<div class="col-lg-6">
							<div class="form-group">
								<label for="phone">N&uacute;mero telef&oacute;nico del negocio <span class="required">*</span></label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-phone"></i></span>
									<input class="form-control" type="text" id="phone" value="'.$this->get_phone().'" readonly>
								</div><!-- /.input-group -->
							</div><!-- /.form-group -->
						</div><!-- /.col-* -->
						<div class="col-sm-12">
							<div class="form-group" data-toggle="tooltip" title="Si no tienes sitio web, deja el espacio en blanco.">
								<label for="website">Sitio web del negocio <i class="fa fa-question-circle text-secondary"></i></label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-globe"></i></span>
									<input class="form-control" type="text" id="website" value="'.$this->get_website().'" readonly>
								</div><!-- /.input-group -->
							</div><!-- /.form-group -->
						</div><!-- /.col-* -->
					</div><!-- /.row -->
					<hr>
					<div class="row">
						<div class="col-lg-8">
							<div class="form-group">
								<label for="address">Direcci&oacute;n del negocio <span class="required">*</span></label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-map-o"></i></span>
									<input class="form-control" type="text" id="address" value="'.$this->get_address().'" readonly>
								</div><!-- /.input-group -->
							</div><!-- /.form-group -->
						</div><!-- /.col-* -->
						<div class="col-lg-4">
							<div class="form-group">
								<label for="postal-code">C&oacute;digo postal  del negocio <span class="required">*</span></label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
									<input class="form-control" type="text" id="postal-code" value="'.$this->get_postal_code().'" readonly>
								</div><!-- /.input-group -->
							</div><!-- /.form-group -->
						</div><!-- /.col-* -->
					</div><!-- /.row -->
					<div class="row">
						<div class="col-lg-4">
							<div class="form-group">
								<label for="country">Pa&iacute;s <span class="required">*</span></label>
								<input class="form-control" type="text" id="country" value="'.$this->get_country().'" readonly>
							</div><!-- /.form-group -->
						</div><!-- /.col-* -->
						<div class="col-lg-4">
							<div class="form-group">
								<label for="state">Estado <span class="required">*</span></label>
								<input class="form-control" type="text" id="state" value="'.$this->get_state().'" readonly>
							</div><!-- /.form-group -->
						</div><!-- /.col-* -->
						<div class="col-lg-4">
							<div class="form-group">
								<label for="city">Ciudad <span class="required">*</span></label>
								<input class="form-control" type="text" id="city" value="'.$this->get_city().'" readonly>
							</div><!-- /.form-group -->
						</div><!-- /.col-* -->
					</div><!-- /.row -->
					<hr>
					<div class="form-group">
						<label>Posici&oacute;n en el mapa <span class="required">*</span></label>
						<div class="detail-content">
							<div class="detail-map">
								<div class="map-position">
									<div id="listing-detail-map"
										 data-transparent-marker-image="'.HOST.'/assets/img/transparent-marker-image.png"
										 data-styles=\'[{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#444444"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2f2f2"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"poi.government","elementType":"labels.text.fill","stylers":[{"color":"#b43b3b"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"hue":"#ff0000"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},{"featureType":"road","elementType":"geometry.fill","stylers":[{"lightness":"8"},{"color":"#bcbec0"}]},{"featureType":"road","elementType":"labels.text.fill","stylers":[{"color":"#5b5b5b"}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#7cb3c9"},{"visibility":"on"}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"color":"#abb9c0"}]},{"featureType":"water","elementType":"labels.text","stylers":[{"color":"#fff1f1"},{"visibility":"off"}]}]\'
										 data-zoom="15"
										 data-latitude="'.$this->get_latitude().'"
										 data-longitude="'.$this->get_longitude().'"
										 data-icon="fa fa-map-marker">
									</div><!-- /#map-property -->
								</div><!-- /.map-property -->
							</div><!-- /.detail-map -->
						</div>
					</div>
					<div class="row">
						<div class="col-sm-6">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
								<input class="form-control" type="text" id="input-latitude" value="'.$this->get_latitude().'" readonly>
							</div><!-- /.form-group -->
						</div><!-- /.col-* -->
						<div class="col-sm-6">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
								<input class="form-control" type="text" id="input-longitude" value="'.$this->get_longitude().'" readonly>
							</div><!-- /.form-group -->
						</div><!-- /.col-* -->
					</div><!-- /.row -->
					<hr>
					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label for="logo">Logo del negocio <span class="required">*</span></label>
								<div class="detail-gallery-preview">
									'.$this->get_logo().'
								</div>
							</div><!-- /.form-group -->
						</div><!-- /.col-* -->
						<div class="col-lg-6">
							<div class="form-group">
								<label for="photo">Fotograf&iacute;a del negocio <span class="required">*</span></label>
								<div class="detail-gallery-preview">
									'.$this->get_header_photo().'
								</div>
							</div><!-- /.form-group -->
						</div><!-- /.col-* -->
					</div><!-- /.row -->
				</div>';
		}
		return $html;
	}

	public function resend_data(array $post){
		$this->set_name($post['name']);
		$this->set_description($post['description']);
		$this->set_brief($post['brief']);
		$this->set_category_id($post['category_id']);
		$this->set_commission($post['commission']);
		$this->set_url($post['url']);
		$this->set_email($post['email']);
		$this->set_phone($post['phone']);
		$this->set_website($post['website']);
		$this->set_address($post['address']);
		$this->set_postal_code($post['postal_code']);
		$this->set_city_id($post['city_id']);
		$this->set_state_id($post['state_id']);
		$this->set_country_id($post['country_id']);
		$this->set_location($post['latitude'], $post['longitude']);
		$this->set_logo($_FILES);
		$this->set_photo($_FILES);
		if(!array_filter($this->error)){
			$this->update_request();
			return true;
		}
		$this->error['warning'] = 'Uno o más campos tienen errores. Verifícalos cuidadosamente.';
		return false;
	}

	private function update_request(){
		if($this->images['logo']['tmp'] && $this->images['logo']['path']){
			if(file_exists(ROOT.'/assets/img/business_request/'.$this->request['logo'])){
				unlink(ROOT.'/assets/img/business_request/'.$this->request['logo']);
			}
			if(!move_uploaded_file($this->images['logo']['tmp'], $this->images['logo']['path'])){
				$this->error['error'] = 'Error al tratar de subir el logo.';
				return false;
			}
			$this->request['logo'] = $this->images['logo']['name'];
		}
		if($this->images['photo']['tmp'] && $this->images['photo']['path']){
			if(file_exists(ROOT.'/assets/img/business_request/'.$this->request['header'])){
				unlink(ROOT.'/assets/img/business_request/'.$this->request['header']);
			}
			if(!move_uploaded_file($this->images['photo']['tmp'], $this->images['photo']['path'])){
				$this->error['error'] = 'Error al tratar de subir la portada.';
				return false;
			}
			$this->request['header'] = $this->images['photo']['name'];
		}
		$query = "UPDATE solicitud_negocio SET 
			nombre = :nombre,
			descripcion = :descripcion,
			breve = :breve,
			id_categoria = :id_categoria,
			comision = :comision,
			url = :url,
			email = :email,
			telefono = :telefono,
			sitio_web = :sitio_web,
			direccion = :direccion,
			codigo_postal = :codigo_postal,
			id_ciudad = :id_ciudad,
			latitud = :latitud,
			longitud = :longitud,
			logo = :logo,
			foto = :foto,
			situacion = 2,
			mostrar_usuario = 2
			WHERE id_solicitud = :id_solicitud";
		$params = array(
			':nombre' => $this->request['name'],
			':descripcion' => $this->request['description'],
			':breve' => $this->request['brief'],
			':id_categoria' => $this->request['category_id'],
			':comision' => $this->request['commission'],
			':url' => $this->request['url'],
			':email' => $this->request['email'],
			':telefono' => $this->request['phone'],
			':sitio_web' => $this->request['website'],
			':direccion' => $this->request['address'],
			':codigo_postal' => $this->request['postal_code'],
			':id_ciudad' => $this->request['city_id'],
			':latitud' => $this->request['latitude'],
			':longitud' => $this->request['longitude'],
			':logo' => $this->request['logo'],
			':foto' => $this->request['header'],
			':id_solicitud' => $this->request['id'],
		);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$_SESSION['notification']['success'] = 'Se ha enviado la solicitud para afiliar tu negocio exitosamente. Te mantendremos informado de cualquier avance.';
		header('Location: '._safe($_SERVER['REQUEST_URI']));
		die();
		return;
	}

	public function delete_request(){
		$query = "UPDATE solicitud_negocio SET mostrar_usuario = 0 WHERE id_solicitud = :id_solicitud";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_solicitud', $this->request['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$_SESSION['notification']['success'] = 'Solicitud eliminada exitosamente.';
		header('Location: '.HOST.'/socio/negocios/solicitudes');
		die();
		return;
	}

	private function set_name($string = null){
		if($string){
			$string = trim($string);
			$this->request['name'] = $string;
			return true;
		}
		$this->error['name'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_description($string = null){
		if($string){
			$string = trim($string);
			$this->request['description'] = $string;
			return true;
		}
		$this->error['description'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_brief($string = null){
		if($string){
			$this->request['brief'] = trim($string);
			if(strlen($string) > 60){
				$this->error['brief'] = 'La descripción corta no debe exceder los 60 caracteres.';
				return false;
			}
			return true;
		}
		$this->error['brief'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_category_id($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				$this->error['category'] = 'Selecciona una categoría.';
				return false;
			}
			$this->request['category_id'] = $string;
			return true;
		}
		$this->error['category'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_commission($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 6 || $string > 100){
				$this->error['commission'] = 'Ingresa un número entero entre 6 y 100.';
				return false;
			}
			$this->request['commission'] = $string;
			return true;
		}
		$this->error['commission'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_url($string = null){
		if($string){
			$string = strtolower(trim($string));
			if(in_array($string,$this->reserved_words)){
				$this->error['url'] = 'La url del negocio no puede ser "'._safe($string).'", la cual es una palabra reservada.';
			}
			if(!preg_match('/^[a-z0-9-]+$/ui',$string)){
				$this->error['url'] = 'La url del negocio solo debe contener letras, números y guiones. No se permiten acentos, caracteres especiales o espacios.';
			}
			$this->request['url'] = $string;
			return;
		}
		$this->error['url'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_email($string = null){
		if($string){
			$email = filter_var($string, FILTER_VALIDATE_EMAIL);
			if(!$email){
				$this->error['email'] = 'Escribe una dirección de correo electrónico correcta. Ejemplo: usuario@ejemplo.com.';
				$this->request['email'] = $string;
				return false;
			}
			$this->request['email'] = $email;
			return true;
		}
		$this->error['email'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_phone($string = null){
		if($string){
			$string = trim($string);
			if(!preg_match('/^[0-9() +-]+$/ui',$string)){
				$this->error['phone'] = 'Escribe un número telefónico correcto. Ejemplo: (123) 456-78-90';
				$this->request['phone'] = $string;
				return false;
			}
			$this->request['phone'] = $string;
			return true;
		}
		$this->error['phone'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_website($string = null){
		if($string){
			if(!preg_match('_^(?:(?:https?|ftp)://)?(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,}))\.?)(?::\d{2,5})?(?:[/?#]\S*)?$_iuS',$string)){
				$this->error['website'] = 'Escribe un enlace correcto. Ejemplo: www.esmartclub.com o http://esmartclub.com';
				$this->request['website'] = $string;
				return false;
			}
			if(!preg_match("@^https?://@", $string)){
				$this->request['website'] = 'http://'.$string;
			}else{
				$this->request['website'] = $string;
			}
		}
		return true;
	}

	private function set_address($string = null){
		if($string){
			$string = trim($string);
			$this->request['address'] = $string;
			return true;
		}
		$this->error['address'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_postal_code($string = null){
		if($string){
			$string = trim($string);
			$this->request['postal_code'] = $string;
			return true;
		}
		$this->error['postal_code'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_city_id($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				$this->error['city'] = 'Selecciona una ciudad.';
				return false;
			}
			$this->request['city_id'] = $string;
			return true;
		}
		$this->error['city'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_state_id($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				return false;
			}
			$this->request['state_id'] = $string;
			return true;
		}
		return false;
	}

	private function set_country_id($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				return false;
			}
			$this->request['country_id'] = $string;
			return true;
		}
		return false;
	}

	private function set_location($lat = null, $lon = null){
		if($lat & $lon){
			if(!filter_var($lat, FILTER_VALIDATE_FLOAT) || !filter_var($lon, FILTER_VALIDATE_FLOAT)){
				$this->error['location'] = 'Utiliza el marcador del mapa para ubicar tu negocio.';
				return false;
			}else{
				$this->request['latitude'] = trim($lat);
				$this->request['longitude'] = trim($lon);
				return true;
			}
		}
		$this->error['location'] = 'Es obligatorio ubicar tu negocio en el mapa.';
		return false;
	}

	private function set_logo($files = null){
		$image = new \assets\libraries\bulletproof\bulletproof($files);
		$image->setLocation(ROOT.'/assets/img/business_request');
		if($image['logo']){
			if($image->upload()){
				$this->images['logo']['tmp'] = $files['logo']['tmp_name'];
				$this->images['logo']['name'] = $image->getName().'.'.$image->getMime();
				$this->images['logo']['path'] = $image->getFullPath();
				return true;
			}
			$this->error['logo'] = $image['error'];
			return false;
		}
		if($files['logo']['error'] == 1){
			$this->error['logo'] = 'Has excedido el límite de imagen de 2MB.';
		}
		return false;
	}

	private function set_photo($files = null){
		$image = new \assets\libraries\bulletproof\bulletproof($files);
		$image->setLocation(ROOT.'/assets/img/business_request');
		if($image['photo']){
			if($image->upload()){
				$this->images['photo']['tmp'] = $files['photo']['tmp_name'];
				$this->images['photo']['name'] = $image->getName().'.'.$image->getMime();
				$this->images['photo']['path'] = $image->getFullPath();
				return true;
			}
			$this->error['photo'] = $image['error'];
			return false;
		}
		if($files['photo']['error'] == 1){
			$this->error['photo'] = 'Has excedido el límite de imagen de 2MB.';
		}
		return false;
	}

	public function get_comment(){
		switch ($this->get_status()) {
			case 1:
				$status_tag = 
					'<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'">
						<button class="btn btn-xs btn-danger pull-right" type="submit" id="delete-request" name="delete_request"><i class="fa fa-times m0"></i></button>
					</form>
					<span class="label label-md label-success mr20">Solicitud aceptada</span>';
				break;
			case 2:
				$status_tag = '<span class="label label-md label-warning mr20">Solicitud pendiente</span>';
				break;
			case 3:
				$status_tag = '<span class="label label-md label-info mr20">Corregir solicitud</span>';
				break;
			case 4:
				$status_tag = 
					'<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'">
						<button class="btn btn-xs btn-danger pull-right" type="submit" id="delete-request" name="delete_request"><i class="fa fa-times m0"></i></button>
					</form>
					<span class="label label-md label-danger mr20">Solicitud rechazada</span>';
				break;
			default:
				$status_tag = '';
				break;
		}
		$title_tag = $status_tag.'<label class="cert-date">'.$this->get_date().'</label>';
		if(!empty($this->request['comment'])){
			$html = 
			'<div class="page-title">'.$title_tag.'</div>
			<label>Comentario de Travel Points para el solicitante</label>
			<p>'.nl2br(_safe($this->request['comment'])).'</p>';
		}else{
			$html = $title_tag;
		}
		return $html;
	}

	public function get_id(){
		return $this->request['id'];
	}

	public function get_name(){
		return _safe($this->request['name']);
	}

	public function get_name_error(){
		if($this->error['name']){
			return '<p class="text-danger">'._safe($this->error['name']).'</p>';
		}
	}

	public function get_description(){
		return _safe($this->request['description']);
	}

	public function get_description_error(){
		if($this->error['description']){
			return '<p class="text-danger">'._safe($this->error['description']).'</p>';
		}
	}

	public function get_brief(){
		return _safe($this->request['brief']);
	}

	public function get_brief_error(){
		if($this->error['brief']){
			$error = '<p class="text-danger">'._safe($this->error['brief']).'</p>';
			return $error;
		}
	}

	public function get_category(){
		return _safe($this->request['category']);
	}

	public function get_categories(){
		$html = null;
		foreach ($this->categories as $key => $value) {
			if($this->request['category_id'] == $key){
				$html .= '<option value="'.$key.'" selected>'._safe($value).'</option>';
			}else{
				$html .= '<option value="'.$key.'">'._safe($value).'</option>';
			}
		}
		return $html;
	}

	public function get_category_error(){
		if($this->error['category']){
			return '<p class="text-danger">'._safe($this->error['category']).'</p>';
		}
	}

	public function get_commission(){
		return _safe($this->request['commission']);
	}

	public function get_commission_error(){
		if($this->error['commission']){
			return '<p class="text-danger">'._safe($this->error['commission']).'</p>';
		}
	}

	public function get_url(){
		return _safe($this->request['url']);
	}

	public function get_url_error(){
		if($this->error['url']){
			return '<p class="text-danger">'._safe($this->error['url']).'</p>';
		}
	}

	public function get_email(){
		return _safe($this->request['email']);
	}

	public function get_email_error(){
		if($this->error['email']){
			return '<p class="text-danger">'._safe($this->error['email']).'</p>';
		}
	}

	public function get_phone(){
		return _safe($this->request['phone']);
	}

	public function get_phone_error(){
		if($this->error['phone']){
			return '<p class="text-danger">'._safe($this->error['phone']).'</p>';
		}
	}

	public function get_website(){
		return _safe($this->request['website']);
	}

	public function get_website_error(){
		if($this->error['website']){
			return '<p class="text-danger">'._safe($this->error['website']).'</p>';
		}
	}

	public function get_address(){
		return _safe($this->request['address']);
	}

	public function get_address_error(){
		if($this->error['address']){
			return '<p class="text-danger">'._safe($this->error['address']).'</p>';
		}
	}

	public function get_postal_code(){
		return _safe($this->request['postal_code']);
	}

	public function get_postal_code_error(){
		if($this->error['postal_code']){
			return '<p class="text-danger">'._safe($this->error['postal_code']).'</p>';
		}
	}

	public function get_country(){
		return _safe($this->request['country']);
	}

	public function get_countries(){
		$html = null;
		$query = "SELECT id_pais, pais FROM pais";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$country = _safe($row['pais']);
			if($this->request['country_id'] == $row['id_pais']){
				$html .= '<option value="'.$row['id_pais'].'" selected>'.$country.'</option>';
			}else{
				$html .= '<option value="'.$row['id_pais'].'">'.$country.'</option>';
			}
		}
		return $html;
	}

	public function get_country_error(){
		if($this->error['country']){
			return '<p class="text-danger">'._safe($this->error['country']).'</p>';
		}
	}

	public function get_state(){
		return _safe($this->request['state']);
	}

	public function get_states(){
		$states = null;
		if($this->request['country_id']){
			$query = "SELECT id_estado, estado FROM estado WHERE id_pais = :id_pais";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_pais', $this->request['country_id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$state = _safe($row['estado']);
				if($this->request['state_id'] == $row['id_estado']){
					$states .= '<option value="'.$row['id_estado'].'" selected>'.$state.'</option>';
				}else{
					$states .= '<option value="'.$row['id_estado'].'">'.$state.'</option>';
				}
			}
		}
		return $states;
	}

	public function get_state_error(){
		if($this->error['state']){
			return '<p class="text-danger">'._safe($this->error['state']).'</p>';
		}
	}

	public function get_city(){
		return _safe($this->request['city']);
	}

	public function get_cities(){
		$cities = null;
		if($this->request['state_id']){
			$query = "SELECT id_ciudad, ciudad FROM ciudad WHERE id_estado = :id_estado";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_estado', $this->request['state_id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$city = _safe($row['ciudad']);
				if($this->request['city_id'] == $row['id_ciudad']){
					$cities.= '<option value="'.$row['id_ciudad'].'" selected>'.$city.'</option>';
				}else{
					$cities.= '<option value="'.$row['id_ciudad'].'">'.$city.'</option>';
				}
			}
		}
		return $cities;
	}


	public function get_city_error(){
		if($this->error['city']){
			return '<p class="text-danger">'._safe($this->error['city']).'</p>';
		}
	}

	public function get_latitude(){
		return _safe($this->request['latitude']);
	}

	public function get_longitude(){
		return _safe($this->request['longitude']);
	}

	public function get_location_error(){
		if($this->error['location']){
			return '<p class="text-danger">'._safe($this->error['location']).'</p>';
		}
	}

	public function get_logo(){
		$html = 
			'<a href="'.HOST.'/assets/img/business_request/'.$this->request['logo'].'">
				<img class="img-thumbnail img-rounded gallery-img" src="'.HOST.'/assets/img/business_request/'.$this->request['logo'].'">
			</a>';
		return $html;
	}

	public function get_header_photo(){
		$html = 
			'<a href="'.HOST.'/assets/img/business_request/'.$this->request['header'].'">
				<img class="img-thumbnail img-rounded gallery-img" src="'.HOST.'/assets/img/business_request/'.$this->request['header'].'">
			</a>';
		return $html;
	}

	public function get_logo_error(){
		if($this->error['logo']){
			$error = '<p class="text-danger">'._safe($this->error['logo']).'</p>';
			return $error;
		}
	}

	public function get_photo_error(){
		if($this->error['photo']){
			$error = '<p class="text-danger">'._safe($this->error['photo']).'</p>';
			return $error;
		}
	}

	public function get_status(){
		return $this->request['status'];
	}

	public function get_date(){
		return date('d/m/Y g:i A', strtotime($this->request['created_at']));
	}

	public function get_notification(){
		$html = null;
		if(isset($_SESSION['notification']['success'])){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-success" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notification']['success']).'
			</div>';
			unset($_SESSION['notification']['success']);
		}
		if(isset($_SESSION['notification']['info'])){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-info" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notification']['info']).'
			</div>';
			unset($_SESSION['notification']['info']);
		}
		if($this->error['warning']){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-warning" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->error['warning']).'
			</div>';
		}
		if($this->error['error']){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->error['error']).'
			</div>';
		}
		return $html;
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\user_request_detail.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}

	private function friendly_url($url){
		$url = strtolower($this->replace_accents(trim($url))); // 1. Trim spaces around, replace all special chars and lowercase all
		$find = array(' ', '&', '\r\n', '\n', '+', ','); // 2. Reple spaces and union characters with ' - '
		$url = str_replace($find, '-', $url);
		$find = array('/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/'); // 3. Delete and replace the rest of special chars
		$repl = array('', ' ', '');
		$url = str_replace(' ', '-', trim(preg_replace($find, $repl, $url)));
		return $url;
	}

	private function replace_accents($var){
		$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
		$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
		$var = str_replace($a, $b, $var);
		return $var;
	}
}
?>