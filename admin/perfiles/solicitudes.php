<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if($_SESSION['user']['id_rol'] != 1 && $_SESSION['user']['id_rol'] != 2 && $_SESSION['user']['id_rol'] != 3){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

if(!isset($_SESSION['user']['admin_authorize'])){
	header('Location: '.HOST.'/admin/acceso');
	die();
}

$solicitudes = new admin\libs\SolicitudPerfil($con);

$includes = new admin\libs\includes($con);
$properties['title'] = 'Solicitudes de negocio | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12 background-white p20 mb30">
		<?php echo $solicitudes->get_notification();?>

		<table  id="solicitudes" class="display" cellspacing="0" width="100%">
		<thead>
            <tr>
				<th>Solicitud</th>
				<th>Status</th>
				<th>Fecha</th>
				<th>Solicitante</th>
				<th>Perfil</th>
				<th></th>           
            </tr>
        </thead>

        <tbody>
   			<?php echo $solicitudes->getSolicitudes(); ?>
        </tbody>
    </table>
	</div><!-- /.col-* -->
</div><!-- /.row -->
<?php echo $footer = $includes->get_admin_footer(); ?>