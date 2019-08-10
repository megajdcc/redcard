<?php 
namespace Hotel\models;
use assets\libs\connection;
use assets\libs\FuncionesAcademia;

use PDO;

/**
 * @author Crespo jhonatan
 */ 
class Includes extends FuncionesAcademia{
	
	private $con,$conection;
	private $user = array(
		'id' => null,
		'username' => null,
		'image' => null,
		'alias' => null,
		'rol' => null,
		'pending_review' => 0,
		'pending_request' => 0
	);
	
	private $admin = array(
		'pending_request' => 0
	);
	private $sidebar = null;
	private $crumbs = array();
	

	private $hoteles = array('id' =>null ,
								'nombrehotel'=>null,
								'otroshoteles'=>array());



	public function __construct(connection $con){
		$this->con = $con->con;
		$this->conection = $con;

		parent::__construct($this->conection,'Hotel');

		$this->user['id'] = $_SESSION['user']['id_usuario'];

		$this->load_data();
		$this->load_sidebar();

		return;


	}

	private function load_data(){
		
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
			$rol = $row['id_rol'];


			if($rol == 1){
				$this->user['rol'] = "Super Administrador";
			}else if($rol == 2){
				$this->user['rol'] = "Administrador";
			}else{
				$this->user['rol'] = "Operador";
			}

		}


		// Hoteles Data
		$query = "SELECT h.id as idhoteles, h.nombre as nombrehotel FROM hotel h 
			INNER JOIN solicitudhotel sh ON h.id = sh.id_hotel
			WHERE sh.id_usuario = :usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':usuario',$this->user['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			if($_SESSION['id_hotel']  == $row['idhoteles']){
				$this->hoteles['nombrehotel'] = _safe($row['nombrehotel']);
			}else{
					$this->hoteles['otroshoteles'][$row['idhoteles']] = _safe($row['nombrehotel']);
				
			}
		}
		return;
		
	}
	private function gethoteles(){
		$html = null;
		if(!is_null($this->hoteles['otroshoteles'])){
		if(count($this->hoteles['otroshoteles']) > 0){
			$html =
			'<div class="header-nav-user">
				<div class="dropdown">
					<button class="btn btn-default dropdown-toggle mimic-header-nav-user-image" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
						<span>'.$this->hoteles['nombrehotel'].'</span> <i class="fa fa-chevron-down"></i>
					</button>
					<ul class="dropdown-menu reverse-dropdown" aria-labelledby="dropdownMenu1">';
			foreach ($this->hoteles['otroshoteles'] as $fila=>$value) {
				
				
				$html .= 
				'<form method="post" action="'.HOST.'/Hotel/">
					<li><a href="#" class="cambiar-hotel">'.$value.'</a></li>

					<input type="hidden" value="'.$fila.'" name="cambiar-hotel">
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
			
			case 'Hotel':
				$this->crumbs[0] = 'Inicio';
				switch (basename($_SERVER['SCRIPT_NAME'])) {
					case 'index.php':
						$this->crumbs[1] = 'Resumen';
						break;
					case 'reporte-de-ventas.php':
						$this->crumbs[1] = 'Estado de Cuenta';
						break;
					case 'comprobantes.php':
						$this->crumbs[1] = 'Comprobantes';
						break;
					default:
						$this->crumbs[1] = '';
						break;
				}
				$this->sidebar =
						'<li'.$this->set_active_sidebar_tab('index.php').'>
							<a href="'.HOST.'/Hotel/">
								<span class="icon"><i class="fa fa-tachometer"></i></span>
								<span class="title">Resumen</span>
								<span class="subtitle">Vistazo general</span>
							</a>
						</li>

						<li'.$this->set_active_sidebar_tab('reporte-de-ventas.php').'>
							<a href="'.HOST.'/Hotel/reporte-de-ventas">
								<span class="icon"><i class="fa fa-dollar"></i></span>
								<span class="title">Estado de Cuenta</span>
								<span class="subtitle">Movimientos</span>
							</a>
						</li>

						<li'.$this->set_active_sidebar_tab('comprobantes.php').'>
							<a href="'.HOST.'/Hotel/comprobantes">
								<span class="icon"><i class="fa fa-file"></i></span>
								<span class="title">Comprobantes</span>
								<span class="subtitle">de pago</span>
							</a>
						</li>
						';

			break;
		
			case 'usuarios':
				$this->crumbs[0] = 'Huespedes';
				switch (basename($_SERVER['SCRIPT_NAME'])) {
					case 'index.php':
						$this->crumbs[1] = 'Listado';
						break;
					case 'nuevousuario.php':
						$this->crumbs[1] = 'Nuevo Usuario';
						break;
					default:
						$this->crumbs[1] = '';
						break;
				}
				$this->sidebar =
						'<li'.$this->set_active_sidebar_tab('index.php').'>
							<a href="'.HOST.'/Hotel/usuarios/">
								<span class="icon"><i class="fa fa-list"></i></span>
								<span class="title">Huespedes</span>
								<span class="subtitle">Todos los usuarios</span>
							</a>
						</li>
						<li'.$this->set_active_sidebar_tab('nuevousuario.php').'>
							<a href="'.HOST.'/Hotel/usuarios/nuevousuario">
								<span class="icon"><i class="fa fa-user-plus"></i></span>
								<span class="title">Nuevo Huesped</span>
								<span class="subtitle">Agregar</span>
							</a>
						</li>

						';

				break;

				case 'reportes':

				$this->crumbs[0] = 'Reportes';
				switch (basename($_SERVER['SCRIPT_NAME'])) {
					case 'reportedeventas.php':
						$this->crumbs[1] = 'de ventas';
						break;

					default:
						$this->crumbs[1] = '';
						break;
				}

				$this->sidebar =
						'<li'.$this->set_active_sidebar_tab('index.php').'>
							<a href="'.HOST.'/Hotel/">
								<span class="icon"><i class="fa fa-tachometer"></i></span>
								<span class="title">Resumen</span>
								<span class="subtitle">Vistazo general</span>
							</a>
						</li>

						<li'.$this->set_active_sidebar_tab('reporte-de-ventas.php').'>
							<a href="'.HOST.'/Hotel/reportes/reporte-de-ventas">
								<span class="icon"><i class="fa fa-dollar"></i></span>
								<span class="title">Estado de Cuenta</span>
								<span class="subtitle">Movimientos</span>
							</a>
						</li>

						<li'.$this->set_active_sidebar_tab('comprobantes.php').'>
							<a href="'.HOST.'/Hotel/comprobantes">
								<span class="icon"><i class="fa fa-file"></i></span>
								<span class="title">Comprobantes</span>
								<span class="subtitle">de pago</span>
							</a>
						</li>
						';
		
				break;

				case 'reservaciones':


					$this->crumbs[0] = 'Reservaciones';

					switch (basename($_SERVER['SCRIPT_NAME'])) {
						case 'index.php':
							$this->crumbs[1] = 'reservar';
							break;
					 	case 'reservaciones.php':
					 		$this->crumbs[1] = 'Historico'; 
					 		break;
					 	
					 	default:
					 		$this->crumbs[1] = '';
					 		break;
					 }


				$this->sidebar = 
						'<li'.$this->set_active_sidebar_tab('index.php').'>
								<a href="'.HOST.'/Hotel/reservaciones/">
								<span class="icon"><i class="fa fa-calendar-plus-o"></i></span>
								<span class="title">Reservar</span>
								<span class="subtitle">nueva reservaci&oacute;n</span>
								</a>
								</li>
								
						<li'.$this->set_active_sidebar_tab('reservaciones.php').'>
								<a href="'.HOST.'/Hotel/reservaciones/reservaciones">
								<span class="icon"><i class="fa fa-list"></i></span>
								<span class="title">Ver reservas</span>
								<span class="subtitle">Historico de reservaciones</span>
								</a>
								</li>'; 

				break;

				case 'promotores':

				$this->crumbs[0] = 'Promotores';
				switch (basename($_SERVER['SCRIPT_NAME'])) {
					case 'index.php':
						$this->crumbs[1] = 'Listado';
						break;
					case 'nuevopromotor.php':
						$this->crumbs[1] = 'Nuevo Promotor';
						break;
					default:
						$this->crumbs[1] = '';
						break;
				}
				$this->sidebar =
						'<li'.$this->set_active_sidebar_tab('index.php').'>
							<a href="'.HOST.'/Hotel/promotores/">
								<span class="icon"><i class="fa fa-list"></i></span>
								<span class="title">Promotores</span>
								<span class="subtitle">Todos los Promotores</span>
							</a>
						</li>
						<li'.$this->set_active_sidebar_tab('nuevopromotor.php').'>
							<a href="'.HOST.'/Hotel/promotores/nuevopromotor">
								<span class="icon"><i class="fa fa-user-plus"></i></span>
								<span class="title">Nuevo promotor</span>
								<span class="subtitle">Agregar</span>
							</a>
						</li>

						';

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

				<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/font-awesome/css/font-awesome.min.css"/>
				<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/owl.carousel/assets/owl.carousel.css"/>
				<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/colorbox/example1/colorbox.css"/>
				<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/bootstrap-select/bootstrap-select.min.css"/>
				<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
				<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/bootstrap-fileinput/fileinput.min.css"/>
				<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/bootstrap-slider/css/bootstrap-slider.min.css"/>
				<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/fontawesome-iconpicker/css/fontawesome-iconpicker.min.css"/>
				<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/css/superlist.css"/>
				<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/css/travelpoints.css"/>
				<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/datatables/datatables.min.css"/>
					<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/jquery-confirm/dist/jquery-confirm.min.css"/>

				


				<script src="'.HOST.'/assets/js/jquery.js" type="text/javascript"></script>
				<script src="'.HOST.'/assets/libraries/datatables/datatables.min.js"></script>
				<script src="'.HOST.'/assets/libraries/Highcharts/highcharts.js"></script>
				<script src="'.HOST.'/assets/libraries/Highcharts/modules/data.js"></script>
				<script src="'.HOST.'/assets/libraries/Highcharts/modules/exporting.js"></script>
				<script type="text/javascript" src="'.HOST.'/assets/libraries/bootstrap/js/popper.min.js"></script>
				

				<link rel="icon" type="image/png" href="'.HOST.'/assets/img/favicon.png">

				<title>'.$title.'</title>
				<meta name="description" content="'.$description.'" />
			</head>';
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
					<script>
				(function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,"script","https://www.google-analytics.com/analytics.js","ga");
				
				ga("create", "UA-57870544-2", "auto");
				ga("send", "pageview");
				
				</script>
				<div id="fb-root"></div>
				<div class="page-wrapper">
					<header class="header">
						<div class="header-wrapper">
							<div class="container">
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
										<div class="header-bottom">';

									
											
									$html .='<div class="header-button">
												<a href="'.HOST.'/contacto" class="header-button-inner" data-toggle="tooltip" data-placement="bottom" title="Contact | Contacto">
													<i class="fa fa-envelope"></i>
												</a>
											</div>
											
										
											<div class="header-button">
												<a href="'.HOST.'/que-es-travel-points" class="header-button-inner green" data-toggle="tooltip" data-placement="bottom" title="What is Travel Points | ¿Qu&eacute; es Travel Points?">
													<i class="fa fa-question"></i>
												</a>
											</div>';

											if(isset($_SESSION['user']['id_usuario'])){

												$html .= 
											'<ul class="header-nav-primary nav nav-pills collapse navbar-collapse">
												'.$e.'
												<li class="visible-xs"><a href="'.HOST.'/que-es-travel-points">What is Travel Points | ¿Qu&eacute; es Travel Points?</a></li>
												
												
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
											
												$html .='</div><!-- /.user-image -->
														<span class="header-nav-user-name">'.$this->user['alias'].'</span> <i class="fa fa-chevron-down"></i>
													</button>
													<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
														<li><a href="'.HOST.'/socio/">Mi inicio</a></li>';
															
												
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
												<li class="visible-xs"><a href="'.HOST.'/que-es-travel-points">What is Travel Points | ¿Qu&eacute; es Travel Points?</a></li>
												
												
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

	public function get_admin_navbar(){

		$html =
			'<body class=""> 
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
									<div class="header-bottom">';
									
										if($this->is_videos()){

												$html .= '<div class="header-button ">
													<button class="btn-academia header-button-inner mr20" data-toggle="tooltip" data-placement="bottom" title="Aprende de Travel Points"><i class="fa fa-graduation-cap"></i></button>
												</div>';

													$html.=	'<div class="header-button">
											<a href="'.HOST.'/contacto" class="header-button-inner " data-toggle="tooltip" data-placement="bottom" title="Contacta Travel Points">
												<i class="fa fa-envelope"></i>
											</a>
										</div>';
											}else{
												$html.=	'<div class="header-button">
											<a href="'.HOST.'/contacto" class="header-button-inner mr20" data-toggle="tooltip" data-placement="bottom" title="Contacta Travel Points">
												<i class="fa fa-envelope"></i>
											</a>
										</div>';
											}

								
										$html .='<div class="header-button">
											<a href="http://www.facebook.com/TravelPointsMX" target="_blank" class="header-button-inner blue" data-toggle="tooltip" data-placement="bottom" title="Travel Points Facebook">
												<i class="fa fa-facebook"></i>
											</a>
										</div>
										
										<div class="header-button">
											<a href="'.HOST.'/que-es-travel-points" class="header-button-inner green" data-toggle="tooltip" data-placement="bottom" title="¿Qu&eacute; es Travel Points?">
												<i class="fa fa-question"></i>
											</a>
										</div>
										<ul class="header-nav-primary nav nav-pills collapse navbar-collapse">
											<li class="visible-xs"><a href="'.HOST.'/que-es-travel-points">¿Qu&eacute; es Travel Points?</a></li>
											
											<li class="visible-xs"><a href="http://www.facebook.com" target="_blank">Travel points Facebook</a></li>
											<li class="visible-xs"><a href="'.HOST.'/contacto">Contacta Travel points</a></li>
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
													<li><a href="'.HOST.'/socio/perfil/">Mi perfil</a></li>
													';
				
											$html .= 
													'<li><a href="'.HOST.'/logout">Cerrar sesi&oacute;n</a></li>
												</ul>
											</div><!-- /.dropdown -->
										</div><!-- /.header-nav-user -->
										'.$this->gethoteles().'
									</div><!-- /.header-bottom -->
								</div><!-- /.header-content -->
							</div><!-- /.header-inner -->
						</div><!-- /.container -->
					</div><!-- /.header-wrapper -->
					<div class="header-statusbar">
						<div class="header-statusbar-inner">
							<div class="header-statusbar-left">
								<h1 class="logo-esmart">
									<span class="header-text ">Panel de control</span>
								</h1>

								<h1 class="logo-esmart">
								<span class="header-text">'.$_SESSION['nombrehotel']. ' | Código: '.$_SESSION['codigohotel'].'</span></h1>
							</div>
							<!-- /.header-statusbar-left -->
							<div class="header-statusbar-right">
								<ul class="breadcrumb">
								
									<li class="hidden-xs hidden-sm">'.$this->crumbs[0].'</li>
									<li>'.$this->crumbs[1].'</li>
								</ul>
							</div><!-- /.header-statusbar-right -->
						</div><!-- /.header-statusbar-inner -->
					</div><!-- /.header-statusbar -->
				</header><!-- /.header -->
				<div class="main">
					<div class="outer-admin">
						<div class="wrapper-admin">
							<div class="sidebar-admin">
					<ul>';
				if($_SESSION['user']['id_rol'] != 9){

			$html .=	'<li'.$this->set_active_tab('Hotel').' data-toggle="tooltip" data-placement="right" title="Inicio"><a href="'.HOST.'/Hotel/"><i class="fa fa-home"></i></a></li>
						
						<li'.$this->set_active_tab('usuarios').' data-toggle="tooltip" data-placement="right" title="Huespedes"><a href="'.HOST.'/Hotel/usuarios/"><i class="fa fa-user-circle-o"></i></a></li>

						<li'.$this->set_active_tab('reservaciones').' data-toggle="tooltip" data-placement="right" title="Reservaciones"><a href="'.HOST.'/Hotel/reservaciones/"><i class="fa fa-calendar-check-o"></i></a></li>
						';
				}


				$html .= 
						'<li'.$this->set_active_tab('promotores').' data-toggle="tooltip" data-placement="right" title="Promotores"><a href="'.HOST.'/Hotel/promotores/"><i class="fa fa-users"></i></a></li>';

			$html .=	'
					</ul>
				</div><!-- /.sidebar-admin-->
				<div class="sidebar-secondary-admin">
					<ul>
						'.$this->sidebar.'
					</ul>
				</div>
							<section class="content-academia p-socio p-hotel">
														
														'.$this->getVideos().'
														
							</section>
							'.$this->getModal().'
				<div class="content-admin contenido-home">
					<div class="content-admin-wrapper">
						<div class="content-admin-main">
							<div class="content-admin-main-inner">
								<div class="container-fluid">
		';
		return $html;
	}


	protected function getVideos(){

		$result  = $this->capturarvideos($this->conection,'Hotel');

		return $result;
	}

	public function get_admin_footer(){
		$ano = date('Y');
		$html ='</div><!-- /.container-fluid -->

													</div><!-- /.content-admin-main-inner -->

													</div><!-- /.content-admin-main -->
													

													<div class="content-admin-footer">
													<div class="container-fluid">
													<div class="content-admin-footer-inner">
													&copy; '.$ano.' Todos los derechos reservados.
													</div><!-- /.content-admin-footer-inner -->
													</div><!-- /.container-fluid -->
													</div><!-- /.content-admin-footer  -->
													</div><!-- /.content-admin-wrapper -->
													</div><!-- /.content-admin -->
													</div><!-- /.wrapper-admin -->
													</div><!-- /.outer-admin -->
													</div><!-- /.main -->
													</div><!-- /.page-wrapper -->
													
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
													<script src="'.HOST.'/assets/libraries/bootstrap-slider/js/bootstrap-slider.min.js" type="text/javascript"></script>
													<script src="'.HOST.'/assets/libraries/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
													<script src="'.HOST.'/assets/libraries/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
													<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCNWsVH2kmknm6knGSRKDuzGeMWM1PT6gA&amp;libraries=weather,geometry,visualization,places,drawing" type="text/javascript"></script>
													<script type="text/javascript" src="'.HOST.'/assets/libraries/jquery-google-map/infobox.js"></script>
													<script type="text/javascript" src="'.HOST.'/assets/libraries/jquery-google-map/markerclusterer.js"></script>
													<script type="text/javascript" src="'.HOST.'/assets/libraries/jquery-google-map/jquery-google-map.js"></script>
													<script type="text/javascript" src="'.HOST.'/assets/libraries/owl.carousel/owl.carousel.js"></script>
													<script type="text/javascript" src="'.HOST.'/assets/libraries/bootstrap-fileinput/fileinput.min.js"></script>
													<script type="text/javascript" src="'.HOST.'/assets/libraries/font-awesome/js/fontawesome.min.js"></script>
													<script type="text/javascript" src="'.HOST.'/assets/libraries/fontawesome-iconpicker/js/fontawesome-iconpicker.min.js"></script>
													<script type="text/javascript" src="'.HOST.'/assets/libraries/jquery-confirm/dist/jquery-confirm.min.js"></script>

													<script type="text/javascript" src="'.HOST.'/assets/js/typeahead.bundle.js"></script>
													<script src="'.HOST.'/assets/js/superlist.js" type="text/javascript"></script>
													<script src="'.HOST.'/assets/js/custom.js" type="text/javascript"></script>
													</body>
													</html>';
												return $html;
	}

	private function catch_errors($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\admin_includes.txt', '['.date('d/M/Y h:i:s A').' on '.$method.' on line '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		return;
	}
}
?>
