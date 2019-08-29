<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';

$con = new assets\libs\connection();

if(!$url = filter_input(INPUT_GET, 'url')){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$url = _safe($url);

$business = new assets\libs\publicBusinessProfile($con);

if(!$business->load_data($url)){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$business->increase_views();

$includes = new assets\libs\includes($con);
$properties['title'] = $business->get_name_unsafe().' | Negocio en Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); 

?>
<div class="main">
	<div class="main-inner">
		<div class="content">
			<div class="mt-80 mb80">

				<!-- header -->
				<div class="detail-banner" style="background-image: url(<?php echo HOST.'/assets/img/business/header/'.$business->get_photo();?>);">
					<div class="container">
						<div class="detail-banner-left">
							<div class="detail-banner-info">
								<div class="detail-label <?php echo $business->get_category_tag();?>"><?php echo $business->get_category();?></div>
								<div class="detail-verified"><?php echo $business->get_commission();?>%</div>
							</div>
							<h1 class="detail-title"><?php echo $business->get_name(); ?></h1>
							<h4 class="text-white"><?php echo $business->get_brief();?></h4>
							<div class="detail-banner-address">
								<i class="fa fa-map-o"></i> <?php echo $business->get_location();?>
							</div>
							<div class="detail-banner-rating">
								<?php echo $business->get_rating_stars($business->get_average_total_ratings()); ?>
							</div>
							<?php echo $business->get_claims();?>

							
								<?php 

								if ($business->get_btnreservacion()) {?>
									<div class="content-btn-reserva">
										
										<button class="detail-banner-btn btn-reserva" data-negocio="<?php echo $business->getIdnegocio();?>"><i class="fa fa-calendar-check-o"></i>Reservar</button>
									</div>
								<?php  }
									
								?>
							
						<script>
							$(document).ready(function() {

								var fechareserva = moment().format('YYYY-MM-DD');
								var diahoy = moment().day();
								var idnegocio  = "<?php echo $business->getIdnegocio() ?>";
								
								if(diahoy == 0){
									diahoy = 7;
								}

								$('#fechareservacion').on("dp.change",function(e){

										$('.btn-reservar').attr('disabled','disabled');

										var fecha = $('#fechareservacion').data("DateTimePicker").date();
										
										var dia = fecha.format("d");
										var fechareserva = fecha.format('YYYY-M-D');
										
										if(dia == 0){
										dia = 7;
										}else if(dia == 1){
										dia == 1; 
										}else if(dia == 2){
										dia == 2; 
										}else if(dia == 3){
										dia == 3; 
										}else if(dia == 4){
										dia == 4; 
										}else if(dia == 5){
										dia == 5; 
										}else if(dia == 6){
										dia == 6; 
										}

									cargar(fechareserva,dia);
								});

								$('.clear-reset').click(function(e) {
									/* Act on the event */
									e.preventDefault();

									$('input[name="totalperson"]').removeAttr('disabled');
									$('input[name="totalperson"]').val('1');

									$('input[name="fecha"]').val('');

									$('.btn-reservar').attr('disabled','disabled');

									var fechareserva = moment().format('YYYY-MM-DD');
									var diahoy = moment().day();
									cargar(fechareserva,diahoy);
								});


								function cargar(fechareserva = null,dia = null){
									
									if(fechareserva != null && dia !=null){
										fechareserva = fechareserva;
										diahoy = dia;
									}

									$.ajax({
										url: '/negocio/Controller/peticiones.php',
										type: 'post',
										dataType: 'JSON',
										data: {peticion: 'horasdisponibles',negocio:idnegocio,fecha:fechareserva,diareserva:diahoy,viewnegocio:true},
										})
										.done(function(response) {

											if($('.horas-reserva').length){
											var hora = response.data.hora;
											$('.contenedorbotones').remove();
											$('.horas-reserva').append('<div class="btn-group btn-group-toggle contenedorbotones" data-toggle="buttons"></div>');
											
											for (var clave in hora) {

												if(response.data.mesas[clave] > 0 ){

													$('.contenedorbotones').append('<label class="btn btn-danger horas" id="'+response.data.idhora[clave]+'" data-hora="'+response.data.hora[clave]+'" data-lugar="'+response.data.mesas[clave]+'" data-toggle="tooltip" title="'+response.data.mesas[clave]+' lugares disponibles" data-placement="bottom"><input type="checkbox"  name="hora"  id="'+response.data.hora[clave]+'"  autocomplete="off"/>'+response.data.hora[clave]+'</label>');
																	 // $("#"+response.data.idhora[clave]).tooltip('enabled');
												}
											
												$('.horas').on('mouseover',function(){
														$(this).tooltip('show');
													});
												}
										
										$('.contenedorbotones .horas').click(function(event) {
												event.preventDefault();
												$('.btn-reservar').attr('disabled', 'disabled');
															
												var cantidad = $(this).attr('data-lugar');
												var numperson  = parseInt($('input[name="totalperson"]').val());
												
												if(cantidad >= numperson){
															$('input[name="totalperson"]').attr('disabled', 'disabled');
																		
															$('.btn').removeClass('active');
																				
															$(this).addClass('active');

															$('.btn-reservar').removeAttr('disabled');
																
																			
																			// $('#cantidad').removeAttr('disabled');
																			// $('#cantidad').val('1');
																			// $('#cantidad').attr('max',cantidad);	
																			// $('#cantidad').change();
																			
																			
														$('input[name="fechaseleccionada"]').val(fechareserva);
														$('input[name="horaseleccionada"]').val($(this).attr('data-hora'));
																			
														$('.reservar').removeAttr('disabled');


														

												}else{
													$('.btn-reservar').attr('disabled', 'disabled');
														alert('No es posible seleccionar esta hora ya que el número de personas es mayor al disponible. Intente con otra hora si desea!');
													}
													});
													}
														
													})
												.fail(function() {
														console.log("error");
												})
												.always(function() {
													console.log("complete");
												});
													
												$('#modal-reserva').modal('show');
								}


								

$('form[name="reservar-user-preview"]').bind('submit',function(e){
																	
		$('.btn-reservar').attr('disabled','disabled');
		$('.btn-reservar').html('Reservando por favor espere...');

		if($('input[name="fechaseleccionada"]').val() == ''){

			$('input[name="fechaseleccionada"]').val(moment().format('YYYY-MM-DD'));
		
		}

		var formdata  = new FormData(document.getElementById('formulario-reserva'));

		formdata.append('peticion','reservar');
		formdata.append('totalperson', $('input[name="totalperson"]').val());
		formdata.append('negocio',idnegocio);
																	
			$.ajax({
					url: '/negocio/Controller/peticiones.php',
					type: 'POST',
					dataType: 'JSON',
					data:formdata,

					processData:false,
					contentType:false
					})
					.done(function(response) {
					if(response.peticion){

						enviarmensaje();
						alert(response.mensajes);
						$('input[name="totalperson"]').removeAttr('disabled');
						$('input[name="totalperson"]').val('1');
						$('input[name="fecha"]').val('');
						$('.btn-reservar').attr('disabled','disabled');
						$('.btn-reservar').html('RESERVAR | BOOK');
						$('#modal-reserva').modal('hide');

					}else{
						alert('No pudimos registrar tu reserva. te pedimos disculpa, pero lo puedes intentar mas tarde.');
					}

					});

				return false;
																	


		});

function enviarmensaje(){

	$.ajax({
		url: '/socio/controller/peticiones.php',
		type: 'POST',
		dataType: 'JSON',
		data: {peticion: 'enviarmensaje'},
	})
	.done(function(response) {
		if(response.peticion){
			return true;
		}else{
			return false;
		}
	})
	.fail(function() {
		return false;
	})
	.always(function() {
		console.log("complete");
	});
	
	// var data = {"apiKey": "407",
	// 		"country":"MX",
	// 		"dial":"26262",
	// 		"message":"PruebadereservacionesSMS",
	// 		"msisdns":['523221071814'],
	// 		"tag":'Quedescanses'
	// 	};

	// var header	= new Headers({
	// 		'Access-Control-Allow-Headers':'Origin,X-Requested-With,Content-Type,Accept',
	// 		'Access-Control-Allow-Origin':'https://api.broadcastermobile.com/brdcstr-endpoint-web/services/messaging/',
	// 		'Access-Control-Allow-Methods':'POST,OPTIONS,GET',
	// 		'Accept':'application/json',
	// 		"Content-Type":'application/json',
	// 		"Authorization":'CjqJxRd+vMYzPvcPuIK4c+3lTyo=',
			
	// 	});
	// // console.log(header.get('Content-Type'));
	// fetch("https://api.broadcastermobile.com/brdcstr-endpoint-web/services/messaging/",{
	// 	method:'POST',
	// 	headers:header,
	// 	body:JSON.stringify(data),
	// 	mode:'cors'
	// }).then(res => res.json())
	// .catch(error => console.log('error:',error))
	// .then(response => console.log('Success:',response));



	// $.ajax({
	// 	url: 'https://api.broadcastermobile.com/brdcstr-endpoint-web/services/messaging/',
	// 	method:'POST',
	// 	body: {apiKey: 407,
	// 		country:"MX",
	// 		dial:"26262",
	// 		message:"Prueba de reservaciones SMS",
	// 		msisdns:['523221071814'],
	// 		tag:'Prueba Travel Points',
	// 	},
	// 	headers:{
	// 		"Content-Type":'application/json',
	// 		"Authorization":'CjqJxRd+vMYzPvcPuIK4c+3lTyo='
	// 	},
	// 	cache:false,
	// 	// contentType:'text/plain',
	// 	processData:false
	// })
	// .done(function() {
	// 	console.log("success");
	// })
	// .fail(function() {
	// 	console.log("error");
	// })
	// .always(function() {
	// 	console.log("complete");
	// });
}
								

														
								$('.btn-reserva').on('click',function(){
									
									var negocio = $(this).attr('data-negocio');


									$.ajax({
										url: '/negocio/Controller/peticiones.php',
										type: 'POST',
										dataType: 'JSON',
										data: {peticion: 'consultarpublicacion',negocio:negocio},
									})
									.done(function(response) {

										if(response.peticion){
											if(response.status.condicion > 0){
												var iduser = "<?php echo $business->isUser()?>";
												
												if(iduser == 'not'){
													location.href = "<?php echo HOST.'/login' ?>";
												}else if(iduser == true){
													cargar(fechareserva,diahoy);
												}

											}else{
												alert('Este restaurant no tiene horas disponible activas, intentelo mas tarde');
											}
											
										}else{
											alert('Este restaurant no esta admintiendo por ahora, reservaciones.');
										}
										
									})
									.fail(function() {
										console.log("error");
									})
									.always(function() {
										console.log("complete");
									});
									


								});
							});
						</script>
						</div>
					</div>
				</div>

			</div>


			<div class="modal fade modal-reservacion" id="modal-reserva" data-backdrop="false" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
			<div class="modal-dialog modal-sm" role="document">
			<div class="modal-content">
			<div class="modal-header">
				<section>
					<h5 class="modal-title" id="exampleModalLongTitle">Reservar</h5>
					<h5 class="modal-title" id="exampleModalLongTitle">Make a reservation</h5>
				</section>
			
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
			</div>
				<form action="" method="POST" name="reservar-user-preview" id="formulario-reserva">
					<div class="modal-body content-modal-reserva">

					
							
					
						
						<div class="row num-person">
							<div class="form-group">
								<section class="form-label-block">
										<label for="cantidad">Personas</label>
								<label for="cantidad">Party size</label>
								</section>	
							
									
									<div class="input-group cant-personas">
										<input type="number" value="1" min="1" name="totalperson" step="1" id="cantidad" class="form-control" autofocus>
									</div>
							</div>
						</div>

						<div class="row calendar-reserva">
							<div class="form-group" id="fechadelareserva">	
										<div class="input-group date" id="fechareservacion">

											<input class="form-control" type="text" id="" name="fecha" value="" placeholder="Hoy | today"/>

											<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
										</div>
									</div>

									<script>
										$(document).ready(function() {
											
										$('#fechareservacion').datetimepicker({
															format:'LL',
															locale:'es',
															minDate:new Date()-1,
															useCurrent:false,
															// daysOfWeekDisabled:diasdisponibles(),
															});

										});
									</script>
							
						</div>
						
						<div class="row horas-reserva">
						
						</div>


						<div class="group-form">
							<label for="observacion">Observ.</label>
							<textarea class="form-control" name="observacion" ></textarea>
						</div>

					</div>
					<div class="modal-footer footer-modal-reserva">

					<button type="submit" class="btn btn-modal-reserva btn-reservar" disabled>RESERVAR | BOOK</button>

							<input type="hidden" name="fechaseleccionada">
							<input type="hidden" name="horaseleccionada">
						<a href="#" class="clear-reset form-control"> Limpiar | clear</a>
						<script>

						</script>
					</div>
			</form>
			</div>
			</div>
			</div>
			<div class="container">
				<?php echo $con->get_notify();?>
				<div class="row detail-content">
					<div class="col-sm-7">
						<?php echo $business->get_gallery();?>
						<!-- <h2>Nos ubicamos en</h2> -->
						<div class="background-white p20">
						<?php echo $business->get_map();?>
						</div>
						<h2>Certificados de regalo</h2>
						<?php echo $business->get_certificates();?>
						<?php echo $business->get_video();?>
						<h2 id="reviews">Opiniones sobre este negocio</h2>
						<?php echo $business->get_reviews();?>
					</div><!-- /.col-sm-7 -->
					<div class="col-sm-5">
						<div class="background-white p20">
							<div class="detail-overview-hearts" id="recommends">
								<i class="fa fa-heart"></i> <strong><?php echo $business->get_recommended();?></strong> personas lo recomiendan
							</div>
							<div class="detail-overview-rating">
								<i class="fa fa-star"></i> <?php echo $business->get_rating_header();?>
							</div>
						</div>
						
						<h2>Sobre <span class="text-secondary"><?php echo $business->get_name();?></span></h2>
						<div class="background-white p20">
							<div class="detail-vcard">
								<?php echo $business->get_logo();?>
								<div class="detail-contact">
									<?php echo $business->get_email();?>
									<?php echo $business->get_phone();?>
									<?php echo $business->get_website();?>
									<?php echo $business->get_address();?>
								</div>
							</div>
							<?php echo $business->get_description();?>
						</div>
						<?php echo $business->get_menu();?>
						<?php echo $business->get_schedule();?>
						<h2>Amenidades</h2>
						<div class="background-white p20">
							<ul class="detail-amenities">
								<?php echo $business->get_amenities();?>
							</ul>
						</div>
						<?php echo $business->get_credit_cards();?>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>
<?php echo $footer = $includes->get_main_footer(); ?>