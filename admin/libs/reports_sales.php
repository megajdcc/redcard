<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace admin\libs;

require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
use assets\libs\connection;


use \Dompdf\Dompdf as pdf;
use \Dompdf\Options;
use PDO;

class reports_sales {
	private $con;

	public $usuario = 0,$negocio = 0;

	private $business = array(
		'id' => null,
		'date_start' => null,
		'date_end' => null,
		'sales' => array(),
		'user_id' => null,
		'business_category' => null,
	);
	private $sales = array();
	private $bonus = 0;

	private $negocios = array(
		'id' => null,
		'fecha_inicio' => null,
		'fecha_fin' => null,
		'negocios' => array(),
		'id_usuario' => null
	);
		private $busqueda = array('fechainicio' => null,'fechafin' => null);
	private $error = array('notification' => null, 'date_start' => null, 'date_end' => null);

	private $estadocuenta = array();
	public function __construct(connection $con){
		$this->con = $con->con;

		$this->cargarData();
		return;
	}

	private function cargarData(){

		if(!empty($this->busqueda['fechainicio']) && !empty($this->busqueda['fechafin']) && empty($this->usuario) && empty($this->negocio)){

				// echo var_dump($this->busqueda);
				// 
		$query = "
(select ne.nombre as negocio, u.username, CONCAT(u.nombre,' ',u.apellido) as nombre, nv.venta, bs.comision, bs.balance,nv.creado 
						FROM balancesistema as bs
						LEFT JOIN negocio_venta as nv on bs.id_venta = nv.id_venta
						LEFT JOIN negocio as ne on nv.id_negocio = ne.id_negocio 
						LEFT JOIN usuario as u on nv.id_usuario = u.id_usuario where nv.creado BETWEEN :fecha1 and :fecha2)
UNION 
	(select  'Retiro Comision' as negocio, 'Retiro Comision' as username, 'Retiro Comision' as nombre, CONCAT('-',rr.monto) as venta, CONCAT('-',rr.monto) as comision,bs.balance,bs.creado
						from retirocomisionsistema as rr  join balancesistema as bs on rr.id = bs.id_retiro where bs.creado BETWEEN :fecha3 and :fecha4
	)
						ORDER BY creado 
";
							$stm = $this->con->prepare($query);
							$stm->execute(array(':fecha1' => $this->busqueda['fechainicio'],
												':fecha2' => $this->busqueda['fechafin'],
												':fecha3' => $this->busqueda['fechainicio'],
												':fecha4' => $this->busqueda['fechafin']));

							$this->estadocuenta = $stm->fetchAll(PDO::FETCH_ASSOC);
		}else if(empty($this->busqueda['fechainicio']) && empty($this->busqueda['fechafin']) && empty($this->usuario) && empty($this->negocio)){
					$query = "(select ne.nombre as negocio, u.username, CONCAT(u.nombre,' ',u.apellido) as nombre, nv.venta, bs.comision, bs.balance,nv.creado 
						FROM balancesistema as bs
						LEFT JOIN negocio_venta as nv on bs.id_venta = nv.id_venta
						LEFT JOIN negocio as ne on nv.id_negocio = ne.id_negocio 
						LEFT JOIN usuario as u on nv.id_usuario = u.id_usuario)
						UNION 
						(select  'Retiro Comision' as negocio, 'Retiro Comision' as username, 'Retiro Comision' as nombre, CONCAT('-',rr.monto) as venta, CONCAT('-',rr.monto) as comision,bs.balance,bs.creado
						from retirocomisionsistema as rr  join balancesistema as bs on rr.id = bs.id_retiro
						)
						ORDER BY creado ";

			$stm = $this->con->prepare($query);
			$stm->execute();
			$this->estadocuenta = $stm->fetchAll(PDO::FETCH_ASSOC);
		}else if(!empty($this->busqueda['fechainicio']) && !empty($this->busqueda['fechafin']) && !empty($this->usuario) && !empty($this->negocio)){
					$query = "(select ne.nombre as negocio, u.username, CONCAT(u.nombre,' ',u.apellido) as nombre, nv.venta, bs.comision, bs.balance,nv.creado 
						FROM balancesistema as bs
						LEFT JOIN negocio_venta as nv on bs.id_venta = nv.id_venta
						LEFT JOIN negocio as ne on nv.id_negocio = ne.id_negocio 
						LEFT JOIN usuario as u on nv.id_usuario = u.id_usuario where nv.id_usuario = :usuario1 and nv.id_negocio = :negocio1 and nv.creado BETWEEN :fecha1 and :fecha2)
						UNION 
						(select  'Retiro Comision' as negocio, 'Retiro Comision' as username, 'Retiro Comision' as nombre, CONCAT('-',rr.monto) as venta, CONCAT('-',rr.monto) as comision,bs.balance,bs.creado
						from retirocomisionsistema as rr  join balancesistema as bs on rr.id = bs.id_retiro where  bs.creado BETWEEN :fecha3 and :fecha4
						)
						ORDER BY creado ";

			$stm = $this->con->prepare($query);
			$stm->execute(array(':fecha1' => $this->busqueda['fechainicio'],
								':fecha2' => $this->busqueda['fechafin'],
								':fecha3' => $this->busqueda['fechainicio'],
								':fecha4' => $this->busqueda['fechafin'],
								':usuario1' => $this->usuario,
								':negocio1' => $this->negocio));
			$this->estadocuenta = $stm->fetchAll(PDO::FETCH_ASSOC);
		}
	}

	public function getEstadoCuenta(){
			$query = "select max(balance) as balance from balancesistema";

		$stm = $this->con->prepare($query);

		$stm->execute();

		$ultimobalance = $stm->fetch(PDO::FETCH_ASSOC)['balance'];
		
		foreach ($this->estadocuenta as $key => $value) {
			
			// $fecha = date('d/m/Y h:m:s', strtotime($value['creado']));
			 $fecha = $value['creado'];
			 // settype($value['venta'],'double');

			 $venta = number_format((float)$value['venta'],2,',','.');

			  if($venta < 0){

			  	$venta = '<strong class="negativo">$'.$venta.'</strong>';

			  }else{

			  	$venta = '$'.$venta;

			  }

			  if($value['comision'] < 0){
			  	
			  	$comision = '<strong class="negativo">$'.number_format((float)$value["comision"],2,',','.').'</strong>';
			  }else{
			  	$comision = '$'.number_format((float)$value["comision"],2,',','.');
			  }
			if(!empty($value['nombre'])){
				$nombre = $value['nombre'];
				}else{
					$nombre = $value['username'];
				}

			
			?>
			

		 		<tr class="estado">
					<td class="b1"><?php echo $fecha ?></td>
					<td class="b1"><?php echo $value['negocio'] ?></td>
					<td class="b1"><?php echo $nombre ?></td>
					<td class="b1"><?php echo $venta; ?></td>
					<td class="b1"><?php echo $comision; ?></td>
					<td class="b2">$<?php echo $value['balance'] ?></td>
				</tr>
<?php  }
	}


	private function setFechainicio($datetime = null){
		if($datetime){
			$datetime = str_replace('/', '-', $datetime);
			$datetime = strtotime($datetime);
			if(!$datetime){
				$this->error['fechainicio'] = 'Formato de fecha y hora incorrecto. Utiliza la herramienta.';
				return false;
			}
			$datetime = date("Y/m/d H:i:s", $datetime);
			$this->busqueda['fechainicio'] = $datetime;
			return true;
		}
		$this->error['fechainicio'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setFechafin($datetime = null){
		if($datetime){
			$datetime = str_replace('/', '-', $datetime);
			$datetime = strtotime($datetime);
			if(!$datetime){
				$this->error['fechafin'] = 'Formato de fecha y hora incorrecto. Utiliza la herramienta.';
				return false;
			}
			$datetime = date("Y/m/d H:i:s", $datetime);
			$this->busqueda['fechafin'] = $datetime;
			return true;
		}
		$this->error['fechafin'] = 'Este campo es obligatorio.';
		return false;
	}

	public function getFecha1(){

		return $this->negocios['fecha_inicio'];
	}

	public function getFecha2(){
		return $this->negocios['fecha_fin'];
	}

	private function setFecha1($fecha){
		$this->negocios['fecha_inicio'] = $fecha;
	}

	private function setFecha2($fecha){
		$this->negocios['fecha_fin'] = $fecha;
	}

	private function setUsuario(int $usuario){
		$this->usuario =$usuario;
	}

	private function setNegocio(int $negocio){
		$this->negocio = $negocio;
	}


	public function getNombreUsuario(){
		$query ="select u.username,concat(u.nombre,' ',u.apellido) as nombre from usuario as u where u.id_usuario = :usuario";
		$stm = $this->con->prepare($query);
		$stm->execute(array(':usuario'=>$this->usuario));

		$fila = $stm->fetch(PDO::FETCH_ASSOC);

		if(empty($fila['nombre'])){
			$nombre = $fila['username'];
		}else{
			$nombre = $fila['nombre'];
		}
		return $nombre;
	}

	public function getNombreNegocio(){

		$query ="select n.nombre as negocio from negocio as n where n.id_negocio = :negocio";
		$stm = $this->con->prepare($query);
		$stm->execute(array(':negocio'=>$this->negocio));
		$fila = $stm->fetch(PDO::FETCH_ASSOC);
		return $fila['negocio'];

	}

	public function getUsuario(){

		$html = null;	


		$query = "select u.id_usuario, u.username,concat(u.nombre,' ',u.apellido) as nombre from usuario as u ";
		$stm = $this->con->prepare($query);
		$stm->execute();

		$fila =$stm->fetchAll(PDO::FETCH_ASSOC);
		foreach ($fila as $key => $value) {
			if(empty($value['nombre'])){
			$nombre = $value['username'];
			}else{
				$nombre = $value['nombre'];
			}

			if($value['id_usuario'] == $this->usuario){

				$html .='<option value="'.$value['id_usuario'].'" selected>'.$nombre.'</option>';
			}else{
					$html .='<option value="'.$value['id_usuario'].'">'.$nombre.'</option>';
			}

		

		}
		

		return $html;

	}

	public function Buscar($post){

		$this->setFecha1($post['date_start']);
		$this->setFecha2($post['date_end']);

		$this->setFechainicio($post['date_start']);
		$this->setFechafin($post['date_end']);

		$this->setUsuario($post['usuario']);
		$this->setNegocio($post['negocio']);

		$this->CargarData();

	}

	public function mostrarpdf(array $post = null){


		$this->setFechainicio($post['date_start']);
		$this->setFechafin($post['date_end']);

		$this->setUsuario($post['usuario']);
		$this->setNegocio($post['negocio']);

		$this->CargarData();
		

			ob_start();
			require_once($_SERVER['DOCUMENT_ROOT'].'/admin/viewreports/estadocuenta.php');

			$context = stream_context_create([
				'ssl'=>[
					'verify_peer' => FALSE,
					'verify_peer_name' =>FALSE,
					'allow_self_signed' => TRUE
				]
			]);
	
			$html = ob_get_clean();
			$option = new Options();
			$option->isPhpEnabled(true);
			$option->isRemoteEnabled(true);
			$option->setIsHtml5ParserEnabled(true);
			
			$dompdf = new pdf($option);
			$dompdf->setHttpContext($context);
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dato = array('Attachment' => 0);

			$fecha1 = date('M-Y', strtotime($this->busqueda['fechainicio']));
		
			$titulo = "Travel Points: reporte de actividades " .$fecha1;
			$dompdf->stream($titulo.'.pdf',$dato);
		
}
	public function load_data(){
		if($this->business['date_start'] && $this->business['date_end']){
			$dates = " AND nv.creado BETWEEN '".$this->business['date_start']."' AND '".$this->business['date_end']."'";
		}
		else{
			$dates = '';
		}
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
		$query = "SELECT nv.id_venta, u.username, u.nombre, u.apellido, u.esmarties, p.pais, nv.iso, nv.venta, nv.comision, nv.bono_esmarties, nv.creado, n.nombre as user_negoico, r.username as e_username, r.nombre as e_nombre, r.apellido as e_apellido 
			FROM negocio_venta nv
			INNER JOIN usuario u ON nv.id_usuario = u.id_usuario
			INNER JOIN negocio n ON n.id_negocio = nv.id_negocio
			LEFT JOIN ciudad c ON u.id_ciudad = c.id_ciudad
			LEFT JOIN estado e ON c.id_estado = e.id_estado
			LEFT JOIN pais p ON e.id_pais = p.id_pais
			INNER JOIN usuario r ON nv.id_empleado = r.id_usuario 
			WHERE n.id_negocio = nv.id_negocio $dates
			ORDER BY nv.id_venta ASC";

			if($this->business['date_start'] && $this->business['date_end'] && $this->business['user_id']){
				$query = "SELECT nv.id_venta, u.username, u.nombre, u.apellido, u.esmarties, p.pais, nv.iso, nv.venta, nv.comision, nv.bono_esmarties, nv.creado, n.nombre as user_negoico, r.username as e_username, r.nombre as e_nombre, r.apellido as e_apellido 
				FROM negocio_venta nv
				INNER JOIN usuario u ON nv.id_usuario = u.id_usuario
				INNER JOIN negocio n ON n.id_negocio = nv.id_negocio
				LEFT JOIN ciudad c ON u.id_ciudad = c.id_ciudad
				LEFT JOIN estado e ON c.id_estado = e.id_estado
				LEFT JOIN pais p ON e.id_pais = p.id_pais
				INNER JOIN usuario r ON nv.id_empleado = r.id_usuario 
				WHERE nv.id_usuario='".$this->business['user_id']."' $dates
				ORDER BY nv.id_venta ASC";

			}if($this->business['date_start'] && $this->business['date_end'] && $this->business['business_category_id']){
				$query = "SELECT nv.id_venta, u.username, u.nombre, u.apellido, u.esmarties, p.pais, nv.iso, nv.venta, nv.comision, nv.bono_esmarties, nv.creado, n.nombre as user_negoico, r.username as e_username, r.nombre as e_nombre, r.apellido as e_apellido 
				FROM negocio_venta nv
				INNER JOIN usuario u ON nv.id_usuario = u.id_usuario
				INNER JOIN negocio n ON n.id_negocio = nv.id_negocio
				LEFT JOIN ciudad c ON u.id_ciudad = c.id_ciudad
				LEFT JOIN estado e ON c.id_estado = e.id_estado
				LEFT JOIN pais p ON e.id_pais = p.id_pais
				INNER JOIN usuario r ON nv.id_empleado = r.id_usuario 
				WHERE nv.id_negocio='".$this->business['business_category_id']."' $dates
				ORDER BY nv.id_venta ASC";
			}if($this->business['date_start'] && $this->business['date_end'] && $this->business['business_category_id'] && $this->business['user_id']){
				$query = "SELECT nv.id_venta, u.username, u.nombre, u.apellido, u.esmarties, p.pais, nv.iso, nv.venta, nv.comision, nv.bono_esmarties, nv.creado, n.nombre as user_negoico, r.username as e_username, r.nombre as e_nombre, r.apellido as e_apellido
				FROM negocio_venta nv
				INNER JOIN usuario u ON nv.id_usuario = u.id_usuario
				INNER JOIN negocio n ON n.id_negocio = nv.id_negocio
				LEFT JOIN ciudad c ON u.id_ciudad = c.id_ciudad
				LEFT JOIN estado e ON c.id_estado = e.id_estado
				LEFT JOIN pais p ON e.id_pais = p.id_pais
				INNER JOIN usuario r ON nv.id_empleado = r.id_usuario 
				WHERE nv.id_usuario='".$this->business['user_id']."' AND nv.id_negocio='".$this->business['business_category_id']."' $dates
				ORDER BY nv.id_venta ASC";
			}
		try{
			$stmt = $this->con->prepare($query);
			// $stmt->bindValue('id_negocio', $this->business['id'], PDO::PARAM_INT);
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
				'user_esmarties' => $row['esmarties'],
				'user_negoico' => $row['user_negoico'],
				'created_at' => $row['creado'],
				'e_username' => $row['e_username'],
				'e_name' => $row['e_nombre'],
				'e_last_name' => $row['e_apellido']
			);
			$this->sales[$row['iso']] += $row['venta'];
			$this->bonus += $row['bono_esmarties'];
		}
		return;
	}

	public function set_date(array $post){
		$this->business['user_id'] = $post['user_id'];
		$this->business['business_category_id'] = $post['category_id'];
		$this->set_date_start($post['date_start']);
		$this->set_date_end($post['date_end']);
		if(!array_filter($this->error)){
			$this->load_data();
			return;
		}
		return false;
	}

	public function get_sales(){		
		$sales = null;
		foreach ($this->business['sales'] as $key => $value) {
			$date = date('d/m/Y g:i A', strtotime($value['created_at']));
			$total = number_format((float)$value['total'], 2, '.', '').' '.$value['iso'];
			$commission = _safe($value['commission']);
			$esmarties = number_format((float)$value['esmarties'], 2, '.', '');
			if(empty($value['name']) && empty($value['last_name'])){
				$customer = _safe($value['username']);
			}else{
				$customer = _safe($value['name'].' '.$value['last_name']);
			}
			$origin = _safe($value['country']);
			if(empty($value['e_name']) && empty($value['e_last_name'])){
				$registrar = _safe($value['e_username']);
			}else{
				$registrar = _safe($value['e_name'].' '.$value['e_last_name']);
			}

			?>
			
			

			<?php
			$sales .= 
				'<tr>
					<td>'.$key.'</td>
					<td>'.$date.'</td>
					<td>'._safe($value['user_negoico']).'</td>
					<td>'.$registrar.'</td>
					<td>$'.$total.'</td>
					<td>$'._safe($value['commission']).'</td>
					<td>$'._safe($value['user_esmarties']).'</td>
				</tr>';
		}
		$html = 
			'<div class="table-responsive">
				<table class="table table-bordered table-hover">
					<thead>
					<tr>
						<th>#</th>
						<th>Fecha y hora</th>
						<th>Negocio</th>
						<th>Usario</th>
						<th>Venta</th>
						<th>Comisi&oacute;n</th>
						<th>Balance</th>
					</tr>
					</thead>
					<tbody>
					'.$sales.'
					</tbody>
				</table>
			</div>';
		return $html;
	}

	private function set_date_start($datetime = null){
		if($datetime){
			$datetime = str_replace('/', '-', $datetime);
			$datetime = strtotime($datetime);
			if(!$datetime){
				$this->error['date_start'] = 'Formato de fecha y hora incorrecto. Utiliza la herramienta.';
				return false;
			}
			$datetime = date("Y/m/d H:i:s", $datetime);
			$this->business['date_start'] = $datetime;
			return true;
		}
		$this->error['date_start'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_date_end($datetime = null){
		if($datetime){
			$datetime = str_replace('/', '-', $datetime);
			$datetime = strtotime($datetime);
			if(!$datetime){
				$this->error['date_end'] = 'Formato de fecha y hora incorrecto. Utiliza la herramienta.';
				return false;
			}
			$datetime = date("Y/m/d H:i:s", $datetime);
			$this->business['date_end'] = $datetime;
			return true;
		}
		$this->error['date_end'] = 'Este campo es obligatorio.';
		return false;
	}

	public function get_total_sales(){
		$html = null;
		foreach ($this->sales as $key => $value) {
			if($value != 0){
				$html .= 
				'<div class="col-sm-3">
					<div class="form-group">
						<label for="'.$key.'">Total de venta: '.$key.'</label>
						<input class="form-control" type="text" id="'.$key.'" value="'.$value.'" readonly />
					</div>
				</div>';
			}
		}
		return $html;
	}

	public function get_total_esmarties(){
		return _safe($this->bonus);
	}

	public function get_date_start(){
		if($this->business['date_start']){
			$datetime = date("d/m/Y h:i A", strtotime($this->business['date_start']));
			return $datetime;
		}
	}

	public function get_user_id(){
		if($this->business['user_id']){
			return $this->business['user_id'];
		}
	}

	public function get_business_category_id(){
		// if($this->business['business_category_id']){
		// 	return $this->business['business_category_id'];
		// }
	}

	public function get_date_start_error(){
		if($this->error['date_start']){
			$error = '<p class="text-danger">'.$this->error['date_start'].'</p>';
			return $error;
		}
	}

	public function get_date_end(){
		if($this->business['date_end']){
			$datetime = date("d/m/Y h:i A", strtotime($this->business['date_end']));
			return $datetime;
		}
	}

	public function get_date_end_error(){
		if($this->error['date_end']){
			$error = '<p class="text-danger">'.$this->error['date_end'].'</p>';
			return $error;
		}
	}

	public function get_notification(){
		if(isset($_SESSION['notification']['success'])){
			$notification = 
			'<div class="alert alert-icon alert-dismissible alert-success" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notification']['success']).'
			</div>';
			unset($_SESSION['notification']['success']);
			return $notification;
		}
		if($this->error['notification']){
			$error = 
			'<div class="alert alert-icon alert-dismissible alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->error['notification']).'
			</div>';
			return $error;
		}
	}

	public function get_profile_url(){
		return HOST.'/'._safe($this->business['url']);
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\reports_sales.txt', '['.date('d/M/Y h:i:s A').' | '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['notification'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>