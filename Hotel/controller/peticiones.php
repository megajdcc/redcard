<?php 


require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';
$con = new assets\libs\connection();

use Hotel\models\NuevoUsuario;
use Hotel\models\Reservacion;
$reservacion = new Reservacion($con);
$newuser = new NuevoUsuario($con);


if($_SERVER["REQUEST_METHOD"] == "POST"){	

	if(isset($_POST['peticion']) && $_POST['peticion'] == 'newUser'){

		$response = array(
			'peticion' =>false ,
			'mensaje'  =>'',
			'data'     =>null
			 );


		$resultado = $newuser->setData($_POST,true);

		if($resultado){
			$response['peticion'] = true;
		}


		echo json_encode($response);


	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'cancelarreservacion'){

		$response = array(
			'peticion' =>false ,
			'mensaje'  =>'',
			'data'     =>null
			 );


		$resultado = $reservacion->cancelar($_POST['idreserva']);

		if($resultado){
			$response['peticion'] = true;
		}
		echo json_encode($response);

	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'datosrestaurant'){

		$response = array(
			'peticion' =>false ,
			'mensaje'  =>'',
			'data'     =>null
			 );


		$resultado = $reservacion->getRestaurant($_POST['negocio']);

		if($resultado){
			$response['peticion'] = true;
			$response['data'] = $resultado->fetchAll(PDO::FETCH_ASSOC);
		}
		echo json_encode($response);

	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'cargarreservaciones'){

		$response = array(
			'data'     =>''
			 );

		$resultado = $reservacion->getDatos($_POST);

		if(count($resultado) > 0){
		
			$response['data'] = $resultado;
		}


		echo json_encode($response);


	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'grafica-reservaciones-mensuales'){

		$resultado = $reservacion->getDataReservacacionAnualMensual();
		// $resultado = array('name'=>'Agendados','data'=> array(['Julio',2,],['Agosto',5])); EXAmple
		
		echo json_encode($resultado);

	}
	
	// else if(isset($_POST['peticion']) && $_POST['peticion'] == 'reservar1'){

	// 	$response = array(
	// 		'peticion' =>false,
	// 		'ticket'   =>null
	// 		 );


	// 	$resultado = $reservacion->reservar($_POST);

	// 	if($resultado){
	// 		$response['peticion'] = true;
	// 		$response['ticket'] = $resultado;
	// 	}
	// 	echo json_encode($response);

	// }

}



 ?>