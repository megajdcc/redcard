<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	
	<?php 

	if(empty($this->busqueda['fechainicio'])){
		$fecha1 = null;
	}else{
		$fecha1 = $this->busqueda['fechainicio'];
	}

	if(empty($this->busqueda['fechafin'])){
		$fecha2 = null;
	}else{
		$fecha2 = $this->busqueda['fechafin'];
	}

	if(is_null($fecha1)){

		if($this->filtro == 0){
			$fechas = 'Todo el dia de hoy';
		}else if($this->filtro == 1){
			$fechas = 'Todo el dia de ayer';
		}else if($this->filtro == 2){
			$fechas = 'Todo el mes';
		}else if($this->filtro == 3){
			$fechas = 'El mes anterior';
		}
		
	}else{
		$fechas  = $fecha1.' al '.$fecha2;
	}

	?>

	<title>Travel Points: Lista de Reservaciones - <?php echo $fechas;?></title>
	<meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="../../Hotel/viewreports/css/style.css">
</head>
<body>

<header id="header" class="cabezera-pdf">
	<table width="100">
		<tr>

			<td><img src="../../assets/img/LOGOV.png" alt="logotipo" class="logo" style="height: 250px, width:auto"></td>
		</tr>
	</table>
</header>
 <main class="cuerpo">
	<h2 class="title">Lista de Reservaciones</h2>

	<table width="50%" border="0">
		<thead>
			<tr>
				<th>Rango</th>
				<td>Hotel</td>
			</tr>
		</thead>
		<tbody>
			<tr>

				<td style="text-align: center"><?php echo $fechas; ?></td>
				<td>
					<?php if(!empty($this->hotel)){
						echo $this->hotel;
 					}else{
 						echo "Todo los hoteles";
 					} ?>
				</td>
				
			</tr>
		</tbody>
	</table>
	<table  id="estadodecuent" class="display" cellspacing="0" width="100%">
			<thead>
			<tr>
				
				<th>Fecha Reserva</th>
				<th>Hotel</th>
				<th>Solicitante</th>
				<th>Personas</th>
				<th>Status</th>
				
				
				
			</tr>
		</thead>
			<tbody>	

			<?php 


		foreach($this->catalogo as $key => $valores) {
			$usuario  = $valores['nombre'];
			if(empty($valores['nombre'])){
				$usuario = $valores['username'];
			}
			$status   = $valores['status'];
			$fecha    = _safe($valores['fecha']);	
			$hotel = _safe($valores['hotel']);
			$personas = _safe($valores['numeropersona']);
			?>

			<tr id="<?php echo $valores['id']?>">
			
				<td><?php echo $fecha ?></td>
				<td><?php echo $hotel ?></td>
				<td><?php echo $usuario; ?></td>
				<td><?php echo $personas; ?></td>
				<td><?php echo $status; ?></td>
            </tr>

            	
			<?php
		}	?>	
			
	
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