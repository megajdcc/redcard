<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace negocio\libs;
use assets\libs\connection;
use PDO;

class preference_video {
	private $con;
	private $business = array('id' => null, 'url' => null, 'video' => array('id' => null, 'content' => null));
	private $error = array('notification' => null, 'video' => null);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->business['id'] = $_SESSION['business']['id_negocio'];
		$this->business['url'] = $_SESSION['business']['url'];
		$this->load_data();
		return;
	}

	private function load_data(){
		$query = "SELECT p.id_preferencia, np.preferencia
			FROM preferencia p 
			LEFT JOIN negocio_preferencia np ON p.id_preferencia = np.id_preferencia AND np.id_negocio = ?
			WHERE p.llave = 'business_video'";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(1, $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['video']['id'] = $row['id_preferencia'];
			$this->business['video']['content'] = $row['preferencia'];
		}
		return;
	}

	public function set_video(array $post){
		if(!isset($post['video']) || empty($post['video'])){
			$this->error['video'] = 'Este campo es obligatorio.';
			return false;
		}else{
			$video = $post['video'];
		}
		if(is_null($this->business['video']['content'])){
			$query = "INSERT INTO negocio_preferencia (id_negocio, id_preferencia, preferencia) 
				VALUES (:id_negocio, :id_preferencia, :preferencia)";
			$query_params = array(
				':id_negocio' => $this->business['id'],
				':id_preferencia' => $this->business['video']['id'],
				':preferencia' => $video
			);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($query_params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$_SESSION['notification']['success'] = 'Video guardado correctamente.';
		}elseif($video != $this->business['video']['content']){
			$query = "UPDATE negocio_preferencia SET preferencia = :preferencia 
				WHERE id_negocio = :id_negocio AND id_preferencia = :id_preferencia";
			$query_params = array(
				':preferencia' => $video,
				':id_negocio' => $this->business['id'],
				':id_preferencia' => $this->business['video']['id']
			);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($query_params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$_SESSION['notification']['success'] = 'Video actualizado correctamente.';
		}
		header('Location: '.HOST.'/negocio/preferencias/video');
		die();
		return;
	}

	public function unset_video(){
		$query = "DELETE FROM negocio_preferencia WHERE id_negocio = :id_negocio AND id_preferencia = :id_preferencia";
		$query_params = array(':id_negocio' => $this->business['id'], ':id_preferencia' => $this->business['video']['id']);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($query_params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$_SESSION['notification']['success'] = 'Video borrado correctamente.';
		header('Location: '.HOST.'/negocio/preferencias/video');
		die();
		return;
	}

	public function get_video(){
		$html = null;
		if($this->business['video']['content']){
			if($this->business['video']['content'] != strip_tags($this->business['video']['content'])){
				// Contiene HTML (iFrame)
				preg_match('/src="([^"]+)"/', $this->business['video']['content'], $match);
				$url = $match[1];
				$html = 
				'<div class="center mb30">
					<iframe src="'.$url.'" width="560" height="315" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
				</div>';
			}else{
				// No contiene HTML (direct link)
				$url = $this->youtube_id_from_url($this->business['video']['content']);
				$html = 
				'<div class="center mb30">
					<iframe src="https://www.youtube.com/embed/'.$url.'" width="560" height="315" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
				</div>';
			}
		}
		return $html;
	}

	private function youtube_id_from_url($url) {
		$pattern = 
			'%^# Match any youtube URL
			(?:https?://)?  # Optional scheme. Either http or https
			(?:www\.)?      # Optional www subdomain
			(?:             # Group host alternatives
			  youtu\.be/    # Either youtu.be,
			| youtube\.com  # or youtube.com
			  (?:           # Group path alternatives
				/embed/     # Either /embed/
			  | /v/         # or /v/
			  | /watch\?v=  # or /watch\?v=
			  )             # End path alternatives.
			)               # End host alternatives.
			([\w-]{10,12})  # Allow 10-12 for 11 char youtube id.
			%x'
			;
		$result = preg_match($pattern, $url, $matches);
		if ($result) {
			return $matches[1];
		}
		return false;
	}

	public function get_url(){
		return HOST.'/'._safe($this->business['url']);
	}


	public function get_video_content(){
		return _safe($this->business['video']['content']);
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

	public function get_video_error(){
		if($this->error['video']){
			$error = '<p class="text-danger">'._safe($this->error['video']).'</p>';
			return $error;
		}
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\preference_video.txt', '['.date('d/M/Y h:i:s A').' | '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['notification'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>