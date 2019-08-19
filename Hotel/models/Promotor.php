<?php 

namespace Hotel\models;

require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as mailerexception;
use \assets\libs\connection as conexion;
use PDO;


/**
 *
 * @author Crespo Jhonatan
 * @since 06/08/2019
 */
class Promotor 
{
	
	private $conexion = null;
	private $iduser   = null;
	private $hotel = array(
		'id'     =>null,
		'nombre' =>''
	);

	private $errors = array();

	private $promotor = array(	
								'nombre'   =>'',
								'apellido' =>'',
								'telefono' =>null,
								'username' =>'',
								'email'    =>'',
								'cargo'    =>'',
								'id_cargo' =>'',
								'activo'   =>false,
								'id'=>null,
								'comision'=>0,
								);

	
	private $error = array('notificacion'=>'');
	function __construct(conexion $conexion,int $idpromotor = null)
	{
		$this->conexion = $conexion->con;
		if(isset($_SESSION['user'])){
			$this->iduser = $_SESSION['user']['id_usuario'];
			
		}

		if(isset($_SESSION['id_hotel'])){
			$this->hotel['id'] = $_SESSION['id_hotel'];
		}


		if(!is_null($idpromotor)){
			$this->promotor['id'] = $idpromotor;
			$this->cargarPromotor();
		}

	}


	public function registrarPass(array $datos){
		
		$pass1 = $datos['pass1'];
		$pass2 = $datos['pass2'];
		$email = $datos['email'];

		if($pass1 == $pass2){

			$this->IngresarPassword($pass1,$email);

		}else{

			$this->error['notificacion'] = "Las contraseña no son iguales por favor verifique!.";
			header('location:'.Host.'/Hotel/login.php?email='.$email);
			die();
		
		}	

	}


	private function IngresarPassword(string $pass,string $email){

		$this->conexion->beginTransaction();
		$contrasena = md5($pass);

		$sql = 'UPDATE promotor set contrasena = :pass where email =:emailp';

		try {
			$stm = $this->conexion->prepare($sql);
			$stm->bindParam(':pass',$contrasena,PDO::PARAM_STR);
			$stm->bindParam(':emailp',$email,PDO::PARAM_STR);
			$stm->execute();
			$this->conexion->commit();
		} catch (\PDOException $e) {
			$this->conexion->rollBack();
			$this->error_log(__METHOD__,__LINE__,$e->getMessage());
			$_SESSION['notificacion']['info'] = 'No se pudo realizar el registro, por favor intentelo mas tarde!';
			header('location: '.HOST.'/Hotel/login.php?email=',$email);
			die();

		}
		$_SESSION['notificacion']['success'] = 'Se ha registrado la contraseña, inicie sesión!';
		header('location: '.HOST.'/Hotel/login');
		die();
	}


	public function login(array $datos){


		$sql = "SELECT p.activo, p.id_hotel, p.id,concat(p.nombre,' ',p.apellido) as nombre, c.cargo, p.username,p.email from promotor as p
						join cargo as c on p.id_cargo = c.id
							where (p.email = :up || p.username =:up1)  AND contrasena =:pass ";

				try {
					$stm = $this->conexion->prepare($sql);
					$stm->bindParam(':up',$datos['username-email'],PDO::PARAM_STR);
					$stm->bindParam(':up1',$datos['username-email'],PDO::PARAM_STR);
					$stm->bindParam(':pass',md5($datos['password']),PDO::PARAM_STR);

					$stm->execute();
				} catch (\PDOException $e) {
					$this->error_log(__METHOD__,__LINE__,$e->getMessage());
				}

				if($stm->rowCount() > 0){

					if($row = $stm->fetch(PDO::FETCH_ASSOC)){

						$this->promotor['activo'] = $row['activo'];

						if($this->is_activo()){
							$_SESSION['promotor'] = array('username'=>$row['username'],
													'nombre' =>$row['nombre'],
													'id'     =>$row['id'],
													'cargo'  =>$row['cargo'],
													'email'  =>$row['email'],
													'hotel' => $row['id_hotel']);
						}else{
							$_SESSION['notificacion']['info'] = 'Su usuario no se encuentra activo. Solicite al encargado de negocio(Hotel) su activación.';
							header('location: '.HOST.'/Hotel/login');
							die();
						}
						
					}
					header('location: '.HOST.'/Hotel/');
					die();
				}else{
					$_SESSION['notificacion']['info'] = 'Imposible iniciar sesión.(Email,username) o contraseña incorrecta. Por favor verifique!';
					header('location: '.HOST.'/Hotel/login');
					die();
				}

	}
	
	public function getNotificacion(){
			$notificacion = null;
		if(isset($_SESSION['notificacion']['success'])){
			$notificacion .= 
			'<div class="alert alert-icon alert-dismissible alert-success" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notificacion']['success']).'
			</div>';
			unset($_SESSION['notificacion']['success']);
		}
		if(isset($_SESSION['notificacion']['info'])){
			$notificacion .= 
			'<div class="alert alert-icon alert-dismissible alert-info" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notificacion']['info']).'
			</div>';
			unset($_SESSION['notificacion']['info']);
		}
		if($this->error['notificacion']){
			$notificacion .= 
			'<div class="alert alert-icon alert-dismissible alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->error['notificacion']).'
			</div>';
		}
		return $notificacion;
	}

	private function cargarPromotor(){


		$sql = "SELECT p.activo,p.nombre,p.apellido,p.telefono,p.username,p.email,c.cargo,c.id as id_cargo, max(b.balance) as comision 
						from promotor as p join cargo as c on p.id_cargo = c.id 
						left join balance  as b on p.id = b.id_promotor
						where p.id = :promotor";


		$stm = $this->conexion->prepare($sql);
		$stm->bindParam(':promotor',$this->promotor['id'],PDO::PARAM_INT);

		$stm->execute();


		while($row = $stm->fetch(PDO::FETCH_ASSOC)){

			$this->promotor['nombre']   = $row['nombre'];
			$this->promotor['apellido'] = $row['apellido'];
			$this->promotor['telefono'] = $row['telefono'];
			$this->promotor['username'] = $row['username'];
			$this->promotor['email']    = $row['email'];
			$this->promotor['id_cargo'] = $row['id_cargo'];
			$this->promotor['cargo'] = $row['cargo'];
			$this->promotor['activo'] = $row['activo'];
			$this->promotor['comision'] = $row['comision'];

		}
	}




	public function getNombre(){

		return _safe($this->promotor['nombre']);
	}

		public function getApellido(){

		return _safe($this->promotor['apellido']);
	}

	public function getTelefono(){

		return $this->promotor['telefono'];
	}

	public function getUsername(){

		return _safe($this->promotor['username']);
	}
	public function getEmail(){

		return _safe($this->promotor['email']);
	}

	public function getId(){
		return $this->promotor['id'];
	}



	public function is_activo(){

		if($this->promotor['activo']){
			return true;
		}else{
			return false;
		}
	}

	public function getCargo(){
		return _safe($this->promotor['cargo']);
	}

	public function newPromotor(array $datos){

		$result = array('peticion'=>false,
						'mensaje'=>'');

		$this->promotor['nombre']   = _safe($datos['nombre']);
		$this->promotor['apellido'] = _safe($datos['apellido']);
		$this->promotor['telefono'] = $datos['telefono'];
		$this->promotor['username'] = _safe($datos['username']);
		$this->promotor['email']    = _safe($datos['email']);
		$this->promotor['cargo']    =$datos['cargo'];


		$sql = "INSERT INTO promotor(nombre,apellido,telefono,username,email,id_cargo,activo,id_hotel)
					values(:nombre,:apellido,:telefono,:username,:email,:cargo,:activo,:hotel)";

		if($this->conexion->inTransaction()){
			$this->conexion->rollBack();

		}

		$this->conexion->beginTransaction();

			$datos = array(':nombre'=>$this->promotor['nombre'],
						':apellido'=>$this->promotor['apellido'],
						':telefono'=>$this->promotor['telefono'],
						':username'=>$this->promotor['username'],
						':email'=>$this->promotor['email'],
						':cargo'=>$this->promotor['cargo'],
						':activo'=>false,
						':hotel'=>$this->hotel['id']);
		try {
			$stm = $this->conexion->prepare($sql);
			$stm->execute($datos);	
			$this->conexion->commit();
			$_SESSION['notificacion']['success'] = "Promotor Registrado exitosamente.!";
			$result['peticion'] = true;
			$result['mensaje'] = 'registro realizado exitosamente!';
		} catch (\PDOException $e) {
			$this->conexion->rollBack();
			$this->error_log(__METHOD__,__LINE__,$e->getMessage());
			$result['peticion'] = false;
			$result['mensaje'] = 'No se pudo realizar el registro del promotor intentelo mas tarde!';
		}
			
		return $result;
	}


	public function updatePromotor(array $datos){

		$result = array('peticion'=>false,
						'mensaje'=>'');

		$this->promotor['nombre']   = _safe($datos['nombre']);
		$this->promotor['apellido'] = _safe($datos['apellido']);
		$this->promotor['telefono'] = $datos['telefono'];
		$this->promotor['username'] = _safe($datos['username']);
		$this->promotor['id']    =$datos['id_promotor'];


		$sql = "UPDATE promotor set nombre = :nombre, apellido =:apellido,telefono =:telefono,username =:username,id_hotel=:hotel
					where id =:promotor";

		if($this->conexion->inTransaction()){
			$this->conexion->rollBack();

		}

		$this->conexion->beginTransaction();

			$datos = array(':nombre'=>$this->promotor['nombre'],
						':apellido'=>$this->promotor['apellido'],
						':telefono'=>$this->promotor['telefono'],
						':username'=>$this->promotor['username'],
						':hotel'=>$this->hotel['id'],
						':promotor'=>$this->promotor['id']);
		try {
			$stm = $this->conexion->prepare($sql);
			$stm->execute($datos);	
			$this->conexion->commit();
			$_SESSION['notificacion']['success'] = "Promotor Modificado exitosamente.!";
			$result['peticion'] = true;
			$result['mensaje'] = 'registro realizado exitosamente!';
		} catch (\PDOException $e) {
			$this->conexion->rollBack();
			$this->error_log(__METHOD__,__LINE__,$e->getMessage());
			$result['peticion'] = false;
			$result['mensaje'] = 'No se pudo realizar la modificación del promotor intentelo mas tarde!';
		}
			
		return $result;
	}

	public function updatePromotorPanel(array $datos){
		
		$this->promotor['nombre']   = _safe($datos['nombre']);
		$this->promotor['apellido'] = _safe($datos['apellido']);
		$this->promotor['telefono'] = $datos['telefono'];
		$this->promotor['username'] = _safe($datos['username']);

		$sql = "UPDATE promotor set nombre = :nombre, apellido =:apellido,telefono =:telefono,username =:username
					where id =:promotor";

		if($this->conexion->inTransaction()){
			$this->conexion->rollBack();

		}

		$this->conexion->beginTransaction();

			$datos = array(':nombre'=>$this->promotor['nombre'],
						':apellido'=>$this->promotor['apellido'],
						':telefono'=>$this->promotor['telefono'],
						':username'=>$this->promotor['username'],
						':promotor'=>$_SESSION['promotor']['id']);
		try {
			$stm = $this->conexion->prepare($sql);
			$stm->execute($datos);	
			$this->conexion->commit();
		} catch (\PDOException $e) {
			$this->conexion->rollBack();
			$this->error_log(__METHOD__,__LINE__,$e->getMessage());
			return false;
		}
			
		return true;
	}



	private function verificarContrasena(string $contrasena){

		$sql = "SELECT count(*) as cantidad from promotor where contrasena = :contrasenaa";

		$stm = $this->conexion->prepare($sql);
		$stm->bindParam(':contrasenaa',$contrasena,PDO::PARAM_STR);

		$stm->execute();

		if($row = $stm->fetch(PDO::FETCH_ASSOC)){

			if($row['cantidad'] >  0){
				return true;
			}else{
				return false;
			}
		}

	}

	public function updatepassword(array $datos){

		$result = array('peticion' => false,'mensaje'=>'');

		$passactual = md5($datos['contrasena']);
		$passnew = md5($datos['contrasena1']);	

		if($this->verificarContrasena($passactual)){

			$sql = "UPDATE promotor set contrasena =:contra where id=:promotor";

			$this->conexion->beginTransaction();
			try {

				$stm = $this->conexion->prepare($sql);
				$stm->bindParam(':contra',$passnew,PDO::PARAM_STR);
				$stm->bindParam(':promotor',$_SESSION['promotor']['id'],PDO::PARAM_INT);
				$stm->execute();
				$this->conexion->commit();

			} catch (\PDOException $e) {

				$this->error_log(__METHOD__,__LINE__,$e->getMessage());
				$this->conexion->rollBack();
				$result['mensaje'] = 'No se pudo grabar la contraseña, intentelo de nuevo mas tarde';
				return $result;

			}

			$result['peticion'] = true;
			$result['mensaje'] = 'Se ha registrado exitosamente la contraseña!';

		}else{

			$result['mensaje'] = 'La contraseña actual no coincide con la registrada en nuestro sistema, intentelo de nuevo con la correcta!.';

		}

		return $result;

	}


	public function datoscomision(array $datos){
		
		$result = array('peticion' => false,'mensaje'=>'');

		if($datos['id_pago'] > 0){
			$sql = "UPDATE datospagocomision set banco =:banco,cuenta=:cuenta,clabe=:clabe,swift=:swift,banco_tarjeta=:bt,numero_tarjeta=:nt,email_paypal=:emailpaypal where id =:iddp";

			$this->conexion->beginTransaction();
			try {
				$stm = $this->conexion->prepare($sql);

				$dato = array(
								':banco'       =>$datos['nombre_banco'],
								':cuenta'      =>$datos['cuenta'],
								':clabe'       =>$datos['clabe'],
								':swift'       =>$datos['swift'],
								':bt'          =>$datos['nombre_banco_tarjeta'],
								':nt'          =>$datos['numero_targeta'],
								':emailpaypal' =>$datos['email_paypal'],
								':iddp'        =>$datos['id_pago']

				);

				$stm->execute($dato);

				$this->conexion->commit();

			} catch (\PDOException $e) {
				$this->conexion->rollBack();
				$this->error_log(__METHOD__,__LINE__,$e->getMessage());
				$result['mensaje'] = 'No se pudo actualizar los datos, intentelo de nuevo mas tarde.!';
				return $result;
			}

			$result['peticion'] = true;
			$result['mensaje'] = 'Se ha actualizado con exito!';

			return $result;


		}else{

			$sql = "INSERT INTO datospagocomision(banco,cuenta,clabe,swift,banco_tarjeta,numero_tarjeta,email_paypal)values(:banco,:cuenta,:clabe,:swift,:bt,:nt,:emailpaypal)";

			$this->conexion->beginTransaction();
				$dato = array(
								':banco'       =>$datos['nombre_banco'],
								':cuenta'      =>$datos['cuenta'],
								':clabe'       =>$datos['clabe'],
								':swift'       =>$datos['swift'],
								':bt'          =>$datos['nombre_banco_tarjeta'],
								':nt'          =>$datos['numero_targeta'],
								':emailpaypal' =>$datos['email_paypal'],

				);

			try {
				$stm = $this->conexion->prepare($sql);
				$stm->execute($dato);
				
				
			} catch (\PDOException $e) {
				$this->conexion->rollBack();
				$this->error_log(__METHOD__,__LINE__,$e->getMessage());
				$result['mensaje'] = 'No se pudo registrar los datos, intentelo de nuevo mas tarde.!';
				return $result;

			}
			$last_id = $this->conexion->lastInsertId();

			$sql1 = "UPDATE promotor set id_datopagocomision =:dp where id =:promotor";

			try {
				$stm = $this->conexion->prepare($sql1);
				$stm->execute(array(':dp'=>$last_id,':promotor'=>$_SESSION['promotor']['id']));
				$this->conexion->commit();
			} catch (\PDOException $e) {
				$this->conexion->rollBack();
				$this->error_log(__METHOD__,__LINE__,$e->getMessage());
				$result['mensaje'] = 'No se pudo registrar los datos, intentelo de nuevo mas tarde.!';
				return $result;
			}

			$result['peticion'] = true;
			$result['mensaje'] = 'Se han registrado los datos con exito!';

			return $result;
		}

		return $result;
	}	


	private function is_verified_status_comision(){


		$sql = "SELECT id_datopagocomision from promotor where id =:promotor)";

		$stm = $this->conexion->prepare($sql);
		$stm->execute(array(':promotor'=>$_SESSION['promotor']['id']));

		if($row = $stm->fetch(PDO::FETCH_ASSOC)){
			if(!is_null($row['id_datopagocomision']) ||  $row['id_datopagocomision'] > 0){
				return true;
			}
		}

	}

	public function getDatosPagoComision(){

		$datos = array('banco' =>'','cuenta'=>'','clabe'=>'','swift'=>'','banco_tarjeta'=>'','numero_tarjeta'=>'','email_paypal'=>'');

		$sql = "SELECT dp.id,dp.banco,dp.cuenta,dp.clabe,dp.swift,dp.banco_tarjeta,dp.numero_tarjeta,dp.email_paypal from datospagocomision as dp
				join promotor as p on dp.id = p.id_datopagocomision
				where p.id = :promotor";

				$stm = $this->conexion->prepare($sql);
				$stm->bindParam(':promotor',$_SESSION['promotor']['id'],PDO::PARAM_INT);
				$stm->execute();

				if($stm->rowCount() > 0){
					while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
						$datos['banco']          = $row['banco'];
						$datos['cuenta']         = $row['cuenta'];
						settype($row['clabe'],'string');
						
						$datos['clabe']          = $row['clabe'];
						$datos['swift']          = $row['swift'];
						$datos['banco_tarjeta']  = $row['banco_tarjeta'];
						$datos['numero_tarjeta'] = $row['numero_tarjeta'];
						$datos['email_paypal']   = $row['email_paypal'];
						$datos['id']             = $row['id'];
					}
				}else{
					$datos = array();
				}

			return $datos;
	}

	public function getPromotores(){

		$sql = "SELECT p.id, upper(concat(p.nombre,' ',p.apellido)) as nombre, p.telefono,c.cargo,p.activo 
			from promotor as p 
			
			join  cargo as c on p.id_cargo = c.id
			
            where p.id_hotel = :hotel";

        $sql2 = "SELECT b.balance  as comision from balance as b where b.id_promotor = :promotor order by b.id desc limit 1";





        $stm = $this->conexion->prepare($sql);
        $stm->bindParam(':hotel',$this->hotel['id'],PDO::PARAM_INT);
        $stm->execute();

        $datos = $stm->fetchAll(PDO::FETCH_ASSOC);
        	
        foreach ($datos as $key => $value) {
        	
        	$btn = '<button class="btn btn-info editar"  data-toggle="tooltip" data-placement="rigth" data-id="'.$value['id'].'" title="Editar promotor"><i class="fa fa-edit"></i></button>';
        	$btn .= '<button class="btn btn-danger eliminar" data-id="'.$value['id'].'" data-toggle="tooltip" data-placement="rigth" title="Eliminar Promotor"><i class="fa fa-trash"></i></button>';
        	
        	if($value['activo']){
        		$datos[$key]['activo'] = '<strong class="activo">Activo</strong>';
        		$btn .= '<button class="btn btn-warning desactivar" data-id="'.$value['id'].'" data-toggle="tooltip" data-placement="rigth" title="Desactivar Promotor"><i class="fa fa-toggle-on"></i></button>';
        	}else{
        		$datos[$key]['activo'] = '<strong>No activo</strong>';
        		$btn .= '<button class="btn btn-warning activar" data-id="'.$value['id'].'" data-toggle="tooltip" data-placement="rigth" title="Activar Promotor"><i class="fa fa-toggle-off"></i></button>';
        	}

        	$datos[$key]['btn'] = '<div class="botonera">'.$btn.'</div>';



	        $stmt = $this->conexion->prepare($sql2);
	        $stmt->bindParam(':promotor',$value['id'],PDO::PARAM_INT);
	        $stmt->execute();

	        if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	        	$datos[$key]['comision'] = '<strong class="comision">'.number_format((float)$row['comision'],2,'.',',') . '$ MXN</strong>';
	        }else{
	        	$datos[$key]['comision'] = '<strong class="comision">0 $ MXN</strong>';
	        }

        	

        	$datos[$key]['telefono'] = '+52-'.$value['telefono'];


        }


        return $datos;

	}


	public function activarpromotor(int $idpromotor){

		if($this->conexion->inTransaction()){
			$this->conexion->rollBack();
		}

		$this->conexion->beginTransaction();


		$sql = "UPDATE promotor set activo = :activo where id =:promotor && id_hotel =:hotel";

		try {

				$stm = $this->conexion->prepare($sql);
				$stm->execute(array(':activo'=>true,
									':promotor'=>$idpromotor,
									':hotel'=>$this->hotel['id']));
				$this->conexion->commit();

		} catch (\PDOException $e) {

				$this->conexion->rollBack();
				$this->error_log(__METHOD__,__LINE__,$e->getMessage());
				return false;

		}

		return true;

	}

	public function desactivarpromotor(int $idpromotor){

		if($this->conexion->inTransaction()){
			$this->conexion->rollBack();
		}

		$this->conexion->beginTransaction();


		$sql = "UPDATE promotor set activo = :activo where id =:promotor && id_hotel =:hotel";

		try {

				$stm = $this->conexion->prepare($sql);
				$stm->execute(array(':activo'=>false,
									':promotor'=>$idpromotor,
									':hotel'=>$this->hotel['id']));
				$this->conexion->commit();

		} catch (\PDOException $e) {

				$this->conexion->rollBack();
				$this->error_log(__METHOD__,__LINE__,$e->getMessage());
				return false;

		}

		return true;

	}	

	private function getComision(int $idpromotor){
		$sql = "SELECT comision from promotor where id =:id";
		$stm = $this->conexion->prepare($sql);
		$stm->bindParam(':id',$idpromotor,PDO::FETCH_ASSOC);
		$stm->execute();

		return $stm->fetch(PDO::FETCH_ASSOC)['comision'];
	}


	public function eliminarpromotor(int $idpromotor){

		if($this->getComision($idpromotor) <= 0){

			if($this->conexion->inTransaction()){
				$this->conexion->rollBack();
			}

			$this->conexion->beginTransaction();


			$sql = "DELETE FROM promotor where id =:id";


			try {

				$stm = $this->conexion->prepare($sql);
				$stm->bindParam(':id',$idpromotor,PDO::PARAM_INT);

				$stm->execute();
				$this->conexion->commit();

				
			} catch (\PDOException $e) {
				$this->conexion->rollBack();
				$this->error_log(__METHOD__,__LINE__,$e->getMessage());
				return false;
			}


			$_SESSION['notificacion']['success'] = "Se ha Eliminado exitosamente al promotor.";
			return true;

		}else{
			return false ;
		}

	}

	public function newCargo(string $newcargo){


		$cargo = array();

		$sql = "INSERT INTO cargo(cargo,id_hotel)values(:cargo,:hotel)";

		if($this->conexion->inTransaction()){
			$this->conexion->rollBack();
		}

		$this->conexion->beginTransaction();

		try {
			
			$stm = $this->conexion->prepare($sql);
			$stm->bindParam(':cargo',$newcargo,PDO::PARAM_STR);
			$stm->bindParam(':hotel',$this->hotel['id'],PDO::PARAM_INT);

			$stm->execute();

			if($stm->rowCount()>0){
				$cargo['idcargo'] = $this->conexion->lastInsertId();

				$cargo['newcargo'] = $this->namecargo($this->conexion->lastInsertId());
			}

			$this->conexion->commit();
			
		} catch (\PDOException $e) {

			$this->conexion->rollBack();
			$this->error_log(__METHOD__,__LINE__,$e->getMessage());
			return false;
		}

		
		return $cargo;

	}



	public function getCargos(){
		$sql = "SELECT c.cargo ,c.id from cargo as c where c.id_hotel = :hotel";

		$stm = $this->conexion->prepare($sql);
		$stm->bindParam(':hotel',$this->hotel['id'],PDO::PARAM_INT);
		$stm->execute();



		foreach ($stm->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {

				if($value['id'] == $this->promotor['id_cargo']){
					echo "<option value='".$value['id']."' selected>".$value['cargo']."</option>";	
				}else{
					echo "<option value='".$value['id']."'>".$value['cargo']."</option>";	
				}
					
		}
	}

	public function is_cargo(){
		if(!empty($this->promotor['id_cargo'])){
			return true;
		}else{
			return false;
		}
	}

	public function ListarCargos(){

		$sql = "SELECT cargo,id from cargo where id_hotel =:hotel";

		$stm = $this->conexion->prepare($sql);
		$stm->bindParam(':hotel',$this->hotel['id'],PDO::PARAM_INT);
		$stm->execute();

		$dato = $stm->fetchAll(PDO::FETCH_ASSOC);

		for ($i=0; $i <count($dato); $i++) { 
			$dato[$i]['id'] = '<button type="button" data-toggle="tooltip" title="Eliminar Cargo" data-placement="left" class="eliminar" data-id="'.$dato[$i]['id'].'"><i class="fa fa-close"></i></button>';
		}

		return $dato;
	}
	
	private function namecargo(int $idcargo){
				$stm = $this->conexion->prepare('SELECT cargo from cargo where id =:idcargo');
				$stm->bindParam(':idcargo',$idcargo,PDO::PARAM_INT);
				$stm->execute();

			return $stm->fetch(PDO::FETCH_ASSOC)['cargo'];
		}


	public function eliminarcargo(int $idcargo){

		if($this->conexion->inTransaction()){
			$this->conexion->rollBack();
		}

		$this->conexion->beginTransaction();

		$sql = "DELETE FROM cargo where id = :idcargo";
		
		try {
			$stm = $this->conexion->prepare($sql);

			$stm->bindParam(':idcargo',$idcargo,PDO::FETCH_ASSOC);

			$stm->execute();

			$this->conexion->commit();

		} catch (\PDOException $e) {
			$this->conexion->rollBack();
			$this->error_log(__METHOD__,__LINE__,$e->getMessage());
			return $false;
		}

		return true;

	}	


	public function cargarcargos(){

		$sql = "SELECT cargo,id from cargo where id_hotel :hotel";
		$stm  = $this->conexion->prepar($sql);
		$stm->bindParam(':hotel',$this->hotel['id'],PDO::FETCH_ASSOC);
		$stm->execute();

		$datos = array();

		foreach ($stm->fet as $key => $value) {
			# code...
		}
	}


	public function verificar(string $email){
			if($this->conexion->inTransaction()){
					$this->conexion->rollBack();
				}

			$sql = 'UPDATE promotor set verificado =:verificadop where email = :emailp';

			$this->conexion->beginTransaction();
			try {
				$stm = $this->conexion->prepare($sql);
				// $stm->bindParam(':verificadop',1,PDO::PARAM_INT);
				// $stm->bindParam(':emailp',$email,PDO::PARAM_STR);
				$stm->execute(array(':verificadop'=>true,':emailp'=>$email));

				$this->conexion->commit();
			} catch (\PDOException $e) {
				$this->conexion->rollBack();
				$this->error_log(__METHOD__,__LINE__,$e->getMessage());
				return false;
			}

			$_SESSION['notificacion']['success'] = 'Se ha verificado correctamente su cuenta, por favor Ingrese su contraseña!';
			return true;

	}



	public function isPromotor(string $email){

		$sql = 'SELECT id,verificado FROM promotor where email = :emailp';

		$stm = $this->conexion->prepare($sql);
		$stm->bindParam(':emailp',$email,PDO::PARAM_STR);

		$stm->execute();

		$datos = array('peticion' => false,'verificado'=>false,'mensaje'=>'');

		if($stm->rowCount() > 0){
			
			$datos['peticion'] = true;


			if($row = $stm->fetch(PDO::FETCH_ASSOC)){

				if($row['verificado']){
					$datos['verificado'] = true;
				}else{
					$result = $this->verificarPromotor($row['id'],$email);
					if($result){
						$datos['mensaje'] = "Se ha enviado un email al correo asociado, por favor verifique!";
					}else{
						$datos['mensaje'] = "No se pudo verificar su cuenta en este momento, intente de nuevo mas tarde!";
					}
				}


			}



		}else{
			$datos['mensaje'] = "El correo no se encuentra asociado a ningún Hotel, debe haber un error.";
		}

		return $datos;
		


	}



	private function verificarPromotor(int $idpromotor,string $email){


				if($this->conexion->inTransaction()){
					$this->conexion->rollBack();
				}

				$hash = md5( rand(0,1000) );
				$title = 'Verificación de cuenta';
				$content = 'Para verificar tu cuenta, debes seguir este enlace para comprobar tu identidad: <a style="outline:none; color:#0082b7; text-decoration:none;" href="'.HOST.'/Hotel/login.php?email='.$email.'&codigo='.$hash.'">Verificar cuenta promotor</a>.<br>Si no solicitaste esta verificaci&oacute;n, por favor ignora este mensaje.';
				$body_alt = 
					'Verificación de cuenta promotor';
					$mail = new PHPMailer(true);
					try {
						
						$mail->CharSet = 'UTF-8';
						// $mail->SMTPDebug = 3; // CONVERSACION ENTRE CLIENTE Y SERVIDOR
						$mail->isSMTP();
						$mail->Host = 'single-5928.banahosting.com';
						$mail->SMTPAuth = true;
						$mail->SMTPSecure = 'ssl';
						$mail->Port = 465;
						// El correo que hará el envío
						$mail->Username = 'notification@travelpoints.com.mx';
						$mail->Password = '20464273jd';
						$mail->setFrom('notification@travelpoints.com.mx', 'Travel Points');
						// El correo al que se enviará
						$mail->addAddress($email);
						// Hacerlo formato HTML
						$mail->isHTML(true);
						// Formato del correo
						$mail->Subject = 'Verificación de cuenta';
						$mail->Body    = $this->email_template($title, $content);
						$mail->AltBody = $body_alt;

						if(!$mail->send()){
							$this->error['error'] = 'El correo de confirmación no se pudo enviar debido a una falla en el servidor. Intenta nuevamente.';
							return;
						}else{

							$this->conexion->beginTransaction();

							$sql = "UPDATE promotor set hash_verificacion = :hash where email = :email";

							try {
								$stm = $this->conexion->prepare($sql);
								$stm->bindParam(':hash',$hash,PDO::PARAM_STR);
								$stm->bindParam(':email',$email,PDO::PARAM_STR);

								$stm->execute();

								$this->conexion->commit();
								
							} catch (\PDOException $e) {
								$this->conexion->rollBack();
								$this->error_log(__METHOD__,__LINE__,$e->getMessage());
								return false;
							}
							
						}
					} catch (mailerexception $e) {
						$this->error_log(__METHOD__,__METHOD__,$mail->ErrorInfo);
						return false;
					}
				

				return true;
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'assets/error_logs/panelhotel.txt', '['.date('d/M/Y h:i:s A').' on '.$method.' on line '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		foreach ($this->errors as $key => $value){
			$this->errors[$key] = null;
		}
		$this->errors['method'] = true;
		return $this;
	}


	private function email_template($title, $content){
		$html = 
				'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<title>'._safe($title).'</title>
				<style type="text/css">
				@media only screen and (max-width: 600px) {
				 table[class="contenttable"] {
				 width: 320px !important;
				 border-width: 3px!important;
				}
				 table[class="tablefull"] {
				 width: 100% !important;
				}
				 table[class="tablefull"] + table[class="tablefull"] td {
				 padding-top: 0px !important;
				}
				 table td[class="tablepadding"] {
				 padding: 15px !important;
				}
				}
				</style>
				</head>
				<body style="margin:0; border: none; background:#f7f8f9">
					<table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
						<tr>
							<td align="center" valign="top"><table class="contenttable" border="0" cellpadding="0" cellspacing="0" width="600" bgcolor="#ffffff" style="border-width: 8px; border-style: solid; border-collapse: separate; border-color:#e9e9e9; margin-top:40px; font-family:Arial, Helvetica, sans-serif">
								<tr>
									<td>
										<table border="0" cellpadding="0" cellspacing="0" width="100%">
											<tbody>
												<tr>
													<td width="100%" height="40">&nbsp;</td>
												</tr>
												<tr>
													<td valign="top" align="center">
														<a href="'.HOST.'" target="_blank">
															<img alt="Travel Points" src="'.HOST.'/assets/img/LOGOV.png" style="padding-bottom: 0; display: inline !important;width:250px;height:auto">
														</a>
													</td>
												</tr>
												<tr>
													<td width="100%" height="40">&nbsp;</td>
												</tr>
											</tbody>
										</table>
									</td>
								</tr>
								<tr>
									<td class="tablepadding" style="color: #444; padding:20px; font-size:14px; line-height:20px; border-top-width:1px; border-top-style:solid; border-top-color:#ececec;">
										<table border="0" cellpadding="0" cellspacing="0" width="100%">
											<tbody>
												<tr>
													<td align="center" class="tablepadding" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
														<strong>'._safe($title).'</strong>
													</td>
												</tr>
												<tr>
													<td class="tablepadding" align="center" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
														'.$content.'<br>
														Para cualquier aclaraci&oacute;n contacta a nuestro equipo de soporte.<br>
														<a style="outline:none; color:#0082b7; text-decoration:none;" href="mailto:soporte@infochannel.si">
															soporte@infochannel.si
														</a>
													</td>
												</tr>
											</tbody>
										</table>
									</td>
								</tr>
								<tr>
									<td bgcolor="#fcfcfc" class="tablepadding" style="padding:20px 0; border-top-width:1px;border-top-style:solid;border-top-color:#ececec;border-collapse:collapse">
										<table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-size:13px;color:#999999; font-family:Arial, Helvetica, sans-serif">
											<tbody>
												<tr>
													<td align="center" class="tablepadding" style="line-height:20px; padding:20px;">
														Marina Vallarta Business Center, Oficina 204, Plaza Marina.<br>
														Puerto Vallarta, México.<br>
														01 800 400 INFO (4636), (322) 225 9635.<br>
														<a style="outline:none; color:#0082b7; text-decoration:none;" href="mailto:info@infochannel.si">info@infochannel.si</a>
													</td>
												</tr>
											</tbody>
										</table>
										<table align="center">
											<tr>
												<td style="padding-right:10px; padding-bottom:9px;">
													<a href="https://www.facebook.com/TravelPointsMX" target="_blank" style="text-decoration:none; outline:none;">
														<img src="'.HOST.'/assets/img/facebook.png" width="32" height="32" alt="Facebook">
													</a>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td>
							<table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-size:13px;color:#999999; font-family:Arial, Helvetica, sans-serif">
								<tbody>
									<tr>
										<td class="tablepadding" align="center" style="line-height:20px; padding:20px;">
											&copy; Travel Points '.date('Y').' Todos los derechos reservados.
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</table>
				</body>
				</html>';
		return $html;
	}

}
 ?>