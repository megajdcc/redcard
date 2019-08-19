<?php 
namespace assets\libs;
use PDO;

class includes extends FuncionesAcademia{
	private $con,$conection;

	private $user = array(
		'id'                                           => null,
		'username'                                     => null,
		'esmarties'                                    => null,
		'image'                                        => null,
		'alias'                                        => null,
		'pending_review'                               => 0,
		'pending_request'                              => 0,
		'solicitud_pendiente_hotel'                    => 0,
		'solicitud_pendiente_hotel_revision'           => 0,
		'solicitud_pendiente_franquiciatario'          => 0,
		'solicitud_pendiente_franquiciatario_revision' => 0,
		'solicitud_pendiente_referidor'                => 0,
		'solicitud_pendiente_referidor_revision'       => 0,

	);
	private $admin = array(
		'pending_request' => 0
	);


	private $reserva = array(
			'numero' => 0,

	);
	public function __construct(connection $con){
		$this->con = $con->con;
		$this->conection = $con;

		parent::__construct($con,'General');

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

		$sql = "SELECT COUNT(*) as cuenta FROM solicitudhotel WHERE id_usuario = :id_usuario && mostrar_usuario = 3 ";
		try {
			$stm = $this->con->prepare($sql);
			$stm->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
			$stm->execute();
		} catch (PDOException $e) {
			$this->error_log(__METHOD__,__LINE__,$e->getMessage());
			return false;
		}
		if($fila = $stm->fetch()){
			$this->user['solicitud_pendiente_hotel'] = $fila['cuenta'];
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

		// verificacion de reservaciones.
		
		$sql = "select count(*) as numreserva from reservacion where usuario_solicitante = :user and status = 0";

		try {
				$stm = $this->con->prepare($sql);
				$stm->bindParam(':user',$this->user['id'],PDO::PARAM_INT);
				$stm->execute();

		} catch (\PDOException $e) {
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
			
		}

		if($stm->rowCount() > 0){
			
			if($row = $stm->fetch(PDO::FETCH_ASSOC)){
				$this->reserva['numero'] = $row['numreserva'];
			}

		}
		
		return;
	}

	public function get_user_sidebar(){



		if(isset($_SESSION['perfil'])){
			
			if($this->reserva['numero'] > 0){
					$notif = '<span class="notification">'.$this->reserva['numero'].'</span>';
				}else{
					$notif = '';
				}

			$html = '<div class="widget">
			<ul class="menu-advanced">
				<li'.$this->set_active_tab('perfil').'>
					<a href="'.HOST.'/socio/perfil/">
						<img src="'.HOST.'/assets/img/user_profile/'.$this->user['image'].'" alt="">
						'.$this->user['alias'].'
					</a>
				</li>
				<li'.$this->set_active_tab('socio').'><a href="'.HOST.'/socio/"><i class="fa fa-home"></i> Inicio</a></li>
				<li'.$this->set_active_tab('negocios').'><a href="'.HOST.'/socio/negocios/"><i class="fa fa-briefcase"></i> Negocios</a></li>
				<li'.$this->set_active_tab('hotel').'><a href="'.HOST.'/socio/hotel/hospedado/"><i class="fa fa-hotel"></i> Mi Hotel</a></li>
				<li'.$this->set_active_tab('reservaciones').'><a href="'.HOST.'/socio/reservaciones/"><i class="fa fa-credit-card-alt "></i> Reservaciones '.$notif.'</a></li>

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
				
				<li'.$this->set_active_sidebar_tab('editar.php').'><a href="'.HOST.'/socio/perfil/editar"><i class="fa fa-pencil"></i> Editar informaci&oacute;n</a></li>
				<li'.$this->set_active_sidebar_tab('cambiar-contrasena.php').'><a href="'.HOST.'/socio/perfil/cambiar-contrasena"><i class="fa fa-key"></i> Cambiar contrase&ntilde;a</a></li>
				<li'.$this->set_active_sidebar_tab('desactivar-cuenta.php').'><a href="'.HOST.'/socio/perfil/desactivar-cuenta"><i class="fa fa-times-circle"></i> Desactivar cuenta</a></li>
			</ul>
		</div>';
			break;

			case 'reservaciones':

				if($this->reserva['numero'] > 0){
					$notif = '<span class="notification">'.$this->reserva['numero'].'</span>';
				}else{
					$notif = '';
				}

				$html .=
						'<div class="widget">
							<ul class="menu-advanced">
								
								<li'.$this->set_active_sidebar_tab('index.php').'><a href="'.HOST.'/socio/reservaciones/"><i class="fa fa-money"></i>Mis Reservaciones '.$notif.'</a></li>
								
								

							</ul>
						</div>';
			break;
			case 'negocios':
					if($this->user['solicitud_pendiente_hotel'] > 0){
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
					}else{
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
				case 'hotel':
					if($this->user['solicitud_pendiente_hotel'] > 0){
						$noti = '<span class="notification">'.$this->user['solicitud_pendiente_hotel'].'</span>';
					}else{
						$noti = '';
					}
					if($_SESSION['user']['id_rol']==8) {
						$html .=
									'<div class="widget">
										<ul class="menu-advanced">
											<li'.$this->set_active_sidebar_tab('hospedado.php').'><a href="'.HOST.'/socio/hotel/hospedado/"><i class="fa fa-hotel"></i> Hospedado</a></li>
										</ul>
									</div>';
					} else {
						$html .=
										'<div class="widget">
											<ul class="menu-advanced">
													<li'.$this->set_active_sidebar_tab('hospedado.php').'><a href="'.HOST.'/socio/hotel/hospedado/"><i class="fa fa-hotel"></i> Hospedado</a></li>
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

		}else{

			if($this->reserva['numero'] > 0){
				$nofifica = '<div class="notification"></div>';
			}else{
				$notifica = '';
			}
			$html = '<div class="widget">
			<ul class="menu-advanced">
				<li'.$this->set_active_tab('perfil').'>
					<a href="'.HOST.'/socio/perfil/">
						<img src="'.HOST.'/assets/img/user_profile/'.$this->user['image'].'" alt="">
						'.$this->user['alias'].'
					</a>
				</li>
				<li'.$this->set_active_tab('socio').'><a href="'.HOST.'/socio/"><i class="fa fa-home"></i> Inicio</a></li>
				<li'.$this->set_active_tab('negocios').'><a href="'.HOST.'/socio/negocios/"><i class="fa fa-briefcase"></i> Negocios</a></li>
				<li'.$this->set_active_tab('hotel').'><a href="'.HOST.'/socio/hotel/hospedado/"><i class="fa fa-hotel"></i> Mi Hotel</a></li>
				<li'.$this->set_active_tab('reservaciones').'><a href="'.HOST.'/socio/reservaciones/"><i class="fa fa-credit-card-alt"></i> Reservaciones '.$notifica.'</a></li>
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
				<li'.$this->set_active_sidebar_tab('travelpoints.php').'><a href="'.HOST.'/socio/perfil/travelpoints"><i class="fa fa-exchange"></i> Mis Travel Points</a></li>
				<li'.$this->set_active_sidebar_tab('editar.php').'><a href="'.HOST.'/socio/perfil/editar"><i class="fa fa-pencil"></i> Editar informaci&oacute;n</a></li>
				<li'.$this->set_active_sidebar_tab('cambiar-contrasena.php').'><a href="'.HOST.'/socio/perfil/cambiar-contrasena"><i class="fa fa-key"></i> Cambiar contrase&ntilde;a</a></li>
				<li'.$this->set_active_sidebar_tab('desactivar-cuenta.php').'><a href="'.HOST.'/socio/perfil/desactivar-cuenta"><i class="fa fa-times-circle"></i> Desactivar cuenta</a></li>
			</ul>
		</div>';
			break;

			case 'reservaciones':

				if($this->reserva['numero'] > 0){
					$notif = '<span class="notification">'.$this->reserva['numero'].'</span>';
				}else{
					$notif = '';
				}

				$html .=
						'<div class="widget">
							<ul class="menu-advanced">
								
								<li'.$this->set_active_sidebar_tab('index.php').'><a href="'.HOST.'/socio/reservaciones/"><i class="fa fa-money"></i>Mis Reservaciones '.$notif.'</a></li>
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

			case 'hotel':
					if($this->user['pending_request'] > 0){
						$noti = '<span class="notification">'.$this->user['solicitud_pendiente_hotel'].'</span>';
					}else{
						$noti = '';
					}
					if($_SESSION['user']['id_rol']==8) {
						$html .=
									'<div class="widget">
										<ul class="menu-advanced">
											<li'.$this->set_active_sidebar_tab('hospedado.php').'><a href="'.HOST.'/socio/hotel/hospedado/"><i class="fa fa-hotel"></i> Hospedado</a></li>
										</ul>
									</div>';
					} else {
						$html .=
										'<div class="widget">
											<ul class="menu-advanced">
													<li'.$this->set_active_sidebar_tab('hospedado.php').'><a href="'.HOST.'/socio/hotel/hospedado/"><i class="fa fa-hotel"></i> Hospedado</a></li>
													<li'.$this->set_active_sidebar_tab('afiliar-hotel.php').'><a href="'.HOST.'/socio/hotel/afiliar-hotel"><i class="fa fa-plus-circle"></i> Afiliar mi hotel</a></li>
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
<head>
	
	<meta name="language" content="english" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	<meta https-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
		

	<link href="https://fonts.googleapis.com/css?family=Nunito:300,400,700" rel="stylesheet" type="text/css" />

	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/font-awesome/css/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/owl.carousel/assets/owl.carousel.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/colorbox/example1/colorbox.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/bootstrap-select/bootstrap-select.min.css" />
	

	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/bootstrap-fileinput/fileinput.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/fontawesome-iconpicker/css/fontawesome-iconpicker.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/css/animate.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/css/superlist.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/css/travelpoints.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/css/principal.css" />
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/jquery-confirm/dist/jquery-confirm.min.css" />

	
	<link rel="stylesheet" type="text/css" media="all" href="'.HOST.'/assets/libraries/datatables/datatables.min.css" />


	<script src="'.HOST.'/assets/js/jquery.js" type="text/javascript"></script>
	<script type="text/javascript" src="'.HOST.'/assets/libraries/datatables/datatables.min.js"></script>
	<script src="https://www.google.com/recaptcha/api.js?render=6LdeqKYUAAAAAJMjfm51tW7h8O8nx0ymBEBy_NgT"></script>

	<link rel="icon" type="image/png" href="'.HOST.'/assets/img/favicon.png" >

	<title>'.$title.'</title>
	<meta name="description" content="'.$description.'" />



	
<script src="//code.jivosite.com/widget.js" data-jv-id="3eCpHzk3og" async></script>
</head>
';

		return $html;
	}

	public function get_main_navbar(){
		if($this->user['id'] && basename(dirname($_SERVER['SCRIPT_NAME'])) == 'tienda'){
			$esm = number_format((float)$this->user['esmarties'], 2, '.', ',');
			$e = '<li><a href="'.HOST.'/tienda/">Tp$ '.$esm.'</a></li>';
		}else{
			$e = '';
		}
		?>

		<body class="">


				<script>
				(function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,"script","https://www.google-analytics.com/analytics.js","ga");
				
				ga("create", "UA-57870544-2", "auto");
				ga("send", "pageview");
				
				</script>

				
<!-- Payment method Pago... -->
<script src="https://www.paypal.com/sdk/js?client-id=AcYKncEXBz2IOKpUfUM_ChomIT4V9AJ97BAha55Y7X_O-OR8lyoSfbObOEkvELFV_5Kw4aiiNpWdytQY&intent=capture&currency=MXN&integration-date=2019-31-05&debug=false"></script>

<div id="fb-root"></div>
<div class="page-wrapper">
	<header data-scroll class="cabecera header" id="cabecera">
		<div class="header-wrapper">
			<div class="container">
				<div class="header-inner">
					<div class="header-logo">
						<a href="<?php echo HOST.'/';?>">
							<div class="logo wow bounceInRight">
										<style>
											.logo{
												background-image: url("<?php echo HOST.'/assets/img/logo.svg';?>");									
											}
										</style>
							</div>
						
						</a>
					</div><!-- /.header-logo -->
					<div class="header-content">
						<div class="header-bottom">
	
							<?php if ($this->is_videos('General')): ?>
								<div class="header-button ">
									<button class="btn-academia header-button-inner" data-toggle="tooltip" data-placement="bottom" title="Aprende de Travel Points"><i class="fa fa-graduation-cap"></i></button>
								</div>
							<?php endif ?>
							

							<div class="header-button">
								<a href="<?php echo HOST.'/contacto'; ?>" class="header-button-inner" data-toggle="tooltip" data-placement="bottom" title="Contact | Contacto">
									<i class="fa fa-envelope"></i>
								</a>
							</div>
							
							<div class="header-button">
								<a href="<?php echo HOST.'/tienda/';?>" class="header-button-inner pink" data-toggle="tooltip" data-placement="bottom" title="Gift Store | Tienda de Regalos">
									<i class="fa fa-gift"></i>
								</a>
							</div>
							<div class="header-button">
								<a href="<?php echo HOST.'/que-es-travel-points'; ?>" class="header-button-inner green" data-toggle="tooltip" data-placement="bottom" title="What is Travel Points | ¿Qu&eacute; es Travel Points?">
									<i class="fa fa-question"></i>
								</a>
							</div>
							<div class="header-button">
								<a href="<?php echo HOST.'/Hotel/login'; ?>" class="header-button-inner blue" data-toggle="tooltip" data-placement="bottom" title="&Aacute;rea de Promotores | Hoteles">
									<i class="fa fa-black-tie"></i>
								</a>
							</div>

							<?php  if(isset($_SESSION['user']['id_usuario'])){?>
								 
							<ul class="header-nav-primary nav nav-pills collapse navbar-collapse">
								<?php echo $e ?>
								<li class="visible-xs"><a href="<?php echo HOST.'/que-es-travel-points'; ?>">What is Travel Points | ¿Qu&eacute; es Travel Points?</a></li>
								<li class="visible-xs"><a href="<?php echo HOST.'/tienda/' ?>">Gift Store | Tienda de Regalos</a></li>
								
								<li class="visible-xs"><a href="<?php echo HOST.'/contacto'; ?>">Contact | Contacto</a></li>

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
											<img src="<?php echo HOST.'/assets/img/user_profile/'.$this->user['image']; ?>">
											<?php  

							if($this->user['pending_review'] > 0 || $this->user['pending_request'] > 0 || $this->admin['pending_request'] > 0 || $this->reserva['numero'] > 0){?>
								<div class="notification"></div>
							<?php  }


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

							 ?>
								</div><!-- /.user-image -->
										<span class="header-nav-user-name"><?php echo $this->user['alias']; ?></span> <i class="fa fa-chevron-down"></i>
									</button>
									<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
										<li><a href="<?php echo HOST.'/socio/'; ?>">Mi inicio</a></li>
										<li><a href="<?php echo HOST.'/socio/perfil/'; ?>">Mi perfil</a></li>
										<?php  
										echo $review.$request.$perfil;
								if($_SESSION['user']['id_rol'] == 1 || $_SESSION['user']['id_rol'] == 2 || $_SESSION['user']['id_rol'] == 3){?>
									
									<li><a href="<?php echo HOST.'/admin/'; ?>">Panel Travel Points</a><li>
									
									<?php  if($this->admin['pending_request'] > 0){?>
										

							
								<?php }
							} 

							if($this->reserva['numero'] > 0 ){
								echo '<li><a href="'.HOST.'/socio/reservaciones/">Reservas agendada<div class="dropdown-notification"></div></a></li>';
							}


								
								
								if($_SESSION['user']['id_rol'] == 9 and !isset($_SESSION['perfil'])){


								echo '<li><a href="'.HOST.'/admin/tienda/">Gift Store | Tienda de Regalos</a></li>';
									
								 } 

								if(isset($_SESSION['business'])){?>
									
									<li><a href="<?php echo HOST.'/negocio';?>">Panel de Negocio</a></li>
								<?php  }?>
								
								<li><a href="<?php echo HOST.'/logout'; ?>">Logout | Cerrar sesi&oacute;n</a></li>
									</ul>
								</div><!-- /.dropdown -->
							</div><!-- /.header-nav-user -->
							
			
								<?php }else{?>

							<ul class="header-nav-primary nav nav-pills collapse navbar-collapse">
								<li><a href="<?php echo HOST.'/login'; ?>">Login | Iniciar sesi&oacute;n</a></li>
								<li><a href="<?php echo HOST.'/hazte-socio';?>">Join | Hazte socio</a></li>
								<li class="visible-xs"><a href="<?php echo HOST.'/que-es-travel-points'; ?>">What is Travel Points | ¿Qu&eacute; es Red Card?</a></li>
								<li class="visible-xs"><a href="<?php echo HOST.'/tienda'; ?>">Gift Store | Tienda de Regalos</a></li>
								
								<li class="visible-xs"><a href="<?php echo HOST."/contacto" ?>">Contacto</a></li>
							</ul>
							<button class="navbar-toggle collapsed" type="button" data-toggle="collapse" data-target=".header-nav-primary">
								<span class="sr-only">Toggle navigation</span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
							</button>
							
							<?php } ?>
						</div><!-- /.header-bottom -->
					</div><!-- /.header-content -->
				</div><!-- /.header-inner -->
			</div><!-- /.container -->

		</div><!-- /.header-wrapper -->

	</header><!-- /.header -->

	<section class="content-academia p-socio">
			<?php 
				echo $this->getVideos();
			?>
	</section>



	<?php 
	}



	protected function getVideos(){
		$result  = $this->capturarvideos($this->conection,'General');

		return $result;
	}

	public function get_main_footer($query = null){

		$ano = date('Y');
	$html = 
		'<footer class="footer">
		<div class="footer-top">
			<div class="container">
				<div class="row">
					<div class="col-xs-6 col-sm-2">
					
						<h2>Member | Socio</h2>
						<p><a href="'.HOST.'/login">Login | Inicia sesi&oacute;n</a></p>
						<p><a href="'.HOST.'/hazte-socio">Join | Hazte socio</a></p>
					</div><!-- /.col-* -->
					<div class="col-xs-6 col-sm-3">
					
						<h2>Gana con Travel Points</h2>
						<p><a href="'.HOST.'/afiliar-hotel"><i">Afilia tu Hotel</a></p>
						
					
					</div><!-- /.col-* -->
					<div class="col-xs-6 col-sm-2">
						<h2>Negocio</h2>
						<p><a href="'.HOST.'/afiliar-negocio">Afilia tu negocio</a></p>
						<p><a href="'.HOST.'/por-que-afiliarme">¿Por qu&eacute; afiliarme?</a></p>
					</div><!-- /.col-* -->
					<div class="col-xs-12 col-sm-5">
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
			<div class="container footer1">
				<div class="footer-bottom-left">
					&copy; '.$ano.' All Rights Reserved | Todos los derechos reservados.
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
	'.$this->getModal().'
	<a data-scroll class="ir-arriba" href="#cabecera" title="top"><i class="fa  fa-arrow-circle-up" aria-hidden="true"></i> </a>
</div><!-- /.page-wrapper -->

<script src="'.HOST.'/assets/js/moment.min.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/js/map.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/libraries/bootstrap-sass/javascripts/bootstrap/collapse.js" type="text/javascript"></script>


<script src="'.HOST.'/assets/libraries/bootstrap-sass/javascripts/bootstrap/carousel.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/js/wow.min.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/js/smooth-scroll.min.js" type="text/javascript"></script>
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
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCNWsVH2kmknm6knGSRKDuzGeMWM1PT6gA&amp;libraries=weather,geometry,visualization,places,drawing" type="text/javascript"></script>
<script type="text/javascript" src="'.HOST.'/assets/libraries/jquery-google-map/infobox.js"></script>
<script type="text/javascript" src="'.HOST.'/assets/libraries/jquery-google-map/markerclusterer.js"></script>
<script type="text/javascript" src="'.HOST.'/assets/libraries/jquery-google-map/jquery-google-map.js"></script>
<script type="text/javascript" src="'.HOST.'/assets/libraries/owl.carousel/owl.carousel.js"></script>
<script type="text/javascript" src="'.HOST.'/assets/libraries/bootstrap-fileinput/fileinput.min.js"></script>
<script src="'.HOST.'/assets/libraries/jquery-confirm/dist/jquery-confirm.min.js" type="text/javascript"></script>
<script type="text/javascript" src="'.HOST.'/assets/libraries/fontawesome-iconpicker/js/fontawesome-iconpicker.min.js"></script>
<script type="text/javascript" src="'.HOST.'/assets/js/typeahead.bundle.js"></script>
<script src="'.HOST.'/assets/js/superlist.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/js/custom.js" type="text/javascript"></script>
<script src="'.HOST.'/assets/js/jd.js" type="text/javascript"></script>
'.$query.'
</body>
</html>';

return $html;
	
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\main_includes.txt', '['.date('d/M/Y h:i:s A').' on '.$method.' on line '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		return;
	}



	private function getHotel(){

		if($this->con->inTransaction()){
			$this->con->rollBack();
		}
			$this->con->beginTransaction();



		$result = false;

		try {

			$sql = "select n.nombre, nc.categoria from negocio as  n
					join negocio_categoria as nc on n.id_categoria = nc.id_categoria
					join solicitud_negocio as s on n.id_solicitud = s.id_solicitud
					join usuario as u on s.id_usuario = u.id_usuario
					where u.id_usuario = :id_usuario";
			$stm = $this->con->prepare($sql);

			$resultado = $stm->execute(array(':id_usuario' => $_SESSION['user']['id_usuario']));

			if($resultado){
				$resultados = array(
					'negocio'=>array(),
					'categoria'=>array(),
					'hotel_exists' => $resultado
			);
				while ($row = $stm->fetch()) {
					$resultados['negocio'][] = $row['nombre'];
					$resultados['categoria'][] = $row['categoria'];
			
				}

				return $resultados;

			}
		$this->con->commit();
		} catch (PDOException $e) {
			$this->con->rollBack();
		}

	}
}
?>