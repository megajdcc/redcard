<?php 


require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';
$con = new assets\libs\connection();

use Hotel\models\NuevoUsuario;
use Hotel\models\Reservacion;
use Hotel\models\Promotor;
use Hotel\models\ReportesVentas;
use Hotel\models\Comprobantes;




$promotor = new Promotor($con);
$reservacion = new Reservacion($con);
$newuser = new NuevoUsuario($con);
$comprobante = new Comprobantes($con);

	$reporte = new ReportesVentas($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){	

	if(isset($_POST['peticion']) && $_POST['peticion'] == 'newUser'){

		$response = array(
			'peticion' =>false ,
			'mensaje'  =>'',
			'data'     =>null
			 );


		$resultado = $newuser->setData($_POST,true);

		if(count($resultado)){
			$response['peticion'] = true;
			$response['mensaje'] = $resultado['mensaje'];
		}else{
			$response['mensaje'] = 'No se pudo realizar el registro en este momento, por favor intentelo mas tarde.';
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


	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'cargarestadocuenta'){

		$response = array(
			'data'     =>''
			 );

		$reporte->CargarData($_POST['rango1'],$_POST['rango2']);

		$resultado = $reporte->getEstadoCuenta();

		if(count($resultado) > 0){
		
			$response['data'] = $resultado;

		}


		echo json_encode($response);


	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'cargarcomprobante'){

		$response = array(
			'data'     =>''
			 );

		$reporte->CargarData();

		$resultado = $comprobante->getComprobantes();

		if(count($resultado) > 0){
		
			$response['data'] = $resultado;

		}


		echo json_encode($response);


	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'grafica-reservaciones-mensuales'){

		$resultado = $reservacion->getDataReservacacionAnualMensual();
		// $resultado = array('name'=>'Agendados','data'=> array(['Julio',2,],['Agosto',5])); EXAmple
		
		echo json_encode($resultado);

	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'guardarcargo'){

		$response = array('peticion' =>false,
							'idcargo' =>0,
							'newcargo' =>null);

		$resultado = $promotor->newCargo($_POST['cargo']);
	
		if($resultado){
			$response['peticion'] = true;
			$response['idcargo'] = $resultado['idcargo'];
			$response['newcargo']= $resultado['newcargo']; 
		}
		echo json_encode($response);

	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'listarcargos'){

		$response = array('data'=>'');
		$resultado = $promotor->ListarCargos();

		$response['data'] = $resultado;
		echo json_encode($response);

	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'eliminarcargo'){

		$response = array('peticion'=>false);


		$resultado = $promotor->eliminarcargo($_POST['id']);

		if($resultado){
			$response['peticion'] = true;
		}

		echo json_encode($response);

	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'cargarcargos'){

		$response = array('peticion'=>false,'data'=>array());


		$resultado = $promotor->cargarcargos();

		if(count($resultado)){
			$response['peticion'] = true;
			$response['data']  = $resultado;
		}

		echo json_encode($response);

	} 

	if(isset($_POST['peticion']) && $_POST['peticion'] == 'grabarpromotor'){

		$response = array(
				'peticion'=>false,
				'mensaje'=>'',
			);


		$resultado = $promotor->newPromotor($_POST);

	
		if($resultado['peticion']){
			$response['peticion'] = true;
			$response['mensaje']  = $resultado['mensaje'];
		}else{
			$response['mensaje']  = $resultado['mensaje'];
		}

		echo json_encode($response);

	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'modificarpromotor'){

		$response = array(
				'peticion'=>false,
				'mensaje'=>'',
			);


		$resultado = $promotor->updatePromotor($_POST);

	
		if($resultado['peticion']){
			$response['peticion'] = true;
			$response['mensaje']  = $resultado['mensaje'];
		}else{
			$response['mensaje']  = $resultado['mensaje'];
		}

		echo json_encode($response);

	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'promotores'){

		$resultado = array('data'=>$promotor->getPromotores());

		echo json_encode($resultado);

	}
	else if(isset($_POST['peticion']) && $_POST['peticion'] == 'activarpromotor'){

		$response = array('peticion' => false);

		if($promotor->activarpromotor($_POST['id'])){
				$response['peticion'] = true;
		}

		echo json_encode($response);

	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'desactivarpromotor'){

		$response = array('peticion' => false);

		if($promotor->desactivarpromotor($_POST['id'])){
				$response['peticion'] = true;
		}

		echo json_encode($response);

	}else if(isset($_POST['peticion']) && $_POST['peticion'] == 'eliminarpromotor'){

		$response = array('peticion' => false,'mensaje' => '');

		if($promotor->eliminarpromotor($_POST['id'])){
				$response['peticion'] = true;
				$response['mensaje'] = "Se ha eliminado correctamente al promotor";
		}else{
			$response['peticion'] = true;
			$response['mensaje'] = "No se puede eliminar al promotor debido  a que tiene comisiones activas, notifiquele que retire el total de sus comisiones para poder eliminarlo";
		}

		echo json_encode($response);

	}



	// VERIFICACION DE EMAIL USUARIO PROMOTOR 
	// 
	// 
		
	if(isset($_POST['peticion']) && $_POST['peticion'] == 'verificarcuentapromotor'){

		$result = $promotor->isPromotor($_POST['emailpromotor']);


		if($result['peticion']){
			if($result['verificado']){
				$result['mensaje'] = "Estimado! ya el correo esta verificado, intente recuperando la contraseña, o solicite al administrador del hotel restauración de clave.";
			}
		}

		echo json_encode($result);

	}


	// PETICION PARA MODIFICAR DATOS DE PROMOTOR
	if(isset($_POST['peticion']) && $_POST['peticion'] == 'updatepromotor'){

		$response = array('peticion' =>false);

		$result = $promotor->updatePromotorPanel($_POST);


		if($result){
			$response['peticion'] = true;
		}

		echo json_encode($response);

	}



	// PETICION PARA MODIFICAR LA CONTRASENA
	if(isset($_POST['peticion']) && $_POST['peticion'] == 'updatepassword'){

		$result = $promotor->updatepassword($_POST);

		echo json_encode($result);

	}


	// PETICION PARA MODIFICAR O ESTABLECER LOS DATOS DE COMISION
	if(isset($_POST['peticion']) && $_POST['peticion'] == 'datoscomision'){

		$result = $promotor->datoscomision($_POST);

		echo json_encode($result);

	}


	// PETICION PARA CAPTURAR LOS DATOS DE PAGO DE COMISION
	if(isset($_POST['peticion']) && $_POST['peticion'] == 'getDatosPagoComision'){


		$response = array('peticion' =>false,'data'=>null);

		$result = $promotor->getDatosPagoComision();

		if(count($result) > 0 ){
			$response['peticion'] = true;
			$response['data'] = $result;
		}

		echo json_encode($response);

	}



}



 ?>