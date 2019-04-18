<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	
	<?php 
	  use Hotel\models\ReportesVentas;

	  	require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
	  	$con = new assets\libs\connection();

	  	$estado = new ReportesVentas($con);

	 ?>
	<title>Titulo</title>
	<meta charset="UTF-8">

		<!-- <link rel="stylesheet" type="text/css" href="css/reportes.css"> -->
		<link rel="stylesheet" type="text/css" href="../Hotel/viewreports/css/style.css">
		<!-- <link rel="stylesheet" type="text/css" href="css/reportes.css"> -->

	

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
	<h2 class="title">Estado de Cuenta</h2>
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
			
			<?php foreach($estado->estadocuenta as $key => $value) {
			
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
		<small> Marina Vallarta Business, Oficina 204, Plaza Marina</small>
	<small>Puerto Vallarta, Mexico.</small>
	<small>01 800 400 INFO (4636),(322) 2259635.</small>
	</section>
	
</footer>
</body>
</html>