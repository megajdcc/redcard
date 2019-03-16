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

$event = new negocio\libs\content_new_event($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['new_event'])){
		$event->create_event($_POST, $_FILES);
	}
}

$includes = new negocio\libs\includes($con);
$properties['title'] = 'Crear nuevo evento | eSmartClub';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $event->get_notification();?>
		<div class="background-white p20 mb30">
			<a href="<?php echo $event->get_profile_url();?>" target="_blank">Ver perfil de negocio</a>
		</div><!-- /.box -->
		<form method="post" action="<?php echo _safe(HOST.'/negocio/contenidos/nuevo-evento');?>" enctype="multipart/form-data">
			<div class="background-white p30 mb30">
				<div class="page-title">
					<h4>Nuevo evento</h4>
				</div>
				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label for="start">Foto <span class="required">*</span></label>
							<input type="file" id="event-image" name="image" required/>
							<?php echo $event->get_image_error();?>
						</div><!-- /.form-group -->
					</div>
					<div class="col-sm-6">
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label for="start">Fecha y hora de inicio <span class="required">*</span></label>
									<div class="input-group date" id="event-start">
										<input class="form-control" type="text" id="start" name="date_start" value="<?php echo $event->get_date_start();?>" placeholder="Fecha y hora de inicio" required/>
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									</div>
									<?php echo $event->get_date_start_error();?>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label for="end">Fecha y hora de fin <span class="required">*</span></label>
									<div class="input-group date" id="event-end">
										<input class="form-control" type="text" id="end" name="date_end" value="<?php echo $event->get_date_end();?>" placeholder="Fecha y hora de fin" required/>
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									</div>
									<?php echo $event->get_date_end_error();?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label for="title">T&iacute;tiulo <span class="required">*</span></label>
					<input class="form-control" type="text" id="title" name="title" value="<?php echo $event->get_title();?>" placeholder="T&iacute;tiulo" required>
					<?php echo $event->get_title_error();?>
				</div><!-- /.form-group -->
				<div class="form-group">
					<label for="content">Contenido <span class="required">*</span></label>
					<textarea class="form-control" id="content" name="content" rows="5" placeholder="Contenido" required><?php echo $event->get_content();?></textarea>
					<?php echo $event->get_content_error();?>
				</div><!-- /.form-group -->
				<hr>
				<div class="form-group">
					<button class="btn btn-success" type="submit" name="new_event">Crear evento</button>
				</div>
			</div>
		</form>
	</div>
</div>
<?php echo $footer = $includes->get_footer(); ?>