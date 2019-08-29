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

$gallery = new negocio\libs\preference_images($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['update_logo'])){
		$gallery->set_logo($_FILES);
	}
	if(isset($_POST['update_header'])){
		$gallery->set_header($_FILES);
	}
}

$includes = new negocio\libs\includes($con);
$properties['title'] = 'Logo y portada del negocio | Travel Points';
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
		<div class="row">
			<div class="col-md-6">
				<div class="background-white p20 mb50">
					<div class="page-title">
						<h4>Logo del negocio</h4>
					</div>
					<p class="mb30">Selecciona el logo que ser&aacute; mostrado en el perfil de negocio.</p>
					<div class="form-group mb30">
						<div class="detail-gallery-preview center">
							<a href="<?php echo $gallery->get_logo_url();?>">
								<img class="img-thumbnail img-rounded gallery-img" src="<?php echo $gallery->get_logo_url();?>">
							</a>
						</div>
					</div>
					<form method="post" action="<?php echo _safe(HOST.'/negocio/preferencias/logo-y-portada');?>" enctype="multipart/form-data">
						<div class="form-group" data-toggle="tooltip" title="Admite solo imagenes en formato JPG Y PNG. Se recomienda una imagen cuadrada de m&iacute;nimo 300x300 pixeles y un peso inferior a 2MB.">
							<input type="file" id="business-logo" name="logo" required/>
							<?php echo $gallery->get_logo_error();?>
						</div><!-- /.form-group -->
						<hr>
						<div class="form-group">
							<button class="btn btn-success" type="submit" name="update_logo">Actualizar logo</button>
						</div>
					</form>
				</div>
			</div>
			<div class="col-md-6">
				<div class="background-white p20 mb50">
					<div class="page-title">
						<h4>Foto de portada</h4>
					</div>
					<p class="mb30">Selecciona la foto de portada que ser&aacute; mostrada en el perfil de negocio.</p>
					<div class="form-group mb30">
						<div class="detail-gallery-preview center">
							<a href="<?php echo $gallery->get_header_url();?>">
								<img class="img-thumbnail img-rounded gallery-img" src="<?php echo $gallery->get_header_url();?>">
							</a>
						</div>
					</div>
					<form method="post" action="<?php echo _safe(HOST.'/negocio/preferencias/logo-y-portada');?>" enctype="multipart/form-data">
						<div class="form-group" data-toggle="tooltip" title="Admite solo imagenes en formato JPG Y PNG. Se recomienda una imagen horizontal panor&aacute;mica y un peso inferior a 2 MB.">
							<input type="file" id="business-header" name="header" required/>
						</div><!-- /.form-group -->
						<hr>
						<div class="form-group">
							<button class="btn btn-success" type="submit" name="update_header">Actualizar portada</button>
						</div>
					</form>
				</div><!-- /.box -->
			</div>
		</div>
	</div>
</div>
<?php echo $footer = $includes->get_footer(); ?>