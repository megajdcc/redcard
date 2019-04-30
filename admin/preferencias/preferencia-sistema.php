<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if($_SESSION['user']['id_rol'] != 1 && $_SESSION['user']['id_rol'] != 2 && $_SESSION['user']['id_rol'] != 3 && $_SESSION['user']['id_rol'] != 9){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if(!isset($_SESSION['user']['admin_authorize'])){
	header('Location: '.HOST.'/admin/acceso');
	die();
}

$preferencia = new admin\libs\PreferenciaSistema($con);
if(isset($_REQUEST['grabar'])){
	$preferencia->registrar($_POST);
}

$includes = new admin\libs\includes($con);
$properties['title'] = 'Preferencias del Sistema';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<form method="post" action="<?php echo _safe(HOST.'/admin/preferencias/preferencia-sistema');?>">
			<div class="background-white p20 mb50">
				<h2 class="page-title">Preferencias del Sistema</h2>
			

			<div class=" row">
				
				<section class="col-sm-12">
					<style>
						.t-preferencias{
							width: 100% !important;
							border: 0px;
						}
						.t-preferencias th{
							text-align: center;
							font-family: 'Nexa Bold Regular';
						}
						.t-preferencias td{
							width: 50%;
						}

					</style>
					<table class="t-preferencias" border="1" width="100"> 
							<thead>
								<th>Preferencias</th>
								<th>Elección</th>
							</thead>				
							<tbody>
							<tr>
								<td><label for="email" class="form-control">Email de Notificacón de retiro</label>
								</td>
								<td data-toggle="tooltip" title="Por defecto el correo para esta acción esta notificacion@smart.com, pero si gusta puede cambiarlo en cualquier momento"><input type="email" id="email" name="emailretiro" class="form-control" value="<?php echo $preferencia->getEmailRetiro(); ?>" placeholder="Ingrese Email de notificación de retiro de comisiones" required/></td>
							</tr>
							</tbody>
					</table>					
				</section>			
			</div>
			<div class="row">
				<section class=" d-flex justify-content-center col-sm-12">
					<div class="btn-group">
						<button type="submit" class="btn btn-secondary" name="grabar"><i class="fa fa-save"></i> Grabar</button>
					</div>
				</section>
				
			</div>
			</div>
		</form>
	</div>
</div><!-- /.background-white p20 mb30 -->
<?php echo $footer = $includes->get_admin_footer(); ?>