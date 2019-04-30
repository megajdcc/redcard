<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';
$con = new assets\libs\connection();

if(!isset($_SESSION['user']) || !isset($_SESSION['business'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if($_SESSION['business']['id_rol'] != 4 && $_SESSION['business']['id_rol'] != 5 && $_SESSION['business']['id_rol'] != 6){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$home = new negocio\libs\manage_home($con);
 
if(isset($_POST['change_business'])){
	$home->change_business($_POST['change_business']);
}
$includes = new negocio\libs\includes($con);
$properties['title'] = 'Administración de negocio | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $home->get_notification();?>
		<div class="background-white p20 mb30">
			<?php echo $home->get_status();?><a href="<?php echo $home->get_profile_url();?>" target="_blank">Ver perfil de negocio</a>
			<label class="pull-right">Afiliado desde: <?php echo $home->get_join_date();?></label>
		</div><!-- /.box -->
		<div class="row">
			<?php echo $home->get_business_sales();?>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>eSmartties bonificados</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->get_esmarties();?></strong>
						<span>Por las ventas del mes</span>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6 col-lg-3">
				<div class="statusbox">
					<h2>Saldo</h2>
					<div class="statusbox-content">
						<?php echo $home->get_balance();?>
					</div><!-- /.statusbox-content -->
				</div><!-- /.statusbox -->
			</div>
			<div class="col-sm-6 col-lg-3">
				<div class="statusbox">
					<h2>Nivel de satisfacci&oacute;n</h2>
					<div class="statusbox-content">
						<?php echo $home->get_rating();?>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-6 col-lg-3">
				<div class="statusbox">
					<h2>Visitas</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->get_views();?></strong>
						<span>Total de visitas</span>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-6 col-lg-3">
				<div class="statusbox">
					<h2>Comisi&oacute;n</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->get_commission();?></strong>
						<span>Comisi&oacute;n actual</span>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
		</div><!-- /.row -->
		<div class="row">
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Seguidores</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->get_follows();?> <i class="fa fa-bookmark" style="color: #67bbe5;"></i></strong>
						<span>Siguen este negocio</span>
					</div><!-- /.statusbox-content -->
				</div><!-- /.statusbox -->
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Recomendaciones</h2>
					<div class="statusbox-content">
						<strong><?php echo $home->get_recommends();?> <i class="fa fa-heart" style="color: #d81814;"></i></strong>
						<span>Recomiendan este negocio</span>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
			<div class="col-sm-3">
				<div class="statusbox">
					<h2>Certificados redimidos</h2>
					<div class="statusbox-content">
						<?php echo $home->get_certificates();?>
					</div><!-- /.statusbox-content -->
				</div>
			</div>
		</div>
	</div>
</div>
<?php echo $footer = $includes->get_footer(); ?>