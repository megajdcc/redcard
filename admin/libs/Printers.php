<?php 

namespace admin\libs;
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';

use PrintNode\Credentials;
use PrintNode\Request;
use assets\libs\connection;

use PrintNode\PrintJob;

use PDO;
/**
 *@author Crespo Jhonatan
 *@since 2019/09/14 
 */
class Printers
{

	private $impresoras = array(
			'name_printer' =>'',
			'id'           =>'',
			'name_equipo'  =>'',
			'id_printer'   =>'',
			'id_hotel'     =>'',
		);

	private $con = null;

	public $error = array(
			'error' => '',
	);

	// define("API_KEY_PRINTNODE", "wJsRsXri4oSXWD_5IzT9XEGE-3rfw5fEZz0pmg4uXhI");
	
	function __construct(connection $con)
	{
		$this->con = $con->con;
		// $this->cargrImpresoras();
	}


	private function cargrImpresoras(){

		$credenciales = new Credentials();
		$credenciales->setApiKey('wJsRsXri4oSXWD_5IzT9XEGE-3rfw5fEZz0pmg4uXhI');

		$request = new Request($credenciales);
		$request->setLimit(80000);

		$computers = $request->getComputers();
		$printers = $request->getPrinters();

		$printJobs = $request->getPrintJobs();

		$printjob = new PrintJob();

		foreach ($printers as $key => $impresora) {

				$this->impresoras['id_printer'] = $impresora->id;
				$this->impresoras['name_printer'] = $impresora->name;
				$this->impresoras['id'] = 0;
				$this->impresoras['name_equipo'] = '';
				$this->impresoras['id_hotel'] = '';
		}



	}

	public function getDatos(){


		$credenciales = new Credentials();
		$credenciales->setApiKey("wJsRsXri4oSXWD_5IzT9XEGE-3rfw5fEZz0pmg4uXhI");

		$request = new Request($credenciales);
		$request->setLimit(80000);

		$computers = $request->getComputers();
		$printers = $request->getPrinters();

		$printJobs = $request->getPrintJobs();


		// foreach ($printers as $key => $impresora) {

		// 		$this->impresoras['id_printer'] = $impresora->id;
		// 		$this->impresoras['name_printer'] = $impresora->name;
		// 		$this->impresoras['id'] = 0;
		// 		$this->impresoras['name_equipo'] = '';
		// 		$this->impresoras['id_hotel'] = '';
		// }
		
		$printjob = new PrintJob();
		
		for ($i=0; $i < count($printers); $i++) { 
				$computer = $printers[$i]->computer;
			if ($printers[$i]->default) {?>
				<tr>
					<td><?php echo $printers[$i]->id; ?></td>
					<td><?php echo $computer->name. ' - '. $computer->state; ?></td>
					<td><?php echo $printers[$i]->name; ?></td>
						<td><?php echo $printers[$i]->state; ?></td>
					<td><?php echo $this->getHotel($printers[$i]->id);?></td>
					<td>
						<?php if ($this->getHotel($printers[$i]->id) == 'Sin definir'): ?>
								<button type="button" name="establecer-hotel" data-id="<?php echo $printers[$i]->id; ?>" class="establecer btn btn-outline-secondary">Establecer Hotel</button>
						<?php else:?>
								<button type="button" name="quitar-hotel" data-hotel="<?php echo $this->getHotel($printers[$i]->id) ?>" class="quitar btn btn-outline-secondary">Quitar Hotel</button>
						<?php endif ?>
					
					</td>
				</tr>
			<?php }
		 }
		
		

	}


	public function getHotel(int $impresora){

		$sql = "SELECT impresora, nombre from hotel where impresora =:print";

		$stm = $this->con->prepare($sql);

		$stm->execute([':print'=>$impresora]);

		if($row = $stm->fetch(PDO::FETCH_ASSOC)){
			return $row['nombre'];
		}else{
			return 'Sin definir';
		} 


	}
	

	public function quitarhotel(array $datos){

		if($this->con->inTransaction()){
			$this->con->rollback();
		}

		$this->con->beginTransaction();

		$sql = 'UPDATE hotel set impresora = 0 where nombre=:hote';

		try {
				$stm = $this->con->prepare($sql);
				$stm->execute([':hote' => $datos['hotel']]);
				$this->con->commit();
		} catch (PDOException $e) {
			$this->con->rollback();

			$this->error_log(__METHOD__,__LINE__,$e->getMessage());
			return false;
			
		}

		$_SESSION['notification']['success'] = 'Se ha quitado exitosamente la impresora del hotel!';

		return true;

	}

	public function establecerimpresora(array $datos){


		if($this->con->inTransaction()){
			$this->con->rollback();
		}

		$this->con->beginTransaction();


		$sql = 'update hotel set impresora = :impresora where id =:hotel';

		try {
				$stm = $this->con->prepare($sql);
				$stm->execute([':impresora' => $datos['impresora'], ':hotel' => $datos['hotel']]);
				$this->con->commit();


		} catch (PDOException $e) {
			$this->con->rollback();

			$this->error_log(__METHOD__,__LINE__,$e->getMessage());
			return false;
			
		}

		$_SESSION['notification']['success'] = 'Se ha emparejado con exito la impresora al hotel!';

		return true;

	}


	public function getNotification(){
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


	public function getHoteles(){

		$stm = $this->con->prepare('SELECT id,nombre from hotel where impresora = 0 || impresora = null');
		$stm->execute();

		while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
			echo "<option value='".$row['id']."'>".$row['nombre']."</option>";
		}

	}


	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'/assets/error_logs/impresora_hotel.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}


}

 ?>
