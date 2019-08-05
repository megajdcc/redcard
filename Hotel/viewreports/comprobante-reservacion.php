<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 

$con = new assets\libs\connection();

use \Hotel\models\Reservacion;

$reservacion = new Reservacion($con,false,true);

?>

<!doctype html>
<html>
<head>
	<title>Reservacion nro <?php echo $reservacion->ticket['ticket'] ?></title>
	<meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="<?php echo HOST.'/Hotel/viewreports/css/style.css' ;?>">
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo HOST.'/assets/libraries/font-awesome/css/font-awesome.min.css';?>"/>
</head>
<body class="tiket-reservacion">

<header id="header" class="cabezera-pdf">
	<img src="../../assets/img/logo.png" alt="logotipo" class="logo">
</header>

<main class="content-ticket">
	<p class="title-ticket">
		<strong>Reservation</strong><br>Confirmation
	</p>
	

	<section class="content-data">

		<table width="100%" border="0" class="table-2">
			<tr>
				<td><strong class="fa fa-user user-icon"></strong></td>
				<td><p class="data-article"><?php echo $reservacion->ticket['nombrecompleto'] ?><br><?php echo $reservacion->ticket['username'] ?></p></td>
			</tr>
		</table>

		<table width="100%"  border="0" class="table-2">
			<tr>
				<td colspan="2" class="cant-person"><strong>TABLE FOR <?php echo $reservacion->ticket['numeropersona'] ?> AT</strong></td>
			</tr>
			<tr>
				<td><strong class="fa fa-home"></strong></td><td><p class="data-article"><?php echo $reservacion->ticket['negocio'] ?></p></td>
			</tr>
			<tr>
				<td><strong class="fa fa-map-marker"></strong></td><td><p class="data-article"><?php echo $reservacion->ticket['direccion-negocio'] ?></p></td>
			</tr>
			<tr>
				<td><strong class="fa fa-calendar calendario"></strong></td><td><p class="data-article calen"><?php echo $reservacion->ticket['fecha'] ?><br><?php echo $reservacion->ticket['hora'] ?></p></td>
			</tr>
		</table>

		<table width="auto" border="0" class="table-3">
			<tr>
				<td colspan="2" class="cant-person"><strong>CONFIRMATION</strong></td>
			</tr>
			<tr>
				<td>Num:</td><td><?php echo $reservacion->ticket['ticket']; ?></td>
			</tr>
			<tr>
				<td>Hotel:</td><td><?php echo $reservacion->ticket['hotel'] ?></td>
			</tr>
			<tr>
				<td>Concierge:</td><td><?php echo $reservacion->ticket['concierge']; ?></td>
			</tr>
		</table>
		
		<div class="purchase-descripcion">
			<strong>REGISTER YOUR PURCHASE & GET FREE POINTS</strong>
		</div>
		
	</section>

</main>

 
<footer class="pie-pagina">

	<figure>
		<table>
			<tr>
				<td><img src="../../assets/img/logo.png" alt="Logo del travel points" class="logo"></td>
				<td><p class="data-article">TravelPoints.com.mx</p></td>
			</tr>
		</table>
		
		
	</figure>
	<section class="leyenda-footer">
		<small>Tel: 01800 400 INFO (4636)</small>
	<small><cite>www.infochannel.si</cite></small>
	<small>Info Channel: "PubliMOCI&Oacute;N en hoteles"</small>
	</section>
	
</footer>
<script type="text/javascript" src="<?php echo HOST.'/assets/libraries/font-awesome/js/fontawesome.min.js' ?>"></script>
</body>
</html>