<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

if(!isset($_SESSION['perfil'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
	}


use Hotel\models\Usuarios;
use Hotel\models\Includes;
use Hotel\models\Reservacion;

$reserva = new Reservacion($con);
$usuarios = new Usuarios($con);


if($_SERVER["REQUEST_METHOD"] == "POST"){
 	// Peticiones al servidors 	
 	// 
 	
 	if(isset($_POST['pdf'])){
 		$reserva->report($_POST);
 		
 	}else if(isset($_POST['date_start'])){
		$reserva->Buscar($_POST);
	} 
 	
}


$includes = new Includes($con);

$properties['title'] = 'Reservaciones | Travel Points';
$properties['description'] = 'Reservaciones en travel points';


echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>


<?php echo $con->get_notify();?>

<div class="row">
	<div class="col-sm-12">
		<?php echo $usuarios->getNotificacion();?>
		<div class="background-white p20 mb50">
			<div class="page-title">
				<h1>Lista de Reservaciones</h1>
			</div>
			<div class="row">
					<div class=" col-sm-10">
						<form class="pull-right" method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>">
							
							<div class="col-sm-4">
								<div class="form-group">
									<label for="start">Fecha y hora de inicio</label>
									<div class="input-group date" id="event-start">
										<input class="form-control" type="text" id="star" name="date_start" value="<?php echo $reserva->getFecha1(); ?>" placeholder="Fecha y hora" />
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									</div>
								</div>
							</div>

							<div class="col-sm-4">
								<div class="form-group">
									<label for="end">Fecha y hora de fin</label>
									<div class="input-group date" id="event-end">

										<input class="form-control" type="text" id="en" name="date_end" value="<?php echo $reserva->getFecha2(); ?>" placeholder="Fecha y hora" />
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									</div>
									
								</div>
							</div>

							<div class="col-sm-4">
									<label>Buscar</label>
									<div class="form-group">
											<button class="btn btn-success buscar" type="submit"><i class="fa fa-search"></i></button>
											<a href="<?php echo _safe(HOST.'/Hotel/reservaciones/reservaciones');?>" class="btn btn-info">Limpiar</a>
									</div>			
							</div>

						</form>
					</div>
					
					<div class="col-sm-2">
						<form class="pull-right" method="post" name="imprimir" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>" target="_blank">
					
							<label>Descargar</label>
							
							<div class="form-group">				
								<input type="hidden" name="start" value="">
								<input type="hidden" name="end" value="">
								
								<button class="btn btn-default text-danger" type="submit" name="pdf"><i class="fa fa-file-pdf-o"></i>PDF</button>
							</div>
							<script>
								
								$(document).ready(function() {
									
									$('form[name="imprimir"]').bind('submit',function(e){

										var start  = $('input[name="date_start"]').val();
										var end = $('input[name="date_end"]').val();

										$('input[name="start"]').val(start);
										$('input[name="end"]').val(end);

										return true;

									});
								});
							</script>
						</form>
					</div>
						

				</div>
				<table  id="listareservaciones" class="display" cellspacing="0" width="100%">

					<thead>
						<tr>
							<th>Fecha</th>
							<th>Negocio</th>
							<th>Usuario</th>
							<th>Registrante</th>
							<th>Status</th>
							<th>Fecha reserva</th>
							<th></th>
						</tr>
					</thead>
					
					<tbody>
						<?php echo $reserva->getReserva();?>
					</tbody>

				</table>


				<a class="btn btn-success" href="<?php echo HOST.'/Hotel/reservaciones/'?>">Reservar</a>
		</div>
	</div>
</div>

<?php echo $usuarios->Modal(); ?>

<script>

				 var t = $('#listareservaciones').DataTable( {
					"paging"        :         false,
					"scrollY"       :        "400px",
					"scrollCollapse": true,
			         "language": {
			                        "lengthMenu": "Mostar _MENU_ registros por pagina",
			                        "info": "",
			                        "infoEmpty": "No se encontro ninguna reservación",
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
			    });
    

    $(document).ready(function() {
    	

    	$('.cancelar').on('click',function(e){



    		var id = $(this).attr('data-id');

    		var result = confirm('¿Realmente desea cancelar esta reservación ?');


    		if(result){
					$.ajax({
					url: '/Hotel/controller/peticiones.php',
					type: 'POST',
					dataType: 'JSON',
					data: {peticion: 'cancelarreservacion',idreserva:id},
					})
					.done(function(response) {
					if(response.peticion){
					$('#'+id).hide('slow', function() {
						
					});
					}
					})
					.fail(function() {
					console.log("error");
					})
					.always(function() {
					console.log("complete");
					});
    		}else{
    			return false;
    		}
    		
    		
    	});
    });
</script>
<?php echo $footer = $includes->get_admin_footer(); ?>