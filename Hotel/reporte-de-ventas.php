<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();


use Hotel\models\Includes;
use Hotel\models\ReportesVentas;
use Hotel\models\Dashboard;
if(!isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$Dashboard = new Dashboard($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['pdf'])){
		$Dashboard->get_sales_pdf();
		die();
	}
}
 
$includes = new Includes($con);
$reporte = new ReportesVentas($con);


// $info = new negocio\libs\preference_info($con);
// $users = new admin\libs\get_allusers($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$reporte->set_date($_POST);
}else{
	$reporte->CargarData();
}

$properties['title'] = 'Estado de Cuenta | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $reporte->getNotificacion();?>
		<div class="background-white p20 mb30">
			<form method="post">


				<!-- HEADER FILTROS DE BUSQUEDA -->
				<div class="row">
					<div class="col-sm-4">
						<div class="form-group">
							<label for="start">Fecha y hora de inicio</label>
							<div class="input-group date" id="event-start">
								<input class="form-control" type="text" id="start" name="date_start" value="" placeholder="Fecha y hora de inicio" required/>
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							</div>
							
						</div>
					</div>


					<div class="col-sm-4">
						<div class="form-group">
							<label for="end">Fecha y hora de fin</label>
							<div class="input-group date" id="event-end">
								<input class="form-control" type="text" id="end" name="date_end" value="" placeholder="Fecha y hora de fin" required/>
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							</div>
							
						</div>
					</div>


					<div class="col-sm-4">
						<label>Buscar</label>
						<div class="form-group">
							<button class="btn btn-success" type="submit"><i class="fa fa-search"></i></button>
							<a href="<?php echo _safe(HOST.'/Hotel/reporte-de-ventas/');?>" class="btn btn-info">Limpiar</a>
						</div>
					</div>
				</div>
			</form>
		</div>
		<div class="page-title">
			<h1>Estado de Cuenta
				<form class="pull-right" method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>" target="_blank">
					<button class="btn btn-default text-danger" type="submit" name="pdf"><i class="fa fa-file-pdf-o"></i>PDF</button>
				</form>
			</h1>
		</div>


		<!--  TABLA DE REPORTE -->
		<div class="background-white p20 mb50">
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
						<?php echo $reporte->getEstadoCuenta();?>
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
			        "order": [[ 0, 'asc' ]]
			    } );
    
</script>
		</div>
	</div>
</div>
<?php echo $footer = $includes->get_admin_footer(); ?>