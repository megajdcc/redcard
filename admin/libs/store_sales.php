<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace admin\libs;
use assets\libs\connection;
use PDO;

class store_sales{
	private $con;
	private $sales = array();
	private $error = array(
		'warning' => null,
		'error' => null
	);
	private $pagination = array(
		'total' => null,
		'rpp' => null,
		'max' => null,
		'page' => null,
		'offset' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		return;
	}

	public function load_data($page = null, $rpp = null){
		$query = "SELECT COUNT(id_venta) as ventas FROM venta_tienda";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->pagination['total'] = $row['ventas'];
			$this->pagination['rpp'] = $rpp;
			$this->pagination['max'] = (int)ceil($this->pagination['total'] / $this->pagination['rpp']);
			$this->pagination['page'] = min($this->pagination['max'], $page);
			$this->pagination['offset'] = ($this->pagination['page'] - 1) * $this->pagination['rpp'];
			// Variables retornables
			$pagination['page'] = $this->pagination['page'];
			$pagination['total'] = $this->pagination['total'];
			// Cargar los certificados
			$query = "SELECT vt.id_venta, vt.id_producto, p.nombre as producto, p.imagen, vt.precio, u.username, u.nombre, u.apellido, u.email, u.telefono, u.domicilio, u.codigo_postal, vt.entrega, vt.situacion, vt.creado
				FROM venta_tienda vt
				INNER JOIN usuario u ON vt.id_usuario = u.id_usuario
				INNER JOIN producto p ON vt.id_producto = p.id_producto
				ORDER BY vt.creado DESC
				LIMIT :limit OFFSET :offset";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':limit', $this->pagination['rpp'], PDO::PARAM_INT);
				$stmt->bindValue(':offset', $this->pagination['offset'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->sales[$row['id_venta']] = $row;
			}
			return $pagination;
		}else{
			$pagination['total'] = 0;
			$pagination['page'] = 0;
			return $pagination;
		}
		return false;
	}

	public function get_sales(){
		$sales = null;
		foreach ($this->sales as $key => $value) {
			$id = $value['id_venta'];
			$image = HOST.'/assets/img/store/'._safe($value['imagen']);
			if($value['entrega'] == 1){
				$label = '<span class="label label-sm label-primary">Entrega</span>';
			}elseif($value['entrega'] == 2){
				$label = '<span class="label label-sm label-secondary">Env&iacute;o</span>';
			}elseif($value['entrega'] == 3){
				$label = '<span class="label label-sm label-success">Digital</span>';
			}else{
				$label = '';
			}
			if($value['situacion'] == 1){
				if($value['entrega'] == 2){
					$status = '<span class="label label-success">Enviado</span>';
				}else{
					$status = '<span class="label label-success">Entregado</span>';
				}
				$btn = '<i class="fa fa-check-circle m0 text-success"></i>';
			}else{
				$status = '<span class="label label-warning">En tienda</span>';
				$btn = '<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'"><button type="submit" class="btn btn-xs btn-success send-product" name="sent" value="'.$id.'"><i class="fa fa-check m0"></i></button></form>';
			}
			$product_id =  $value['id_producto'];
			$name = _safe($value['producto']);
			$price = number_format((float)$value['precio'], 2, '.', '');
			$date = date('d/m/Y', strtotime($value['creado']));
			$username = _safe($value['username']);
			if($value['nombre'] || $value['apellido']){
				$alias = trim(_safe($value['nombre'].' '.$value['apellido']));
			}else{
				$alias = $username;
			}
			$email = _safe($value['email']);
			$phone = _safe($value['telefono']);
			$home = _safe($value['domicilio']);
			$postal = _safe($value['codigo_postal']);

			$sales .= 
				'<tr>
					<td>'.$btn.'</td>
					<td>'.$id.'</td>
					<td>'.$status.'</td>
					<td>
						<div class="user user-md">
							<a href="'.HOST.'/tienda/producto/'.$product_id.'" target="_blank"><img class="low-border" src="'.$image.'"></a>
						</div>
					</td>
					<td><a href="'.HOST.'/tienda/producto/'.$product_id.'" target="_blank">'.$name.'</a></td>
					<td>Tp$ '.$price.'</td>
					<td>'.$date.'</td>
					<td>'.$label.'</td>
					<td><a href="'.HOST.'/socio/'.$username.'" target="_blank">'.$alias.'</a></td>
					<td>'.$email.'</td>
					<td>'.$phone.'</td>
					<td>'.$home.'</td>
					<td>'.$postal.'</td>
				</tr>';
		}
		$html = 
			'<div class="table-responsive">
				<table class="table table-hover">
					<thead>
					<tr>
						<th></th>
						<th>Ticket</th>
						<th>Situacion</th>
						<th>Imagen</th>
						<th>Nombre</th>
						<th>Precio</th>
						<th>Fecha compra</th>
						<th>Tipo</th>
						<th>Cliente</th>
						<th>Email</th>
						<th>Tel&eacute;fono</th>
						<th>Domicilio</th>
						<th>C.Postal</th>
					</tr>
					</thead>
					<tbody>
					'.$sales.'
					</tbody>
				</table>
			</div>';
		return $html;
	}

	public function get_products_pdf(){
		$query = "SELECT vt.id_venta, vt.id_producto, p.nombre as producto, p.imagen, vt.precio, u.username, u.nombre, u.apellido, u.email, u.telefono, u.domicilio, u.codigo_postal, vt.entrega, vt.situacion, vt.creado
				FROM venta_tienda vt
				INNER JOIN usuario u ON vt.id_usuario = u.id_usuario
				INNER JOIN producto p ON vt.id_producto = p.id_producto
				ORDER BY vt.creado DESC";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$rows = null;
		while($row = $stmt->fetch()){
			$id = $row['id_venta'];
			if($row['entrega'] == 1){
				$label = 'Entrega';
			}elseif($row['entrega'] == 2){
				$label = 'Env&iacute;o';
			}elseif($row['entrega'] == 3){
				$label = 'Digital';
			}else{
				$label = '';
			}
			if($row['situacion'] == 1){
				if($row['entrega'] == 2){
					$status = 'Enviado';
				}else{
					$status = 'Entregado';
				}
			}else{
				$status = 'En tienda';
			}
			$product_id =  $row['id_producto'];
			$name = _safe($row['producto']);
			$price = number_format((float)$row['precio'], 2, '.', '');
			$date = date('d/m/Y', strtotime($row['creado']));
			$username = _safe($row['username']);
			if($row['nombre'] || $row['apellido']){
				$alias = trim(_safe($row['nombre'].' '.$row['apellido']));
			}else{
				$alias = $username;
			}
			$email = _safe($row['email']);
			$phone = _safe($row['telefono']);
			$home = _safe($row['domicilio']);
			$postal = _safe($row['codigo_postal']);

			$rows .= 
				'<tr>
					<td>'.$id.'</td>
					<td>'.$status.'</td>
					<td>'.$name.'</td>
					<td>Tp$ '.$price.'</td>
					<td>'.$date.'</td>
					<td>'.$label.'</td>
					<td>'.$alias.'</td>
					<td>'.$email.'</td>
					<td>'.$phone.'</td>
					<td>'.$postal.'</td>
					<td>'.$home.'</td>
				</tr>';
		}
		$html = 
'<style type="text/css">
	#cabecera{
		background:#f7f8f9;
		padding:10px 20px;
		border-radius: 6px;
	}
	h1,h2{
		float:left;
		margin: 5px;
	}
	table {
		width: 100%;
		border-spacing: 0;
		border-collapse: collapse;
		padding: 8px;
	}
	.table-bordered, th, td{
		border: 1px solid #ddd;
		padding: 5px;
	}

</style>
<page style="font-size: 10px">
	<div id="cabecera">
		<h1>Travel Points</h1>
		<h2>Reporte de ventas en tienda</h2>
	</div>
	<table class="table-bordered">
		<thead>
			<tr>
				<th>Ticket</th>
				<th>Situacion</th>
				<th>Nombre</th>
				<th>Precio</th>
				<th>Fecha compra</th>
				<th>Tipo</th>
				<th>Cliente</th>
				<th>Email</th>
				<th>Tel&eacute;fono</th>
				<th>C.Postal</th>
				<th>Domicilio</th>
			</tr>
		</thead>
		<tbody>
		'.$rows.'
		</tbody>
	</table>
</page>';

		require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/vendor/autoload.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/vendor/spipu/html2pdf/html2pdf.class.php';
		$html2pdf = new \HTML2PDF('P','A4','es');
		$html2pdf->WriteHTML($html);
		$html2pdf->Output('reporte.pdf');
		return;
	}

	public function sent_product(array $post){
		if(!array_key_exists($post['sent'], $this->sales)){
			$this->error['error'] = 'Error al tratar de realizar la operación';
			return false;
		}else{
			$id = (int)$post['sent'];
		}
		$query = "UPDATE venta_tienda SET situacion = 1 WHERE id_venta = :id_venta";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_venta', $id, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$_SESSION['notification']['success'] = 'Operación realizada con éxito.';
		header('Location: '._safe($_SERVER['REQUEST_URI']));
		die();
		return;
	}

	public function get_notification(){
		$html = null;
		if(isset($_SESSION['notification']['success'])){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-success" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notification']['success']).'
			</div>';
			unset($_SESSION['notification']['success']);
		}
		if(isset($_SESSION['notification']['info'])){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-info" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notification']['info']).'
			</div>';
			unset($_SESSION['notification']['info']);
		}
		if($this->error['warning']){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-warning" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->error['warning']).'
			</div>';
		}
		if($this->error['error']){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->error['error']).'
			</div>';
		}
		return $html;
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\store_sales.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>