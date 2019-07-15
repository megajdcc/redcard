
$(document).ready(function() {
	'use strict';


	$('body').fadeIn(2000);
	$('img').fadeIn(2000);
			
	// $('.owl-carousel').owlCarousel();
	$('.owl-carousel').owlCarousel({
		loop:true,
		margin:10,
		items:3,
		autoplay:true,
		autoplayTimeout:1500,
		autoplayHoverPause:true
	})

	$('.owl-carousel1').owlCarousel({
		loop:true,
		margin:10,
		items:1,
		autoplay:true,
		autoplayTimeout:3000,
		autoplayHoverPause:false
	})

	// Deshabilitar boton de registro si no aceptan los ToS.
	$('#tos-check').click(function(){
		if($(this).is(":checked")){
			$('#register-btn').removeAttr('disabled');
		}else{
			$('#register-btn').attr('disabled', 'disabled');
		}
	});

	// Al cambiar el pais, se cargan los nuevos estados y se limpia el select ciudades.
	$('#country-select').on('change', function(){
		
		$('#city-select').empty();
		$('#city-select').selectpicker('refresh');
		load_states(this.value);
	});

	$('#country-select-franquiciatario').on('change', function(){
		
		$('#city-select-franquiciatario').empty();
		$('#city-select-franquiciatario').selectpicker('refresh');
		load_states_franquiciatario(this.value);
	});

	$('#country-select-referidor').on('change', function(){
		
		$('#city-select-referidor').empty();
		$('#city-select-referidor').selectpicker('refresh');
		load_states_referidor(this.value);
	});



	// Al cambiar el pais, se cargan los nuevos estados y se limpia el select ciudades.
	$('#country-select-affiliate').on('change', function(){
		
		$('#city-select-affiliate').empty();
		$('#city-select-affiliate').selectpicker('refresh');
		load_states_affiliate(this.value);
	});

	// Al cambiar el estado, se cargan las nuevas ciudades
	$('#state-select').on('change', function(){
		load_cities(this.value);
	});

	// Al cambiar el estado, se cargan las nuevas ciudades
	$('#state-select-affiliate').on('change', function(){
		load_cities_affiliate(this.value);
	});

	$('#state-select-franquiciatario').on('change', function(){
		load_cities_franquiciatario(this.value);
	});

	$('#state-select-referidor').on('change', function(){
		load_cities_referidor(this.value);
	});



	// Funcion para cargar estados
	var load_states = function(id){
		var id_pais = id;
		$.ajax({
			type: "POST",
			url: "/ajax.php",
			data: {
				id_pais: id_pais
			},
			dataType: 'json',
			success: function(data){
				$('#state-select').empty();
				for(var i in data){
					$('#state-select').append('<option value="' + data[i].id_estado + '">' + data[i].estado + '</option>');
				}
				$('#state-select').selectpicker('refresh');
			}
		});
	}

		// Funcion para cargar estados
	var load_states_affiliate = function(id){
		var id_pais = id;
		$.ajax({
			type: "POST",
			url: "/ajax.php",
			data: {
				id_pais: id_pais
			},
			dataType: 'json',
			success: function(data){
				$('#state-select-affiliate').empty();
				for(var i in data){
					$('#state-select-affiliate').append('<option value="' + data[i].id_estado + '">' + data[i].estado + '</option>');
				}
				$('#state-select-affiliate').selectpicker('refresh');
			}
		});
	}

	var load_states_franquiciatario = function(id){
		var id_pais = id;
		$.ajax({
			type: "POST",
			url: "/ajax.php",
			data: {
				id_pais: id_pais
			},
			dataType: 'json',
			success: function(data){
				$('#state-select-franquiciatario').empty();
				for(var i in data){
					$('#state-select-franquiciatario').append('<option value="' + data[i].id_estado + '">' + data[i].estado + '</option>');
				}
				$('#state-select-franquiciatario').selectpicker('refresh');
			}
		});
	}

	var load_states_referidor = function(id){
		var id_pais = id;
		$.ajax({
			type: "POST",
			url: "/ajax.php",
			data: {
				id_pais: id_pais
			},
			dataType: 'json',
			success: function(data){
				$('#state-select-referidor').empty();
				for(var i in data){
					$('#state-select-referidor').append('<option value="' + data[i].id_estado + '">' + data[i].estado + '</option>');
				}
				$('#state-select-referidor').selectpicker('refresh');
			}
		});
	}


	// Funcion para cargar ciudades
	var load_cities = function(id){
		var id_estado = id;
		$.ajax({
			type: "POST",
			url: "/ajax.php",
			data: {
				id_estado: id_estado
			},
			dataType: 'json',
			success: function(data){
				$('#city-select').empty();
				for(var i in data){
					$('#city-select').append('<option value="' + data[i].id_ciudad + '">' + data[i].ciudad + '</option>');
				}
				$('#city-select').selectpicker('refresh');
			}
		});
	}

	var load_cities_affiliate = function(id){
		var id_estado = id;
		$.ajax({
			type: "POST",
			url: "/ajax.php",
			data: {
				id_estado: id_estado
			},
			dataType: 'json',
			success: function(data){
				$('#city-select-affiliate').empty();
				for(var i in data){
					$('#city-select-affiliate').append('<option value="' + data[i].id_ciudad + '">' + data[i].ciudad + '</option>');
				}
				$('#city-select-affiliate').selectpicker('refresh');
			}
		});
	}

	var load_cities_franquiciatario = function(id){
		var id_estado = id;
		$.ajax({
			type: "POST",
			url: "/ajax.php",
			data: {
				id_estado: id_estado
			},
			dataType: 'json',
			success: function(data){
				$('#city-select-franquiciatario').empty();
				for(var i in data){
					$('#city-select-franquiciatario').append('<option value="' + data[i].id_ciudad + '">' + data[i].ciudad + '</option>');
				}
				$('#city-select-franquiciatario').selectpicker('refresh');
			}
		});
	}

	var load_cities_referidor = function(id){
		var id_estado = id;
		$.ajax({
			type: "POST",
			url: "/ajax.php",
			data: {
				id_estado: id_estado
			},
			dataType: 'json',
			success: function(data){
				$('#city-select-referidor').empty();
				for(var i in data){
					$('#city-select-referidor').append('<option value="' + data[i].id_ciudad + '">' + data[i].ciudad + '</option>');
				}
				$('#city-select-referidor').selectpicker('refresh');
			}
		});
	}


	


	// Confirmar aceptar o rechazar una solicitud de perfil
	

		$('#corregirsolicitud').on('click', function(){
			return confirm('¿Realmente desea enviar a corrección esta solicitud?');
		});
		$('#rechazarsolicitud').on('click', function(){
			return confirm('¿Realmente desea rechazar esta solicitud?');
		});
		$('#eliminarsolicitud').on('click', function(){
			return confirm('¿Realmente desea eliminar esta solicitud? Una vez hecho, no se puede deshacer.');
		});

	// Confirmar aceptar o rechazar una solicitud

	$('#check-request').on('click', function(){
		return confirm('¿Realmente desea enviar a corrección esta solicitud?');
	});
	$('#reject-request').on('click', function(){
		return confirm('¿Realmente desea rechazar esta solicitud?');
	});
	$('#delete-request').on('click', function(){
		return confirm('¿Realmente desea eliminar esta solicitud? Una vez hecho, no se puede deshacer.');
	});
	$('.user-ban').on('click', function(){
		return confirm('¿Realmente desea cambiar el status de este usuario?');
	});
	$('.delete-product').on('click', function(){
		return confirm('¿Realmente desea eliminar este producto permanentemente?');
	});


	$('.reservar').on('click',function(){
		return confirm('¿Esta seguro de publicar esta reservación ?');
	});
	$('.buy-product').on('click', function(){
		var name = $('#product-name').text();
		var price = $('#product-price').text();
		return confirm('Confirmar compra de ' + name + ' por ' + price + ' Travel Points.');
	});
	$('.send-product').on('click', function(){
		return confirm('¿Desea marcar esta venta como entregada?');
	});

	// Confirmar cambiar el correo electrónico
	$('#change-email').on('click', function(){
		return confirm('¿Cambiar correo a ' + $('#email').val() + '? \n\nAdvertencia\nSi cambias tu correo electrónico, tu sesión se cerrará y deberás confirmar tu nueva dirección de correo electrónico para entrar a tu cuenta.\nEnviaremos un correo de confirmación a tu nuevo correo electrónico.');
	});
	//DateTimePicker de Birthday
	$(function(){
		$('#user-birthdate').datetimepicker({
			viewMode: 'years',
			icons: {
				time: "fa fa-clock-o",
				date: "fa fa-calendar",
				up: "fa fa-chevron-up",
				down: "fa fa-chevron-down"
			},
			useCurrent: false,
			locale: 'es',
			format: 'DD/MM/YYYY',
			allowInputToggle: true
		});
	});

	// Imagen del perfil de usuario
	$('#profile-picture-click').on('click', function(e) {
		e.preventDefault();
		$("#profile-photo-input").trigger('click');
	});
	$('#profile-photo-input').change(function() {
		$(this).closest("form").submit();
	});

	// Buscador de usuarios
	var users = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.obj.whitespace('username'),
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: '/ajax.php?referral=%QUERY',
			wildcard: '%QUERY'
		}
	});

// buscador de hoteles
var hoteles = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.obj.whitespace('username'),
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: '/ajax.php?hotel=%QUERY',
			wildcard: '%QUERY'
		}
	});
	
$('#user-search-hotel .typeahead').typeahead({
			hint: false,
			highlight: true,
			minLength: 3,
			autoselect: true,
		},
		{
		limit: 15,
		name: 'users',
		display: 'username',

		source: hoteles,
		templates: {
			empty: [
				'<div class="tt-empty-message">',
					'Sé más específico.',
				'</div>'
			].join('\n'),
			suggestion: function(data){
				return '<div><img src="/assets/img/user_profile/' + data.imagen + '" class="meta-img img-rounded" alt=""><strong>' + data.display + '</strong> @' + data.username + '     |    <img src="/assets/img/hoteles/' + data.imghotel + '" class="meta-img img-rounded" alt=""><strong>' + data.displayhotel + '</strong></div>';
			}
		}
	}).on('typeahead:selected', function(object, data) {
		if($('#user-search-placeholder').length){
			$('#user-search-placeholder').empty();
			$('#user-search-placeholder').append('<div><img src="/assets/img/user_profile/' + data.imagen + '" class="meta-img img-rounded" alt=""><strong>' + data.display + '</strong> @' + data.username + '    | <img src="/assets/img/hoteles/' + data.imghotel + '" class="meta-img img-rounded" alt=""><strong>' + data.displayhotel + '</strong></div>');
		}
		
	});

// BUSQUEDA DE HOTEL DE HOSPEDAJE USUARIO SOCIO>
// 
	var hoteleshospedados = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.obj.whitespace('hotel'),
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: '/ajax.php?hoteleshospedados=%QUERY',
			wildcard: '%QUERY'
		}
	});
	var busquedahotel = $('#busquedad-hotel-hospedado .hospedado').typeahead({
			hint: false,
			highlight: true,
			minLength: 2,
			autoselect: true,
		},
		{
		limit:20,
		name: 'hotel',
		display: 'nombrehotel',
		source: hoteleshospedados,
		templates: {
			empty: [
				'<div class="tt-empty-message">',
					'Sé más específico, con su busqueda, si no consigue su hotel escribala igual.',
				'</div>'
			].join('\n'),
			suggestion: function(data){
				return '<div><img src="/assets/img/hoteles/' + data.imghotel + '" class="meta-img img-rounded"><strong>' + data.displayhotel + '</strong></div>';
			}
		}
	}).on('typeahead:select', function(object, data) {

		if($('#hotel-search-placeholder').length){
			$('#hotel-search-placeholder').empty();
			$('#hotel-search-placeholder').append('<div><img src="/assets/img/hoteles/' + data.imghotel + '" class="meta-img img-rounded" alt=""><strong>' + data.displayhotel + '</strong></div>');
			$('input[name="hotel"]').val(data.displayhotel);
			$('input[name="idhotel"]').val(data.id);
		}
				
	});


	busquedahotel.on('typeahead:idle',function(ev,data){
		
	});


	if($('#hotel-search-placeholder').length){



	}

	$('#user-search-reservacion .typeahead').typeahead({
			hint: false,
			highlight: true,
			minLength: 1,
			autoselect: true,
		},
		{
		limit: 20,
		name: 'users',
		display: 'username',
		source: users,
		templates: {
			empty: [
				'<div class="tt-empty-message">',
					'El usuario no esta afiliado hagalo ahora <button type="button" class="btn btn-warning afiliarnew" data-toggle="modal" data-target="#modal-afiliar-new-usuario-reservacion">Afiliar</button>',
				'</div>'
			].join('\n'),
			suggestion: function(data){
				return '<div><img src="/assets/img/user_profile/' + data.imagen + '" class="meta-img img-rounded" alt=""><strong>' + data.display + '</strong></div>';
			}
		}
	}).on('typeahead:selected', function(object, data) {

		if($('#user-search-placeholder').length){
			 $('#user-search-placeholder').empty();
			 $('#user-search-placeholder').append('<div><img src="/assets/img/user_profile/' + data.imagen + '" class="meta-img img-rounded" alt=""><strong>' + data.display + '</strong> @' + data.username + '</div>');
		}
	});




	// Buscador de restaurantes...
	// 
	
	var restaurant = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.obj.whitespace('nombre'),
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: '/ajax.php?restaurantes=%QUERY',
			wildcard: '%QUERY'
		}
	});
		
	$('#user-search-reservacion-negocios .complete').typeahead(


// Opciones de configuracion...
		{
			hint: false,
			highlight: true,
			minLength: 2,
			autoselect: true,
		},
// conjunto de datos... 
		{
		limit: 15,
		name: 'datos',
		display: 'nombre',
		source: restaurant,
		templates: {
			empty: [
				'<div class="tt-empty-message">',
					'Sin registro, sea mas especifico',
				'</div>'
			].join('\n'),
			suggestion: function(data){
				return '<div><img src="/assets/img/business/header/' + data.imagen + '" class="meta-img img-rounded" alt=""><strong>' + data.display + '</strong> @' + data.nombre + '</div>';
			}
		}
	}).on('typeahead:selected', function(object, data) {
		


		if($('.horas').length){
			$('.horas').remove();
		}

		if($('#fechadelareserva').length){
			$('#fechadelareserva').attr('title','Seleccione la fecha solicitada, si el campo no se puede seleccionar es debido a que el negocio no tiene horas disponibles para ese dia de la semana en particular.');
			
		}


		if($('#user-search-placeholder-reservacion').length){

					$('.reservar').attr('disabled','disabled');
						$('#cantidad').attr('disabled', 'disabled');

							var negocio = $('input[name="restaurantes"]').val();
							$.ajax({
									url: '/negocio/Controller/peticiones.php',
									type: 'POST',
									dataType: 'JSON',
									data: {peticion: 'diasdisponibles',restaurant:negocio},
									cache:false,
									})
									.done(function(response) {
											if(response.peticion){
												$('#fechareservacion').data("DateTimePicker").destroy();
													$('#fechareservacion').datetimepicker({
													format:'LL',
													locale:'es',
													minDate:new Date()-1,
													useCurrent:false,
													 daysOfWeekDisabled:response.data,
													});
											

											$('#fechareservacion').data("DateTimePicker").enable();


												// $('#fechareservacion').data("DateTimePicker").options({
												// 		daysOfWeekDisabled:response.data
												// 	});

												var dia = response.data;
												var existe = false;
												var tamano = 0;
												for(var clave in dia){
													tamano++;
												}
												if($('.sinexistencia').length){
														$('.sinexistencia').remove();
													}

												// alert(tamano);
												if(tamano == 7){
													$('.horas-reserva').append('<div class="alert sinexistencia alert-danger"><h3>Sin disponibilidad</h3></div>');
													$('input[name="fecha"]').attr('disabled','disabled');
												}else{
													$('input[name="fecha"]').removeAttr('disabled');
												}
												// return response.data;
											}else{
												alert('Este restaurant, no tiene ningún dia disponible.');
												// return [];
											}
										})
										.fail(function() {
											console.log("error");
										})
										.always(function() {
											console.log("complete");
										});

					$('#fechareservacion').on("dp.change",function(e){

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

						// alert(dia);s
						
						var negocio = $('input[name="restaurantes"]').val();
						
						$.ajax({
							url: '/negocio/Controller/peticiones.php',
							type: 'POST',
							dataType: 'JSON',
							data: {peticion: 'horasdisponibles',negocio:negocio,diareserva:dia,fecha:fechareserva},
						})
						.done(function(response) {
							if(response.peticion){
								if($('.horas-reserva').length){
									var hora = response.data.hora;
									// if($('.horas').length){
									// 	$('.horas').remove();
									// }
									
									if($('.contenedorbotones').length){
										$('.contenedorbotones').remove();
									}

									$('.horas-reserva').append('<div class="btn-group btn-group-toggle contenedorbotones" data-toggle="buttons"></div>');
									for (var clave in hora) {
										if(response.data.mesas[clave] > 0 ){
											$('.contenedorbotones').append('<label class="btn btn-danger horas" id="'+response.data.idhora[clave]+'" data-hora="'+response.data.hora[clave]+'" data-lugar="'+response.data.mesas[clave]+'" data-toggle="tooltip" title="'+response.data.mesas[clave]+' lugares disponibles" data-placement="bottom"><input type="checkbox"  name="hora"  id="'+response.data.hora[clave]+'"  autocomplete="off"/>'+response.data.hora[clave]+'</label>');
											 // $("#"+response.data.idhora[clave]).tooltip('enabled');
										}										
									}
									$('#cantidad').attr('disabled', 'disabled');

									

									$('.contenedorbotones .btn').click(function(event) {


										$('.btn').removeClass('active');
										/* Act on the event */

										$(this).addClass('active');
										var cantidad = $(this).attr('data-lugar');

										$('#cantidad').removeAttr('disabled');
										$('#cantidad').val('1');
										$('#cantidad').attr('max',cantidad);	
										$('#cantidad').change();


										$('input[name="fechaseleccionada"]').val(fechareserva);
										$('input[name="horaseleccionada"]').val($(this).attr('data-hora'));

										$('.reservar').removeAttr('disabled');

									});

									$('.horas').on('mouseover',function(){
										$(this).tooltip('show');
									});



								
									 
								}
							}
								
						})
						.fail(function() {
							console.log("error");
						})
						.always(function() {
							console.log("complete");
						});
						

						
					})
					;
																
								

			 $('#user-search-placeholder-reservacion').empty();
			 $('#user-search-placeholder-reservacion').append('<div><img class="img-reservacion-selected rest"  data-idrestaurant="'+data.id_negocio+'" src="/assets/img/business/header/' + data.imagen + '" class="meta-img img-rounded" alt=""></div>');
				$('.rest').change();
				$('.rest').css('cursor', 'pointer');
				$('.rest').click(function(e){
					var idrest  = $(this).attr('data-idrestaurant');

					$.ajax({
						url: '/Hotel/controller/peticiones.php',
						type: 'POST',
						dataType: 'JSON',
						data: {peticion: 'datosrestaurant',negocio:idrest},
					})
					.done(function(response) {
						if(response.peticion){
							$('#modal-datos-restaurant input[name="restaurant"]').val(response.data[0].negocio);
							$('#modal-datos-restaurant input[name="telefonorestaurant"]').val(response.data[0].telefono);
							$('#modal-datos-restaurant textarea[name="direccion"]').val(response.data[0].direccion);
							$('#modal-datos-restaurant').modal('show');
						}
					})
					.fail(function() {
						console.log("error");
					})
					.always(function() {
						console.log("complete");
					});
					
					
				});
			
			$('input[name="negocio"]').val(data.id_negocio);

		}
	
	});


	$('#user-search .typeahead').typeahead({
			hint: false,
			highlight: true,
			minLength: 2,
			autoselect: true,
		},
		{
		limit: 15,
		name: 'users',
		display: 'username',
		source: users,
		templates: {
			empty: [
				'<div class="tt-empty-message">',
					'Sé más específico.',
				'</div>'
			].join('\n'),
			suggestion: function(data){
				return '<div><img src="/assets/img/user_profile/' + data.imagen + '" class="meta-img img-rounded" alt=""><strong>' + data.display + '</strong> @' + data.username + '</div>';
			}
		}
	}).on('typeahead:selected', function(object, data) {
		if($('#user-search-placeholder').length){
			 $('#user-search-placeholder').empty();
			 $('#user-search-placeholder').append('<div><img src="/assets/img/user_profile/' + data.imagen + '" class="meta-img img-rounded" alt=""><strong>' + data.display + '</strong> @' + data.username + '</div>');
		}
		if($('#certificate-load').length){
			load_user_certs();
		}
	});

	var placeholder_clone = $("#user-search-placeholder").clone();
	var certificate_clone = $("#certificate-load").clone();
	$("#user-search-input").on("input", function() {
		$("#user-search-placeholder").replaceWith(placeholder_clone.clone());
		$("#certificate-load").replaceWith(certificate_clone.clone());
	});

	if($('#user-search-input').length > 1 && $('#user-search-placeholder').length){
		var username = $('#user-search-input').val();
	
		$.ajax({
			type: "POST",
			url: "/ajax.php",
			data: {
				load_username: username
			},
			success: function(data){
				
				$('#user-search-placeholder').empty();

				$('#user-search-placeholder').append(data);
			}
		});
	}



	
	$('#user-search-input-referidor').typeahead({
			hint: false,
			highlight: true,
			minLength: 3,
			autoselect: true,
		},
		{
		limit: 15,
		name: 'users',
		display: 'username',

		source: users,
		templates: {
			empty: [
				'<div class="tt-empty-message">',
					'Sé más específico.',
				'</div>'
			].join('\n'),
			suggestion: function(data){
				return '<div><img src="/assets/img/user_profile/' + data.imagen + '" class="meta-img img-rounded" alt=""><strong>' + data.display + '</strong> @' + data.username + '</div>';
			}
		}
	}).on('typeahead:selected', function(object, data) {
		if($('#user-search-placeholder-referidor').length){
			$('#user-search-placeholder-referidor').empty();
			$('#user-search-placeholder-referidor').append('<div><img src="/assets/img/user_profile/' + data.imagen + '" class="meta-img img-rounded" alt=""><strong>' + data.display + '</strong> @' + data.username + '</div>');
		}
		if($('#certificate-load').length){
			load_user_certs_referidor();
		}
	});

	// var placeholder_clone = $("#user-search-placeholder-referidor").clone();
	// var certificate_clone = $("#certificate-load").clone();
	// $("#user-search-input-referidor").on("input", function() {
	// 	$("#user-search-placeholder-referidor").replaceWith(placeholder_clone.clone());
	// 	$("#certificate-load").replaceWith(certificate_clone.clone());
	// });

	// if($('#user-search-input-referidor').val() != '' && $('#user-search-placeholder-referidor').length){
	// 	var username = $('#user-search-input-referidor').val();
	// 	$.ajax({
	// 		type: "POST",
	// 		url: "/ajax.php",
	// 		data: {
	// 			load_username: username
	// 		},
	// 		success: function(data){
	// 			$('#user-search-placeholder-referidor').empty();
	// 			$('#user-search-placeholder-referidor').append( data );
	// 		}
	// 	});
	// }

	// Cargar certificados pendiente en formulario de venta
	function load_user_certs(){
		var username = $('#user-search-input').val();
		$.ajax({
			type: "POST",
			url: "/ajax.php",
			data: {
				cert_username: username
			},
			success: function(data){
				$( "#certificate-load" ).empty();
				$( "#certificate-load" ).append( data );
			}
		});
		return;
	}

	function load_user_certs_referidor(){
		var username = $('#user-search-input-referidor').val();
		$.ajax({
			type: "POST",
			url: "/ajax.php",
			data: {
				cert_username: username
			},
			success: function(data){
				$( "#certificate-load" ).empty();
				$( "#certificate-load" ).append( data );
			}
		});
		return;
	}

	// Buscador de usuarios
	var businesses = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.obj.whitespace('url'),
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: '/ajax.php?business=%QUERY',
			wildcard: '%QUERY'
		}
	});

	$('#business-search .typeahead').typeahead({
			hint: false,
			highlight: true,
			minLength: 3
		},
		{
		limit: 15,
		name: 'businesses',
		display: 'url',
		source: businesses,
		templates: {
			empty: [
				'<div class="tt-empty-message">',
					'Sé más específico.',
				'</div>'
			].join('\n'),
			suggestion: function(data){
				return '<div><img src="/assets/img/business/header/' + data.imagen + '" class="meta-img img-rounded"><strong>' + data.nombre + '</strong> @' + data.url + ' - Saldo: $ ' + data.balance + '</div>';
			}
		}
	}).on('typeahead:selected', function(object, data) {
		if($('#business-search-placeholder').length){
			$('#business-search-placeholder').empty();
			$('#business-search-placeholder').append('<div><img src="/assets/img/business/header/' + data.imagen + '" class="meta-img img-rounded" alt=""><strong>' + data.nombre + '</strong> @' + data.url + ' - Saldo: $ ' + data.balance + '</div>');
			$('#current-balance').val(data.balance);
		}
	});

	var business_placeholder_clone = $("#business-search-placeholder").clone();
	$("#business-search-input").on("input", function() {
		$("#business-search-placeholder").replaceWith(business_placeholder_clone.clone());
	});

	$( "#add-balance" ).keyup(function() {
		var add = $('#add-balance').val();
		var current = $('#current-balance').val();
		if(add == ''){
			$('#new-balance').val(current);
		}else{
			var total = +current + +add;
			$('#new-balance').val('$' + total.toFixed(2));
		}
	});

	$( "#withdraw-balance" ).keyup(function() {
		var withdraw = $('#withdraw-balance').val();
		var current = $('#current-balance').val();
		if(withdraw == ''){
			$('#new-balance').val(current);
		}else{
			var total = +current - +withdraw;
			$('#new-balance').val('$' + total.toFixed(2));
		}
	});

	if($("#sale-currency").length && $("#sale-total").length && $("#business-commission").length && $("#sale-esmarties").length){
		$("#sale-currency").change(calculate_esmarties);
		$("#sale-total").blur(calculate_esmarties);
	}
	function calculate_esmarties(){
		var iso = $("#sale-currency").val();
		var total = $("#sale-total").val();
		var commission = $("#business-commission").val();
		var balance = $("#balance").val();
		if(total == ''){
			total = 0;
		}
		if(iso != ''){
			$.ajax({
				type: "POST",
				url: "/ajax.php",
				data: {
					currency: iso,
					total: total,
					commission: commission
				},
				success: function(data){
					$('#sale-esmarties').val(data);
					var after = +balance - +data;
					$('#after').text('Saldo después de venta: ' + after.toFixed(2));
					if(after < -500){
						$('#after').removeClass();
						$('#after').addClass('required');
					}else{
						$('#after').removeClass();
						$('#after').addClass('text-primary');
					}
				}
			});
		}
		return;
	}

	// Tooltips
	$(function () {
		$('[data-toggle="tooltip"]').tooltip({
			'selector': '',
		'container': 'body'
		})
	})

	// FileInput general
	$('#affiliate-logo, #affiliate-photo, #business-logo, #business-header, #gallery-image, #post-image, #event-image, #certificate-image').fileinput({
		allowedFileExtensions: ['jpg', 'png', '.gif'],
		allowedFileTypes: ['image'], 
		showUpload: false, 
		browseLabel: "Buscar &hellip;",
		removeLabel: "Quitar",
		initialCaption: "Selecciona una imagen"
	});

	// Seguir negocio
	$("#bookmark, .following-btn").click(function(){
		var business_id = $(this).data("id");
		if($(this).data('function') == 'add'){
			$.ajax({
				type: "POST",
				url: "/ajax.php",
				data: {
					bookmark_add: business_id
				}
			});
			$(this).data('function','del');
			return;
		}
		if($(this).data('function') == 'del'){
			$.ajax({
				type: "POST",
				url: "/ajax.php",
				data: {
					bookmark_del: business_id
				}
			});
			$(this).data('function','add');
			return;
		}
	});

	// Recomendar negocio
	$("#recommend, .recommend-btn").click(function(){
		var business_id = $(this).data("id");
		if($(this).data('function') == 'add'){
			$.ajax({
				type: "POST",
				url: "/ajax.php",
				data: {
					recommend_add: business_id
				},
				success: function(){
					$('#recommends').load(document.URL +  ' #recommends');
				}
			});
			$(this).data('function','del');
			return;
		}
		if($(this).data('function') == 'del'){
			$.ajax({
				type: "POST",
				url: "/ajax.php",
				data: {
					recommend_del: business_id
				},
				success: function(){
					$('#recommends').load(document.URL +  ' #recommends');
				}
			});
			$(this).data('function','add');
			return;
		}
	});

	// Certificados de regalo
	$(".wishlist-btn, .cert-wishlist").click(function(){
		var certificate_id = $(this).data('id');
		if($(this).data('function') == 'add'){
			$.ajax({
				type: "POST",
				url: "/ajax.php",
				data: {
					wishlist_add: certificate_id
				}
			});
			$(this).data('function','del');
			return;
		}
		if($(this).data('function') == 'del'){
			$.ajax({
				type: "POST",
				url: "/ajax.php",
				data: {
					wishlist_del: certificate_id
				}
			});
			$(this).data('function','add');
			return;
		}
	});

	$(".cert-wishlist, .recommend-btn, .following-btn").click(function(){
		$(this).toggleClass("marked");
	});

	$(".wishlist-btn").click(function(){
		$(this).toggleClass("marked");
		var span = $(this).children("span");
		var toggleText = span.data("toggle");
		span.data("toggle", span.text());
		span.text(toggleText);
	});

	// Enviar formulario para cambiar de negocio
	$('.change-business').on('click', function(e) {
		e.preventDefault();
		var is_good = confirm('¿Cambiar de negocio?');
		if(is_good){
			$(this).closest("form").submit();
		}
	});
	// Enviar formulario para cambiar de negocio
	$('.cambiar-hotel').on('click', function(e) {
		e.preventDefault();
		var is_good = confirm('¿Cambiar de hotel?');
		if(is_good){
			$(this).closest("form").submit();
		}
	});

	// Enviar formulario para cambair el rol de un empleado
	$('.change-role').on('click', function(e) {
		e.preventDefault();
		var is_good = confirm('¿Realmente desea actualizar este rol de empleado?');
		if(is_good){
			$(this).closest("form").submit();
		}
	});
	$('.change-admin-role').on('click', function(e) {
		e.preventDefault();
		var is_good = confirm('¿Realmente desea actualizar este rol de administrador?');
		if(is_good){
			$(this).closest("form").submit();
		}
	});
	// Cambiar el estado de un negocio
	$('.change-business-status').on('click', function(e) {
		e.preventDefault();
		var is_good = confirm('¿Realmente desea actualizar el status de este negocio?');
		if(is_good){
			$(this).closest("form").submit();
		}
	});

	// Eliminar negocio
	$('.eliminar-negocio').on('click', function(e) {
		e.preventDefault();
		var is_good = confirm('¿Realmente desea eliminar este negocio?');
		if(is_good){
			$(this).closest("form").submit();
		}
	});

	// Borrar un empleado
	$('.delete-employee').on('click', function(){
		return confirm('¿Realmente desea eliminar a este empleado?');
	});
	$('.delete-admin').on('click', function(){
		return confirm('¿Realmente desea quitarle los privilegios de administrador a este usuario?');
	});


	// Borrar imagen de la galería
	$('.delete-gallery-image').on('click', function(){
		return confirm('¿Realmente desea eliminar esta imagen? Una vez hecho, no se puede deshacer.');
	});

	// Agregar o quitar correos dinamicamente
	var max_fields = 4;
	var email_wrapper = $("#email-wrap");
	var email_add_button = $("#add-email");
	var email_count = $('#email-wrap input').length;
	$(email_add_button).click(function(e){
		e.preventDefault();
		if(email_count < max_fields){
			email_count++;
			$(email_wrapper).append('<div class="input-group"><span class="input-group-addon"><i class="fa fa-at"></i></span><input class="form-control" type="email" name="email[]" placeholder="Correo electr&oacute;nico" required/><a href="#" class="input-group-addon remove-field"><i class="fa fa-times text-danger"></i></a></div><!-- /.input-group -->');
		}
	});
	$(email_wrapper).on("click",".remove-field", function(e){
		e.preventDefault(); $(this).parent('div').remove();
		email_count--;
	});
	// Agregar o quitar telefonos dinamicamente
	var phone_wrapper = $("#phone-wrap");
	var phone_add_button = $("#add-phone");
	var phone_count = $('#phone-wrap input').length;
	$(phone_add_button).click(function(e){
		e.preventDefault();
		if(phone_count < max_fields){
			phone_count++;
			$(phone_wrapper).append('<div class="input-group"><span class="input-group-addon"><i class="fa fa-phone"></i></span><input class="form-control" type="text" name="phone[]" placeholder="N&uacute;mero telef&oacute;nico" required/><a href="#" class="input-group-addon remove-field"><i class="fa fa-times text-danger"></i></a></div><!-- /.input-group -->'); //add input box
		}
	});
	$(phone_wrapper).on("click",".remove-field", function(e){
		e.preventDefault(); $(this).parent('div').remove();
		phone_count--;
	});

	// DateTimePicker de Horario de trabajo
	$(function(){
		$('.schedule').datetimepicker({
			format: 'LT',
			icons: {
				time: "fa fa-clock-o",
				date: "fa fa-calendar",
				up: "fa fa-chevron-up",
				down: "fa fa-chevron-down"
			},
			useCurrent: false
		});
	});

	// Borrar publicación
	$('.delete-post').on('click', function(){
		return confirm('¿Realmente quieres eliminar esta publicación? La publicación será eliminada permanentemente.');
	});
	// Borrar evento
	$('.delete-event').on('click', function(){
		return confirm('¿Realmente quieres eliminar este evento? El evento será eliminado permanentemente.');
	});
	// Borrar certificado
	$('.delete-certificate').on('click', function(){
		return confirm('¿Realmente quieres eliminar este certificado? El certificado será eliminado permanentemente.');
	});
	// Cancelar certificado
	$('.cancel-certificate').on('click', function(){
		return confirm('¿Realmente quieres cancelar este certificado? Puedes reactivar el certificado más tarde.');
	});
	// Borrar certificado de wishlist
	$('.discard-wishlist').on('click', function(){
		return confirm('¿Quitar este certificado de regalo de mi lista de deseos?');
	});
	// Cancelar el apartado de regalo
	$('.cancel-redeem').on('click', function(){
		return confirm('¿Realmente quieres cancelar el apartado de este certificado? Una vez hecho, no se puede deshacer.');
	});




	// DataTimePicker: Fechas de evento
	$(function(){
		$('#event-start, #certificate-start').datetimepicker({
			viewMode: 'days',
			icons: {
				time: "fa fa-clock-o",
				date: "fa fa-calendar",
				up: "fa fa-chevron-up",
				down: "fa fa-chevron-down"
			},
			useCurrent: true,
			locale: 'es',
			format: 'DD/MM/YYYY h:mm A',
			allowInputToggle: true
		});
		$('#event-end, #certificate-end').datetimepicker({
			viewMode: 'days',
			icons: {
				time: "fa fa-clock-o",
				date: "fa fa-calendar",
				up: "fa fa-chevron-up",
				down: "fa fa-chevron-down"
			},
			useCurrent: false,
			locale: 'es',
			format: 'DD/MM/YYYY h:mm A',
			allowInputToggle: true
		});
		$("#event-start, #certificate-start").on("dp.change", function (e) {
			$('#event-end, #certificate-end').data("DateTimePicker").minDate(e.date);
		});
		$("#event-end, #certificate-end").on("dp.change", function (e) {
			$('#event-start, #certificate-start').data("DateTimePicker").maxDate(e.date);
		});
	});

	$(function(){
		$('#report-start').datetimepicker({
			viewMode: 'days',
			icons: {
				time: "fa fa-clock-o",
				date: "fa fa-calendar",
				up: "fa fa-chevron-up",
				down: "fa fa-chevron-down"
			},
			useCurrent: true,
			locale: 'es',
			format: 'DD/MM/YYYY',
			allowInputToggle: true
		});
		$('#report-end').datetimepicker({
			viewMode: 'days',
			icons: {
				time: "fa fa-clock-o",
				date: "fa fa-calendar",
				up: "fa fa-chevron-up",
				down: "fa fa-chevron-down"
			},
			useCurrent: false,
			locale: 'es',
			format: 'DD/MM/YYYY',
			allowInputToggle: true
		});
		$("#report-start").on("dp.change", function (e) {
			$('#report-end').data("DateTimePicker").minDate(e.date);
		});
		$("#report-end").on("dp.change", function (e) {
			$('#report-start').data("DateTimePicker").maxDate(e.date);
		});
	});

	// Generar la sugerencia de negocio automaticamente
	$("#business-name").blur(safe_url);
	function safe_url(){
		var the_url = $('#business-name').val();
		$.ajax({
			type: "POST",
			url: "/ajax.php",
			data: {
				the_url: the_url
			},
			success: function(data){
				$('#url').val(data);
			}
		});
		return;
	}

	if($('#input-latitude').val() != ''){
		$('#input-latitude').prop('readonly',true);
	}
	if($('#input-longitude').val() != ''){
		$('#input-longitude').prop('readonly',true);
	}

	// Facebook like button
	(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.net/es_MX/sdk.js#xfbml=1&version=v2.8";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));





if($('#solicitudes').length){
	 var t = $('#solicitudes').DataTable({
      "paging":         false,
      "scrollY":        "800px",
        "scrollCollapse": true,
         "language": {
                        "lengthMenu": "Mostar _MENU_ registros por pagina",
                        "info": "",
                        "infoEmpty": "No se encontro ninguna solcitud",
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
}



  




//generar Iata
	//
		

		$('.generarcodigo').click(function(){
			var iata =  $(this).attr('data-iata');
			var hotel =  $(this).attr('data-hotel');

		
			var cadena = hotel.split(" ");

			var total = cadena.length;
			var resultado = "";

			for(var i = 0; i < total; resultado += cadena[i][0], i++);
			

			$('#codigohotel').val(iata+resultado.toUpperCase());

		});

		$('.crearcodigo').click(function(){
			var iata =  $(this).attr('data-iata');
			var hotel =  $(this).attr('data-hotel');
		
			var cadena = hotel.split(" ");

			var total = cadena.length;
			var resultado = "";

			for(var i = 0; i < total; resultado += cadena[i][0], i++);
			

			$('#codigohotel').val(iata+resultado.toUpperCase());

		});


	//slider
	
	if($('#ex8').length){
		var slider = new Slider('#ex8');
		var valorslider = slider.getValue();

		slider.on("slide", function(sliderValue){
			valorslider = sliderValue;
				document.getElementById('val-slider').textContent = sliderValue + " %";
		});
	}
	

	// Modales 
		$('.modales').modal({
			keyboard:true,
			backdrop:false,
			focus:true,
			show:false

		});


		$('.cerrar').click(function() {
				
				var result = confirm("Acepta salir y dejarlo tal cual?");
				if(result){
					$('#ex8').modal('hide');
				}else{
					return false;	
				}
		});
		// $('.close').click(function() {
				
		// 		var result = confirm("Acepta salir y dejarlo tal cual?");
		// 		if(result){
		// 			$('#ex8').modal('hide');
		// 		}else{
		// 			return false;	
		// 		}
		// });


	// Funciones con Ajax... 
	// 
	// 
		
		// $("#rechazarsolicitud").click(function(){

		// 		var result = confirm('¿Realmente desea Cancelar esta solicitud?');


		// 		if(result){

		// 		$.ajax({
		// 		 	type:'POST',
		// 			url: $(this).attr("action"),s
		// 			data: 'rechazarsolicitud:true',
		// 			beforeSend: function(){
		// 				$(this).text("Cancelando");
		// 				$(this).attr("disabled","disabled");
		// 			},
		// 			complete:function(data){
						
		// 			},
		// 			success:function(data){

		// 				location.reload();
		// 			},
		// 			error:function(data){
		// 				alert("No se pudo cancelar la solicitud, por favor intente mas tarde... ");
		// 			}

		// 		});	
				
		// 		}
		// });
		// 
		// 
		
		$('#aceptarsolicitud').click(function() {
			$("#formulariosolicitud").bind("submit",function(){
		
				var btnaceptar = $('#aceptarsolicitud');

				var result = confirm('¿Realmente desea aceptar esta solicitud?');
				if(result){

					$.ajax({
				 	type:$(this).attr("method"),
					url: $(this).attr("action"),
					data: $(this).serialize(),
					beforeSend: function(){
						btnaceptar.text("Enviando");
						btnaceptar.attr("disabled","disabled");
					},
					complete:function(data){
						btnaceptar.text("Acetar solicitud");
						btnaceptar.removeAttr('disabled');
					},
					success:function(data){

						$('.aceptar').modal('show');
					},
					error:function(data){
						alert("No se pudo aceptar la solicitud, por favor intente mas tarde... ");
					}

				});	
				}else{
					return false;
				}
				return false;				
		});
		});
		

		$('.adjudicar').click(function(){

				$(this).attr('disabled',"disabled");
				$('.close').attr('disabled', 'disabled');
				$('.cerrar').attr('disabled', 'disabled');

				$(this).text("Registrando");

				var perfil = $(this).attr('data-perfil');
				var idhotel = null;

				if($(this).attr('data-hotel') != null){
					idhotel = $(this).attr('data-hotel');
				}


				
				if(perfil == 'Hotel'){
					var codigohotel = document.getElementById("codigohotel").value;
					var comision =  valorslider;

					var path = $(this).attr('data-path');

					$.ajax({
						url: path,
						type: 'POST',
						data: 'action=adjudicar&perfil=Hotel&codigohotel='+codigohotel+'&comision='+comision+'&hotel='+idhotel,
						cache:false
					})
					.done(function(response) {


					location.reload();
						
					})
					.fail(function() {
						console.log("error");
					})
				}else if(perfil == 'Franquiciatario'){

					var comision = valorslider;
					
					var path = $(this).attr('data-path');

					var franquiciatario = $(this).attr('data-path');
					var codigohotel = document.getElementById("codigohotel").value;
					var idhotel = null;
					var idfranquiciatario = null;
					if($(this).attr('data-hotel')){
						idhotel = $(this).attr('data-hotel');
					}
					if($(this).attr('data-franquiciatario')){
						idfranquiciatario = $(this).attr('data-franquiciatario');
					}

					$.ajax({
						url: path,
						type: 'POST',
						
						data: 'action=adjudicar&perfil=Franquiciatario&codigohotel='+codigohotel+'&comision='+comision+'&franquiciatario='+idfranquiciatario+'&hotel='+idhotel,
					})
					.done(function(data) {
											

							//
							// $(document).load(path);

						// document.innerHTML = path;
						 location.reload();
						
					})
					.fail(function() {
						console.log("error");
					})
				}else if(perfil == 'Referidor'){
					var comision = valorslider;
					var codigohotel = document.getElementById("codigohotel").value;

					var idhotel = null;
					var idreferidor = null;
					if($(this).attr('data-hotel')){
						idhotel = $(this).attr('data-hotel');
					}
					if($(this).attr('data-referidor')){
						idreferidor = $(this).attr('data-referidor');
					}

					var path = $(this).attr('data-path');
					

					$.ajax({
						url: path,
						type: 'POST',
						data: 'action=adjudicar&perfil=Referidor&codigohotel='+codigohotel+'&comision='+comision+'&referidor='+idreferidor+'&hotel='+idhotel,
					})
					.done(function(data) {
											

							//
							// $(document).load(path);

						// document.innerHTML = path;
						location.reload();
						
					})
					.fail(function() {
						console.log("error");
					})
				}

				
		});
		    
          
});

// Tabs
// Abrir el tab con link directo
// var url = document.location.toString();
// if (url.match('#')) {
//     $('.nav-tabs a[href="#'+url.split('#')[1]+'"]').tab('show') ;
// } 
// // With HTML5 history API, we can easily prevent scrolling!
// $('.nav-tabs a').on('shown.bs.tab', function (e) {
//     if(history.pushState) {
//         history.pushState(null, null, e.target.hash); 
//     } else {
//         window.location.hash = e.target.hash; //Polyfill for old browsers
//     }
// })

// Icon picker.
// $('.icp-dd').iconpicker({
// 	title: 'Choose an icon to display'
// });

// $('.checkbox').click(function(){
// 	if($(this).children().is(":checked")){
// 		$(this).next('div.row').find('input').removeAttr('disabled');
// 	}else{
// 		$(this).next('div.row').find('input').attr('disabled', 'disabled');
// 	}
// });

// $('.cbx_sn').click(function(){
// 	if($(this).children().is(":checked")){
// 		$(this).next('div.input-group').find('input').removeAttr('disabled');
// 	}else{
// 		$(this).next('div.input-group').find('input').attr('disabled', 'disabled');
// 	}
// });
// 
// 