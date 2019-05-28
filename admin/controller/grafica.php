<?php 

require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';
$con = new assets\libs\connection();

use admin\libs\Home;
use admin\libs\Iata;

$home = new Home($con);

$iata = new Iata($con);

/**
 * 	Controladores para peticiones ajax para graficos... 
 * 
 */

if(isset($_POST['grafica']) && $_POST['grafica'] == 'ventaspromediopornegocios'){

	if(isset($_POST['f1'])){

		$f1 = str_replace('+', ' ', $_POST['f1']);
		$f2 = str_replace('+', ' ', $_POST['f2']);
		$result = $home->getVentasPromedioNegocios($f1,$f2);

		if($result){
			$response = array();
			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				$promedio = $row['promedio'];


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
		
			echo json_encode($response);

		}
	}else{
		$result = $home->getVentasPromedioNegocios();

		if($result){
			$response = array();
			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				$promedio = number_format((float)$row['promedio'],2,'.',',');


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
		
			echo json_encode($response);

		}
	}

	}

if(isset($_POST['grafica']) && $_POST['grafica'] == 'comisionperfiles'){


	if(isset($_POST['f1'])){

		$f1 = str_replace('+', ' ', $_POST['f1']);
		$f2 = str_replace('+', ' ', $_POST['f2']);
		
		$result = $home->getComisionPerfiles($f1,$f2);

		if($result){
			$response = array();
			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				$comision = number_format((float)$row['total'],2,'.',',');
				

				 settype($comision,'float');
				$response[] = array('name'=>$row['perfil'],'y'=>$comision);
	
			}
		
			echo json_encode($response);

		}

	}else{
		$result = $home->getComisionPerfiles();

		if($result){
			$response = array();
			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				$comision = number_format((float)$row['total'],2,'.',',');
				

				 settype($comision,'float');
				$response[] = array('name'=>$row['perfil'],'y'=>$comision);
	
			}
		
			echo json_encode($response);
	}

	}
}

	if(isset($_POST['grafica']) && $_POST['grafica'] == 'totalconsumohuesped'){


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

	if(isset($_POST['grafica']) && $_POST['grafica'] == 'totalregalosusuarios'){


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

		}else{

		}

	}

if(isset($_POST['grafica']) && $_POST['grafica'] == 'perfilesnuevos'){

		$result = $home->getNuevosPerfiles();

		if($result){
			$response = array();
			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				$usuarios = number_format((float)$row['usuarios'],2,'.',',');

				$perfil = $row['perfil'];
				 settype($usuarios,'float');
				$response[] = array('name'=>$perfil,'y'=>$usuarios);
	
			}
		
			echo json_encode($response);

		}
}

if(isset($_POST['newiata']) && $_POST['newiata']){

		$post = array('codigo'=>$_POST['codigo'],'aeropuerto'=>$_POST['aeropuerto'],
					'ciudad'=>$_POST['ciudad'],'estado'=>$_POST['estado']);

		$iata->registrocliente($post);

	}

 ?>