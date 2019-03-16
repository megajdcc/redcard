<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!isset($_SESSION['user']) || !isset($_SESSION['business'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if($_SESSION['business']['id_rol'] != 4){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$video = new negocio\libs\preference_video($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['save'])){
		$video->set_video($_POST);
	}
	if(isset($_POST['delete'])){
		$video->unset_video($_POST);
	}
}

$includes = new negocio\libs\includes($con);
$properties['title'] = 'Video del negocio | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $video->get_notification();?>
		<div class="background-white p20 mb30">
			<a href="<?php echo $video->get_url();?>" target="_blank">Ver perfil de negocio</a>
		</div><!-- /.box -->
		<form method="post" action="<?php echo _safe(HOST.'/negocio/preferencias/video');?>">
			<div class="background-white p20 mb50">
				<h4 class="page-title">Video del negocio</h4>
				<div class="form-group">
					<p>Comparte un video de tu negocio con tus clientes. El video enlazado aparecer&aacute; en el perfil de negocio.</p>
					<p>Puedes ingresar:</p>
					<ul>
						<li>Cualquier enlace a un video de <strong>YouTube</strong></li>
						<li>Tambi&eacute;n puedes insertar el c&oacute;digo HTML (iframe) que provee <strong>YouTube</strong> o <strong>Vimeo</strong>.</li>
					</ul>
				</div>
				<div class="form-group">
					<textarea class="form-control" id="video" name="video" placeholder="Enlace o c&oacute;digo" required><?php echo $video->get_video_content();?></textarea>
					<?php echo $video->get_video_error();?>
				</div>
				<button class="btn btn-success mr20" type="submit" name="save">Guardar</button>
<?php if(!empty($video->get_video_content())){ ?>
				<button class="btn btn-danger" type="submit" name="delete">Borrar</button>
<?php } ?>
			</div><!-- /.box -->
		</form>
		<?php echo $video->get_video();?>
	</div>
</div>
<?php echo $footer = $includes->get_footer(); ?>