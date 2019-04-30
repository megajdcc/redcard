<?php 


namespace admin\libs;
use assets\libs\connection;
use PDO;


/**
 * @author Crespo jhonatan
 * @since 22/04/2019
 */
class PreferenciaSistema{
	

	private $conection = null;

	private $preferencias = array(
		'email-notificacion-retiro' => null,
		'email-notificacion-general' =>null,
		'id' =>null
	);

	function __construct(connection $conec){
		$this->conection  = $conec->con;

		$this->cargarData();
	}

	private function cargarData(){

		$query = "select * from preferenciasistema";
		$stm = $this->conection->prepare($query);
		$stm->execute();

		while ($fila =$stm->fetch(PDO::FETCH_ASSOC) ) {
			if($fila['preferencia'] == 1){
				$this->preferencias['email-notificacion-retiro'] = $fila['eleccion'];
				$this->preferencias['id'] = $fila['id'];
			}
			
		}		

	}


	public function registrar(array $post){

			$emailretiro = $post['emailretiro'];


			if(!isset($this->preferencias['email-notificacion-retiro'])){
				$query = "insert into preferenciasistema(preferencia,eleccion) value(1,:eleccion)";

				try {
				 	$this->conection->beginTransaction();
				 	$stm = $this->conection->prepare($query);
				 	$stm->execute(array(':eleccion'=>$emailretiro));
				 	$this->conection->commit();
				 } catch (PDOException $e) {
				 	$this->conection->rollBack();
				 } 

				 header('location: '.HOST.'/admin/preferencias/preferencia-sistema');
				 die();
			}else{
				$query = "update preferenciasistema set preferencia = 1, eleccion=:eleccion where id =:id";

				try {
				 	$this->conection->beginTransaction();
				 	$stm = $this->conection->prepare($query);
				 	$stm->execute(array(':eleccion'=>$emailretiro,
				 						':id'=>$this->preferencias['id']));
				 	$this->conection->commit();
				 } catch (PDOException $e) {
				 	$this->conection->rollBack();
				 } 

				 header('location: '.HOST.'/admin/preferencias/preferencia-sistema');
				 die();

			}

			
	}



	public function getNotificacion(){

	}

	public function getEmailRetiro(){

		return $this->preferencias['email-notificacion-retiro'];

	}
}


 ?>