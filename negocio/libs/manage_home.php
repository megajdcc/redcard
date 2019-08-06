<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace negocio\libs;
use assets\libs\connection;
use PDO;
 
class manage_home {
	private $con;
	private $user = array('id' => null);
	private $business = array(
		'id' => null,
		'url' => null,
		'currency' => null,
		'views' => null,
		'operations' => null,
		'businesses' => null,
		'negocios' => null,
		'certificates' => array(),
		'sales' => array(),
		'eSmarties' => 0,
		'follows' => null,
		'recommends' => null,
		'rating' => array(),
		'commission' => null,
		'balance' => array(),
		'status' => null
		);
	private $error = array('notification' => null);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->user['id'] = $_SESSION['user']['id_usuario'];
		$this->business['id'] = $_SESSION['business']['id_negocio'];
		$this->business['url'] = $_SESSION['business']['url'];
		$this->load_data();
		return;
	}

	public function load_data(){
		$query = "SELECT np.preferencia
			FROM preferencia p 
			LEFT JOIN negocio_preferencia np ON p.id_preferencia = np.id_preferencia AND np.id_negocio = ?
			WHERE p.llave = 'default_currency'";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(1, $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['currency'] = $row['preferencia'];
		}
		// Cargar las vistas
		// echo 'id: '.$this->business['id'];
		// die();
		
		$query = "SELECT vistas, comision, saldo, ultima_recarga, situacion, creado FROM negocio WHERE id_negocio=".$this->business['id']."";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', 4, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['views'] = $row['vistas'];
			$this->business['commission'] = $row['comision'];
			$this->business['balance'] = array('balance' => $row['saldo'], 'last' => $row['ultima_recarga']);
			$this->business['status'] = $row['situacion'];
			$this->business['join_date'] = $row['creado'];
		}
		// Cargar las divisas
		$query = "SELECT iso, divisa FROM divisa";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->business['sales'][$row['iso']] = array('name' => $row['divisa'], 'total' => 0);
		}
		// Cargar el total de certificados + los publicados + los usados
		$query = "SELECT ne.disponibles, (SELECT COUNT(*) FROM usar_certificado uc WHERE uc.id_certificado = ne.id_certificado AND uc.situacion != 0) as usados FROM negocio_certificado ne WHERE ne.id_negocio = :id_negocio";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$available = $used = $certs = 0;
		while($row = $stmt->fetch()){
			$certs++;
			$available += $row['disponibles'];
			$used += $row['usados'];
		}
		$this->business['certificates']['total'] = $certs;
		$this->business['certificates']['available'] = $available;
		$this->business['certificates']['used'] = $used;
		// Cargar el total de ventas entre este mes
		$start_month = date('Y-m').'-01 00:00:00';
		$end_month = date('Y-m', strtotime('+1 month')).'-01 00:00:00';
		$query = "SELECT bono_esmarties FROM negocio_venta WHERE id_negocio = :id_negocio
			";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->business['eSmarties'] += $row['bono_esmarties'];
		}
		$query = "SELECT iso, venta FROM negocio_venta";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->business['sales'][$row['iso']]['total'] += $row['venta'];
		}
		// Cargar el total de seguidores
		$query = "SELECT COUNT(*) FROM seguir_negocio WHERE id_negocio = :id_negocio";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['follows'] = $row['COUNT(*)'];
		}
		// Cargar el total de recomendaciones
		$query = "SELECT COUNT(*) FROM recomendar_negocio WHERE id_negocio = :id_negocio";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['recommends'] = $row['COUNT(*)'];
		}
		// Cargar las calificaciones
		$query = "SELECT o.calificacion_servicio, o.calificacion_producto, o.calificacion_ambiente 
			FROM opinion o 
			INNER JOIN negocio_venta nv ON o.id_venta = nv.id_venta 
			WHERE nv.id_negocio = :id_negocio";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$reviews = $service = $product = $ambient = 0;
		while($row = $stmt->fetch()){
			$service += $row['calificacion_servicio'];
			$product += $row['calificacion_producto'];
			$ambient += $row['calificacion_ambiente'];
			$reviews++;
		}
		if($reviews == 0){
			$this->business['rating'] = array('rating' => 0, 'percent' => 0, 'reviews' => 0);
		}else{
			$rating = ($service + $product + $ambient) / ($reviews * 3);
			$percent = $rating / 0.05; // 5 / 100
			$this->business['rating'] = array('rating' => $rating, 'percent' => $percent, 'reviews' => $reviews);
		}
		return;
	}

	public function change_business($business_id){
		if($_SESSION['business']['id_negocio'] == $business_id){
			return;
		}
		$query = "SELECT ne.id_negocio, ne.id_rol, n.url, n.nombre 
			FROM negocio_empleado ne 
			INNER JOIN negocio n ON ne.id_negocio = n.id_negocio 
			WHERE ne.id_empleado = :id_empleado AND ne.id_negocio = :id_negocio";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_empleado', $this->user['id'], PDO::PARAM_INT);
			$stmt->bindValue(':id_negocio', $business_id, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$_SESSION['business']['id_negocio'] = $row['id_negocio'];
			$_SESSION['business']['id_rol'] = $row['id_rol'];
			$_SESSION['business']['url'] = $row['url'];
			$_SESSION['notification']['info'] = 'Te encuentras en el panel de negocio de "'._safe($row['nombre']).'"';
			header('Location: '.HOST.'/negocio/');
			die();
		}else{
			$this->error['notification'] = 'Error al tratar de cambiar de negocio.';
		}
		return;
	}

	public function get_status(){
		switch ($this->business['status']) {
			case 0:
				$tag = '<span class="label label-danger mr20">Baja</span';
				break;
			case 1:
				$tag = '<span class="label label-success mr20">Activo</span>';
				break;
			case 2:
				$tag = '<span class="label label-danger mr20">Suspendido</span>';
				break;
			case 3:
				$tag = '<span class="label label-warning mr20">Cerrado por temporada</span>';
				break;
			default:
				$tag = '';
				break;
		}
		return $tag;
	}

	public function get_profile_url(){
		return HOST.'/'._safe($this->business['url']);
	}

	public function get_join_date(){
		return date('d/m/Y', strtotime($this->business['join_date']));
	}

	public function get_balance(){
		$balance = number_format((float)$this->business['balance']['balance'], 2, '.', '');
		if($balance < 0){
			$red = ' class="text-danger"';
		}else{
			$red = null;
		}
		$date = date('d/m/Y',strtotime($this->business['balance']['last']));
		$html =
			'<strong'.$red.'>$'.$balance.'</strong>
			<span class="mb15">&Uacute;ltima recarga '.$date.'</span>
			<a href="'.HOST.'/negocio/recargar-saldo" class="btn btn-xs btn-success">Comprar saldo</a>';
		return $html;
	}

	public function get_rating(){
		$stars = $this->get_rating_stars($this->business['rating']['rating']);
		$percent = round($this->business['rating']['percent'],2);
		$rating = round($this->business['rating']['rating'],2);
		$html =
			'<strong class="rating-stars">'.$stars.'</strong>
			<span>'.$rating.' / 5 | '.$percent.'%</span>';
		return $html;
	}

	public function get_views(){
		return $this->business['views'];
	}
	public function get_operations(){
		$sql="SELECT count(*) FROM negocio_venta";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$number_of_rows = $stmt->fetchColumn();
		$this->business['operations']=$number_of_rows;
		return $number_of_rows;
	}
	public function get_businesses(){
		$sql="SELECT count(*) FROM negocio";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$number_of_rows = $stmt->fetchColumn();
		$this->business['businesses']=$number_of_rows;
		return $number_of_rows;
	}
	public function get_toatl_debt(){
		$sql="SELECT SUM(comision) AS total FROM negocio";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return number_format((float)$row['total'], 2, '.', '');
	}
	public function get_business_debt(){
		$sql="SELECT count(*) FROM negocio WHERE saldo <=0";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$number_of_rows = $stmt->fetchColumn();
		$this->business['businesses_debt']=$number_of_rows;
		return $number_of_rows;
	}
	public function get_usd_sales(){
		$sql="SELECT SUM(venta) AS total FROM negocio_venta WHERE iso='USD'";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return number_format((float)$row['total']/$this->business['operations'], 2, '.', '');
	}
	public function get_mxn_sales(){
		$sql="SELECT SUM(venta) AS total FROM negocio_venta WHERE iso='MXN'";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return number_format((float)$row['total']/$this->business['operations'], 2, '.', '');
	}
	public function get_toatl_commision(){
		$sql="SELECT SUM(comision) AS total FROM negocio_venta";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return number_format((float)$row['total'], 2, '.', '');
	}
	public function get_average_consuption(){
		$sql="SELECT count(*) FROM negocio_venta";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$number_of_rows = $stmt->fetchColumn();
		$commission=$number_of_rows;
		$sql="SELECT SUM(venta) AS total FROM negocio_venta";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$sale = $stmt->fetch(PDO::FETCH_ASSOC);
		$result=number_format((float)$sale['total']/$commission, 2, '.', '');
		return $result;
	}
	public function get_raw_utility(){
		$sql="SELECT SUM(comision) AS total FROM negocio_venta";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$comision = $stmt->fetch(PDO::FETCH_ASSOC);
		$sql="SELECT SUM(venta) AS total FROM negocio_venta";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$sale = $stmt->fetch(PDO::FETCH_ASSOC);
		$result=$sale['total']-$comision['total'];
		return $result;
	}


	public function getPorcentageComisionHotel(){
		$sql="SELECT SUM(n.comision) AS total FROM negocio as n join negocio_categoria as nc on n.id_categoria = nc.id_categoria
						where nc.categoria = 'Hotel'";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return number_format((float)$row['total'], 2, '.', '');
	}
	public function get_percentage_commision(){
		$sql="SELECT SUM(comision) AS total FROM negocio";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return number_format((float)$row['total'], 2, '.', '');
	}
	public function get_commision_referral(){
		$sql="SELECT SUM(bono_referente) AS total FROM negocio_venta";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return number_format((float)$row['total'], 2, '.', '');
	}
	public function get_user_total_points(){
		$sql="SELECT SUM(id_certificado) AS total FROM usar_certificado";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row['total'];
	}
	public function get_user_total_old_points(){
		$sql="SELECT SUM(id_certificado) AS total FROM usar_certificado WHERE creado <= DATE_SUB(NOW(),INTERVAL 1 YEAR)";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row['total'];
	}
	public function get_total_amount_store(){
		$sql="SELECT SUM(precio) AS total FROM venta_tienda";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return number_format((float)$row['total'], 2, '.', '');
	}
	public function get_average_amount_store(){
		$sql="SELECT SUM(precio) AS total FROM venta_tienda";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$sql="SELECT count(*) FROM venta_tienda";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$number_of_rows = $stmt->fetchColumn();
		$total=$number_of_rows;
		return number_format((float)$row['total']/$total, 2, '.', '');
	}
	public function get_total_gifts(){
		$sql="SELECT count(*) FROM lista_deseos_certificado";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$number_of_rows = $stmt->fetchColumn();
		$total=$number_of_rows;
		return $total;
	}
	public function get_total_requested_gifts(){
		$sql="SELECT count(*) FROM lista_deseos_certificado";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$number_of_rows = $stmt->fetchColumn();
		$total=$number_of_rows;
		$sql="SELECT count(*) FROM usuario";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$number_of_rows = $stmt->fetchColumn();
		$users=$number_of_rows;

		return round($total/$users);
	}
	public function get_total_users(){
		$sql="SELECT count(*) FROM usuario where id_rol=8";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$number_of_rows = $stmt->fetchColumn();
		$users=$number_of_rows;
		return $users;
	}
	public function get_total_participant_users(){
		$sql="SELECT count(*) FROM negocio_venta nv INNER JOIN usuario u ON u.id_usuario=nv.id_usuario where id_rol=8";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$number_of_rows = $stmt->fetchColumn();
		$users=$number_of_rows;
		return $users;
	}
	public function get_user_spent(){
		$sql="SELECT count(*) FROM negocio_venta nv INNER JOIN usuario u ON u.id_usuario=nv.id_usuario where id_rol=8";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$number_of_rows = $stmt->fetchColumn();
		$users=$number_of_rows;
		$sql="SELECT SUM(venta) AS total FROM negocio_venta nv INNER JOIN usuario u ON u.id_usuario=nv.id_usuario where id_rol=8";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return number_format((float)$row['total']/$users, 2, '.', '');
	}
	public function get_registration_per_user(){
		$sql="SELECT count(*) FROM negocio_venta nv INNER JOIN usuario u ON u.id_usuario=nv.id_usuario where id_rol=8";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$number_of_rows = $stmt->fetchColumn();
		$participants=$number_of_rows;
		return round($this->business['operations']/$participants);
	}
	public function get_commision_franchiser(){
		$sql="SELECT SUM(bono_esmarties) AS total FROM negocio_venta";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return number_format((float)$row['total'], 2, '.', '');
	}
	public function get_average_commision(){
		return number_format($this->business['businesses']/$this->business['operations']*100);
	}
	public function get_negocios(){
		return number_format($this->business['operations']/$this->business['businesses']*100);
	}

	public function get_commission(){
		return _safe($this->business['commission'].'%');
	}

	public function get_follows(){
		return $this->business['follows'];
	}

	public function get_recommends(){
		return $this->business['recommends'];
	}

	public function get_certificates(){
		$used = $this->business['certificates']['used'];
		$total = $this->business['certificates']['total'];
		$available = $this->business['certificates']['available'];
		$html =
			'<strong>'.$used.' / '.$available.'</strong>
			<span>De '.$total.' certificados</span>';
		return $html;
	}

	public function get_esmarties(){
		$eS = number_format($this->business['eSmarties']);
		return 'Tp$'.$eS ;
	}

	public function get_sales(){
		$pref = $nonpref = null;

		foreach ($this->business['sales'] as $key => $value) {

			if($value['total'] != 0){
				if($key == 'EUR'){
					$sign = '€';
				}else{
					$sign = '$';
				}

				$sale = number_format((float)$value['total'], 2, '.', '');
				$pref='';
				if($key == $this->business['currency']){
					$pref .=
					'<div class="col-sm-3">
						<div class="statusbox">
							<h2>Total de Ventas</h2>
							<div class="statusbox-content">
								<strong>'.$sign.$sale.' '.$key.'</strong>
							</div><!-- /.statusbox-content -->
						</div>
					</div>';
				}else{
					$nonpref .=
					'<div class="col-sm-3">
						<div class="statusbox">
							<h2>Total de Ventas</h2>
							<div class="statusbox-content">
								<strong>'.$sign.$sale.' '.$key.'</strong>
							</div><!-- /.statusbox-content -->
						</div>
					</div>';
				}
			}
		}
		$html = $pref.$nonpref;
		if(!$html){
			$html =
			'<div class="col-sm-3">
				<div class="statusbox">
					<h2>Total de ventas</h2>
					<div class="statusbox-content">
						<strong>$0</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>';
		}
		return $html;
	}
	public function get_business_sales(){
		$query = "SELECT iso FROM divisa";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->sales[$row['iso']] = 0;
		}
		$query = "SELECT nv.id_venta, u.username, u.nombre, u.apellido, p.pais, nv.iso, nv.venta, nv.comision, nv.bono_esmarties, nv.creado, r.username as e_username, r.nombre as e_nombre, r.apellido as e_apellido 
			FROM negocio_venta nv
			INNER JOIN usuario u ON nv.id_usuario = u.id_usuario
			LEFT JOIN ciudad c ON u.id_ciudad = c.id_ciudad
			LEFT JOIN estado e ON c.id_estado = e.id_estado
			LEFT JOIN pais p ON e.id_pais = p.id_pais
			INNER JOIN usuario r ON nv.id_empleado = r.id_usuario 
			WHERE id_negocio = :id_negocio
			ORDER BY nv.id_venta ASC";
		try{
			$stmt = $this->con->prepare($query);
	
			$stmt->bindValue('id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->business['sales'][$row['id_venta']] = array(
				'username' => $row['username'],
				'name' => $row['nombre'],
				'last_name' => $row['apellido'],
				'country' => $row['pais'],
				'iso' => $row['iso'],
				'total' => $row['venta'],
				'commission' => $row['comision'],
				'esmarties' => $row['bono_esmarties'],
				'created_at' => $row['creado'],
				'e_username' => $row['e_username'],
				'e_name' => $row['e_nombre'],
				'e_last_name' => $row['e_apellido']
			);
			$this->sales[$row['iso']] += $row['venta'];

		}

		$pref = $nonpref = null;
		foreach ($this->sales as $key => $value) {
			// if($value['total'] != 0){
				if($key == 'EUR'){
					$sign = '€';

				}else{
					$sign = '$';
				}
				$sale = number_format((float)$this->sales[$key], 2, '.', '');
				$pref='';
				
					
				if($key == $this->business['currency']){
					// echo 'true';
					$pref .=
					'<div class="col-sm-3">
						<div class="statusbox">
							<h2>Total de Ventas</h2>
							<div class="statusbox-content">
								<strong>'.$sign.$sale.' '.$key.'</strong>
							</div><!-- /.statusbox-content -->
						</div>
					</div>';
				}
				if($key != 'CAD' && $key !='EUR' && $key != 'USD' ){
					
					$nonpref .=
					'<div class="col-sm-3">
						<div class="statusbox">
							<h2>Total de Ventas</h2>
							<div class="statusbox-content">

								<strong>'.$sign.$sale.' '.$key.'</strong>
							</div><!-- /.statusbox-content -->
						</div>
					</div>';
				}
			// }
		}

		$html = $pref.$nonpref;
		if(!$html){
			$html =
			'<div class="col-sm-3">
				<div class="statusbox">
					<h2>Total de ventas</h2>
					<div class="statusbox-content">
						<strong>$0</strong>
					</div><!-- /.statusbox-content -->
				</div>
			</div>';
		}
		return $html;
	}

	private function get_rating_stars($rating){
		$rating = round($rating*2)/2;
		$string = null;
		for ($i=0; $i < 5; $i++){
			if($i < $rating){
				if($rating == $i+0.5){
					$string .= '<i class="fa fa-star-half-o"></i>';
				}else{
					$string .= '<i class="fa fa-star"></i>';
				}
			}else{
				$string .= '<i class="fa fa-star-o"></i>';
			}
		}
		return $string;
	}

	public function get_notification(){
		$notification = null;
		if(isset($_SESSION['notification']['success'])){
			$notification .= 
			'<div class="alert alert-icon alert-dismissible alert-success" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notification']['success']).'
			</div>';
			unset($_SESSION['notification']['success']);
		}
		if(isset($_SESSION['notification']['info'])){
			$notification .= 
			'<div class="alert alert-icon alert-dismissible alert-info" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notification']['info']).'
			</div>';
			unset($_SESSION['notification']['info']);
		}
		if($this->error['notification']){
			$notification .= 
			'<div class="alert alert-icon alert-dismissible alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->error['notification']).'
			</div>';
		}
		return $notification;
	}

	private function catch_errors($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\business_index.txt', '['.date('d/M/Y h:i:s A').' on '.$method.' on line '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		return;
	}
}
?>