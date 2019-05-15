// Desarrollado por Alan Casillas. alan.stratos@hotmail.com
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

	$('#user-search .typeahead').typeahead({
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

	if($('#user-search-input').val() != '' && $('#user-search-placeholder').length){
		var username = $('#user-search-input').val();
		$.ajax({
			type: "POST",
			url: "/ajax.php",
			data: {
				load_username: username
			},
			success: function(data){
				$('#user-search-placeholder').empty();
				$('#user-search-placeholder').append( data );
			}
		});
	}

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
      "scrollY":        "400px",
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

				if(perfil == 'Hotel'){
					var codigohotel = document.getElementById("codigohotel").value;
					var comision =  valorslider;

					var path = $(this).attr('data-path');

					$.ajax({
						url: path,
						type: 'POST',
						dataType: 'HTML',
						data: 'action=adjudicar&perfil=Hotel&codigohotel='+codigohotel+'&comision='+comision,
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

					$.ajax({
						url: path,
						type: 'POST',
						dataType: 'HTML',
						data: 'action=adjudicar&perfil=Franquiciatario&comision='+comision,
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

					var path = $(this).attr('data-path');
					

					$.ajax({
						url: path,
						type: 'POST',
						data: 'action=adjudicar&perfil=Referidor&codigohotel='+codigohotel+'&comision='+comision,
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