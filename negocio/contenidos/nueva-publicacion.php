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

$post = new negocio\libs\content_new_post($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['new_post'])){
		$post->set_post($_POST, $_FILES);
	}
}

$includes = new negocio\libs\includes($con);
$properties['title'] = 'Publicar contenido | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $post->get_notification();?>
		<div class="background-white p20 mb30">
			<a href="<?php echo $post->get_profile_url();?>" target="_blank">Ver perfil de negocio</a>
		</div><!-- /.box -->
		<form method="post" action="<?php echo _safe(HOST.'/negocio/contenidos/nueva-publicacion');?>" enctype="multipart/form-data">
			<div class="background-white p30 mb30">
				<div class="page-title">
					<h4>Nueva publicaci&oacute;n</h4>
				</div>
				<div class="form-group" data-toggle="tooltip" title="Opcional">
					<label for="start">Foto <i class="fa fa-question-circle text-secondary"></i></label>
					<input type="file" id="post-image" name="image" />
					<?php echo $post->get_image_error();?>
				</div><!-- /.form-group -->
				<div class="form-group">
					<label for="title">T&iacute;tiulo <span class="required">*</span></label>
					<input class="form-control" type="text" id="title" name="title" value="<?php echo $post->get_title();?>" placeholder="T&iacute;tiulo"/ required>
					<?php echo $post->get_title_error();?>
				</div><!-- /.form-group -->
				<div class="form-group">
					<label for="content">Contenido <span class="required">*</span></label>
					<textarea class="form-control" id="content" name="content" rows="5" placeholder="Contenido" required><?php echo $post->get_content();?></textarea>
					<?php echo $post->get_content_error();?>
				</div><!-- /.form-group -->
				<hr>
				<div class="form-group">
					<button class="btn btn-success" type="submit" name="new_post">Publicar</button>
				</div>
			</div>
		</form>
	</div>
</div>
<?php echo $footer = $includes->get_footer(); ?>