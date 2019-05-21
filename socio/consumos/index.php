<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	header('Location: '.HOST.'/login');
	die();
}
if(!isset($_SESSION['user']['id_usuario'])){
	header('Location: '.HOST.'/login');
	die();
}

$expenses = new socio\libs\user_expenses($con);

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));
$rpp = 10;
$options = $expenses->load_data($page, $rpp);

$paging = new assets\libraries\pagination\pagination($options['page'], $options['total']);
$paging->setRPP($rpp);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$expenses->submit_review($_POST);
}

$includes = new assets\libs\includes($con);
$properties['title'] = 'Mis consumos | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
	<div class="main">
		<div class="main-inner">
			<div class="container">
				<?php echo $con->get_notify();?>
				<div class="row">
					<div class="col-sm-4 col-lg-3">
						<div class="sidebar">
							<?php echo $includes->get_user_sidebar();?>
						</div><!-- /.sidebar -->
					</div><!-- /.col-* -->
					<div class="col-sm-8 col-lg-9">
						<div class="content">
							<?php echo $expenses->get_notification();?>
							<div class="page-title"><?php echo $expenses->get_count();?></div>
							<?php echo $expenses->get_expenses(); echo $paging->parse();?>
						</div><!-- /.content -->
					</div><!-- /.col-* -->
				</div><!-- /.row -->
			</div><!-- /.container -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>