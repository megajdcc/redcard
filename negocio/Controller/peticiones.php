<?php 

require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';
$con = new assets\libs\connection();


use negocio\libs\Restaurant;
use Hotel\models\Reservacion;


$reservacion = new Reservacion($con,true);


$restaurant = new Restaurant($con);
/**
 * 	Controladores para peticiones ajax xde negocios
 * 
 */

if(isset($_POST['peticion']) && $_POST['peticion'] == 'listardisponibilidad'){

	$response = array(
						'peticion' => false,
						'mensajes' => null,
						'dias'    => null
						 );

	$resultado = $restaurant->getDisponibilidad();

	if(count($resultado) > 0 ){
		$response['peticion'] = true;
		$response['mensaje'] = "Se ha encontrado los siguientes datos de los dias disponible..";

		$response['dias'] = $resultado;
	}
	echo json_encode($response);
}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'removerhora'){
		$response = array(
						'peticion' => false,
						'mensajes' => null,
						'dias'    => null
						 );

	$resultado = $restaurant->eliminarhora($_POST['idhora']);

	if($resultado){
		$response['peticion'] = true;
	}


	echo json_encode($response);

}
else if(isset($_POST['peticion']) && $_POST['peticion'] == 'numeromesas'){
		$response = array(
						'peticion' => false,
						'mensajes' => null,
						'dias'    => null
						 );

	$resultado = $restaurant->cambiarnumeromesas($_POST['id'],$_POST['mesas']);

	if($resultado){
		$response['peticion'] = true;
	}


	echo json_encode($response);

}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'asignarhora'){
		$response = array(
						'peticion' => false,
						'mensajes' => null,
						'dias'    => null
						 );

	$resultado = $restaurant->asignarhora($_POST['hora'],$_POST['mesas'],$_POST['dia']);

	if($resultado){
		$response['peticion'] = true;
	}


	echo json_encode($response);

}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'publicar'){
		$response = array(
						'peticion' => false,
						'mensajes' => null,
						'dias'    => null
						 );

	$resultado = $restaurant->publicar();

	if($resultado){
		$response['peticion'] = true;
	}


	echo json_encode($response);

}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'desactivar'){
		$response = array(
						'peticion' => false,
						'mensajes' => null,
						'dias'    => null
						 );

	$resultado = $restaurant->desactivar();

	if($resultado){
		$response['peticion'] = true;
	}


	echo json_encode($response);

}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'consultarpublicacion'){
		$response = array(
						'peticion' => false,
						'mensajes' => null,
						'status'    => null
						 );

		if(isset($_POST['negocio'])){
			$resultado = $restaurant->getPublicacionStatus($_POST['negocio']);
		}else{
				$resultado = $restaurant->getPublicacionStatus();
		}

	if($resultado){
		$response['peticion'] = true;
		$response['status'] = $resultado;
	}


	echo json_encode($response);

}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'diasdisponibles'){
		$response = array(
						'peticion' => false,
						'mensajes' => null,
						'data'    => null
						 );

	$resultado = $restaurant->getDisponibilidadDia($_POST['restaurant']);


		$response['peticion'] = true;
		$response['data'] = $resultado;
	


	echo json_encode($response);

}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'horasdisponibles'){
		$response = array(
						'peticion' => false,
						'mensajes' => null,
						'data'    => null
						 );

		if(isset($_POST['viewnegocio'])){
			$resultado = $restaurant->getHoraDisponibles($_POST['negocio'],$_POST['fecha'],$_POST['diareserva'],$_POST['viewnegocio']);
		}else{
			$resultado = $restaurant->getHoraDisponibles($_POST['negocio'],$_POST['fecha'],$_POST['diareserva']);
		}

	

	if($resultado){
		$response['peticion'] = true;
		$response['data'] = $resultado;
	}


	echo json_encode($response);

}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'reservar'){
		$response = array(
						'peticion' => false,
						'mensajes' => null
						 );

		$resultado  = $reservacion->reservar($_POST);
		if($resultado){
			if(isset($_SESSION['notification'])){
				$response['mensajes'] = $_SESSION['notification']['success'];
				unset( $_SESSION['notification']['success']);

			}	
			$response['peticion'] = true;
		}
	echo json_encode($response);
}




?>