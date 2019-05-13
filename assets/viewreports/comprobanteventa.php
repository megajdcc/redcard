

<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Travel Points: Comprobante de compra de <?php echo $this->getNameProduct();?></title>
	<meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="../assets/viewreports/css/stilo.css">
</head>
<body>

<header id="header" class="cabezera-pdf">
	<table width="100">
		<tr>
			<td><img src="../assets/img/LOGOV.png" alt="logotipo" class="logo"></td>
		</tr>
	</table>
</header>
 <main class="cuerpo">
	<h2 class="title">Comprobante de compra</h2>
	

	<p class="idventa" style="margin-right: 0px; text-align: right;">Nro de recibo:<strong><?php echo $this->getIdventa();?></strong></p>
	<table width="100%" border="0">
		<thead>
			<tr>
				<th></th>
				<th>Product</th>
				<th>Price</th>
				<th>Category</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td style="text-align: center;"><img  class="img-compr" src="../assets/img/store/<?php echo $this->getImageProduct();?>"></td>
				<td><label><?php echo $this->getNameProduct(); ?></label></td>
				<td><label>Tp$<?php echo $this->getPriceProduct().' Travel Points';?></label></td>
				<td><label><?php echo $this->getCategoryProduct(); ?></label></td>
			</tr>
		</tbody>
		
	</table>

	<label style="margin-top: 50px; text-align: justify !important;">
		!Felicidades por tu compra!.<br> 
	 	Retiralo en nuestra tienda ubicada en Marina Vallarta Business Center, Oficina 204. Interior Plaza Marina, en el fraccionamiento Marina Vallarta, Puerto Vallarta. Teléfono (322) 2259635. de lunes a viernes de 9AM a 6PM y los Sabados de 9AM a 2PM. Guarda este comprobante para futuras referencias.
	</label>

</main> 

<footer class="pie-pagina">
	<section class="leyenda-footer">
		<small> Info Channel "Publimoción en hoteles"</small>
	<small><cite>www.infochannel.si | info@infochannel.si</cite></small>
	<small>01 800 400 4636</small>
	</section>
	
</footer>
</body>
</html>