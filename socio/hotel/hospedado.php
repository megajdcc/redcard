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

	echo var_dump($_POST);

	// if(isset($_POST['send'])){
	// 	// echo 'jhonatan';
	// 	// $result  =  $Huesped->procesar($_POST);
		
	// 		// header('location: /login');
	// }

	// if(isset($_POST['quitar'])  && !empty($_POST['quitar'])){
	// 	// $Huesped->quitar($_POST);
	// }

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



								<form id="huespedform" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>" method="post"  enctype="multipart/form-data" >
									<div class="background-white p30 mb50">
									  <h3 class="page-title">Informaci&oacute;n del Hotel</h3>
									  

									  <div class="row">
									  	<div class="col-12 d-flex">
									  		<div class="form-group flex" data-toggle="tooltip"  title="Ingrese el nombre del hotel o busque en nuestro catalógo los hoteles registrados en el sistema">
								            <label for="business-name">Nombre del Hotel donde te hospedas:<span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
								            <div class="input-hotel">

								            	<select class="form-control" name="hotel" data-live-search="true">
								            		<option value="0" selected>Seleccione su hotel</option>
								            		<?php $Huesped->getHoteles(); ?>
								            	</select>
								             
													
								            </div>
								            <small id="hotel" class="form-text text-muted">El sistema te ayudara a detectar si existe o no el hotel en nuestro directorio. Si no existe no te preocupes. </small>
								            
								           </div><!-- /.form-group -->
									  	</div>
								         


									  </div>

									  <div class="row">
									  	<section class="col-ld-4">
											<?php if(!empty($Huesped->getNombreHotel())){?>
													<button  class="retirar btn btn-outline-success btn-xl" data-path="<?php echo _safe(HOST.'/socio/perfil/huesped'); ?>" type="button" value="grabar" name="send"><i class="fa fa-remove"></i>Borrar Hotel</button>
													<?php }else{?>
											<button  class="enviar btn btn-outline-success btn-xl" type="submit" value="grabar" name="send"><i class="fa fa-save"></i>Grabar</button>
											<?php 	}  ?>
									  	</section>
									  </div>


									</div>
									 
								</form>
						</div><!-- /.content -->
					</div><!-- /.col-* -->
				</div><!-- /.row -->
			</div><!-- /.container -->
		</div><!-- /.main-inner -->
	</div><!-- /.main -->
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

				save.text("Procesando por favor Espere...");
				save.attr("disabled","disabled");

				return true;

		});
});

</script>

<?php echo $footer = $includes->get_main_footer(); ?>