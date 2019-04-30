<?php 
namespace Referidor\models;
use assets\libs\connection;
use PDO;

/**
 * @author Crespo Jhonatan 
 * @since 05/04/19
 * 
 */
class Usuarios {



	private $con;

	private $user = array('id' => null);

	// private $usuarios = array(
	// 	'id'       => null,
	// 	'username' => null,
	// 	'email'    => null,
	// 	'points'   => null,
	// 	'nombre'   => null,
	// 	'apellido' =>null
	// );
	// 
	
	private $usuarios = array();

	private $error = array(
		'warning' => null,
		'error' => null
	);

	private $hotel = array(
		'id' => null
	);

	private $referidor = array(
		'id' => null
	);

	public function __construct(connection $con){

		$this->con = $con->con;
		$this->user['id'] = $_SESSION['user']['id_usuario'];
		$this->hotel['id'] = $_SESSION['id_hotel'];
		$this->referidor['id'] = $_SESSION['id_referidor'];
		$this->Cargar();
		return;

	}






	private function Cargar(){
	
					$query = "select u.id_usuario, u.username, u.email, u.esmarties, u.imagen, u.nombre, 
					u.apellido, u.sexo, u.fecha_nacimiento, c.ciudad, p.pais, u.telefono, u.id_rol, u.activo, u.verificado, u.ultimo_login,
					u.creado
					FROM solicitudreferidor as srf 
					JOIN usuario as u on srf.id_usuario = u.id_usuario	
					LEFT JOIN ciudad as c ON u.id_ciudad = c.id_ciudad
					LEFT JOIN estado as e ON c.id_estado = e.id_estado
					LEFT JOIN pais as p ON e.id_pais = p.id_pais
					JOIN referidor as rf on srf.id_referidor = rf.id
					where rf.id = :referidor;
					";

						try {
							
							$stm = $this->con->prepare($query);
							$stm->bindParam(':referidor', $this->referidor['id']);
							$stm->execute();
							while($row = $stm->fetch()){
							$this->usuarios[$row['id_usuario']] = array(
								'username' => $row['username'],
								'email' => $row['email'],
								'eSmarties' => $row['esmarties'],
								'imagen' => $row['imagen'],
								'name' => $row['nombre'],
								'last_name' => $row['apellido'],
								'gender' => $row['sexo'],
								'birthdate' => $row['fecha_nacimiento'],
								'ciudad' => $row['ciudad'],
								'pais' => $row['pais'],
								'phone' => $row['telefono'],
								'role_id' => $row['id_rol'],
								'active' => $row['activo'],
								'verified' => $row['verificado'],
								'last_login' => $row['ultimo_login'],
								'created_at' => $row['creado']);

						}
						
						} catch (Exception $e) {
							$this->RegistrarError(__METHOD__,__LINE__,$e->getMessage());
						}
	}

	/**
	 * Description
	 * Mostrar en el catalogo del data table la info de todos los usuarios....
	 * @return type
	 */
	public function getUsuarios(){

		$urlimg =  HOST.'/assets/img/user_profile/';

	
		foreach($this->usuarios as $key => $valores) {

			if(empty($valores['imagen'])){
				$image = HOST.'/assets/img/user_profile/default.jpg';
			}else{
				$image = HOST.'/assets/img/user_profile/'._safe($valores['imagen']);
			}

			if($valores['active'] == 1){
				$status = ' green';
				$btn = '<button class="btn btn-xs btn-danger user-ban" name="ban_user" value="'.$key.'" type="submit"><i class="fa fa-ban m0"></i></button>';
			}elseif($valores['active'] == 2){
				$status = ' yellow';
				$btn = '<button class="btn btn-xs btn-danger user-ban" name="ban_user" value="'.$key.'" type="submit"><i class="fa fa-ban m0"></i></button>';
			}else{
				$status = '';
				$btn = '<button class="btn btn-xs btn-success user-ban" name="unban_user" value="'.$key.'" type="submit"><i class="fa fa-check-circle m0"></i></button>';
			}
			
			if($valores['gender'] == 1){
				$gender = 'Hombre';
			}elseif($valores['gender'] == 2){
				$gender = 'Mujer';
			}else{
				$gender = '';
			}

			$username = _safe($valores['username']);
			$name = _safe($valores['name']);
			$last_name = _safe($valores['last_name']);
			$email = _safe($valores['email']);
			$phone = _safe($valores['phone']);
			$date = date('d/m/Y', strtotime($valores['created_at']));
			$eSmarties = _safe($valores['eSmarties']);
			
			if(!empty($valores['birthdate'])){
				$birthdate = date('d/m/Y', strtotime($valores['birthdate']));
			}else{
				$birthdate = '';
			}
			
			if(!empty($valores['ciudad']) && !empty($valores['ciudad'])){
				$location = _safe($valores['ciudad'].', '.$valores['pais']);
			}else{
				$location = 'No registrado';
			}
			if($valores['verified'] == 1){
				$verified = 'S&iacute;';
			}elseif($valores['verified'] == 0){
				$verified = 'No';
			}else{
				$verified = '';
			}

			$last_login = date('d/m/Y', strtotime($valores['last_login']));

			?>
			<tr class="fila" id="fila-<?php echo $id_usuario; ?>" data-user="<?php echo $key; ?>" class="seleccioneusuario" style="cursor:pointer;" 
				data-href="<?php echo HOST.'/socio/'.$username; ?>" data-img="<?php echo $image; ?>" data-notificacion="<?php echo 'notification'.$status;?>" 
				data-username="<?php echo $username; ?>" data-name="<?php echo $name ?>" data-apellido="<?php echo $last_name ?>" data-email="<?php echo $email;?>"
				data-phone="<?php echo $phone ?>" data-date="<?php echo $date; ?>" data-cumple="<?php echo $birthdate; ?>" data-location="<?php echo $location; ?>" 
				data-verificado="<?php echo $verified ?>" data-ultimologin="<?php echo $last_login; ?>" data-gender="<?php echo $gender; ?>" data-puntos="<?php echo $eSmarties; ?>">
				
				<td><?php echo $key ?></td>
				<td>
					<div class="user user-md">
							<a href="<?php echo HOST.'/socio/'.$username; ?>" target="_blank"><img src="<?php echo $image; ?>"></a>
							<div class="<?php echo 'notification'.$status;?>"></div>
						</div>
				</td>
				<td><?php echo $username; ?></td>
				<td><?php echo $email; ?></td>
				<td><?php echo $eSmarties; ?></td>
				
				<td><?php echo $name; ?></td>
				<td><?php echo $last_name ?></td>
				
            </tr>

            	
			<?php
		}

	}


	public function get_roles(){
		$html = null;
		foreach ($this->admin['roles'] as $key => $value) {
			if($this->new_admin['role'] == $key){
				$html .= '<option value="'.$key.'" selected>'.$value.'</option>';
			}else{
				$html .= '<option value="'.$key.'">'.$value.'</option>';
			}
		}
		return $html;
	}

	/**
	 * Description
	 * Poner en suspension al Usuario...
	 * @param array $post 
	 * @return type
	 */
	public function Suspension(array $post){

		if($post['ban_user'] == $this->user['id']){
			$this->error['error'] = 'No puedes suspenderte a tí mismo.';
			return false;
		}

		if(!array_key_exists($post['ban_user'], $this->members)){
			$this->error['error'] = 'Error al tratar de suspender al usuario.';
			return false;
		}else{
			$id = (int)$post['ban_user'];
		}
		$query = "UPDATE usuario SET activo = 0 WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $id, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		// SE MANDA LA NOTIFICACION AL USUARIO
		$user_email = $this->members[$id]['email'];
		$username = $this->members[$id]['username'];
		$body_alt = 
			'Tu cuenta '.$username.' ha sido suspendida. Para cualquier aclaración contacta a Travel Points. soporte@esmartclub.com';
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
		$mail->addAddress($user_email);
		// Hacerlo formato HTML
		$mail->isHTML(true);
		// Formato del correo
		$mail->Subject = 'Tu cuenta ha sido suspendida.';
		$mail->Body    = $this->PlantillaCorreo();
		$mail->AltBody = $body_alt;
		// Enviar
		if(!$mail->send()){
			$_SESSION['notificacion']['info'] = 'El correo de aviso no se pudo enviar debido a una falla en el servidor.';
		}
		$_SESSION['notificacion']['success'] = 'Usuario suspendido exitosamente.';
		header('Location: '.HOST.'/admin/usuarios/');
		die();
		return;
	}



	/**
	 * [PlantillaCorreo description]
	 * Plantilla de correo para enviar al usuario suspendido o quitado su suspension...
	 */
	private function PlantillaCorreo(){
		$html = 
					'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
					<html xmlns="http://www.w3.org/1999/xhtml">
					<head>
					<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
					<meta name="viewport" content="width=device-width, initial-scale=1.0">
					<title>Tu cuenta ha sido suspendida</title>
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
																<img alt="Travel Points" src="'.HOST.'/assets/img/logo.png" style="padding-bottom: 0; display: inline !important;">
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
															<strong>Lamentamos informarte que tu cuenta ha sido suspendida</strong>
														</td>
													</tr>
													<tr>
														<td class="tablepadding" align="center" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
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

	/**
	 * Description
	 * Quitar suspension de usuarios.
	 * @param array $post 
	 * @return type
	 */
	public function QuitarSuspension(array $post){

		if($post['unban_user'] == $this->user['id']){
			$this->error['error'] = 'No puedes quitar tu suspensión a tí mismo.';
			return false;
		}
		if(!array_key_exists($post['unban_user'], $this->members)){
			$this->error['error'] = 'Error al tratar de suspender al usuario.';
			return false;
		}else{
			$id = (int)$post['unban_user'];
		}
		$query = "UPDATE usuario SET activo = 1 WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $id, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$_SESSION['notificacion']['success'] = 'El usuario ya no está suspendido.';
		header('Location: '.HOST.'/admin/usuarios/');
		die();
		return;
	}


	/**
	 * [Modal description]
	 * Cuando se le da click a alguna celda de la tabla de usuarios, este methodo es mostrado al usuario, con los datos detallados del usuario seleccionado ... 
	 */
	public function Modal(){?>
		

		<!-- Modal -->
		<div class="modal fade " id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
		  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		    <div class="modal-content">
		      <div class="modal-header">
		      	<div class="container-fluid fluid">
		      		<div class="row header-titulo">
		      			<style>
		      				.fluid{
		      					width: 100%;
		      				}
		      				.header-titulo{
		      					display: flex;
		      					justify-content: space-between;
		      					align-items: center;
		      				}
		      				.ultimo_login{
								flex: 1 1 300px;
								display: flex;
								justify-content: flex-end;
								margin-left: auto;	
		      				}
		      				.foto{
		      					flex: 1 1 400px;
		      					display: flex;
		      					justify-content: flex-start;
		      					-ms-align-items: center;
		      					align-items: center;
		      				}
		      			</style>
		      			<div class="col-xs-12 col-sm-6 foto">
		      				 
			        		<div class="user user-md">
								<a href="" target="_blank" id="url-img-user"><img id="img-user" src=""></a>
								<div class="" id="notificacion-img-user"></div>
							</div>
							 <h5 class="modal-title" id="label-title-name-user">
						
		        			</h5>
		      			</div>
		      			<div class="col-xs-12 col-sm-6">
		      				<h5 class="eSmarties" id="puntos"></h5>
		      			</div>

		      			<div class="col-xs-12 col-sm-6 ultimo_login">
		      				<label class="form title">Ultimo inicio de sesión: <strong id="ultimo-login"></strong></label>
		      			</div>
		      			
		      		</div>
		      	</div>
		      
		       
		      </div>
		      <div class="modal-body">
								<form>
								  <div class="form-row">

										<div class="form-group col-md-6">
											<label for="inputAddress">Nombre:</label>
											<input type="text" class="form-control" id="nombre" readonly>
										</div>
										<div class="form-group col-md-6">
											<label for="inputAddress">apellido:</label>
											<input type="text" class="form-control" id="apellido" readonly>
										</div>

										<div class="form-group col-md-4">
											<label for="inputAddress">Sexo:</label>
											<input type="text" class="form-control" id="sexo" readonly>
										</div>

										<div class="form-group col-md-4">
											<label for="inputAddress">Fecha de nacimiento:</label>
											<input type="text" class="form-control" id="fnacimiento" readonly>
										</div>
										<div class="form-group col-md-4">
											<label for="inputAddress">Email:</label>
											<input type="email" class="form-control" id="email" readonly>
										</div>

										<div class="form-group col-md-4">
											<label for="inputAddress">Teléfono:</label>
											<input type="text" class="form-control" id="telefono" readonly>
										</div>

										<div class="form-group col-md-8">
											<label for="inputAddress">Origen:</label>
											<textarea name="direcion" class="form-control" id="direccion" readonly></textarea>
										</div>

										<small class="form" id="verificado"></small>
								</form>
		      </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
		      
		      </div>
		    </div>
		  </div>
		</div>

		<script>
			$(document).ready(function(){

				$('.fila').click(function(){
						var hrf = $(this).attr('data-href');
						var img = $(this).attr('data-img');
						var notificacion = $(this).attr('data-notificacion');
						var username = $(this).attr('data-username');
						var name = $(this).attr('data-name');
						var apellido = $(this).attr('data-apellido');
						var ultimo_login = $(this).attr('data-ultimologin');
						var email = $(this).attr('data-email');
						var phone = $(this).attr('data-phone');
						var fnac = $(this).attr('data-cumple');s
						var location = $(this).attr('data-location');
						var puntos = $(this).attr('data-puntos');
						
						var verificado = $(this).attr('data-verificado');
						var gender = $(this).attr('data-gender');
						$('#url-img-user').attr({
							href: hrf
						});

						$('#img-user').attr({
							src: img
						});

						$('#notificacion-img-user').attr('class', notificacion);
						$('#label-title-name-user').text(username);

						$('#ultimo-login').text(ultimo_login);
						
						$('#nombre').val(name);
						$('#apellido').val(apellido);
						$('#email').val(email);
						$('#telefono').val(phone);
						$('#direccion').val(location);
						$('#sexo').val(gender);
						$('#fnacimiento').val(fnac);
						$('#verificado').text('Cuenta verificada: '+verificado);
						$('#ultimo-login').text(ultimo_login);
						$('#puntos').text('eSmarties: '+puntos);
						$('#exampleModalCenter').modal('show');

				});


			});
		</script>

	 <?php  }


	/**
	 * [getNotificacion description]
	 *  Notificaciones de alerta de usuarios ... 
	 * @return String retorna una cadena en texto plano con en html plano dependiendo de la alerta... 
	 */
	public function getNotificacion(){
		$html = null;
		if(isset($_SESSION['notificacion']['success'])){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-success" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notificacion']['success']).'
			</div>';
			unset($_SESSION['notificacion']['success']);
		}
		if(isset($_SESSION['notificacion']['info'])){
			$html .= 
			'<div class="alert alert-icon alert-dismissible alert-info" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<i class="fa fa-times" aria-hidden="true"></i>
				</button>
				'._safe($_SESSION['notificacion']['info']).'
			</div>';
			unset($_SESSION['notificacion']['info']);
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


	private function RegistrarError($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\listausuarioserror.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}

}
?>