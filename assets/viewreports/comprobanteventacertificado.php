

<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Travel Points: Comprobante de compra de <?php// echo $this->getNameProduct();?></title>
	<meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="../../assets/viewreports/css/stilo.css">
</head>
<body>

<header id="header" class="cabezera-pdf">
	<table width="100">
		<tr>
			<td><img src="../../assets/img/travelpointslogofondoazul.png" alt="logotipo" class="logo"></td>
		</tr>
	</table>
</header>
 <main class="cuerpo">
	<h2 class="title">Comprobante de compra y certificado</h2>
	

	<p class="idventa" style="margin-right: 0px; text-align: right;">Nro de recibo:<strong><?php //echo $this->getIdventa();?></strong></p>
	<table width="100%" border="0">
		<thead>
			<tr>
				<th rowspan="2" style="text-align: center;"><img  class="img-compr" src="../assets/img/store/<?php echo $this->getImageProduct();?>"></th>
				<th class="c1">Product</th>
				<th class="c1">Price</th>
				<th class="c1">Category</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				
				<td><label><?php //echo $this->getNameProduct(); ?></label></td>
				<td><label>Tp$<?php //echo $this->getPriceProduct().' Travel Points';?></label></td>
				<td><label><?php //echo $this->getCategoryProduct(); ?></label></td>
			</tr>
		</tbody>
		
	</table>
			<h2 class="h2-2">!Felicidades por tu adquisición de servicio!</h2><br> 
	<figure class="certificado">
		<h4>Certificado</h4>
		<img src="../assets/img/store/coupon/<?php //echo $this->getCertificado();?>">
	</figure>
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