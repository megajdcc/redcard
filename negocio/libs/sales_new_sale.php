<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace negocio\libs;
use assets\libs\connection;
use PDO;

class sales_new_sale {
	private $con;
	private $user = array(
		'id' => null
	);
	private $business = array(
		'id' => null,
		'balance' => null,
		'status' => null
	);
	private $sale = array(
		'id' => null,
		'username' => null,
		'total' => null,
		'commission' => null,
		'eSmarties' => null,
		'referral_id' => null,
		'referral_commission' => 0,
		'certificate_id' => null
	);
	private $currencies = array();
	private $certificates = array();
	private $reserved = array();
	private $error = array(
		'username' => null,
		'total' => null,
		'currency' => null,
		'certificate' => null,
		'reserved' => null,
		'warning' => null,
		'error' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->user['id'] = $_SESSION['user']['id_usuario'];
		$this->business['id'] = $_SESSION['business']['id_negocio'];
		$this->load_data();
		return;
	}

	private function load_data(){
		$query = "SELECT saldo, comision, situacion FROM negocio WHERE id_negocio = :id_negocio";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->sale['commission'] = $row['comision'];
			$this->business['balance'] = $row['saldo'];
			$this->business['status'] = $row['situacion'];
		}
		$query = "SELECT iso FROM divisa";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->currencies[$row['iso']] = $row['iso'];
		}
		$query = "SELECT np.preferencia
			FROM negocio_preferencia np 
			INNER JOIN preferencia p ON np.id_preferencia = p.id_preferencia
			WHERE id_negocio = :id_negocio AND p.llave = 'default_currency'";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->sale['currency'] = $row['preferencia'];
		}
		$now = date('Y/m/d H:i:s', time());
		$query = "SELECT id_certificado, imagen, nombre, precio, iso FROM negocio_certificado WHERE id_negocio = :id_negocio AND fecha_inicio < :now1 AND fecha_fin > :now2 AND situacion = 1";
		$params = array('id_negocio' => $this->business['id'], 'now1' => $now, 'now2' => $now);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->certificates[$row['id_certificado']] = array(
				'image' => $row['imagen'],
				'name' => $row['nombre'],
				'price' => $row['precio'],
				'currency' => $row['iso']
			);
		}
		if($this->business['status'] != 1){
			$this->error['error'] = 'Tu negocio no se encuentra activo. Por el momento no puedes registrar ninguna venta. Contacta a Travel Points.';
		}
		return;
	}

	public function submit_sale(array $post){
		$this->set_user($post['username']);
		$this->set_total($post['total']);
		$this->set_currency($post['currency']);
		$this->set_eSmarties();
		$this->set_certificate($post['certificate']);
		if(isset($post['use_cert'])){
			$this->set_certificates($post['use_cert']);
		}
		if(!array_filter($this->error)){
			$this->create_sale();
			return true;
		}
		if($this->business['status'] != 1){
			return false;
		}
		$this->error['warning'] = 'Uno o más campos tienen errores. Por favor revísalos cuidadosamente.';
		return false;
	}

	private function create_sale(){
		$afterwards = $this->business['balance'] - $this->sale['eSmarties'];
		if($afterwards < -500){
			$query = "UPDATE negocio SET situacion = 2 WHERE id_negocio = :id_negocio";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$this->error['error'] = 'Has excedido el límite de crédito permitido ($500) y tu negocio se ha suspendido automáticamente. Contacta a Travel Points.';
			return false;
		}
		if($this->sale['certificate_id']){
			$query = "INSERT INTO usar_certificado (
				id_usuario,
				id_certificado,
				situacion
				) VALUES (
				:id_usuario,
				:id_certificado,
				1
			)";
			$params = array(
				':id_usuario' => $this->sale['id'],
				':id_certificado' => $this->sale['certificate_id']
			);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$query = "SELECT disponibles, (SELECT COUNT(*) FROM usar_certificado uc WHERE uc.id_certificado = ne.id_certificado AND uc.situacion != 0) as usados FROM negocio_certificado ne WHERE  id_certificado = :id_certificado";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_certificado', $this->sale['certificate_id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				if($row['usados'] >= $row['disponibles']){
					$query = "UPDATE negocio_certificado SET situacion = 2 WHERE id_certificado = :id_certificado";
					try{
						$stmt = $this->con->prepare($query);
						$stmt->bindValue(':id_certificado', $this->sale['certificate_id'], PDO::PARAM_INT);
						$stmt->execute();
					}catch(\PDOException $ex){
						$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
						return false;
					}
				}
			}
		}
		foreach ($this->reserved as $key => $value) {
			if($value == 0){
				$query = "SELECT ne.id_certificado, ne.disponibles, (SELECT COUNT(*) FROM usar_certificado uc WHERE uc.id_certificado = ne.id_certificado AND uc.situacion != 0) as usados, ne.situacion FROM usar_certificado uc INNER JOIN negocio_certificado ne ON uc.id_certificado = ne.id_certificado WHERE uc.id_uso = :id_uso";
				try{
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(':id_uso', $key, PDO::PARAM_INT);
					$stmt->execute();
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				if($row = $stmt->fetch()){
					if($row['situacion'] == 2 && $row['disponibles'] == $row['usados']){
						$query = "UPDATE negocio_certificado SET situacion = 1 WHERE id_certificado = :id_certificado";
						try{
							$stmt = $this->con->prepare($query);
							$stmt->bindValue(':id_certificado', $row['id_certificado'], PDO::PARAM_INT);
							$stmt->execute();
						}catch(\PDOException $ex){
							$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
							return false;
						}
					}
				}
			}
			$query = "UPDATE usar_certificado SET situacion = :situacion WHERE id_uso = :id_uso";
			$params = array(':situacion' => $value,':id_uso' => $key);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
		}
		$query = "INSERT INTO negocio_venta (
			id_usuario, 
			id_negocio, 
			id_empleado, 
			iso, 
			venta, 
			comision, 
			bono_esmarties,
			bono_referente
			) VALUES (
			:id_usuario, 
			:id_negocio, 
			:id_empleado, 
			:iso, 
			:venta, 
			:comision, 
			:bono_esmarties,
			:bono_referente
		)";


		if($this->sale['currency'] != 'MXN'){
			$valor = $this->sale['total'] *  19.02;
		}else{
			$valor = $this->sale['total'];
		}
		$params = array(
			':id_usuario' => $this->sale['id'],
			':id_negocio' => $this->business['id'],
			':id_empleado' => $this->user['id'],
			':iso' => $this->sale['currency'],
			':venta' => $valor,
			':comision' => $this->sale['commission'],
			':bono_esmarties' => $this->sale['eSmarties'],
			':bono_referente' => $this->sale['referral_commission']
		);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($params);
			$idventa = $this->con->lastInsertId();
			$this->registrarbalancesistema($idventa);
			$this->registrarbalancehotel($idventa);
			$this->registrarbalancefranquiciatario($idventa);
			$this->registrarbalancereferidor($idventa);
			
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		
		$query = "UPDATE negocio SET saldo = saldo - :esmarties WHERE id_negocio = :id_negocio";
		$params = array(':esmarties' => $this->sale['eSmarties'],':id_negocio' => $this->business['id']);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$query = "UPDATE usuario SET esmarties = esmarties + :esmarties WHERE id_usuario = :id_usuario";
		$params = array(':esmarties' => $this->sale['eSmarties'],':id_usuario' => $this->sale['id']);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($this->sale['referral_id'] && $this->sale['referral_commission']){
			$query = "UPDATE usuario SET esmarties = esmarties + :esmarties WHERE id_usuario = :id_usuario";
			$params = array(':esmarties' => $this->sale['referral_commission'],':id_usuario' => $this->sale['referral_id']);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
		}
		$_SESSION['notification']['success'] = 'Venta registrada exitosamente.';
		header('Location: '.HOST.'/negocio/ventas/');
		die();
		return;
	}



	private function registrarbalancesistema(int $idventa){


				
					$ultimobalance = $this->capturarultimobalancesistema();
					$comisionsistema = $this->sale['eSmarties'];
					
					$balance = $comisionsistema + $ultimobalance;

					$query = "insert into balancesistema(balance,id_venta,comision) values(:balance,:venta,:comision)";
					$stm  = $this->con->prepare($query);
					$stm->execute(array(':balance'=>$balance,
										':venta'=>$idventa,':comision'=>$comisionsistema));
				
	}

	private function registrarbalancehotel(int $idventa){

		$query = 'select h.id as hotel, h.comision from 
						hotel as h join huespedhotel as hh on h.id = hh.id_hotel
							join huesped as hu on hh.id_huesped = hu.id
							where hu.id_usuario = :usuario';
				$stm = $this->con->prepare($query);
				$stm->execute(array(':usuario'=>$this->sale['id']));

				$filas = $stm->fetch(PDO::FETCH_ASSOC);
				$idhotel = $filas['hotel'];
				$comisionhotel = $filas['comision'];

				if($idhotel > 0){
				
					$ultimobalance = $this->capturarultimobalancehotel($idhotel);
					$comisionhotelnew = ($this->sale['eSmarties'] * $comisionhotel / 100);
					$balance = ($this->sale['eSmarties'] * $comisionhotel / 100) + $ultimobalance;

					$query = "insert into balancehotel(balance,id_hotel,id_venta,comision) values(:balance,:hotel,:venta,:comision)";
					$stm  = $this->con->prepare($query);
					$stm->execute(array(':balance'=>$balance,':hotel'=>$idhotel,
										':venta'=>$idventa,':comision'=>$comisionhotelnew));
				}
		}

	private function registrarbalancefranquiciatario(int $idventa){

		$query = 'select fr.id as franquiciatario, fr.comision from 
						franquiciatario as fr join hotel as h on fr.codigo_hotel  = h.codigo 
							join huespedhotel as hh on h.id = hh.id_hotel 
							join huesped as hu on hh.id_huesped = hu.id 
							where hu.id_usuario = :usuario';
				$stm = $this->con->prepare($query);
				$stm->execute(array(':usuario'=>$this->sale['id']));

				$filas = $stm->fetch(PDO::FETCH_ASSOC);
				$idfranquiciatario = $filas['franquiciatario'];
				$comisionfranquiciatario = $filas['comision'];

				if($idfranquiciatario > 0){
				
					$ultimobalance = $this->capturarultimobalancefranquiciatario($idfranquiciatario);
					$comisionfranquiciatarionew = ($this->sale['eSmarties'] * $comisionfranquiciatario / 100);
					$balance = ($this->sale['eSmarties'] * $comisionfranquiciatario / 100) + $ultimobalance;

					$query = "insert into balancefranquiciatario(balance,id_franquiciatario,id_venta,comision) values(:balance,:franquiciatario,:venta,:comision)";
					$stm  = $this->con->prepare($query);
					$stm->execute(array(':balance'=>$balance,':franquiciatario'=>$idfranquiciatario,
										':venta'=>$idventa,':comision'=>$comisionfranquiciatarionew));
				}
		}

	private function registrarbalancereferidor(int $idventa){

		$query = 'select rf.id as referidor, rf.comision from 
						referidor as rf join hotel as h on rf.codigo_hotel  = h.codigo 
							join huespedhotel as hh on h.id = hh.id_hotel 
							join huesped as hu on hh.id_huesped = hu.id 
							where hu.id_usuario = :usuario';

				$stm = $this->con->prepare($query);
				$stm->execute(array(':usuario'=>$this->sale['id']));

				$filas = $stm->fetch(PDO::FETCH_ASSOC);
				$idReferidor = $filas['referidor'];
				$comisionreferidor = $filas['comision'];

				if($idReferidor > 0){
				
					$ultimobalance = $this->capturarultimobalancereferidor($idReferidor);
					$comisionreferidornew = ($this->sale['eSmarties'] * $comisionreferidor / 100);
					$balance = ($this->sale['eSmarties'] * $comisionreferidor / 100) + $ultimobalance;

					$query = "insert into balancereferidor(balance,id_referidor,id_venta,comision) values(:balance,:referidor,:venta,:comision)";
					$stm  = $this->con->prepare($query);
					$stm->execute(array(':balance'=>$balance,':referidor'=>$idReferidor,
										':venta'=>$idventa,':comision'=>$comisionreferidornew));
				}
				
		}

	private function capturarultimobalancehotel(int $idhotel){
		$query = "select balance from balancehotel where id_hotel =:hotel order by id desc LIMIT 1";
		$stm = $this->con->prepare($query);
		$stm->execute(array(':hotel'=>$idhotel));
		$balance = $stm->fetch(PDO::FETCH_ASSOC)['balance'];
		if($balance > 0){
			return $balance;
		}else{
			return 0;
		}
	}

	private function capturarultimobalancesistema(){
		$query = "select balance from balancesistema order by id desc LIMIT 1";
		$stm = $this->con->prepare($query);
		$stm->execute();
		$balance = $stm->fetch(PDO::FETCH_ASSOC)['balance'];
		if($balance > 0){
			return $balance;
		}else{
			return 0;
		}
	}

	private function capturarultimobalancefranquiciatario(int $idfranquiciatario){
		$query = "select balance from balancefranquiciatario where id_franquiciatario =:franquiciatario order by id desc LIMIT 1";
		$stm = $this->con->prepare($query);
		$stm->execute(array(':franquiciatario'=>$idfranquiciatario));
		$balance = $stm->fetch(PDO::FETCH_ASSOC)['balance'];
		if($balance > 0){
			return $balance;
		}else{
			return 0;
		}
	}

	private function capturarultimobalancereferidor(int $idreferidor){
		$query = "select balance from balancereferidor where id_referidor =:referidor order by id desc LIMIT 1";
		$stm = $this->con->prepare($query);
		$stm->execute(array(':referidor'=>$idreferidor));
		$balance = $stm->fetch(PDO::FETCH_ASSOC)['balance'];
		if($balance > 0){
			return $balance;
		}else{
			return 0;
		}
	}

	private function set_user($username = null){
		if($username){
			$this->sale['username'] = trim($username);
			if(!preg_match('/^[a-zA-Z0-9]+$/ui',$this->sale['username'])){
				$this->error['username'] = 'El nombre de usuario solo debe contener letras y números. No se permite acentos.';
				return false;
			}
			$query = "SELECT u.id_usuario, ur.id_usuario as referente
				FROM usuario u
				LEFT JOIN usuario_referencia ur ON u.id_usuario = ur.id_nuevo_usuario
				WHERE u.username = :username";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':username', $this->sale['username'], PDO::PARAM_STR);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				$this->sale['id'] = $row['id_usuario'];
				$this->sale['referral_id'] = $row['referente'];
				return;
			}
			$this->error['username'] = 'Esta persona no existe.';
			return false;
		}
		$this->error['username'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_total($total = null){
		if($total){
			$this->sale['total'] = $total;
			$total = filter_var($total, FILTER_VALIDATE_FLOAT);
			if(!$total){
				$this->error['total'] = 'Ingresa una cantidad correcta.';
				return false;
			}
			return;
		}
		$this->error['total'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_currency($currency = null){
		if(!empty($currency) && array_key_exists($currency, $this->currencies)){
			$this->sale['currency'] = $currency;
			return;
		}else{
			$this->error['currency'] = 'Este campo es obligatorio.';
			return false;
		}
	}

	private function set_eSmarties(){
		if($this->sale['total'] && $this->sale['currency']){
			if($this->sale['currency'] == 'MXN'){
				$rate = 1;
			}else{
				$converter = new \assets\libraries\CurrencyConverter\CurrencyConverter;
				$cacheAdapter = new \assets\libraries\CurrencyConverter\Cache\Adapter\FileSystem(dirname(dirname(__DIR__)) . '/assets/cache/');
				$cacheAdapter->setCacheTimeout(\DateInterval::createFromDateString('10 second'));
				$converter->setCacheAdapter($cacheAdapter);
				$rate = $converter->convert($this->sale['currency'], 'MXN');
			}
			$esmarties = $this->sale['total'] * $rate * ($this->sale['commission']/100);
			$this->sale['eSmarties'] = floor($esmarties * 10000)/10000;
			if($this->sale['referral_id']){
				$referral_commission = $esmarties * 0;
				$this->sale['referral_commission'] = floor($referral_commission * 10000)/10000;
			}
			return;
		}
		return false;
	}

	private function set_certificate($cert_id = null){
		if(empty($cert_id)){
			return;
		}
		if(array_key_exists($cert_id, $this->certificates)){
			$this->sale['certificate_id'] = $cert_id;
			return;
		}else{
			$this->error['certificate'] = 'Selecciona un certificado vigente.';
			return false;
		}
	}

	private function set_certificates($certificate = null){
		foreach ($certificate as $key => $value) {
			if($value == 1 || $value == 0){
				$query = "SELECT ne.id_negocio FROM usar_certificado uc INNER JOIN negocio_certificado ne ON uc.id_certificado = ne.id_certificado WHERE uc.id_uso = :id_uso";
				try{
					$stmt = $this->con->prepare($query);
					$stmt->bindValue(':id_uso', $key, PDO::PARAM_INT);
					$stmt->execute();
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
				if($row = $stmt->fetch()){
					if($row['id_negocio'] == $this->business['id']){
						$this->reserved[$key] = $value;
						continue;
					}
				}
			}
			$this->error['reserved'] = 'Error al validar certificado(s).';
		}
		return;
	}

	public function load_reserved_certificates(){
		$html = null;
		if($this->sale['username']){
			$now = date('Y/m/d H:i:s', time());
			$query = "SELECT uc.id_uso, ne.imagen, ne.nombre as certificado, ne.descripcion, ne.condiciones, ne.restricciones, ne.precio, ne.iso, u.nombre, u.apellido, u.username 
				FROM usar_certificado uc 
				INNER JOIN negocio_certificado ne ON uc.id_certificado = ne.id_certificado AND ne.id_negocio = :id_negocio AND ne.fecha_inicio < :now1 AND ne.fecha_fin > :now2
				INNER JOIN usuario u ON uc.id_usuario = u.id_usuario
				WHERE u.username = :username AND uc.situacion = 2";
			$params = array(
				':id_negocio' => $this->business['id'],
				':username' => $this->sale['username'],
				':now1' => $now,
				':now2' => $now
			);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$id = $row['id_uso'];
				$html .= 
				'<div class="row">
					<div class="col-sm-3">
						<img src="'.HOST.'/assets/img/business/certificate/'.$row['imagen'].'" class="img-responsive" alt="">
					</div>
					<div class="col-sm-9">
						<div class="row">
							<div class="col-sm-8">
								<div class="radio sideways">';
				if($this->reserved[$id] == 0){
					$html .= 
									'<input id="use-'.$id.'" type="radio" name="use_cert['.$id.']" value="1" required><label for="use-'.$id.'">Utilizar certificado</label>
									<input id="thrash-'.$id.'" type="radio" name="use_cert['.$id.']" value="0" checked required><label for="thrash-'.$id.'">Desechar certificado</label>';
				}else{
					$html .= 
									'<input id="use-'.$id.'" type="radio" name="use_cert['.$id.']" value="1" checked required><label for="use-'.$id.'">Utilizar certificado</label>
									<input id="thrash-'.$id.'" type="radio" name="use_cert['.$id.']" value="0" required><label for="thrash-'.$id.'">Desechar certificado</label>';

				}
				$html .=
								'</div>
							</div>
							<div class="col-sm-4">
								<div class="radio">
									<label>Valuado en: $'.number_format((float)$row['precio'], 2, '.', '').' '.$row['iso'].'</label>
								</div>
							</div>
						</div>
						<hr>
						<div class="form-group">
							<label>'._safe($row['certificado']).'</label>
							<p class="text-default">'._safe($row['descripcion']).'</p>
						</div>
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<p>Condiciones:<br>'._safe($row['condiciones']).'</p>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<p>Restricciones:<br>'._safe($row['restricciones']).'</p>
								</div>
							</div>
						</div>
					</div>
				</div>';
			}
		}
		if(!$html){
			$html = '<p class="text-default">No hay certificados reservados.</p>';
		}
		return $html;
	}

	public function get_balance(){
		$balance = number_format((float)$this->business['balance'], 2, ',', '.').' MXN';
		if($this->business['balance'] >= 0){
			$html = '<strong class="text-primary">'.$balance.'</strong>';
		}else{
			$html = '<span class="required"><strong class="mr20">'.$balance.'</strong>¡Advertencia! Si excedes el l&iacute;mite de -$500 tu negocio ser&aacute; suspendido automaticamente.</span>';
		}
		return $html;
	}

	public function get_clean_balance(){
		return $this->business['balance'];
	}

	public function get_username(){
		return _safe($this->sale['username']);
	}

	public function get_username_error(){
		if($this->error['username']){
			return '<p class="text-danger">'._safe($this->error['username']).'</p>';
		}
	}

	public function get_total(){
		return _safe($this->sale['total']);
	}

	public function get_total_error(){
		if($this->error['total']){
			return '<p class="text-danger">'._safe($this->error['total']).'</p>';
		}
	}

	public function get_currencies(){
		$html = null;
		foreach ($this->currencies as $key => $value) {
			if($key == 'MXN'){
				$html .= '<option value="'.$key.'" selected>'.$value.'</option>';
			}
			// else{
			// 	$html .= '<option value="'.$key.'">'.$value.'</option>';
			// }
		}
		return $html;
	}

	public function get_currency_error(){
		if($this->error['currency']){
			return '<p class="text-danger">'._safe($this->error['currency']).'</p>';
		}
	}

	public function get_commission(){
		return _safe($this->sale['commission']);
	}

	public function get_eSmarties(){
		return $this->sale['eSmarties'];
	}

	public function get_certificates(){
		$html = null;
		foreach ($this->certificates as $key => $value) {
			$price = number_format((float)$value['price'], 2, '.', '').' '.$value['currency'];
			$name = _safe($value['name']);
			$image = HOST.'/assets/img/business/certificate/'._safe($value['image']);
			if($this->sale['certificate_id'] == $key){
				$html .= '<option title="'.$name.' - '.$price.'" data-content="<img src=\''.$image.'\' class=\'meta-img img-rounded\' alt=\'\'><strong>'.$name.'</strong> '.$price.'" value="'.$key.'" selected>'.$name.'</option>';
			}else{
				$html .= '<option title="'.$name.' - '.$price.'" data-content="<img src=\''.$image.'\' class=\'meta-img img-rounded\' alt=\'\'><strong>'.$name.'</strong> '.$price.'" value="'.$key.'">'.$name.'</option>';
			}
		}
		return $html;
	}

	public function get_certificate_error(){
		if($this->error['certificate']){
			return '<p class="text-danger">'._safe($this->error['certificate']).'</p>';
		}
	}

	public function get_reserved_error(){
		if($this->error['reserved']){
			return '<p class="text-danger">'._safe($this->error['reserved']).'</p>';
		}
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
		file_put_contents(ROOT.'\assets\error_logs\sales_new_sale.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>