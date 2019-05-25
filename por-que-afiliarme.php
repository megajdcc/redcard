<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

$includes = new assets\libs\includes($con);
$properties['title'] = '¿Por qué afiliarme? | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
<div class="main">
	<div class="main-inner">
		<div class="content">
			<div class="mt-80">
				<div class="document-title">
					<h1 class="text-binary">¿Por qu&eacute; afiliarme?</h1>
				</div><!-- /.document-title -->
			</div>
		<!-- 	<div class="container">
				<?php echo $con->get_notify();?>
				<img class="img-responsive mb30" src="<?php echo HOST;?>/assets/img/travelpoints/afilia-tu-negocio-travel-points.jpg" alt="Afilia tu negocio en Travel Points">
				<img class="img-responsive mb30" src="<?php echo HOST;?>/assets/img/travelpoints/aquiere-regalos-travel-points.jpg" alt="Ofrece eSmartties a tus clientes en Travel Points">
				<img class="img-responsive mb30" src="<?php echo HOST;?>/assets/img/travelpoints/encuentra-travelpoints.jpg" alt="Recibe nuevos clientes en Travel Points">
				<img class="img-responsive mb30" src="<?php echo HOST;?>/assets/img/travelpoints/free-gifts-travelpoints.jpg" alt="Programa de lealtad de Travel Points">
				<div class="center"><a href="<?php echo HOST;?>/afiliar-negocio" class="btn btn-xl btn-primary">Afilia tu negocio aqu&iacute;</a></div>
			</div>-->

			<div class="container">
				<?php echo $con->get_notify();?>
				<div>
					<img class="img-responsive mb30" src="<?php echo HOST;?>/assets/img/travelpoints01.jpeg" alt="Afilia tu negocio">
					<img class="img-responsive mb30" src="<?php echo HOST;?>/assets/img/travelpoints02.jpeg" alt="Ofrece Travel Points">
					<img class="img-responsive mb30" src="<?php echo HOST;?>/assets/img/travelpoints03.jpeg" alt="Recibe Nuevos clientes">
				</div>
				<div class="posts post-detail">
					<div class="post-content">
						<img class="post-content-image pull-left" src="<?php echo HOST;?>/assets/img/travelpoints/travelpoints04.png" alt="Regalos en Travel Points" style="width:150px;height:auto;margin-top:-30px;">
						<p>
							<strong class="text-default">Travel Points</strong> acerca clientes a los negocios registrados. 
						</p>
						<p>
							Los compradores usan Travel Points para encontrar la información actualizada de los negocios, sus promociones y los regalos disponibles en nuestra tienda para ellos. Por eso usan Travel Points antes de decidir sus compras.
						</p>
						<div class="clearfix"></div>
						<img class="post-content-image pull-right" src="<?php echo HOST;?>/assets/img/travelpoints/esmart-club-regalos.png" alt="Regalos en Travel Points">
						<h3>¿Qué gano como Negocio?</h3>
						<p>
							Como negocio afiliado obtienes la preferencia de los usuarios del sistema, que son compradores reales para tu negocio.
						</p>
						<p>
							Travel Points es una fuente de clientes reales que están buscando dónde comprar y están comprando.
						</p>
						<p>
							El negocio obtiene una serie de herramientas para publicar su oferta, atraer a los compradores a través de los beneficios que ofrezca. El sistema le ofrece herramientas gratuitas de control de sus promociones y ventas para medir los resultados.
						</p>
						<p>
							La afiliación al club es gratuita y el servicio cuesta solamente si logra concretar ventas.
						</p>
						<p>
							El costo del servicio es una comisión de venta equivalente al 6% o superior, cantidad que el negocio elige libremente en su panel de negocio dentro del sitio.
						</p>
						<p>
							La aplicación permite también promover tus eventos, promociones, novedades, etc. para que tus seguidores estén enterados.
						</p>

						<div class="center"><a href="<?php echo HOST;?>/afiliar-negocio" class="btn btn-xl btn-primary">Afilia tu negocio aqu&iacute;</a></div>
						
						
					
					</div><!-- /.post-content -->
				</div>
			</div><!-- /.content -->
		</div><!-- /.container -->
	</div><!-- /.main-inner -->
</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>