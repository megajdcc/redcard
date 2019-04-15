<?php 

namespace Models\;

/**
 *
 * @author Crespo jhonatan
 */
class Perfiles
{
	
	private $conection, $nombre, $id;

	function __construct($conection){
		
		$this->conection = $conection;

	}




	//GETTERS Y SETTERS 
	//
	
	public function setNombre($nombre){
		$this->nombre = $nombre;
	}

	public function getNombre(){

		return $this->nombre;
	}

	public function setId($id){
		$this->id = $id;
	}

	public function getId(){

		return $this->id;
	}


	// gettters y setter protegidas para clases hijas ...
	protected function getConection(){
		return $this->conection;
	}

	protected function setConection($conection){
		$this->conection = $conection;
	}

}

 ?>