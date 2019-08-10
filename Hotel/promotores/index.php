<?php 

	require_once $_SERVER['DOCUMENT_ROOT'].'assets/libs/init.php';

	$conexion = new \assets\libs\connection; 

	use Hotel\models\Includes;
	use Hotel\models\Promotor;

	$includes = new Includes($conexion);
	$promotor = new Promotor($conexion);


	if($_SERVER["REQUEST_METHOD"] == "POST"){
			
	}



$properties['title'] = 'Promotor | Travel Points';
$properties['description'] = '';

echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $conexion->get_notify();?>

 <div class="row lista-promotores">
		<?php echo $promotor->getNotificacion();?>
			<div class="background-white p20 mb30">
				<div class="page-title">
					<h1>Promotores del hotel</h1>
				</div>


				<div class="row">
					<section class="col-xs-12 table-comprobantes">
					
						<table  id="promotores" class="display" cellspacing="0" width="100%">
							<thead>
								<tr>
									<th></th>
									<th>Nombre</th>
									<th>Tel&eacute;fono</th>
									<th>Cargo</th>
									<th>Comisión Acumulada</th>
									<th>Activo</th>
								</tr>
							</thead>
							
							<tbody>
							
							</tbody>
						</table>

				</section>
				</div>

				<div class="row">
					<section class="col-xs-12 col-lg-12">
						<button class="btn btn-primary new-promotor" type="button" data-toggle="tooltip" title="Nuevo Promotor" data-placement="bottom" data-url="<?php echo HOST.'/Hotel/promotores/nuevopromotor' ?>"><i class="fa fa-users" style="margin:0px;"></i> Nuevo Promotor</button>
					</section>
				</div>
			</div>
</div>


<script>
	
	$(document).ready(function() {


		$('.new-promotor').on('click',function(e){

			var url = $(this).attr('data-url');
			$('.lista-promotores').slideUp('slow',function(){
				location.replace(url);
			});
		});

		var t = $('#promotores').DataTable( {
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
							d.peticion ='promotores';
						}
					},
					
					columns:[
								{data:'btn',width:'40px'},
						 		{data:'nombre'},
						 		{data:'telefono'},
						 		{data:'cargo'},
						 		{data:'comision'},
						 		{data:'activo'}
					 		],
			         language:{
			                        "lengthMenu": "Mostar _MENU_ registros por pagina",
			                        "info": "",
			                        "infoEmpty": "No se encontro ningun promotor",
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
			$('.btn').tooltip('enable');

			$('.activar').on('click',function(){
				var idpromotor = $(this).attr('data-id');

				$.confirm({
					title:'Confirm!',
					content:'Esta seguro de activar a este promotor?',
					buttons:{
						Si:function(){
							$.ajax({
								url: '/Hotel/controller/peticiones.php',
								type: 'POST',
								dataType: 'JSON',
								data: {peticion: 'activarpromotor',id:idpromotor},
							})
							.done(function(response) {
									if(response.peticion){
										$.alert('Promotor activado');
										t.ajax.reload(null,false);
									}else{
										$.alert('El promotor no se pudo activar intentelo mas tarde...');
									}
							});
							
						},
						No:function(){
							$.alert('Ok! lo puedes activar despues.');
						}
					}
				});
			});

				$('.desactivar').on('click',function(){
				var idpromotor = $(this).attr('data-id');

				$.confirm({
					title:'Confirm!',
					content:'Esta seguro de desactivar a este promotor?',
					buttons:{
						Si:function(){
							$.ajax({
								url: '/Hotel/controller/peticiones.php',
								type: 'POST',
								dataType: 'JSON',
								data: {peticion: 'desactivarpromotor',id:idpromotor},
							})
							.done(function(response) {
									if(response.peticion){
										$.alert('Promotor Desactivado');
										t.ajax.reload(null,false);
									}else{
										$.alert('El promotor no se pudo desactivar intentelo mas tarde...');
									}
							});
							
						},
						No:function(){
							$.alert('Ok! lo puedes desactivar despues.');
						}
					}
				});
			});

				$('.eliminar').on('click',function(){
				var idpromotor = $(this).attr('data-id');

				$.confirm({
					title:'Confirm!',
					content:'Esta seguro de eliminar a este promotor?',
					buttons:{
						Si:function(){
							$.ajax({
								url: '/Hotel/controller/peticiones.php',
								type: 'POST',
								dataType: 'JSON',
								data: {peticion: 'eliminarpromotor',id:idpromotor},
							})
							.done(function(response) {
									if(response.peticion){
										$.alert(response.mensaje);
										t.ajax.reload(null,false);
									}else{
										$.alert(response.mensaje);
									}
							});
							
						},
						No:function(){
							$.alert('Ok! lo puedes eliminar despues.');
						}
					}
				});
			});


		});
	});

</script>
<?php echo $footer = $includes->get_admin_footer(); ?>

