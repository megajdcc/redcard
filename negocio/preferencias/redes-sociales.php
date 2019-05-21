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

$networks = new negocio\libs\preference_networks($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$networks->set_networks($_POST);
}

$includes = new negocio\libs\includes($con);
$properties['title'] = 'Redes sociales del negocio | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $networks->get_notification();?>
		<div class="background-white p20 mb30">
			<a href="<?php echo $networks->get_url();?>" target="_blank">Ver perfil de negocio</a>
		</div><!-- /.box -->
		<form method="post" action="<?php echo _safe(HOST.'/negocio/preferencias/redes-sociales');?>">
			<div class="background-white p20 mb50">
				<div class="form-group page-title">
					<h4>Redes sociales</h4>
					<p>
						Puedes compartir las redes sociales de tu negocio en el perfil para que tus clientes te sigan. Inserta el enlace correspondiente para cada una de tus redes sociales.<br>Ejemplos de enlaces correctos:
						<ul>
							<li>facebook.com/minegocio</li>
							<li>www.facebook.com/minegocio</li>
							<li>http://facebook.com/minegocio</li>
							<li>http://www.facebook.com/minegocio</li>
							<li>https://www.facebook.com/minegocio</li>
						</ul>
					</p>
				</div>
				<div class="form-group">
					<?php echo $networks->get_networks();?>
				</div><!-- /.form-group -->
				<hr>
				<div class="form-group">
					<button class="btn btn-success" type="submit">Guardar</button>
				</div>
			</div><!-- /.box -->
		</form>
	</div>
</div>
<?php echo $footer = $includes->get_footer(); ?>