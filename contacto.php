<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

$contact = new assets\libs\contact_form($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['send'])){
		$contact->send_message($_POST);
	}
}

$includes = new assets\libs\includes($con);
$properties['title'] = 'Contacto | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
<div class="main">
	<div class="main-inner">
		<div class="content">
			<div class="mt-80">
				<div class="document-title">
					<h1 class="text-binary">Contact us | Contacto</h1>
				</div><!-- /.document-title -->
			</div>
			<div class="container">
				<?php echo $con->get_notify(); echo $contact->get_notification(); ?>
				<div class="row">
					<div class="col-sm-4">
						<h3>Address | Direcci&oacute;n</h3>
						<p>
							Marina Vallarta Business Center, Oficina 204, Plaza Marina.<br>
							Puerto Vallarta, México.
						</p>
					</div><!-- /.col-sm-4 -->
					<div class="col-sm-4">
						<h3>Phones | Tel&eacute;fonos</h3>
						<p>
							Of: 01 800 400 INFO (4636)<br>Of: +52 (55) 5014 0020.
						</p>
					</div><!-- /.col-sm-4 -->
					<div class="col-sm-4">
						<h3>Email | Correo electr&oacute;nico</h3>
						<p>
							<a href="mailto:soporte@infochannel.si" target="_blank">soporte@infochannel.si</a>
						</p>
					</div><!-- /.col-sm-4 -->
				</div><!-- /.row -->
				<div class="background-white p30 mt30 mb30">
					<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d29866.67044017014!2d-105.25707077942599!3d20.65599426687491!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x842145c6ff0855f5%3A0x405660b2dc9fb070!2sMarina+Vallarta+Business+Center!5e0!3m2!1ses!2smx!4v1490807644461" width="100%" height="350" frameborder="0" marginwidth="0" marginheight="0" scrolling="no"></iframe>
				</div>
				<h3>Get in touch!  ¡Cont&aacute;ctanos!</h3>
				<div class="contact-form-wrapper clearfix background-white p30">
					<form class="contact-form" method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>">
						<div class="row">
							<div class="col-sm-4">
								<div class="form-group">
									<label for="contact-form-name">Name | Nombre</label>
									<input type="text" name="name" id="contact-form-name" class="form-control" value="<?php echo $contact->get_name();?>" placeholder="Name | Nombre" required>
									<?php echo $contact->get_name_error();?>
								</div><!-- /.form-group -->
							</div><!-- /.col-* -->
							<div class="col-sm-4">
								<div class="form-group">
									<label for="contact-form-subject">Subject | Asunto</label>
									<input type="text" name="subject" id="contact-form-subject" class="form-control" value="<?php echo $contact->get_subject();?>" placeholder="Subject | Asunto" required>
									<?php echo $contact->get_subject_error();?>
								</div><!-- /.form-group -->
							</div><!-- /.col-* -->
							<div class="col-sm-4">
								<div class="form-group" data-toggle="tooltip" title="Your email | El correo electr&oacute;nico donde nos comunicaremos contigo.">
									<label for="contact-form-email">Email | Correo electr&oacute;nico <i class="fa fa-question-circle text-secondary"></i></label>
									<input type="email" name="email" id="contact-form-email" class="form-control" value="<?php echo $contact->get_email();?>" placeholder="Your email | Su correo electr&oacute;nico" required>
									<?php echo $contact->get_email_error();?>
								</div><!-- /.form-group -->
							</div><!-- /.col-* -->
						</div><!-- /.row -->
						<div class="form-group">
							<label for="contact-form-message">Message | Mensaje</label>
							<textarea class="form-control" id="contact-form-message" rows="6" placeholder="Your message here&hellip; | Escriba su mensaje&hellip;" name="message" required><?php echo $contact->get_message();?></textarea>
							<?php echo $contact->get_message_error();?>
						</div><!-- /.form-group -->
						<button class="btn btn-primary pull-right" type="submit" name="send">Send | Enviar</button>
					</form><!-- /.contact-form -->
				</div><!-- /.contact-form-wrapper -->
			</div><!-- /.container-->
		</div><!-- /.content -->
	</div><!-- /.main-inner -->
</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>