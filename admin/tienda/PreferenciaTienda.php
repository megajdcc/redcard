<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

if($_SESSION['user']['id_rol'] != 1 && $_SESSION['user']['id_rol'] != 2 && $_SESSION['user']['id_rol'] != 9){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

if(!isset($_SESSION['user']['admin_authorize'])){
	header('Location: '.HOST.'/admin/acceso');
	die();
}

use admin\libs\PreferenciaTienda;

$preferencia = new PreferenciaTienda($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	
}

$includes = new admin\libs\includes($con);
$properties['title'] = 'Preferencia | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>

<?php echo $con->get_notify();?>

<div class="row">
	<div class="col-sm-12">
		<?php echo $preferencia->getNotificacion();?>
		<div class="background-white p20 mb30">
			<a href="<?php echo HOST.'/tienda/'; ?>" target="_blank">Ver tienda</a>
		</div><!-- /.box -->
		<form method="post" action="<?php echo _safe(HOST.'/admin/tienda/PreferenciaTienda');?>" enctype="multipart/form-data">
			<div class="background-white p30 mb30">

				<div class="page-title">
					<h4>Preferencias de la tienda</h4>
				</div>
				<div class="btn-group" role="group" aria-label="Basic example" data-toggle="collapse" href="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
				<button type="button" class="btn btn-secondary">Mensaje para ventas Satisfactorias</button>
			<!-- 	<button type="button" class="btn btn-secondary">Middle</button>
				<button type="button" class="btn btn-secondary">Right</button> -->
			

				</div>



			<div class="collapse" id="collapseExample">
			 <div><p>hola</p></div>
			</div>


			</div>
		</form>
	</div>
</div>
<?php echo $footer = $includes->get_admin_footer(); ?>