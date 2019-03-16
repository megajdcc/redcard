<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!isset($_SESSION['user']) || !isset($_SESSION['business'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if($_SESSION['business']['id_rol'] != 4 && $_SESSION['business']['id_rol'] != 5){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

$cert = new negocio\libs\manage_certificates($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$cert->setData($_POST, $_FILES);
}

$includes = new negocio\libs\includes($con);

$properties['title'] = 'Crear un nuevo certificado de regalo | eSmart Club';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $cert->get_notification(); ?>
		<form method="post" action="<?php echo _safe(HOST.'/negocio/certificados/crear');?>" enctype="multipart/form-data">
			<div class="background-white p30 mb50">
				<div class="page-title">
					<h1>Crear un nuevo certificado de regalo</h1>
				</div>
				<div class="row">
					<div class="col-md-8">
						<div class="form-group" data-toggle="tooltip" title="Mantenga el nombre corto y objetivo">
							<label for="certificate-name">Nombre del certificado <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
							<input class="form-control" type="text" id="certificate-name" name="name" value="<?php echo $cert->getName();?>" placeholder="Nombre del certificado" maxlength="255" required/>
							<?php echo $cert->getNameError(); ?>
						</div>
						<div class="form-group">
							<label for="certificate-description">Descripci&oacute;n del certificado <span class="required">*</span></label>
							<textarea class="form-control" rows="2" id="certificate-description" name="description" placeholder="Descripci&oacute;n del certificado" required><?php echo $cert->getDescription();?></textarea>
							<?php echo $cert->getDescriptionError(); ?>
						</div>
						<div class="form-group" data-toggle="tooltip" title="Opcional: Puede escribir las condiciones para redimir el certificado">
							<label for="certificate-condition">Condiciones <i class="fa fa-question-circle text-secondary"></i></label>
							<textarea class="form-control" rows="2" id="certificate-condition" name="condition" placeholder="Condiciones"><?php echo $cert->getCondition();?></textarea>
						</div>
						<div class="form-group" data-toggle="tooltip" title="Opcional: Puede escribir las restricciones del certificado">
							<label for="certificate-restriction">Restricciones <i class="fa fa-question-circle text-secondary"></i></label>
							<textarea class="form-control" rows="2" id="certificate-restriction" name="restriction" placeholder="Restricciones"><?php echo $cert->getRestriction();?></textarea>
						</div>
					</div>
					<div class="col-md-4">
						<div class="row">
							<div class="col-sm-6 col-md-12">
								<div class="form-group">
									<label for="start">Fecha y hora de inicio <span class="required">*</span></label>
									<div class="input-group date" id="certificate-start">
										<input class="form-control" type="text" id="start" name="date-start" value="<?php echo $cert->getDateStart();?>" placeholder="Fecha y hora de inicio" required/>
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									</div>
									<?php echo $cert->getDateStartError(); ?>
								</div>
							</div>
							<div class="col-sm-6 col-md-12">
								<div class="form-group">
									<label for="end">Fecha y hora de fin <span class="required">*</span></label>
									<div class="input-group date" id="certificate-end">
										<input class="form-control" type="text" id="end" name="date-end" value="<?php echo $cert->getDateEnd();?>" placeholder="Fecha y hora de fin" required/>
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									</div>
									<?php echo $cert->getDateEndError(); ?>
								</div>
							</div>
							<div class="col-sm-4 col-md-12">
								<div class="form-group" data-toggle="tooltip" title="Ingrese el total de certificados que estar&aacute;n disponibles">
									<label for="certificate-quantity">Disponibles <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
									<input class="form-control" type="number" min="1" id="certificate-quantity" name="quantity" value="<?php echo $cert->getQuantity();?>" placeholder="Total disponibles" required/>
									<?php echo $cert->getQuantityError(); ?>
								</div>
							</div>
							<div class="col-xs-6 col-sm-4 col-md-12">
								<div class="form-group" data-toggle="tooltip" title="Se refiere al valor al p&uacute;blico del certificado">
									<label for="certificate-cost">Precio <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
									<input class="form-control" type="text" id="certificate-cost" name="cost" value="<?php echo $cert->getCost();?>" placeholder="Precio" required/>
									<?php echo $cert->getCostError(); ?>
								</div>
							</div>
							<div class="col-xs-6 col-sm-4 col-md-12">
								<div class="form-group" data-toggle="tooltip" title="La divisa en la que est&aacute; valuado el certificado">
									<label for="certificate-currency">Divisa <i class="fa fa-question-circle text-secondary"></i> <span class="required">*</span></label>
									<select class="form-control" id="certificate-currency" name="currency" title="Selecciona una divisa" required>
									<?php $cert->getCurrencies();?>
									</select>
									<?php echo $cert->getCurrencyError(); ?>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xs-12">
						<div class="form-group">
							<label for="certificate-image">Imagen <span class="required">*</span></label>
							<input type="file" id="certificate-image" name="image" required/>
							<?php echo $cert->getImageError(); ?>
						</div>
					</div>
				</div>
				<hr>
				<label class="pull-right"><span class="required">*</span> Los campos marcados son obligatorios</label>
				<button class="btn btn-success" type="submit">Crear nuevo certificado</button>
			</div><!-- /.box -->
		</form>
	</div><!-- /.col-* -->
</div><!-- /.row -->
<?php echo $footer = $includes->get_footer(); ?>