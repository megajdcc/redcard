<?php 
namespace Franquiciatario\models;
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

	public $franquiciatario = array(
		'id'=> null,
	);

	private $fechas = array(
		'inicio' => null,
		'fin'    =>null
		);
	private $fecha1, $fecha2;
	
	private $error = array('notificacion' => null,
							'fechainicio' => null,
							'fechafin' => null);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->user['id'] = $_SESSION['user']['id_usuario'];


		$this->CargarHotel();
		return;
	}

	public function busqueda(array $post){
		
		$this->setFecha1($post['fecha_inicio']);
		$this->setFecha2($post['fecha_fin']);
		$this->fecha1 = $post['fecha_inicio'];
		$this->fecha2 = $post['fecha_fin'];

		// header('location:'.HOST.'/Hotel/');
	}

	public function getFecha1(){
		return $this->fecha1;
	}

	public function getFecha2(){
		return $this->fecha2;
	}

	private function setFecha1($datetime = null){
		if($datetime){
			$datetime = str_replace('/', '-', $datetime);
			$datetime = strtotime($datetime);
			if(!$datetime){
				$this->error['fechainicio'] = 'Formato de fecha y hora incorrecto. Utiliza la herramienta.';
				return false;
			}
			$datetime = date("Y/m/d H:i:s", $datetime);
			$this->fechas['inicio'] = $datetime;
			return true;
		}
		$this->error['fechainicio'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setFecha2($datetime = null){
		if($datetime){
			$datetime = str_replace('/', '-', $datetime);
			$datetime = strtotime($datetime);
			if(!$datetime){
				$this->error['fechafin'] = 'Formato de fecha y hora incorrecto. Utiliza la herramienta.';
				return false;
			}
			$datetime = date("Y/m/d H:i:s", $datetime);
			$this->fechas['fin'] = $datetime;
			return true;
		}
		$this->error['fechafin'] = 'Este campo es obligatorio.';
		return false;
	}

	public function getFechaInicio(){
		return $this->fechas['inicio'];
	}

	public function getFechaFin(){
		return $this->fechas['fin'];
	}

	private function CargarHotel(){

		$query = "select h.id, fr.id  as idfranquiciatario from hotel as h 
			inner join franquiciatario as fr on h.codigo = fr.codigo_hotel
inner join solicitudfr as sfr on fr.id = sfr.id_franquiciatario 
			inner join usuario as u on sfr.id_usuario = u.id_usuario
				where u.id_usuario = :id";

		$stm = $this->con->prepare($query);
		$stm->bindParam(':id',$this->user['id'], PDO::PARAM_INT);
		$stm->execute();

		$fila = $stm->fetch(PDO::FETCH_ASSOC);
		$this->hotel['id'] = $fila['id'];
		$this->franquiciatario['id']  = $fila['idfranquiciatario'];

		$_SESSION['id_hotel'] = $this->hotel['id'];
		$_SESSION['id_franquiciatario'] = $this->franquiciatario['id'];
	}

	public function getOperaciones(){
		if($this->fechas['inicio'] and $this->fechas['fin']){
						$sql="SELECT COUNT(nven.venta)
						FROM negocio as ne
						JOIN negocio_venta as nven on ne.id_negocio = nven.id_negocio
						JOIN usuario as usu on nven.id_usuario = usu.id_usuario
						JOIN huesped as hu on usu.id_usuario = hu.id_usuario
						JOIN huespedhotel as hh on hu.id = hh.id_huesped
						JOIN hotel as h on hh.id_hotel = h.id
						where h.id = :idhotel and nven.creado between :fecha1 and :fecha2";
						$stmt = $this->con->prepare($sql);
						$stmt->execute(array(':idhotel'=>$this->hotel['id'],
											':fecha1'=>$this->fechas['inicio'],
											':fecha2'=>$this->fechas['fin'])); 
						$number_of_rows = $stmt->fetchColumn();
						$this->hotel['operations']=$number_of_rows;
						return $number_of_rows;
			}else{
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
	}

	public function getOperacionesNegocios(){

		if($this->fechas['inicio'] and $this->fechas['fin']){

			$sql=" SELECT (SELECT COUNT(ne.id_negocio)
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
			 where hu.id_usuario = nven.id_usuario and ne.situacion =1 and hot.id = :idhotel and nven.creado between :fecha1 and :fecha2";
					$stmt = $this->con->prepare($sql);

			$stmt->execute(array(':idhotel'=>$this->hotel['id'],
									':fecha1' => $this->fechas['inicio'],
									':fecha2' => $this->fechas['fin'])); 


			$fila = $stmt->fetch(PDO::FETCH_ASSOC);

			$porcentaje = number_format((float)$fila['porcentaje'], 2, '.', '');
			$html = '
				<strong>AFILIADOS: '.$fila['afiliados'].'</strong>
				<strong>OPERADOS: '.$fila['operados'].'</strong>
				<strong>'.$porcentaje.' %</strong>
			';
			return $html;

		}else{
			$sql=" SELECT (SELECT COUNT(ne.id_negocio)
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

	}

	public function getNegociosDeudores(){

		if($this->fechas['inicio'] and $this->fechas['fin']){
			$query = "SELECT COUNT(ne.id_negocio) as deudores
			FROM
			negocio_venta as nven INNER JOIN negocio as ne ON ne.id_negocio = nven.id_negocio
			INNER JOIN usuario as usu on usu.id_usuario = nven.id_usuario
			INNER JOIN huesped as hu  on hu.id_usuario = usu.id_usuario
			INNER JOIN huespedhotel as hp	ON hp.id_huesped = hu.id
			INNER JOIN hotel	as hot	ON hot.id = hp.id_hotel
			INNER JOIN divisa as di ON nven.iso = di.iso
			where ne.situacion = 1 and ne.saldo <=0 and hot.id = :idhotel and nven.creado between :fecha1 and :fecha2";
		  	$stm = $this->con->prepare($query);

		  	$stm->execute(array(':idhotel'=>$this->hotel['id'],
		  						':fecha1' => $this->fechas['inicio'],
		  						'fecha2' => $this->fechas['fin']));

		  	return $stm->fetch(PDO::FETCH_ASSOC)['deudores'];
		  }else{
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

	}

	public function getTotalComisionAdeudo(){

		if($this->fechas['inicio'] and $this->fechas['fin']){
			$query = "SELECT (((SUM(nven.venta)*(nven.comision))/100) * hot.comision / 100) + ne.saldo  as adeudo, nven.iso as divisa
						FROM
				negocio_venta as nven INNER JOIN negocio as ne ON ne.id_negocio = nven.id_negocio
				INNER JOIN usuario as usu on usu.id_usuario = nven.id_usuario
				INNER JOIN huesped as hu  on hu.id_usuario = usu.id_usuario
				INNER JOIN huespedhotel as hp	ON hp.id_huesped = hu.id
				INNER JOIN hotel	as hot	ON hot.id = hp.id_hotel
				INNER JOIN divisa as di ON nven.iso = di.iso
				where ne.situacion = 1  and ne.saldo <= 0 and hot.id = :idhotel and nven.creado between :fecha1 and :fecha2";

				$stm = $this->con->prepare($query);
  				$stm->execute(array(':idhotel'=>$this->hotel['id'],
  									':fecha1' =>$this->fechas['inicio'],
  									':fecha2' =>$this->fechas['fin']));
  				$fila = $stm->fetch(PDO::FETCH_ASSOC);
  				$comision = number_format((float)$fila['adeudo'],2,'.','');
  				if($fila['divisa'] == 'EUR'){
  					$div = '€';
  				}else{
  					$div = '$';
  				}
  				$total = $div.$comision.' '.$fila['divisa'];
  			return $total;
		}else{
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

		if($this->fechas['inicio'] and $this->fechas['fin']){
			$sql ="select AVG(nv.venta) as promedio, (select COUNT(venta) from negocio_venta) as nroventas 
				from negocio_venta as nv join balancehotel as bh on nv.id_venta = bh.id_venta
				join hotel as h on bh.id_hotel = :hotel and nv.creado between :fecha1 and :fecha2";
			$stmt = $this->con->prepare($sql);
			$stmt->bindParam(':hotel',$this->hotel['id'], PDO::PARAM_INT);
			$stmt->bindParam(':fecha1',$this->fechas['inicio'], PDO::PARAM_STR);
			$stmt->bindParam(':fecha2',$this->fechas['fin'], PDO::PARAM_STR);
			$stmt->execute(); 

			$promedio = $stmt->fetch(PDO::FETCH_ASSOC)['promedio'];

			$result=number_format((float)$promedio, 2, ',', '.');
			return $result;
		}else{
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
		if($this->fechas['inicio']){

			$sql="select COUNT(u.id_usuario) as usuarios from usuario as u join huesped as hu on u.id_usuario = hu.id_usuario
				join huespedhotel as hh on hu.id = hh.id_huesped 
				join hotel as h on hh.id_hotel = h.id
				where h.id =:hotel and u.creado between :fecha1 and :fecha2";
				$stmt = $this->con->prepare($sql);
				$stmt->bindParam(':hotel',$this->hotel['id']);
				$stmt->bindParam(':fecha1',$this->fechas['inicio']);
				$stmt->bindParam(':fecha2',$this->fechas['fin']);
				$stmt->execute(); 
				$usuarios = $stmt->fetch(PDO::FETCH_ASSOC)['usuarios'];
				return $usuarios;

		}else{

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
	}

	public function getUsuariosParticipantes(){
		
			if($this->fechas['inicio']){
				$sql="select COUNT(nv.id_usuario) as usuarios from negocio_venta as nv
				left join usuario as u on nv.id_usuario = u.id_usuario 
				left join balancehotel as bh on nv.id_venta = bh.id_venta
				left join hotel as h on bh.id_hotel = h.id
				where h.id = :hotel and nv.creado between :fecha1 and :fecha2 GROUP BY nv.id_usuario ";
				$stmt = $this->con->prepare($sql);
				$stmt->bindParam(':hotel',$this->hotel['id']);
				$stmt->bindParam(':fecha1',$this->fechas['inicio']);
				$stmt->bindParam(':fecha2',$this->fechas['fin']);
				
				$stmt->execute(); 
				$usuarios = $stmt->fetchAll();
				return count($usuarios);
			}else{
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
		if($this->fechas['inicio']){
			$sql="select SUM(nv.bono_esmarties) as puntos from negocio_venta as nv
				join usuario as u on nv.id_usuario = u.id_usuario
				join huesped as hu on u.id_usuario = hu.id_usuario 
				join huespedhotel as hh on hu.id = hh.id_huesped
				join hotel as h on hh.id_hotel = h.id
				where h.id = :hotel and nv.creado between :fecha1 and :fecha2";
			$stmt = $this->con->prepare($sql);
			$stmt->bindParam(':hotel',$this->hotel['id'],PDO::PARAM_INT);
			$stmt->bindParam(':fecha1',$this->fechas['inicio']);
			$stmt->bindParam(':fecha2',$this->fechas['fin']);
			$stmt->execute(); 
			$puntos = $stmt->fetch(PDO::FETCH_ASSOC)['puntos'];
			return number_format((float)$puntos, 2, ',','.');

		}else{
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
	}

	public function getPuntosCanjeados(){
		if($this->fechas['inicio']){
			$sql="select SUM(vt.precio) as canjeados from venta_tienda as vt 
				join usuario as u on vt.id_usuario = u.id_usuario 
				join huesped as hu on u.id_usuario = hu.id_usuario 
				join huespedhotel as hh on hu.id = hh.id_huesped 
				join hotel as h on hh.id_hotel = h.id	
				where h.id = :hotel and vt.creado between :fecha1 and :fecha2";
			$stmt = $this->con->prepare($sql);
			$stmt->bindParam(':hotel',$this->hotel['id'],PDO::PARAM_INT);
			$stmt->bindParam(':fecha1',$this->fechas['inicio']);
			$stmt->bindParam(':fecha2',$this->fechas['fin']);
			$stmt->execute(); 
			$puntos = $stmt->fetch(PDO::FETCH_ASSOC)['canjeados'];
			return number_format((float)$puntos, 2, ',','.');
		}else{
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
	}

	public function getRegalosEntregados($idhotel = null){
		if($this->fechas['inicio']){
				$sql="select COUNT(vt.id_venta) as regalos from venta_tienda as vt 
				join usuario as u on vt.id_usuario = u.id_usuario 
				join huesped as hu on u.id_usuario = hu.id_usuario 
				join huespedhotel as hh on hu.id = hh.id_huesped 
				join hotel as h on hh.id_hotel = h.id	
				where h.id = :hotel and vt.entrega = 1 and vt.creado between :fecha1 and :fecha2";
				$stmt = $this->con->prepare($sql);
				$stmt->bindParam(':hotel',$idhotel,PDO::PARAM_INT);
				$stmt->bindParam(':fecha1',$this->fechas['inicio']);
				$stmt->bindParam(':fecha2',$this->fechas['fin']);
				$stmt->execute(); 
				$regalos = $stmt->fetch(PDO::FETCH_ASSOC)['regalos'];
				return $regalos;
			}else{
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
	}

	public function getTotalRegalosPorUsuarios($idhotel=null,$fecha1 = null, $fecha2 = null){
		if($fecha1){
			$sql="select COUNT(vt.id_venta) as regalos, CONCAT(u.nombre,' ',u.apellido) as nombre, u.username from venta_tienda as vt 
				join usuario as u on vt.id_usuario = u.id_usuario 
 				join huesped as hu on u.id_usuario = hu.id_usuario 
 				join huespedhotel as hh on hu.id = hh.id_huesped 
 				join hotel as h on hh.id_hotel = h.id	
 				where h.id = :hotel and vt.entrega = 1 and vt.creado between :fecha1 and :fecha2
					GROUP BY nombre";
				$stmt = $this->con->prepare($sql);
				$stmt->bindParam(':hotel',$idhotel,PDO::PARAM_INT);
				$stmt->bindParam(':fecha1',$fecha1);
				$stmt->bindParam(':fecha2',$fecha2);
				$stmt->execute(); 
				return $stmt;
			}else{
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
	}

	public function getTotalValorRegalos(){
	
		if($this->fechas['inicio']){
				$sql="select sum(vt.precio) as valor from venta_tienda as vt 
				join usuario as u on vt.id_usuario = u.id_usuario
				join huesped as hu on u.id_usuario = hu.id_usuario 
				join huespedhotel as hh on hu.id = hh.id_huesped
				join hotel as h on hh.id_hotel = h.id
				where h.id = :hotel and vt.entrega = 1 and vt.creado between :fecha1 and :fecha2";
				$stmt = $this->con->prepare($sql);
				$stmt->bindParam(':hotel',$this->hotel['id'],PDO::PARAM_INT);
				$stmt->bindParam(':fecha1',$this->fechas['inicio']);
				$stmt->bindParam(':fecha2',$this->fechas['fin']);
				$stmt->execute(); 

				if( $stmt->fetch(PDO::FETCH_ASSOC)['valor']  > 0 ){
					$valor = number_format((float) $stmt->fetch(PDO::FETCH_ASSOC)['valor'],2,',','.');
				}else{
					$valor = 0;
				}
				return $valor;
			}else{
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
	}

	public function getValorRegaloPromedio(){
		if($this->fechas['inicio']){

		$sql="select AVG(vt.precio) as valor from venta_tienda as vt 
				join usuario as u on vt.id_usuario = u.id_usuario
				join huesped as hu on u.id_usuario = hu.id_usuario 
				join huespedhotel as hh on hu.id = hh.id_huesped
				join hotel as h on hh.id_hotel = h.id
				where h.id = :hotel and vt.entrega = 1 and vt.creado between :fecha1 and :fecha2";
				$stmt = $this->con->prepare($sql);
				$stmt->bindParam(':hotel',$this->hotel['id'],PDO::PARAM_INT);
				$stmt->bindParam(':fecha1',$this->fechas['inicio']);
				$stmt->bindParam(':fecha2',$this->fechas['fin']);
				$stmt->execute(); 

				if( $stmt->fetch(PDO::FETCH_ASSOC)['valor']  > 0 ){
					$valor = number_format((float) $stmt->fetch(PDO::FETCH_ASSOC)['valor'],2,',','.');
				}else{
					$valor = 0;
				}
				return $valor;
		}else{

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
		if($this->fechas['inicio'] and $this->fechas['fin']){
			
			$query  = "select nv.iso  as divisa, (select bf.balance as balance from balancefranquiciatario as bf where bf.id_franquiciatario  = :fr1 
								and bf.id = (select max(id) from balancefranquiciatario)) as balance
								from negocio_venta as nv join balancefranquiciatario as bf on nv.id_venta = bf.id_venta
								where bf.id_franquiciatario = :fr2 and bf.creado BETWEEN :fecha1 and :fecha2";

				$stm = $this->con->prepare($query);
				$stm->execute(array(':fr1'=>$this->franquiciatario['id'],
				                    ':fr2'=>$this->franquiciatario['id'],
				                	':fecha1' =>$this->fechas['inicio'],
				                	':fecha2' => $this->fechas['fin']));

				
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

		}else{
			$query  = "select nv.iso  as divisa, (select bf.balance as balance from balancefranquiciatario as bf where bf.id_franquiciatario  = :fr1 
								and bf.id = (select max(id) from balancefranquiciatario)) as balance
								from negocio_venta as nv join balancefranquiciatario as bf on nv.id_venta = bf.id_venta
								where bf.id_franquiciatario = :fr2 and bf.creado BETWEEN bf.creado and now()";

				$stm = $this->con->prepare($query);
				$stm->execute(array(':fr1'=>$this->franquiciatario['id'],
				                    ':fr2'=>$this->franquiciatario['id']));

				
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
				
	}

	public function getBalance(){
		$query  = "SELECT  bf.balance as balance
 					from  balancefranquiciatario as bf
 				where bf.id_franquiciatario = :idfranquiciatario and bf.id = (select max(id) from balancefranquiciatario)";
				$stm = $this->con->prepare($query);
				$stm->execute(array(':idfranquiciatario'=>$this->franquiciatario['id']));
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