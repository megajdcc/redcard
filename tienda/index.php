<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Info Channel
$con = new assets\libs\connection();

$catalog = new assets\libs\product_catalog($con);

if (isset($_SESSION['perfil'])) {
	header('Location: ../index.php');
	die();
	
}

$filter['category'] = filter_input(INPUT_GET, 'categoria', FILTER_VALIDATE_INT);
$filter['sorting'] = filter_input(INPUT_GET, 'ordenar');
$filter['show'] = filter_input(INPUT_GET, 'mostrar', FILTER_VALIDATE_INT, array('options' => array('default' => 12, 'min_range' => 12)));

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));
$search = filter_input(INPUT_GET, 'buscar');
$options = $catalog->load_data($search, $page, $filter);

$paging = new assets\libraries\pagination\pagination($options['page'], $options['total']);
$paging->setRPP($options['rpp']);
$paging->setCrumbs(10);

$includes = new assets\libs\includes($con);
$properties['title'] = 'Gift Shop | Tienda de Regalos';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
<div class="main">
	<div class="main-inner">
		<div class="content">
			<div class="mt-80">
				<div class="document-title">
					<h1 class="text-binary">Gift Shop | Tienda de Regalos</h1>
				</div><!-- /.document-title -->
			</div>
			<div class="container">
				<?php echo $con->get_notify(); echo $catalog->get_notification(); ?>
				<form method="get" action="<?php echo _safe(HOST.'/tienda/');?>">
					<div class="background-white p20 mb30">
						<div class="row">

							<div class="col-md-5">
								<div class="form-group" data-toggle="tooltip" title="Type Keywords | Buscar por palabra o frase.">
									<div class="input-group">
										<input class="form-control" type="text" name="buscar" value="<?php echo _safe($search);?>" placeholder="Find gifts | Buscar regalos&hellip;">
										<span class="input-group-btn">
											<button class="btn btn-primary" type="submit"><i class="fa fa-search"></i></button>
										</span>
									</div>
								</div>
							</div>
							
							<div class="col-md-7">
								<div class="right">
									<div class="form-group display-inline-block mr20">
										<select class="form-control" title="Category | Categor&iacute;a" name="categoria" onChange="this.form.submit();">
											<?php echo $catalog->get_categories();?>
										</select>
									</div><!-- /.form-group -->
									<div class="form-group display-inline-block mr20">
										<select class="form-control" title="Select Order | Ordenar por" name="ordenar" onChange="this.form.submit();">
											<?php echo $catalog->get_sorting();?>
										</select>
									</div><!-- /.form-group -->
									<div class="form-group display-inline-block mr20">
										<select class="form-control" title="Productos por p&aacute;gina" name="mostrar" onChange="this.form.submit();">
											<?php echo $catalog->get_show();?>
										</select>
									</div><!-- /.form-group -->
									<div class="filter-actions display-inline-block">
										<a href="<?php echo HOST.'/tienda/';?>"><i class="fa fa-close"></i> Clean Filters | Limpiar Filtros</a>
									</div><!-- /.filter-actions -->
								</div>
							</div>
						</div>
					</div>
				</form>
				<div class="cards-simple-wrapper">
					<div class="row">
					<?php echo $catalog->get_products();?>
					</div>
					<div class="mb30">
					<?php echo $paging->parse();?>
					</div>
				</div>
			</div><!-- /.content -->
		</div><!-- /.container -->
	</div><!-- /.main-inner -->
</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>