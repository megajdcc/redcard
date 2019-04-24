<?php 

namespace admin\libs;
use assets\libs\connection;
use negocio\libs\manage_home;
use PDO;


/**
 * @author Crespo Jhonatan 
 * @since 24/04/2019
 */
class Home{
	

	//Propiedades 
	
	private $conection = null;

	private $ventas = array(
		// 'total' => null,
		// 'iso' => null,
		// 'operaciones' =>null
	);

	private $afiliados = array(
		'afiliados'  =>null,
		'operados'   =>null,
		'porcentaje' =>null
	);

	private $negocios = array(
		'deudores' =>null,
		'total-adeudo' =>null,
		'utilidad_bruta' =>null,
	);
	private $error = array(
		'notificacion' => null,
	);

	private $saldo = array(
		'afavor' =>null,
		'comision_total' => null,
		'comision_promedio' =>null
	);

	private $consumo = array(
		'promedio' =>null,
	);

	function __construct(connection $conec){
		$this->conection = $conec->con;

		$this->cargarData();
	}



// Methodos 
// 
// 
// 

private function cargarData(){

	//Total ventas negocios 
	$query = "select sum(nv.venta) as total, nv.iso, count(nv.venta) as operaciones from negocio_venta as nv ";

	try {

		$stm = $this->conection->prepare($query);
		$stm->execute();

		$this->ventas = $stm->fetchAll(PDO::FETCH_ASSOC);

		// $total = $fila['totalventa'];
		// settype($total,'float');

		// $this->ventas['total'] = number_format((float)$fila['totalventa'],2,',','.');
		// $this->ventas['iso'] = $fila['iso'];
		// $this->ventas['operaciones'] = $fila['operaciones'];

		
	} catch (PDOException $e) {
		$this->capturarerror(__METHOD__,__LINE__,$e->getMessage());
	}

	// Negocios Afiliados, operados y procentaje 
	// 
	$query = "SELECT (SELECT COUNT(ne.id_negocio) FROM negocio as ne where ne.situacion =1) as afiliados, 
 					(COUNT(DISTINCT ne.id_negocio)) as operados,(COUNT(DISTINCT ne.id_negocio)*100)/(SELECT COUNT(ne.id_negocio)  FROM negocio as ne where ne.situacion =1) as porcentaje FROM negocio_venta as nven INNER JOIN negocio as ne ON ne.id_negocio = nven.id_negocio";

 		$stm = $this->conection->prepare($query);
 		$stm->execute();

 		$fila = $stm->fetch(PDO::FETCH_ASSOC);

		$this->afiliados['afiliados']  = $fila['afiliados'];
		$this->afiliados['operados']   = $fila['operados'];
		$this->afiliados['porcentaje'] = $fila['porcentaje'];


	// Negocios deudores 
	// 
	$query  = "select count(*) as deudores from negocio_venta as nv 
				join negocio as n on nv.id_negocio = n.id_negocio
					where n.saldo <= 0";

		$stm = $this->conection->prepare($query);
		$stm->execute();

		$this->negocios['deudores'] = $stm->fetch(PDO::FETCH_ASSOC)['deudores'];

	// Total Adeudos
	// 
		$query = "select sum(n.saldo) as adeudo from negocio_venta as nv 
			join negocio as n on nv.id_negocio = n.id_negocio
			where n.saldo <= 0";
		$stm = $this->conection->prepare($query);
		$stm->execute();

		$this->negocios['total-adeudo'] = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['adeudo'],2,',','.');


	// Saldo Favor
	// 
	 $query = "select sum(nv.bono_esmarties) - (SELECT  sum(bh.comision) as balance
  					from  balancehotel as bh) - (select sum(bf.comision) as balance from balancefranquiciatario as bf ) 
					- (select sum(br.comision) as balance from balancereferidor as br ) as saldo
    		 from negocio_venta as nv";

    $stm = $this->conection->prepare($query);
    $stm->execute();

    $this->saldo['afavor'] = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['saldo'],2,',','.');


    // Comision total
    
    $query = "select sum(nv.bono_esmarties) as comision from negocio_venta as nv";

    $stm = $this->conection->prepare($query);
    $stm->execute();

     $this->saldo['comision_total'] = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['comision'],2,',','.');


     //Comision Promedio
     $query = "select avg(n.comision) as comision from negocio_venta as nv join negocio as n on nv.id_negocio = n.id_negocio";

     $stm = $this->conection->prepare($query);
     $stm->execute();

     $this->saldo['comision_promedio'] = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['comision'],1,',','.');

     //Consumo Promedio
     
     $query = "select avg(nv.venta) as consumopromedio from negocio_venta as nv";

     $stm = $this->conection->prepare($query);

    $stm->execute();

    $this->consumo['promedio'] = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['consumopromedio'],2,',','.');


    //Utilidad Bruta.
    //
    //
    
    $query = "select  (select  sum(nv.venta) from negocio_venta as nv) - sum(nv.bono_esmarties) as bruto from negocio_venta as nv ";
    $stm = $this->conection->prepare($query);
    $stm->execute();

    $this->negocios['utilidad_bruta'] = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['bruto'],2,',','.');

}

public function getVentas(){
	
		$pref = $nonpref = null;

		foreach($this->ventas as $key => $value) {
			
			$total = number_format((float)$value['total'],2,',','.');

			if($total != 0){
				
				if($value['iso'] == 'EUR'){
						$sign = '€';
					}else{
						$sign = '$';
					}

			
				$pref='';
				$nonpref = '';
				if($value['iso'] == 'MXN'){
					$pref .=
					'
						<div class="statusbox">
							<h2>Total de Ventas</h2>
							<div class="statusbox-content">
								<strong>'.$sign.$total.' '.$value['iso'].'</strong>
							</div><!-- /.statusbox-content -->
						</div>';
				}else{
					$nonpref .=
					'
						<div class="statusbox">
							<h2>Total de Ventas</h2>
							<div class="statusbox-content">
								<strong>'.$sign.$total.' '.$value['iso'].'</strong>
							</div><!-- /.statusbox-content -->
						</div>
					';
				}
			}
		}
		$html = $pref.$nonpref;
		if(!$html){
			$html =
			'
				<div class="statusbox">
					<h2>Total de ventas</h2>
					<div class="statusbox-content">
						<strong>$0</strong>
					</div><!-- /.statusbox-content -->
				</div>
		';
		}
		return $html;
}


public function getOperaciones(){

	return $this->ventas[0]['operaciones'];
}


public function getAfiliados(){
	return $this->afiliados['afiliados'];
}

public function getOperados(){
	return $this->afiliados['operados'];
}

public function getPorcentaje(){
	return number_format((float)$this->afiliados['porcentaje'],1,',','');
}


public function getVentasPromedioNegocios(){

	$query = "SELECT n.nombre as negocio , AVG(nven.venta) as promedio, di.iso FROM
					negocio_venta as nven INNER JOIN negocio as ne ON ne.id_negocio = nven.id_negocio
					INNER JOIN negocio as n on nven.id_negocio = n.id_negocio
					INNER JOIN divisa as di ON nven.iso = di.iso
					GROUP BY n.nombre";

	$stm = $this->conection->prepare($query);
	$stm->execute();

	return $stm;

}

public function getNegociosDeudores(){
	return $this->negocios['deudores'];
}

public function getTotalDeuda(){
	return $this->negocios['total-adeudo'];
}

public function getSaldoFavor(){
	return $this->saldo['afavor'];
}

public function getTotalComision(){
	return $this->saldo['comision_total'];
}

public function getComisionPromedio(){
	return $this->saldo['comision_promedio'];
}

public function getConsumoPromedio(){
	return $this->consumo['promedio'];
}

public function getNotificacion(){
	$notification = null;
		if(isset($_SESSION['notification']['success'])){
			$notification .= 
			'<div class="alert alert-icon alert-dismissible alert-success" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notification']['success']).'
			</div>';
			unset($_SESSION['notification']['success']);
		}
		if(isset($_SESSION['notification']['info'])){
			$notification .= 
			'<div class="alert alert-icon alert-dismissible alert-info" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notification']['info']).'
			</div>';
			unset($_SESSION['notification']['info']);
		}
		if($this->error['notificacion']){
			$notification .= 
			'<div class="alert alert-icon alert-dismissible alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($this->error['notificacion']).'
			</div>';
		}
		return $notification;
}

public function getUtilidadBruta(){
	return $this->negocios['utilidad_bruta'];
}


public function getTotalUsuario(){
		$sql="SELECT count(*) FROM usuario where id_rol=8";
		$stmt = $this->conection->prepare($sql);
		$stmt->execute(); 
		$number_of_rows = $stmt->fetchColumn();
		$users=$number_of_rows;
		return $users;
}

public function getUsuariosParticipantes(){
	$sql="select (select count(*) FROM negocio_venta nv INNER JOIN usuario u ON u.id_usuario=nv.id_usuario where id_rol=8) as participantes,
				((select count(*) from negocio_venta as nv join usuario u on nv.id_usuario = u.id_usuario where id_rol=8) * 100) / 
							(select count(*) from usuario where id_rol = 8) as porcentaje";
		$stmt = $this->conection->prepare($sql);
		$stmt->execute(); 
		$fila = $stmt->fetch(PDO::FETCH_ASSOC);


		$porcentaje = number_format((float)$fila['porcentaje'],2,',','.');

		$html = '<strong>'.$fila['participantes'].'</strong>
				<p style="color: black;">'.$porcentaje.' %</p>';

		return $html;
}

public function getComisionPerfiles(){

	$query = "select sum(bh.comision) as total,'Hotel' as perfil from balancehotel as bh where bh.id_venta != 0 
			UNION
			select sum(bfr.comision) as total, 'Franquiciatario' as perfil from balancefranquiciatario as bfr where bfr.id_venta != 0
			UNION
			select sum(brf.comision) as total,'Referidor' as perfil from balancereferidor as brf where brf.id_venta != 0";

					$stm = $this->conection->prepare($query);
				$stm->execute();

				return $stm;
}

public function getConsumoUsuarioPromedio(){
	$query = "select avg(nv.venta) as total from negocio_venta as nv join usuario as u on nv.id_usuario = u.id_usuario";

	$stm = $this->conection->prepare($query);
	$stm ->execute();

	$total = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['total'],2,',','.');

	return $total;
}

public function getRegistroPorUsuario(){

	$query = "select ((select count(*) from negocio_venta)  / 
					(select count(*) from usuario as u join negocio_venta as nv on u.id_usuario = nv.id_usuario where u.id_rol = 8)) as total";
	$stm = $this->conection->prepare($query);
	$stm->execute();

	$total = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['total'],2,',','.');

	return $total;

}

public function getTotalPuntos(){
	$query = "select sum(nv.bono_esmarties) as puntos from negocio_venta as nv";
	$stm = $this->conection->prepare($query);
	$stm->execute();
	return number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['puntos'],2,',','.');
}

public function getPuntosCanjeados(){

	$query = 'SELECT SUM(id_certificado) AS total FROM usar_certificado';
	$stm = $this->conection->prepare($query);
	$stm->execute();
	$puntos = $stm->fetch(PDO::FETCH_ASSOC)['total'];
	return $puntos;
}

public function getValorRegalosEntregado(){
	$query = "select sum(vt.precio) as canjeados from venta_tienda as vt join usuario as u on vt.id_usuario = u.id_usuario";

	$stm = $this->conection->prepare($query);
	$stm->execute();

	$total = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['canjeados'],2,',','.');
	return $total;
}

public function getCantidadRegalosEntregado(){
	$query = "select count(*) as cant from venta_tienda where entrega = 1";

	$stm = $this->conection->prepare($query);

	$stm->execute();

	return $stm->fetch(PDO::FETCH_ASSOC)['cant'];
}

public function getValorRegaloPromedio(){
	$query="select avg(vt.precio) as promedio from venta_tienda as vt where vt.entrega =1 ";

	$stm = $this->conection->prepare($query);
	$stm->execute();

	$total = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['promedio'],2,',','.');

	return $total;
}

public function getRegalosPorUsuarioDeseo(){
	$sql="SELECT count(*) FROM lista_deseos_certificado";
		$stmt = $this->conection->prepare($sql);
		$stmt->execute(); 
		$number_of_rows = $stmt->fetchColumn();
		$total=$number_of_rows;
		$sql="SELECT count(*) FROM usuario";
		$stmt = $this->conection->prepare($sql);
		$stmt->execute(); 
		$number_of_rows = $stmt->fetchColumn();
		$users=$number_of_rows;

		return round($total/$users);
}
private function capturarerror($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\administrativo_error.txt', '['.date('d/M/Y h:i:s A').' on '.$method.' on line '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		return;
	}

}

 ?>