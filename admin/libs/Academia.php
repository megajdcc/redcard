<?php 
namespace admin\libs;

use assets\libs\connection as conexion;

use PDO;
/**
 * @author Crespo jhonatan
 * @since 27/07/2019
 */
class Academia
{
	
	private $conexion;
	private $clases = array();


	private $titulo,$descripcion,$categoria,$contenido,$url;

	private $error = array(
			'error' => null,
			'warning' => null,
	);
	function __construct(conexion $conexion)
	{
		$this->conexion = $conexion->con;
		$this->cargar();
	}




// Functions of class Academia...
 

	/**
	 * Como primer trabajo de la class Academia, su objetivo es cargar todos las clases academicas de aprendizaje de travel points... 
	 * @return [void] 
	 */
	private function cargar(){

		$sql = "SELECT v.id, v.titulo,v.contenido,v.url_video,v.descripcion, cv.categoria FROM video_blog as v join categoria_video as cv on  v.id_categoria = cv.id";

		$stm = $this->conexion->prepare($sql);
		$stm->execute();	

		if($stm->rowCount() > 0 ){
			$this->clases = $stm->fetchAll(PDO::FETCH_ASSOC);
		}
	}

	public function listarcategoria(){
		$sql = "SELECT categoria, id FROM categoria_video order by categoria asc";

		$stm = $this->conexion->prepare($sql);
		$stm->execute();


		while($row = $stm->fetch(PDO::FETCH_ASSOC)){

			if(!empty($row['categoria'])){
				echo "<option value='".$row['id']."'>".$row['categoria']."</option>";
			}
			
		}

	}


	public function getVideos(){

		foreach ($this->clases as $key => $value) {
			echo '<div class="contenedor-video col-sm-12 col-md-4" id="class-'.$value['id'].'">
				<div class="card-simple" data-background-image="'.HOST.'/assets/img/academia/clases.jpg">
					<div class="card-simple-background">
						<div class="card-simple-content">
							<h2><a href="">'.$value['titulo'].'</a></h2>

							<div class="card-simple-actions">
								
								<button type="button" name="reproducir" class="reproducir" data-url="'.$value['url_video'].'" data-id="'.$value['id'].'" title="Reproducir"><i class="fa fa-play-circle"></i></button>
								<button type="button" name="eliminar" class="eliminar" data-id="'.$value['id'].'" title="Eliminar Clase"><i class="fa fa-trash-o"></i></button>
							</div>
						</div>
						<div class="card-simple-label">'.$value['categoria'].'</div>
						
					</div>
				</div>
			</div>
			 ';
		}


	}

	public function search_clases(string $busqueda){


		$sql = "SELECT v.titulo, v.id, c.categoria,v.url_video FROM video_blog as v join categoria_video as c on v.id_categoria = c.id
						where v.titulo like :busqueda1 || v.descripcion like :busqueda2 || c.categoria like :busqueda3";


		$stm = $this->conexion->prepare($sql);
		$stm->execute(array(':busqueda1'=>'%'.$busqueda.'%',
							':busqueda2'=>'%'.$busqueda.'%',
							':busqueda3'=>'%'.$busqueda.'%'
							));


		$dato = array();
		foreach ($stm->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
			
			$dato[] = '<div class="contenedor-video col-sm-12 col-md-4" id="class-'.$value['id'].'">
				<div class="card-simple" data-background-image="'.HOST.'/assets/img/academia/clases.jpg">
					<div class="card-simple-background">
						<div class="card-simple-content">
							<h2><a href="">'.$value['titulo'].'</a></h2>

							<div class="card-simple-actions">
								
								<button type="button" name="reproducir" class="reproducir" data-id="'.$value['id'].'" title="Reproducir" data-url="'.$value['url_video'].'"><i class="fa fa-play-circle"></i></button>
								<button type="button" name="eliminar" class="eliminar" data-id="'.$value['id'].'" title="Eliminar Clase"><i class="fa fa-trash-o"></i></button>
							</div>
						</div>
						<div class="card-simple-label">'.$value['categoria'].'</div>
						
					</div>
				</div>
			</div>';

		}

		return $dato;

	}


	public function getClass(int $idclass){

		$sql = "SELECT v.id, v.titulo,v.descripcion,c.categoria,c.id as idcategoria, v.contenido,v.url_video from video_blog as v
						join categoria_video as c on v.id_categoria = c.id
							where v.id = :id";

			$stm = $this->conexion->prepare($sql);
			$stm->bindParam(':id',$idclass,PDO::PARAM_INT);

			$stm->execute();


			$datos = array(	
							'id'           =>null,
							'titulo'       =>null,
							'descripcion'  =>null,
							'categoria'    =>null,
							'idcategoria' =>null,
							'contenido'    =>null,
							'urlvideo'    =>null);

			foreach ($stm->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
					$datos['id'] = $value['id'];
					$datos['titulo'] = _safe($value['titulo']);
					$datos['descripcion'] = _safe($value['descripcion']);
					$datos['categoria'] = _safe($value['categoria']);
					$idcategoria = $value['idcategoria'];


					$datos['idcategoria'] = $idcategoria;
					$datos['contenido'] = _safe($value['contenido']);
					$datos['urlvideo'] = _safe($value['url_video']);
				}	
			return $datos;

	}

	public function setClass(array $post){

		$this->setTitulo($post['titulo']);
		$this->setDescripcion($post['description']);
		$this->setCategoria($post['categoria']);
		$this->setContenido($post['contenido']);
		$this->setUrlVideo($post['video']);


		$result = $this->saveClass();

		if($result){
			header('location: '.HOST.'/admin/academia/new-clases');
			$_SESSION['notification']['success'] = 'Se ha registrado exitosamente la clase...';
			die();
		}else{
			header('location: '.HOST.'/admin/academia/new-clases');
			$_SESSION['notification']['info'] = 'No ha tenido exito el registro de la clase, por favor intente mas tarde!';
			die();
		}
	}


	public function delete_class(int $idclass){

		$sql = 'DELETE FROM video_blog where id =:id';

		$this->conexion->beginTransaction();

		try {
			$stm = $this->conexion->prepare($sql);
			$stm->bindParam(':id',$idclass,PDO::PARAM_INT);
			$stm->execute();
			$this->conexion->commit();

		} catch (\PDOException $e) {
			$this->conexion->rollBack();
			$thi->error_log(__METHOD__,__LINE__,$e->getMessage());
			return false;
		}

		return true;
	}

	/**
	 * Method utilizado para guardar la clase en la Bd..
	 * @return [boolean} [Retorna un booleano indicando si el registro tuvo exito o no, si no tuvo exito debido algun problema se captura la exception y la enviamos a error_log/academia_error.txt, dentro de assets paquete...]
	 */
	private function saveClass(){

		$sql = 'INSERT INTO video_blog(titulo,descripcion,contenido,url_video,id_categoria) values(:titulo,:descripcion,:contenido,:url,:categoria)';

		$datos = array(':titulo' =>$this->titulo,
						':descripcion'=>$this->descripcion,
						':contenido'=>$this->contenido,
						':url'=>$this->url,
						':categoria'=>$this->categoria);
		$this->conexion->beginTransaction();

		try {
			$stm = $this->conexion->prepare($sql);
			$stm->execute($datos);
			$this->conexion->commit();
			
		} catch (\PDOException $e) {
			$this->error_log(__METHOD__,__LINE__,$e->getMessage());
			$this->conexion->rollBack();
			return false;
		}
		return true;
	}


	public function getCategorias(){
		$sql = 'SELECT * FROM categoria_video';

		$stm = $this->conexion->prepare($sql);
		$stm->execute();

		$dato = $stm->fetchAll(PDO::FETCH_ASSOC);


		for ($i=0; $i <count($dato); $i++) { 
			$dato[$i]['id'] = '<button type="button" class="eliminar" data-id="'.$dato[$i]['id'].'"><i class="fa fa-close"></i></button>';
		}

	
		return $dato;

	}

	public function newCategoria(string $categoria){
		$sql = 'INSERT into categoria_video(categoria) values(:categoria)';
		
		$last_id = null;
		$this->conexion->beginTransaction();

		try {
			$stm =$this->conexion->prepare($sql);
			$stm->bindParam(':categoria',$categoria,PDO::FETCH_ASSOC);
			$stm->execute();
			$last_id = $this->conexion->lastInsertId();
			$this->conexion->commit();
		} catch (\PDOException $e) {
			$this->error_log(__METHOD__,__LINE__,$e->getMessage());
			$this->conexion->rollBack();
			$last_id  = 0;
		}
		
		return $last_id;

	}


	public function deleteCategorie(int $id){

		$sql = 'DELETE FROM categoria_video where id = :id';

		$this->conexion->beginTransaction();

		try {
			$stm = $this->conexion->prepare($sql);
			$stm->bindParam(':id',$id,PDO::PARAM_INT);
			$stm->execute();
			$this->conexion->commit();
			
		} catch (\PDOException $e) {
			$this->error_log(__METHOD__,__LINE__,$e->getMessage());
			$this->conexion->rollBack();
			return false;
		}

		return true;
	}


	// GETTERS Y SETTERS 
	
	protected function setTitulo(string $titulo){

		$this->titulo = $titulo;
	}

	protected function setDescripcion(string $descripcion){

		$this->descripcion =$descripcion;
	}

	protected function setCategoria(int $categoria){

		$this->categoria = $categoria;
	}

	protected function setContenido(string $contenido){
		$this->contenido = $contenido;
	}

	protected function setUrlVideo(string $Url){

		$this->url = $Url;
	}



	// Methodos para mostrar las notificaciones...

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
	// Notificacion de errores
	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'/assets/error_logs/academia_error.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}

 ?>