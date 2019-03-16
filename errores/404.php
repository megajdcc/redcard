<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();
$includes = new assets\libs\includes($con);
$properties['title'] = 'Página no encontrada | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
	<div class="main">
		<div class="main-inner">
			<div class="container">
				<div class="content">
					<?php echo $con->get_notify();?>
					<div class="caution">
						<h1>404</h1>
						<h2>P&aacute;gina no encontrada</h2>
						<p>El enlace que seguiste puede estar roto, o la p&aacute;gina pudo haber sido removida.</p>
						<a href="<?php echo HOST.'/';?>">Ir al inicio</a>
					</div><!-- /.page-header -->
				</div><!-- /.content -->
			</div><!-- /.container -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>