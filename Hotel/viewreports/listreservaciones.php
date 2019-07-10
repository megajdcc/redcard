<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	
	<?php 
	$fecha1 = $this->busqueda['datestart'];
	$fecha2 = $this->busqueda['dateend'];

	 ?>

	<title>Travel Points: Lista de Reservaciones - <?php echo $fecha1.' al '.$fecha2; ?></title>
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
			</tr>
		</thead>
		<tbody>
			<tr>
				
				<?php 

				if(empty($this->busqueda['datestart'])){
					echo "<td style='text-align: center'>Todo el listado</td>";
				}else{?>
				<td style="text-align: center"><?php echo $this->busqueda['datestart']. ' al ' . $this->busqueda['dateend']  ?></td>
				
			<?php } ?>
			</tr>
		</tbody>
	</table>
	<table  id="estadodecuent" class="display" cellspacing="0" width="100%">
			<thead>
			<tr>
				<th>Fecha</th>
				<th>Hotel</th>
				<th>Solicita</th>
				<th>Personas</th>
				<th>Status</th>
			</tr>
		</thead>
			<tbody>	

			<?php 
			

		foreach($this->catalogo as $key => $valores) {
			
			$fecha   = _safe($valores['fecha']);
			$hotel  = _safe($valores['hotel']);
			$username  = _safe($valores['username']);
			$numeropersona = _safe($valores['numeropersona']);
			$status   = _safe($valores['status']);

			switch ($status) {
				case 0:
						$status = 'Agendada';
						$clas = 'sinconfirmar';
					break;
				case 1:
						$status = 'Consumada';
						$clas = 'consumada';
					break;
				case 2:
						$status = 'Confirmada';
						$clas = 'confirmada';
					break;
				case 3:
						$status = 'Cancelada';
						$clas = 'cancelada';
					break;
				
				default:
					# code...
					break;
			}

			if(empty($hotel)){
				$hotel = 'directo (sin hotel)';
			}

	
			?>

			<tr id="<?php echo $valores['id']?>">
				
				<td><?php echo $fecha ?></td>
				<td>
					<?php echo $hotel ?>
				</td>
				<td><?php echo $username; ?></td>
				
				<td><?php echo $numeropersona; ?></td>
				
				<td><strong class="<?php echo $clas ?>">
					<?php echo $status;?>
				</strong>
					</td>
				
			
				
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