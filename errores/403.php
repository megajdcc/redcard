<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 
$con = new assets\libs\connection();
$includes = new assets\libs\includes($con);
$properties['title'] = 'Acceso Prohibido | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
	<div class="main">
		<div class="main-inner">
			<div class="container">
				<div class="content">
					<?php echo $con->get_notify();?>
					<div class="caution">
						<h1>403</h1>
						<h2>Acceso Prohibido</h2>
						<p>El acceso al enlace que seguiste est&aacute; prohibido.</p>
						<a href="<?php echo HOST.'/';?>">Ir al inicio</a>
					</div><!-- /.page-header -->
				</div><!-- /.content -->
			</div><!-- /.container -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>