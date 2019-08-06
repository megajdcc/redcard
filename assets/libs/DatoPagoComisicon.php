<?php 

namespace assets\libs;

use PDO;


/**
 * @author Jhonatan Crespo
 * @since 05/08/2019
 */
class DatoPagoComision 
{
	
	private $conection = null;
	
	function __construct(connection $conec )
	{
		$this->conection = $conec->con;
	}
}

 ?>