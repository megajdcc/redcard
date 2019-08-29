<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if($_SESSION['user']['id_rol'] != 1 && $_SESSION['user']['id_rol'] != 2){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if(!isset($_SESSION['user']['admin_authorize'])){
	header('Location: '.HOST.'/admin/acceso');
	die();
}

$balance = new admin\libs\recharge_business($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$balance->new_balance($_POST);
}

$includes = new admin\libs\includes($con);
$properties['title'] = 'Recargar saldo | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $balance->get_notification();?>
		<form method="post" action="<?php echo _safe(HOST.'/admin/negocios/recargar');?>">
			<div class="background-white p30 mb50">
				<div class="page-title">
					<h4>Recargar saldo a un negocio</h4>
				</div>
				<div class="row">
					<div class="col-md-8">
						<div class="form-group" id="business-search" data-toggle="tooltip" title="Ingrese el nombre del negocio o la url del negocio afiliado a Travel Points al que desea recargarle saldo. Verifique su coincidencia cuidadosamente.">
							<label for="business-search-input">Negocio afiliado a Travel Points <i class="fa fa-question-circle text-secondary"></i></label>
							<div class="search-placeholder" id="business-search-placeholder">
								<img src="<?php echo HOST;?>/assets/img/user_profile/default.jpg" class="meta-img img-rounded">
							</div>
							<input class="form-control typeahead" type="text" id="business-search-input" name="url" value="<?php echo $balance->get_url();?>" placeholder="Negocio de Travel Points" autocomplete="off" required/>
							<?php echo $balance->get_url_error();?>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group" data-toggle="tooltip" title="Escriba la cantidad sin signos o comas. Utilice punto decimal si es necesario.">
							<label for="add-balance">Cantidad a recargar <i class="fa fa-question-circle text-secondary"></i></label>
							<input class="form-control" type="text" id="add-balance" name="balance" value="<?php echo $balance->get_balance();?>" placeholder="Cantidad a recargar" required/>
							<?php echo $balance->get_balance_error();?>
						</div>
						<div class="form-group">
							<label>Saldo despu&eacute;s del movimiento</label>
							<input type="hidden" id="current-balance">
							<input class="form-control" type="text" id="new-balance" readonly>
						</div>
					</div>
				</div>
				<hr>
				<button class="btn btn-success" type="submit">Recargar saldo</button>
			</div>
		</form>
	</div>
</div>
<?php echo $footer = $includes->get_admin_footer(); ?>