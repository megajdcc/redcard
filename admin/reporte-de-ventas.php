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

$businesses = new admin\libs\business_dashboard($con);

 
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));
$rpp = 20;
$options = $businesses->load_data($page, $rpp);

$paging = new assets\libraries\pagination\pagination($options['page'], $options['total']);
$paging->setRPP($rpp);
$paging->setCrumbs(10);

 
$includes = new admin\libs\includes($con);
$reports = new admin\libs\reports_sales($con);
$info = new negocio\libs\preference_info($con);
$users = new admin\libs\get_allusers($con);


if(isset($_REQUEST['pdf'])){
		$reports->mostrarpdf($_POST);
		die();
}
if(isset($_REQUEST['buscar'])){
		$reports->Buscar($_POST);
		
}

$properties['title'] = 'Negocios | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $reports->get_notification();?>
		<div class="background-white p20 mb30">
			<style>
				.mb30{
					margin-bottom: 0px !important;
				}
				.btnera{
					display: flex;
					justify-content: center;
					align-items: center;
				}
			</style>
			
			<form class="pull-right" method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>">
				<div class="row">
					<div class="col-sm-3">
						<div class="form-group">
							<label for="start">Fecha y hora de inicio</label>
							<div class="input-group date" id="event-start">
								<input class="form-control" type="text" id="start" name="date_start" value="<?php echo $reports->getFecha1();?>" placeholder="Fecha y hora de inicio"  required/>
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							</div>
							<?php echo $reports->get_date_start_error();?>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="form-group">
							<label for="end">Fecha y hora de fin</label>
							<div class="input-group date" id="event-end">
								<input class="form-control" type="text" id="end" name="date_end" value="<?php echo $reports->getfecha2();?>" placeholder="Fecha y hora de fin"  required/>
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							</div>
							<?php echo $reports->get_date_end_error();?>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="form-group">
							<label for="category">Usuario</label>
								<select  data-live-search="true" class="form-control" id="user" name="usuario" title="Seleccionar usuario" required>
									<?php echo $reports->getUsuario(); ?>
									
								</select>
								
						</div>
					</div>
					<div class="col-sm-3">
						<div class="form-group">
							<label for="category">Negocios </label>
								<select class="form-control" data-live-search="true" id="category" name="negocio" title="Seleccionar categor&iacute;a" required>
									<?php echo $info->getNegocios();?>
								</select>
								<?php echo $info->get_category_error();?>
						</div>
					</div>
				</div>


				<div class="row btnera">
					
			
					<div class="col-sm-8">
						<label>Buscar</label>
						<div class="form-group">
							<button class="btn btn-success" type="submit" name="buscar"><i class="fa fa-search"></i></button>
							<a href="<?php echo _safe(HOST.'/admin/reporte-de-ventas/');?>" class="btn btn-info">Limpiar</a>
						</div>
					</div>
				</div>
				</form>	
				
					
				
					<form class="pull-right" method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>" target="_blank">
						<div class="col-sm-4">
							<input type="hidden" name="date_start" value="<?php echo $reports->getFecha1();?>" >
							<input type="hidden" name="date_end" value="<?php echo $reports->getFecha2();?>" >

							<input type="hidden" name="usuario" value="<?php echo $reports->usuario;?>" >
							<input type="hidden" name="negocio" value="<?php echo $reports->negocio;?>" >
							<button class="btn btn-default text-danger" type="submit" name="pdf"><i class="fa fa-file-pdf-o"></i>PDF</button>
						</div>
					</form>
					
				


		
		</div>


		<div class="background-white p20 mb50">
			<div class="page-title">
						<h1>Reporte de Ventas
						
						
						
						</h1>
					</div>

			<table  id="estadodecuenta" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
						
						<th>Fecha</th>
						<th>Negocio</th>
						<th>Usuario</th>
						<th>Venta</th>
						<th>Comisión</th>
						<th>Balance</th>						
						</tr>
					</thead>
					
					<tbody>			
						<?php echo $reports->getEstadoCuenta();?>
					</tbody>
				</table>

				<script>

				 var t = $('#estadodecuenta').DataTable( {
					"paging"        :         false,
					"scrollY"       :        "400px",
					"scrollCollapse": true,
			         "language": {
			                        "lengthMenu": "Mostar _MENU_ registros por pagina",
			                        "info": "",
			                        "infoEmpty": "No se encontro ningún estado por este filtro intente de nuevo con otro...",
			                        "infoFiltered": "(filtrada de _MAX_ registros)",
			                        "search": "Buscar: ",
			                        "paginate": {
			                            "next":       "Siguiente",
			                            "previous":   "Anterior"
			                        },
			                    },
			        "columnDefs": [ {
			            "searchable": true,
			            "orderable": true,
			            "targets": 0
			        } ],
			       
			    } );
    
</script>

		
		</div>
	</div>
</div>
<?php echo $footer = $includes->get_admin_footer(); ?>
<script type="text/javascript">
	$('#user').val("<?php echo $reports->get_user_id();?>");
	$('#category').val("<?php echo $reports->get_business_category_id();?>");
</script>