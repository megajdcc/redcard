

<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Travel Points: Comprobante de compra de <?php echo $this->getNameProduct();?></title>
	<meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="../assets/viewreports/css/stilo.css">
</head>
<body>

<header id="header" class="cabezera-pdf">
	<img src="../assets/img/travelpointslogofondoazul.png" alt="logotipo" class="logo">
</header>

 <main class="cuerpo">
	<div class="title">
 		<h2 class="">PURCHASE RECEIPT</h2>
	<h3 class="">Comprobante de Compra</h3>
 	</div>

	<table width="80%" border="0">
		<thead>
			<tr>
				<th>Recibo</th>
				<th></th>
			
				<th class="c1">Producto</th>
				<th class="c1">Precio | Price</th>
				<th class="c1">Categoría</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php echo $this->getIdventa();?></td>
				<td><img  class="img-compr" src="../assets/img/store/<?php echo $this->getImageProduct();?>"></td>
				<td><label><?php echo $this->getNameProduct(); ?></label></td>
				<td><label><?php echo $this->getPriceProduct().' Tps';?></label></td>
				<td><label><?php echo $this->getCategoryProduct(); ?></label></td>
			</tr>
		</tbody>
		
	</table>
</main> 


<footer class="pie-pagina">
	
	<section class="leyenda-footer">
		<small style="font-weight: bold;">Travel Points</small>
		
		<small> Marina Vallarta Business Center, oficina 204, Interior de Plaza Marina, Puerto Vallarta, Jalisco, México.</small>
		
	<small>T:+52 (55) 5014 0020 | <cite>soporte@infochannel.si |</cite> <cite>www.infochannel.si</cite></small>
	</section>
	
</footer>


</body>
</html>