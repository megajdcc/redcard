<?php 
namespace Hotel\models;
use assets\libs\connection;
use PDO;
 

class Home {

	private $con;
	private $user = array('id' => null);


	public $hotel = array(
		'id' => null,
		'url' => null,
		'currency' => null,
		'views' => null,
		'operations' => null,
		'hoteles' => null,
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


	private $error = array('notificacion' => null);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->user['id'] = $_SESSION['user']['id_usuario'];


		$this->CargarHotel();
		$this->cargar();
		return;
	}

	private function CargarHotel(){

		$query = "select h.id from hotel as h 
			inner join solicitudhotel as sh on h.id = sh.id_hotel
			inner join usuario as u on sh.id_usuario = u.id_usuario
				where u.id_usuario = :id";

		$stm = $this->con->prepare($query);
		$stm->bindParam(':id',$this->user['id'], PDO::PARAM_INT);
		$stm->execute();

		$this->hotel['id'] = $stm->fetch(PDO::FETCH_ASSOC)['id'];
		$_SESSION['id_hotel'] = $this->hotel['id'];
	}
	public function cargar(){

	
	}


	public function get_status(){
		switch ($this->hotel['status']) {
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
		return HOST.'/'._safe($this->hotel['url']);
	}

	public function get_join_date(){
		return date('d/m/Y', strtotime($this->hotel['join_date']));
	}

	public function get_balance(){
		$balance = number_format((float)$this->hotel['balance']['balance'], 2, '.', '');
		if($balance < 0){
			$red = ' class="text-danger"';
		}else{
			$red = null;
		}
		$date = date('d/m/Y',strtotime($this->hotel['balance']['last']));
		$html =
			'<strong'.$red.'>$'.$balance.'</strong>
			<span class="mb15">&Uacute;ltima recarga '.$date.'</span>
			<a href="'.HOST.'/negocio/recargar-saldo" class="btn btn-xs btn-success">Comprar saldo</a>';
		return $html;
	}

	public function get_rating(){
		$stars = $this->get_rating_stars($this->hotel['rating']['rating']);
		$percent = round($this->hotel['rating']['percent'],2);
		$rating = round($this->hotel['rating']['rating'],2);
		$html =
			'<strong class="rating-stars">'.$stars.'</strong>
			<span>'.$rating.' / 5 | '.$percent.'%</span>';
		return $html;
	}

	public function get_views(){
		return $this->hotel['views'];
	}
	public function getOperaciones(){
		$sql="SELECT COUNT(nven.venta)
 					FROM negocio as ne
 					JOIN negocio_venta as nven on ne.id_negocio = nven.id_negocio
 					JOIN usuario as usu on nven.id_usuario = usu.id_usuario
 					JOIN huesped as hu on usu.id_usuario = hu.id_usuario
 					JOIN huespedhotel as hh on hu.id = hh.id_huesped
 					JOIN hotel as h on hh.id_hotel = h.id
 				where h.id = :idhotel";
		$stmt = $this->con->prepare($sql);

		$stmt->execute(array(':idhotel'=>$this->hotel['id'])); 
		$number_of_rows = $stmt->fetchColumn();
		$this->hotel['operations']=$number_of_rows;
		return $number_of_rows;
	}

	public function getOperacionesNegocios(){
		$sql="SELECT (SELECT COUNT(ne.id_negocio) FROM negocio as ne where ne.situacion =1) as afiliados, COUNT(ne.id_negocio) as operados,
						(COUNT(ne.id_negocio)*100)/(SELECT COUNT(ne.id_negocio)
						FROM negocio as ne where ne.situacion =1) as porcentaje
						FROM negocio as ne 
						JOIN negocio_venta as nven ON ne.id_negocio = nven.id_negocio
						JOIN usuario as usu on  nven.id_usuario = usu.id_usuario
						JOIN huesped as hu  on  usu.id_usuario = hu.id_usuario
						JOIN huespedhotel as hp	ON  hu.id = hp.id_huesped
						JOIN hotel	as hot	ON  hp.id_hotel = hot.id
						where hu.id_usuario = nven.id_usuario and ne.situacion =1 and hot.id = :idhotel";
		$stmt = $this->con->prepare($sql);

		$stmt->execute(array(':idhotel'=>$this->hotel['id'])); 


		$fila = $stmt->fetch(PDO::FETCH_ASSOC);

		$porcentaje = number_format((float)$fila['porcentaje'], 2, '.', '');
		$html = '
			<strong>AFILIADOS: '.$fila['afiliados'].'</strong>
			<strong>OPERADOS: '.$fila['operados'].'</strong>
			<strong>'.$porcentaje.' %</strong>
		';
		
		return $html;
	}

	public function getNegociosDeudores(){

		$query = "SELECT COUNT(ne.id_negocio) as deudores
			FROM
			negocio_venta as nven INNER JOIN negocio as ne ON ne.id_negocio = nven.id_negocio
			INNER JOIN usuario as usu on usu.id_usuario = nven.id_usuario
			INNER JOIN huesped as hu  on hu.id_usuario = usu.id_usuario
			INNER JOIN huespedhotel as hp	ON hp.id_huesped = hu.id
			INNER JOIN hotel	as hot	ON hot.id = hp.id_hotel
			INNER JOIN divisa as di ON nven.iso = di.iso
			where ne.situacion = 1 and ne.saldo <=0 and hot.id = :idhotel";
  	$stm = $this->con->prepare($query);

  	$stm->execute(array(':idhotel'=>$this->hotel['id']));

  	return $stm->fetch(PDO::FETCH_ASSOC)['deudores'];

	}

	public function getTotalComisionAdeudo(){

		$query = "SELECT (((SUM(nven.venta)*(nven.comision))/100) * hot.comision / 100) + ne.saldo  as adeudo, nven.iso as divisa
						FROM
				negocio_venta as nven INNER JOIN negocio as ne ON ne.id_negocio = nven.id_negocio
				INNER JOIN usuario as usu on usu.id_usuario = nven.id_usuario
				INNER JOIN huesped as hu  on hu.id_usuario = usu.id_usuario
				INNER JOIN huespedhotel as hp	ON hp.id_huesped = hu.id
				INNER JOIN hotel	as hot	ON hot.id = hp.id_hotel
				INNER JOIN divisa as di ON nven.iso = di.iso
				where ne.situacion = 1  and ne.saldo <= 0 and hot.id = :idhotel";

				$stm = $this->con->prepare($query);
  				$stm->execute(array(':idhotel'=>$this->hotel['id']));
  				$fila = $stm->fetch(PDO::FETCH_ASSOC);
  				$comision = number_format((float)$fila['adeudo'],2,'.','');
  				if($fila['divisa'] == 'EUR'){
  					$div = '€';
  				}else{
  					$div = '$';
  				}
  				$total = $div.$comision.' '.$fila['divisa'];
  			return $total;
	}


	public function get_hoteles(){
		$sql="SELECT count(*) FROM negocio";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$number_of_rows = $stmt->fetchColumn();
		$this->hotel['hoteles']=$number_of_rows;
		return $number_of_rows;
	}
	public function get_toatl_debt(){
		$sql="SELECT SUM(comision) AS total FROM negocio";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return number_format((float)$row['total'], 2, '.', '');
	}
	public function get_hotel_debt(){
		$sql="SELECT count(*) FROM negocio WHERE saldo <=0";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$number_of_rows = $stmt->fetchColumn();
		$this->hotel['hoteles_debt']=$number_of_rows;
		return $number_of_rows;
	}
	public function get_usd_sales(){
		$sql="SELECT SUM(venta) AS total FROM negocio_venta WHERE iso='USD'";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return number_format((float)$row['total']/$this->hotel['operations'], 2, '.', '');
	}
	public function get_mxn_sales(){
		$sql="SELECT SUM(venta) AS total FROM negocio_venta WHERE iso='MXN'";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return number_format((float)$row['total']/$this->hotel['operations'], 2, '.', '');
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
		return round($this->hotel['operations']/$participants);
	}

	public function get_commision_franchiser(){
		$sql="SELECT SUM(bono_esmarties) AS total FROM negocio_venta";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return number_format((float)$row['total'], 2, '.', '');
	}

	public function getConsumosPromedioCompra(int $hotel = null){
		
		$query = "  SELECT usu.username, CONCAT(usu.nombre,' ',usu.apellido) as huesped , AVG(nven.venta) as promedio, di.iso
				 FROM
				 negocio_venta as nven INNER JOIN negocio as ne ON ne.id_negocio = nven.id_negocio
				 INNER JOIN usuario as usu on usu.id_usuario = nven.id_usuario
				 INNER JOIN divisa as di ON nven.iso = di.iso
				
				 GROUP BY nven.venta,usu.username";
		$stm = $this->con->prepare($query);
		$stm->execute();
		return $stm; 
	}

	public function getNegocios(){
		return number_format($this->hotel['operations']/$this->hotel['hoteles']*100);
	}

	public function get_commission(){
		return _safe($this->hotel['commission'].'%');
	}

	public function get_follows(){
		return $this->hotel['follows'];
	}

	public function get_recommends(){
		return $this->hotel['recommends'];
	}

	public function get_certificates(){
		$used = $this->hotel['certificates']['used'];
		$total = $this->hotel['certificates']['total'];
		$available = $this->hotel['certificates']['available'];
		$html =
			'<strong>'.$used.' / '.$available.'</strong>
			<span>De '.$total.' certificados</span>';
		return $html;
	}

	public function get_esmarties(){
		$eS = number_format($this->hotel['eSmarties']);
		return 'e$'.$eS;
	}

	public function getComisiones(){




			$query  = "SELECT nven.iso as divisa,((SUM(nven.venta)*(nven.comision))/100) * h.comision / 100 as comision_hotel, nven.creado
 					FROM negocio as ne
 					JOIN negocio_venta as nven on ne.id_negocio = nven.id_negocio
 					JOIN usuario as usu on nven.id_usuario = usu.id_usuario
 					JOIN huesped as hu on usu.id_usuario = hu.id_usuario
 					JOIN huespedhotel as hh on hu.id = hh.id_huesped
 					JOIN hotel as h on hh.id_hotel = h.id
 				where h.id = :idhotel";

				$stm = $this->con->prepare($query);
				$stm->execute(array(':idhotel'=>$this->hotel['id']));


				
				while($row = $stm->fetch(PDO::FETCH_ASSOC)){

					if($row['divisa'] == 'EUR'){
							$sign = '€';
						}else{
							$sign = '$';
					}

					$comision = number_format((float)$row['comision_hotel'],2,'.','');
					$pref = null;
					if($comision  > 0){
						$pref .='<strong>'.$sign.$comision.' '.$row['divisa'].'</strong>';
					}
				


				}
		
				$html = $pref;
				if(!$html){
					$html ='<strong>$ 0</strong>';
				}
		return $html;
	}
	public function get_hotel_sales(){
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
	
			$stmt->bindValue('id_negocio', $this->hotel['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->hotel['sales'][$row['id_venta']] = array(
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
				
					
				if($key == $this->hotel['currency']){
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

	public function getNotificacion(){
		$notificacion = null;
		if(isset($_SESSION['notificacion']['success'])){
			$notificacion .= 
			'<div class="alert alert-icon alert-dismissible alert-success" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notificacion']['success']).'
			</div>';
			unset($_SESSION['notificacion']['success']);
		}
		if(isset($_SESSION['notificacion']['info'])){
			$notificacion .= 
			'<div class="alert alert-icon alert-dismissible alert-info" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notificacion']['info']).'
			</div>';
			unset($_SESSION['notificacion']['info']);
		}
		if($this->error['notificacion']){
			$notificacion .= 
			'<div class="alert alert-icon alert-dismissible alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->error['notificacion']).'
			</div>';
		}
		return $notificacion;
	}

	private function catch_errors($method, $line, $error){
		try {
			
			file_put_contents(ROOT.'\assets\error_logs\hotel_index.txt', '['.date('d/M/Y h:i:s A').' on '.$method.' on line '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		} catch (Exception $e) {
			
		}
		
		return;
	}
}
?>