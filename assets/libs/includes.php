<?php # Desarrollado por Info Channel
namespace assets\libs;
use PDO;

class includes {
	private $con;
	private $user = array(
		'id' => null,
		'username' => null,
		'esmarties' => null,
		'image' => null,
		'alias' => null,
		'pending_review' => 0,
		'pending_request' => 0,
	);
	private $admin = array(
		'pending_request' => 0
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		if(isset($_SESSION['user']['id_usuario'])){
			$this->user['id'] = $_SESSION['user']['id_usuario'];
			$this->load_data();
		}
		return;
	}

	private function load_data(){
		$query = "SELECT username, imagen, nombre, apellido, esmarties FROM usuario WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->user['username'] = _safe($row['username']);
			$this->user['esmarties'] = _safe($row['esmarties']);
			if(!empty($row['imagen'])){
				$this->user['image'] = _safe($row['imagen']);
			}else{
				$this->user['image'] = 'default.jpg';
			}
			if(empty($row['nombre']) && empty($row['apellido'])){
				$this->user['alias'] = $this->user['username'];
			}else{
				$this->user['alias'] = _safe($row['nombre'].' '.$row['apellido']);
			}
		}
		$query = "SELECT 
			(SELECT COUNT(id_venta) FROM negocio_venta WHERE id_usuario = :id_usuario) - 
			(SELECT COUNT(o.id_opinion) 
			FROM opinion o 
			INNER JOIN negocio_venta nv ON o.id_venta = nv.id_venta 
			WHERE nv.id_usuario = :id_usuario2) 
			as pendientes";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
			$stmt->bindValue(':id_usuario2', $this->user['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->user['pending_review'] = $row['pendientes'];
		}
		$query = "SELECT COUNT(*) FROM solicitud_negocio WHERE id_usuario = :id_usuario AND mostrar_usuario = 3";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->user['pending_request'] = $row['COUNT(*)'];
		}
		if($_SESSION['user']['id_rol'] == 1 || $_SESSION['user']['id_rol'] == 2 || $_SESSION['user']['id_rol'] == 3){
			$query = "SELECT COUNT(*) FROM solicitud_negocio WHERE situacion = 2";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				$this->admin['pending_request'] = $row['COUNT(*)'];
			}
		}
		return;
	}

	public function get_user_sidebar(){
		$html = 
		'<div class="widget">
			<ul class="menu-advanced">
				<li'.$this->set_active_tab('perfil').'>
					<a href="'.HOST.'/socio/perfil/">
						<img src="'.HOST.'/assets/img/user_profile/'.$this->user['image'].'" alt="">
						'.$this->user['alias'].'
					</a>
				</li>
				<li'.$this->set_active_tab('socio').'><a href="'.HOST.'/socio/"><i class="fa fa-home"></i> Inicio</a></li>
				<li'.$this->set_active_tab('negocios').'><a href="'.HOST.'/socio/negocios/"><i class="fa fa-briefcase"></i> Negocios</a></li>
				<li'.$this->set_active_tab('consumos').'><a href="'.HOST.'/socio/consumos/"><i class="fa fa-credit-card"></i> Consumos</a></li>
				<li'.$this->set_active_tab('certificados').'><a href="'.HOST.'/socio/certificados/"><i class="fa fa-gift"></i> Certificados</a></li>
				<li'.$this->set_active_tab('compras').'><a href="'.HOST.'/socio/compras/"><i class="fa fa-shopping-bag"></i> Compras</a></li>
			</ul>
		</div>';
		switch (basename(dirname($_SERVER['SCRIPT_NAME']))) {
			case 'perfil':
				$html .=
		'<div class="widget">
			<ul class="menu-advanced">
				<li'.$this->set_active_sidebar_tab('index.php').'><a href="'.HOST.'/socio/perfil/"><i class="fa fa-user"></i> Perfil de socio</a></li>
				<li'.$this->set_active_sidebar_tab('invitados.php').'><a href="'.HOST.'/socio/perfil/invitados"><i class="fa fa-user-plus"></i> Mis invitados</a></li>
				<li'.$this->set_active_sidebar_tab('esmartties.php').'><a href="'.HOST.'/socio/perfil/esmartties"><i class="fa fa-exchange"></i> Mis eSmartties</a></li>
				<li'.$this->set_active_sidebar_tab('editar.php').'><a href="'.HOST.'/socio/perfil/editar"><i class="fa fa-pencil"></i> Editar informaci&oacute;n</a></li>
				<li'.$this->set_active_sidebar_tab('cambiar-contrasena.php').'><a href="'.HOST.'/socio/perfil/cambiar-contrasena"><i class="fa fa-key"></i> Cambiar contrase&ntilde;a</a></li>
				<li'.$this->set_active_sidebar_tab('desactivar-cuenta.php').'><a href="'.HOST.'/socio/perfil/desactivar-cuenta"><i class="fa fa-times-circle"></i> Desactivar cuenta</a></li>
			</ul>
		</div>';
			break;
			case 'negocios':
			if($this->user['pending_request'] > 0){
				$noti = '<span class="notification">'.$this->user['pending_request'].'</span>';
			}else{
				$noti = '';
			}
			if($_SESSION['user']['id_rol']==8) {
				$html .=
		'<div class="widget">
			<ul class="menu-advanced">
				<li'.$this->set_active_sidebar_tab('siguiendo.php').'><a href="'.HOST.'/socio/negocios/siguiendo"><i class="fa fa-bookmark"></i> Siguiendo</a></li>
				<li'.$this->set_active_sidebar_tab('recomendados.php').'><a href="'.HOST.'/socio/negocios/recomendados"><i class="fa fa-heart"></i> Recomendados</a></li>
			</ul>
		</div>';
			}
			else {
				$html .=
		'<div class="widget">
			<ul class="menu-advanced">
				<li'.$this->set_active_sidebar_tab('index.php').'><a href="'.HOST.'/socio/negocios/"><i class="fa fa-user"></i> Mis negocios</a></li>
				<li'.$this->set_active_sidebar_tab('afiliar-negocio.php').'><a href="'.HOST.'/socio/negocios/afiliar-negocio"><i class="fa fa-plus-circle"></i> Afiliar mi negocio</a></li>
				<li'.$this->set_active_sidebar_tab('siguiendo.php').'><a href="'.HOST.'/socio/negocios/siguiendo"><i class="fa fa-bookmark"></i> Siguiendo</a></li>
				<li'.$this->set_active_sidebar_tab('recomendados.php').'><a href="'.HOST.'/socio/negocios/recomendados"><i class="fa fa-heart"></i> Recomendados</a></li>
				<li'.$this->set_active_sidebar_tab('solicitudes.php').'><a href="'.HOST.'/socio/negocios/solicitudes"><i class="fa fa-file"></i> Solicitudes enviadas'.$noti.'</a></li>
			</ul>
		</div>';
			}
				break;
			case 'consumos':
				if($this->user['pending_review'] > 0){
					$noti = '<span class="notification">'.$this->user['pending_review'].'</span>';
				}else{
					$noti = '';
				}
				$html .=
		'<div class="widget">
			<ul class="menu-advanced">
				<li'.$this->set_active_sidebar_tab('index.php').'><a href="'.HOST.'/socio/consumos/"><i class="fa fa-money"></i> Consumos'.$noti.'</a></li>
				<li'.$this->set_active_sidebar_tab('opiniones.php').'><a href="'.HOST.'/socio/consumos/opiniones"><i class="fa fa-bullhorn"></i> Opinions | Opiniones</a></li>
			</ul>
		</div>';
				break;
			case 'certificados':
				$html .=
		'<div class="widget">
			<ul class="menu-advanced">
				<li'.$this->set_active_sidebar_tab('index.php').'><a href="'.HOST.'/socio/certificados/"><i class="fa fa-star"></i> Whislist | Lista de deseos</a></li>
				<li'.$this->set_active_sidebar_tab('canjeados.php').'><a href="'.HOST.'/socio/certificados/canjeados"><i class="fa fa-check-circle"></i> Canjeados</a></li>
			</ul>
		</div>';
				break;
			case 'compras':
				$html .=
		'<div class="widget">
			<ul class="menu-advanced">
				<li'.$this->set_active_sidebar_tab('index.php').'><a href="'.HOST.'/socio/compras/"><i class="fa fa-shopping-bag"></i> Compras en tienda</a></li>
			</ul>
		</div>';
				break;
		}
		return $html;
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
<head><meta http-equiv="Content-Type" content="text/html; charset=euc-jp">
	
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

	<link rel="shortcut icon" href="'.HOST.'/assets/img/favicon.png">

	<title>'.$title.'</title>
	<meta name="description" content="'.$description.'" />
</head>
';

		return $html;
	}

	public function get_main_navbar(){
		if($this->user['id'] && basename(dirname($_SERVER['SCRIPT_NAME'])) == 'tienda'){
			$esm = number_format((float)$this->user['esmarties'], 2, '.', '');
			$e = '<li><a href="'.HOST.'/tienda/">e$ '.$esm.'</a></li>';
		}else{
			$e = '';
		}
		$html =
'<body class="">
<div id="fb-root"></div>
<div class="page-wrapper">
	<header class="header">
		<div class="header-wrapper">
			<div class="container">
				<div class="header-inner">
					<div class="header-logo">
						<a href="'.HOST.'/">
							<img src="'.HOST.'/assets/img/logo.png" alt="Red Card Logo">
						</a>
					</div><!-- /.header-logo -->
					<div class="header-content">
						<div class="header-bottom">
							<div class="header-button">
								<a href="'.HOST.'/contacto" class="header-button-inner" data-toggle="tooltip" data-placement="bottom" title="Contact | Contacto">
									<i class="fa fa-envelope"></i>
								</a>
							</div>
							
							<div class="header-button">
								<a href="'.HOST.'/tienda/" class="header-button-inner pink" data-toggle="tooltip" data-placement="bottom" title="Gift Store | Tienda de Regalos">
									<i class="fa fa-gift"></i>
								</a>
							</div>
							<div class="header-button">
								<a href="'.HOST.'/que-es-esmart-club" class="header-button-inner green" data-toggle="tooltip" data-placement="bottom" title="What is Red Card | ¿Qu&eacute; es Red Card?">
									<i class="fa fa-question"></i>
								</a>
							</div>';
							if(isset($_SESSION['user']['id_usuario'])){
								$html .= 
							'<ul class="header-nav-primary nav nav-pills collapse navbar-collapse">
								'.$e.'
								<li class="visible-xs"><a href="'.HOST.'/que-es-esmart-club">What is Red Card | ¿Qu&eacute; es Red Card?</a></li>
								<li class="visible-xs"><a href="'.HOST.'/tienda/">Gift Store | Tienda de Regalos</a></li>
								
								<li class="visible-xs"><a href="'.HOST.'/contacto">Contact | Contacto</a></li>
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
											<img src="'.HOST.'/assets/img/user_profile/'.$this->user['image'].'">';
							if($this->user['pending_review'] > 0 || $this->user['pending_request'] > 0 || $this->admin['pending_request'] > 0){
								$html .= '<div class="notification"></div>';
							}
							if($this->user['pending_review'] > 0){
								$review = '<li><a href="'.HOST.'/socio/consumos/">Opiniones pendientes<div class="dropdown-notification"></div></a></li>';
							}else{
								$review = '';
							}
							if($this->user['pending_request'] > 0){
								$request = '<li><a href="'.HOST.'/socio/negocios/solicitudes">Solicitudes pendientes<div class="dropdown-notification"></div></a></li>';
							}else{
								$request = '';
							}
								$html .='</div><!-- /.user-image -->
										<span class="header-nav-user-name">'.$this->user['alias'].'</span> <i class="fa fa-chevron-down"></i>
									</button>
									<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
										<li><a href="'.HOST.'/socio/">Mi inicio</a></li>
										<li><a href="'.HOST.'/socio/perfil/">Mi perfil</a></li>
										'.$review.$request;
								if($_SESSION['user']['id_rol'] == 1 || $_SESSION['user']['id_rol'] == 2 || $_SESSION['user']['id_rol'] == 3){
									$html .= '<li><a href="'.HOST.'/admin/">Panel eSmart Club</a></li>';
									if($this->admin['pending_request'] > 0){
										$html .= '<li><a href="'.HOST.'/admin/negocios/solicitudes">Solicitudes pendientes<div class="dropdown-notification"></div></a></li>';
									}
								}
								if($_SESSION['user']['id_rol'] == 9){
									$html .= '<li><a href="'.HOST.'/admin/tienda/">Gift Store | Tienda de Regalos</a></li>';
								}
								if(isset($_SESSION['business'])){
									$html .= '<li><a href="'.HOST.'/negocio/">Panel de Negocio</a></li>';
								}
								$html .= 
										'<li><a href="'.HOST.'/logout">Logout | Cerrar sesi&oacute;n</a></li>
									</ul>
								</div><!-- /.dropdown -->
							</div><!-- /.header-nav-user -->
							';
							}else{
								$html .=
							'<ul class="header-nav-primary nav nav-pills collapse navbar-collapse">
								<li><a href="'.HOST.'/login">Login | Iniciar sesi&oacute;n</a></li>
								<li><a href="'.HOST.'/hazte-socio">Join | Hazte socio</a></li>
								<li class="visible-xs"><a href="'.HOST.'/que-es-esmart-club">What is Red Card | ¿Qu&eacute; es Red Card?</a></li>
								<li class="visible-xs"><a href="'.HOST.'/tienda/">Gift Store | Tienda de Regalos</a></li>
								
								<li class="visible-xs"><a href="'.HOST.'/contacto">Contacto</a></li>
							</ul>
							<button class="navbar-toggle collapsed" type="button" data-toggle="collapse" data-target=".header-nav-primary">
								<span class="sr-only">Toggle navigation</span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
							</button>';
							}
							$html .= '
						</div><!-- /.header-bottom -->
					</div><!-- /.header-content -->
				</div><!-- /.header-inner -->
			</div><!-- /.container -->
		</div><!-- /.header-wrapper -->
	</header><!-- /.header -->
';
		return $html;
	}

	public function get_main_footer(){
		$html = 
'	<footer class="footer">
		<div class="footer-top">
			<div class="container">
				<div class="row">
					<div class="col-xs-6 col-sm-2">
						<h2>Member | Socio</h2>
						<p><a href="'.HOST.'/login">Login | Inicia sesi&oacute;n</a></p>
						<p><a href="'.HOST.'/hazte-socio">Join | Hazte socio</a></p>
					</div><!-- /.col-* -->
					<div class="col-xs-6 col-sm-2">
						<h2>Negocio</h2>
						<p><a href="'.HOST.'/afiliar-negocio">Afilia tu negocio</a></p>
						<p><a href="'.HOST.'/por-que-afiliarme">¿Por qu&eacute; afiliarme?</a></p>
					</div><!-- /.col-* -->
					<div class="col-xs-12 col-sm-4">
						<h2>Contacto</h2>
						<p>
							Marina Vallarta Business Center, Oficina 204, Plaza Marina.<br>
							Puerto Vallarta, M&eacute;xico.<br>
							01 800 400 INFO (4636), (322) 225 9635.<br>
							<a href="mailto:soporte@infochannel.si" target="_blank">soporte@infochannel.si</a>
						</p>
					</div><!-- /.col-* -->
					<div class="col-xs-12 col-sm-4">
						
					</div><!-- /.col-* -->
				</div><!-- /.row -->
			</div><!-- /.container -->
		</div><!-- /.footer-top -->
		<div class="footer-bottom">
			<div class="container">
				<div class="footer-bottom-left">
					&copy; 2018 All Rights Reserved | Todos los derechos reservados.
				</div><!-- /.footer-bottom-left -->
				<div class="footer-bottom-right">
					<ul class="nav nav-pills">
						<li><a href="'.HOST.'/">Home | Inicio</a></li>
						<li><a href="'.HOST.'/terminos-y-condiciones">Termns and Conditions | T&eacute;rminos y Condiciones</a></li>
						<li><a href="'.HOST.'/preguntas-frecuentes">FAQ | Preguntas Frecuentes</a></li>
						<li><a href="'.HOST.'/contacto">Contact | Contacto</a></li>
					</ul><!-- /.nav -->
				</div><!-- /.footer-bottom-right -->
			</div><!-- /.container -->
		</div>
	</footer><!-- /.footer -->
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
<script src="'.HOST.'/assets/js/custom.js" type="text/javascript"></script></body>
</html>';

		return $html;
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\main_includes.txt', '['.date('d/M/Y h:i:s A').' on '.$method.' on line '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		return;
	}
}
?>