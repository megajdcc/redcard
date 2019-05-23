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
					<img class="img-responsive mb30" src="<?php echo HOST;?>/assets/img/travelpoints/travelpoints01.jpg" alt="Monedero en Travel Points">
					<img class="img-responsive mb30" src="<?php echo HOST;?>/assets/img/travelpoints/travelpoints02.jpg" alt="Encuentra negocios que ofrecen mas Travel Points">
					<a href="<?php echo HOST;?>/tienda/"><img class="img-responsive mb80" src="<?php echo HOST;?>/assets/img/travelpoints/travelpoints03.jpg" alt="Adquiere Regalos en Travel Points"></a>
				</div>
				<div class="posts post-detail">
					<div class="post-content">
						<img class="post-content-image pull-left" src="<?php echo HOST;?>/assets/img/travelpoints/travelpoints04.png" alt="Regalos en Travel Points" style="width:150px;height:auto;margin-top:-30px;">
						<p>
							<strong class="text-default">Travel Points</strong> es un sistema de premiación para los compradores registrados, con afiliación y uso totalmente gratuito, que permite reunir puntos generados en varios negocios dentro de una misma cartera digital.
						</p>
						<p>
							En un solo sitio los compradores encuentran negocios, promociones y regalos, lo que lo convierte en una app muy conveniente antes de ir a comprar cualquier cosa.
						</p>
						<div class="clearfix"></div>
						<img class="post-content-image pull-right" src="<?php echo HOST;?>/assets/img/travelpoints/esmart-club-regalos.png" alt="Regalos en Travel Points">
						<h3>¿Qu&eacute; gano como Usuario de Travel Points?</h3>
						<p>
							<mark>Gana Regalos</mark>  de calidad, Productos y Servicios como joyas, hospedajes, masajes y mucho más. Velos ahora mismo en nuestra <a href="<?php echo HOST.'/tienda/';?>">tienda</a>.
						</p>
						<p>
							Elige tus regalos favoritos y págalos con tus puntos (<strong>Travel Points</strong>) qque vas acumulando con cada compra registrada en cualquiera de los negocios afiliados.
						</p>
						<p>
							Cada negocio te regala un porcentaje de tu compra en puntos, y se suman todos en tu cuenta de Travel Points: así sumas puntos mucho más rápido.
						</p>
						<p>
							Para saber cuánto te ofrece cada negocio puedes visitarlo y verlo. Está en el cuadrito de color ubicado junto a la categoría del negocio.
						</p>
						<p>
							Cuando ingresas a tu cuenta podrás ver tus puntos, y si vas a Compras podrás ver todas las comprs que te han registrado. No te olvides de pedir que te registren tus puntos cada vez que compres en cualquiera de los negocios afiliados.
						</p>
						<p>
							También puedes seguir a tus negocios favoritos para enterarte de todas sus novedades y promociones.
						</p>
						<p>
							Podrás publicar tu opinión del negocio tras cada compra, y así ayudarás a los demás usuarios. También tú podrás ver la opinión de los demás, para que elijas ir a los negocios que más te atraigan.
						</p>
						
					
					</div><!-- /.post-content -->
				</div>
			</div><!-- /.content -->
		</div><!-- /.container -->
	</div><!-- /.main-inner -->
</div><!-- /.main -->
<?php echo $footer = $includes->get_main_footer(); ?>