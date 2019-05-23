<?php

namespace negocio\libs;
use assets\libs\connection;
use PDO;

class includes {
	private $con;
	private $user = array(
		'id' => null,
		'username' => null,
		'image' => null,
		'alias' => null,
		'admin' => null,
		'other_business' => null
	);
	private $business = array(
		'id' => null,
		'name' => null
	);
	private $sidebar = null;
	private $crumbs = array();

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->user['id'] = $_SESSION['user']['id_usuario'];
		$this->business['id'] = $_SESSION['business']['id_negocio'];
		$this->load_data();
	
		$this->load_sidebar();
		return;
	}

	private function load_data(){
		// User Data
		$query = "SELECT username, imagen, nombre, apellido, id_rol FROM usuario WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->user['username'] = _safe($row['username']);
			if(!empty($row['imagen'])){
				$this->user['image'] = _safe($row['imagen']);
			}else{
				$this->user['image'] = 'default.jpg';
			}
			if(!empty($row['nombre']) && !empty($row['apellido'])){
				$this->user['alias'] = _safe($row['nombre'].' '.$row['apellido']);
			}else{
				$this->user['alias'] = $this->user['username'];
			}
			if($row['id_rol'] == 1 || $row['id_rol'] == 2){
				$this->user['admin'] = true;
			}
		}
		// Business Data
		$query = "SELECT n.id_negocio, nombre FROM negocio_empleado ne 
			INNER JOIN negocio n ON ne.id_negocio = n.id_negocio 
			WHERE id_empleado = :id_empleado";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_empleado', $this->user['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			if($this->business['id'] == $row['id_negocio']){
				$this->business['name'] = _safe($row['nombre']);
			}else{
				$this->user['other_business'][$row['id_negocio']] = _safe($row['nombre']);
			}
		}
		return;
	}

	private function get_business_dropdown(){
		$html = null;
		if(!is_null($this->user['other_business'])){
		if(count($this->user['other_business']) > 0){
			$html =
			'<div class="header-nav-user">
				<div class="dropdown">
					<button class="btn btn-default dropdown-toggle mimic-header-nav-user-image" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
						<span>'.$this->business['name'].'</span> <i class="fa fa-chevron-down"></i>
					</button>
					<ul class="dropdown-menu reverse-dropdown" aria-labelledby="dropdownMenu1">';
			foreach ($this->user['other_business'] as $key => $value) {
				
				$html .= 
				'<form method="post" action="'.HOST.'/negocio/">
					<li><a href="#" class="change-business">'.$value.'</a></li>

					<input type="hidden" value="'.$key.'" name="change_business">
				</form>';
			}
			$html .= 
			'		</ul>
				</div><!-- /.dropdown -->
			</div><!-- /.header-nav-user -->';
		}
	}
		return $html;
	}

	private function load_sidebar(){
		switch (basename(dirname($_SERVER['SCRIPT_NAME']))) {
			case 'negocio':
				$this->crumbs[0] = 'Inicio';
				switch (basename($_SERVER['SCRIPT_NAME'])) {
					case 'index.php':
						$this->crumbs[1] = 'Resumen';
						break;
					case 'recargar-saldo.php':
						$this->crumbs[1] = 'Recargar saldo';
						break;
					default:
						$this->crumbs[1] = '';
						break;
				}
				$this->sidebar =
						'<li'.$this->set_active_sidebar_tab('index.php').'>
							<a href="'.HOST.'/negocio/">
								<span class="icon"><i class="fa fa-tachometer"></i></span>
								<span class="title">Resumen</span>
								<span class="subtitle">Vistazo general</span>
							</a>
						</li>';
				if($_SESSION['business']['id_rol'] == 4 || $_SESSION['business']['id_rol'] == 5){
					$this->sidebar .=
						'<li'.$this->set_active_sidebar_tab('recargar-saldo.php').'>
							<a href="'.HOST.'/negocio/recargar-saldo">
								<span class="icon"><i class="fa fa-credit-card"></i></span>
								<span class="title">Recargar saldo</span>
								<span class="subtitle">Agrega saldo a tu negocio</span>
							</a>
						</li>';
				}
				break;
			case 'ventas':
				$this->crumbs[0] = 'Ventas';
				switch (basename($_SERVER['SCRIPT_NAME'])) {
					case 'index.php':
						$this->crumbs[1] = 'Nueva venta';
						break;
					case 'historial.php':
						$this->crumbs[1] = 'Historial';
						break;
					default:
						$this->crumbs[1] = '';
						break;
				}
				$this->sidebar =
						'<li'.$this->set_active_sidebar_tab('index.php').'>
							<a href="'.HOST.'/negocio/ventas/">
								<span class="icon"><i class="fa fa-credit-card"></i></span>
								<span class="title">Nueva venta</span>
								<span class="subtitle">Registrar un consumo</span>
							</a>
						</li>';
				if($_SESSION['business']['id_rol'] == 4 || $_SESSION['business']['id_rol'] == 5){
					$this->sidebar .=
						'<li'.$this->set_active_sidebar_tab('historial.php').'>
							<a href="'.HOST.'/negocio/ventas/historial">
								<span class="icon"><i class="fa fa-list"></i></span>
								<span class="title">Historial</span>
								<span class="subtitle">Ver todas las ventas</span>
							</a>
						</li>';
				}
				break;
			case 'certificados':
				$this->crumbs[0] = 'Certificados de regalo';
				switch (basename($_SERVER['SCRIPT_NAME'])) {
					case 'reservar.php':
						$this->crumbs[1] = 'Reservar';
						break;
					case 'crear.php':
						$this->crumbs[1] = 'Crear Nuevo';
						break;
					case 'lista.php':
						$this->crumbs[1] = 'Listado';
						break;
					case 'canjeados.php':
						$this->crumbs[1] = 'Canjeados';
						break;
					default:
						$this->crumbs[1] = '';
						break;
				}
				$this->sidebar =
						'<li'.$this->set_active_sidebar_tab('reservar.php').'>
							<a href="'.HOST.'/negocio/certificados/reservar">
								<span class="icon"><i class="fa fa-gift"></i></span>
								<span class="title">Reservar</span>
								<span class="subtitle">Reservar un certificado</span>
							</a>
						</li>';
				if($_SESSION['business']['id_rol'] == 4 || $_SESSION['business']['id_rol'] == 5){
					$this->sidebar .=
						'<li'.$this->set_active_sidebar_tab('crear.php').'>
							<a href="'.HOST.'/negocio/certificados/crear">
								<span class="icon"><i class="fa fa-upload"></i></span>
								<span class="title">Crear Nuevo</span>
								<span class="subtitle">Subir un nuevo certificado</span>
							</a>
						</li>
						<li'.$this->set_active_sidebar_tab('lista.php').'>
							<a href="'.HOST.'/negocio/certificados/lista">
								<span class="icon"><i class="fa fa-list"></i></span>
								<span class="title">Listado</span>
								<span class="subtitle">Ver todos los certificados</span>
							</a>
						</li>
						<li'.$this->set_active_sidebar_tab('canjeados.php').'>
							<a href="'.HOST.'/negocio/certificados/canjeados">
								<span class="icon"><i class="fa fa-check-square"></i></span>
								<span class="title">Canjeados</span>
								<span class="subtitle">Ver certificados canjeados</span>
							</a>
						</li>';
				}
				break;
			case 'reportes':
				$this->crumbs[0] = 'Reportes';
				switch (basename($_SERVER['SCRIPT_NAME'])) {
					case 'index.php':
						$this->crumbs[1] = 'Ventas';
						break;
					case 'certificados.php':
						$this->crumbs[1] = 'Certificados canjeados';
						break;
						$this->crumbs[1] = '';
						break;
				}
				$this->sidebar =
						'<li'.$this->set_active_sidebar_tab('index.php').'>
							<a href="'.HOST.'/negocio/reportes/">
								<span class="icon"><i class="fa fa-money"></i></span>
								<span class="title">Ventas</span>
								<span class="subtitle">Reporte de ventas</span>
							</a>
						</li>
						<li'.$this->set_active_sidebar_tab('certificados.php').'>
							<a href="'.HOST.'/negocio/reportes/certificados">
								<span class="icon"><i class="fa fa-gift"></i></span>
								<span class="title">Certificados</span>
								<span class="subtitle">Reporte de canjeados</span>
							</a>
						</li>';
				break;
			case 'personal':
				$this->crumbs[0] = 'Personal';
				switch (basename($_SERVER['SCRIPT_NAME'])) {
					case 'index.php':
						$this->crumbs[1] = 'Lista de empleados';
						break;
					case 'nuevo-empleado.php':
						$this->crumbs[1] = 'Nuevo empleado';
						break;
					case 'codigo-seguridad.php':
						$this->crumbs[1] = 'Código de seguridad';
						break;
					default:
						$this->crumbs[1] = '';
						break;
				}
				$this->sidebar = null;
			if($_SESSION['business']['id_rol'] == 4){
				$this->sidebar = 
						'<li'.$this->set_active_sidebar_tab('index.php').'>
							<a href="'.HOST.'/negocio/personal/">
								<span class="icon"><i class="fa fa-list-alt"></i></span>
								<span class="title">Personal</span>
								<span class="subtitle">Ver todo el personal</span>
							</a>
						</li>
						<li'.$this->set_active_sidebar_tab('nuevo-empleado.php').'>
							<a href="'.HOST.'/negocio/personal/nuevo-empleado">
								<span class="icon"><i class="fa fa-user-plus"></i></span>
								<span class="title">Nuevo empleado</span>
								<span class="subtitle">Dar de alta nuevo empleado</span>
							</a>
						</li>';
				}
				$this->sidebar .= 
						'<li'.$this->set_active_sidebar_tab('codigo-seguridad.php').'>
							<a href="'.HOST.'/negocio/personal/codigo-seguridad">
								<span class="icon"><i class="fa fa-user-plus"></i></span>
								<span class="title">C&oacute;digo de seguridad</span>
								<span class="subtitle">Cambiar tu c&oacute;digo</span>
							</a>
						</li>';
				break;
			case 'contenidos':
				$this->crumbs[0] = 'Publicaciones y contenidos';
				switch (basename($_SERVER['SCRIPT_NAME'])) {
					case 'galeria.php':
						$this->crumbs[1] = 'Galería de imagenes';
						break;
					case 'nueva-publicacion.php':
						$this->crumbs[1] = 'Nueva publicación';
						break;
					case 'publicaciones.php':
						$this->crumbs[1] = 'Publicaciones';
						break;
					case 'nuevo-evento.php':
						$this->crumbs[1] = 'Nuevo evento';
						break;
					case 'eventos.php':
						$this->crumbs[1] = 'Eventos';
						break;
					default:
						$this->crumbs[1] = '';
						break;
				}
				$this->sidebar =
						'<li'.$this->set_active_sidebar_tab('galeria.php').'>
							<a href="'.HOST.'/negocio/contenidos/galeria">
								<span class="icon"><i class="fa fa-camera"></i></span>
								<span class="title">Galer&iacute;a</span>
								<span class="subtitle">Im&aacute;genes del perfil</span>
							</a>
						</li>
						<li'.$this->set_active_sidebar_tab('nueva-publicacion.php').'>
							<a href="'.HOST.'/negocio/contenidos/nueva-publicacion">
								<span class="icon"><i class="fa fa-font"></i></span>
								<span class="title">Nueva publicaci&oacute;n</span>
								<span class="subtitle">Crea contenido</span>
							</a>
						</li>
						<li'.$this->set_active_sidebar_tab('publicaciones.php').'>
							<a href="'.HOST.'/negocio/contenidos/publicaciones">
								<span class="icon"><i class="fa fa-list"></i></span>
								<span class="title">Publicaciones</span>
								<span class="subtitle">Ver todas las publicacions</span>
							</a>
						</li>
						<li'.$this->set_active_sidebar_tab('nuevo-evento.php').'>
							<a href="'.HOST.'/negocio/contenidos/nuevo-evento">
								<span class="icon"><i class="fa fa-calendar-plus-o"></i></span>
								<span class="title">Nuevo Evento</span>
								<span class="subtitle">Crea un evento</span>
							</a>
						</li>
						<li'.$this->set_active_sidebar_tab('eventos.php').'>
							<a href="'.HOST.'/negocio/contenidos/eventos">
								<span class="icon"><i class="fa fa-calendar"></i></span>
								<span class="title">Eventos</span>
								<span class="subtitle">Ver todos los eventos</span>
							</a>
						</li>';
				break;
			case 'preferencias':
				$this->crumbs[0] = 'Preferencias';
				switch (basename($_SERVER['SCRIPT_NAME'])) {
					case 'informacion.php':
						$this->crumbs[1] = 'Información, contacto y ubicación';
						break;
					case 'logo-y-portada.php':
						$this->crumbs[1] = 'Logo y portada';
						break;
					case 'redes-sociales.php':
						$this->crumbs[1] = 'Redes sociales';
						break;
					case 'divisa-y-precios.php':
						$this->crumbs[1] = 'Divisa y rango de precios';
						break;
					case 'horario.php':
						$this->crumbs[1] = 'Horario de trabajo';
						break;
					case 'amenidades-y-pagos.php':
						$this->crumbs[1] = 'Amenidades y formas de pago';
						break;
					case 'video.php':
						$this->crumbs[1] = 'Video';
						break;
					default:
						$this->crumbs[1] = '';
						break;
				}
				$this->sidebar =
						'<li'.$this->set_active_sidebar_tab('informacion.php').'>
							<a href="'.HOST.'/negocio/preferencias/informacion">
								<span class="icon"><i class="fa fa-info-circle"></i></span>
								<span class="title">Informaci&oacute;n</span>
								<span class="subtitle">Detalles del negocio</span>
							</a>
						</li>
						<li'.$this->set_active_sidebar_tab('logo-y-portada.php').'>
							<a href="'.HOST.'/negocio/preferencias/logo-y-portada">
								<span class="icon"><i class="fa fa-picture-o"></i></span>
								<span class="title">Logo y portada</span>
								<span class="subtitle">Personaliza tu perfil</span>
							</a>
						</li>
						<li'.$this->set_active_sidebar_tab('amenidades-y-pagos.php').'>
							<a href="'.HOST.'/negocio/preferencias/amenidades-y-pagos">
								<span class="icon"><i class="fa fa-flag"></i></span>
								<span class="title">Amenidades</span>
								<span class="subtitle">&amp; Formas de pago</span>
							</a>
						</li>
						<li'.$this->set_active_sidebar_tab('horario.php').'>
							<a href="'.HOST.'/negocio/preferencias/horario">
								<span class="icon"><i class="fa fa-clock-o"></i></span>
								<span class="title">Horario de trabajo</span>
								<span class="subtitle">Horario de la semana</span>
							</a>
						</li>
						<li'.$this->set_active_sidebar_tab('divisa-y-precios.php').'>
							<a href="'.HOST.'/negocio/preferencias/divisa-y-precios">
								<span class="icon"><i class="fa fa-usd"></i></span>
								<span class="title">Divisa y precios</span>
								<span class="subtitle">Rango de precios</span>
							</a>
						</li>
						<li'.$this->set_active_sidebar_tab('redes-sociales.php').'>
							<a href="'.HOST.'/negocio/preferencias/redes-sociales">
								<span class="icon"><i class="fa fa-facebook"></i></span>
								<span class="title">Redes sociales</span>
								<span class="subtitle">Comparte tus redes</span>
							</a>
						</li>
						<li'.$this->set_active_sidebar_tab('video.php').'>
							<a href="'.HOST.'/negocio/preferencias/video">
								<span class="icon"><i class="fa fa-video-camera"></i></span>
								<span class="title">Video</span>
								<span class="subtitle">Publica un video</span>
							</a>
						</li>';
				break;
			default:
				$this->crumbs[0] = '';
				$this->crumbs[1] = '';
				break;
		}
		return;
	}

	private function set_active_tab($tab = null){
		if(basename(dirname($_SERVER['SCRIPT_NAME'])) == $tab){
			$class = ' class="active"';
		}else{
			$class= '';
		}
		return $class;
	}

	private function set_active_sidebar_tab($tab = null){
		if(basename($_SERVER['SCRIPT_NAME']) == $tab){
			$class = ' class="active"';
		}else{
			$class= '';
		}
		return $class;
	}

	public function get_no_indexing_header(array $properties){
		$title = _safe($properties['title']);
		$description = _safe($properties['description']);
		$html = 
'<!DOCTYPE html>
<html lang="es_mx">
<head>
	<meta charset="utf-8" />
	<meta name="language" content="english" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	<meta name="apple-mobile-web-app-capable" content="yes" />

	<meta name="robots" content="none" />
	<meta name="robots" content="none, nindex, nfollow" />
	<meta name="robots" content="noindex, nofollow" />
	<meta name="googlebot" content="none" />
	<meta name="googlebot" content="none, noindex, nofollow" />
	<meta name="googlebot" content="noindex, nofollow" />
	<meta content="none" name="yahoo-slurp" />
	<meta name="yahoo-slurp" content="none, noindex, nofollow" />
	<meta name="yahoo-slurp" content="noindex, nofollow" />
	<meta name="msnbot" content="noindex, nofollow" />
	<meta name="ia_archiver" content="none" />
	<meta name="googlebot-image" content="none" />
	<meta name="robots" content="none" />

	<link href="http://fonts.googleapis.com/css?family=Nunito:300,400,700" rel="stylesheet" type="text/css" />

	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/font-awesome/css/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/owl.carousel/assets/owl.carousel.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/colorbox/example1/colorbox.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/bootstrap-select/bootstrap-select.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/bootstrap-fileinput/fileinput.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/fontawesome-iconpicker/css/fontawesome-iconpicker.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/css/superlist.css" />

		<link rel="icon" type="image/png" href="'.HOST.'/assets/img/favicon.png" >
	<title>'.$title.'</title>
	<meta name="description" content="'.$description.'" />
</head>
';
		return $html;
	}

	public function get_navbar(){
		$html =
'<body class="">
 <script>
  (function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,"script","https://www.google-analytics.com/analytics.js","ga");

  ga("create", "UA-57870544-2", "auto");
  ga("send", "pageview");

</script>
<div class="page-wrapper">
	<header class="header header-minimal">
		<div class="header-wrapper">
			<div class="container-fluid">
				<div class="header-inner">
					<div class="header-logo">
						<a href="'.HOST.'/">

							<div class="logo" alt="Travel Points">
										<style>
											.logo{
												background-image: url('.HOST.'/assets/img/logo.svg)									
											}
										</style>
							</div>
							
						</a> 
					</div><!-- /.header-logo -->
					<div class="header-content">
						<div class="header-bottom">
							<div class="header-button">
								<a href="'.HOST.'/contacto" class="header-button-inner mr20" data-toggle="tooltip" data-placement="bottom" title="Contacta Travel Points">
									<i class="fa fa-envelope"></i>
								</a>
							</div>
							<div class="header-button">
								<a href="http://www.facebook.com" target="_blank" class="header-button-inner blue" data-toggle="tooltip" data-placement="bottom" title="Travel Points Facebook">
									<i class="fa fa-facebook"></i>
								</a>
							</div>
							<div class="header-button">
								<a href="'.HOST.'/tienda/" class="header-button-inner pink" data-toggle="tooltip" data-placement="bottom" title="Tienda de Regalos">
									<i class="fa fa-gift"></i>
								</a>
							</div>
							<div class="header-button">
								<a href="'.HOST.'/que-es-travel-points" class="header-button-inner green" data-toggle="tooltip" data-placement="bottom" title="¿Qu&eacute; es Travel Points?">
									<i class="fa fa-question"></i>
								</a>
							</div>
							<ul class="header-nav-primary nav nav-pills collapse navbar-collapse">
								<li class="visible-xs"><a href="'.HOST.'/que-es-travel-points">¿Qu&eacute; es Travel Points</a></li>
								<li class="visible-xs"><a href="'.HOST.'/tienda/">Tienda de Regalos</a></li>
								<li class="visible-xs"><a href="http://www.facebook.com" target="_blank">Travel Points Facebook</a></li>
								<li class="visible-xs"><a href="'.HOST.'/contacto">Contacta Travel Points</a></li>
							</ul>
							<button class="navbar-toggle collapsed" type="button" data-toggle="collapse" data-target=".header-nav-primary">
								<span class="sr-only">Toggle navigation</span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
							</button>
							<div class="header-nav-user">
								<div class="dropdown">
								
									<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
										<div class="user-image">
											<img src="'.HOST.'/assets/img/user_profile/'.$this->user['image'].'">
										</div><!-- /.user-image -->
										<span class="header-nav-user-name">'.$this->user['alias'].'</span> <i class="fa fa-chevron-down"></i>
									</button>

									<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
										<li><a href="'.HOST.'/socio/">Mi inicio</a></li>
										<li><a href="'.HOST.'/socio/perfil/">Mi perfil</a></li>';
								if($this->user['admin']){
									$html .= 
									'<li><a href="'.HOST.'/admin/">Panel Travel Points</a></li>';
								}
								$html .= 
										'<li><a href="'.HOST.'/logout">Cerrar sesi&oacute;n</a></li>
									</ul>
								</div><!-- /.dropdown -->
							</div><!-- /.header-nav-user -->
							'.$this->get_business_dropdown().'
						</div><!-- /.header-bottom -->
					</div><!-- /.header-content -->
				</div><!-- /.header-inner -->
			</div><!-- /.container -->
		</div><!-- /.header-wrapper -->
		<div class="header-statusbar">
			<div class="header-statusbar-inner">
				<div class="header-statusbar-left">
					<h1 class="logo-esmart">
						
						<span class="header-text">'.$this->business['name'].'</span>
					</h1>
				</div><!-- /.header-statusbar-left -->
				<div class="header-statusbar-right">
					<ul class="breadcrumb">
						<li class="hidden-xs hidden-sm"><a href="'.HOST.'/negocio/">Administrar Negocio</a></li>
						<li class="hidden-xs hidden-sm">'._safe($this->crumbs[0]).'</li>
						<li>'._safe($this->crumbs[1]).'</li>
					</ul>
				</div><!-- /.header-statusbar-right -->
			</div><!-- /.header-statusbar-inner -->
		</div><!-- /.header-statusbar -->
	</header><!-- /.header -->
	<div class="main">
		<div class="outer-admin">
			<div class="wrapper-admin">
				<div class="sidebar-admin">
					<ul>
						<li'.$this->set_active_tab('negocio').' data-toggle="tooltip" data-placement="right" title="Inicio"><a href="'.HOST.'/negocio/"><i class="fa fa-home"></i></a></li>
						<li'.$this->set_active_tab('ventas').' data-toggle="tooltip" data-placement="right" title="Ventas"><a href="'.HOST.'/negocio/ventas/"><i class="fa fa-money"></i></a></li>
						<li'.$this->set_active_tab('certificados').' data-toggle="tooltip" data-placement="right" title="Certificados de regalo"><a href="'.HOST.'/negocio/certificados/reservar"><i class="fa fa-gift"></i></a></li>';
		if($_SESSION['business']['id_rol'] == 4 || $_SESSION['business']['id_rol'] == 5){
			$html .= 	'<li'.$this->set_active_tab('contenidos').' data-toggle="tooltip" data-placement="right" title="Publicaciones y contenidos"><a href="'.HOST.'/negocio/contenidos/galeria"><i class="fa fa-globe"></i></a></li>';
		}
		if($_SESSION['business']['id_rol'] == 4 || $_SESSION['business']['id_rol'] == 5 || $_SESSION['business']['id_rol'] == 6){
			if($_SESSION['business']['id_rol'] == 4){
				$html .= 	'<li'.$this->set_active_tab('personal').' data-toggle="tooltip" data-placement="right" title="Personal"><a href="'.HOST.'/negocio/personal/"><i class="fa fa-user-circle-o"></i></a></li>
							<li'.$this->set_active_tab('preferencias').' data-toggle="tooltip" data-placement="right" title="Preferencias de negocio"><a href="'.HOST.'/negocio/preferencias/informacion"><i class="fa fa-cog"></i></a></li>';
				
			}else{
				
			}
		}
		if($_SESSION['business']['id_rol'] == 4 || $_SESSION['business']['id_rol'] == 5){
			$html .= 	'<li'.$this->set_active_tab('reportes').' data-toggle="tooltip" data-placement="right" title="Reportes"><a href="'.HOST.'/negocio/reportes/"><i class="fa fa-line-chart"></i></a></li>';
		}
		$html .=	'</ul>
				</div><!-- /.sidebar-admin-->
				<div class="sidebar-secondary-admin">
					<ul>
						'.$this->sidebar.'
					</ul>
				</div><!-- /.sidebar-secondary-admin -->
				<div class="content-admin">
					<div class="content-admin-wrapper">
						<div class="content-admin-main">
							<div class="content-admin-main-inner">
								<div class="container-fluid">
';
		return $html;
	}

	public function get_footer(){
		$html = 
'								</div><!-- /.container-fluid -->
							</div><!-- /.content-admin-main-inner -->
						</div><!-- /.content-admin-main -->
						<div class="content-admin-footer">
							<div class="container-fluid">
								<div class="content-admin-footer-inner">
									&copy; '.date("Y").' Todos los derechos reservados.
								</div><!-- /.content-admin-footer-inner -->
							</div><!-- /.container-fluid -->
						</div><!-- /.content-admin-footer  -->
					</div><!-- /.content-admin-wrapper -->
				</div><!-- /.content-admin -->
			</div><!-- /.wrapper-admin -->
		</div><!-- /.outer-admin -->
	</div><!-- /.main -->
</div><!-- /.page-wrapper -->
<script src="'.HOST.'/assets/js/jquery.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/js/moment.min.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/js/map.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/libraries/bootstrap-sass/javascripts/bootstrap/collapse.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/libraries/bootstrap-sass/javascripts/bootstrap/carousel.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/libraries/bootstrap-sass/javascripts/bootstrap/transition.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/libraries/bootstrap-sass/javascripts/bootstrap/dropdown.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/libraries/bootstrap-sass/javascripts/bootstrap/tooltip.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/libraries/bootstrap-sass/javascripts/bootstrap/tab.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/libraries/bootstrap-sass/javascripts/bootstrap/alert.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/libraries/bootstrap-sass/javascripts/bootstrap/modal.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/libraries/colorbox/jquery.colorbox-min.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/libraries/flot/jquery.flot.min.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/libraries/flot/jquery.flot.spline.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/libraries/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/libraries/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCNWsVH2kmknm6knGSRKDuzGeMWM1PT6gA&amp;libraries=weather,geometry,visualization,places,drawing" type="text/javascript"></script>
<script type="text/javascript" src="'.HOST.'/assets/libraries/jquery-google-map/infobox.js"></script>
<script type="text/javascript" src="'.HOST.'/assets/libraries/jquery-google-map/markerclusterer.js"></script>
<script type="text/javascript" src="'.HOST.'/assets/libraries/jquery-google-map/jquery-google-map.js"></script>
<script type="text/javascript" src="'.HOST.'/assets/libraries/owl.carousel/owl.carousel.js"></script>
<script type="text/javascript" src="'.HOST.'/assets/libraries/bootstrap-fileinput/fileinput.min.js"></script>
<script type="text/javascript" src="'.HOST.'/assets/libraries/fontawesome-iconpicker/js/fontawesome-iconpicker.min.js"></script>
<script type="text/javascript" src="'.HOST.'/assets/js/typeahead.bundle.js"></script>
<script src="'.HOST.'/assets/js/superlist.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/js/custom.js" type="text/javascript"></script>

</body>
</html>';
		return $html;
	}

	private function catch_errors($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\business_includes.txt', '['.date('d/M/Y h:i:s A').' on '.$method.' on line '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		return;
	}
}
?>