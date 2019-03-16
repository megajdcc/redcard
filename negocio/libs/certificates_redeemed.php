<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace negocio\libs;
use assets\libs\connection;
use PDO;

class certificates_redeemed {
	private $con;
	private $user = array('id' => null);
	private $business = array(
		'id' => null,
		'url' => null,
		'redeemed' => array()
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
		$this->business['id'] = $_SESSION['business']['id_negocio'];
		$this->business['url'] = $_SESSION['business']['url'];
		$this->user['id'] = $_SESSION['user']['id_usuario'];
		return;
	}

	public function load_data($page = null, $rpp = null){
		$query = "SELECT COUNT(*) FROM usar_certificado uc
			INNER JOIN negocio_certificado ne ON uc.id_certificado = ne.id_certificado 
			WHERE ne.id_negocio = :id_negocio";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
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
			// Cargar los usados
			$query = "SELECT uc.id_uso, ne.descripcion, ne.url, ne.imagen, ne.nombre as certificado, u.username, u.nombre, u.apellido, u.imagen as imagen_u, uc.situacion, uc.actualizado
				FROM usar_certificado uc
				INNER JOIN negocio_certificado ne ON uc.id_certificado = ne.id_certificado
				INNER JOIN usuario u ON uc.id_usuario = u.id_usuario
				WHERE ne.id_negocio = :id_negocio
				ORDER BY uc.actualizado DESC LIMIT :limit OFFSET :offset";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue('id_negocio', $this->business['id'], PDO::PARAM_INT);
				$stmt->bindValue(':limit', $this->pagination['rpp'], PDO::PARAM_INT);
				$stmt->bindValue(':offset', $this->pagination['offset'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->business['redeemed'][$row['id_uso']] = array(
					'url' => $row['url'],
					'certificate' => $row['certificado'],
					'description' => $row['descripcion'],
					'cert_image' => $row['imagen'],
					'username' => $row['username'],
					'name' => $row['nombre'],
					'last_name' => $row['apellido'],
					'user_image' => $row['imagen_u'],
					'status' => $row['situacion'],
					'updated_at' => $row['actualizado']
				);
			}
			return $pagination;
		}
		return false;
	}

	public function get_redeemed(){
		$html = null;
		foreach ($this->business['redeemed'] as $key => $value) {
			$username = _safe($value['username']);
			if(!empty($value['name']) || !empty($value['last_name'])){
				$alias = _safe($value['name'].' '.$value['last_name']);
			}else{
				$alias = $username;
			}
			$user_image = $value['user_image'];
			$cert_image = $value['cert_image'];
			$name = _safe($value['certificate']);
			$description = _safe($value['description']);
			$form = '';
			if($value['status'] == 1){
				$status = '<span class="label btn-xs label-success pull-left mr20">Canjeado</span>';
			}elseif($value['status'] == 2){
				$status = '<span class="label btn-xs label-warning pull-left mr20">Apartado</span>';
				$form = 
				'<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'">
					<input type="hidden" name="id" value="'.$key.'">
					<button class="btn btn-xs btn-danger pull-right cancel-redeem" type="submit" name="cancel_redeem" data-toggle="tooltip" title="Cancelar apartado" data-placement="left"><i class="fa fa-times m0"></i></button>
				</form>';
			}else{
				$status = '<span class="label btn-xs label-danger pull-left mr20">Cancelado</span>';
			}
			$date = date('d/m/Y g:i A', strtotime($value['updated_at']));
			$html .= 
			'<div class="background-white p15 mb30">
				<div class="page-title">

				'.$form.$status.'<div class="cert-date">'.$date.'</div>
				</div>
				<div class="row">
					<div class="col-sm-4">
						<a class="user user-lg" href="'.HOST.'/socio/'.$username.'" target="_blank">
							<img class="low-border" src="'.HOST.'/assets/img/user_profile/'.$user_image.'">
						</a>
						'.$alias.'
					</div>
					<div class="col-sm-8">
						<a class="user user-lg" href="'.HOST.'/certificado/'.$value['url'].'" target="_blank">
							<img class="low-border" src="'.HOST.'/assets/img/business/certificate/'.$cert_image.'">
						</a>
						<div class="display-inline-block">
							<strong class="text-default">'.$name.'</strong>
							<p>'.nl2br($description).'</p>
						</div>
					</div>
				</div>
			</div>';
		}
		if(is_null($html)){
			$html = '<div class="background-white p30"><h4>No se han canjeado certificados de regalo.</h4></div>';
		}
		return $html;
	}

	public function cancel_redeem(array $post){
		if(array_key_exists($post['id'], $this->business['redeemed'])){
			$query = "UPDATE usar_certificado SET situacion = 0 WHERE id_uso = :id_uso";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_uso', $post['id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$_SESSION['notification']['success'] = 'Cancelado el apartado del certificado correctamente.';
			header('Location: '._safe($_SERVER['REQUEST_URI']));
			die();
		}
		return;
	}

	public function get_profile_url(){
		return HOST.'/'._safe($this->business['url']);
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
		file_put_contents(ROOT.'\assets\error_logs\admin_new.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>