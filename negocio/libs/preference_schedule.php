<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace negocio\libs;
use assets\libs\connection;
use PDO;

class preference_schedule {
	private $con;
	private $business = array('id'> null, 'url' => null, 'schedule' => array());
	private $days = array(
		1 => array('Lunes','mon'), 
		2 => array('Martes','tues'), 
		3 => array('Mi&eacute;rcoles','wed'), 
		4 => array('Jueves','thurs'), 
		5 => array('Viernes','fri'), 
		6 => array('S&aacute;bado','sat'), 
		7 => array('Domingo','sun')
	);
	private $error = array('notification' => null);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->business['id'] = $_SESSION['business']['id_negocio'];
		$this->business['url'] = $_SESSION['business']['url'];
		$this->load_data();
		return;
	}

	private function load_data(){
		$query = "SELECT id_horario, dia, hora_apertura, hora_cierre FROM negocio_horario WHERE id_negocio = :id_negocio";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->business['schedule'][$row['dia']]['open'] = $row['hora_apertura'];
			$this->business['schedule'][$row['dia']]['close'] = $row['hora_cierre'];
		}
		return;
	}

	public function set_schedule(array $post){
		$i = 1;
		foreach ($post as $key => $value){
			if(!empty($value[0]) && !empty($value[1])){
				$open = date('H:i:s', strtotime($value[0]));
				$close = date('H:i:s', strtotime($value[1]));
			}else{
				$open = $close = null;
			}
			if($open != $this->business['schedule'][$i]['open'] || $close != $this->business['schedule'][$i]['close']){
				$query = "UPDATE negocio_horario SET hora_apertura = :hora_apertura, hora_cierre = :hora_cierre 
					WHERE id_negocio = :id_negocio AND dia = :dia";
				$params = array(
					':id_negocio' => $this->business['id'],
					':dia' => $i, 
					':hora_apertura' => $open,
					':hora_cierre' => $close);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
			}
			$i++;
		}
		$_SESSION['notification']['success'] = 'Horario de Trabajo actualizado correctamente.';
		header('Location: '.HOST.'/negocio/preferencias/horario');
		die();
		return;
	}

	public function get_url(){
		return HOST.'/'._safe($this->business['url']);
	}

	public function get_schedule(){
		$html = null;
		foreach($this->business['schedule'] as $key => $value){
			$day = $this->days[$key];
			if(!is_null($value['open']) && !is_null($value['close'])){
				$open = date('h:i A', strtotime($value['open']));
				$close = date('h:i A', strtotime($value['close']));
				$label = '<span class="label label-success">Abierto</span>';
			}else{
				$close = $open = null;
				$label = '<span class="label label-danger">Cerrado</span>';
			}
			$html .= 
				'<div class="row">
					<div class="col-md-3">
						<div class="form-group">
							<label>'.$day[0].'</label>
							<label class="pull-right">'.$label.'</label>
						</div>
						<div class="form-group">
						</div>
					</div>
					<div class="col-sm-6 col-md-4">
						<div class="input-group date schedule">
							<input class="form-control" type="text" name="'.$day[1].'[]" value="'.$open.'" placeholder="Apertura" />
							<span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
						</div>
					</div>
					<div class="col-sm-6 col-md-4">
						<div class="input-group date schedule">
							<input class="form-control" type="text" name="'.$day[1].'[]" value="'.$close.'" placeholder="Cierre" />
							<span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
						</div>
					</div>
				</div>';

		}
		return $html;
	}

	public function get_notification(){
		if(isset($_SESSION['notification']['success'])){
			$notification = 
			'<div class="alert alert-icon alert-dismissible alert-success mb50" role="alert">
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
			'<div class="alert alert-icon alert-dismissible alert-danger mb50" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->error['notification']).'
			</div>';
			return $error;
		}
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\preference_schedule.txt', '['.date('d/M/Y h:i:s A').' | '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['notification'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>