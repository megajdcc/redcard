<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

$listing = new assets\libs\listing_results($con);

$filter['keyword'] = filter_input(INPUT_GET, 'buscar');
$filter['type'] = filter_input(INPUT_GET, 'tipo', FILTER_VALIDATE_INT);
$filter['category'] = filter_input(INPUT_GET, 'categoria', FILTER_VALIDATE_INT);
$filter['location'] = filter_input(INPUT_GET, 'ciudad', FILTER_VALIDATE_INT);

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));
$rpp = 12;
$options = $listing->load_data($page, $rpp, $filter);

$paging = new assets\libraries\pagination\pagination($options['page'], $options['total']);
$paging->setRPP($options['rpp']);
$paging->setCrumbs(10);

$includes = new assets\libs\includes($con);
$properties['title'] = 'Listados | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
<div class="main">
	<div class="main-inner">
		<div class="content">
			<div class="mt-80">
				<div class="document-title">
					<h1 class="text-binary">Listados</h1>
				</div><!-- /.document-title -->
			</div>
			<div class="container">
				<?php echo $con->get_notify();?>
				<form class="filter" method="get" action="<?php echo HOST.'/listados';?>">
					<div class="row">
						<div class="col-sm-12 col-md-6">
							<div class="form-group" data-toggle="tooltip" title="Buscar por palabra o frase.">
								<input class="form-control" type="text" name="buscar" value="<?php echo $listing->get_keyword();?>" placeholder="Encuentra por palabra o frase">
							</div><!-- /.form-group -->
						</div><!-- /.col-* -->
						<div class="col-sm-12 col-md-3">
							<div class="form-group">
								<select class="form-control" title="Selecciona el tipo" name="tipo">
									<?php echo $listing->get_types();?>
								</select>
							</div><!-- /.form-group -->
						</div><!-- /.col-* -->
						<div class="col-sm-12 col-md-3">
							<div class="form-group">
								<select class="form-control" title="Selecciona una categor&iacute;a" name="categoria" data-size="10">
									<?php echo $listing->get_categories();?>
								</select>
							</div><!-- /.form-group -->
						</div><!-- /.col-* -->
					</div><!-- /.row -->
					<hr>
					<p>Choose a city | Elige una ciudad</p>
					<div class="row">
						<div class="col-sm-12 col-md-4">
							<div class="form-group">
								<select class="form-control" id="city-select" name="ciudad" title="Luego una ciudad" data-size="10" data-live-search="true">
									<option value="28120">Puerto Vallarta</option>
									<option value="48320">Quintana Roo</option>
									<option value="48321">Los Cabos</option>
								</select>
							</div><!-- /.form-group -->
						</div><!-- /.col-* -->
					</div><!-- /.row -->
					<hr>
					<div class="row">
						<div class="col-sm-8">
							<div class="filter-actions">
								<a href="<?php echo HOST.'/listados';?>"><i class="fa fa-close"></i> Limpiar Filtros</a>
							</div><!-- /.filter-actions -->
						</div><!-- /.col-* -->
						<div class="col-sm-4">
							<button type="submit" class="btn btn-primary">¡Encuentra!</button>
						</div><!-- /.col-* -->
					</div><!-- /.row -->
				</form>
				<h2 class="page-title clearfix">
					<label><?php echo $options['count']; ?></label>
				</h2><!-- /.page-title -->
				<div class="cards-simple-wrapper">
					<div class="row">
						<?php echo $listing->get_results(); ?>
					</div><!-- /.row -->
				</div><!-- /.cards-simple-wrapper -->
				<?php echo $paging->parse(); ?>
			</div><!-- /.content -->
		</div><!-- /.container -->
	</div><!-- /.main-inner -->
</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>