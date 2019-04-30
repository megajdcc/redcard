<?php 
namespace Referidor\models;
use assets\libs\connection;
use PDO;


/**
 * @author Crespo Jhonatan
 * @since 18-06-19
 */
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

	public $referidor = array(
		'id'=> null,
	);
	private $error = array('notificacion' => null);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->user['id'] = $_SESSION['user']['id_usuario'];


		$this->CargarHotel();
		return;
	}

	private function CargarHotel(){

		$query = "select h.id, rf.id  as idreferidor from hotel as h 
			inner join referidor as rf on h.codigo = rf.codigo_hotel
inner join solicitudreferidor as srf on rf.id = srf.id_referidor 
			inner join usuario as u on srf.id_usuario = u.id_usuario
				where u.id_usuario = :id";

		$stm = $this->con->prepare($query);
		$stm->bindParam(':id',$this->user['id'], PDO::PARAM_INT);
		$stm->execute();

		$fila = $stm->fetch(PDO::FETCH_ASSOC);
		$this->hotel['id'] = $fila['id'];
		$this->referidor['id']  = $fila['idreferidor'];

		$_SESSION['id_hotel'] = $this->hotel['id'];
		$_SESSION['id_referidor'] = $this->referidor['id'];
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
		$sql="SELECT 
		  (SELECT COUNT(ne.id_negocio)
		   FROM negocio as ne where ne.situacion =1) as afiliados, 
		 (COUNT(DISTINCT ne.id_negocio)) as operados,
		 (COUNT(DISTINCT ne.id_negocio)*100)/(SELECT COUNT(ne.id_negocio)
		 FROM negocio as ne where ne.situacion =1) as porcentaje 
		 FROM
		 negocio_venta as nven INNER JOIN negocio as ne ON ne.id_negocio = nven.id_negocio
		 INNER JOIN usuario as usu on usu.id_usuario = nven.id_usuario
		 INNER JOIN huesped as hu  on hu.id_usuario = usu.id_usuario
		 INNER JOIN huespedhotel as hp	ON hp.id_huesped = hu.id
		 INNER JOIN hotel	as hot	ON hot.id = hp.id_hotel
		INNER JOIN divisa as di ON nven.iso = di.iso
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

	public function get_toatl_commision(){
		$sql="SELECT SUM(comision) AS total FROM negocio_venta";
		$stmt = $this->con->prepare($sql);
		$stmt->execute(); 
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return number_format((float)$row['total'], 2, ',', '.');
	}

	public function getPromedioConsumo(){
		$sql ="select AVG(nv.venta) as promedio, (select COUNT(venta) from negocio_venta) as nroventas 
				from negocio_venta as nv join balancehotel as bh on nv.id_venta = bh.id_venta
				join hotel as h on bh.id_hotel = :hotel";
		$stmt = $this->con->prepare($sql);
		$stmt->bindParam(':hotel',$this->hotel['id'], PDO::PARAM_INT);
		$stmt->execute(); 

		$promedio = $stmt->fetch(PDO::FETCH_ASSOC)['promedio'];

		$result=number_format((float)$promedio, 2, ',', '.');
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

	public function getUsuarios(){
		$sql="select COUNT(u.id_usuario) as usuarios from usuario as u join huesped as hu on u.id_usuario = hu.id_usuario
				join huespedhotel as hh on hu.id = hh.id_huesped 
				join hotel as h on hh.id_hotel = h.id
			where h.id =:hotel";
		$stmt = $this->con->prepare($sql);
		$stmt->bindParam(':hotel',$this->hotel['id']);
		$stmt->execute(); 
		$usuarios = $stmt->fetch(PDO::FETCH_ASSOC)['usuarios'];
		return $usuarios;
	}

	public function getUsuariosParticipantes(){
		$sql="select COUNT(nv.id_usuario) as usuarios from negocio_venta as nv
				left join usuario as u on nv.id_usuario = u.id_usuario 
				left join balancehotel as bh on nv.id_venta = bh.id_venta
				left join hotel as h on bh.id_hotel = h.id
				where h.id = :hotel GROUP BY nv.id_usuario";
		$stmt = $this->con->prepare($sql);
		$stmt->bindParam(':hotel',$this->hotel['id']);
		$stmt->execute(); 
		$usuarios = $stmt->fetchAll();
		return count($usuarios);
	}

	public function getTotalConsumoHuesped(){
			$sql=" select SUM(nv.venta) as consumo, u.username as usuario from negocio_venta as nv 
	 					join usuario as u on nv.id_usuario = u.id_usuario 
	 					join huesped as hu on u.id_usuario = hu.id_usuario 
	 					join huespedhotel as hh on hu.id = hh.id_huesped
	 					where hh.id_hotel = :hotel GROUP BY u.id_usuario";
			$stmt = $this->con->prepare($sql);
			$stmt->bindParam(':hotel',$this->hotel['id']);
			$stmt->execute(); 
			return $stmt;
	}

	public function getPuntosGenerados(){
		$sql="select SUM(nv.bono_esmarties) as puntos from negocio_venta as nv
				join usuario as u on nv.id_usuario = u.id_usuario
				join huesped as hu on u.id_usuario = hu.id_usuario 
				join huespedhotel as hh on hu.id = hh.id_huesped
				join hotel as h on hh.id_hotel = h.id
				where h.id = :hotel";
		$stmt = $this->con->prepare($sql);
		$stmt->bindParam(':hotel',$this->hotel['id'],PDO::PARAM_INT);
		$stmt->execute(); 
		$puntos = $stmt->fetch(PDO::FETCH_ASSOC)['puntos'];
		return number_format((float)$puntos, 2, ',','.');
	}

	public function getPuntosCanjeados(){
		$sql="select SUM(vt.precio) as canjeados from venta_tienda as vt 
				join usuario as u on vt.id_usuario = u.id_usuario 
				join huesped as hu on u.id_usuario = hu.id_usuario 
				join huespedhotel as hh on hu.id = hh.id_huesped 
				join hotel as h on hh.id_hotel = h.id	
				where h.id = :hotel";
			$stmt = $this->con->prepare($sql);
			$stmt->bindParam(':hotel',$this->hotel['id'],PDO::PARAM_INT);
			$stmt->execute(); 
			$puntos = $stmt->fetch(PDO::FETCH_ASSOC)['canjeados'];
			return number_format((float)$puntos, 2, ',','.');
	}

	public function getRegalosEntregados($idhotel = null){
		$sql="select COUNT(vt.id_venta) as regalos from venta_tienda as vt 
				join usuario as u on vt.id_usuario = u.id_usuario 
				join huesped as hu on u.id_usuario = hu.id_usuario 
				join huespedhotel as hh on hu.id = hh.id_huesped 
				join hotel as h on hh.id_hotel = h.id	
				where h.id = :hotel and vt.entrega = 1";
				$stmt = $this->con->prepare($sql);
				$stmt->bindParam(':hotel',$idhotel,PDO::PARAM_INT);
				$stmt->execute(); 
				$regalos = $stmt->fetch(PDO::FETCH_ASSOC)['regalos'];
				return $regalos;
	}

	public function getTotalRegalosPorUsuarios($idhotel){
		$sql="select COUNT(vt.id_venta) as regalos, CONCAT(u.nombre,' ',u.apellido) as nombre, u.username from venta_tienda as vt 
				join usuario as u on vt.id_usuario = u.id_usuario 
 				join huesped as hu on u.id_usuario = hu.id_usuario 
 				join huespedhotel as hh on hu.id = hh.id_huesped 
 				join hotel as h on hh.id_hotel = h.id	
 				where h.id = :hotel and vt.entrega = 1
					GROUP BY nombre";
				$stmt = $this->con->prepare($sql);
				$stmt->bindParam(':hotel',$idhotel,PDO::PARAM_INT);
				$stmt->execute(); 
				return $stmt;
	}

	public function getTotalValorRegalos(){
		$sql="select sum(vt.precio) as valor from venta_tienda as vt 
				join usuario as u on vt.id_usuario = u.id_usuario
				join huesped as hu on u.id_usuario = hu.id_usuario 
				join huespedhotel as hh on hu.id = hh.id_huesped
				join hotel as h on hh.id_hotel = h.id
				where h.id = :hotel and vt.entrega = 1 ";
				$stmt = $this->con->prepare($sql);
				$stmt->bindParam(':hotel',$this->hotel['id'],PDO::PARAM_INT);
				$stmt->execute(); 

				if( $stmt->fetch(PDO::FETCH_ASSOC)['valor']  > 0 ){
					$valor = number_format((float) $stmt->fetch(PDO::FETCH_ASSOC)['valor'],2,',','.');
				}else{
					$valor = 0;
				}
				return $valor;
	}

	public function getValorRegaloPromedio(){
		$sql="select AVG(vt.precio) as valor from venta_tienda as vt 
				join usuario as u on vt.id_usuario = u.id_usuario
				join huesped as hu on u.id_usuario = hu.id_usuario 
				join huespedhotel as hh on hu.id = hh.id_huesped
				join hotel as h on hh.id_hotel = h.id
				where h.id = :hotel and vt.entrega = 1 ";
				$stmt = $this->con->prepare($sql);
				$stmt->bindParam(':hotel',$this->hotel['id'],PDO::PARAM_INT);
				$stmt->execute(); 

				if( $stmt->fetch(PDO::FETCH_ASSOC)['valor']  > 0 ){
					$valor = number_format((float) $stmt->fetch(PDO::FETCH_ASSOC)['valor'],2,',','.');
				}else{
					$valor = 0;
				}
				return $valor;
	}

	public function getConsumosPromedioCompra(int $hotel = null){
		
		$query = "SELECT usu.username, CONCAT(usu.nombre,' ',usu.apellido) as huesped , AVG(nven.venta) as promedio, di.iso
				 FROM
				 negocio_venta as nven INNER JOIN negocio as ne ON ne.id_negocio = nven.id_negocio
				 INNER JOIN usuario as usu on usu.id_usuario = nven.id_usuario
				 INNER JOIN divisa as di ON nven.iso = di.iso
				INNER JOIN balancehotel as bh on nven.id_venta = bh.id_venta
				where bh.id_hotel =:hotel
				 GROUP BY usu.username";
				$stm = $this->con->prepare($query);
				$stm->bindParam(':hotel', $hotel, PDO::PARAM_INT);
				$stm->execute();
				return $stm; 
	}

	public function getConsumosPromedioNegocio(int $hotel = null){
		
			$query = "SELECT n.nombre as negocio , AVG(nven.venta) as promedio, di.iso FROM
					negocio_venta as nven INNER JOIN negocio as ne ON ne.id_negocio = nven.id_negocio
					INNER JOIN usuario as usu on usu.id_usuario = nven.id_usuario
					INNER JOIN negocio as n on nven.id_negocio = n.id_negocio
					INNER JOIN divisa as di ON nven.iso = di.iso
					INNER JOIN balancehotel as bh on nven.id_venta = bh.id_venta
					where bh.id_hotel =:hotel	
					GROUP BY n.nombre";
					$stm = $this->con->prepare($query);
					$stm->bindParam(':hotel', $hotel, PDO::PARAM_INT);
					$stm->execute();
					return $stm; 
	}

	// public function get_esmarties(){
	// 	$eS = number_format($this->hotel['eSmarties']);
	// 	return 'e$'.$eS;
	// }

	public function getComisiones(){
				$query  = "select nv.iso  as divisa, (select br.balance as balance from balancereferidor as br where br.id_referidor = :fr1 
								and br.id = (select max(id) from balancereferidor)) as balance
								from negocio_venta as nv join balancereferidor as br on nv.id_venta = br.id_venta
								where br.id_referidor = :fr2 and br.creado BETWEEN br.creado and now()";

				$stm = $this->con->prepare($query);
				$stm->execute(array(':fr1'=>$this->referidor['id'],
				                    ':fr2'=>$this->referidor['id']));

				
				$pref = null;
				while($row = $stm->fetch(PDO::FETCH_ASSOC)){

					if($row['divisa'] == 'EUR'){
							$sign = '€';
						}else{
							$sign = '$';
					}

					$comision = number_format((float)$row['balance'],2,'.','');
					
					if($comision  > 0){
						$pref ='<strong>'.$sign.$comision.' '.$row['divisa'].'</strong>';
					}
				


				}
		
				$html = $pref;
				if(!$html){
					$html ='<strong>$ 0</strong>';
				}
		return $html;
	}

	public function getBalance(){
		$query  = "SELECT  brf.balance as balance
 					from  balancereferidor as brf
 				where brf.id_referidor = :idreferidor and brf.id = (select max(id) from balancereferidor)";
				$stm = $this->con->prepare($query);
				$stm->execute(array(':idreferidor'=>$this->referidor['id']));
				return $stm->fetch(PDO::FETCH_ASSOC)['balance'];
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