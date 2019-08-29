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

	if(isset($_POST['idhotel'])){
		$Huesped->procesar($_POST);
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
	
							<?php echo $Huesped->getNotification(); ?>


								<form id="huespedform" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>" method="post"  enctype="multipart/form-data" >
									<div class="background-white p30 mb50">
									  <h3 class="page-title">Informaci&oacute;n del Hotel</h3>
									  

									  <div class="row">
									  	<div class="col-12 d-flex">
									  	<div class="form-group" id="busquedad-hotel-hospedado" data-toggle="tooltip" title="Busca y selecciona tu hotel donde te hospedas, si no se encuentra escribe el nombre del hotel igual.">
									<label for="hotel">Hotel where you stay? (Hotel) | ¿Hotel donde te hospedas;? (Hotel) <i class="fa fa-question-circle text-secondary"></i></label>

									
										<div class="search-placeholder" id="hotel-search-placeholder" style="flex:1 1 auto;">
										<img src="<?php //echo HOST;?>/assets/img/hoteles/defaulthotel.jpg" class="meta-img img-rounded">
										</div>
										

									<input type="text" class="form-control hospedado" name="hotel" value="<?php echo $Huesped->getNombreHotel(); ?>" placeholder="Nombre del hotel donde te hospedas?" autocomplete="false"/>
									
									<input type="hidden" name="idhotel" value="">
								</div>
									  	</div>
								         


									  </div>

									  <div class="row">
									  	<section class="col-ld-4">
											<?php if(!empty($Huesped->getNombreHotel())){?>
													<button  class="retirar btn btn-outline-success btn-xl" data-path="<?php echo $_SERVER['REQUEST_URI']?>" type="button" value="quitar" name="quitar"><i class="fa fa-remove"></i>Borrar Hotel</button>
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