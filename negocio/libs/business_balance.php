<?php 


namespace negocio\libs;
use assets\libs\connection;
use PDO;

class business_balance {
	private $con;
	private $error = array(
		'warning' => null,
		'error' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		return;
	}





	public function recargarsaldo(int $idnegocio, $monto){


		if($this->con->inTransaction()){
			$this->con->rollBack();
		}

		$this->con->beginTransaction();


		$sql = "update negocio set saldo =:saldo where id_negocio=:negocio";


		try {
			$newmonto = $this->getSaldo($idnegocio) + $monto;
			$stm = $this->con->prepare($sql);
			$stm->bindParam(':saldo',$newmonto);
			$stm->bindParam(':negocio',$idnegocio);
			$stm->execute();
			$this->con->commit();


			
		} catch (PDOException $e) {

			$this->con->rolLBack();
			return false;
			
		}

		return true;
		




	}


	private function getSaldo(int $idnegocio){
		$sql = "SELECT saldo from negocio where id_negocio=:negocio";

		$stm = $this->con->prepare($sql);

		$stm->execute(array(':negocio'=>$idnegocio));

		$fila = $stm->fetch(PDO::FETCH_ASSOC);

		return $fila['saldo'];
	}
	public function saldoactual(int $idnegocio){


		$sql = "SELECT saldo from negocio where id_negocio=:negocio";

		$stm = $this->con->prepare($sql);

		$stm->execute(array(':negocio'=>$idnegocio));

		$fila = $stm->fetch(PDO::FETCH_ASSOC);

		return '$ '.number_format((float)$fila['saldo'],2,'.',',').' MXN';
	}

	public function get_notification(){
		$html = null;
		if(isset($_SESSION['notification']['success'])){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-success" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notification']['success']).'
			</div>';
			unset($_SESSION['notification']['success']);
		}
		if(isset($_SESSION['notification']['info'])){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-info" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notification']['info']).'
			</div>';
			unset($_SESSION['notification']['info']);
		}
		if($this->error['warning']){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-warning" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->error['warning']).'
			</div>';
		}
		if($this->error['error']){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->error['error']).'
			</div>';
		}
		return $html;
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\business_balance.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>