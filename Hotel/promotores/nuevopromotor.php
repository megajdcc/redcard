<?php 

	require_once $_SERVER['DOCUMENT_ROOT'].'assets/libs/init.php';


	if(!isset($_SESSION['perfil'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
	}
	
	if(!isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
	}


	$conexion = new \assets\libs\connection; 

	use Hotel\models\Includes;
	use Hotel\models\Promotor;

	$includes = new Includes($conexion);
	$promotor = new Promotor($conexion);


	if($_SERVER["REQUEST_METHOD"] == "POST"){
			
	}



$properties['title'] = 'New Promotor | Travel Points';
$properties['description'] = '';

echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $conexion->get_notify();?>

 <div class="row panel-newpromotor">
		<?php echo $promotor->getNotificacion();?>
	<div class="background-white p20 mb30">
				
		<div class="page-title"><h1>Nuevo promotor</h1></div>
	<button class="show-lista btn btn-secondary" type="button"><i class="fa fa-info-circle"></i> Importante Saber!</button>
		
	
		<ul class="list-group lista">
	
			<li class="list-group-item">El promotor en su panel, podr&aacute; modificar los datos de usuario excepto el email y el cargo</li>
			<li class="list-group-item"> Las comisiones obtenidas del hotel son absolutas de quien realice la operaci&oacute;n de reserva, excepto las comisiones de aquellos consumos obtenidos de reservas realizadas de forma directa por el usuario.</li>
			<li class="list-group-item">
		Si desea eliminar a un promotor de su hotel y el mismo tiene comisiones acumuladas, deber&aacute; notificar al mismo, solicitar el retiro del total de comisiones acumuladas.</li>
		</ul>
		<form action="<?php echo HOST.'/Hotel/promotores/nuevopromotor.php' ?>" method="POST" name="form-newpromotor" id="form-newpromotor">
		<div class="row">
			


			<section class="col-xs-12 col-lg-6">
				

				<h3 class="title">Datos personales</h3>


				<div class="form-group">
					<label for="nombre">Nombre: | <span class="required">*</span> </label>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-user"></i></span>
						<input type="text" name="nombre" class="form-control" placeholder="Nombre del promotor" required>
					</div>
				</div>


				<div class="form-group">
					<label for="apellido">Apellido: | <span class="required">*</span> </label>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-user"></i></span>
						<input type="text" name="apellido" class="form-control" placeholder="Apellido del promotor" required>
					</div>
				</div>

				<div class="form-group">
					<label for="telefono">Tel&eacute;fono:</label>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-phone"></i></span>
						<span class="input-group-addon">+52 - </span>
						<input type="tel" name="telefono" pattern="[0-9]{10}" class="form-control" placeholder="Tel&eacute;fono del promotor. Example: 3224518789">
					</div>
				</div>


			</section>

			<section class="col-xs-12 col-lg-6">

				<h3 class="title">Datos de usuario</h3>
				
				<div class="form-group" data-toggle="tooltip" title="El nombre de usuario(username), se podr&aacute; utilizar para que el promotor pueda iniciar sesi&oacute;n en el panel del hotel.">
					<label for="username">Username: | <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-user"></i></span>
						<input type="text" name="username" class="form-control" placeholder="Username" required>
					</div>
				</div>
				

				<div class="form-group" data-toggle="tooltip" title="El email al igual que el nombre de usuario(username), se podr&aacute; utilizar para que el promotor pueda iniciar sesi&oacute;n en el panel del hotel.">
					<label for="email">Email: | <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-envelope"></i></span>
						<input type="email" name="email" class="form-control" placeholder="Email del promotor example: emailpromotor@example.com" required>
					</div>
				</div>

				<div class="form-group" data-toggle="tooltip" title="El cargo es una representaci&oacute;n de cada hotel, Es utilizada para diferenciar a los departamentos, y para ">
					<label for="email">Cargo: | <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
					<div class="input-group select-cargo">


						<span class="input-group-addon"><i class="fa fa-black-tie"></i></span>
						<select name="cargo" id="cargo" class="form-control" required>
							<option value="0">Seleccione</option>
							<?php $promotor->getCargos(); ?>
						</select>
						<button class="btn btn-info new-cargo" type="button">New Cargo</button>
					</div>

				</div>
			</section>
		
		</div>

		

	<label>Los campos marcados con <span class="required">*</span> son requeridos.</label>

			
			<input type="hidden" name="peticion" value="grabarpromotor">
		<footer class="row">
			<button type="submit" name="grabar" class="btn btn-success" data-toggle="tooltip" title="Se utliza tanto para guardar como para modificar datos"> <i class="fa fa-save"></i> Grabar</button>
			<button type="submit" name="de-alta" class="btn btn-info" data-toggle="tooltip" title="Activar al promotor en el hotel"><i class="fa fa-child"></i> Dar de alta</button>
			<button type="submit" name="delete" data-toggle="tooltip" title="Eliminar el promotor ? Importante si el promotor, tiene comisiones acumuladas no podr&aacute; eliminar a el mismo, hasta que el promotor haya retirado su comisi&oacute;n correspondiente" class="btn btn-warning"> <i class="fa fa-close"></i> Eliminar</button>
		</footer>

		</form>
	
	</div>


	<div class="modal" id="modal-afiliar-cargo" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-check-square"></i> | Nuevo cargo de hotel</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      	<form name="new-cargo-form" method="POST">
		      <div class="modal-body">

		      	<div class="row">
		      		<div class="col-lg-12">
						<div class="form-group">
							<label for="categoria">Cargo</label>
							<div class="input-group categoria">
								<strong class="input-group-addon"><i class="fa fa-edit"></i></strong>
								<input  type="text" id="cargo" name="l-cargo" class="form-control" placeholder="Nueva Cargo" autocomplete="off" required>
							</div>
						</div>
		      		</div>
		      	</div>

		      	<div class="row">
		      		<div class="col-lg-12">
		      			<div class="table-categories">
		      				<label> Cargos Registrados</label>
		      			<table id="listarcargos" cellspacing="0" width="90%">
		      				<thead>
		      					<th>Cargos</th>
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

		// CARGAR SELECT CARGOS 
		// 
		// 
		
		$.ajax({
			url: '/Hotel/controller/peticiones.php',
			type: 'POST',
			dataType: 'JSON',
			data: {peticion: 'cargarcargos'},
		})
		.done(function(response) {
			if(response.peticion){

			}
		});
		


		$('.new-promotor').on('click',function(e){

			var url = $(this).attr('data-url');
			$('html').close('slow',function(){
				location.replace(url);
			});
		});



		var t = $('#listarcargos').DataTable( {
					paging        	:true,
					lengthChange	:false,
					scrollY      	:200,
					scrollCollapse	:true,
					ordering		:true,
					
					dom:'lrtip',
					ajax:{
						url:'/Hotel/controller/peticiones.php',
						type:'POST',
						dataType:'JSON',
						data:function(d){
							d.peticion ='listarcargos';
						}
					},
					
					columns:[
						 		{data:'cargo'},
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


		t.on('draw',function(){
			

			$('.eliminar').on('click',function(){
				var idcargo = $(this).attr('data-id');

				$.confirm({
					title:'Confirm!',
					content:'Esta seguro de eliminar el cargo?',
					buttons:{
						Si:function(){
							$.ajax({
							url: '/Hotel/controller/peticiones.php',
							type: 'POST',
							dataType: 'JSON',
							data: {peticion: 'eliminarcargo',id:idcargo},
							})
							.done(function(response) {
							if(response.peticion){
							$.alert('Cargo eliminado correctamente');
							t.ajax.reload(null,false);
							}else{
							$.alert('No se pudo eliminar el cargo, posible utilización por parte de un promotor activo');
							}
							});

						},
						No:function(){
							$.alert('Ok lo puede Eliminar despues');
						}
					}
				});
								
			});

		});


		var listaoculta = false;


		$('.show-lista').on('click',function(){

			if(listaoculta == false){
				$('.lista').addClass('lista-show');
				listaoculta = true;
			}else{
				$('.lista').removeClass('lista-show');
				listaoculta = false;
			}
			
		});


		$('.new-cargo').on('click',function(){
			$('#modal-afiliar-cargo').modal('show');
		});



		$('form[name="new-cargo-form"]').on('submit',function(e){
			e.preventDefault();


			var cargo = $('input[name="l-cargo"]').val();


			$.confirm({
				title:'Confirmación!',
				content:'Esta seguro de guardar este cargo?',
				buttons:{
					Si:function(){
					
						$('.grabar-categories').attr('disabled','disabled');
						$('.grabar-categories').text('Guardando...');


						$.ajax({
							url: '/Hotel/controller/peticiones.php',
							type: 'POST',
							dataType: 'JSON',
							data: {peticion: 'guardarcargo',cargo:cargo},
						})
						.done(function(response) {
							if(response.peticion){
								$.alert('Registro con exito!');
								$('.grabar-categories').removeAttr('disabled');
								$('.grabar-categories').text('');
								$('.grabar-categories').append('<i class="fa fa-save"></i>Guardar');

								

								

								$('input[name="l-cargo"]').val('');

								t.ajax.reload(null,false);
								var optioncargo = document.createElement("option");

								optioncargo.value = response.idcargo;
								optioncargo.text = response.newcargo;
								$('#cargo').append(optioncargo);

							}else{
								$.alert('No se pudo guardar el nuevo cargo, puede ser que ya esté registrado por favor verifique!');
								$('.grabar-categories').removeAttr('disabled');
								$('.grabar-categories').text('');
								$('.grabar-categories').append('<i class="fa fa-save"></i>Guardar');
							}
						});
					},
					No:function(){
						$.alert('OK corrige e intentalo de nuevo!');
					}
				}
			});
		});


		$('form[name="form-newpromotor"]').bind('submit',function(e){

			e.preventDefault();

			var formu = new FormData(document.getElementById('form-newpromotor'));

			// formu.append('peticion','grabarpromotor');

			$('button[name="grabar"]').attr('disabled', 'disabled');
			$('button[name="delete"]').attr('disabled', 'disabled');
			$('button[name="de-alta"]').attr('disabled', 'disabled');
			$('button[name="grabar"]').text('Grabando por favor espere...');


			$.ajax({
				url: '/Hotel/controller/peticiones.php',
				type: 'POST',
				dataType: 'JSON',
				data: $(this).serialize(),
				processData:false
			})
			.done(function(response) {
				if(response.peticion){
					$.alert(response.mensaje);

				}else{
					$.alert(response,mensaje);

				}
			});
			

			return false;

		});



	});

</script>

<?php echo $footer = $includes->get_admin_footer(); ?>