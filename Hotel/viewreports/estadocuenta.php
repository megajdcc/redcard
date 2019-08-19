<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	
	<?php 

	$fecha1 = date('g/m/Y h:i A', strtotime($this->busqueda['fechainicio']));

	$fecha2 = date('g/m/Y h:i A', strtotime($this->busqueda['fechafin']));

	 ?>

	<title>Travel Points: Reporte de Actividades - <?php echo $fecha1.' al '.$fecha2; ?></title>
	<meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="../Hotel/viewreports/css/style.css">
</head>
<body>

<header id="header" class="cabezera-pdf">
	<table width="100">
		<tr>
			<td><img src="../assets/img/LOGOV.png" alt="logotipo" class="logo" style="height: 250px, width:auto"></td>
		</tr>
	</table>
</header>
 <main class="cuerpo">

 	<?php if (isset($_SESSION['promotor'])): ?>
 		<h2 class="title">Mi Estado de Cuenta</h2>
 	<?php else: ?>
 		<h2 class="title">Estado de Cuenta</h2>
 	<?php endif ?>
	


	<table width="90%" border="0" style="margin-bottom: 2rem">
		<thead>
			<tr>
				<th>Hotel</th>

			 	<?php if (isset($_SESSION['promotor'])): ?>
			 		<th>Promotor</th>
			 	<?php endif; ?>

				<th colspan="2">Rango</th>
				<th>Balance</th>
				
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php echo $this->getNombreHotel(); ?></td>
				<?php if (isset($_SESSION['promotor'])): ?>
			 		<td><?php echo $this->getPromotor(); ?></td>
			 	<?php endif; ?>

				<?php if(isset($this->busqueda['fechainicio']) && $this->busqueda['fechainicio'] == null){
					echo "<td>Todo el historial</td>";
				}else if(empty($this->busqueda['fechainicio'])){?>
				
				<td colspan="2">Sin rango</td>
				
			<?php }else{?>
				<td><?php echo $this->busqueda['fechainicio'] ?></td>
				<td><?php echo $this->busqueda['fechafin'] ?></td>
			<?php } ?>
			
			<td><?php echo $this->getBalance()?></td>
			</tr>
		</tbody>
	</table>
	<table  id="estadodecuent" class="display" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>Fecha</th>
					<th>Negocio</th>
					<th>Usuario</th>
					<th>Venta</th>
					<th>Comisión</th>
					<th>Balance</th>						
				</tr>
			</thead>
			<tbody>			
			
			<?php foreach($this->estadocuenta as $key => $value) {
			
			 $fecha = date('d/m/Y', strtotime($value['creado']));
			// $fecha = $value['creado'];
			 // settype($value['venta'],'double');

			 $venta = number_format((float)$value['venta'],2,',','.');
			  if($venta < 0){
			  	$venta = '<strong class="negativo">$'.$venta.'</strong>';
			  }else{
			  	$venta = '$'.$venta;
			  }
			  if($value['comision'] < 0){
			  	
			  	$comision = '<strong class="negativo">$'.$value["comision"].'</strong>';
			  }else{
			  	$comision = '$'.$value['comision'];
			  }
			if(!empty($value['nombre'])){
				$nombre = $value['nombre'];
			}else{
				$nombre = $value['username'];
			}
			?>
		 		<tr class="estado">
					<td class="b1"><?php echo $fecha ?></td>
					<td class="b1"><?php echo $value['negocio'] ?></td>
					<td class="b1"><?php echo $nombre ?></td>
					<td class="b1"><?php echo $venta; ?></td>
					<td class="b1"><?php echo $comision; ?></td>
					<td class="b2">$<?php echo $value['balance'] ?></td>
				</tr>
<?php  } ?>
			</tbody>
	</table>
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