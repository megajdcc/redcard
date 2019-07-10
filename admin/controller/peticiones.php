<?php 


require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';
$con = new assets\libs\connection();

use admin\libs\Reservacion;

$reservacion = new Reservacion($con);



if($_SERVER["REQUEST_METHOD"] == "POST"){	

	if(isset($_POST['peticion']) && $_POST['peticion'] == 'cargarreservaciones'){

		$response = array(
			
			'data'     =>''
			 );

		$resultado = $reservacion->getDatos($_POST);

		if(count($resultado) > 0){
		
			$response['data'] = $resultado;
		}


		echo json_encode($response);


	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'imprimir'){

		$response = array(
			'peticion' =>true,
			'data'     =>''
			 );
		
		$reservacion->getDatos($_POST);
		$reservacion->report();

	
	}

}



 ?>