<?php 

namespace Hotel\models;
use assets\libs\connection;
use PDO;

class DetallesSolicitud {
	private $con;
	private $reserved_words = array(
		'admin',
		'assets',
		'errors',
		'errores',
		'business',
		'negocio',
		'member',
		'socio',
		'tienda',
		'store',
		'affiliate-business',
		'afiliar-negocio',
		'ajax',
		'change-password',
		'cambiar-contrasena',
		'certificate',
		'certificado',
		'contact-us',
		'contacto',
		'hazte-socio',
		'signup',
		'index',
		'home',
		'listings',
		'listados',
		'login',
		'logout',
		'negocio_certificados',
		'negocio_eventos',
		'negocio_opiniones',
		'negocio_publicaciones',
		'about-us',
		'nosotros',
		'perfil_negocio',
		'perfil_socio',
		'faq',
		'preguntas-frecuentes',
		'what-is-esmart-club',
		'que-es-esmart-club',
		'recover-account',
		'recuperar-cuenta',
		'terms-of-service',
		'terminos-y-condiciones'
	);
	private $user = array(
		'id' => null
	);
	private $request = array();

	private $iatas = array();

	private $error = array(
		'codigo'               => null,
		'nombre'               => null,
		'direccion'            => null,
		'location'             => null,
		'sitio_web'            => null,
		'id_ciudad'            => null,
		'id_estado'            => null,
		'id_pais'              => null,
		'id_responsable'       => null,
		'id_datospagocomision' => null,
		'comision'             => 0,
		'aprobada'             => 0,
		'id_iata'              => null,
		'codigopostal'         => null,
		'nombre_responsable'   => null,
		'apellido_responsable' => null,
		'cargo'                => null,
		'email'                => null,
		'telefonomovil'        => null,
		'telefonofijo'         => null,
		'nombre_responsable'   => null,
		'banco'                => null,
		'banco_targeta'        => null,
		'banco_nombre_tarjeta' => null,
		'cuenta'               => null,
		'clabe'                => null,
		'swift'                => null,
		'numero_tarjeta'       => null,
		'email_paypal'         => null,
		'warning'              => null,
		'error'                => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->user['id'] = $_SESSION['user']['id_usuario'];
		return;
	}

	public function load_data($id = null){

	
			$query = "SELECT s.id,s.condicion,s.creado,s.actualizado,s.comentario, h.nombre, h.codigo, h.direccion, h.latitud, h.longitud, h.sitio_web,h.comision,h.aprobada,
						h.id_ciudad, c.ciudad, e.id_estado,e.estado, p.id_pais, p.pais,h.id_iata,h.codigo_postal, i.codigo as iata,dp.banco,dp.banco_tarjeta,dp.clabe,
						dp.cuenta, dp.email_paypal, dp.numero_tarjeta, dp.swift,
						rp.cargo, rp.email, rp.telefono_fijo, rp.telefono_movil,
						per.nombre as nombreresponsable, per.apellido		
						FROM solicitudhotel s 
						INNER JOIN hotel as h on s.id_hotel = h.id
						INNER JOIN ciudad as c on h.id_ciudad = c.id_ciudad
						INNER JOIN estado e ON c.id_estado = e.id_estado 
						INNER JOIN pais p ON e.id_pais = p.id_pais
						INNER JOIN iata as i on h.id_iata = i.id
						INNER JOIN datospagocomision dp on h.id_datospagocomision = dp.id
			 			INNER JOIN responsableareapromocion rp ON h.id_responsable_promocion = rp.id
						INNER JOIN persona as per on rp.dni_persona = per.id
						where s.id = :id_solicitud";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_solicitud', $id, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->request = array(
				'id'                => $row['id'],
				'nombre'            => $row['nombre'],
				'codigo'            => $row['codigo'],
				'direccion'         => $row['direccion'],
				'latitud'           => $row['latitud'],
				'longitud'          => $row['longitud'],
				'sitio_web'         => $row['sitio_web'],
				'comision'          => $row['comision'],
				'aprobada'          => $row['aprobada'],
				'id_ciudad'         => $row['id_ciudad'],
				'ciudad'            => $row['ciudad'],
				'id_estado'         => $row['id_estado'],
				'estado'         => $row['estado'],
				'id_pais'        => $row['id_pais'],
				'pais'           => $row['pais'],
				'id_iata'        => $row['id_iata'],
				'iata'           => $row['iata'],
				'banco'          => $row['banco'],
				'bancotarjeta'   => $row['banco_tarjeta'],
				'clabe'          => $row['clabe'],
				'cuenta'         => $row['cuenta'],
				'email_paypal'   => $row['email_paypal'],
				'numero_tarjeta' => $row['numero_tarjeta'],
				'swift'          => $row['swift'],
				'cargo'               => $row['cargo'],
				'email'               => $row['email'],
				'telefonofijo'       => $row['telefono_fijo'],
				'telefonomovil'      => $row['telefono_movil'],
				'nombreresponsable'   => $row['nombreresponsable'],
				'apellidoresponsable' => $row['apellido'],
				'condicion'           => $row['condicion'],
				'comentario'          => $row['comentario'],
				'codigopostal'        => $row['codigo_postal'],
				'creado'              =>$row['creado'],
				'actualizado'         =>$row['actualizado']
			);
			$query = "SELECT id, codigo FROM iata";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->iatas[$row['id']] = $row['codigo'];
			}
			return true;
			}else{
				return false;
			}
		
	}

	public function accept_request(array $post){
		$this->set_name($post['name']);
		$this->set_description($post['description']);
		$this->set_brief($post['brief']);
		$this->set_category_id($post['category_id']);
		$this->set_commission($post['commission']);
		$this->set_url($post['url']);
		$this->set_email($post['email']);
		$this->set_phone($post['phone']);
		$this->set_website($post['website']);
		$this->set_address($post['address']);
		$this->set_postal_code($post['postal_code']);
		$this->set_city_id($post['city_id']);
		$this->set_state_id($post['state_id']);
		$this->set_country_id($post['country_id']);
		$this->set_location($post['latitude'], $post['longitude']);
		$this->set_comment($post['comment']);
		$this->set_logo($_FILES);
		$this->set_photo($_FILES);
		if(!array_filter($this->error)){
			$query = "SELECT p1.id_preferencia as logo, p2.id_preferencia as header
				FROM preferencia p1 
				LEFT JOIN preferencia p2 ON p2.llave = 'business_header'
				WHERE p1.llave = 'business_logo'";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				$logo_id = $row['logo'];
				$header_id = $row['header'];
			}else{
				$this->error['error'] = 'Error de conexión a la base de datos.';
				return false;
			}
			$logo = $this->request['url'].'-logo-esmart-club'.substr(strrchr($this->request['logo'], "."), 0);
			$header = $this->request['url'].'-portada-esmart-club'.substr(strrchr($this->request['header'],"."), 0);
			if($this->images['logo']['tmp'] && $this->images['logo']['path']){
				if(file_exists(ROOT.'/assets/img/business_request/'.$this->request['logo'])){
					unlink(ROOT.'/assets/img/business_request/'.$this->request['logo']);
				}
				if(!move_uploaded_file($this->images['logo']['tmp'], $this->images['logo']['path'])){
					$this->error['error'] = 'Error al tratar de subir el logo.';
					return false;
				}
				$this->request['logo'] = $this->images['logo']['name'];
			}
			if($this->images['photo']['tmp'] && $this->images['photo']['path']){
				if(file_exists(ROOT.'/assets/img/business_request/'.$this->request['header'])){
					unlink(ROOT.'/assets/img/business_request/'.$this->request['header']);
				}
				if(!move_uploaded_file($this->images['photo']['tmp'], $this->images['photo']['path'])){
					$this->error['error'] = 'Error al tratar de subir la portada.';
					return false;
				}
				$this->request['header'] = $this->images['photo']['name'];
			}
			if(
				!copy(ROOT.'/assets/img/business_request/'.$this->request['logo'], ROOT.'/assets/img/business/logo/'.$logo) ||
				!copy(ROOT.'/assets/img/business_request/'.$this->request['header'], ROOT.'/assets/img/business/header/'.$header)
			){
				$this->error['error'] = 'Error al validar imágenes.';
				return false;
			}
			// INSERTAR EL NUEVO NEGOCIO
			$query = "INSERT INTO negocio (
				nombre, 
				descripcion, 
				breve,
				id_categoria, 
				comision, 
				url, 
				sitio_web, 
				direccion, 
				codigo_postal, 
				id_ciudad, 
				latitud, 
				longitud,
				id_solicitud
				) VALUES (
				:nombre, 
				:descripcion, 
				:breve,
				:id_categoria, 
				:comision, 
				:url, 
				:sitio_web, 
				:direccion, 
				:codigo_postal, 
				:id_ciudad, 
				:latitud, 
				:longitud,
				:id_solicitud
				)";
			$params = array(
				':nombre' => $this->request['name'],
				':descripcion' => $this->request['description'],
				':breve' => $this->request['brief'],
				':id_categoria' => $this->request['category_id'],
				':comision' => $this->request['commission'],
				':url' => $this->request['url'],
				':sitio_web' => $this->request['website'],
				':direccion' => $this->request['address'],
				':codigo_postal' => $this->request['postal_code'],
				':id_ciudad' => $this->request['city_id'],
				':latitud' => $this->request['latitude'],
				':longitud' => $this->request['longitude'],
				':id_solicitud' => $this->request['id']
			);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
				$last_id = $this->con->lastInsertId();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			// DEFINIR LA PREFERENCIA DEL LOGO
			$query = "INSERT INTO negocio_preferencia (id_negocio, id_preferencia, preferencia) VALUES (:id_negocio, :id_preferencia, :preferencia)";
			$params = array(':id_negocio' => $last_id, ':id_preferencia' => $logo_id, ':preferencia' => $logo);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			// DEFINIR LA PREFERENCIA DEL HEADER
			$query = "INSERT INTO negocio_preferencia (id_negocio, id_preferencia, preferencia) VALUES (:id_negocio, :id_preferencia, :preferencia)";
			$params = array(':id_negocio' => $last_id, ':id_preferencia' => $header_id, ':preferencia' => $header);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			// INSERTAR EL CORREO
			$query = "INSERT INTO negocio_email (id_negocio, email) VALUES (:id_negocio, :email)";
			$params = array(':id_negocio' => $last_id,':email' => $this->request['email']);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			// INSERTAR EL TELEFONO
			$query = "INSERT INTO negocio_telefono (id_negocio, telefono) VALUES (:id_negocio, :telefono)";
			$params = array(':id_negocio' => $last_id,':telefono' => $this->request['phone']);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			// CREAR EL HORARIO DE TRABAJO EN VACIO
			$query = "INSERT INTO negocio_horario (id_negocio, dia) VALUES 
				($last_id, 1),($last_id, 2),($last_id, 3),($last_id, 4),($last_id, 5),($last_id, 6),($last_id, 7)";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			// ASIGNAR EL USUARIO COMO ADMINISTRADOR DEL NEGOCIO
			$query = "INSERT INTO negocio_empleado (
				id_negocio, 
				id_empleado, 
				id_rol, 
				codigo_seguridad
				) VALUES (
				:id_negocio, 
				:id_empleado, 
				4,
				:codigo_seguridad
				)";
			$params = array(
				':id_negocio' => $last_id,
				':id_empleado' => $this->request['user_id'],
				':codigo_seguridad' => $this->request['password']
			);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			// BUSCAR EL ID DE PREFERENCIA DEL NEGOCIO DEFAULT Y LA PREFERENCIA DEL USUARIO QUE ENVIO LA SOLICITUD
			$query = "SELECT p.id_preferencia, up.preferencia 
				FROM preferencia p 
				LEFT JOIN usuario_preferencia up ON up.id_usuario = :id_usuario
				WHERE p.llave = 'default_business'";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue('id_usuario', $this->request['user_id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				if(is_null($row['preferencia'])){ // SI NO TIENE PREFERENCIA, SE LE CREA UNA NUEVA CON ESTE NUEVO NEGOCIO
					$query = "INSERT INTO usuario_preferencia (id_usuario, id_preferencia, preferencia) VALUES (:id_usuario, :id_preferencia, :preferencia)";
					$params = array(':id_usuario' => $this->request['user_id'], ':id_preferencia' => $row['id_preferencia'], ':preferencia' => $last_id);
					try{
						$stmt = $this->con->prepare($query);
						$stmt->execute($params);
					}catch(\PDOException $ex){
						$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
						return false;
					}
				}
			}
			// SE ACTUALIZA LA SITUACION DE LA SOLICITUD A ACEPTADA
			$query = "UPDATE solicitud_negocio SET 
				nombre = :nombre,
				descripcion = :descripcion,
				breve = :breve,
				id_categoria = :id_categoria,
				comision = :comision,
				url = :url,
				email = :email,
				telefono = :telefono,
				sitio_web = :sitio_web,
				direccion = :direccion,
				codigo_postal = :codigo_postal,
				id_ciudad = :id_ciudad,
				latitud = :latitud,
				longitud = :longitud,
				logo = :logo,
				foto = :foto,
				comentario = :comentario,
				situacion = 1,
				mostrar_usuario = 1
				WHERE id_solicitud = :id_solicitud";
			$params = array(
				':nombre' => $this->request['name'],
				':descripcion' => $this->request['description'],
				':breve' => $this->request['brief'],
				':id_categoria' => $this->request['category_id'],
				':comision' => $this->request['commission'],
				':url' => $this->request['url'],
				':email' => $this->request['email'],
				':telefono' => $this->request['phone'],
				':sitio_web' => $this->request['website'],
				':direccion' => $this->request['address'],
				':codigo_postal' => $this->request['postal_code'],
				':id_ciudad' => $this->request['city_id'],
				':latitud' => $this->request['latitude'],
				':longitud' => $this->request['longitude'],
				':logo' => $this->request['logo'],
				':foto' => $this->request['header'],
				':comentario' => $this->request['comment'],
				':id_solicitud' => $this->request['id'],
			);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			// SE MANDA LA NOTIFICACION AL USUARIO
			$header = 'Tu solicitud de negocio ha sido aceptada por eSmart Club';
			$link = 'Puedes ver tu negocio aquí: <a style="outline:none; color:#0082b7; text-decoration:none;" href="'.HOST.'/'.$this->request['url'].'">'.HOST.'/'.$this->request['url'].'</a>.';
			$body_alt = 'Tu solicitud de negocio ha sido aprobada por eSmart Club. Puedes ver tu negocio aquí: '.HOST.'/'.$this->request['url'];
			require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/phpmailer/PHPMailerAutoload.php';
			$mail = new \PHPMailer;
			$mail->CharSet = 'UTF-8';
			// $mail->SMTPDebug = 3; // CONVERSACION ENTRE CLIENTE Y SERVIDOR
			$mail->isSMTP();
			$mail->Host = 'a2plcpnl0735.prod.iad2.secureserver.net';
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = 'ssl';
			$mail->Port = 465;
			// El correo que hará el envío
			$mail->Username = 'notificacion@esmartclub.com';
			$mail->Password = 'Alan@2017_pv';
			$mail->setFrom('notificacion@esmartclub.com', 'eSmart Club');
			// El correo al que se enviará
			$mail->addAddress($this->request['user_email']);
			if($this->request['user_email'] != $this->request['email']){
				$mail->AddCC($this->request['email']);
			}
			// Hacerlo formato HTML
			$mail->isHTML(true);
			// Formato del correo
			$mail->Subject = 'Tu solicitud de negocio ha sido aceptada.';
			$mail->Body    = $this->email_template($header, $link);
			$mail->AltBody = $body_alt;
			// Enviar
			if(!$mail->send()){
				$_SESSION['notification']['info'] = 'El correo de aviso no se pudo enviar debido a una falla en el servidor.';
			}

			$_SESSION['notification']['success'] = 'Solicitud aceptada exitosamente. El negocio ha sido creado.';
			header('Location: '._safe($_SERVER['REQUEST_URI']));
			die();
			return;
		}
		$this->error['warning'] = 'Uno o más campos tienen errores. Verifícalos cuidadosamente.';
		return false;
	}

	public function check_request(array $post){
		$this->set_name($post['name']);
		$this->set_description($post['description']);
		$this->set_brief($post['brief']);
		$this->set_category_id($post['category_id']);
		$this->set_commission($post['commission']);
		$this->set_url($post['url']);
		$this->set_email($post['email']);
		$this->set_phone($post['phone']);
		$this->set_website($post['website']);
		$this->set_address($post['address']);
		$this->set_postal_code($post['postal_code']);
		$this->set_city_id($post['city_id']);
		$this->set_state_id($post['state_id']);
		$this->set_country_id($post['country_id']);
		$this->set_location($post['latitude'], $post['longitude']);
		$this->set_comment($post['comment']);
		$this->set_logo($_FILES);
		$this->set_photo($_FILES);
		if(!array_filter($this->error)){
			if($this->images['logo']['tmp'] && $this->images['logo']['path']){
				if(file_exists(ROOT.'/assets/img/business_request/'.$this->request['logo'])){
					unlink(ROOT.'/assets/img/business_request/'.$this->request['logo']);
				}
				if(!move_uploaded_file($this->images['logo']['tmp'], $this->images['logo']['path'])){
					$this->error['error'] = 'Error al tratar de subir el logo.';
					return false;
				}
				$this->request['logo'] = $this->images['logo']['name'];
			}
			if($this->images['photo']['tmp'] && $this->images['photo']['path']){
				if(file_exists(ROOT.'/assets/img/business_request/'.$this->request['header'])){
					unlink(ROOT.'/assets/img/business_request/'.$this->request['header']);
				}
				if(!move_uploaded_file($this->images['photo']['tmp'], $this->images['photo']['path'])){
					$this->error['error'] = 'Error al tratar de subir la portada.';
					return false;
				}
				$this->request['header'] = $this->images['photo']['name'];
			}
			$query = "UPDATE solicitud_negocio SET 
				nombre = :nombre,
				descripcion = :descripcion,
				breve = :breve,
				id_categoria = :id_categoria,
				comision = :comision,
				url = :url,
				email = :email,
				telefono = :telefono,
				sitio_web = :sitio_web,
				direccion = :direccion,
				codigo_postal = :codigo_postal,
				id_ciudad = :id_ciudad,
				latitud = :latitud,
				longitud = :longitud,
				logo = :logo,
				foto = :foto,
				comentario = :comentario,
				situacion = 3,
				mostrar_usuario = 3
				WHERE id_solicitud = :id_solicitud";
			$params = array(
				':nombre' => $this->request['name'],
				':descripcion' => $this->request['description'],
				':breve' => $this->request['brief'],
				':id_categoria' => $this->request['category_id'],
				':comision' => $this->request['commission'],
				':url' => $this->request['url'],
				':email' => $this->request['email'],
				':telefono' => $this->request['phone'],
				':sitio_web' => $this->request['website'],
				':direccion' => $this->request['address'],
				':codigo_postal' => $this->request['postal_code'],
				':id_ciudad' => $this->request['city_id'],
				':latitud' => $this->request['latitude'],
				':longitud' => $this->request['longitude'],
				':logo' => $this->request['logo'],
				':foto' => $this->request['header'],
				':comentario' => $this->request['comment'],
				':id_solicitud' => $this->request['id'],
			);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			// SE MANDA LA NOTIFICACION AL USUARIO
			$header = 'Hemos detectado inconsistencias en tu solicitud de negocio y ha sido regresada para su corrección';
			$link = '<a style="outline:none; color:#0082b7; text-decoration:none;" href="'.HOST.'/socio/negocios/solicitud/'.$this->request['id'].'">Ver mi solicitud</a>.';
			$body_alt =
				'Hemos detectado inconsistencias en tu solicitud de negocio y ha sido regresada para su revisión. Puedes ver tu solicitud aquí: '.HOST.'/socio/negocios/solicitud/'.$this->request['id'];
			require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/phpmailer/PHPMailerAutoload.php';
			$mail = new \PHPMailer;
			$mail->CharSet = 'UTF-8';
			// $mail->SMTPDebug = 3; // CONVERSACION ENTRE CLIENTE Y SERVIDOR
			$mail->isSMTP();
			$mail->Host = 'a2plcpnl0735.prod.iad2.secureserver.net';
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = 'ssl';
			$mail->Port = 465;
			// El correo que hará el envío
			$mail->Username = 'notificacion@esmartclub.com';
			$mail->Password = 'Alan@2017_pv';
			$mail->setFrom('notificacion@esmartclub.com', 'eSmart Club');
			// El correo al que se enviará
			$mail->addAddress($this->request['user_email']);
			if($this->request['user_email'] != $this->request['email']){
				$mail->AddCC($this->request['email']);
			}
			// Hacerlo formato HTML
			$mail->isHTML(true);
			// Formato del correo
			$mail->Subject = 'Debes corregir tu solicitud de negocio';
			$mail->Body    = $this->email_template($header, $link);
			$mail->AltBody = $body_alt;
			// Enviar
			if(!$mail->send()){
				$_SESSION['notification']['info'] = 'El correo de aviso no se pudo enviar debido a una falla en el servidor.';
			}
			$_SESSION['notification']['success'] = 'Solicitud regresada para correcciones exitosamente.';
			header('Location: '._safe($_SERVER['REQUEST_URI']));
			die();
			return;
		}
		$this->error['warning'] = 'Uno o más campos tienen errores. Verifícalos cuidadosamente.';
		return false;
	}

	public function reject_request(array $post){
		$this->set_comment($post['comment']);
		if(!array_filter($this->error)){
			$query = "UPDATE solicitud_negocio SET situacion = 4, mostrar_usuario = 4, comentario = :comentario WHERE id_solicitud = :id_solicitud";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':comentario', $this->request['comment'], PDO::PARAM_STR);
				$stmt->bindValue(':id_solicitud', $this->request['id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			// SE MANDA LA NOTIFICACION AL USUARIO
			$header = 'Lamentamos informarte que la solicitud para afiliar tu negocio ha sido rechazada';
			$link = '<a style="outline:none; color:#0082b7; text-decoration:none;" href="'.HOST.'/socio/negocios/solicitud/'.$this->request['id'].'">Ver mi solicitud</a>.';
			$body_alt =
				'Lamentamos informarte que la solicitud para afiliar tu negocio ha sido rechazada. Puedes ver tu solicitud aquí: '.HOST.'/socio/negocios/solicitud/'.$this->request['id'];
			require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/phpmailer/PHPMailerAutoload.php';
			$mail = new \PHPMailer;
			$mail->CharSet = 'UTF-8';
			// $mail->SMTPDebug = 3; // CONVERSACION ENTRE CLIENTE Y SERVIDOR
			$mail->isSMTP();
			$mail->Host = 'a2plcpnl0735.prod.iad2.secureserver.net';
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = 'ssl';
			$mail->Port = 465;
			// El correo que hará el envío
			$mail->Username = 'notificacion@esmartclub.com';
			$mail->Password = 'Alan@2017_pv';
			$mail->setFrom('notificacion@esmartclub.com', 'eSmart Club');
			// El correo al que se enviará
			$mail->addAddress($this->request['user_email']);
			if($this->request['user_email'] != $this->request['email']){
				$mail->AddCC($this->request['email']);
			}
			// Hacerlo formato HTML
			$mail->isHTML(true);
			// Formato del correo
			$mail->Subject = 'Solicitud para afiliar tu negocio rechazada';
			$mail->Body    = $this->email_template($header, $link);
			$mail->AltBody = $body_alt;
			// Enviar
			if(!$mail->send()){
				$_SESSION['notification']['info'] = 'El correo de aviso no se pudo enviar debido a una falla en el servidor.';
			}
			$_SESSION['notification']['success'] = 'Solicitud rechazada exitosamente';
			header('Location: '._safe($_SERVER['REQUEST_URI']));
			die();
			return;
		}
		$this->error['warning'] = 'Uno o más campos tienen errores. Verifícalos cuidadosamente.';
		return false;
	}

	public function delete_request(){
		$query = "UPDATE solicitud_negocio SET situacion = 0 WHERE id_solicitud = :id_solicitud";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_solicitud', $this->request['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$_SESSION['notification']['success'] = 'Solicitud eliminada exitosamente';
		header('Location: '.HOST.'/admin/negocios/solicitudes');
		die();
		return;
	}

	private function email_template($header, $link){
		$html = 
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>'._safe($header).'</title>
<style type="text/css">
@media only screen and (max-width: 600px) {
 table[class="contenttable"] {
 width: 320px !important;
 border-width: 3px!important;
}
 table[class="tablefull"] {
 width: 100% !important;
}
 table[class="tablefull"] + table[class="tablefull"] td {
 padding-top: 0px !important;
}
 table td[class="tablepadding"] {
 padding: 15px !important;
}
}
</style>
</head>
<body style="margin:0; border: none; background:#f7f8f9">
	<table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
		<tr>
			<td align="center" valign="top"><table class="contenttable" border="0" cellpadding="0" cellspacing="0" width="600" bgcolor="#ffffff" style="border-width: 8px; border-style: solid; border-collapse: separate; border-color:#e9e9e9; margin-top:40px; font-family:Arial, Helvetica, sans-serif">
				<tr>
					<td>
						<table border="0" cellpadding="0" cellspacing="0" width="100%">
							<tbody>
								<tr>
									<td width="100%" height="40">&nbsp;</td>
								</tr>
								<tr>
									<td valign="top" align="center">
										<a href="'.HOST.'" target="_blank">
											<img alt="eSmart Club" src="'.HOST.'/assets/img/logo.png" style="padding-bottom: 0; display: inline !important;">
										</a>
									</td>
								</tr>
								<tr>
									<td width="100%" height="40">&nbsp;</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td class="tablepadding" style="color: #444; padding:20px; font-size:14px; line-height:20px; border-top-width:1px; border-top-style:solid; border-top-color:#ececec;">
						<table border="0" cellpadding="0" cellspacing="0" width="100%">
							<tbody>
								<tr>
									<td align="center" class="tablepadding" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
										<strong>'._safe($header).'</strong>
									</td>
								</tr>
								<tr>
									<td class="tablepadding" align="center" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
										'.$link.'<br>
										Para cualquier aclaraci&oacute;n contacta a nuestro equipo de soporte.<br>
										<a style="outline:none; color:#0082b7; text-decoration:none;" href="mailto:soporte@esmartclub.com">
											soporte@esmartclub.com
										</a>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td bgcolor="#fcfcfc" class="tablepadding" style="padding:20px 0; border-top-width:1px;border-top-style:solid;border-top-color:#ececec;border-collapse:collapse">
						<table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-size:13px;color:#999999; font-family:Arial, Helvetica, sans-serif">
							<tbody>
								<tr>
									<td align="center" class="tablepadding" style="line-height:20px; padding:20px;">
										Marina Vallarta Business Center, Oficina 204, Plaza Marina.<br>
										Puerto Vallarta, México.<br>
										01 800 400 INFO (4636), (322) 225 9635.<br>
										<a style="outline:none; color:#0082b7; text-decoration:none;" href="mailto:info@infochannel.si">info@infochannel.si</a>
									</td>
								</tr>
							</tbody>
						</table>
						<table align="center">
							<tr>
								<td style="padding-right:10px; padding-bottom:9px;">
									<a href="https://www.facebook.com/eSmart-Club-130433773794677" target="_blank" style="text-decoration:none; outline:none;">
										<img src="'.HOST.'/assets/img/facebook.png" width="32" height="32" alt="Facebook">
									</a>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-size:13px;color:#999999; font-family:Arial, Helvetica, sans-serif">
				<tbody>
					<tr>
						<td class="tablepadding" align="center" style="line-height:20px; padding:20px;">
							&copy; eSmart Club 2017 Todos los derechos reservados.
						</td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>
</table>
</body>
</html>';
		return $html;
	}

	public function getSolicitud(){
		if($this->request['condicion'] == 1){
			
		
		}else{
			$html = 
				'<div class="background-white p30 mb50">
									<h3 class="page-title">Informaci&oacute;n de hotel</h3>
									<div class="row">

										<div class="col-lg-8">
								
											<div class="form-group" data-toggle="tooltip" title="Los clientes Huespedes de Travel Points pueden afiliarse desde su propio perfil...">
												<label for="business-name">Nombre del hotel <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>

												<input class="form-control" type="text" id="business-name" name="nombre" value="'.$this->getNombre().'" placeholder="Nombre del hotel" required />
												
											</div><!-- /.form-group -->
										
										</div><!-- /.col-* -->
										
										<div class="col-lg-4">
											<div class="row">
												<div class="col-sm-6 col-md-12 form-group" data-toggle="tooltip" title="El codigo Iata es utilizado para ayudar a agilizar los procesos de transporte aereo y turistico.">
													<label for="category">C&oacute;digo IATA <span class="required">*</span><i class="fa fa-question-circle text-secondary"></i></label>
													<select class="form-control" id="category" name="iata" title="Seleccionar c&oacute;digo IATA" required>
														<option value="null" selected>Seleccione</option>	
														'.$this->getIata().'
													</select>
												
												</div><!-- /.form-group -->
											</div>
										</div>
											<div class="col-sm-12">
											<div class="form-group" data-toggle="tooltip" title="Si no tienes sitio web, deja el espacio en blanco.">
												<label for="website">Sitio web del hotel <i class="fa fa-question-circle text-secondary"></i></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-globe"></i></span>
													<input class="form-control" type="text" id="website" name="website" value="'.$this->getSitioWeb().'" placeholder="Sitio web del hotel">
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->

									</div><!-- /.row -->
									
								
									
								</div><!-- /.box -->

								<div class="background-white p30 mb30">
									<h3 class="page-title">Ubicaci&oacute;n del hotel</h3>
									<div class="row">
										<div class="col-lg-8">
											<div class="form-group">
												<label for="address">Direcci&oacute;n del hotel <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-map-o"></i></span>
													<input class="form-control" type="text" id="address" name="direccion" value="'.$this->getDireccion().'" placeholder="Direcci&oacute;n del hotel" required >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="postal-code">C&oacute;digo postal  del hotel <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
													<input class="form-control" type="text" id="postal-code" name="codigopostal" value="'.$this->getCodigoPostal().'" placeholder="C&oacute;digo postal del hotel" required >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div><!-- /.row -->
									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<label for="country-select">Pa&iacute;s <span class="required">*</span></label>
												<select class="form-control" id="country-select" name="pais" title="Selecciona un pa&iacute;s" data-size="10" data-live-search="true" required>
													
												</select>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="state-select">Estado <span class="required">*</span></label>
												<select class="form-control" id="state-select" name="estado" title="Luego un estado" data-size="10" data-live-search="true" required>
													
												</select>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="city-select">Ciudad <span class="required">*</span></label>
												<select class="form-control" id="city-select" name="ciudad" title="Luego una ciudad" data-size="10" data-live-search="true" required>
													'.$this->getCiudades().'
												</select>
												
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div><!-- /.row -->
									<hr>
									<div class="form-group">
										<label for="map-canvas">Posici&oacute;n en el mapa <span class="required">*</span></label>
										<p>
											<ul>
												<li>Arrastra el marcador hacia la ubicaci&oacute;n de tu hotel.</li>
												<li>Puedes apoyarte escribiendo una ubicaci&oacute;n como una ciudad, municipio, colonia, etc. y seleccionar una de las opciones sugeridas.</li>
											</ul>
											Las coordenadas de la ubicaci&oacute;n se crean automaticamente.
										</p>
										'.$this->getLocationError().'
									</div>
									<input class="controls form-control mb30" type="text" id="pac-input" placeholder="Escribe una ubicaci&oacute;n" />
									<div id="map-canvas"></div>
									<div class="row">
										<div class="col-sm-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
												<input class="form-control" type="text" id="input-latitude" name="latitud" value="'.$this->getLatitud().'" placeholder="Latitud" required>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-sm-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
												<input class="form-control" type="text" id="input-longitude" name="longitud" value="'.$this->getLongitud().'" placeholder="Longitud" required>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div><!-- /.row -->
								</div><!-- /.box -->


								<div class="background-white p30 mb30">
									<h3 class="page-title">Responsable del &aacute;rea de promoci&oacute;n</h3>
									
									<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="nombre">Nombre<span class="required">*</span></label>
												<div class="input-group">
														<span class="input-group-addon"><i class="fa fa-address-card-o"></i></span>
													<input class="form-control" type="text" id="nombre_responsable" name="nombre_responsable" value="'.$this->getNombreResponsable().'" placeholder="Nombre del responsable &aacute;rea de promoci&oacute;n" required >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->

										<div class="col-lg-6">
											<div class="form-group">
												<label for="apellido">Apellido<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-address-card-o"></i></span>
													<input class="form-control" type="text" id="apellido_responsable" name="apellido_responsable" value="'.$this->getApellidoResponsable().'" placeholder="Apellido del responsable &aacute;rea de promoci&oacute;n" required >
												</div><!-- /.input-group -->
											
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div>
									
									
										
									
										<div class="row">
										<div class="col-lg-6">
											<div class="form-group">
												<label for="email">Email<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
													<input class="form-control" type="email" id="email" name="email" value="'.$this->getEmail().'" placeholder="Email del responsable" required >
												</div><!-- /.input-group -->
											
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										

										<div class="col-lg-6">
											<div class="form-group">
												<label for="cargo">Cargo<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-black-tie"></i></span>
													<input class="form-control" type="text" id="cargo" name="cargo" value="'.$this->getCargo().'" placeholder="Cargo" required >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->

											

										</div><!-- /.col-* -->
										<div class="col-lg-6">
										<div class="form-group">
												<label for="phone">T&eacute;lefono fijo <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-phone-square"></i></span>
													<input class="form-control" type="text" id="phone" name="telefonofijo" value="'.$this->getTelefonoFijo().'" placeholder="N&uacute;mero de t&eacute;lefono fijo" required >
												</div><!-- /.input-group -->
												'.$this->getTelefonoFijoError().'
											</div><!-- /.form-group -->
										</div>
										<div class="col-lg-6">
										<div class="form-group">
												<label for="phone">T&eacute;lefono novil <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-mobile-phone"></i></span>
													<input class="form-control" type="text" id="movil" name="movil" value="'.$this->getTelefonoMovil().'" placeholder="N&uacute;mero de t&eacute;lefono movil" required >
												</div><!-- /.input-group -->
												'. $this->getTelefonoMovilError().'
											</div><!-- /.form-group -->
										</div>
									
									</div><!-- /.row -->
								
								</div><!-- /.box -->
								
								<div class="background-white p30 mb30">
									<h3 class="page-title">Datos para el pago de comisiones</h3>
									
								
									<div class="row">

										<div class="col-lg-6 col-sm-4">
										<h5 class="page-title">Transferencia Bancaria</h5>
											<div class="form-group">
												<label for="nombre">Nombre del banco<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-bank"></i></span>
													<input class="form-control" type="text" id="nombre_banco" name="nombre_banco" value="'.$this->getBanco().'" placeholder="Nombre del banco" required >
												</div><!-- /.input-group -->
											
											</div><!-- /.form-group -->

											<div class="form-group">
												<label for="cuenta">Cuenta<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
													<input class="form-control" type="text" id="cuenta" name="cuenta" value="'.$this->getCuenta().'" placeholder="Cuenta." required >
												</div><!-- /.input-group -->
											
											</div><!-- /.form-group -->

											<div class="form-group">
												<label for="clabe">Clabe<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
													<input class="form-control" type="text" id="clabe" name="clabe" value="'.$this->getClabe().'" placeholder="Clabe" required >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->

											<div class="form-group">
												<label for="swift">Swift / Bic<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
													<input class="form-control" type="text" id="swift" name="swift" value="'.$this->getSwift().'" placeholder="Swift" required >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->

										</div><!-- /.col-* -->



										<div class="col-lg-6 col-sm-4">
											<h5 class="page-title">Deposito a tarjeta</h5>
											<div class="form-group">
												<label for="nombre">Nombre del banco<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-bank"></i></span>
													<input class="form-control" type="text" id="nombre_banco_targeta" name="nombre_banco_tarjeta" value="'.$this->getBancoNombreTarjeta().'" placeholder="Nombre del banco" required >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->
											<div class="form-group">
												<label for="nombre">N&uacute;mero de tarjeta<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-cc"></i></span>
													<input class="form-control" type="text" id="numero_targeta" name="numero_targeta" value="'.$this->getBancoNombreTarjeta().'" placeholder="N&uacute;mero de Tarjeta" required>
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->
								
										
												<h5 class="page-title">Transferencia PayPal</h5>
											<div class="form-group">
												<label for="nombre">Email de Paypal<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-cc-paypal"></i></span>
													<input class="form-control" type="email" id="email_paypal" name="email_paypal" value="'.$this->getEmailPaypal().'" placeholder="Nombre del banco" required >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->
										</div>
									</div>
								</div>';
		}
		return $html;
	}

	private function set_name($string = null){
		if($string){
			$string = trim($string);
			$this->request['name'] = $string;
			return true;
		}
		$this->error['name'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_description($string = null){
		if($string){
			$string = trim($string);
			$this->request['description'] = $string;
			return true;
		}
		$this->error['description'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_brief($string = null){
		if($string){
			$this->request['brief'] = trim($string);
			if(strlen($string) > 60){
				$this->error['brief'] = 'La descripción corta no debe exceder los 60 caracteres.';
				return false;
			}
			return true;
		}
		$this->error['brief'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_category_id($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				$this->error['category'] = 'Selecciona una categoría.';
				return false;
			}
			$this->request['category_id'] = $string;
			return true;
		}
		$this->error['category'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_commission($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 6 || $string > 100){
				$this->error['commission'] = 'Ingresa un número entero entre 6 y 100.';
				return false;
			}
			$this->request['commission'] = $string;
			return true;
		}
		$this->error['commission'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_url($string = null){
		if($string){
			$this->request['url'] = $string;
			$string = strtolower(trim($string));
			if(in_array($string,$this->reserved_words)){
				$this->error['url'] = 'La url del negocio no puede ser "'._safe($string).'", la cual es una palabra reservada.';
				return;
			}
			if(!preg_match('/^[a-z0-9-]+$/ui',$string)){
				$this->error['url'] = 'La url del negocio solo debe contener letras, números y guiones. No se permiten acentos, caracteres especiales o espacios.';
				return;
			}
			if(strlen($string) > 200){
				$this->error['url'] = 'La url del negocio excede los 200 caracteres.';
				return;
			}
			return;
		}
		$this->error['url'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_email($string = null){
		if($string){
			$email = filter_var($string, FILTER_VALIDATE_EMAIL);
			if(!$email){
				$this->error['email'] = 'Escribe una dirección de correo electrónico correcta. Ejemplo: usuario@ejemplo.com.';
				$this->request['email'] = $string;
				return false;
			}
			$this->request['email'] = $email;
			return true;
		}
		$this->error['email'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_phone($string = null){
		if($string){
			$string = trim($string);
			if(!preg_match('/^[0-9() +-]+$/ui',$string)){
				$this->error['phone'] = 'Escribe un número telefónico correcto. Ejemplo: (123) 456-78-90';
				$this->request['phone'] = $string;
				return false;
			}
			$this->request['phone'] = $string;
			return true;
		}
		$this->error['phone'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_website($string = null){
		if($string){
			if(!preg_match('_^(?:(?:https?|ftp)://)?(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,}))\.?)(?::\d{2,5})?(?:[/?#]\S*)?$_iuS',$string)){
				$this->error['website'] = 'Escribe un enlace correcto. Ejemplo: www.esmartclub.com o http://esmartclub.com';
				$this->request['website'] = $string;
				return false;
			}
			if(!preg_match("@^https?://@", $string)){
				$this->request['website'] = 'http://'.$string;
			}else{
				$this->request['website'] = $string;
			}
		}
		return true;
	}

	private function set_address($string = null){
		if($string){
			$string = trim($string);
			$this->request['address'] = $string;
			return true;
		}
		$this->error['address'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_postal_code($string = null){
		if($string){
			$string = trim($string);
			$this->request['postal_code'] = $string;
			return true;
		}
		$this->error['postal_code'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_city_id($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				$this->error['city'] = 'Selecciona una ciudad.';
				return false;
			}
			$this->request['city_id'] = $string;
			return true;
		}
		$this->error['city'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_state_id($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				return false;
			}
			$this->request['state_id'] = $string;
			return true;
		}
		return false;
	}

	private function set_country_id($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				return false;
			}
			$this->request['country_id'] = $string;
			return true;
		}
		return false;
	}

	private function set_location($lat = null, $lon = null){
		if($lat & $lon){
			if(!filter_var($lat, FILTER_VALIDATE_FLOAT) || !filter_var($lon, FILTER_VALIDATE_FLOAT)){
				$this->error['location'] = 'Utiliza el marcador del mapa para ubicar tu negocio.';
				return false;
			}else{
				$this->request['latitude'] = trim($lat);
				$this->request['longitude'] = trim($lon);
				return true;
			}
		}
		$this->error['location'] = 'Es obligatorio ubicar tu negocio en el mapa.';
		return false;
	}

	private function set_comment($comment = null){
		if($comment){
			$this->request['comment'] = trim($comment);
			return;
		}
		$this->error['comment'] = 'Este campo es obligatorio.';
		return false;
	}

	private function set_logo($files = null){
		$image = new \assets\libraries\bulletproof\bulletproof($files);
		$image->setLocation(ROOT.'/assets/img/business_request');
		if($image['logo']){
			if($image->upload()){
				$this->images['logo']['tmp'] = $files['logo']['tmp_name'];
				$this->images['logo']['name'] = $image->getName().'.'.$image->getMime();
				$this->images['logo']['path'] = $image->getFullPath();
				return true;
			}
			$this->error['logo'] = $image['error'];
			return false;
		}
		if($files['logo']['error'] == 1){
			$this->error['logo'] = 'Has excedido el límite de imagen de 2MB.';
		}
		return false;
	}

	private function set_photo($files = null){
		$image = new \assets\libraries\bulletproof\bulletproof($files);
		$image->setLocation(ROOT.'/assets/img/business_request');
		if($image['photo']){
			if($image->upload()){
				$this->images['photo']['tmp'] = $files['photo']['tmp_name'];
				$this->images['photo']['name'] = $image->getName().'.'.$image->getMime();
				$this->images['photo']['path'] = $image->getFullPath();
				return true;
			}
			$this->error['photo'] = $image['error'];
			return false;
		}
		if($files['photo']['error'] == 1){
			$this->error['photo'] = 'Has excedido el límite de imagen de 2MB.';
		}
		return false;
	}

	public function getHeader(){
		switch ($this->getCondicion()) {
			case 1:
				$status_tag = 
					'<span class="label label-md label-success mr20">Solicitud aceptada</span>';
				$form = 
					'<form class="display-inline-block" method="post" action="'._safe($_SERVER['REQUEST_URI']).'">
						<button class="btn btn-xs btn-danger" type="submit" id="delete-request" name="delete_request"><i class="fa fa-times m0"></i></button>
					</form>';
				break;
			case 0:
				$status_tag = '<span class="label label-md label-warning mr20">Solicitud pendiente</span>';
				$form = '';
				break;
			case 3:
				$status_tag = '<span class="label label-md label-info mr20">Corregir solicitud</span>';
				$form = '';
				break;
			case 4:
				$status_tag = '<span class="label label-md label-danger mr20">Solicitud rechazada</span>';
				$form = 
					'<form class="display-inline-block" method="post" action="'._safe($_SERVER['REQUEST_URI']).'">
						<button class="btn btn-xs btn-danger" type="submit" id="delete-request" name="delete_request"><i class="fa fa-times m0"></i></button>
					</form>';
				break;
			default:
				$status_tag = '';
				break;
		}
		$title_tag = '<label class="cert-date mr20">#'.$this->getId().'</label>'.$status_tag.'<label class="cert-date">'.$this->getFecha().'</label><span class="pull-right">Solicitud enviada por <a class="mr20" href="'.HOST.'/socio/'.$this->getNombre().'" target="_blank">'.$this->getNombreCompleto().'</a>'.$form.'</span>';
		if(!empty($this->request['comentario'])){
			$html = 
			'<div class="page-title">'.$title_tag.'</div>
			<label>Comentario de Travel Points para el solicitante</label>
			<p>'.nl2br(_safe($this->request['comentario'])).'</p>';
		}else{
			$html = $title_tag;
		}
		return $html;
	}


	//GETTERS Y SETTERS 
	public function getId(){
		return $this->request['id'];
	}

	public function getNombreResponsable(){
		return _safe($this->request['nombreresponsable']);
	}
	public function getApellidoResponsable(){
		return _safe($this->request['apellidoresponsable']);
	}

	public function getNombreCompleto(){
		if($this->request['nombreresponsable'] && $this->request['apellido']){
			return _safe($this->request['nombreresponsable'].' '.$this->request['apellido']);
		}else{
			return _safe($this->request['nombreresponsable']);
		}
	}

	public function getNombre(){
		return _safe($this->request['nombre']);
	}

	public function getNombreError(){
		if($this->error['nombre']){
			return '<p class="text-danger">'._safe($this->error['nombre']).'</p>';
		}
	}
	public function getIata(){
		return _safe($this->request['iata']);
	}

	public function getCargo(){
		return _safe($this->request['cargo']);
	}
	public function getBanco(){
		return _safe($this->request['banco']);
	}
	public function getBancoNombreTarjeta(){
		return _safe($this->request['bancotarjeta']);
	}
	public function getCuenta(){
		return _safe($this->request['cuenta']);
	}
	public function getClabe(){
		return _safe($this->request['clabe']);
	}
	public function getNumeroTarjeta(){
		return _safe($this->request['numero_tarjeta']);
	}

	public function getSwift(){
		return _safe($this->request['swift']);
	}

	public function getIatas(){
		$html = null;
		foreach ($this->iata as $key => $value) {
			if($this->request['id_iata'] == $key){
				$html .= '<option value="'.$key.'" selected>'._safe($value).'</option>';
			}else{
				$html .= '<option value="'.$key.'">'._safe($value).'</option>';
			}
		}
		return $html;
	}

	public function getIataError(){
		if($this->error['iata']){
			return '<p class="text-danger">'._safe($this->error['iata']).'</p>';
		}
	}

	public function getComision(){
		return _safe($this->request['commision']);
	}

	public function getComisionError(){
		if($this->error['commision']){
			return '<p class="text-danger">'._safe($this->error['commision']).'</p>';
		}
	}

	public function getEmail(){
		return _safe($this->request['email']);
	}
	public function getEmailPaypal(){
		return _safe($this->request['email_paypal']);
	}

	public function getEmailError(){
		if($this->error['email']){
			return '<p class="text-danger">'._safe($this->error['email']).'</p>';
		}
	}

	public function getTelefonoFijo(){
		return _safe($this->request['telefonofijo']);
	}

	public function getTelefonoFijoerror(){
		if($this->error['telefonofijo']){
			return '<p class="text-danger">'._safe($this->error['telefonofijo']).'</p>';
		}
	}

	public function getTelefonoMovil(){
		return _safe($this->request['telefonomovil']);
	}

	public function getTelefonoMovilerror(){
		if($this->error['telefonomovil']){
			return '<p class="text-danger">'._safe($this->error['telefonomovil']).'</p>';
		}
	}

	public function getSitioWeb(){
		return _safe($this->request['sitio_web']);
	}

	public function getSitioWebError(){
		if($this->error['sitio_web']){
			return '<p class="text-danger">'._safe($this->error['sitio_web']).'</p>';
		}
	}

	public function getDireccion(){
		return _safe($this->request['direccion']);
	}

	public function getDireccionError(){
		if($this->error['direccion']){
			return '<p class="text-danger">'._safe($this->error['direccion']).'</p>';
		}
	}

	public function getCodigoPostal(){
		return _safe($this->request['codigopostal']);
	}

	public function getCodigoPostalError(){
		if($this->error['codigopostal']){
			return '<p class="text-danger">'._safe($this->error['codigopostal']).'</p>';
		}
	}

	public function getPais(){
		return _safe($this->request['pais']);
	}

	public function getPaises(){
		$html = null;
		$query = "SELECT id_pais, pais FROM pais";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$pais = _safe($row['pais']);
			if($this->request['id_pais'] == $row['id_pais']){
				$html .= '<option value="'.$row['id_pais'].'" selected>'.$pais.'</option>';
			}else{
				$html .= '<option value="'.$row['id_pais'].'">'.$pais.'</option>';
			}
		}
		return $html;
	}

	public function getPaisError(){
		if($this->error['pais']){
			return '<p class="text-danger">'._safe($this->error['pais']).'</p>';
		}
	}

	public function getEstado(){
		return _safe($this->request['estado']);
	}

	public function getEstados(){
		$estados = null;
		if($this->request['id_pais']){
			$query = "SELECT id_estado, estado FROM estado WHERE id_pais = :id_pais";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_pais', $this->request['id_pais'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$estado = _safe($row['estado']);
				if($this->request['id_estado'] == $row['id_estado']){
					$estados .= '<option value="'.$row['id_estado'].'" selected>'.$estado.'</option>';
				}else{
					$estados .= '<option value="'.$row['id_estado'].'">'.$estado.'</option>';
				}
			}
		}
		return $estados;
	}

	public function getEstadoError(){
		if($this->error['estado']){
			return '<p class="text-danger">'._safe($this->error['estado']).'</p>';
		}
	}

	public function getCiudad(){
		return _safe($this->request['ciudad']);
	}

	public function getCiudades(){
		$ciudades = null;
		if($this->request['id_estado']){
			$query = "SELECT id_ciudad, ciudad FROM ciudad WHERE id_estado = :id_estado";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_estado', $this->request['id_estado'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$ciudad = _safe($row['ciudad']);
				if($this->request['id_ciudad'] == $row['id_ciudad']){
					$ciudades.= '<option value="'.$row['id_ciudad'].'" selected>'.$ciudad.'</option>';
				}else{
					$ciudades.= '<option value="'.$row['id_ciudad'].'">'.$ciudad.'</option>';
				}
			}
		}
		return $ciudades;
	}


	public function getCiudadError(){
		if($this->error['id_ciudad']){
			return '<p class="text-danger">'._safe($this->error['id_ciudad']).'</p>';
		}
	}

	public function getLatitud(){
		return _safe($this->request['latitud']);
	}

	public function getLongitud(){
		return _safe($this->request['longitud']);
	}

	public function getLocationError(){
		if($this->error['location']){
			return '<p class="text-danger">'._safe($this->error['location']).'</p>';
		}
	}

	public function getComentario(){
		return _safe($this->request['comentario']);
	}

	public function getComentarioError(){
		if($this->error['comentario']){
			return '<p class="text-danger">'._safe($this->error['comentario']).'</p>';
		}
	}

	public function getCondicion(){
		return $this->request['condicion'];
	}

	public function getFecha(){
		return date('d/m/Y g:i A', strtotime($this->request['creado']));
	}

	public function getNotificacion(){
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
		file_put_contents(ROOT.'\assets\error_logs\user_request_detail.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}

	private function friendly_url($url){
		$url = strtolower($this->replace_accents(trim($url))); // 1. Trim spaces around, replace all special chars and lowercase all
		$find = array(' ', '&', '\r\n', '\n', '+', ','); // 2. Reple spaces and union characters with ' - '
		$url = str_replace($find, '-', $url);
		$find = array('/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/'); // 3. Delete and replace the rest of special chars
		$repl = array('', ' ', '');
		$url = str_replace(' ', '-', trim(preg_replace($find, $repl, $url)));
		return $url;
	}

	private function replace_accents($var){
		$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
		$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
		$var = str_replace($a, $b, $var);
		return $var;
	}
}
?>