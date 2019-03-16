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

$pricing = new negocio\libs\preference_pricing($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$pricing->set_price_range($_POST);
}

$includes = new negocio\libs\includes($con);
$properties['title'] = 'Divisa y rango de precios del negocio | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $pricing->get_notification();?>
		<div class="background-white p20 mb30">
			<a href="<?php echo $pricing->get_url();?>" target="_blank">Ver perfil de negocio</a>
		</div><!-- /.box -->
		<form method="post" action="<?php echo _safe(HOST.'/negocio/preferencias/divisa-y-precios');?>">
			<div class="background-white p30 mb50">
				<div class="form-group page-title">
					<h4>Rango de precios y divisa del negocio</h4>
					<p>La divisa predeterminada es la divisa principal que utiliza tu negocio para cualquier registro que hagas. Tambi&eacute;n se muestra en el perfil de negocio. Es posible cambiarla en cualquier momento, o especificar una divisa distinta para cada registro en particular.</p>
					<p>Para especificar el rango de precios:</p>
					<ul>
						<li>Utiliza solo n&uacute;meros y puntos decimales.</li>
						<li>Evita cualquier s&iacute;mbolo como: <strong>$</strong> y comas ( <strong>,</strong> ).</li>
						<li>Ejemplos: 25.90, 199, 50.50, 299.99, etc.</li>
					</ul>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							<label for="currency">Divisa predeterminada</label>
							<select class="form-control" id="currency" name="iso" title="Selecciona una divisa">
							<?php echo $pricing->get_currencies();?>
							</select>
						</div><!-- /.form-group -->
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="min">Precio m&iacute;nimo</label>
							<input class="form-control" type="text" id="min" name="min" value="<?php echo $pricing->get_min_price();?>" placeholder="Precio m&iacute;nimo" />
						</div><!-- /.form-group -->
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="max">Precio m&aacute;ximo</label>
							<input class="form-control" type="text" id="max" name="max" value="<?php echo $pricing->get_max_price();?>" placeholder="Precio m&aacute;ximo" />
						</div><!-- /.form-group -->
					</div>
				</div>
				<hr>
				<div class="form-group">
					<button class="btn btn-success" type="submit">Guardar</button>
				</div>
			</div><!-- /.box -->
		</form>
	</div>
</div>
<?php echo $footer = $includes->get_footer(); ?>