<?php 

namespace Hotel\models;
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
								'id'=>null
								);

	
	private $error = array('notificacion'=>'');
	function __construct(conexion $conexion,int $idpromotor = null)
	{
		$this->conexion = $conexion->con;
		$this->iduser = $_SESSION['user']['id_usuario'];
		$this->hotel['id'] = $_SESSION['id_hotel'];


		if(!is_null($idpromotor)){
			$this->promotor['id'] = $idpromotor;
			$this->cargarPromotor();
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


		$sql = "SELECT p.activo,p.nombre,p.apellido,p.telefono,p.username,p.email,c.cargo,c.id as id_cargo from promotor as p join cargo as c on p.id_cargo = c.id 
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

	public function getPromotores(){

		$sql = "SELECT p.id, upper(concat(p.nombre,' ',p.apellido)) as nombre, p.telefono,c.cargo, p.comision,p.activo 
			from promotor as p 
			join  cargo as c on p.id_cargo = c.id
            where p.id_hotel = :hotel";

        $stm = $this->conexion->prepare($sql);
        $stm->bindParam(':hotel',$this->hotel['id'],PDO::FETCH_ASSOC);
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



        	$datos[$key]['comision'] = '<strong class="comision">'.number_format((float)$value['comision'],2,',','.') . '$ MXN</strong>';

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

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'assets/error_logs/panelhotel.txt', '['.date('d/M/Y h:i:s A').' on '.$method.' on line '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		foreach ($this->errors as $key => $value){
			$this->errors[$key] = null;
		}
		$this->errors['method'] = true;
		return $this;
	}
}
 ?>