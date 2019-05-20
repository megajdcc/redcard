<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';
use assets\libs\connection;
$con = new connection();

use admin\libs\Home;
use admin\libs\Iata;
use assets\libs\user_signup;
use Hotel\models\AfiliarHotel;
use Franquiciatario\models\AfiliarFranquiciatario;
use Referidor\models\AfiliarReferidor;
use admin\libs\DetallesSolicitud;


$solicitud = new DetallesSolicitud($con);

$reg = new user_signup($con);
$home = new Home($con);
$iata = new Iata($con);

$hotel = new AfiliarHotel($con);
$franquiciatario = new AfiliarFranquiciatario($con);
$referidor = new AfiliarReferidor($con);

/**
 * 	Controladores para peticiones ajax para registros nuevos...
 * 
 */

if($_SERVER["REQUEST_METHOD"] == "POST"){


//CAPTURA DE DATOS DE PERFILES>>>


if(isset($_POST['solicitudhotel'])){

	$response = array('peticion' => false,
						'mensaje' => '',
						'datos'=>array());

	$result = $solicitud->CargarHotel($_POST['solicitudhotel']);

	if($result){

		$response['peticion'] = true;
		$response['mensaje'] = "Hotel encontrado";
		$response['datos'] = $solicitud->getDatos('Hotel'); 
	}

	echo json_encode($response);


}

if(isset($_POST['solicitudfranquiciatario'])){
	
	$response = array('peticion' => false,
						'mensaje' => '',
						'datos'=>array());

	$result = $solicitud->CargarFranquiciatarioAdmin($_POST['solicitudfranquiciatario']);

	if($result){

		$response['peticion'] = true;
		$response['mensaje'] = "Franquiciatario encontrado";
		$response['datos'] = $solicitud->getDatos('Franquiciatario'); 
	}

	echo json_encode($response);


}

if(isset($_POST['solicitudcodigo'])){

	if(isset($_POST['perfil']) &&  $_POST['perfil'] == 'Franquiciatario'){
		$response = array('peticion' => false,
							'mensaje' => '');

		$nrosolicitud = $_POST['solicitud'];
		$codigo = $_POST['codigo'];

		$solicitud->CargarFranquiciatarioAdmin($nrosolicitud);


		$result = $solicitud->crearcodigo('Franquiciatario', $codigo);
		
		if($result){

			$response['peticion'] = true;
			$response['mensaje'] = "Codigo Generado";
			
		}

		echo json_encode($response);
	}else{
						$response = array('peticion' => false,
						'mensaje' => '');
						
						$nrosolicitud = $_POST['solicitud'];
						$codigo = $_POST['codigo'];
						
						$solicitud->CargarHotel($nrosolicitud);
						
						
						$result = $solicitud->crearcodigo('Hotel', $codigo);
						
						if($result){
						
						$response['peticion'] = true;
						$response['mensaje'] = "Codigo Generado";
						
						}
						
						echo json_encode($response);
	}



}

// ACCIONES PARA REGSITRAR HOTEL
	if(isset($_POST['form-hotel'])){

		$response = array(
		'peticion'           => false,
		'datosinvalidos'     => false,
		'usuario_registrado' => false,
		'hotel_registrado'   => false,
		'pago_registrado'    => false,
		'mensaje'            =>"",
		'nrosolicitud'       =>null,
		'nombrehotel'        =>null,
		'codigoiata'         =>null,
		'id_hotel'           =>null);
			
			$datosusuario = array(	'username'=>$_POST['username'],
									'email'           =>$_POST['emailuser'],
									'password'        =>$_POST['password'],
									'password-retype' =>$_POST['password-retype'],
									'referral'        =>$_SESSION['user']['id_usuario']);


			$datohotel = array('nombre'=>$_POST['nombre'],
								'iata'                 =>$_POST['iata'],
								'website'              =>$_POST['website'],
								'direccion'            =>$_POST['direccion'],
								'codigopostal'         =>$_POST['codigopostal'],
								'pais'                 =>$_POST['pais'],
								'estado'               =>$_POST['estado'],
								'ciudad'               =>$_POST['ciudad'],
								'latitud'              =>$_POST['latitud'],
								'longitud'             =>$_POST['longitud'],
								'nombre_responsable'   =>$_POST['nombre_responsable'],
								'apellido_responsable' =>$_POST['apellido_responsable'],
								'email'                =>$_POST['email'],
								'cargo'                =>$_POST['cargo'],
								'telefonofijo'         =>$_POST['telefonofijo'],
								'movil'                =>$_POST['movil']);


			if($_POST['pago'] == false){

				$datopago = null;
			}else{

				$datopago = array('nombre_banco'=>$_POST['nombre_banco'],
								'cuenta'               =>$_POST['cuenta'],
								'clabe'                =>$_POST['clabe'],
								'swift'                =>$_POST['swift'],
								'nombre_banco_targeta' =>$_POST['nombre_banco_tarjeta'],
								'numero_targeta'       =>$_POST['numero_targeta'],
								'email_paypal'         =>$_POST['email_paypal']);

			}
			

			$result = $reg->setData($datosusuario,'Admin');
			

			if($result > 0){
				$response['usuario_registrado'] = true;
				
				$result = $hotel->set_data($datohotel,$datopago,$result);

				if($result){
					$response['peticion'] = true;
					$response['hotel_registrado'] = true;
					$response['pago_registrado'] = true;
					$response['mensaje'] = "Hotel registrado con exito, Si Desea Genere el Codigo de hotel y adjudique su comision de una vez...";



					$datos = $hotel->capturarultimo();

					
					$response['nrosolicitud'] = $datos[0]['solicitud'];
					$response['nombrehotel']  = $datos[0]['nombrehotel'];
					$response['codigoiata']   = $datos[0]['codigo'];
					$response['id_hotel']   = $datos[0]['idhotel'];


				}else{
					$response['peticion']           = true;
					$response['mensaje'] = "El registro no tuvo exito...";
				}
			
			}else{
				$response['peticion']           = true;
				$response['usuario_registrado'] = false;
			}

				echo json_encode($response);
	}


//ACCIONES PARA REGISTRAR FRANQUICIATARIO
	if(isset($_POST['form-franquiciatario'])){

		$response = array(
		'peticion'           => false,
		'datosinvalidos'     => false,
		'usuario_registrado' => false,
		'hotel_registrado'   => false,
		'pago_registrado'    => false,
		'mensaje'            =>"",
		'nrosolicitud'       =>null,
		'nombrehotel'        =>null,
		'codigoiata'         =>null,
		'id_hotel'           =>null,
		'id_franquiciatario' =>null);
			
			$datosusuario = array(	'username'=>$_POST['username'],
									'email'           =>$_POST['emailuser'],
									'password'        =>$_POST['password'],
									'password-retype' =>$_POST['password-retype'],
									'referral'        =>$_SESSION['user']['id_usuario']);


			$datohotel = array('nombrehotel'=>$_POST['nombrehotel'],
								'iata'                 =>$_POST['iata'],
								'website'              =>$_POST['website'],
								'direccion'            =>$_POST['direccion'],
								'codigopostal'         =>$_POST['codigopostal'],
								'pais'                 =>$_POST['pais'],
								'estado'               =>$_POST['estado'],
								'ciudad'               =>$_POST['ciudad'],
								'nombre'               =>$_POST['nombre'],
								'apellido'             =>$_POST['apellido'],
								'emailfranquiciatario' =>$_POST['emailfranquiciatario'],
								'telefonofijo'         =>$_POST['telefonofijo'],
								'telefonomovil'        =>$_POST['movil']
							);


			if($_POST['pago'] == false){

				$datopago = null;
			}else{

				$datopago = array('nombre_banco'=>$_POST['nombre_banco'],
								'cuenta'               =>$_POST['cuenta'],
								'clabe'                =>$_POST['clabe'],
								'swift'                =>$_POST['swift'],
								'nombre_banco_targeta' =>$_POST['nombre_banco_tarjeta'],
								'numero_targeta'       =>$_POST['numero_targeta'],
								'email_paypal'         =>$_POST['email_paypal']);

			}
			

			$result = $reg->setData($datosusuario,'Admin');
			

			if($result > 0){
				$response['usuario_registrado'] = true;
				
				$result = $franquiciatario->set_data($datohotel,$datopago,$result);

				if($result){
					$response['peticion'] = true;
					$response['hotel_registrado'] = true;
					$response['pago_registrado'] = true;
					$response['mensaje'] = "Franquiciatario registrado con exito, Si Desea Genere el Codigo de hotel y adjudique su comision de una vez...";

					$datos = $franquiciatario->capturarultimo($franquiciatario->ultimohotel);

					
					$response['nrosolicitud'] = $datos[0]['solicitud'];
					$response['nombrehotel']  = $datos[0]['nombrehotel'];
					$response['codigoiata']   = $datos[0]['codigo'];
					$response['id_hotel']   = $datos[0]['idhotel'];
					$response['id_franquiciatario']   = $datos[0]['idfranquiciatario'];


				}else{
					$response['peticion']           = true;
					$response['mensaje'] = "El registro no tuvo exito...";
				}
			
			}else{
				$response['peticion']           = true;
				$response['usuario_registrado'] = false;
			}

				echo json_encode($response);
	}


	//ACCIONES PARA REGISTRAR REFERIDOR
	if(isset($_POST['form-referidor'])){

		$response = array(
		'peticion'           => false,
		'datosinvalidos'     => false,
		'usuario_registrado' => false,
		'hotel_registrado'   => false,
		'pago_registrado'    => false,
		'mensaje'            =>"",
		'nrosolicitud'       =>null,
		'nombrehotel'        =>null,
		'codigoiata'         =>null,
		'id_hotel'           =>null,
		'id_referidor' =>null);
			
			$datosusuario = array(	'username'=>$_POST['username'],
									'email'           =>$_POST['emailuser'],
									'password'        =>$_POST['password'],
									'password-retype' =>$_POST['password-retype'],
									'referral'        =>$_SESSION['user']['id_usuario']);


			$datohotel = array('nombrehotel'=>$_POST['nombrehotel'],
								'iata'                 =>$_POST['iata'],
								'website'              =>$_POST['website'],
								'direccion'            =>$_POST['direccion'],
								'codigopostal'         =>$_POST['codigopostal'],
								'pais'                 =>$_POST['pais'],
								'estado'               =>$_POST['estado'],
								'ciudad'               =>$_POST['ciudad'],
								'nombre'               =>$_POST['nombre'],
								'apellido'             =>$_POST['apellido'],
								'telefonofijo'         =>$_POST['telefonofijo'],
								'telefonomovil'        =>$_POST['movil']
							);


			if($_POST['pago'] == false){

				$datopago = null;
			}else{

				$datopago = array('nombre_banco'=>$_POST['nombre_banco'],
								'cuenta'               =>$_POST['cuenta'],
								'clabe'                =>$_POST['clabe'],
								'swift'                =>$_POST['swift'],
								'nombre_banco_targeta' =>$_POST['nombre_banco_tarjeta'],
								'numero_targeta'       =>$_POST['numero_targeta'],
								'email_paypal'         =>$_POST['email_paypal']);

			}
			

			$result = $reg->setData($datosusuario,'Admin');
			

			if($result > 0){
				$response['usuario_registrado'] = true;
				
				$result = $referidor->set_data($datohotel,$datopago,$result);

				if($result){
					$response['peticion'] = true;
					$response['hotel_registrado'] = true;
					$response['pago_registrado'] = true;
					$response['mensaje'] = "Referidor registrado con exito, Si Desea Genere el Codigo de hotel y adjudique su comision de una vez...";

					$datos = $referidor->capturarultimo($referidor->ultimohotel);

					
					$response['nrosolicitud'] = $datos[0]['solicitud'];
					$response['nombrehotel']  = $datos[0]['nombrehotel'];
					$response['codigoiata']   = $datos[0]['codigo'];
					$response['id_hotel']     = $datos[0]['idhotel'];
					$response['id_referidor'] = $datos[0]['idreferidor'];


				}else{
					$response['peticion']           = true;
					$response['mensaje'] = "El registro no tuvo exito...";
				}
			
			}else{
				$response['peticion']           = true;
				$response['usuario_registrado'] = false;
			}

				echo json_encode($response);
	}


	if(isset($_POST['solicitud']) && $_POST['solicitud'] == 'eliminar'){

		if($_POST['perfil'] == 'Franquicitario'){
				$response = array('peticion' =>false,'mensaje'=>'');
				$solicitud->CargarFranquiciatarioAdmin($_POST['franquiciatario']);


				$result  = $solicitud->EliminarSolicitudFranquiciatario();
				if($result){
				$response['peticion'] = true;
				$response['mensaje'] = 'Eliminacion Exitosa';
				}else{
				$response['peticion'] = false;
				$response['mensaje'] = 'No se pudo eliminar a este hotel Intente Eliminarlo mas tarde...';
				}
				
				echo json_encode($response);
		}else{

			$response = array('peticion' =>false,'mensaje'=>'');
			
			$result  = $solicitud->EliminarSolicitud('Hotel',$_POST['hotel']);
			if($result){
			$response['peticion'] = true;
			$response['mensaje'] = 'Eliminacion Exitosa';
			}else{
			$response['peticion'] = false;
			$response['mensaje'] = 'No se pudo eliminar a este hotel Intente Eliminarlo mas tarde...';
			}
			
			echo json_encode($response);
		}

		
	
	}


	if(isset($_POST['actualizar-hotel'])){
		$response = array('peticion' => false ,
						'mensaje'=>null);

		$result = $solicitud->cargarDatosActualizacion($_POST,$_POST['solicitud']);

		if($result){
			$response['peticion'] = true;
			$response['mensaje'] ="Hotel Actualizado exitosamente...";
		}else{

			$response['peticion'] = false;
			$response['mensaje'] ="Hotel no se pudo actualizar, intente mas tarde...";

		}
		echo json_encode($response);
	}

	if(isset($_POST['actualizar-franquiciatario'])){
		$response = array('peticion' => false ,
						'mensaje'=>null);

		$result = $solicitud->cargarDatosActualizacionFranquiciatario($_POST,$_POST['solicitud']);

		if($result){

			$response['peticion'] = true;
			$response['mensaje'] ="Franquiciatario Actualizado exitosamente...";

		}else{

			$response['peticion'] = false;
			$response['mensaje'] ="Franquicitario no se pudo actualizar, intente mas tarde...";

		}
		echo json_encode($response);
	}

}
	

 ?>