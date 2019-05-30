<?php 


require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	header('Location: '.HOST.'/login');
	die();
}
if(!isset($_SESSION['user']['id_usuario'])){
	header('Location: '.HOST.'/login');
	die();
}
use Hotel\models\Huesped;

$Huesped = new Huesped($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){


	
	if(isset($_POST['send']) && !empty($_POST['send'])){
		$Huesped->procesar($_POST);
			unset($_POST['send']);
	}

	if(isset($_POST['quitar'])  && !empty($_POST['quitar'])){
		$Huesped->quitar($_POST);
	}

}

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array('options' => array('default' => 1, 'min_range' => 1)));
$rpp = 30;


$includes = new assets\libs\includes($con);
$properties['title'] = 'Socios que he invitado | Travel Points';
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

							<style>
								.registro, .eliminacion{
									display: none;
								} 

							</style>
							<div class="registro alert-success" role="alert">
								<strong>Ya esta!!!</strong> has agregegado correctamente al hotel en la que cual te estas hospedando...
							</div>
							
							<div class="eliminacion alert-danger" role="alert">
								<strong>Ok Hecho!!!</strong> Te esperamos pronto en nuestro Hotel. <STRONG>No dudes en venir nuevamente. !!!</STRONG>
							</div>



								<form id="huespedform" action="<?php echo _safe(HOST.'/socio/perfil/huesped');?>" method="post"  enctype="multipart/form-data" >
									<div class="background-white p30 mb50">
									  <h3 class="page-title">Informaci&oacute;n del Hotel</h3>
									  

									  <div class="row">
									  	<div class="col-12 d-flex">
									  		<div class="form-group flex" data-toggle="tooltip"  title="Ingrese el nombre del hotel o busque en nuestro catalógo los hoteles registrados en el sistema">
								            <label for="business-name">Nombre del Hotel donde te hospedas:<span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
								            <div class="input-hotel">
								            <div class="input-group d-flex">

								            <span class="input-group-addon"><i class="fa fa-building"></i></span>

								            <input class ="form-control" type="text" id="nombre_hotel" name="hotel"  value="<?php echo $Huesped->getNombreHotel(); ?>" placeholder="Nombre del hotel" aria-describedby="hotel" required/>
														<button type ="button"  data-toggle="modal" data-target="#modalhotel"  data-placement="top" name="buscarhotel" class=" buscar form-control"><i class="fa fa-search"></i>Buscar</button>
								            </div>
								             
													
								            </div>
								            <small id="hotel" class="form-text text-muted">El sistema te ayudara a detectar si existe o no el hotel en nuestro directorio. Si no existe no te preocupes. </small>
								            
								           </div><!-- /.form-group -->
									  	</div>

									  	
									  </div>


									</div>
									 
									 	<div class="background-white p30 mb50">
									 		<div class="row">
								         <div class="col-xs-6">
								         	 <p>Los campos marcados son obligatorios <span class="required">*</span></p>
								         </div>
								         <div class="col-xs-6 right">
								         	<input type="hidden" name="idhotel" value="0" class="hotel">

								         	<div class="">
								         		<input type="hidden" name="send" value="registro">
								         	</div>


								         	<?php if(!empty($Huesped->getNombreHotel())){?>
	 										<button  class="retirar btn btn-outline-success btn-xl" data-path="<?php echo _safe(HOST.'/socio/perfil/huesped'); ?>" type="button" value="grabar" name="send"><i class="fa fa-remove"></i>retirarme</button>
								         	<?php }else{?>
 											<button  class="enviar btn btn-outline-success btn-xl" type="submit" value="grabar" name="send"><i class="fa fa-save"></i>Grabar</button>
								         <?php 	}  ?>
								         
								         </div>
								     </div>
							        </div>
								</form>
						</div><!-- /.content -->
					</div><!-- /.col-* -->
				</div><!-- /.row -->
			</div><!-- /.container -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->


	<div class="modal fade " id="modalhotel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
				<div class="modal-content">
					
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">Hoteles Afiliados</h5>
						
					</div>

					<div class="modal-body">
						<section class="table">
						<table  id="hoteles" class="display" cellspacing="0" width="auto">
							<thead>
					            <tr>
					                <th>Nombre</th>
					                <th>Dirección</th>
					                <th>Ubicación</th>
					         
					            </tr> 
					        </thead>

					        <tbody>
					   			<?php 
					   				echo  $Huesped->ListarHoteles();
					   			 ?>
					        </tbody>
							</table>
						</section>
					
					</div>
						
					<div class="modal-footer">
						
						
						<button  type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
						
					</div>
				</div>
			</div>
		</div>

<script>  





 $(document).ready(function() {


 		$('.retirar').click(function(event) {
 			
 			var path  = $(this).attr('data-path');

 			$.ajax({
 				url: path,
 				type: 'POST',
 				data: {quitar: 'retirar'},
 			})
 			.done(function() {
 				 location.reload();
 			})
 			.fail(function() {
 				console.log("error");
 			})

 		}); 

 		$("#huespedform").bind("submit",function(){
		
				var save = $('.enviar');

					$.ajax({
				 	type:$(this).attr("method"),
					url: $(this).attr("action"),
					data: $(this).serialize(),
					beforeSend: function(){
						save.text("Procesando por favor Espere...");
						save.attr("disabled","disabled");
					},
					complete:function(data){
						save.html("<i class='fa fa-remove'></i>Retirarme.");
						
						save.attr('data-retirar', 1);
						save.removeAttr('disabled');
					},
					success:function(data){
						location.reload();
						$('.registro').css({
							display: 'flex',
							padding: '10px 10px'
 							
						});

						$('.eliminacion').css({
							display: 'none'
						});
					},
					error:function(data){
						alert("No se pudo aceptar la solicitud, por favor intente mas tarde... ");
					}

				});	
				

				return false;

		});

  $('#hoteles').DataTable( {
      "paging":         false,
      "scrollY":        "150px",
        "scrollCollapse": true,
         "language": {
                        "lengthMenu": "Mostar _MENU_ registros por pagina",
                        "info": "",
                        "infoEmpty": "No se encontro nigún hotel",
                        "infoFiltered": "(filtrada de _MAX_ registros)",
                        "search": "Buscar:",
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
});

</script>

<?php echo $footer = $includes->get_main_footer(); ?>