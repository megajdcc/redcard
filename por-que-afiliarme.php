<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

$includes = new assets\libs\includes($con);
$properties['title'] = '¿Por qué afiliarme? | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
<div class="main">
	<div class="main-inner">
		<div class="content">
			<div class="mt-80">
				<div class="document-title">
					<h1 class="text-binary">¿Por qu&eacute; afiliarme?</h1>
				</div><!-- /.document-title -->
			</div>
			<div class="container">
				<?php echo $con->get_notify();?>
				<img class="img-responsive mb30" src="<?php echo HOST;?>/assets/img/esmartclub/afilia-tu-negocio-esmart-club.jpg" alt="Afilia tu negocio en eSmart Club">
				<img class="img-responsive mb30" src="<?php echo HOST;?>/assets/img/esmartclub/ofrece-esmartties-esmart-club.jpg" alt="Ofrece eSmartties a tus clientes en eSmart Club">
				<img class="img-responsive mb30" src="<?php echo HOST;?>/assets/img/esmartclub/recibe-nuevos-clientes-esmart-club.jpg" alt="Recibe nuevos clientes en eSmart Club">
				<img class="img-responsive mb30" src="<?php echo HOST;?>/assets/img/esmartclub/programa-lealtad-esmart-club.jpg" alt="Programa de lealtad de eSmart Club">
				<div class="center"><a href="<?php echo HOST;?>/afiliar-negocio" class="btn btn-xl btn-primary">Afilia tu negocio aqu&iacute;</a></div>
			</div><!-- /.content -->
		</div><!-- /.container -->
	</div><!-- /.main-inner -->
</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>