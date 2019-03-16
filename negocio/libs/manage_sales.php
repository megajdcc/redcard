<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace negocio\libs;
use assets\libs\connection;
use PDO;

class manage_sales {
	private $con;
	private $businessId;
	private $employeeId;
	private $commission;
	private $user = array ('id' => null, 'username' => null);
	private $total;
	private $currency;
	private $esmarties;
	private $referral_commission;
	private $token;
	private $certificates = array();
	private $errors = array('method' => null, 'user' => null, 'total' => null, 'currency' => null, 'esmarties' => null, 'token' => null, 'form' => null, 'certificate' => null);
	private $pagination = array('total' => null, 'rpp' => null, 'max' => null, 'page' => null, 'offset' => null);
	private $messages = array('success' => null);
	private $rates = array('MXN' => 1, 'USD' => 21, 'CAD' => 16, 'EUR' => 22.5);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->businessId = $_SESSION['business']['id_negocio'];
		$this->employeeId = $_SESSION['user']['id_usuario'];
		$this->loadData();
	}

	private function loadData(){
		$query = "SELECT comision FROM negocio WHERE id_negocio = :id_negocio";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->businessId, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->commission = $row['comision'];
		}

		return $this;
	}

	public function setData(array $post){
		$this->setUser($post['username']);
		$this->setTotal($post['total']);
		$this->setCurrency($post['currency']);
		$this->seteSmarties();
		// $this->setToken($post['token']);
		if(isset($post['use_cert'])){
			$this->set_certificate($post['use_cert']);
		}
		if($this->user['id'] && $this->total && $this->currency && $this->esmarties && !array_filter($this->errors)){
			$this->submitSale();
			return true;
		}
		return false;
	}

	private function submitSale(){
		foreach ($this->certificates as $key => $value) {
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
			$query_params = array(':situacion' => $value,':id_uso' => $key);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($query_params);
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
			bono_esmarties
			) VALUES (
			:id_usuario, 
			:id_negocio, 
			:id_empleado, 
			:iso, 
			:venta, 
			:comision, 
			:bono_esmarties
		)";
		$query_params = array(
			':id_usuario' => $this->user['id'],
			':id_negocio' => $this->businessId,
			':id_empleado' => $this->employeeId,
			':iso' => $this->currency,
			':venta' => $this->total,
			':comision' => $this->commission,
			':bono_esmarties' => $this->esmarties
		);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($query_params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$query = "UPDATE negocio SET saldo = saldo - :esmarties WHERE id_negocio = :id_negocio";
		$query_params = array(':esmarties' => $this->esmarties,':id_negocio' => $this->businessId);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($query_params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$query = "UPDATE usuario SET esmarties = esmarties + :esmarties WHERE id_usuario = :id_usuario";
		$query_params = array(':esmarties' => $this->esmarties,':id_usuario' => $this->user['id']);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($query_params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$query = "SELECT id_usuario FROM usuario_referencia WHERE id_nuevo_usuario = :id_nuevo_usuario";
		$query_params = array(':id_nuevo_usuario' => $this->user['id']);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($query_params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$query = "UPDATE usuario SET esmarties = esmarties + :esmarties WHERE id_usuario = :id_usuario";
			$query_params = array(':esmarties' => $this->referral_commission,':id_usuario' => $row['id_usuario']);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($query_params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
		}
		$_SESSION['business']['success'] = 'Venta registrada exitosamente.';
		header('Location: '.HOST.'/negocio/ventas/');
		die();
		return;
	}

	public function getSaleHistory(){
		if($this->errors['form']){
			$this->errors['form'] = 'Error de paginación';
			return $this;
		}
		$query = "SELECT nv.id_venta, u.username, u.nombre, u.apellido, nv.iso, nv.venta, nv.comision, nv.bono_esmarties, nv.creado, e.username as e_username, e.nombre as e_nombre, e.apellido as e_apellido 
			FROM negocio_venta nv
			LEFT JOIN usuario u ON nv.id_usuario = u.id_usuario
			LEFT JOIN usuario e ON nv.id_empleado = e.id_usuario 
			WHERE id_negocio = :id_negocio
			ORDER BY id_venta DESC
			LIMIT :limit OFFSET :offset";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue('id_negocio', $this->businessId, PDO::PARAM_INT);
			$stmt->bindValue(':limit', $this->pagination['rpp'], PDO::PARAM_INT);
			$stmt->bindValue(':offset', $this->pagination['offset'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		($this->pagination['page'] > 1) ? $foo = '?page='.$this->pagination['page'] : $foo = '';
		$fetched = false;
		while($row = $stmt->fetch()){
			if(!$fetched){ $fetched = true; }
			$id = $row['id_venta'];
			$name = htmlspecialchars($row['nombre'].' '.$row['apellido']);
			$username = htmlspecialchars($row['username']);
			$currency = htmlspecialchars($row['iso']);
			$total = number_format((float)$row['venta'], 2, '.', '');
			$commission = htmlspecialchars($row['comision']);
			$esmarties = rtrim(rtrim(htmlspecialchars($row['bono_esmarties']),'0'),'.');
			$eName = htmlspecialchars($row['e_nombre'].' '.$row['e_apellido']);
			$eUsername = htmlspecialchars($row['e_username']);
			$date = htmlspecialchars(date('m/d/Y h:i A', strtotime($row['creado'])));
			echo 
				'<div class="background-white p30 mb50">
					<div class="row">
						<div class="col-sm-12">
						<div class="form-group">
							<label for="sale-username">Nombre del cliente</label>
							<p id="sale-username"><a href="'.HOST.'/socio/'.$username.'" target="_blank">'.$name.'</a> @'.$username.'</p>
						</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<label for="sale-currency">Total de venta</label>
								<p id="sale-currency">'.$total.' '.$currency.'</p>
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<label for="sale-commission">Commisi&oacute;n</label>
								<p id="sale-commission">'.$commission.' %</p>
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<label for="sale-esmarties">eSmartties Bonificados</label>
								<p id="sale-esmarties">'.$esmarties.'</p>
							</div>
						</div>
					</div><!-- /.row -->
					<hr>
					<div class="form-group">
						<p id="sale-date">Registrado por <a href="'.HOST.'/socio/'.$username.'" target="_blank">'.$eName.'</a> @'.$eUsername.' on '.$date.'<span class="pull-right">#'.$id.'</span></p>
					</div>
				</div><!-- /.box -->';
		}
		if(!$fetched){
			echo 
			'<div class="background-white p30 mb50">
					<h4>No hay ventas registradas.</h4>
				</div><!-- /.box -->';
		}
		return $this;
	}

	private function setUser($username = null){
		if($username){
			if(!preg_match('/^[a-zA-Z0-9]+$/ui',$username)){
				$this->errors['username'] = 'El nombre de usuario solo debe contener letras y números. No se permite acentos.';
				$this->user['username'] = $username;
				return $this;
			}
			$query = "SELECT id_usuario FROM usuario WHERE username = :username";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':username', $username, PDO::PARAM_STR);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				$this->user['id'] = $row['id_usuario'];
				$this->user['username'] = $username;
				return $this;
			}
			$this->errors['user'] = 'Esta persona no existe.';
			$this->user['username'] = $username;
			return $this;
		}
		$this->errors['user'] = 'Este campo es obligatorio.';
		return $this;
	}

	private function setTotal($total = null){
		if($total){
			$total = str_replace(',','.',$total);
			$total = filter_var($total, FILTER_VALIDATE_FLOAT);
			if(!$total){
				$this->errors['total'] = 'Ingresa una cantidad correcta.';
				return $this;
			}
			$this->total = $total;
			return $this;
		}
		$this->errors['total'] = 'Este campo es obligatorio.';
		return $this;
	}

	private function setCurrency($currency = null){
		if($currency){
			if($currency == 'MXN' || $currency == 'USD' || $currency == 'CAD' || $currency == 'EUR'){
				$this->currency = $currency;
				return $this;
			}
			$this->errors['currency'] = 'Este campo es obligatorio.';
			return $this;
		}
		$this->errors['currency'] = 'Este campo es obligatorio.';
		return $this;
	}

	private function seteSmarties(){
		if($this->total && $this->currency){
			if($this->currency == 'MXN'){
				$rate = 1;
			}else{
				$converter = new \assets\libraries\CurrencyConverter\CurrencyConverter;
				$cacheAdapter = new \assets\libraries\CurrencyConverter\Cache\Adapter\FileSystem(dirname(dirname(__DIR__)) . '/assets/cache/');
				$cacheAdapter->setCacheTimeout(\DateInterval::createFromDateString('10 second'));
				$converter->setCacheAdapter($cacheAdapter);
				$rate = $converter->convert($this->currency, 'MXN');
			}
			$esmarties = $this->total * $rate * ($this->commission/100);
			$referral_commission = $esmarties * 0.1;
			$this->esmarties = floor($esmarties * 10000)/10000;
			$this->referral_commission = floor($referral_commission * 10000)/10000;
			return $this;
		}
		return $this;
	}

	private function setToken($token = null){
		if($token){
			$query = "SELECT codigo_seguridad FROM negocio_empleado WHERE id_empleado = :id_empleado AND id_negocio = :id_negocio";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_empleado', $this->employeeId, PDO::PARAM_INT);
				$stmt->bindValue(':id_negocio', $this->businessId, PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				if(password_verify($token,$row['codigo_seguridad'])){
					$this->token = true;
					return $this;
				}
				$this->errors['token'] = 'Authorization Code does not match.';
				return $this;
			}
		}
		$this->errors['token'] = 'You must enter your authorization code.';
		return $this;
	}

	private function set_certificate($certificate = null){
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
					if($row['id_negocio'] == $this->businessId){
						$this->certificates[$key] = $value;
						continue;
					}
				}
			}
			$this->errors['certificate'] = 'Error al validar certificado(s).';
		}
	}

	public function get_username_cert(){
		$html = null;
		if($this->user['username']){
			$query = "SELECT uc.id_uso, ne.imagen, ne.nombre as certificado, ne.descripcion, ne.condiciones, ne.restricciones, ne.precio, ne.iso, u.nombre, u.apellido, u.username 
				FROM usar_certificado uc 
				INNER JOIN negocio_certificado ne ON uc.id_certificado = ne.id_certificado AND ne.id_negocio = :id_negocio AND ne.fecha_inicio < now() AND ne.fecha_fin > now() 
				INNER JOIN usuario u ON uc.id_usuario = u.id_usuario
				WHERE u.username = :username AND uc.situacion = 2";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_negocio', $_SESSION['business']['id_negocio'], PDO::PARAM_STR);
				$stmt->bindValue(':username', $this->user['username'], PDO::PARAM_STR);
				$stmt->execute();
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
				if($this->certificates[$id] == 0){
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
									<label>Valuado en: $'.$this->min_precision($row['precio']).' '.$row['iso'].'</label>
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
				</div>
				<hr>';
			}
		}
		return $html;
	}

	public function setPagination($page = null, $rpp = null){
		if($page && $rpp){
			$page = filter_var($page, FILTER_VALIDATE_INT);
			if(!$page){
				$this->errors['pagination'] = 'Current page must be INT.';
				return $this;
			}
			$rpp = filter_var($rpp, FILTER_VALIDATE_INT);
			if(!$rpp){
				$this->errors['pagination'] = 'Rows per page must be INT.';
				return $this;
			}
			$query = "SELECT COUNT(*) FROM negocio_venta WHERE id_negocio = :id_negocio";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_negocio', $this->businessId, PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return;
			}
			if($row = $stmt->fetch()){
				$this->pagination['total'] = $row['COUNT(*)'];
				$this->pagination['rpp'] = $rpp;
				$this->pagination['max'] = (int)ceil($this->pagination['total'] / $this->pagination['rpp']);
				$this->pagination['page'] = min($this->pagination['max'], $page);
				$this->pagination['offset'] = ($this->pagination['page'] - 1) * $this->pagination['rpp'];
				return $this;
			}
		}
		$this->errors['pagination'] = 'Pagination not properly initialized.';
		return $this;
	}

	public function getSuccessMessage(){
		if(isset($_SESSION['business']['success'])){
			$msg = 
			'<div class="alert alert-icon alert-dismissible alert-success" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['business']['success']).'
			</div>';
			unset($_SESSION['business']['success']);
			return $msg;
		}
	}

	public function getMethodError(){
		if($this->errors['method']){
			$error = 
			'<div class="alert alert-icon alert-dismissible alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				<strong>Oh no! </strong> It looks like we are having technical issues. Sorry for the inconvenience. Please try again later <i class="fa fa-smile-o" aria-hidden="true"></i>.
			</div>';
			return $error;
		}
	}

	public function getUser(){
		return _safe($this->user['username']);
	}

	public function getUserError(){
		if($this->errors['user']){
			$error = '<p class="text-danger">'._safe($this->errors['user']).'</p>';
			return $error;
		}
	}

	public function getTotal(){
		return _safe($this->total);
	}

	public function getTotalError(){
		if($this->errors['total']){
			$error = '<p class="text-danger">'._safe($this->errors['total']).'</p>';
			return $error;
		}
	}

	public function getCurrencies(){
		if(!$this->currency){
			$query = "SELECT preferencia FROM negocio_preferencia WHERE id_negocio = :id_negocio AND id_preferencia = 2";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_negocio', $this->businessId, PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				$this->currency = $row['preferencia'];
			}
		}
		$query = "SELECT * FROM divisa";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			if($this->currency == $row['iso']){
				echo '<option value="'.$row['iso'].'" selected>'.$row['iso'].'</option>';
			}else{
				echo '<option value="'.$row['iso'].'">'.$row['iso'].'</option>';
			}
		}
	}

	public function getCurrencyError(){
		if($this->errors['currency']){
			$error = '<p class="text-danger">'._safe($this->errors['currency']).'</p>';
			return $error;
		}
	}

	public function getCommission(){
		return _safe($this->commission);
	}

	public function geteSmarties(){
		return _safe($this->esmarties);
	}

	public function getTokenError(){
		if($this->errors['token']){
			$error = '<p class="text-danger">'._safe($this->errors['token']).'</p>';
			return $error;
		}
	}

	public function get_certificate_error(){
		if($this->errors['certificate']){
			$error = '<p class="text-danger">'._safe($this->errors['certificate']).'</p>';
			return $error;
		}
	}

	public function getPage(){
		return $this->pagination['page'];
	}

	public function getTotalPage(){
		return $this->pagination['total'];
	}

	private function min_precision($x){
		$p = 2;
		$e = pow(10,$p);
		return floor($x*$e)==$x*$e?sprintf("%.${p}f",$x):$x;
	}

	private function error_log($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\sales.txt', '['.date('d/M/Y h:i:s A').' on '.$method.' on line '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		foreach ($this->errors as $key => $value){
			$this->errors[$key] = null;
		}
		$this->errors['method'] = true;
		return $this;
	}
}
?>