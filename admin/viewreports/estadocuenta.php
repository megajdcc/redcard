<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	
	<?php 
	  // use Hotel\models\ReportesVentas;

	  // 	require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
	  // 	$con = new assets\libs\connection();

	  // 	$estado = new ReportesVentas($con);


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
			<td><img src="../assets/img/logo.png" alt="logotipo" class="logo"></td>
		</tr>
	</table>
</header>
 <main class="cuerpo">
	<h2 class="title">Estado de Cuenta</h2>

	<table width="50%" border="0">
		<thead>
			<tr>
				
				<th colspan="2">Rango</th>
				<th>Usuario</th>
				<th>Negocio</th>
				
			</tr>
		</thead>
		<tbody>
			<tr>
				
				<?php if(isset($this->busqueda['fechainicio']) && $this->busqueda['fechainicio'] == null || empty($this->busqueda['fechainicio'])){
					echo "<td colspan='2' style='text-aling:center;'>Todo el historial</td>";
				}else{?>
				<td><?php echo $this->busqueda['fechainicio'] ?></td>
				<td><?php echo $this->busqueda['fechafin'] ?></td>
			<?php } ?>


			<?php if(!empty($this->usuario) && !empty($this->negocio)){ ?>
						<td><?php echo $this->getNombreUsuario(); ?></td>
						<td><?php echo $this->getNombreNegocio(); ?></td>
				<?php  }else{?>
						
						<td>Todos los usuarios</td>
						<td >Todos los negocios</td>
				<?php  }?>
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