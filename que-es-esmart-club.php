<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 
$con = new assets\libs\connection();

$includes = new assets\libs\includes($con);
$properties['title'] = '¿Qué es Travel Points? | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
<div class="main">
	<div class="main-inner">
		<div class="content">
			<div class="mt-80">
				<div class="document-title">
					<h1 class="text-binary">What is the Travel Points?</h1>
				</div><!-- /.document-title -->
			</div>
			<div class="container">
				<?php echo $con->get_notify();?>
				<div>
					<img class="img-responsive mb30" src="<?php echo HOST;?>/assets/img/esmartclub/monedero-esmart-club.jpg" alt="Monedero en Travel Points">
					<img class="img-responsive mb30" src="<?php echo HOST;?>/assets/img/esmartclub/encuentra-esmartties-esmart-club.jpg" alt="Encuentra negocios que den eSmartties en Travel Points">
					<a href="<?php echo HOST;?>/tienda/"><img class="img-responsive mb80" src="<?php echo HOST;?>/assets/img/esmartclub/adquiere-regalos-esmart-club.jpg" alt="Adquiere Regalos en Travel Points"></a>
				</div>
				<div class="posts post-detail">
					<div class="post-content">
						<img class="post-content-image pull-left" src="<?php echo HOST;?>/assets/img/esmartclub/esmart-club-logo.png" alt="Regalos en Travel Points" style="width:100px;height:auto;margin-top:-30px;">
						<p>
							<strong class="text-default">Travel Points</strong> is the REWARD programm for Club Regina guests, in order topromote what we believe is worth in our destinations, so travelers improve their stay. 
						</p>
						<p>
							Find great places to go and add points to your Rewards Wallet for every purchase you do in any of the listed businesses. Exchange your points for anything you like in our Gift Store.
						</p>
						<div class="clearfix"></div>
						<img class="post-content-image pull-right" src="<?php echo HOST;?>/assets/img/esmartclub/esmart-club-regalos.png" alt="Regalos en Travel Points">
						<h3>¿Qu&eacute; gano como Socio comprador?</h3>
						<p>
							<mark>Regalos</mark> Productos y Servicios de calidad (joyas, hospedajes, masajes y mucho m&aacute;s. Puedes verlos ahora mismo en la <a href="<?php echo HOST.'/tienda/';?>">tienda</a>).
						</p>
						<p>
							Cada regalo tiene un valor en puntos (<strong>eSmartties</strong>) que acumulas con cada compra registrada en cualquier de los negocios afiliados.
						</p>
						<p>
							Cada negocio te regala un porcentaje de tu compra, la cual recibir&aacute;s en puntos llamados eSmartties en tu monedero.
						</p>
						<p>
							Puedes ver el porcentaje que ofrece cada negocio en el sitio de Travel Points.
						</p>
						<p>
							Accedes a tu monedero electr&oacute;nico al iniciar sesi&oacute;n.
						</p>
						<p>
							Todos los puntos que obtengas de cualquier Negocio Afiliado se acumula en tu monedero. Al registrar la venta ver&aacute;s tus puntos inmediatamente.
						</p>
						<p>
							Adem&aacute;s, la aplicaci&oacute;n te permite seguir a tus negocios favoritos y enterarte de sus novedades y prmomociones.
						</p>
						<p>
							Tras cada compra, el socio podr&aacute; evaluar al negocio y opinar sobre su experiencia. Esto permite a los dem&aacute;s socios tener m&aacute;s informaci&oacute;n para poder decidir sus compras. Es por esto que estamos seguros de que los negocios afiliados al club est&aacute;n comprometidos con la satisfacci&oacute;n de sus clientes.
						</p>
						<img class="post-content-image pull-left" src="<?php echo HOST;?>/assets/img/esmartclub/esmart-club-clientes.jpg" alt="Clientes en Travel Points">
						<h3>¿Qu&eacute; gano como Negocio?</h3>
						<p>
							<mark>Compradores reales para tu negocio.</mark>
						<p>
							Para los negocios el club es una fuente de clientes porque todos los socios son compradores, est&aacute;n buscando d&oacute;nde comprar y est&aacute;n comprando.
						</p>
						<p>
							El negocio podr&aacute; posicionarse en un nicho de compradores reales e incrementar sus ventas al darse a conocer a nuevos clientes, publicar su oferta, atraer a los socios a trav&eacute;s de los beneficios que ofrezca. Adem&aacute;s el sistema le ofrece herramientas gratuitas de control de sus promociones y ventas a trav&eacute;s del club para que mida resultados.
						</p>
						<p>
							La afiliaci&oacute;n al club es gratuita.
						</p>
						<p>
							El negocio pagar&aacute; una comisi&oacute;n equivalente al porcentaje que &eacute;l mismo publique en el sitio solamente cuando un socio registre una compra.
						</p>
						<p>
							Adem&aacute;s, la aplicaci&oacute;n te permite promover tus eventos, promociones, novedades, etc. para que tus seguidores est&eacute;n enterados.
						</p>
					</div><!-- /.post-content -->
				</div>
			</div><!-- /.content -->
		</div><!-- /.container -->
	</div><!-- /.main-inner -->
</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>