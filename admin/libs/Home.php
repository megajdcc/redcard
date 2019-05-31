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

	private $fechas = array(
		'inicio' => null,
		'fin'    =>null
		);

	private $fecha1, $fecha2;

	function __construct(connection $conec){
		$this->conection = $conec->con;

		$this->cargarData();
	}



// Methodos 
// 
 
 
 
	public function busqueda(array $post){
		
		$this->setFecha1($post['fecha_inicio']);
		$this->setFecha2($post['fecha_fin']);
		$this->fecha1 = $post['fecha_inicio'];
		$this->fecha2 = $post['fecha_fin'];

		$this->cargarData();
		// header('location:'.HOST.'/Hotel/');
		

	}


public function getFecha1(){
		return $this->fecha1;
	}

public function getFecha2(){
		return $this->fecha2;
	}

private function setFecha1($datetime = null){
		if($datetime){
			$datetime = str_replace('/', '-', $datetime);
			$datetime = strtotime($datetime);
			if(!$datetime){
				$this->error['fechainicio'] = 'Formato de fecha y hora incorrecto. Utiliza la herramienta.';
				return false;
			}
			$datetime = date("Y/m/d H:i:s", $datetime);
			$this->fechas['inicio'] = $datetime;
			return true;
		}
		$this->error['fechainicio'] = 'Este campo es obligatorio.';
		return false;
	}

private function setFecha2($datetime = null){
		if($datetime){
			$datetime = str_replace('/', '-', $datetime);
			$datetime = strtotime($datetime);
			if(!$datetime){
				$this->error['fechafin'] = 'Formato de fecha y hora incorrecto. Utiliza la herramienta.';
				return false;
			}
			$datetime = date("Y/m/d H:i:s", $datetime);
			$this->fechas['fin'] = $datetime;
			return true;
		}
		$this->error['fechafin'] = 'Este campo es obligatorio.';
		return false;
	}

public function getFechaInicio(){
		return $this->fechas['inicio'];
	}

public function getFechaFin(){
		return $this->fechas['fin'];
	}

private function cargarData(){



	if($this->fechas['inicio'] and $this->fechas['fin']){
			//Total ventas negocios 
	$query = "SELECT sum(nv.venta) as total, nv.iso, count(nv.venta) as operaciones from negocio_venta as nv where nv.creado between :fecha1 and :fecha2";

	try {

		$stm = $this->conection->prepare($query);
		$stm->bindParam(':fecha1',$this->fechas['inicio']);
		$stm->bindParam(':fecha2',$this->fechas['fin']);

		$stm->execute();

		$this->ventas = $stm->fetchAll(PDO::FETCH_ASSOC);

		// $total = $fila['totalventa'];
		// settype($total,'float');

		// $this->ventas['total'] = number_format((float)$fila['totalventa'],2,'.',',');
		// $this->ventas['iso'] = $fila['iso'];
		// $this->ventas['operaciones'] = $fila['operaciones'];

		
	} catch (PDOException $e) {
		$this->capturarerror(__METHOD__,__LINE__,$e->getMessage());
	}

	// Negocios Afiliados, operados y procentaje 
	// 
	$query = "SELECT (SELECT COUNT(ne.id_negocio) FROM negocio as ne where ne.situacion =1) as afiliados, 
 					(COUNT(DISTINCT ne.id_negocio)) as operados,(COUNT(DISTINCT ne.id_negocio)*100)/(SELECT COUNT(ne.id_negocio)  FROM negocio as ne where ne.situacion =1) as porcentaje FROM negocio_venta as nven INNER JOIN negocio as ne ON ne.id_negocio = nven.id_negocio
 								where nven.creado between :fecha1 and :fecha2";

 		$stm = $this->conection->prepare($query);
 		$stm->bindParam(':fecha1',$this->fechas['inicio']);
 		$stm->bindParam(':fecha2',$this->fechas['fin']);
 		$stm->execute();

 		$fila = $stm->fetch(PDO::FETCH_ASSOC);

		$this->afiliados['afiliados']  = $fila['afiliados'];
		$this->afiliados['operados']   = $fila['operados'];
		$this->afiliados['porcentaje'] = $fila['porcentaje'];


	// Negocios deudores 
	// 
	$query  = "SELECT count(*) as deudores from negocio_venta as nv 
				join negocio as n on nv.id_negocio = n.id_negocio
					where n.saldo < 0 and nv.creado between :fecha1 and :fecha2";

		$stm = $this->conection->prepare($query);
		$stm->bindParam(':fecha1',$this->fechas['inicio']);
 		$stm->bindParam(':fecha2',$this->fechas['fin']);
		$stm->execute();

		$this->negocios['deudores'] = $stm->fetch(PDO::FETCH_ASSOC)['deudores'];

	// Total Adeudos
	// 
		$query = "SELECT sum(n.saldo) as adeudo from negocio_venta as nv 
			join negocio as n on nv.id_negocio = n.id_negocio
			where n.saldo < 0 and nv.creado between :fecha1 and :fecha2";
		$stm = $this->conection->prepare($query);
		$stm->bindParam(':fecha1',$this->fechas['inicio']);
 		$stm->bindParam(':fecha2',$this->fechas['fin']);
		$stm->execute();

		$this->negocios['total-adeudo'] = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['adeudo'],2,'.',',');


	// Saldo Favor
	// 
	 $query = "SELECT sum(nv.bono_esmarties) - (SELECT  sum(bh.comision) as balance
  					from  balancehotel as bh) - (SELECT sum(bf.comision) as balance from balancefranquiciatario as bf ) 
					- (SELECT sum(br.comision) as balance from balancereferidor as br ) as saldo
    		 from negocio_venta as nv where nv.creado between :fecha1 and :fecha2";

    $stm = $this->conection->prepare($query);
    $stm->bindParam(':fecha1',$this->fechas['inicio']);
 	$stm->bindParam(':fecha2',$this->fechas['fin']);
    $stm->execute();

    $this->saldo['afavor'] = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['saldo'],2,'.',',');


    // Comision total
    
    $query = "SELECT sum(nv.bono_esmarties) as comision from negocio_venta as nv where nv.creado between :fecha1 and :fecha2";

    $stm = $this->conection->prepare($query);

    $stm->bindParam(':fecha1',$this->fechas['inicio']);
 	$stm->bindParam(':fecha2',$this->fechas['fin']);

    $stm->execute();

     $this->saldo['comision_total'] = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['comision'],2,'.',',');


     //Comision Promedio
     $query = "SELECT avg(n.comision) as comision from negocio_venta as nv join negocio as n on nv.id_negocio = n.id_negocio where nv.creado between :fecha1 and :fecha2";

     $stm = $this->conection->prepare($query);
     $stm->bindParam(':fecha1',$this->fechas['inicio']);
 	$stm->bindParam(':fecha2',$this->fechas['fin']);
     $stm->execute();

     $this->saldo['comision_promedio'] = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['comision'],1,'.',',');

     //Consumo Promedio
     
    $query = "SELECT avg(nv.venta) as consumopromedio from negocio_venta as nv where nv.creado between :fecha1 and :fecha2";

    $stm = $this->conection->prepare($query);
    $stm->bindParam(':fecha1',$this->fechas['inicio']);
 	$stm->bindParam(':fecha2',$this->fechas['fin']);

    $stm->execute();

    $this->consumo['promedio'] = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['consumopromedio'],2,'.',',');


    //Utilidad Bruta.
    //
    //
    
   $query = "SELECT sum(nv.bono_esmarties) as utilidad  from negocio_venta as nv where nv.creado between :fecha1 and :fecha2";
    $stm = $this->conection->prepare($query);
    $stm->bindParam(':fecha1',$this->fechas['inicio']);
 	$stm->bindParam(':fecha2',$this->fechas['fin']);
    $stm->execute();

    $utilidad = $stm->fetch(PDO::FETCH_ASSOC)['utilidad'];


    $sql = "SELECT max(bh.balance) as balance from balancehotel as bh where bh.creado between :fecha1 and :fecha2";

    $stm = $this->conection->prepare($sql);
    $stm->bindParam(':fecha1',$this->fechas['inicio']);
 	$stm->bindParam(':fecha2',$this->fechas['fin']);

    $stm->execute();

    $utilidadhotel = $stm->fetch(PDO::FETCH_ASSOC)['balance'];

    $saldohotel = 0;
    if($utilidadhotel != null){
    	$saldohotel = $utilidadhotel;
    }



	$sql = "SELECT max(bfr.balance) as balance from balancefranquiciatario as bfr where bfr.creado between :fecha1 and :fecha2";
	
	$stmt = $this->conection->prepare($sql);
	 $stmt->bindParam(':fecha1',$this->fechas['inicio']);
 	$stmt->bindParam(':fecha2',$this->fechas['fin']);
	
	$stmt->execute();

 	$utilidadfranquiciatario = $stmt->fetch(PDO::FETCH_ASSOC)['balance'];


 	 $saldofranquiciatario = 0;
    if($utilidadfranquiciatario != null){
    	$saldofranquiciatario = $utilidadfranquiciatario;
    }

    $sql = "SELECT max(brf.balance) as balance from balancereferidor as brf where brf.creado between :fecha1 and :fecha2";
	
	$stmtt = $this->conection->prepare($sql);
	$stmtt->bindParam(':fecha1',$this->fechas['inicio']);
 	$stmtt->bindParam(':fecha2',$this->fechas['fin']);
	
	$stmtt->execute();

 	$utilidadreferidor = $stmtt->fetch(PDO::FETCH_ASSOC)['balance'];

		$saldoreferidor = 0;
		if($utilidadreferidor != null){
		$saldoreferidor = $utilidadreferidor;
		}

	$utilidadbruta = ($utilidad - $saldohotel - $saldofranquiciatario - $saldoreferidor);


	$this->negocios['utilidad_bruta'] = number_format((float)$utilidadbruta,2,'.',',');


	}else{
			//Total ventas negocios 
	$query = "SELECT sum(nv.venta) as total, nv.iso, count(nv.venta) as operaciones from negocio_venta as nv ";

	try {

		$stm = $this->conection->prepare($query);
		
		$stm->execute();

		$this->ventas = $stm->fetchAll(PDO::FETCH_ASSOC);

		// $total = $fila['totalventa'];
		// settype($total,'float');

		// $this->ventas['total'] = number_format((float)$fila['totalventa'],2,'.',',');
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
	$query  = "SELECT count(*) as deudores from negocio as n
					where n.saldo < 0";

		$stm = $this->conection->prepare($query);
		$stm->execute();

		$this->negocios['deudores'] = $stm->fetch(PDO::FETCH_ASSOC)['deudores'];

	// Total Adeudos
	// 
		$query = "SELECT sum(n.saldo) as adeudo from negocio as n
			where n.saldo < 0";
		$stm = $this->conection->prepare($query);
		$stm->execute();

		$this->negocios['total-adeudo'] = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['adeudo'],2,'.',',');


	// Saldo Favor
	// 
	 // $query = "SELECT sum(nv.bono_esmarties) - (SELECT  sum(bh.comision) as balance
  // 					from  balancehotel as bh) - (SELECT sum(bf.comision) as balance from balancefranquiciatario as bf ) 
		// 			- (SELECT sum(br.comision) as balance from balancereferidor as br ) as saldo
  //   		 from negocio_venta as nv";

  //   $query2 = "SELECT max(balance) as balance from balancesistema";

	$sql = "SELECT sum(saldo)  as saldo from negocio";

    
     $stmt = $this->conection->prepare($sql);
    $stmt->execute();

    
    	 $this->saldo['afavor'] = number_format((float)$stmt->fetch(PDO::FETCH_ASSOC)['saldo'],2,'.',',');
   
  


    // Comision total
    
    $query = "SELECT sum(nv.bono_esmarties) as comision from negocio_venta as nv";

    $stm = $this->conection->prepare($query);
    $stm->execute();

     $this->saldo['comision_total'] = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['comision'],2,'.',',');


     //Comision Promedio
     $query = "SELECT avg(n.comision) as comision from negocio_venta as nv join negocio as n on nv.id_negocio = n.id_negocio";

     $stm = $this->conection->prepare($query);
     $stm->execute();

     $this->saldo['comision_promedio'] = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['comision'],1,'.',',');

     //Consumo Promedio
     
     $query = "SELECT avg(nv.venta) as consumopromedio from negocio_venta as nv";

     $stm = $this->conection->prepare($query);

    $stm->execute();

    $this->consumo['promedio'] = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['consumopromedio'],2,'.',',');


    //Utilidad Bruta.
    
    
    
    $query = "SELECT sum(comision) as utilidad from balancesistema ";
    $stm = $this->conection->prepare($query);
    $stm->execute();

    $utilidad = $stm->fetch(PDO::FETCH_ASSOC)['utilidad'];


    $sql = "SELECT sum(bh.comision) as balance from balancehotel as bh ";

    $stm = $this->conection->prepare($sql);

    $stm->execute();

    $utilidadhotel = $stm->fetch(PDO::FETCH_ASSOC)['balance'];

    $saldohotel = 0;
    if($utilidadhotel != null){
    	$saldohotel = $utilidadhotel;
    }



	$sql = "SELECT sum(bfr.comision) as balance from balancefranquiciatario as bfr";
	
	$stmt = $this->conection->prepare($sql);
	
	$stmt->execute();

 	$utilidadfranquiciatario = $stmt->fetch(PDO::FETCH_ASSOC)['balance'];


 	 $saldofranquiciatario = 0;
    if($utilidadfranquiciatario != null){
    	$saldofranquiciatario = $utilidadfranquiciatario;
    }

    $sql = "SELECT sum(brf.comision) as balance from balancereferidor as brf ";
	
	$stmtt = $this->conection->prepare($sql);
	
	$stmtt->execute();

 	$utilidadreferidor = $stmtt->fetch(PDO::FETCH_ASSOC)['balance'];

		$saldoreferidor = 0;
		if($utilidadreferidor != null){
		$saldoreferidor = $utilidadreferidor;
		}

		$utilidadperil =  ($saldohotel + $saldofranquiciatario + $saldoreferidor);
		 // echo $utilidadperil;
	$utilidadbruta = ($utilidad - $utilidadperil);


	$this->negocios['utilidad_bruta'] = number_format((float)$utilidadbruta,2,'.',',');

   
	}

}

public function getVentas(){
	
		$pref = $nonpref = null;


			foreach($this->ventas as $key => $value) {
			$total = number_format((float)$value['total'],2,'.',',');
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


public function getVentasPromedioNegocios($fecha1 = null,$fecha2 = null){


	if(!empty($fecha1)){
				$query = "SELECT n.nombre as negocio , AVG(nven.venta) as promedio, di.iso FROM
					negocio_venta as nven INNER JOIN negocio as ne ON ne.id_negocio = nven.id_negocio
					INNER JOIN negocio as n on nven.id_negocio = n.id_negocio
					INNER JOIN divisa as di ON nven.iso = di.iso where nven.creado between :fecha1 and :fecha2
					GROUP BY n.nombre";
					
				$stm = $this->conection->prepare($query);
				$stm->bindParam(':fecha1',$fecha1);
				$stm->bindParam(':fecha2',$fecha2);
				$stm->execute();
					
				return $stm;
	}else{
				$query = "SELECT n.nombre as negocio , AVG(nven.venta) as promedio, di.iso FROM
					negocio_venta as nven INNER JOIN negocio as ne ON ne.id_negocio = nven.id_negocio
					INNER JOIN negocio as n on nven.id_negocio = n.id_negocio
					INNER JOIN divisa as di ON nven.iso = di.iso
					GROUP BY n.nombre";
					
				$stm = $this->conection->prepare($query);
				$stm->execute();
					
				return $stm;
	}

}

public function getNuevosPerfiles(){

	$query ="(SELECT 'Hotel' as perfil, count(*) as usuarios from hotel)
					UNION
				(SELECT 'Franquiciatario' as perfil, (SELECT count(*) as usuarios from solicitudfr) as usuarios from solicitudfr)
					UNION
				(SELECT 'Referidor' as perfil, (SELECT count(*) as usuarios from solicitudreferidor) as usuarios from solicitudreferidor)";
					
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

	if(!empty($this->fechas['inicio'])){
		$sql="SELECT count(*) FROM usuario where id_rol=8 and creado between :fecha1 and :fecha2";
		$stmt = $this->conection->prepare($sql);
		$stmt->bindParam(':fecha1',$this->fechas['inicio']);
		$stmt->bindParam(':fecha2',$this->fechas['fin']);
		$stmt->execute(); 
		$number_of_rows = $stmt->fetchColumn();
		$users=$number_of_rows;
		return $users;
	}else{
		$sql="SELECT count(*) FROM usuario where id_rol=8";
		$stmt = $this->conection->prepare($sql);
		$stmt->execute(); 
		$number_of_rows = $stmt->fetchColumn();
		$users=$number_of_rows;
		return $users;
	}
		
}

public function getUsuariosParticipantes(){

	if(!empty($this->fechas['inicio'])){
		$sql="SELECT (SELECT count(distinct u.id_usuario) FROM negocio_venta nv INNER JOIN usuario u ON u.id_usuario=nv.id_usuario where  nv.creado between :fecha1 and :fecha2) as participantes,
				((SELECT count(distinct u.id_usuario) from negocio_venta as nv join usuario u on nv.id_usuario = u.id_usuario where  nv.creado between :fecha3 and :fecha4) * 100) / 
							(SELECT count(*) from usuario) as porcentaje";
		$stmt = $this->conection->prepare($sql);
		$stmt->bindParam(':fecha1',$this->fechas['inicio']);
		$stmt->bindParam(':fecha2',$this->fechas['fin']);
		$stmt->bindParam(':fecha3',$this->fechas['inicio']);
		$stmt->bindParam(':fecha4',$this->fechas['fin']);
		$stmt->execute(); 
		$fila = $stmt->fetch(PDO::FETCH_ASSOC);


		$porcentaje = number_format((float)$fila['porcentaje'],2,'.',',');

		$html = '<strong>'.$fila['participantes'].'</strong>
				<p style="color: black;">'.$porcentaje.' %</p>';

		return $html;
	}else{
		$sql="SELECT (SELECT count(distinct u.id_usuario) FROM negocio_venta nv JOIN usuario u ON u.id_usuario=nv.id_usuario) as participantes,
 				((SELECT count(distinct u.id_usuario) from negocio_venta as nv join usuario u on nv.id_usuario = u.id_usuario ) * 100) / 
							(SELECT count(*) from usuario ) as porcentaje";
		$stmt = $this->conection->prepare($sql);
		$stmt->execute(); 
		$fila = $stmt->fetch(PDO::FETCH_ASSOC);


		$porcentaje = number_format((float)$fila['porcentaje'],2,'.',',');

		$html = '<strong>'.$fila['participantes'].'</strong>
				<p style="color: black;">'.$porcentaje.' %</p>';

		return $html;
	}
	
}

public function getComisionPerfiles($fecha1 = null, $fecha2 = null){


	if($fecha1){
		$query = "SELECT balance as total,'Hotel' as perfil from balancehotel as bh where bh.creado between :fecha1 and :fecha2 order by bh.id desc limit 1
			UNION
			SELECT  balance as total, 'Franquiciatario' as perfil from balancefranquiciatario as bfr where bfr.creado between :fecha3 and :fecha4 order by bfr.id desc limit 1
			UNION
			SELECT  balance as total,'Referidor' as perfil from balancereferidor as brf where
			brf.creado between :fecha5 and :fecha6 order by brf.id desc limit 1";

				$stm = $this->conection->prepare($query);
				$stm->bindParam(':fecha1',$fecha1);
				$stm->bindParam(':fecha2',$fecha2);
				$stm->bindParam(':fecha3',$fecha1);
				$stm->bindParam(':fecha4',$fecha2);
				$stm->bindParam(':fecha5',$fecha1);
				$stm->bindParam(':fecha6',$fecha2);
				$stm->execute();

				return $stm;
	}else{

		$query = "(SELECT sum(comision) as total,'Hotel' as perfil from balancehotel as bh )
			UNION
			(SELECT sum(comision) as total, 'Franquiciatario' as perfil from balancefranquiciatario as bfr order by bfr.id desc limit 1)
			UNION
			(SELECT sum(comision) as total,'Referidor' as perfil from balancereferidor as brf  order by brf.id desc limit 1)";

			$stm = $this->conection->prepare($query);
			$stm->execute();

			return $stm;

	}


	
}

public function getConsumoUsuarioPromedio(){

	if(!empty($this->fechas['inicio'])){
		$query = "SELECT avg(nv.venta) as total from negocio_venta as nv join usuario as u on nv.id_usuario = u.id_usuario where nv.creado between :fecha1 and :fecha2";
		
		$stm = $this->conection->prepare($query);
		$stm->bindParam(':fecha1',$this->fechas['inicio']);
		$stm->bindParam(':fecha2',$this->fechas['fin']);
		$stm ->execute();
		
		$total = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['total'],2,'.',',');
		
		return $total;
	}else{
			$query = "SELECT avg(nv.venta) as total from negocio_venta as nv join usuario as u on nv.id_usuario = u.id_usuario";
			
			$stm = $this->conection->prepare($query);
			$stm ->execute();
			
			$total = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['total'],2,'.',',');
			
			return $total;
	}
	
}

public function getRegistroPorUsuario(){

	if(!empty($this->fechas['inicio'])){
					$query = "SELECT ((SELECT count(*) from negocio_venta)  / 
					(SELECT count(*) from usuario as u join negocio_venta as nv on u.id_usuario = nv.id_usuario where u.id_rol = 8 and nv.creado between :fecha1 and :fecha2)) as total";
					$stm = $this->conection->prepare($query);

					$stm->bindParam(':fecha1',$this->fechas['inicio']);
					$stm->bindParam(':fecha2',$this->fechas['fin']);
					$stm->execute();
					
					$total = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['total'],0,'.',',');
					
					return $total;
	}else{
					$query = "SELECT ((SELECT count(*) from negocio_venta)  / 
					(SELECT count(distinct u.id_usuario) from usuario as u join negocio_venta as nv on u.id_usuario = nv.id_usuario)) as total";
					$stm = $this->conection->prepare($query);
					$stm->execute();
					
					$total = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['total'],0,'.',',');
					
					return $total;
	}
	

}

public function getTotalPuntos(){

	if(!empty($this->fechas['inicio'])){
		$query = "SELECT sum(nv.bono_esmarties) as puntos from negocio_venta as nv where nv.creado between :fecha1 and :fecha2";
		$stm = $this->conection->prepare($query);
			$stm->bindParam(':fecha1',$this->fechas['inicio']);
			$stm->bindParam(':fecha2',$this->fechas['fin']);
		$stm->execute();
		return number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['puntos'],2,'.',',');
	}else{
		$query = "SELECT sum(nv.bono_esmarties) as puntos from negocio_venta as nv";
		$stm = $this->conection->prepare($query);
		$stm->execute();
		return number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['puntos'],2,'.',',');

	}
	
}

public function getPuntosCanjeados(){

	$puntos = 0;

	if($this->fechas['inicio'] != null){
		
		$query = 'SELECT SUM(nv.bono_esmarties) AS total FROM usar_certificado where creado between :fecha1 and :fecha2';
		$stm = $this->conection->prepare($query);
		$stm->bindParam(':fecha1',$this->fechas['inicio']);
		$stm->bindParam(':fecha2',$this->fechas['fin']);
		$stm->execute();
		$puntos = $stm->fetch(PDO::FETCH_ASSOC)['total'];
		
	}else{
		// $query = 'SELECT SUM(nv.bono_esmarties) AS total FROM usar_certificado as uc join negocio_certificado as nc on uc.id_certificado = nc.id_certificado join negocio_venta as nv on nc.id_negocio = nv.id_negocio';
		// 
		 $query = 'SELECT sum(precio) as total from venta_tienda';
		$stm = $this->conection->prepare($query);
		$stm->execute();
		$puntos = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['total'],2,'.',',');
		
	}

	if($puntos == null){
		$puntos = 0;
	}

	return $puntos;
	
}

public function getValorRegalosEntregado(){

	if(!empty($this->fechas['inicio'])){
		$query = "SELECT sum(vt.precio) as canjeados from venta_tienda as vt join usuario as u on vt.id_usuario = u.id_usuario where vt.creado between :fecha1 and :fecha2";
		
		$stm = $this->conection->prepare($query);
		$stm->bindParam(':fecha1',$this->fechas['inicio']);
		$stm->bindParam(':fecha2',$this->fechas['fin']);
		$stm->execute();
		
		$total = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['canjeados'],2,'.',',');
		return $total;
	}else{
		$query = "SELECT sum(vt.precio) as canjeados from venta_tienda as vt join usuario as u on vt.id_usuario = u.id_usuario";

	$stm = $this->conection->prepare($query);
	$stm->execute();

	$total = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['canjeados'],2,'.',',');
	return $total;
	}
	
}

public function getCantidadRegalosEntregado(){


	if(!empty($this->fechas['inicio'])){
		$query = "SELECT count(*) as cant from venta_tienda where entrega = 1 and creado between :fecha1 and :fecha2";
		
		$stm = $this->conection->prepare($query);
		$stm->bindParam(':fecha1',$this->fechas['inicio']);
		$stm->bindParam(':fecha2',$this->fechas['fin']);
		$stm->execute();
		return $stm->fetch(PDO::FETCH_ASSOC)['cant'];

	}else{
		
		$query = "SELECT count(*) as cant from venta_tienda where entrega = 1";
		$stm = $this->conection->prepare($query);
		$stm->execute();
		return $stm->fetch(PDO::FETCH_ASSOC)['cant'];

	}
	
}

public function getValorRegaloPromedio(){
	if(!empty($this->fechas['inicio'])){
		$query="SELECT avg(vt.precio) as promedio from venta_tienda as vt where vt.entrega =1 and vt.creado between :fecha1 and :fecha2";
		
		$stm = $this->conection->prepare($query);
		$stm->bindParam(':fecha1',$this->fechas['inicio']);
		$stm->bindParam(':fecha2',$this->fechas['fin']);
		$stm->execute();
		
		$total = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['promedio'],2,'.',',');
		
		return $total;
	}else{
		$query="SELECT avg(vt.precio) as promedio from venta_tienda as vt where vt.entrega =1 ";
		
		$stm = $this->conection->prepare($query);
		$stm->execute();
		
		$total = number_format((float)$stm->fetch(PDO::FETCH_ASSOC)['promedio'],2,'.',',');
		
		return $total;
	}
	
}

public function getRegalosPorUsuarioDeseo(){

	
	if(!empty($this->fechas['inicio'])){
		$sql="SELECT count(*) FROM lista_deseos_producto where creado between :fecha1 and :fecha2";
		$stmt = $this->conection->prepare($sql);
		$stmt->bindParam(':fecha1',$this->fechas['inicio']);
		$stmt->bindParam(':fecha2',$this->fechas['fin']);
		$stmt->execute(); 
		$number_of_rows = $stmt->fetchColumn();
		$total=$number_of_rows;
		$sql="SELECT count(*) FROM usuario where creado between :fecha1 and :fecha2";
		$stmt = $this->conection->prepare($sql);
		$stmt->bindParam(':fecha1',$this->fechas['inicio']);
		$stmt->bindParam(':fecha2',$this->fechas['fin']);
		$stmt->execute(); 
		$number_of_rows = $stmt->fetchColumn();
		$users=$number_of_rows;

		return round($total/$users);
	}else{
		$sql="SELECT count(*) as cuenta FROM lista_deseos_producto";
		$stmt = $this->conection->prepare($sql);
		$stmt->execute(); 
		// $number_of_rows = $stmt->fetchColumn();
		$total=$stmt->fetch(PDO::FETCH_ASSOC)['cuenta'];


		$sql="SELECT count(*) as users FROM usuario";
		$stmt3 = $this->conection->prepare($sql);
		$stmt3->execute(); 
		
		$users=$stmt3->fetch(PDO::FETCH_ASSOC)['users'];
		
		echo $total;
		// return round($users/$total);

	}
	
}
private function capturarerror($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\administrativo_error.txt', '['.date('d/M/Y h:i:s A').' on '.$method.' on line '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		return;
	}

}

 ?>