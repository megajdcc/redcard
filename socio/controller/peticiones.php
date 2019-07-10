<?php 

require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';
$con = new assets\libs\connection();


use socio\libs\Reservacion;

$reservacion = new Reservacion($con);

/**
 * 	Controladores para peticiones ajax xde negocios
 * 	@author Crespo Jhonatan
 * 	@since 30/06/2019
 * 
 */

if(isset($_POST['peticion']) && $_POST['peticion'] == 'buscarreserva'){

	$response = array(
					'peticion' => false,
					'datos'    => null,
					'mensaje'  => ''
				);


	$reservacion->cargar($_POST['busqueda']);

	$resultado = $reservacion->catalogo;

	if(count($resultado) > 0){
		$response['peticion'] = true;
		$response['datos'] = $resultado;
	}

	echo json_encode($response);

}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'cancelarreservacion'){

	$response = array(
					'peticion' => false,
				);
	$result =  $reservacion->CancelarReserva($_POST['idreserva']);

	if($result){
		$response['peticion'] = true;
		
	}

	echo json_encode($response);

}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'listarreservacionesusuario'){

	$response = array(
					'peticion' => false,
					'datos' => null,
				);
	$result =  $reservacion->getDatos();

	if(count($result) > 0 ){
		$response['peticion'] = true;
		$response['datos'] = $result;
	}

	echo json_encode($response);

}