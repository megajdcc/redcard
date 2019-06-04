<?php 
namespace socio\libs;
use assets\libs\connection;
use PDO;

class user_purchases {
	private $con;
	private $user = array(
		'id' => null,
		'purchases' => array()
	);
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
		$this->user['id'] = $_SESSION['user']['id_usuario'];
		return;
	}


	public function cambiarStatusPagado(int $idventa){


		if($this->con->inTransaction()){
			$this->con->rollBack();
		}

		$this->con->beginTransaction();

		$sql = "UPDATE venta_tienda set pagado = 1 where id_venta=:venta";

		try {
				$stm = $this->con->prepare($sql);
				
				$stm->bindParam(':venta',$idventa);
				
				$stm->execute();
				
				$this->con->commit();
		} catch (PDOException $e) {

			$this->error_log(__METHOD__,__LINE__,$e->getMessage());
			$this->con->rollBack();
			return false;
			
		}

		return true;

	}
	public function load_data($page = null, $rpp = null){
		$query = "SELECT COUNT(*) FROM venta_tienda WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->pagination['total'] = $row['COUNT(*)'];
			$this->pagination['rpp'] = $rpp;
			$this->pagination['max'] = (int)ceil($this->pagination['total'] / $this->pagination['rpp']);
			$this->pagination['page'] = min($this->pagination['max'], $page);
			$this->pagination['offset'] = ($this->pagination['page'] - 1) * $this->pagination['rpp'];
			// Variables retornables
			$pagination['page'] = $this->pagination['page'];
			$pagination['total'] = $this->pagination['total'];
			// Cargar los certificados
			$query = "SELECT p.envio,vt.pagado, vt.id_venta, vt.id_producto, p.imagen, p.nombre, vt.precio, vt.entrega, vt.situacion, vt.creado
				FROM venta_tienda vt
				INNER JOIN producto p ON vt.id_producto = p.id_producto
				WHERE vt.id_usuario = :id_usuario
				ORDER BY vt.creado DESC 
				LIMIT :limit OFFSET :offset";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_usuario', $this->user['id'], PDO::PARAM_INT);
				$stmt->bindValue(':limit', $this->pagination['rpp'], PDO::PARAM_INT);
				$stmt->bindValue(':offset', $this->pagination['offset'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->user['purchases'][$row['id_venta']] = array(
					'product_id' => $row['id_producto'],
					'image'      => $row['imagen'],
					'name'       => $row['nombre'],
					'price'      => $row['precio'],
					'deliver'    => $row['entrega'],
					'status'     => $row['situacion'],
					'created_at' => $row['creado'],
					'pagado'     =>$row['pagado'],
					'penvio'     =>number_format((float)$row['envio'],2,'.','')
				);
			}
			return $pagination;
		}
		return false;
	}

	public function get_purchases(){
		$html = null;
		foreach ($this->user['purchases'] as $key => $value) {
			if($value['status'] == 1){
				$status = '<span class="label btn-xs label-success pull-left mr20">Entregado</span>';
				$msg = '';
				$coupon = '';
			}else{
				$status = '<span class="label btn-xs label-danger pull-left mr20">No entregado</span>';
				if($value['deliver'] == 2){

					if($value['pagado'] == 1 ){
						$msg = '<p class="text-danger">Su compra esta en proceso de envio.</p>';
						$coupon='';
						?>
					<?php }else{
							$msg = '<p class="text-danger">Su compra estar&aacute; en mostrador por los siguientes 7 d&iacute;as despu&eacute;s de la compra. Para recibirlo por env&iacute;o debe completar el pago.</p>';
							$coupon ="<button class='btn-pagar' data-precioenvio='".$value['penvio']."' data-idventa='".$key."'><i class='fa fa-pay'></i>Pagar Ahora</button>";
					}
				
				}else{
					$coupon = '';
					$msg = '<p class="text-danger">Su compra estar&aacute; en mostrador por los siguientes 7 d&iacute;as despu&eacute;s de la compra.</p>';
				}
			}
			$date = date('d/m/Y \a \l\a\s g:i A', strtotime($value['created_at']));
			$image = HOST.'/assets/img/store/'._safe($value['image']);
			$name = _safe($value['name']);
			$url = HOST.'/tienda/producto/'.$value['product_id'];

			$html .= 
			'<div class="col-sm-12">
				<div class="background-white p15 mb30">
					<div class="page-title">
					'.$status.'N. de Ticket: <strong class="text-default">'.$key.'</strong><div class="cert-date pull-right">'.$date.'</div>
					</div>
					'.$msg.'
					<a class="user user-lg" href="'.$url.'" target="_blank">
						<img class="low-border" src="'.$image.'">
					</a>
					<div class="display-inline-block">
						<strong class="text-default">'.$name.'</strong>
					</div>
					'.$coupon.'
				</div>
			</div>';
		}
		if(is_null($html)){
			$html = '<div class="background-white p20 text-default">No has realizado ninguna compra en la tienda de Travel Points.</div>';
		}else{
			$html = '<div class="row">'.$html.'</div>';
		}
		return $html;
	}

	public function get_count(){
		$i = $this->pagination['total'];
		if($i > 0){
			return 'He comprado '.$i.' cosas en la tienda de Travel Points';
		}else{
			return 'Mis compras en la tienda de Travel Points';
		}
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
		file_put_contents(ROOT.'\assets\error_logs\user_purchases.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>