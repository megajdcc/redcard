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

$schedule = new negocio\libs\preference_schedule($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$schedule->set_schedule($_POST);
}

$includes = new negocio\libs\includes($con);
$properties['title'] = 'Horario de trabajo del negocio | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $schedule->get_notification();?>
		<div class="background-white p20 mb30">
			<a href="<?php echo $schedule->get_url();?>" target="_blank">Ver perfil de negocio</a>
		</div><!-- /.box -->
		<form method="post" action="<?php echo _safe(HOST.'/negocio/preferencias/horario');?>">
			<div class="background-white p20 mb50">
				<div class="form-group page-title">
					<h4>Horario de trabajo</h4>
					<p>El horario de trabajo indica a tus clientes en qu&eacute; d&iacute;as de la semana abres, as&iacute; como el horario de cada d&iacute;a en espec&iacute;fico.</p>
					<ul>
						<li>Al especificar el horario de un d&iacute;a, es necesario ingresar la hora de entrada y la hora de salida (ambas).</li>
						<li>Para indicar un d&iacute;a cerrado basta con dejar en blanco ambos espacios.</li>
						<li>Ejemplos: Entrada: 8:30 AM, Salida: 10:00 PM.</li>
					</ul>
					<p>Puedes apoyarte en la herramienta para seleccionar la hora.</p>
				</div>
				<div class="row hidden-xs">
					<div class="col-sm-6 col-md-4 col-md-offset-3">
						<div class="form-group center">
							Hora de Entrada
						</div>
					</div>
					<div class="col-sm-6 col-md-4">
						<div class="form-group center">
							Hora de Salida
						</div>
					</div>
				</div>
				<?php echo $schedule->get_schedule();?>
				<hr>
				<div class="form-group">
					<button class="btn btn-success" type="submit">Guardar</button>
				</div>
			</div><!-- /.box -->
		</form>
	</div>
</div>
<?php echo $footer = $includes->get_footer(); ?>