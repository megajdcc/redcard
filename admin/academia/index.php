<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 
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


use admin\libs\Academia;

$academia = new Academia($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){

}

$includes = new admin\libs\includes($con);
$properties['title'] = 'Academia | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>

<div class="modal" id="modal-mostrar-clase" tabindex="-1" role="dialog">
  <div class="modal-dialog  modal-dialog-centered modal-lg" role="document">
    <div class="modal-content modal-video">
    	<div class="modal-header header-video">
    	 <button type="button" class="close" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
    	 </div>
		<div class="modal-body modal-video">
		      	<iframe src="" width="100%" height="400" frameborder="0" allow="autoplay" allowfullscreen allowcriptaccess="always"></iframe>
		</div>

		
    </div>

  </div>
</div>



<div class="modal" id="modal-edit-class" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-check-square"></i> | Edici&oacute;n de class</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
      	<form action="<?php echo HOST.'/admin/academia/new-clases.php' ?>" method="POST" name="edit-clase">
			
				<div class="row">

					<section class="col-sm-12 col-lg-4">
						<div class="form-group">
							<label for="titulo">T&iacute;tulo de la clase | <strong class="required">*</strong></label>
							<div class="input-group">
								<span class="fa fa-edit input-group-addon"></span>
								<input type="text" name="titulo" placeholder="Please enter the title class" class="form-control" required> 

							</div>
						</div>
					</section>


					<section class="col-sm-12 col-lg-4">
						<div class="form-group">
							<label for="titulo">Decripci&oacute;n de la clase </label>
							<div class="input-group">
								<span class="fa fa-edit input-group-addon"></span>
								<input type="text" name="description" placeholder="Please enter short description" class="form-control">

							</div>
						</div>
					</section>


					<section class="col-sm-12 col-lg-4">
						<div class="form-group ">
							<label for="categoria">Categoria de la clase | <strong class="required ">*</strong></label>

					
								<div class="input-group">
								
									<select class="categorias" name="categoria" id="categoria">
										<option value="0">Select</option>
										<?php $academia->listarcategoria();?>
									</select>

								</div>
						</div>
					</section>
				</div>

				<div class="row">
					<div class="col-sm-12 col-lg-12">
						<div class="form-group">
							<label for="contenido">Contenido</label>
							<textarea class="form-control" name="contenido" placeholder="De que se trata esta clase?,  a quien esta dirigida?,  que contiene?. Escriba una rese&ntilde;a al respecto... "></textarea>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-12 col-lg-12">
					<div class="form-group">
						<label>El video de la clase a los socios de TravelPoints | <strong class="required">*</strong></label>
						<p>Puedes ingresar:</p>
						<ul>
							<li>Cualquier enlace a un video de <strong>YouTube</strong></li>
							<li>Tambi&eacute;n puedes insertar el c&oacute;digo HTML (iframe) que provee <strong>YouTube</strong> o <strong>Vimeo</strong>.</li>
						</ul>

							<textarea class="form-control" id="video" name="video" placeholder="Enlace o c&oacute;digo" required></textarea>
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-sm-12 col-lg-12">
						<p> Los campos marcados con <strong class="required">*</strong>: Son requeridos.<p>
					</div>
				</div>

				<div class="row">
					<div class="btn-group col-lg-12">
						<button class="btn btn-sucess" name="grabar" type="submit"><i class="fa fa-save"></i>Grabar</button>
					</div>
				</div>

			</form>
      </div>

			
			

      	
    </div>
  </div>
</div>






<div class="row">
	<div class="col-sm-12">
		<div class="background-white p20 mb30">
			<h2 class="page-title">Videos tutoriales</h2>

			<div class="row">
				<div class="form-group col-sm-12 col-lg-12" >
					<label for="busqueda">Digite para buscar el video.</label>
					<div class="input-group campo-busqueda-video">
						
						<input data-toggle="tooltip" title="Puede buscar por categoría, título e incluso si lo que busca se encuentra dentro del contenido del mismo lo lista. Intentelo usted ahora!" data-placement="top" type="search" name="busqueda" class="form-control" placeholder="Teclee para buscar el video tutorial...">

					<!-- 	<div class="input-group-append">
							<button class="input-group-button btn btn-info"><span class="fa fa-search"></span></button>
						</div> -->
					</div>
				</div>
				

				<!-- <div class="form-group col-sm-12 col-lg-4">
					<label for="categoria">Categoria del video</label>

					<select class="form-control" name="categoria">
						<option value="0">All categories</option>
						<?php 
							//$academia->listarcategoria();
						 ?>

						
					</select>
				</div> -->
				
			</div>
			 <script >
						 	$(document).ready(function() {
								$('*[data-background-image]').each(function() {
									$(this).css({
									'background-image': 'url(' + $(this).data('background-image') + ')'
									});
								});
						 	});
						 </script>


			<div class="row lista-clases">



				<?php 

					$academia->getVideos();

				 ?>

			<script>
				
				$(document).ready(function() {

						var src = '';
					$('input[name="busqueda"]').on('keyup',function(){
						var val = $(this).val();
						if(val.length > 0 ){
							
							if($('.fondo-busqueda').length  != 1){
								var fondo = document.createElement("div");
								fondo.className = 'fondo-busqueda';

								var loadingcontent = document.createElement('div');
								loadingcontent.className = 'loading-v';
								fondo.append(loadingcontent);

								$('.lista-clases').append(fondo);
							}
							

						}else{
							$('.fondo-busqueda').remove();
						} 

						$.ajax({
							url: '/admin/controller/peticiones.php',
							type: 'POST',
							dataType: 'JSON',
							data: {peticion: 'busqueda_clases',busqueda:val},
						})
						.done(function(response) {
							if(response.peticion){

								$('.contenedor-video').remove();

								$('.fondo-busqueda').remove();
								var data = response.datos;
								for(var key in data){
									$('.lista-clases').append(data[key]);
								}

								$('*[data-background-image]').each(function() {
									$(this).css({
									'background-image': 'url(' + $(this).data('background-image') + ')'
									});
								});

							}

							$('.eliminar').click(function(event) {
								var idclass = $(this).attr('data-id');
								
								eliminarClass(idclass);
							
							});

							
							$('.reproducir').on('click',function(e){
								src = $(this).attr('data-url');
								
								var newurl = src.replace("?","",'gi').replace('watchv=','embed/','gi');
								// alert(newurl)
								
								$('#modal-mostrar-clase').modal('show');
								$('#modal-mostrar-clase iframe').attr('src', newurl+'?autoplay=1&amp;modestbranding=1&amp;showinfo=0');
								
							});
							
							});
					

						

					});

				

					$('.reproducir').on('click',function(e){
						src = $(this).attr('data-url');

						var newurl = src.replace("?","",'gi').replace('watchv=','embed/','gi');
						// alert(newurl)

						
						$('#modal-mostrar-clase').modal('show');
						$('#modal-mostrar-clase iframe').attr('src', newurl+'?autoplay=1&amp;modestbranding=1&amp;showinfo=0');

						


					});


					$('.editar').on('click',function(e){



						var id = $(this).attr('data-id');



						$.ajax({
							url: '/admin/controller/peticiones.php',
							type: 'POST',
							dataType: 'JSON',
							data: {peticion: 'capturar_class',id_class:id},
						})
						.done(function(response) {
							
							if(response.peticion){
								

								$('form[name="edit-clase"] input[name="titulo"]').val(response.datos.titulo);
								$('form[name="edit-clase"] input[name="description"]').val(response.datos.descripcion);


								var idcategoria = response.datos.idcategoria;


								var optioncategoria = new Option(response.datos.categoria,response.datos.idcategoria,true);
								$('form[name="edit-clase"] selec[name="categoria"]').remove();


								$('form[name="edit-clase"] 	textarea[name="contenido"]').val(response.datos.contenido);
								$('form[name="edit-clase"] textarea[name="video"]').val(response.datos.urlvideo);
								$('#modal-edit-class').modal('show');
								
							}

						});
						

						
					});


					$('.close').on('click',function(e){
						$('#modal-mostrar-clase iframe').attr('src', src);
						$('#modal-mostrar-clase').modal('hide');
					});



				});


						$('.eliminar').click(function(event) {
								var idclass = $(this).attr('data-id');
								
								eliminarClass(idclass);
							
							});


						function eliminarClass(id){

							if(id == null){
								$.alert('No se puede eliminar en este momento intente mas tarde.');
							}else{
								$.confirm({
								title:'Confirm!',
								content:'Esta seguro usted de querer eliminar esta clase?',
								buttons:{
									seguro:function(){

										$.ajax({
										url: '/admin/controller/peticiones.php',
										type: 'post',
										dataType: 'JSON',
										data: {peticion: 'eliminarClass',idclass:id},
										})
										.done(function(response) {
										
										if(response.peticion){
											$('#class-'+id).hide('slow', function() {
												$.alert('Se ha eliminado exitosamente la clase.');
											});
										
										}else{
										alert('No se ha podido eliminar la clase, intente mas tarde');
										}
										
										});
									},
									cancelar:function(){
										$.alert('Ok lo puedes intentar despues no pasa nada.!');
									}
								}
							});
							}
							
							

							
							
						}
			</script>

			</div>
		
		</div>
	</div>
</div>
<?php echo $navbar = $includes->get_admin_footer(); ?>