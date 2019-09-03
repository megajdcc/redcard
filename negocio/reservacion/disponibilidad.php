<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 
$con = new assets\libs\connection();


use negocio\libs\includes;
use negocio\libs\Restaurant;


$restaurant  = new Restaurant($con);

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

$includes = new includes($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['pdf'])){
		
			
		}

	}
if($_SERVER["REQUEST_METHOD"] == "POST"){

	if(isset($_POST['date_start'])){
			
		}

}

$properties['title'] = 'Estado de Cuenta | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar();  
?>

<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
	
	
		<div class="background-white p20 mb30">

				<div class="page-title">
					<h1>Publicar Disponibilidad</h1>
				</div>


				<div class="row">
					<div class="col-lg-12">
						<label class="new-disponibilidad-label" data-toggle="tooltip" title="Si va asignar un Horario para un solo dia o para varios dias, seleccione los dias correspondientes. Almenos un dia debe estar seleccionado y mas de un espacio asignado para poder establecer la hora correspondiente al horario deseado." data-placement="bottom">Dias,horarios y espacios disponibles <strong class="fa fa-info-circle">*</strong></label>



						<!-- <div class="alert alert-dimisible alert-info"><label class="title">perfecto</label></div> -->
					</div>
				</div>

				<div class="row">
					<div class="col-lg-12 caja-new-disponibilidad">
						<table>
							<thead>
								<th>Nuevo Horario</th>
								<th>Espacios</th>
								<th>Lunes</th>
								<th>Martes</th>
								<th>Miercoles</th>
								<th>Jueves</th>
								<th>Viernes</th>
								<th>Sabado</th>
								<th>Domingo</th>
							</thead>

							<tbody>

								<script>

									function cargarnuevamente(){

											$.ajax({
											url: '/negocio/Controller/peticiones.php',
											type: 'POST',
											dataType: 'JSON',
											data: {peticion: 'listardisponibilidad'},
										})
										.done(function(response) {

											var dias = response.dias;
											var horario = new Array();
											var hora; 
											var valor ;
											
												for (var clave in dias) {
													var dia = clave;

													 horario.push(dias[clave]);

													 // alert(dias[clave].horas.hora);
													 // $('.'+dia).append('<strong class="disponibilidad-mesas-'+dia+'"></strong>');
												  if(dias[clave].horas.hora != null){

												  		
												  	 $('input[name="dias"]').each(function(e,v) {
											  		 valor = $(v).val();

													  

												  		if(valor==dia){
												  			
													  		if($('.dis-linea-'+dia).length){
													  			$('.dis-linea-'+dia).remove();
													  		}
													  		$(v).attr('checked', 'checked');
													  		$('.'+dia).append('<section class="dis-linea-'+dia+'"></section>');

													  		var horas = dias[clave].horas.hora;


													  		var mesas =0;

													  		for (var i = 0; i < horas.length; i++) {
													  			
													  			$('.dis-linea-'+dia).append('<button type="button" data-toggle="tooltip" title="Elimine esta hora si desea." class="remove-hora" data-idhorario="'+dias[clave].horas.id[i]+'"><i class="fa fa-trash"></i></button><strong class="hora">'+horas[i]+'</strong><input data-toggle="tooltip" title="Puedes aumentar o quitar las mesas o  espacios disponibles a esta hora en particular, el registro es instantaneo, no se necesita de ninguna otra acción." type="number" data-idhora="'+dias[clave].horas.id[i]+'" name="espacios-dis" class="form-control numeromesas" value="'+dias[clave].horas.mesas[i]+'" min="0">');
													  			mesas += dias[clave].horas.mesas[i];
													  		}
													  		$('.remove-hora').change();
													  		$('.disponibilidad-mesas-'+dia).text('Disponiblidad: '+mesas+' lugares');
													  		

													  		
													  		var changemesas = $('.numeromesas');

													  		changemesas.change(function(event) {


													  			var idhora = $(this).attr('data-idhora');


													  			$.ajax({
													  					url: '/negocio/Controller/peticiones.php',
																			type: 'POST',
																			dataType: 'JSON',
																			data: {peticion: 'numeromesas',id:idhora,mesas:$(this).val()},
													  			})
													  			.done(function(responses) {
													  					
													  				if(responses.peticion){
													  						cargarnuevamente();
													  					}

													  			})
													  			.fail(function() {
													  				console.log("error");
													  			})
													  			.always(function() {
													  				console.log("complete");
													  			});
													  			
													 
													  		}); 

													  		$('.remove-hora').click(function(){
										

											var id  = $(this).attr('data-idhorario');
												$.ajax({
													url: '/negocio/Controller/peticiones.php',
													type: 'POST',
													dataType: 'JSON',
													data: {peticion: 'removerhora',idhora:id},
												})
												.done(function(response) {
													if(response.peticion){
													location.reload();
													}
												})
												.fail(function() {
													console.log("error");
												})
												.always(function() {
													console.log("complete");
												});
												
											});
										
													  	}
													 	});

													 }else{
													 	$('.disponibilidad-mesas-'+dia).text('Cerrado');
													 }
												}
											
										})
										.fail(function() {
											console.log("error");
										})
										.always(function() {
											console.log("complete");
										});



									}


									$(document).ready(function() {

										// chequear cuales dias estan diposnible..
										// 
										// 
										// 
										
										$.ajax({
											url: '/negocio/Controller/peticiones.php',
											type: 'POST',
											dataType: 'JSON',
											data: {peticion: 'listardisponibilidad'},
										})
										.done(function(response) {




											var dias = response.dias;
											var horario = new Array();
											var hora; 
											var valor ;
											
												for (var clave in dias) {
													var dia = clave;

													 horario.push(dias[clave]);

													 // alert(dias[clave].horas.hora);
													 // $('.'+dia).append('<strong class="disponibilidad-mesas-'+dia+'"></strong>');
												  if(dias[clave].horas.hora != null){

												  		
												  	 $('input[name="dias"]').each(function(e,v) {
											  		 valor = $(v).val();

													  

												  		if(valor==dia){
												  			
													  			if($('.dis-linea-'+dia).length){
													  			$('.dis-linea-'+dia).remove();
													  		}
													  		$(v).attr('checked', 'checked');
													  		$('.'+dia).append('<section class="dis-linea-'+dia+'"></section>');

													  		var horas = dias[clave].horas.hora;


													  		var mesas =0;

													  		for (var i = 0; i < horas.length; i++) {
													  			
													  			$('.dis-linea-'+dia).append('<div class="content-hora"><button type="button" data-toggle="tooltip" title="Elimine esta hora si desea." class="remove-hora" data-idhorario="'+dias[clave].horas.id[i]+'"><i class="fa fa-trash"></i></button><strong class="hora">'+horas[i]+'</strong><input type="number" data-idhora="'+dias[clave].horas.id[i]+'" name="espacios-dis" class="form-control numeromesas" value="'+dias[clave].horas.mesas[i]+'" min="0"></div>');
													  			mesas = mesas + dias[clave].horas.mesas[i];
													  		}
													  		$('.remove-hora').change();
													  		$('.disponibilidad-mesas-'+dia).text('Disponiblidad: '+mesas+' lugares');
													  		

													  		
													  		var changemesas = $('.numeromesas');

													  		changemesas.change(function(event) {


													  			var idhora = $(this).attr('data-idhora');


													  			$.ajax({
													  					url: '/negocio/Controller/peticiones.php',
																			type: 'POST',
																			dataType: 'JSON',
																			data: {peticion: 'numeromesas',id:idhora,mesas:$(this).val()},
													  			})
													  			.done(function(responses) {
													  					
													  					if(responses.peticion){
													  						cargarnuevamente();
													  					}

													  			})
													  			.fail(function() {
													  				console.log("error");
													  			})
													  			.always(function() {
													  				console.log("complete");
													  			});
													  			
													 
													  		}); 

													  		$('.remove-hora').click(function(){
										

											var id  = $(this).attr('data-idhorario');
												$.ajax({
													url: '/negocio/Controller/peticiones.php',
													type: 'POST',
													dataType: 'JSON',
													data: {peticion: 'removerhora',idhora:id},
												})
												.done(function(response) {
													if(response.peticion){
													location.reload();
													}
												})
												.fail(function() {
													console.log("error");
												})
												.always(function() {
													console.log("complete");
												});
												
										});
										
													  	}
													 	});

													 }else{
													 	$('.disponibilidad-mesas-'+dia).text('Cerrado');
													 }
												}
										
											
										})
										.fail(function() {
											console.log("error");
										})
										.always(function() {
											console.log("complete");
										});
										
										
									


										var cantidad =0;

										$('input[name="dias"]').click(function(event) {
											 
											 // $('input[name="dias"]:checked').each(function(){
													cantidad = $('input[name="dias"]:checked').length;
											 // }
											
											
											if(cantidad > 0){
												$('.new-horario').removeAttr('disabled');
											}else{
													$('.new-horario').attr('disabled','disabled');
											}
										});


										$('.new-horario').click(function(event) {
											/* Act on the event */

											var espacios = $('input[name="espacios"]').val();



											if(espacios > 0 ){
												$('#time-horario').modal('show');	

											

											}else{
												alert('Asigne los espacios que va a permitir en reserva.');
												return false;
											}
											

										});


										

										$('.establecer').click(function(event){


												var mesas = $('input[name="espacios"]').val();
												var hora = $('input[name="horario"]').val();
												var dia = new Array();



												if(hora.length  == 0 ){
													alert("Establesca la hora de la disponiblidad");
													return false;
												}

												$('input[name="dias"]:checked').each(function(){
														
															dia.push($(this).val());
														
												});

												// alert(dia.join());


												$.ajax({
													url: '/negocio/Controller/peticiones.php',
													type: 'POST',
													dataType: 'JSON',
													data: {peticion: 'asignarhora',hora:hora,mesas:mesas,dia:dia},
												})
												.done(function(response) {

													if(response.peticion){
														cargarnuevamente();
														$('#time-horario').modal('hide');	
													}
													console.log("success");
												})
												.fail(function() {
													console.log("error");
												})
												.always(function() {
													console.log("complete");
												});
										
									});

										$('.publicar').click(function(event) {
											/* Act on the event */


											$.ajax({
													url: '/negocio/Controller/peticiones.php',
													type: 'POST',
													dataType: 'JSON',
													data: {peticion: 'publicar'},
											})
											.done(function(response) {

												if(response.peticion){
													alert('Este horario ya se encuentra en Linea');
													location.reload();

												}
												console.log("success");
											})
											.fail(function() {
												console.log("error");
											})
											.always(function() {
												console.log("complete");
											});
											
										});

										$('.desactivar').click(function(event) {
											/* Act on the event */


											$.ajax({
													url: '/negocio/Controller/peticiones.php',
													type: 'POST',
													dataType: 'JSON',
													data: {peticion: 'desactivar'},
											})
											.done(function(response) {

												if(response.peticion){
													alert('Horario fuera de linea.');
													location.reload();

												}
												console.log("success");
											})
											.fail(function() {
												console.log("error");
											})
											.always(function() {
												console.log("complete");
											});
											
										});



										$.ajax({
											url: '/negocio/Controller/peticiones.php',
													type: 'POST',
													dataType: 'JSON',
													data: {peticion: 'consultarpublicacion'},
										})
										.done(function(response) {
												if(response.peticion){
													if(response.status.condicion == 0){
														$('.desactivar').attr('disabled','disabled');
														$('.publicar').attr('data-toggle', 'tooltip');
														$('.publicar').attr('title', 'publicar');


													}else{
														$('.publicar').attr('disabled','disabled');
													}
												}
										})
										.fail(function() {
											console.log("error");
										})
										.always(function() {
											console.log("complete");
										});
										


							
									});

								




								</script>
								<tr>
									<td><button class="btn btn-danger new-horario" type="button" data-toggle="tooltip" title="Si va asignar un Horario para un solo dia o para varios dias, seleccione los dias correspondiente, de lo contrario solo seleccione los dias que usted desee." data-placement="bottom"><i class="fa fa-plus-circle"></i></button></td>
									<td><input type="number" step="1" max="2000" min="0" value="0" name="espacios" class="form-control" min="0"></td>
									<td><input type="checkbox" name="dias" value="lunes" class="" ></td>
									<td><input type="checkbox" name="dias" value="martes" class=""></td>
									<td><input type="checkbox" name="dias" value="miercoles" class=""></td>
									<td><input type="checkbox" name="dias" value="jueves" class=""></td>
									<td><input type="checkbox" name="dias" value="viernes" class=""></td>
									<td><input type="checkbox" name="dias" value="sabado" class=""></td>
									<td><input type="checkbox" name="dias" value="domingo" class=""></td>
								</tr>
							</tbody>
						</table>

				

					<div class="disponibilidad">

						<section class="lunes">
							<label class="title-dia">Lunes</label><strong class="disponibilidad-mesas-lunes"></strong>
					
						</section>

						<section class="martes">
							<label class="title-dia">Martes</label><strong class="disponibilidad-mesas-martes"></strong>
						</section>

						<section class="miercoles">
							<label class="title-dia">Miercoles</label><strong class="disponibilidad-mesas-miercoles"></strong>
						</section>

						<section class="jueves">
							<label class="title-dia">Jueves</label><strong class="disponibilidad-mesas-jueves"></strong>
						</section>

						<section class="viernes">
							<label class="title-dia">Viernes</label><strong class="disponibilidad-mesas-viernes"></strong>
						</section>

						<section class="sabado">
							<label class="title-dia">Sabado</label><strong class="disponibilidad-mesas-sabado"></strong>
						</section>

						<section class="domingo">
							<label class="title-dia">Domingo</label><strong class="disponibilidad-mesas-domingo"></strong>
						</section>
							<?php $restaurant->getDisponibilidad(); ?>
					</div>

			

					
					<div class="botones-registro">
						<button class="publicar btn btn-success" name="publicar" type="button"><i class="fa fa-save"></i>Publicar</button>
						<button class="desactivar btn btn-danger" name="desactivar" type="button"><i class="fa fa-close"></i>Desactivar</button>
					</div>
					
			






						
					</div>
				</div>


		</div>
			

	</div>
</div>


<!-- MODAL PARA ADJUDICAR HORARIO TIME BOOTSTRAP  -->

<div class="modal fade" id="time-horario" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Establesca las horas para este nuevo horario</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
       
        <div class="form-group">
                <div class='input-group date' id='datetimepicker3'>
                		<label for="horario" class="input-group-addon">Horario:</label>
                    <input type='text' class="form-control" id="horario" name="horario" placeholder="Asigne la hora" />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-time"></span>
                    </span>
                </div>
								<script type="text/javascript">
										$(function () {
											$('#datetimepicker3').datetimepicker({
												format: 'LT',

											});
										});
								</script>
            </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary establecer">Establecer</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
<?php echo $includes->get_footer() ?>