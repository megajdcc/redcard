<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace admin\libs;
use assets\libs\connection;
use PDO;

class get_allusers {
	private $con;
	private $users = array();
	private $user = array(
		'id' => null, 
		'nombre' => null,
		'apellido' => null
	);
	private $error = array(
		'id' => null,
		'nombre' => null,
		'apellido' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->load_data();
		return;
	}

	private function load_data(){
		
		$query = "SELECT id_usuario,nombre,apellido FROM usuario WHERE nombre!='' AND apellido!=''";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$count=0;
		while($row = $stmt->fetch()){
			$this->users[$count]['id'] = $row['id_usuario'];
			$this->users[$count]['nombre'] = $row['nombre'];
			$this->users[$count]['apellido'] = $row['apellido'];
			$count++;
		}
		return;
	}

	public function get_users(){
		$html = null;
		foreach ($this->users as $key => $value) {
				$html .= '<option value="'.$value['id'].'">'._safe($value['nombre']).' '._safe($value['apellido']).'</option>';
		}
		return $html;
	}

	public function get_users_error(){
		if($this->error['id']){
			$error = '<p class="text-danger">'._safe($this->error['nombre']).' '._safe($this->error['apellido']).'</p>';
			return $error;
		}
	}

	private function error_log($method, $line, $error){
		// file_put_contents(ROOT.'\assets\error_logs\get_allusers.txt', '['.date('d/M/Y h:i:s A').' | '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['notification'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>