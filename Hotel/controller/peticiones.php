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

	}

}



 ?>