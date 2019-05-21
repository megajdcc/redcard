<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!isset($_SESSION['user']) || !isset($_SESSION['business'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if($_SESSION['business']['id_rol'] != 4 && $_SESSION['business']['id_rol'] != 5){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$gallery = new negocio\libs\content_gallery($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['new_image'])){
		$gallery->set_image($_POST, $_FILES);
	}
	if(isset($_POST['default'])){
		$gallery->default_image($_POST);
	}
	if(isset($_POST['hide'])){
		$gallery->hide_image($_POST);
	}
	if(isset($_POST['show'])){
		$gallery->show_image($_POST);
	}
	if(isset($_POST['delete'])){
		$gallery->delete_image($_POST);
	}
}

$includes = new negocio\libs\includes($con);
$properties['title'] = 'Galería del negocio | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $gallery->get_notification();?>
		<div class="background-white p20 mb30">
			<a href="<?php echo $gallery->get_profile_url();?>" target="_blank">Ver perfil de negocio</a>
		</div><!-- /.box -->
		<form method="post" action="<?php echo _safe(HOST.'/negocio/contenidos/galeria');?>" enctype="multipart/form-data">
			<div class="background-white p30 mb30">
				<div class="page-title">
					<h4>Nueva imagen</h4>
				</div>
				<p class="mb30">Sube una nueva imagen para la galer&iacute;a del perfil de negocio.</p>
				<div class="form-group">
					<input type="file" id="gallery-image" name="image" required/>
					<?php echo $gallery->get_image_error();?>
				</div><!-- /.form-group -->
				<div class="form-group">
					<label for="title">T&iacute;tulo de la imagen</label>
					<input class="form-control" type="text" id="title" name="title" value="<?php echo $gallery->get_title();?>" placeholder="T&iacute;tiulo de la imagen" required/>
					<?php echo $gallery->get_title_error();?>
				</div><!-- /.form-group -->
				<hr>
				<div class="form-group">
					<button class="btn btn-success" type="submit" name="new_image">Subir imagen</button>
				</div>
			</div>
		</form>
		<div class="background-white p30 mb50" id="galeria">
			<div class="page-title">
				<h4>Galer&iacute;a de im&aacute;genes</h4>
			</div>
			<div class="row">
				<?php echo $gallery->get_gallery();?>
			</div>
		</div><!-- /.box -->
	</div>
</div>
<?php echo $footer = $includes->get_footer(); ?>