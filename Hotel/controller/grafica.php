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
		
		$result = $home->getConsumosPromedioCompra();

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





 ?>