<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace admin\libs;
use assets\libs\connection;
use PDO;

class SolicitudPerfil {
	private $con;
	private $user = array(
		'id' => null
	);
	private $request = array();
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

	public function load_data($page = null, $rpp = null){
		
		$query = "(select count(*) from solicitudhotel as sh where condicion !=0)
				 	UNION
				 	(select count(*) from solicitudfr as sfr where condicion != 0)
				 	UNION
					(select count(*) from solicitudreferidor as sr where condicion != 0)";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->pagination['total'] = $row['count(*)'];
			$this->pagination['rpp'] = $rpp;
			$this->pagination['max'] = (int)ceil($this->pagination['total'] / $this->pagination['rpp']);
			$this->pagination['page'] = min($this->pagination['max'], $page);
			$this->pagination['offset'] = ($this->pagination['page'] - 1) * $this->pagination['rpp'];
			// Variables retornables
			$pagination['page'] = $this->pagination['page'];
			$pagination['total'] = $this->pagination['total'];
			// Cargar los certificados
			$query = "SELECT s.id_solicitud, u.username, u.nombre, u.apellido, s.nombre as negocio, s.id_categoria, nc.categoria, s.situacion, s.creado 
				FROM solicitud_negocio s
				INNER JOIN usuario u ON s.id_usuario = u.id_usuario
				INNER JOIN negocio_categoria nc ON s.id_categoria = nc.id_categoria 
				WHERE situacion != 0
				ORDER BY s.creado DESC
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
				$this->request[$row['id_solicitud']] = array(
					'username' => $row['username'],
					'name' => $row['nombre'],
					'last_name' => $row['apellido'],
					'business' => $row['negocio'],
					'category_id' => $row['id_categoria'],
					'category' => $row['categoria'],
					'status' => $row['situacion'],
					'created_at' => $row['creado']
				);
			}
			return $pagination;
		}
		return false;
	}

	public function getSolicitudes(){

		$sentenciaSQL = "(SELECT sh.id, CONCAT(u.nombre,' ', u.apellido)as nombre,u.username, 'Hotel' as perfil, sh.condicion, sh.creado
							FROM solicitudhotel as sh 
							INNER JOIN usuario as u on sh.id_usuario = u.id_usuario
							JOIN hotel as h on sh.id_hotel = h.id
							
							ORDER BY sh.creado DESC)
								UNION
								(SELECT sfr.id, CONCAT(u.nombre,' ',u.apellido) as nombre,u.username, 'Franquiciatario' as perfil, sfr.condicion,sfr.creado
												from solicitudfr as sfr
												INNER JOIN usuario as u on sfr.id_usuario = u.id_usuario 
												where sfr.hotel !=''
												order by sfr.creado DESC)
								UNION
								(SELECT sr.id, CONCAT(u.nombre,' ',u.apellido) as nombre,u.username, 'Referidor' as perfil, sr.condicion, sr.creado
												from solicitudreferidor as sr
												INNER JOIN usuario as u on sr.id_usuario = u.id_usuario 
												where sr.hotel !=''
												order by sr.creado DESC)";

		$result = $this->con->prepare($sentenciaSQL);
		$result->execute();

		$urlimg =  HOST.'/assets/img/user_profile/';
		while ($fila = $result->fetch(PDO::FETCH_ASSOC)) {

			if($fila['condicion'] == 0){
				$status = '<span class="label label-warning mr5">Pendiente</span>';
			}else if($fila['condicion'] == 1){
				$status = '<span class="label label-success mr5">Aceptado</span>';
			}else if($fila['condicion'] == 3){
				$status = '<span class="label label-info mr5">Revisi&oacute;n</span>';
			}else if($fila['condicion'] == 4){
				$status = '<span class="label label-danger mr5">Rechazada</span>';
			}
			$solicitud = $fila['id'];
			if($fila['nombre'] == null){
				$nombre = $fila['username'];
			}else{
				$nombre    = $fila['nombre'];
			}
			
			$perfil    = $fila['perfil'];
			$condicion = $fila['condicion'];
			
			$creado = date('d/m/Y g:i A', strtotime($fila['creado']));
			?>
			<tr id="fila-<?php echo $solicitud; ?>" >
				<td><label class="cert-date mr20"><?php echo '# '.$solicitud;?></label></td>
				<td><?php echo $status; ?></td>
				<td><label class="cert-date mr20"><?php echo $creado;?></label></td>
				<td><?php  echo $nombre;?></td>
				<td><?php  echo $perfil;?></td>
				<td>
					<a  class="btn btn-primary btn-xs pull-right" href="<?php echo HOST.'/admin/perfiles/solicitud.php?solicitud='.$solicitud.'&perfil='.$perfil?>">Ver detalles</a>
				</td>
            </tr>
			<?php
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
		file_put_contents(ROOT.'\assets\error_logs\request_listing.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>