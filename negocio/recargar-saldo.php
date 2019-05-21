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

$balance = new negocio\libs\business_balance($con);

$includes = new negocio\libs\includes($con);
$properties['title'] = 'Recargar saldo | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-6 col-sm-offset-3">
		<?php echo $balance->get_notification();?>
		<h1 class="page-title">Recargar saldo</h1>
		<div class="mb30">LISTA DE INSTRUCCIONES</div>
		<div class="background-white p30 mb30">
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="99UU42D2V5FNJ">
				<table>
				<tr><td><input type="hidden" name="on0" value="Seleccione Saldo">Seleccione Saldo</td></tr><tr><td><select name="os0">
				    <option value="Cien">Cien $100.00 MXN</option>
				    <option value="Quinientos">Quinientos $500.00 MXN</option>
				    <option value="Mil">Mil $1,000.00 MXN</option>
				</select> </td></tr>
				</table>
				<input type="hidden" name="currency_code" value="MXN">
				<input type="image" src="https://www.paypalobjects.com/es_XC/MX/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal, la forma más segura y rápida de pagar en línea.">
				<img alt="" border="0" src="https://www.paypalobjects.com/es_XC/i/scr/pixel.gif" width="1" height="1">
			</form>
		</div>
	</div>
</div>
<?php echo $footer = $includes->get_footer(); ?>