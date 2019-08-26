<?php  

require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';

$con = new assets\libs\connection();

use \Hotel\models\Includes;
use \Hotel\models\Promotor;



$includes = new Includes($con,true);
$promotor = new Promotor($con);

if(isset($_GET['email']) && isset($_GET['codigo'])){
			$promotor->verificar($_GET['email']);
		}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
  if(isset($_POST['registrar-pass'])){
   $promotor->registrarPass($_POST);
   die();
  }

  if(isset($_POST['ingresar'])){

  	$promotor->login($_POST);
  	die();

  }

}

if(isset($_SESSION['promotor'])){
	header('location: '.HOST.'/Hotel/');
	die();
}

$properties['title'] = 'Login de promotores en Hoteles| Travel Points';
$properties['description'] = '';

echo $header = $includes->get_no_indexing_header($properties);

?>

<div class="login-p">


<header class="cabecera-login">
	<div class="container">
		<figure class="col-xs-12 logo-login">
			<a href="<?php echo HOST.'/index' ?>">
				<img src="<?php echo HOST.'/assets/img/logo.svg' ?>">
			</a>
		</figure>
	</div>
</header>
<?php echo $promotor->getNotificacion(); ?>
<main class="content-login">

	<?php  
		if(isset($_GET['email'])){
			$email = $_GET['email'];
			?>
	<section class="panel-login">
						<header>
							<h2 class="title">Ingrese su contraseña de usuario.</h2>
						</header>
	
						<main class="content-logueo">

						<form action="<?php echo _safe($_SERVER['REQUEST_URI']);?>" name="form-verified-pass" method="POST">
							<div class="form-group">
								<label for="password">Password</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-unlock-alt"></i></span>
									<input type="password" name="pass1" class="form-control" placeholder="Ingrese contraseña" required>
								</div>
							</div>
							<div class="form-group">
								<label for="password">Confirm password</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-unlock-alt"></i></span>
									<input type="password" name="pass2" class="form-control" placeholder="Vuelva a ingresar la contraseña" required>
								</div>
							</div>
							<div class="btn-footer">
								<button class="registrar-pass btn btn-info" name="registrar-pass" data-toggle="tooltip" title="Registrar contraseña." data-placement="bottom" disabled><i class="fa fa-send"></i>Registrar</button>
							</div>
							<input type="hidden" name="email" value="<?php echo $email; ?>">

						</form>

						</main>


					</section>

		<?php }else{?>
					<section class="panel-login">
						<header>
							<h2 class="page-title">Iniciar Sesi&oacute;n <br> <small>Hotel Panel</small></h2>
			
						</header>

						<main class="content-logueo">

							<form action="<?php echo _safe($_SERVER['REQUEST_URI']);?>" name="form-login-promotor" method="POST">
								
								<div class="form-group">
									<label for="username-email">Nombre de usuario || Email</label>
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-user"></i></span>
										<input type="text" name="username-email" class="form-control" placeholder="Username or email." required>
									</div>
								</div>
							
								<div class="form-group">
									<label for="password">Password</label>
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-expeditedssl"></i></span>
										<input type="password" name="password" placeholder="Enter Password" class="form-control">
									</div>
								</div>

								<div class="btn-footer">
									<a class="link-promotor form-control" href="<?php echo HOST.'/Hotel/new-promotor.php' ?>">No has establecido su contraseña?</a>
									<button type="submit" name="ingresar" class="ingresar btn btn-info" data-toggle="tooltip" title="Ingresar o validar en el caso de usuarios nuevos" data-placement="bottom"><i class="fa fa-send"></i>Ingresar</button>
								</div>

							</form>

						</main>

						<main class="panel-new-promotor">

							<form action="#" name="verificar-email-promotor" method="POST">
								
							
							<div class="form-group">
								<label for="username-email">Username || Email</label>
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-user"></i></span>
									<input type="text" name="email-promotor-new" class="form-control" placeholder="Email de registro como promotor" required>
								</div>

							</div>



							<div class="btn-footer">
								<button type="submit" class="verificar btn btn-info" data-toggle="tooltip" title="Ingresar o validar en el caso de usuarios nuevos" data-placement="bottom"><i class="fa fa-send"></i>Solicitar</button>
							</div>
							</form>
						</main>


					</section>
		<?php } ?>

</main>
<footer class="pie-login">
	<div class="container">
		<div class="footer-bottom-left">
					&copy; <?php echo date('Y') ?> All Rights Reserved | Todos los derechos reservados.
		</div><!-- /.footer-bottom-left -->
		<div class="footer-bottom-right">
					<ul class="nav nav-pills">
						<li><a href="<?php echo HOST.'/' ?>">Home | Inicio</a></li>
						<li><a href="<?php echo HOST.'/terminos-y-condiciones'?>">Termns and Conditions | T&eacute;rminos y Condiciones</a></li>
						<li><a href="<?php echo HOST.'/preguntas-frecuentes'?>">FAQ | Preguntas Frecuentes</a></li>
						<li><a href="<?php echo HOST.'/contacto'?>">Contact | Contacto</a></li>
					</ul><!-- /.nav -->
				</div><!-- /.footer-bottom-right -->
	</div>
	
</footer>
</div>

<script >
	
	$(document).ready(function() {
		var pass1 = '';
		var pass2 = '';
		$('input[name="pass1"]').on('keyup',function(e){
			 pass1 = $(this).val();
		});


		$('input[name="pass2"]').on('keyup',function(e){
		 		pass2 = $(this).val();

		 		if(pass1 == pass2){
		 			$('.registrar-pass').removeAttr('disabled');
		 		}else{
		 			$('.registrar-pass').attr('disabled','disabled');
		 		}
 			});
		$('.link-promotor').on('click',function(e){
			e.preventDefault();
			$('.content-logueo').slideUp('slow',function(){

				$('.panel-new-promotor').show('slow', function() {
					$('.panel-new-promotor').addClass('show-panel');
				});
				
			});
		});




		// SOLICITAR CONTRASENA... 
		// 
		

		$('form[name="verificar-email-promotor"]').on('submit',function(e){
			e.preventDefault();

			$('.verificar').text('Verificando, por favor espere...');
			$('.verificar').attr('disabled', 'disabled');
				var email = $('input[name="email-promotor-new"]').val();
				$.ajax({
					url: '/Hotel/controller/peticiones.php',
					type: 'POST',
					dataType: 'JSON',
					data: {peticion: 'verificarcuentapromotor',emailpromotor:email},
				})
				.done(function(response) {
					
					if(response.peticion){
						$.alert({
							title:'Verificación de cuenta!',
							content:response.mensaje
						});

					}else{
						$.alert({
							title:'Verificación de cuenta!',
							content:response.mensaje
						});
					}
					$('.verificar').text('');
					$('.verificar').append('<i class="fa fa-send"></i>Volver a enviar.')

					$('.verificar').removeAttr('disabled');
				});


		});



	
		
	});
</script>
<?php echo $includes->get_link_footer();?>