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



		if(isset($_POST['f1']) and isset($_POST['f2'])){

			$idhotel = $_POST['hotel'];
			
			$f1 = str_replace('+', ' ', $_POST['f1']);
			$f2 = str_replace('+', ' ', $_POST['f2']);

			$result = $home->getConsumosPromedioCompra($idhotel,$f1,$f2);

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


		}else{

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

	}

if(isset($_POST['grafica']) && $_POST['grafica'] == 'consumospromediopornegocio'){

	if(isset($_POST['f1'])){
		$idhotel = $_POST['hotel'];
		$f1 = str_replace('+', ' ', $_POST['f1']);
			$f2 = str_replace('+', ' ', $_POST['f2']);
		$result = $home->getConsumosPromedioNegocio($idhotel,$f1,$f2);

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
		
			

		}else{

		}
		echo json_encode($response);
	}else{
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
		
			

		}else{

		}
		echo json_encode($response);
	}


		
	}

	if(isset($_POST['grafica']) && $_POST['grafica'] == 'totalconsumohuesped'){


		if(isset($_POST['f1'])){
			$idhotel = $_POST['hotel'];
				$f1 = str_replace('+', ' ', $_POST['f1']);
			$f2 = str_replace('+', ' ', $_POST['f2']);

		$result = $home->getTotalConsumoHuesped($idhotel,$f1,$f2);

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
	}else{
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

		

	}

	if(isset($_POST['grafica']) && $_POST['grafica'] == 'totalregalosusuarios'){


		if(isset($_POST['f1'])){
			$idhotel = $_POST['hotel'];
			$f1 = str_replace('+', ' ', $_POST['f1']);
			$f2 = str_replace('+', ' ', $_POST['f2']);

		$result = $home-> getTotalRegalosPorUsuarios($idhotel,$f1,$f2);

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

		}
		}else{
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

			}
		}
		

	}





 ?>