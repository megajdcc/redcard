<?php 


require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';
$con = new assets\libs\connection();

use admin\libs\Reservacion;
use \admin\libs\Academia;
use \admin\libs\Comprobantes;

$comprobante = new Comprobantes($con);
$academia = new Academia($con);
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


	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'cargarcomprobante'){

		$response = array(
			
			'data'     =>''
			 );

		$resultado = $comprobante->getSolicitudes();

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

	
	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'grafica-reservaciones-mensuales'){
		$resultado = $reservacion->getDataReservacacionAnualMensual();
		echo json_encode($resultado);
	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'cargarcategoria'){

		$response = array(
			
			'data'     =>''
			 );

		$resultado = $academia->getCategorias();
		$response['data'] = $resultado;
		echo json_encode($response);

	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'new-categories'){

		$response = array(
			'peticion' =>false,
			'id'       => 0
		);

		$resultado = $academia->newCategoria($_POST['categories']);
		
		if($resultado > 0){
			$response['peticion'] = true;
			$response['id'] = $resultado;
		}

		echo json_encode($response);

	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'eliminar-categories'){

		$response = array(
			'peticion' =>false
		);

		$resultado = $academia->deleteCategorie($_POST['idcategorie']);
		
		if($resultado){
			$response['peticion'] = true;
		}

		echo json_encode($response);

	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'busqueda_clases'){

		$response = array(
			'peticion' => false,
			'datos'    => null
		);

		$resultado = $academia->search_clases($_POST['busqueda']);
		
		if(count($resultado) > 0){
			$response['peticion'] = true;
			$response['datos'] = $resultado;
		}

		echo json_encode($response);

	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'eliminarClass'){

		$response = array(
			'peticion' => false,
			
		);

		$resultado = $academia->delete_class($_POST['idclass']);
		
		if($resultado){
			$response['peticion'] = true;
		}

		echo json_encode($response);

	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'capturar_class'){

		$response = array(
			'peticion' => false,
			'datos' => false
		);

		$resultado = $academia->getClass($_POST['id_class']);
		
		if($resultado){
			$response['peticion'] = true;
			$response['datos'] = $resultado;
		}

		echo json_encode($response);

	}
}



 ?>