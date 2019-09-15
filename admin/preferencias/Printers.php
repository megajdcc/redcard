<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 
$con = new assets\libs\connection();
use admin\libs\Printers;
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


$printer = new Printers($con);

if(isset($_REQUEST['registrar'])){



}

if(isset($_REQUEST['eliminar'])){
	
}

$includes = new admin\libs\includes($con);
$properties['title'] = 'Impresoras | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>

<div class="row">
	<div class="background-white p20 mb50 col-xs-12 col-sm-12">
		<?php  echo $printer->getNotification();?>
		<div class="page-title">
			<h1>Impresoras en el Hotel</h1>
		</div>
		<div class="background-white p20 mb50">
				
				<table id="impresoras" class=" col-lg-12 display" cellspacing="0" width="100%">
					
					<thead>
						<tr>
							<th>Id</th>
							<th>Equipo</th>
							<th>Impresora</th>
							<th>Estado</th>
							<th>Hotel</th>
							<th></th>
						</tr>
					</thead>
					<tbody>

						<?php echo $printer->getDatos(); ?>
					
					</tbody>
				</table>

		</div>

	</div>
</div>


<div class="modal fade " id="hoteles" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="true">
			<div class="modal-dialog modal-sm" role="document">
				<div class="modal-content modal-dialog-centered">
					
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">Establecer Hotel</h5>
						
					</div>

					<div class="modal-body">

						<div class="form-group">

							<label for="hotel">Hoteles</label>
							
							<select name="hotel">
								<option value="0">Seleccione Hotel</option>
								<?php echo $printer->getHoteles();?>
							</select>
							
						</div>
	
								
					</div>
						
					<div class="modal-footer">
						
						<button style="margin-left: auto;" type="submit"  data-path="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" name="registrar" class="guardar btn btn-success">Registrar</button>
						<button  type="button" class="cerrarmodal btn btn-secondary">Cerrar</button>
					</div>
				</form>

				</div>
			</div>
		</div>
<script>
	
	

	jQuery(document).ready(function($) {

		var print_id = 0;

		$('.establecer').on('click',function(){
			print_id = $(this).attr('data-id');
			$('#hoteles').modal('show');
		});

		$('.cerrarmodal').on('click',function(){
			$('#hoteles').modal('hide');
		});

		$('.guardar').on('click',function(){
			var hotel = $('select[name="hotel"]').val();

			if(hotel !=0){
					$(this).attr('disabled', 'disabled');
					$(this).text('Guardando por favor espere');
					$.ajax({
					url: '/admin/controller/peticiones.php',
					type: 'post',
					dataType: 'json',
					data: {peticion: 'establecerimpresora',impresora:print_id,hotel:hotel},
					})
					.done(function(response) {
					if(response.peticion){
					location.reload();
					}else{
					$.alert('No se pudo establecer la impresora al hotel intentalo mas tarde.');
					$(this).removeAttr('disabled');
					$(this).text('Registrar');
					}
					});
		 
				}else{
					$.alert('Tiene que elegir un hotel!');
				}
			
		});

		$('.quitar').on('click',function(){
				var hotel = $(this).attr('data-hotel');
				$.confirm({
					title:'Confirmar!',
					content:'Esta seguro de quitar el hotel?',
					buttons:{
						
						Si:function(){
							$.ajax({
								url: '/admin/controller/peticiones.php',
								type: 'post',
								dataType: 'json',
								data: {peticion: 'quitarhotel',hotel:hotel},
							})
							.done(function(response) {
								if(response.peticion){
									location.reload();
								}else{
									$alert({
										title:'Alert!',
										content:'No se pudo el quitar el hotel en este momento, intentelo mas tarde!'
									});
								}
							});
						},
						
						No:function(){
							$.alert('Ok puedes quitarlo en otro momento!');
						}

					}
				});
		});
	

	 // var t = $('#impresoras').DataTable( {
		// 			paging        	:true,
		// 			lengthChange	:false,
		// 			scrollY      	:400,
		// 			scrollCollapse	:true,
		// 			ordering		:true,
		// 			responsive:true,
					
		// 			dom:'lrtip',
		// 			ajax:{
		// 				url:'/admin/controller/peticiones.php',
		// 				type:'POST',
		// 				dataType:'JSON',
		// 				data:function(d){
		// 					d.peticion ='cargarimpresoras';
		// 				}
		// 			},
					
		// 			columns:[
		// 				 		{data:'id',responsivePriority:1},
		// 				 		{data:'name_printer',responsivePriority:2,width:'200'},
		// 				 		{data:'id_printer',responsivePriority:2},
		// 				 		{data:'name_equipo',responsivePriority:1},
		// 				 		{data:'id_hotel',responsivePriority:2},
						 	
		// 			 		],
		// 	         language:{
		// 	                        "lengthMenu": "Mostrar _MENU_ registros por pagina",
		// 	                        "info": "",
		// 	                        "infoEmpty": "No se encontro Ninguna impresora",
		// 	                        "infoFiltered": "(filtrada de _MAX_ registros)",
		// 	                        "search": "Buscar: ",
		// 	                        "paginate": {
		// 	                            "next":       "Siguiente",
		// 	                            "previous":   "Anterior"
		// 	                        },
		// 	                    },
		// 	        order:[[0,'desc']]

			        
		// 	    });

		// });
		// 
		 var t = $('#impresoras').DataTable({
						"paging"        	:true,
						"lengthChange"	:false,
						"scrollY"    	:400,
						"scrollCollapse": true,
			      "language": {
			                        "lengthMenu": "Mostrar _MENU_ Registros por pagina",
			                        "info": "",
			                        "infoEmpty": "No se encontro ningúna impresora",
			                        "infoFiltered": "(filtrada de _MAX_ registros)",
			                        "search": "Buscar:",
			                        "paginate": {
			                            "next":       "Siguiente",
			                            "previous":   "Anterior"
			                        },
			                    },
			    });

		});

</script>

<?php echo $footer = $includes->get_admin_footer(); ?>


