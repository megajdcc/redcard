<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();


use Franquiciatario\models\Includes;
use Franquiciatario\models\ReportesVentas;
use Franquiciatario\models\Dashboard;
if(!isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$Dashboard = new Dashboard($con);
$includes = new Includes($con);
$reporte = new ReportesVentas($con);
if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['pdf'])){
		
		if($_POST['date_start'] && !empty($_POST['date_start']) && $_POST['date_end'] && !empty($_POST['date_end'])){
			$reporte->mostrarpdf($_POST['date_start'], $_POST['date_end']);
			die();
		}else{
			$reporte->mostrarpdf();
			die();
		}
	}
}
// $info = new negocio\libs\preference_info($con);
// $users = new admin\libs\get_allusers($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){

	if(isset($_POST['seard'])){
		$reporte->Buscar($_POST);
	}
	// $reporte->setFechas($_POST);
}

$properties['title'] = 'Estado de Cuenta | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $reporte->getNotificacion();?>
		<form class="pull-right" method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>">
		<div class="background-white p20 mb30">

				<div class="page-title">
					<h1>Estado de Cuenta</h1>
				</div>

				<!-- HEADER FILTROS DE BUSQUEDA -->
				<div class="row">
					<div class="col-sm-4">
						<div class="form-group">
							<label for="start">Fecha y hora de inicio</label>
							<div class="input-group date" id="event-start">
								<input class="form-control" type="text" id="star" name="date_start" value="<?php echo $reporte->getFecha1();?>" placeholder="Fecha y hora de inicio" />
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							</div>
						</div>
					</div>

					<div class="col-sm-4">
						<div class="form-group">
							<label for="end">Fecha y hora de fin</label>
							<div class="input-group date" id="event-end">
								<input class="form-control" type="text" id="en" name="date_end" value="<?php echo $reporte->getFecha2();?>" placeholder="Fecha y hora de fin" />
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							</div>
							
						</div>
					</div>

					<div class="col-sm-4">
						<label>Buscar</label>
						<div class="form-group">

							<button class="btn btn-success buscar" data-path="<?php echo _safe(HOST.'/Hotel/reporte-de-ventas');?>" type="button" ><i class="fa fa-search"></i></button>
							<a href="<?php echo _safe(HOST.'/Hotel/reporte-de-ventas/');?>" class="btn btn-info">Limpiar</a>
							<button class="btn btn-default text-danger" type="submit" name="pdf"><i class="fa fa-file-pdf-o"></i>PDF</button>

						</div>
					</div>
				</div>

		</div>

		<script>


			
			
	
			$('.buscar').click(function(){
				var inicio = document.getElementById('star').value;
				var end = document.getElementById('en').value;
				
				if(inicio != null && end != null){
					
		
				$.ajax({
					url: $(this).attr('data-path'),
					type: 'POST',
					data: {	seard:'buscar',
							f1:inicio,
							f2:end},
				})
				.done(function(data) {

					$(document.documentElement).hide('600',function(){
						document.documentElement.innerHTML = data;
						$(document.documentElement).show('400',function(){

						});
							 
					});


					
				
					
				})
				.fail(function() {
					console.log("error");
				})
		
				
			}
					
			});

			
			


		</script>
		
			
					
			
		
		</form>

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
			       
			    } );
    
</script>
		</div>
	</div>
</div>
<?php echo $footer = $includes->get_admin_footer(); ?>