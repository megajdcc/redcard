<?php 

namespace assets\libs;
use assets\libs\connection;
use PDO;

/**
 * 
 */
abstract class FuncionesAcademia 
{

	protected $categoria = '';
	private $conexion = null;

	protected function __construct(connection $con,string $categoria){
			$this->categoria = _safe($categoria);
			$this->conexion = $con->con;
	}


	protected function capturarvideos(connection $conexion, string $categoria){
		
		$this->conexion = $conexion->con;
		$this->categoria = _safe($categoria);


		$sql = "SELECT c.categoria, v.id, v.titulo,v.descripcion ,v.contenido,v.url_video from video_blog as v
		join categoria_video as c on v.id_categoria = c.id
		where c.categoria like :parametro";

		$stm = $this->conexion->prepare($sql);

		$datos = array(':parametro' => '%'.$categoria.'%');

		$stm->execute($datos);

		$html = '';
		foreach ($stm->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
			$html .= '<div class="contenedor-video col-sm-12 col-lg-12" id="class-'.$value['id'].'">
				<div class="card-simple" data-background-image="'.HOST.'/assets/img/academia/clases.jpg">
					<div class="card-simple-background">
						<div class="card-simple-content">
							<h2><a href="">'.$value['titulo'].'</a></h2>

							<div class="card-simple-actions">
								
								<button type="button" name="reproducir" class="reproducir" data-url="'.$value['url_video'].'" data-id="'.$value['id'].'" title="Reproducir"><i class="fa fa-play-circle"></i></button>
								
							</div>
						</div>
						<div class="card-simple-label">'.$value['categoria'].'</div>
						
					</div>
				</div>
			</div>';

		}

		return $html;

	}

	protected function getModal(){?>

		<div class="modal" id="modal-mostrar-clase" tabindex="-1" role="dialog">
				  <div class="modal-dialog  modal-dialog-centered modal-lg" role="document">
				    <div class="modal-content modal-video">
				    	<div class="modal-header header-video">
				    	 <button type="button" class="close" aria-label="Close">
				          <span aria-hidden="true">&times;</span>
				        </button>
				    	 </div>
						<div class="modal-body modal-video">
						      	<iframe src="" width="100%" height="400" frameborder="0" allow="autoplay" allowfullscreen allowcriptaccess="always"></iframe>
						</div>

						
				    </div>

				  </div>
		</div>
			<script type="text/javascript">
				$(document).ready(function() {

					var src = '';
					$('.reproducir').on('click',function(e){
						src = $(this).attr('data-url');

						var newurl = src.replace("?","",'gi').replace('watchv=','embed/','gi');
						$('#modal-mostrar-clase').modal('show');
						$('#modal-mostrar-clase iframe').attr('src', newurl+'?autoplay=1&amp;modestbranding=1&amp;showinfo=0');
					});

					$('.close').on('click',function(e){
						$('#modal-mostrar-clase iframe').attr('src', src);
						$('#modal-mostrar-clase').modal('hide');
					});
				});

			</script>				
			<?php }

	protected function is_videos(){

		$sql = "SELECT count(*) as cantidad from video_blog as v join categoria_video as c on v.id_categoria = c.id 
					where c.categoria like :parametro";

		$datos = array(':parametro' =>'%'.$this->categoria.'%');

		$stm = $this->conexion->prepare($sql);

		$stm->execute($datos);

		$contiene = false;
		if($stm->fetch(PDO::FETCH_ASSOC)['cantidad'] > 0 ){
			$contiene = true;
		}

		return $contiene;

	}

	abstract protected function getVideos();

}


 ?>


