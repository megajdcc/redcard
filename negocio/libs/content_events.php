<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace negocio\libs;
use assets\libs\connection;
use PDO;

class content_events {
	private $con;
	private $business = array(
		'id' => null, 
		'url' => null,
		'events' => array(),
		'event_id' => null,
		'image' => array('tmp_name' => null, 'file_name' => null, 'path' => null),
		'date_start' => null,
		'date_end' => null,
		'title' => null,
		'content' => null,
		'modal' => null
	);
	private $error = array(
		'image' => null,
		'date_start' => null,
		'date_end' => null,
		'title' => null,
		'content' => null,
		'modal' => false,
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
		return;
	}

	public function load_data($page = null, $rpp = null){
		$query = "SELECT COUNT(*) FROM negocio_evento WHERE situacion = 1 AND id_negocio = :id_negocio";
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
			// Cargar los posts
			$query = "SELECT id_evento, titulo, contenido, fecha_inicio, fecha_fin, imagen, creado 
				FROM negocio_evento 
				WHERE id_negocio = :id_negocio AND situacion = 1 
				ORDER BY creado DESC LIMIT :limit OFFSET :offset";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
				$stmt->bindValue(':limit', $this->pagination['rpp'], PDO::PARAM_INT);
				$stmt->bindValue(':offset', $this->pagination['offset'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->business['events'][$row['id_evento']] = array(
					'title' => $row['titulo'],
					'content' => $row['contenido'],
					'date_start' => $row['fecha_inicio'],
					'date_end' => $row['fecha_fin'],
					'image' => $row['imagen'],
					'created_at' => $row['creado']
				);
			}
			return $pagination;
		}
		return false;
	}

	public function get_events(){
		$html = null;
		foreach ($this->business['events'] as $key => $value) {
			$title = _safe($value['title']);
			$content = _safe($value['content']);
			$date['start'] = date('d/m/Y \a \l\a\s g:i A', strtotime($value['date_start']));
			$date['end'] = date('d/m/Y \a \l\a\s g:i A', strtotime($value['date_end']));
			$date['start_'] = date('d/m/Y g:i A', strtotime($value['date_start']));
			$date['end_'] = date('d/m/Y g:i A', strtotime($value['date_end']));
			$date['created'] = date('d/m/Y \a \l\a\s g:i A', strtotime($value['created_at']));
			$image = _safe($value['image']);
			if($this->error['modal'] && $this->business['event_id'] == $key){
				$image_error = $this->get_image_error();
				$title_error = $this->get_title_error();
				$content_error = $this->get_content_error();
				$date_start_error = $this->get_date_start_error();
				$date_end_error = $this->get_date_end_error();
				$this->business['modal'] = 
				'<script>
					$("#modal-'.$key.'").modal("show");
				</script> ';
			}else{
				$image_error = $title_error = $content_error = $date_start_error = $date_end_error = null;
			}
			$html .= 
			'<div class="background-white p20 mb30">
				<div class="row">
					<div class="col-sm-3 detail-gallery-preview">
						<a href="'.HOST.'/assets/img/business/event/'.$image.'">
							<img class="img-thumbnail img-rounded post-img" src="'.HOST.'/assets/img/business/event/'.$image.'">
						</a>
					</div>
					<div class="col-sm-9">
						<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'">
							<div class="page-title">
								Inicia: '.$date['start'].' | Termina: '.$date['end'].'
								<input type="hidden" name="id" value="'.$key.'">
								<button class="btn btn-xs btn-danger pull-right delete-event" type="submit" name="delete_event"><i class="fa fa-times m0"></i></button>
								<button class="btn btn-xs btn-info pull-right edit-event mr5" type="button" data-toggle="modal" data-target="#modal-'.$key.'"><i class="fa fa-pencil m0"></i></button>
							</div>
						</form>
						<strong class="text-default">'.$title.'</strong>
						<p>'.nl2br($content).'</p>
						<hr>
						<p>Publicado el '.$date['created'].'</p>
					</div>
				</div>
			</div>
			<div class="modal fade" id="modal-'.$key.'" tabindex="-1" role="dialog" aria-labelledby="label-'.$key.'">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="label-'.$key.'">Editar evento</h4>
						</div>
						<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'" enctype="multipart/form-data">
							<div class="modal-body">
								<div class="p30">
									<div class="form-group" data-toggle="tooltip" title="Puedes actualizar la imagen o dejar el campo en blanco para conservar la actual">
										<label for="post-image">Foto <i class="fa fa-question-circle text-secondary"></i></label>
										<input type="file" id="post-image" name="image">
										'.$image_error.'
									</div><!-- /.form-group -->
									<div class="row">
										<div class="col-sm-6">
											<div class="form-group">
												<label for="start">Fecha y hora de inicio <span class="required">*</span></label>
												<div class="input-group date" id="event-start">
													<input class="form-control" type="text" id="start" name="date_start" value="'.$date['start_'].'" placeholder="Fecha y hora de inicio" required/>
													<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
												</div>
												'.$date_start_error.'
											</div>
										</div>
										<div class="col-sm-6">
											<div class="form-group">
												<label for="end">Fecha y hora de fin <span class="required">*</span></label>
												<div class="input-group date" id="event-end">
													<input class="form-control" type="text" id="end" name="date_end" value="'.$date['end_'].'" placeholder="Fecha y hora de fin" required/>
													<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
												</div>
												'.$date_end_error.'
											</div>
										</div>
									</div>
									<div class="form-group">
										<label for="title">T&iacute;tiulo <span class="required">*</span></label>
										<input class="form-control" type="text" id="title" name="title" value="'.$title.'" placeholder="T&iacute;tiulo" required>
										'.$title_error.'
									</div><!-- /.form-group -->
									<div class="form-group">
										<label for="content">Contenido <span class="required">*</span></label>
										<textarea class="form-control" id="content" name="content" rows="5" placeholder="Contenido" required>'.$content.'</textarea>
										'.$content_error.'
									</div><!-- /.form-group -->
								</div>
							</div>
							<div class="modal-footer">
								<input type="hidden" name="id" value="'.$key.'">
								<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
								<button type="submit" class="btn btn-success" name="edit_event">Guardar cambios</button>
							</div>
						</form>
					</div>
				</div>
			</div>';
		}
		if(is_null($html)){
			$html = '<div class="background-white p30"><h4>No hay eventos.</h4></div>';
		}
		return $html;
	}

	public function delete_event(array $post){
		if(array_key_exists($post['id'], $this->business['events'])){
			$query = "UPDATE negocio_evento SET situacion = 0 WHERE id_negocio = :id_negocio AND id_evento = :id_evento";
			$params = array(':id_negocio' => $this->business['id'], 'id_evento' => $post['id']);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$_SESSION['notification']['success'] = 'Evento eliminado correctamente.';
			header('Location: '._safe($_SERVER['REQUEST_URI']));
			die();
		}
		return;
	}

	public function edit_event(array $post, array $files){
		if(array_key_exists($post['id'], $this->business['events'])){
			$this->business['event_id'] = $post['id'];
			$this->set_date_start($post['date_start']);
			$this->set_date_end($post['date_end']);
			$this->set_title($post['title']);
			$this->set_content($post['content']);
			$this->set_image($files);
			if(!array_filter($this->error)){
				if($this->business['image']['tmp_name'] && $this->business['image']['path']){
					if(file_exists(ROOT.'/assets/img/business/post/'.$this->business['events'][$post['id']]['image'])){
						unlink(ROOT.'/assets/img/business/post/'.$this->business['events'][$post['id']]['image']);
					}
					if(!move_uploaded_file($this->business['image']['tmp_name'], $this->business['image']['path'])){
						$this->error['error'] = 'La publicación no se ha podido editar correctamente.';
						return false;
					}
					$file_name = $this->business['image']['file_name'];
				}else{
					$file_name = $this->business['events'][$post['id']]['image'];
				}
				$query = "UPDATE negocio_evento SET 
					fecha_inicio = :fecha_inicio,
					fecha_fin = :fecha_fin,
					titulo = :titulo,
					contenido = :contenido,
					imagen = :imagen
					WHERE id_negocio = :id_negocio AND id_evento = :id_evento";
				$params = array(
					':fecha_inicio' => $this->business['date_start'],
					':fecha_fin' => $this->business['date_end'],
					':titulo' => $this->business['title'],
					':contenido' => $this->business['content'],
					':imagen' => $file_name,
					':id_negocio' => $this->business['id'],
					':id_evento' => $post['id']
				);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				$_SESSION['notification']['success'] = 'Evento editado correctamente.';
				header('Location: '._safe($_SERVER['REQUEST_URI']));
				die();
			}
			$this->error['warning'] = 'Uno o más campos tienen errores. Revísalos cudiadosamente.';
			$this->error['modal'] = true;
			return false;
		}
		return;
	}

	private function set_image($files = null){
		if(!$this->business['title']){
			return;
		}
		// RECORTAR NOMBRE DE IMAGEN
		$image_prefix = _safe('-'.$this->business['url'].'-evento-esmart-club');
		$max = 150 - strlen($image_prefix);
		$safe_name = $this->friendly_url($this->business['title']);
		if(strlen($safe_name) > $max){
			$file = substr($safe_name, 0, $max);
		}else{
			$file = $safe_name;
		}
		$file_name = $file.$image_prefix;
		$image = new \assets\libraries\bulletproof\bulletproof($files);
		$image->setName($file_name);
		$image->setLocation(ROOT.'/assets/img/business/event');
		if($image['image']){
			if($image->upload()){
				// REVISAR QUE SEA UNICA
				try{
					$query = "SELECT 1 FROM negocio_evento WHERE imagen = :imagen";
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(':imagen', $image->getName().'.'.$image->getMime(), PDO::PARAM_STR);
					$stmt->execute();
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				if($row = $stmt->fetch()){
					$image->setName($image->getName().'-'.time()); // AGREGAR EL TIEMPO SI NO LO ES
				}
				$this->business['image']['tmp_name'] = $files['image']['tmp_name'];
				$this->business['image']['file_name'] = $image->getName().'.'.$image->getMime();
				$this->business['image']['path'] = $image->getFullPath();
				return true;
			}
			$this->error['image'] = $image['error'];
			return false;
		}
		if($files['image']['error'] == 1){
			$this->error['image'] = 'Has excedido el límite de imagen de 2MB.';
		}
		return;
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

	private function set_title($string = null){
		if($string){
			$string = trim($string);
			$this->business['title'] = $string;
			return true;
		}
		$this->error['title'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_content($string = null){
		if($string){
			$string = trim($string);
			$this->business['content'] = $string;
			return true;
		}
		$this->error['content'] = 'Este campo es obligatorio.';
		return false;
	}

	public function get_image_error(){
		if($this->error['image']){
			return '<p class="text-danger">'.$this->error['image'].'</p>';
		}
	}

	public function get_title(){
		return _safe($this->business['title']);
	}

	public function get_title_error(){
		if($this->error['title']){
			$error = '<p class="text-danger">'.$this->error['title'].'</p>';
			return $error;
		}
	}

	public function get_content(){
		return _safe($this->business['content']);
	}

	public function get_content_error(){
		if($this->error['content']){
			$error = '<p class="text-danger">'.$this->error['content'].'</p>';
			return $error;
		}
	}

	public function get_date_start_error(){
		if($this->error['date_start']){
			$error = '<p class="text-danger">'.$this->error['date_start'].'</p>';
			return $error;
		}
	}

	public function get_date_end_error(){
		if($this->error['date_end']){
			$error = '<p class="text-danger">'.$this->error['date_end'].'</p>';
			return $error;
		}
	}

	public function show_modal(){
		return $this->business['modal'];
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
		file_put_contents(ROOT.'\assets\error_logs\content_events.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}

	private function friendly_url($url){
		// 1. No spaces to begin or end, replace all special characters and lowercase everything
		$url = strtolower($this->replace_accents(trim($url)));
		// decode html maybe needed if there's html I normally don't use this
		//$url = html_entity_decode($url,ENT_QUOTES,'UTF8');
		// 2. Replacing spaces and union characters with -
		$find = array(' ', '&', '\r\n', '\n', '+',',');
		$url = str_replace($find, '-', $url);
		// 3. Delete and replace the rest of special characters
		$find = array('/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/');
		$repl = array('', ' ', '');
		$url = str_replace(' ','-',trim(preg_replace($find, $repl, $url)));
		return $url;
	}

	private function replace_accents($var){
		$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
		$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
		$var = str_replace($a, $b, $var);
		return $var;
	}
}
?>