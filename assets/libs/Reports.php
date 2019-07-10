<?php 

namespace assets\libs;


use PDO;
use assets\libs\connection;

/**
 * @author Crespo Jhonatan
 * @since 03/07/2019
 */

abstract class Reports 
{
	
	protected $conec = null;

	protected $error = array(
			'fechainicio' => null,
			'fechafin'    => null,
		);



	function __construct(connection $con)
	{
		$this->conec = $con->con;
	
	}

	abstract function report();

}




 ?>