<?php 

require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';
$con = new assets\libs\connection();
use Hotel\models\Home;

$home = new Home($con);

/**
 * 	Controladores para peticiones ajax para graficos... 
 * 
 */

if(isset($_POST['grafica']) && $_POST['grafica'] == 'consumospromedioporcompra'){


		$idhotel = $_POST['hotel'];
		
		$result = $home->getConsumosPromedioCompra($idhotel);

		if($result){
			$response = array();
			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				$promedio = number_format((float)$row['promedio'],2,'.','');


				if($row['iso'] == 'EUR'){
					$div = '€';
				}else{
					$div = '$';
				}
				$username = $row['username'];
				$prome = $promedio;
				 settype($promedio,'float');
				$response[] = array('name'=>$username,'y'=>$promedio);
	
			}
		
			echo json_encode($response);

		}else{

		}

	}

if(isset($_POST['grafica']) && $_POST['grafica'] == 'consumospromediopornegocio'){


		$idhotel = $_POST['hotel'];
		
		$result = $home->getConsumosPromedioNegocio($idhotel);

		if($result){
			$response = array();
			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				$promedio = number_format((float)$row['promedio'],2,'.','');


				if($row['iso'] == 'EUR'){
					$div = '€';
				}else{
					$div = '$';
				}
				$negocio = $row['negocio'];
				$prome = $promedio;
				 settype($promedio,'float');
				$response[] = array('name'=>$negocio,'y'=>$promedio);
	
			}
		
			echo json_encode($response);

		}else{

		}

	}

	if(isset($_POST['grafica']) && $_POST['grafica'] == 'totalconsumohuesped'){


		$idhotel = $_POST['hotel'];

		$result = $home->getTotalConsumoHuesped($idhotel);

		if($result){
			$response = array();
			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				$consumo = $row['consumo'];
				$usuario = $row['usuario'];
				settype($consumo, 'float');

				$response[] = array('name' => $usuario, 'y' => $consumo);
	
			}
		
			echo json_encode($response);

		}else{

		}

	}

	if(isset($_POST['grafica']) && $_POST['grafica'] == 'totalregalosusuarios'){


		$idhotel = $_POST['hotel'];

		$result = $home-> getTotalRegalosPorUsuarios($idhotel);

		if($result){
			$response = array();
			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			
				$nombre = '';
				if(!empty($row['nombre'])){
					$nombre = $row['nombre'];
				}else{
					$nombre = $row['username'];
				}
				$total = $row['regalos'];
				settype($total, 'integer');
				$response[] = array($nombre,$total);
			}
		
			echo json_encode($response);

		}else{

		}

	}





 ?>