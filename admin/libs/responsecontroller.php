<?php 
namespace admin\libs;
 require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 
use assets\libs\connection;

use PDO;

/**
 * @author Crespo Jhonatan
 * @since 22/04/2019
 */
class responsecontroller 
{
	
	private $con,$solicitud;
	private $perfil;

	private $hotel       = array();
	private $pago        = array();
	private $solicitante = array();

	private $archivo = null;

	function __construct(int $solicitud = null,string $perfil = null, $archivo = null){
		$con = new connection();
		$this->con = $con->con;
		$this->solicitud = $solicitud;
		$this->perfil = $perfil;

		$this->cargardatossolicitante();
		$this->cargardatoshotel();
		$this->cargardatospago();

	}


	private function cargardatossolicitante(){

		switch ($this->perfil) {
			case 'Hotel':
					
					$query = "select r.mensaje,u.nombre,u.apellido, u.username, u.imagen, resp.telefono_fijo as telefonofijo, resp.telefono_movil as telefonomovil
					from retiro as r join usuario as u on r.id_usuario_solicitud = u.id_usuario 
					join hotel as h on r.id_hotel = h.id	
					join responsableareapromocion as resp on h.id_responsable_promocion = resp.id
					where  r.id = :solicitud";

					$stm = $this->con->prepare($query);
					$stm->bindParam(':solicitud',$this->solicitud,PDO::PARAM_INT);
					$stm->execute();
					$this->solicitante = $stm->fetchALL(PDO::FETCH_ASSOC);
				
			break;

			case 'Franquiciatario':
					$query = "select r.mensaje, u.nombre,u.apellido, u.username, u.imagen, f.telefonofijo, f.telefonomovil
					from retiro as r join usuario as u on r.id_usuario_solicitud = u.id_usuario 
					join franquiciatario as f on r.id_franquiciatario = f.id 
					where r.id = :solicitud";

					$stm = $this->con->prepare($query);
					$stm->bindParam(':solicitud',$this->solicitud,PDO::PARAM_INT);
					$stm->execute();
					$this->solicitante = $stm->fetchALL(PDO::FETCH_ASSOC);

			break;

			case 'Referidor':
					$query = "select r.mensaje, u.nombre, u.apellido, u.username, u.imagen, rf.telefonofijo, rf.telefonomovil
					from retiro as r join usuario as u on r.id_usuario_solicitud = u.id_usuario 
					join referidor as rf on r.id_referidor = rf.id 
					where r.id = :solicitud";

					$stm = $this->con->prepare($query);
					$stm->bindParam(':solicitud',$this->solicitud,PDO::PARAM_INT);
					$stm->execute();
					$this->solicitante = $stm->fetchALL(PDO::FETCH_ASSOC);
			break;

			case 'Promotor':
					$query = "select r.mensaje, p.nombre, p.apellido, p.username, '' as imagen, p.telefono as telefonofijo, '' as telefonomovil
					from retiro as r join promotor as p on r.id_promotor = p.id 
					where r.id = :solicitud";

					$stm = $this->con->prepare($query);
					$stm->bindParam(':solicitud',$this->solicitud,PDO::PARAM_INT);
					$stm->execute();
					$this->solicitante = $stm->fetchALL(PDO::FETCH_ASSOC);
			break;


			default:
				return;
				break;
		}
	
	} 

	private function cargardatoshotel(){
		switch ($this->perfil) {
			case 'Hotel':
					
					$query = "select h.nombre as nombrehotel, h.sitio_web,h.direccion, c.ciudad,e.estado,p.pais
						from retiro as r join hotel as h on r.id_hotel = h.id
						join ciudad as c on h.id_ciudad = c.id_ciudad 
						join estado as e on c.id_estado = e.id_estado
						join pais as p on e.id_pais = p.id_pais
						where r.id = :solicitud";

					$stm = $this->con->prepare($query);
					$stm->bindParam(':solicitud',$this->solicitud,PDO::PARAM_INT);
					$stm->execute();
					$this->hotel = $stm->fetchALL(PDO::FETCH_ASSOC);
				
			break;

			case 'Franquiciatario':
					$query = "select h.nombre as nombrehotel, h.sitio_web,h.direccion, c.ciudad,e.estado,p.pais
						from retiro as r join franquiciatario as fr on r.id_franquiciatario = fr.id
						join hotel as h on fr.codigo_hotel = h.codigo
						join ciudad as c on h.id_ciudad = c.id_ciudad 
						join estado as e on c.id_estado = e.id_estado
						join pais as p on e.id_pais = p.id_pais
						where r.id = :solicitud";

					$stm = $this->con->prepare($query);
					$stm->bindParam(':solicitud',$this->solicitud,PDO::PARAM_INT);
					$stm->execute();
					$this->hotel = $stm->fetchALL(PDO::FETCH_ASSOC);

			break;

			case 'Referidor':
					$query = "select h.nombre as nombrehotel, h.sitio_web,h.direccion, c.ciudad,e.estado,p.pais
						from retiro as r join referidor as rf on r.id_referidor = rf.id
						join hotel as h on rf.codigo_hotel = h.codigo
						join ciudad as c on h.id_ciudad = c.id_ciudad 
						join estado as e on c.id_estado = e.id_estado
						join pais as p on e.id_pais = p.id_pais
						where r.id = :solicitud";

					$stm = $this->con->prepare($query);
					$stm->bindParam(':solicitud',$this->solicitud,PDO::PARAM_INT);
					$stm->execute();
					$this->hotel = $stm->fetchALL(PDO::FETCH_ASSOC);
			break;

			case 'Promotor':
					$query = "select h.nombre as nombrehotel, h.sitio_web,h.direccion, c.ciudad,e.estado,pa.pais
						from retiro as r join promotor as p on r.id_promotor = p.id
						join hotel as h on p.id_hotel = h.id
						join ciudad as c on h.id_ciudad = c.id_ciudad 
						join estado as e on c.id_estado = e.id_estado
						join pais as pa on e.id_pais = pa.id_pais
						where r.id = :solicitud";

					$stm = $this->con->prepare($query);
					$stm->bindParam(':solicitud',$this->solicitud,PDO::PARAM_INT);
					$stm->execute();
					$this->hotel = $stm->fetchALL(PDO::FETCH_ASSOC);
			break;

			default:
				return;
				break;
		}
	}

	private function cargardatospago(){

		switch ($this->perfil) {
			case 'Hotel':

			$datos = array('banco' =>'','cuenta'=>'','clabe'=>'','swift'=>'','bancotarjeta'=>'','numerotarjeta'=>'','email_paypal'=>'');
					
				$query = "select p.banco as banco, p.cuenta, p.clabe, p.swift, p.banco_tarjeta as bancotarjeta, p.numero_tarjeta as numerotarjeta,p.email_paypal
					from retiro as r join hotel as h  on r.id_hotel = h.id	
					join datospagocomision as p on h.id_datospagocomision = p.id
					where r.id = :solicitud";

					$stm = $this->con->prepare($query);
					$stm->bindParam(':solicitud',$this->solicitud,PDO::PARAM_INT);
					$stm->execute();
					if($stm->rowCount() > 0){
					while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
						$datos['banco']          = $row['banco'];
						$datos['cuenta']         = $row['cuenta'];
						settype($row['clabe'],'string');
						$datos['clabe']          = $row['clabe'];
						$datos['swift']          = $row['swift'];
						$datos['bancotarjeta']  = $row['bancotarjeta'];
						$datos['numerotarjeta'] = $row['numerotarjeta'];
						$datos['email_paypal']   = $row['email_paypal'];
					}
				}else{
					$this->pago = array();
				}

					$this->pago[] = $datos;
					
				
			break;

			case 'Franquiciatario':
				$datos = array('banco' =>'','cuenta'=>'','clabe'=>'','swift'=>'','bancotarjeta'=>'','numerotarjeta'=>'','email_paypal'=>'');
					$query = "select p.banco as banco, p.cuenta, p.clabe, p.swift, p.banco_tarjeta as bancotarjeta, p.numero_tarjeta as numerotarjeta,p.email_paypal
					from retiro as r join franquiciatario as fr  on r.id_franquiciatario = fr.id	
					join datospagocomision as p on fr.id_datospagocomision = p.id
					where r.id = :solicitud";

					$stm = $this->con->prepare($query);
					$stm->bindParam(':solicitud',$this->solicitud,PDO::PARAM_INT);
					$stm->execute();

					if($stm->rowCount() > 0){
					while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
						$datos['banco']          = $row['banco'];
						$datos['cuenta']         = $row['cuenta'];
						settype($row['clabe'],'string');
						$datos['clabe']          = $row['clabe'];
						$datos['swift']          = $row['swift'];
						$datos['bancotarjeta']  = $row['bancotarjeta'];
						$datos['numerotarjeta'] = $row['numerotarjeta'];
						$datos['email_paypal']   = $row['email_paypal'];
					}
				}else{
					$this->pago = array();
				}

					$this->pago[] = $datos;


					

			break;

			case 'Referidor':
					$datos = array('banco' =>'','cuenta'=>'','clabe'=>'','swift'=>'','bancotarjeta'=>'','numerotarjeta'=>'','email_paypal'=>'');
					$query = "select p.banco as banco, p.cuenta, p.clabe, p.swift, p.banco_tarjeta as bancotarjeta, p.numero_tarjeta as numerotarjeta,p.email_paypal
					from retiro as r join referidor as rf  on r.id_referidor = rf.id	
					join datospagocomision as p on rf.id_datospagocomision = p.id
					where r.id = :solicitud";

					$stm = $this->con->prepare($query);
					$stm->bindParam(':solicitud',$this->solicitud,PDO::PARAM_INT);
					$stm->execute();
						if($stm->rowCount() > 0){
						while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
						$datos['banco']          = $row['banco'];
						$datos['cuenta']         = $row['cuenta'];
						settype($row['clabe'],'string');
						$datos['clabe']          = $row['clabe'];
						$datos['swift']          = $row['swift'];
						$datos['bancotarjeta']  = $row['bancotarjeta'];
						$datos['numerotarjeta'] = $row['numerotarjeta'];
						$datos['email_paypal']   = $row['email_paypal'];
						}
						}else{
						$this->pago = array();
						}
						
						$this->pago[] = $datos;
			break;

			case 'Promotor':
						$datos = array('banco' =>'','cuenta'=>'','clabe'=>'','swift'=>'','bancotarjeta'=>'','numerotarjeta'=>'','email_paypal'=>'');

					$query = "select dp.banco as banco, dp.cuenta, dp.clabe, dp.swift, dp.banco_tarjeta as bancotarjeta, dp.numero_tarjeta as numerotarjeta,dp.email_paypal
					from retiro as r join promotor as p  on r.id_promotor = p.id	
					left join datospagocomision as dp on p.id_datopagocomision = dp.id
					where r.id = :solicitud";

					$stm = $this->con->prepare($query);
					$stm->bindParam(':solicitud',$this->solicitud,PDO::PARAM_INT);
					$stm->execute();

					if($stm->rowCount() > 0){
					while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
						$datos['banco']          = $row['banco'];
						$datos['cuenta']         = $row['cuenta'];
						settype($row['clabe'],'string');
						$datos['clabe']          = $row['clabe'];
						$datos['swift']          = $row['swift'];
						$datos['bancotarjeta']  = $row['bancotarjeta'];
						$datos['numerotarjeta'] = $row['numerotarjeta'];
						$datos['email_paypal']   = $row['email_paypal'];
					}
				}else{
					$this->pago = array();
				}

					$this->pago[] = $datos;
			break;
			default:
				return;
				break;
		}

	}

	public function getDatos(){
		
		$response = array(
			"result"       => false,
			"solicitante"  => null,
			"hotel"        => null,
			"pagocomision" => null
		);

		if(count($this->solicitante) > 0 && count($this->hotel) > 0){

			$response['result'] = true;
			$response['solicitante'] = $this->solicitante;
			$response['hotel'] = $this->hotel;
			$response['pagocomision'] = $this->pago;
		}
		return  json_encode($response);
	}

	public function ActualizarRetiro(){

		$nombre_temporal = $_FILES['recibo']['tmp_name'];
		$nombre = $_FILES['recibo']['name'];
		move_uploaded_file($nombre_temporal, HOST.'/assets/img/recibodepago/'.$nombre);
	}


	 
}
if($_SERVER["REQUEST_METHOD"] == "POST"){

	if(isset($_POST['solicitud'])){

		$response = new responsecontroller($_POST['solicitud'], $_POST['perfil']);
		echo $response->getDatos();
	}
	if(isset($_POST['subir'])){

		$response = new responsecontroller($_POST['solicitud'], $_POST['perfil'], $_POST['archivo']);

		$response->ActualizarRetiro();
	}
}

 ?>