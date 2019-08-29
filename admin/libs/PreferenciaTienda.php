<?php 

namespace admin\libs;
use assets\libs\connection;
use PDO;

/**
 * @author Crespo Jhonatan
 * @since 11-05-2019
 */
class PreferenciaTienda{

	private $conection = null;

	
	function __construct(connection $conec)
	{
		$this->conection = $conec;
	}




	public function getNotificacion(){
		
	}
}
 ?>