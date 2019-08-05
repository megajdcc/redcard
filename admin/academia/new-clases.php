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
	if(isset($_POST['grabar'])){
		$academia->setClass($_POST);
	}
}


$includes = new admin\libs\includes($con);
$properties['title'] = 'Academia | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>

<div class="row">
	<div class="col-sm-12">

		<div class="background-white p20 mb30">

			<?php echo $academia->get_notification(); ?>

			<h2 class="page-title">Nueva Clase</h2>


			
			<form action="<?php echo HOST.'/admin/academia/new-clases.php' ?>" method="POST" name="nueva-clase">
			
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
							
								<select class="form-control categorias" name="categoria" required>
									<option value="0">Select</option>
									<?php $academia->listarcategoria();?>
								</select>
								<button type="button" class="input-group-addon new-categories" data-toggle="tooltip" data-placement="left" title="New Categorie"><i class="fa fa-plus-circle"></i></button>


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

<div class="modal" id="modal-afiliar-new-categories" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-check-square"></i> | Nueva Categoria de clase</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>


      	<form name="new-categories-form" method="POST">
		      <div class="modal-body">

		      	<div class="row">
		      		<div class="col-lg-12">
						<div class="form-group">
							<label for="categoria">Categoria</label>
							<div class="input-group categoria">
								<strong class="input-group-addon"><i class="fa fa-edit"></i></strong>
								<input  type="text" id="categoria" name="l-categoria" class="form-control" placeholder="Nueva Categoria" autocomplete="off" required>
							</div>
						</div>
		      		</div>
		      	</div>

		      	<div class="row">
		      		<div class="col-lg-12">
		      			<div class="table-categories">
		      				<label> Categorias registradas</label>
		      			<table id="listarcategoria" cellspacing="0" width="90%">
		      				<thead>
		      					<th>Categoria</th>
		      					<th></th>
		      				</thead>

		      				<tbody>
		      				</tbody>
		      			</table>
		      			</div>
		      			
		      		</div>
		      	</div>
		      
			</div>
	
		      <div class="modal-footer">
		        <button type="submit" class="btn btn-primary grabar-categories"><i class="fa fa-save"></i>Guardar</button>
		        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-close"></i>Cerrar</button>
		      </div>
  		</form>
    
    </div>
  </div>
</div>

<script>
	$(document).ready(function() {
		$('.new-categories').on('click',function(e){
			$('#modal-afiliar-new-categories').modal('show');
		});

		var t = $('#listarcategoria').DataTable( {
					paging        	:true,
					lengthChange	:false,
					scrollY      	:400,
					scrollCollapse	:true,
					ordering		:true,
					
					dom:'lrtip',
					ajax:{
						url:'/admin/controller/peticiones.php',
						type:'POST',
						dataType:'JSON',
						data:function(d){
							d.peticion ='cargarcategoria';
						}
					},
					
					columns:[
						 		{data:'categoria'},
						 		{data:'id'}

					 		],
			         language:{
			                        "lengthMenu": "Mostar _MENU_ registros por pagina",
			                        "info": "",
			                        "infoEmpty": "No se encontro ninguna categoria",
			                        "infoFiltered": "(filtrada de _MAX_ registros)",
			                        "search": "Buscar: ",
			                        "paginate": {
			                            "next":       "Siguiente",
			                            "previous":   "Anterior"
			                        },
			                    },
			        columnsDefs:[{
			        	orderable:true,targets:0
			        }],
			        order:[[0,'asc']]
			    });



		$('form[name="new-categories-form"]').bind('submit',function(e){
			e.preventDefault();

			$('.grabar-categories').text('Grabando categoria...');
			$('.grabar-categories').attr('disabled', 'disabled');

			var newcategor = $('input[name="l-categoria"]').val();

			$.ajax({
				url: '/admin/controller/peticiones.php',
				type: 'POST',
				dataType: 'JSON',
				data: {peticion: 'new-categories','categories':newcategor},
			})
			.done(function(response) {
				if(response.peticion){
					$('.grabar-categories').removeAttr('disabled');
					$('.grabar-categories').text('Grabar');
					t.ajax.reload();

					$('input[name="l-categoria"]').val('');

					var option  = document.createElement('option');

					option.value=response.id;
					option.text = newcategor;

					$('select[name="categoria"]').append(option);

				}else{
					$('.grabar-categories').removeAttr('disabled');
					$('.grabar-categories').text('Grabar');
					alert('No se pudo registrar la nueva categoria');
					t.ajax.reload();
				}
			});

			return false;
		});

		t.on('draw',function(){
			$('.eliminar').click(function(e){

				var id = $(this).attr('data-id');

				$.confirm({
								title:'Confirm!',
								content:'Esta seguro usted de querer eliminar esta categoria?',
								buttons:{
									seguro:function(){

										
										
										$.ajax({
										url: '/admin/controller/peticiones.php',
										type: 'POST',
										dataType: 'JSON',
										data: {peticion: 'eliminar-categories',idcategorie:id},
										})
										.done(function(response) {
										if(response.peticion){

											t.ajax.reload();
											$('select[name="categoria"] option[value="'+id+'"]').remove();
											$.alert('Se ha eliminado exitosamente la categoria');
										}else{
										$.alert('No se pudo eliminar la categoria, puede que se este utilizando en una clase existente...');
										t.ajax.reload();
										}
										});
									},
									cancelar:function(){
										$.alert('Ok lo puedes intentar despues no pasa nada.!');
									}
								}
							});

				
					
				
					
					
					
			});
		});

		



	});

</script>

<?php echo $navbar = $includes->get_admin_footer(); ?>