<?php 

namespace admin\libs;
use assets\libs\connection;
use PDO;

class includes {
	private $con;
	private $user = array(
		'id' => null,
		'username' => null,
		'image' => null,
		'alias' => null,
		'rol' => null
	);
	private $admin = array(
		'pending_request' => 0,
		'solicitudes_pendiente_perfiles' => 0,
		'solicitudes_pendiente_retiros' => 0
	);
	private $sidebar = null;
	private $crumbs = array();
	

	public function __construct(connection $con , $boolean = false){
		$this->con = $con->con;
		$this->user['id'] = $_SESSION['user']['id_usuario'];

		$this->load_data();
		$this->load_sidebar($boolean);

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
		$query = "(SELECT COUNT(*) as cuenta, 'Negocio' as perfil FROM solicitud_negocio WHERE situacion = 2)
					UNION
					(SELECT COUNT(*) as cuenta, 'Hotel' as perfil FROM solicitudhotel where condicion = 0)
					UNION
					(SELECT COUNT(*) as cuenta, 'Franquiciatario' as perfil FROM solicitudfr where condicion = 0)
					UNION
					(SELECT COUNT(*) as cuenta, 'Referidor' as perfil FROM solicitudreferidor where condicion = 0)";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			
			if($row['perfil'] != 'Negocio'){
				$this->admin['solicitudes_pendiente_perfiles'] += $row['cuenta'];
			}else{
				$this->admin['pending_request'] += $row['cuenta'];
			}
		}

		$query = "select count(r.id) as retiros from retiro r where r.aprobado = 0";

		$stm = $this->con->prepare($query);
		$stm->execute();

		$this->admin['solicitudes_pendiente_retiros'] = $stm->fetch(PDO::FETCH_ASSOC)['retiros'];

 		return;
	}

	private function load_sidebar($boolean = false){
 
		if($boolean){

		}
		switch (basename(dirname($_SERVER['SCRIPT_NAME']))) {
			case 'admin':
				$this->crumbs[0] = 'Inicio';
				switch (basename($_SERVER['SCRIPT_NAME'])) {
					case 'index.php':
						$this->crumbs[1] = 'Resumen';
						break;
					case 'reporte-de-ventas.php':
						$this->crumbs[1] = 'Estado de Cuenta';
						break;
					default:
						$this->crumbs[1] = '';
						break;
				}
				if($_SESSION['user']['id_rol'] == 1 || $_SESSION['user']['id_rol'] == 2){
				$this->sidebar =
						'<li'.$this->set_active_sidebar_tab('index.php').'>
							<a href="'.HOST.'/admin/">
								<span class="icon"><i class="fa fa-tachometer"></i></span>
								<span class="title">Resumen</span>
								<span class="subtitle">Vistazo general</span>
							</a>
						</li>
						<li'.$this->set_active_sidebar_tab('reporte-de-ventas.php').'>
							<a href="'.HOST.'/admin/reporte-de-ventas">
								<span class="icon"><i class="fa fa-dollar"></i></span>
								<span class="title">Estado de Cuenta</span>
								<span class="subtitle">Movimientos</span>
							</a>
						</li>';
					}
				break;

				case 'reservacion':
				$this->crumbs[0] = 'Reservaciones';

					switch (basename($_SERVER['SCRIPT_NAME'])) {
						case 'index.php':
							$this->crumbs[1] = 'Ver Reservas';
							break;
						default:
							$this->crumbs[1] = '';
							break;

				}

				$this->sidebar = '<li'.$this->set_active_sidebar_tab('index.php').'>
							<a href="'.HOST.'/admin/reservacion/">
								<span class="icon"><i class="fa fa-align-justify"></i></span>
								<span class="title">Reservaciones</span>
								<span class="subtitle">Ver Reservas</span>
							</a>
						</li>

						';
				break;

				
			case 'tienda':
				$this->crumbs[0] = 'Tienda';
				switch (basename($_SERVER['SCRIPT_NAME'])) {
					case 'index.php':
						$this->crumbs[1] = 'Productos';
						break;
					case 'nuevo-producto.php':
						$this->crumbs[1] = 'Nuevo producto';
						break;
					case 'ventas.php':
						$this->crumbs[1] = 'Ventas';
						break;
					
					default:
						$this->crumbs[1] = '';
						break;
				}
				$this->sidebar =
						'<li'.$this->set_active_sidebar_tab('index.php').'>
							<a href="'.HOST.'/admin/tienda/">
								<span class="icon"><i class="fa fa-list"></i></span>
								<span class="title">Productos</span>
								<span class="subtitle">Todos los productos</span>
							</a>
						</li>';
			if($_SESSION['user']['id_rol'] == 1 || $_SESSION['user']['id_rol'] == 2 || $_SESSION['user']['id_rol'] == 3){
				$this->sidebar .= 
						'<li'.$this->set_active_sidebar_tab('nuevo-producto.php').'>
							<a href="'.HOST.'/admin/tienda/nuevo-producto">
								<span class="icon"><i class="fa fa-plus"></i></span>
								<span class="title">Nuevo producto</span>
								<span class="subtitle">Agregar nuevo</span>
							</a>
						</li>';
			}
				$this->sidebar .=
						'<li'.$this->set_active_sidebar_tab('ventas.php').'>
							<a href="'.HOST.'/admin/tienda/ventas">
								<span class="icon"><i class="fa fa-th-list"></i></span>
								<span class="title">Ventas</span>
								<span class="subtitle">Ventas de la tienda</span>
							</a>
						</li>';
				break;
			case 'usuarios':
				$this->crumbs[0] = 'Usuarios';
				switch (basename($_SERVER['SCRIPT_NAME'])) {
					case 'index.php':
						$this->crumbs[1] = 'Listado';
						break;
					case 'administradores.php':
						$this->crumbs[1] = 'Administradores';
						break;
					case 'nuevo-administrador.php':
						$this->crumbs[1] = 'Nuevo administrador';
						break;
					default:
						$this->crumbs[1] = '';
						break;
				}
				if($_SESSION['user']['id_rol'] == 1 || $_SESSION['user']['id_rol'] == 2){
				$this->sidebar =
						'<li'.$this->set_active_sidebar_tab('index.php').'>
							<a href="'.HOST.'/admin/usuarios/">
								<span class="icon"><i class="fa fa-list"></i></span>
								<span class="title">Usuarios</span>
								<span class="subtitle">Todos los usuarios</span>
							</a>
						</li>';
					}
			if($_SESSION['user']['id_rol'] == 1){
				$this->sidebar .= 
						'<li'.$this->set_active_sidebar_tab('administradores.php').'>
							<a href="'.HOST.'/admin/usuarios/administradores">
								<span class="icon"><i class="fa fa-user-circle"></i></span>
								<span class="title">Administradores</span>
								<span class="subtitle">Todos los administradores</span>
							</a>
						</li>
						<li'.$this->set_active_sidebar_tab('nuevo-administrador.php').'>
							<a href="'.HOST.'/admin/usuarios/nuevo-administrador">
								<span class="icon"><i class="fa fa-user-plus"></i></span>
								<span class="title">Nuevo administrador</span>
								<span class="subtitle">Asignar privilegios</span>
							</a>
						</li>';
			}
				break;
			case 'negocios':
				$this->crumbs[0] = 'Negocios';
				switch (basename($_SERVER['SCRIPT_NAME'])) {
					case 'index.php':
						$this->crumbs[1] = 'Listado';
						break;
					case 'solicitudes.php':
						$this->crumbs[1] = 'Solicitudes';
						break;
					case 'recargar.php':
						$this->crumbs[1] = 'Recargar saldo';
						break;
					case 'quitar-saldo.php':
						$this->crumbs[1] = 'Quitar saldo';
						break;
					case 'solicitud.php':
						$this->crumbs[1] = 'Detalles de solicitud';
						break;
					case 'reporte.php':
						$this->crumbs[1] = 'Movimientos de saldos';
						break;
					default:
						$this->crumbs[1] = '';
						break;
				}
				if($this->admin['pending_request'] > 0){
					$noti = '<span class="notification">'.$this->admin['pending_request'].'</span>';
				}else{
					$noti = '';
				}


				if($_SESSION['user']['id_rol'] == 1 || $_SESSION['user']['id_rol'] == 2){
				$this->sidebar =
						'<li'.$this->set_active_sidebar_tab('index.php').'>
							<a href="'.HOST.'/admin/negocios/">
								<span class="icon"><i class="fa fa-list"></i></span>
								<span class="title">Negocios</span>
								<span class="subtitle">Ver todos los negocios</span>
							</a>
						</li>
						<li'.$this->set_active_sidebar_tab('solicitudes.php').'>
							<a href="'.HOST.'/admin/negocios/solicitudes">
								<span class="icon"><i class="fa fa-file"></i></span>
								<span class="title">Solicitudes'.$noti.'</span>
								<span class="subtitle">Ver todas las solicitudes</span>
							</a>
						</li>';
					}
			if($_SESSION['user']['id_rol'] == 1 || $_SESSION['user']['id_rol'] == 2){
				$this->sidebar .= 
						'
						<li'.$this->set_active_sidebar_tab('recargar.php').'>
							<a href="'.HOST.'/admin/negocios/recargar">
								<span class="icon"><i class="fa fa-plus-circle"></i></span>
								<span class="title">Recargar saldo</span>
								<span class="subtitle">Recarga saldo a un negocio</span>
							</a>
						</li>';
			}
			if($_SESSION['user']['id_rol'] == 1){
				$this->sidebar .= 
						'<li'.$this->set_active_sidebar_tab('quitar-saldo.php').'>
							<a href="'.HOST.'/admin/negocios/quitar-saldo">
								<span class="icon"><i class="fa fa-minus-circle"></i></span>
								<span class="title">Quitar saldo</span>
								<span class="subtitle">Quitar saldo a un negocio</span>
							</a>
						</li>';
			}
			if($_SESSION['user']['id_rol'] == 1 || $_SESSION['user']['id_rol'] == 2){
			$this->sidebar .= 
					'<li'.$this->set_active_sidebar_tab('reporte.php').'>
						<a href="'.HOST.'/admin/negocios/reporte">
							<span class="icon"><i class="fa fa-list-alt"></i></span>
							<span class="title">Reporte</span>
							<span class="subtitle">Movimientos de saldos</span>
						</a>
					</li>';
				}
				break;

				case 'perfiles':
				$this->crumbs[0] = 'Perfiles';
				switch (basename($_SERVER['SCRIPT_NAME'])) {
					case 'index.php':
						$this->crumbs[1] = 'Listado';
						break;
					case 'solicitudes.php':
						$this->crumbs[1] = 'Solicitudes';
						break;
					case 'comprobantes.php':
						$this->crumbs[1] = 'Comprobantes de Pago';
						break;
					case 'iata.php':
						$this->crumbs[1] = 'Codigo IATA';
						break;
					
					default:
						$this->crumbs[1] = '';
						break;
				}
				if($this->admin['solicitudes_pendiente_perfiles'] > 0){
					$noti = '<span class="notification">'.$this->admin['solicitudes_pendiente_perfiles'].'</span>';
				}else{
					$noti = '';
				}

				if($this->admin['solicitudes_pendiente_retiros'] > 0){
					$notif = '<span class="notification">'.$this->admin['solicitudes_pendiente_retiros'].'</span>';
				}else{
					$notif = '';
				}

				if($_SESSION['user']['id_rol'] == 1 || $_SESSION['user']['id_rol'] == 2){
				$this->sidebar =
						'<li'.$this->set_active_sidebar_tab('index.php').'>
							<a href="'.HOST.'/admin/perfiles/">
								<span class="icon"><i class="fa fa-list"></i></span>
								<span class="title">Perfiles</span>
								<span class="subtitle">Usuarios con perfiles</span>
							</a>
						</li>

						
						<li'.$this->set_active_sidebar_tab('hoteles.php').'>
							<a href="'.HOST.'/admin/perfiles/hoteles">
								<span class="icon"><i class="fa fa-hotel"></i></span>
								<span class="title">Hoteles</span>
								<span class="subtitle">Todos los hoteles</span>
							</a>
						</li>
					
						<li'.$this->set_active_sidebar_tab('comprobantes.php').'>
							<a href="'.HOST.'/admin/perfiles/comprobantes">
								<span class="icon"><i class="fa fa-file-pdf-o"></i></span>
								<span class="title">Comprobantes'.$notif.'</span>
								<span class="subtitle">Emitir comprobantes</span>
							</a>
						</li>
						
						<li'.$this->set_active_sidebar_tab('iata.php').'>
							<a href="'.HOST.'/admin/perfiles/iata">
								<span class="icon"><i class="fa fa-fighter-jet"></i></span>
								<span class="title">IATA</span>
								<span class="subtitle">codigo IATA</span>
							</a>
						</li>
						';

					}
				break;

			case 'preferencias':
				$this->crumbs[0] = 'Preferencias';

				switch (basename($_SERVER['SCRIPT_NAME'])) {
					case 'codigo-seguridad.php':
						$this->crumbs[1] = 'C&oacute;digo de seguridad';
						break;
					case 'preferencia-sistema.php':
						$this->crumbs[1] = 'del sistema';
						break;
					
					
					default:
						$this->crumbs[1] = '';
						break;
				}

				if($_SESSION['user']['id_rol'] == 1 || $_SESSION['user']['id_rol'] == 2){
				$this->sidebar =
						'<li'.$this->set_active_sidebar_tab('codigo-seguridad.php').'>
							<a href="'.HOST.'/admin/preferencias/codigo-seguridad">
								<span class="icon"><i class="fa fa-lock"></i></span>
								<span class="title">C&oacute;digo de seguridad</span>
								<span class="subtitle">Cambiar el c&oacute;digo</span>
							</a>
						</li>

						<li'.$this->set_active_sidebar_tab('preferencia-sistema.php').'>
							<a href="'.HOST.'/admin/preferencias/preferencia-sistema">
								<span class="icon"><i class="fa fa-cogs"></i></span>
								<span class="title">Preferencias</span>
								<span class="subtitle">Del sistema</span>
							</a>
						</li>';
					}
				break;

				case 'academia':
				$this->crumbs[0] = 'Academia';

				switch (basename($_SERVER['SCRIPT_NAME'])) {
					case 'videos':
						$this->crumbs[1] = 'Aprendiendo de Travel';
						break;
					case 'new':
						$this->crumbs[1] = 'del sistema';
						break;

					default:
						$this->crumbs[1] = '';
						break;
					
				}

				if($_SESSION['user']['id_rol'] == 1 || $_SESSION['user']['id_rol'] == 2){
				$this->sidebar =
						'<li'.$this->set_active_sidebar_tab('index.php').'>
							<a href="'.HOST.'/admin/academia/">
								<span class="icon"><i class="fa fa-video-camera"></i></span>
								<span class="title">Videos</span>
								<span class="subtitle">Aprendiendo de TravelPoints</span>
							</a>
						</li>

						<li'.$this->set_active_sidebar_tab('new-clases.php').'>
							<a href="'.HOST.'/admin/academia/new-clases">
								<span class="icon"><i class="fa fa-edit"></i></span>
								<span class="title">New Class </span>
								<span class="subtitle">Nuevo video-tutorial</span>
							</a>
						</li>';
					}
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
	<meta https-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<link rel="icon" type="image/png" href="'.HOST.'/assets/img/favicon.png" >
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

	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/font-awesome/css/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/owl.carousel/assets/owl.carousel.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/colorbox/example1/colorbox.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/bootstrap-select/bootstrap-select.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/bootstrap-fileinput/fileinput.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/bootstrap-slider/css/bootstrap-slider.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/fontawesome-iconpicker/css/fontawesome-iconpicker.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/datatables/datatables.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/css/superlist.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/css/travelpoints.css" />
	<link rel="stylesheet" href="'.HOST.'/assets/libraries/jquery-confirm/dist/jquery-confirm.min.css" />
	
	<script src="'.HOST.'/assets/js/jquery.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/libraries/jquery-confirm/dist/jquery-confirm.min.js"></script>
	<script type="text/javascript" src="'.HOST.'/assets/libraries/datatables/datatables.min.js"></script>
	<script type="text/javascript" src="'.HOST.'/assets/libraries/bootstrap/js/popper.min.js"></script>
	

	<script src="'.HOST.'/assets/libraries/Highcharts/highcharts.js"></script>
				<script src="'.HOST.'/assets/libraries/Highcharts/modules/data.js"></script>
				<script src="'.HOST.'/assets/libraries/Highcharts/modules/exporting.js"></script>

				
	

	<title>'.$title.'</title>
	<meta name="description" content="'.$description.'" />

	</head>
';
		return $html;
	}

	public function get_admin_navbar(){

		if($this->admin['pending_request'] > 0){
			$noti = '<div class="notification"></div>';
			$link = '<li><a href="'.HOST.'/admin/negocios/solicitudes">Solicitudes pendientes<div class="dropdown-notification"></div></a></li>';
		}else{
			$noti = '';
			$link = '';
		}

		if($this->admin['solicitudes_pendiente_perfiles'] > 0){
			$noti .= '<div class="notification"></div>';
			$link .= '<li><a href="'.HOST.'/admin/perfiles/solicitudes">Solicitudes pendientes de perfiles<div class="dropdown-notification"></div></a></li>';
		}else{
			$noti .= '';
			$link .= '';
		}


		if($this->admin['solicitudes_pendiente_retiros'] > 0){
			$noti .= '<div class="notification"></div>';
			$link .= '<li><a href="'.HOST.'/admin/perfiles/comprobantes">Solicitudes pendientes de retiro de comisión<div class="dropdown-notification"></div></a></li>';
		}else{
			$noti .= '';
			$link .= '';
		}

		$perfil = '';
							if(isset($_SESSION['perfil']) && !empty($_SESSION['perfil'])){
							

								foreach ($_SESSION['perfil'] as $key => $value) {


									if($value['perfil'] == "Hotel"){
											$perfil .= '<li><a href="'.HOST.'/Hotel/">Panel Hotel</a></li>';
										}

										if($value['perfil'] == 'Franquiciatario'){
												$perfil .= '<li><a href="'.HOST.'/Franquiciatario/">Panel Franquiciatario</a></li>';
										}
										if($value['perfil'] == 'Referidor'){
											$perfil .= '<li><a href="'.HOST.'/Referidor/">Panel Referidor</a></li>';
										}
								}

								
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

	<script src="https://www.paypal.com/sdk/js?client-id=AQbkx9FrI9LADfmA-SGNPa3CmkORFFVx87kixFLBqXtZXk-F4mpM4r9GwD5O80zIDI7_P3HkngJjnMUY"></script>

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
								<a href="https://www.facebook.com/TravelPointsMX" target="_blank" class="header-button-inner blue" data-toggle="tooltip" data-placement="bottom" title="Travel Points Facebook">
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
								<li class="visible-xs"><a href="'.HOST.'/que-es-travel-points">¿Qu&eacute; es Travel Points?</a></li>
								<li class="visible-xs"><a href="'.HOST.'/tienda/">Tienda de Regalos</a></li>
								<li class="visible-xs"><a href="https://www.facebook.com/TravelPointsMX" target="_blank">Travel Points Facebook</a></li>
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
											'.$noti.'
										</div><!-- /.user-image -->
										<span class="header-nav-user-name">'.$this->user['alias'].'</span> <i class="fa fa-chevron-down"></i>
									</button>
									<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
										<li><a href="'.HOST.'/socio/">Mi inicio</a></li>
										<li><a href="'.HOST.'/socio/perfil/">Mi perfil</a></li>
										'.$link.$perfil;
								if(isset($_SESSION['business']['id_negocio'])){
									$html .= 
										'<li><a href="'.HOST.'/negocio/">Panel de Negocio</a></li>';
								}
								$html .= 
										'<li><a href="'.HOST.'/logout">Cerrar sesi&oacute;n</a></li>
									</ul>
								</div><!-- /.dropdown -->
							</div><!-- /.header-nav-user -->
						</div><!-- /.header-bottom -->
					</div><!-- /.header-content -->
				</div><!-- /.header-inner -->
			</div><!-- /.container -->
		</div><!-- /.header-wrapper -->
		<div class="header-statusbar">
			<div class="header-statusbar-inner">
			<button class="btn-largue-mod"><i class="fa fa-bars"></i></button>
				<div class="header-statusbar-left">

					<h1 class="logo-esmart">
						<span class="header-text ">Panel de control</span>
					</h1>

					<h1 class="logo-esmart">
					<span class="header-text"> '.$this->user['rol'].'</span></h1>
				</div>
				<!-- /.header-statusbar-left -->
				<div class="header-statusbar-right">
					<ul class="breadcrumb">
						<li class="hidden-xs hidden-sm"><a href="'.HOST.'/admin/">Administrador</a></li>
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


				

				if($_SESSION['user']['id_rol'] == 1 || $_SESSION['user']['id_rol'] == 2){

			$html .=	'<li'.$this->set_active_tab('admin').' data-toggle="tooltip" data-placement="right" title="Inicio"><a href="'.HOST.'/admin/"><i class="fa fa-home"></i></a></li>
						<li'.$this->set_active_tab('negocios').' data-toggle="tooltip" data-placement="right" title="Negocios"><a href="'.HOST.'/admin/negocios/"><i class="fa fa-briefcase"></i></a></li>


						
						<li'.$this->set_active_tab('usuarios').' data-toggle="tooltip" data-placement="right" title="Usuarios"><a href="'.HOST.'/admin/usuarios/"><i class="fa fa-user-circle-o"></i></a></li>

								<li'.$this->set_active_tab('perfiles').' data-toggle="tooltip" data-placement="right" title="Perfiles"><a href="'.HOST.'/admin/perfiles/"><i class="fa fa-users"></i></a></li>
		
								';
				$html .= '<li'.$this->set_active_tab('reservacion').' data-toggle="tooltip" data-placement="right" title="Reservaciones"><a href="'.HOST.'/admin/reservacion/"><i class="fa fa-calendar-check-o"></i></a></li>';
				}
					if($_SESSION['user']['id_rol'] == 1 || $_SESSION['user']['id_rol'] == 2 || $_SESSION['user']['id_rol'] == 3){

			$html .=	'<li'.$this->set_active_tab('tienda').' data-toggle="tooltip" data-placement="right" title="Tienda de regalos"><a href="'.HOST.'/admin/tienda/"><i class="fa fa-shopping-bag"></i></a></li>';

			}
			if($_SESSION['user']['id_rol'] == 1 || $_SESSION['user']['id_rol'] == 2){

				
			$html .='<li'.$this->set_active_tab('preferencias').' data-toggle="tooltip" data-placement="right" title="Preferencias"><a href="'.HOST.'/admin/preferencias/codigo-seguridad"><i class="fa fa-cog"></i></a></li>';

			$html .='<li'.$this->set_active_tab('academia').' data-toggle="tooltip" data-placement="right" title="Academia"><a href="'.HOST.'/admin/academia/"><i class="fa fa-graduation-cap"></i></a></li>';



					}
			$html .='</ul>
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

	public function get_admin_footer(){
		$ano = date('Y');
		$html = 
'								</div><!-- /.container-fluid -->
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
<script src="'.HOST.'/assets/libraries/bootstrap-slider/js/bootstrap-slider.min.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/libraries/colorbox/jquery.colorbox-min.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/libraries/flot/jquery.flot.min.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/libraries/flot/jquery.flot.spline.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/libraries/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/libraries/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCNWsVH2kmknm6knGSRKDuzGeMWM1PT6gA&amp;libraries=weather,geometry,visualization,places,drawing" type="text/javascript"></script>
<script type="text/javascript" src="'.HOST.'/assets/libraries/jquery-google-map/infobox.js"></script>
<script type="text/javascript" src="'.HOST.'/assets/libraries/jquery-google-map/markerclusterer.js"></script>
<script type="text/javascript" src="'.HOST.'/assets/libraries/jquery-google-map/jquery-google-map.js"></script>
<script type="text/javascript" src="'.HOST.'/assets/libraries/owl.carousel/owl.carousel.js"></script>
<script type="text/javascript" src="'.HOST.'/assets/libraries/bootstrap-fileinput/fileinput.min.js"></script>
<script type="text/javascript" src="'.HOST.'/assets/libraries/bootstrap/js/popper.min.js"></script>
<script type="text/javascript" src="'.HOST.'/assets/libraries/font-awesome/js/fontawesome.min.js"></script>
<script type="text/javascript" src="'.HOST.'/assets/libraries/fontawesome-iconpicker/js/fontawesome-iconpicker.min.js"></script>

<script type="text/javascript" src="'.HOST.'/assets/js/typeahead.bundle.js"></script>
<script src="'.HOST.'/assets/js/superlist.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/js/custom.js" type="text/javascript"></script>
</body>
</html>';
		return $html;


		// AIzaSyAfGXqiorl8HZHXRQaGKpj95C8W8TU80co&amp
	}

	private function catch_errors($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\admin_includes.txt', '['.date('d/M/Y h:i:s A').' on '.$method.' on line '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		return;
	}
}
?>
