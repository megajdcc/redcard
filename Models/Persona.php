<?php 
namespace Models;

use assets\libs\connection as Conection;

  /**
  * @author Crespo Jhonatan...
  */
 class Persona extends Conection{
 	
 	private $conection;
 	private $dni;
 	private $nombre;
 	private $apellido;
 	private $telefonomovil;

 	function __construct(){	
 		parent::__construct();
 	
 	}

 	//GETTERS Y SETTERS ;
 	//
 
 	public function setNombre($nombre){

 		$this->nombre = $nombre;
 	}

 	public function getNombre(){
 		return $this->nombre;
 	}

 	public function setApellido($apellido){
 		$this->apellido = $apellido;
 	}

 	public function getApellido(){
 		return $this->apellido;
 	}

 	public function setTelefonoMovil($telefonomovil){
 		$this->telefonomovil = $telefonomovil;
 	}

 	public fucntion getTelefonoMovil(){
 		return $this->telefonomovil;
 	}

 	protected function setDni($dni){
 		$this->dni = $dni;
 	}

 	protected function getDni(){
 		return $this->getdni;
 	}


	//Methods de Objetos... 
	
	/**
	 * Metodo utilizado para grabar un registro de Persona en la Bd..
	 */
 	protected function registrar(){
 		$result = false;


 		if($con->inTransaction()){
 			$con->rollback();
 		}

 		$con->beginTransaction();
 		$datos = array(':dni'=>$this->getDni(),':nombre' => $this->getNombre, ':apellido' => $this->getApellido());

 		try {
 			$sql = "insert into Persona(dni,nombre,apellido) value(:dni,:nombre,:apellido)";

 			$stm = $con->prepare($sql);
 			$result = $stm->execute($datos);
 			$con->commit();
 		} catch (PDOException $e) {
 			return $result;
 		}
 		return $result;
 	}

 	/**
 	 *Metodo protegido utlizado para modificar datos de Persona...  
 	 */
 	protected function modificar(){
 		$result = false ;

 		if($con->inTransaction()){
 			$con->rollback();
 		}

 		$con->beginTransaction();

 		$datos = array(':dni'=>$this->getDni(),':nombre' => $this->getNombre, ':apellido' => $this->getApellido());


 		$sql = "update from Persona nombre = :nombre, apellido = :apellido where dni = :dni";

 		try {
 			
 			$stm = $con->prepare($sql);
 			$result = $stm->execute($datos);

 			$con->commit();

 		} catch (PDOException $e) {
 			return $result;
 		}

 		return $result;
 	}

 	
 	public function consultar($dni = null, $datos = null){
 			$result = array('nombre'=>'',
 			                'apellido'=>'',
 			                'dni'=>null;
 							);
 		if(is_null($dni) && is_null($datos)){
 			return $result;
 		}else if(!is_null($datos) && is_null($dni)) {
 			if($con->inTransaction()){
 				$con->rollback;
 			}


 			try {
						$sql = "select * from Persona where dni = :dni || nombre = :nombre || apellido = :apellido";
						
						$stm = $con->prepare($sql);
						
						$stm->bindParam(':dni',$datos['dni'],PDO::PARAM_INT);
						$stm->bindParam(':nombre',datos['nombre'], PDO::PARAM_STR);
						$stm->bindParam(':apellido', datos['apellido'], PDO::PARAM_STR);
						$resultado = $stm->execute();
						
						if($resultado){
						while($row = $stm->fetch(PDO::FETCH_ASSOC)){
						$result['nombre'] = $row['nombre'];
						$result['apellido'] = $row['apellido'];
						$result['dni'] = $row['dni'];
						}
						}

 			} catch (PDOException $e) {
 				return $result;
 			}

 			

 			return $result;
 		}else{
 			if($con->inTransaction()){
 				$con->rollback;
 			}

 			try {
 				$sql = "select * from Persona where dni = :dni";

	 			$stm = $con->prepare($sql);

	 			$stm->bindParam(':dni', $dni , PDO::PARAM_INT);
	 			$resultado = $stm->execute();

	 			if($resultado){
	 				while($row = $stm->fetch(PDO::FETCH_ASSOC)){
	 					$result['nombre'] = $row['nombre'];
	 					$result['apellido'] = $row['apellido'];
	 					$result['dni'] = $row['dni'];
	 				}
	 			}

 			} catch (PDOException $e) {
 				return $result;
 			}
 			
 			return $result;
 		}

 	}

 } 


 ?>