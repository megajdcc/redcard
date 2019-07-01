<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	
	<?php 
	$fecha1 = date('g/m/Y h:i A', strtotime($this->busqueda['fechainicio']));

	$fecha2 = date('g/m/Y h:i A', strtotime($this->busqueda['fechafin']));

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
			
				<th >Rango</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				
				<?php 

				if(is_null($this->busqueda['fechainicio'])){
					echo "<td style='text-align: center'>Todo el listado</td>";
				}else{?>
				<td style="text-align: center"><?php echo $this->busqueda['fechainicio']. ' al ' . $this->busqueda['fechafin']  ?></td>
				
			<?php } ?>
			</tr>
		</tbody>
	</table>
	<table  id="estadodecuent" class="display" cellspacing="0" width="100%">
			<thead>
			<tr>
				<th>Fecha</th>
				<th>Negocio</th>
				<th>Usuario</th>
				<th>Registrante</th>
				<th>Status</th>
				<th>Fecha Reserva</th>
				
				
			</tr>
		</thead>
			<tbody>	

			<?php 
				$sql1 = "SELECT u.username,r.id from usuario as u join reservacion as r on u.id_usuario = r.usuario_registrante 
					where r.usuario_registrante = :user";

		foreach($this->catalogo as $key => $valores) {
			
			$creado   = _safe($valores['creado']);
			$negocio  = _safe($valores['negocio']);
			$usuario  = _safe($valores['usuario']);
			$solicita = _safe($valores['usuario']);
			$status   = _safe($valores['status']);
			$fecha    = _safe($valores['fecha']);	




			switch ($status) {
				case 0:
						$status = 'Sin confirmar';
						$clas = 'sinconfirmar';
					break;
				case 1:
						$status = 'Consumada';
						$clas = 'consumada';
					break;
				case 3:
						$status = 'Confirmada';
						$clas = 'confirmada';
					break;
				case 4:
						$status = 'Cancelada';
						$clas = 'cancelada';
					break;
				
				default:
					# code...
					break;
			}

			$stm = $this->conec->prepare($sql1);
			$stm->bindParam(':user',$valores['usuario_registrante'],PDO::PARAM_INT);
			$stm->execute();

			$registrante = $stm->fetch(PDO::FETCH_ASSOC)['username'];	





			?>

			<tr id="<?php echo $valores['id']?>">
				
				<td><?php echo $creado ?></td>
				<td>
					<?php echo $negocio ?>
				</td>
				<td><?php echo $usuario; ?></td>
				<!-- <td><?php// echo $email; ?></td> -->
				<td><?php echo $registrante; ?></td>
				
				<td><strong class="<?php echo $clas ?>">
					<?php echo $status;?>
				</strong>
					</td>
				<td><?php echo $fecha ?></td>
			
				
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