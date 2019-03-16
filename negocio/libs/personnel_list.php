<?php # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
namespace negocio\libs;
use assets\libs\connection;
use PDO;

class personnel_list {
	private $con;
	private $user = array('id' => null);
	private $business = array(
		'id' => null,
		'roles' => array(),
		'personnel' => array(),
		'admins' => 0
	);
	private $error = array(
		'warning' => null,
		'error' => null
	);


	public function __construct(connection $con){
		$this->con = $con->con;
		$this->business['id'] = $_SESSION['business']['id_negocio'];
		$this->user['id'] = $_SESSION['user']['id_usuario'];
		$this->load_data();
		return;
	}

	private function load_data(){
		$query = "SELECT n.nombre, p.id_preferencia 
			FROM negocio n 
			LEFT JOIN preferencia p ON p.llave = 'default_business' 
			WHERE n.id_negocio = :id_negocio";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['name'] = $row['nombre'];
			$this->business['preference_id'] = $row['id_preferencia'];
		}
		$query = "SELECT id_rol, rol FROM roles WHERE llave = 'business'";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->business['roles'][$row['id_rol']] = $row['rol'];
		}
		$query = "SELECT ne.id_empleado, ne.id_rol, ne.creado, u.username, u.email, u.imagen, u.nombre, u.apellido, u.telefono, u.activo 
			FROM negocio_empleado ne 
			INNER JOIN usuario u ON ne.id_empleado = u.id_usuario 
			WHERE id_negocio = :id_negocio
			ORDER BY  ne.id_rol, ne.creado ASC";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue('id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->business['personnel'][$row['id_empleado']] = array(
				'role_id' => $row['id_rol'],
				'created_at' => $row['creado'],
				'username' => $row['username'],
				'email' => $row['email'],
				'image' => $row['imagen'],
				'name' => $row['nombre'],
				'last_name' => $row['apellido'],
				'phone' => $row['telefono'],
				'active' => $row['activo']
			);
			if($row['id_rol'] == 4){
				$this->business['admins']++;
			}
		}
		return;
	}

	public function get_personnel(){
		$personnel = null;
		foreach ($this->business['personnel'] as $key => $value) {
			if(empty($value['image'])){
				$image = 'default.jpg';
			}else{
				$image = $value['image'];
			}
			if($value['active'] == 1){
				$status = ' green';
			}elseif($value['active'] == 2){
				$status = ' yellow';
			}else{
				$status = '';
			}
			if(empty($value['name']) && empty($value['last_name'])){
				$alias = '<em>'._safe($value['username']).'</em>';
			}else{
				$alias = _safe(trim($value['name'].' '.$value['last_name']));
			}
			$username = _safe($value['username']);
			$email = _safe($value['email']);
			$phone = _safe($value['phone']);
			$date = date('d/m/Y', strtotime($value['created_at']));
			$roles = null;
			if($value['role_id'] == 4 && $this->business['admins'] <= 1){
				$roles = $this->business['roles'][$value['role_id']];
			}else{
				foreach ($this->business['roles'] as $role_id => $role) {
					if($value['role_id'] != $role_id){
						$roles .= 
						'<form method="post" action="'._safe(HOST.'/negocio/personal/').'">
							<li><a href="#" class="change-role">'.$role.'</a></li>
							<input type="hidden" value="'.$key.'" name="employee_id">
							<input type="hidden" value="'.$role_id.'" name="role_id">
						</form>';
					}
				}
				$roles = '<div class="role-dropdown">
							<div class="dropdown">
								<button class="btn btn-primary btn-xs dropdown-toggle mimic-header-nav-user-image" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
									<span>'.$this->business['roles'][$value['role_id']].'</span> <i class="fa fa-chevron-down"></i>
								</button>
								<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
								'.$roles.'
								</ul>
							</div><!-- /.dropdown -->
						</div><!-- /.header-nav-user -->';
			}
			if($value['role_id'] == 4 && $this->business['admins'] <= 1){
				$delete = '';
			}else{
				$delete = 
				'<form method="post" action="'._safe(HOST.'/negocio/personal/').'">
					<input type="hidden" value="'.$key.'" name="delete_id">
					<button class="btn btn-xs btn-danger delete-employee" type="submit" name="delete_employee"><i class="fa fa-times m0"></i></button>
				</form>';
			}
			$personnel .= 
				'<tr>
					<td>
						<div class="user user-md">
							<a href="'.HOST.'/socio/'.$username.'" target="_blank"><img src="'.HOST.'/assets/img/user_profile/'.$image.'"></a>
							<div class="notification'.$status.'"></div>
						</div>
					</td>
					<td>
						'.$roles.'
					</td>
					<td>'.$alias.'</td>
					<td>'.$email.'</td>
					<td>'.$phone.'</td>
					<td>'.$date.'</td>
					<td>'.$delete.'</td>
				</tr>';
		}
		$html = 
			'<div class="table-responsive">
				<table class="table table-hover">
					<thead>
					<tr>
						<th>Estado</th>
						<th>Rol</th>
						<th>Nombre</th>
						<th>Correo electr&oacute;nico</th>
						<th>Tel&eacute;fono</th>
						<th>Registrado</th>
						<th>Eliminar</th>
					</tr>
					</thead>
					<tbody>
					'.$personnel.'
					</tbody>
				</table>
			</div>';
		return $html;
	}

	public function change_employee_role(array $post){
		if(!array_key_exists($post['role_id'], $this->business['roles']) || !array_key_exists($post['employee_id'], $this->business['personnel'])){
			$this->error['error'] = 'Error al tratar de cambiar el rol de un empleado.';
			return false;
		}else{
			$post['role_id'] = (int)$post['role_id'];
			$post['employee_id'] = (int)$post['employee_id'];
		}
		if($this->business['personnel'][$post['employee_id']]['role_id'] == 4 && $this->business['admins'] <= 1){
			$this->error['warning'] = 'No puedes cambiar el rol del último administrador del negocio.';
			return false;
		}
		$query = "UPDATE negocio_empleado SET id_rol = :id_rol WHERE id_negocio = :id_negocio AND id_empleado = :id_empleado";
		$params = array(
			':id_rol' => $post['role_id'],
			':id_negocio' => $this->business['id'],
			':id_empleado' => $post['employee_id']
		);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$_SESSION['notification']['success'] = 'Rol de empleado actualizado exitosamente.';
		if($this->user['id'] == $post['employee_id']){
			$_SESSION['business']['id_rol'] = $post['role_id'];
			if($post['role_id'] == 6 || $post['role_id'] == 5){
				header('Location: '.HOST.'/negocio/');
				die();
			}
		}
		header('Location: '.HOST.'/negocio/personal/');
		die();
		return;
	}

	public function delete_employee(array $post){
		if(!array_key_exists($post['delete_id'], $this->business['personnel'])){
			$this->error['error'] = 'Error al tratar de eliminar el empleado.';
			return false;
		}else{
			$post['delete_id'] = (int)$post['delete_id'];

		}
		if($this->business['personnel'][$post['delete_id']]['role_id'] == 4 && $this->business['admins'] <= 1){
			$this->error['warning'] = 'No puedes dar de baja al último administrador del negocio';
			return false;
		}
		// Borar el empleado del negocio
		$query = "DELETE FROM negocio_empleado WHERE id_negocio = :id_negocio AND id_empleado = :id_empleado";
		$params = array(
			':id_negocio' => $this->business['id'],
			':id_empleado' => $post['delete_id']
		);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		// Verificar si hay negocios restantes
		$query = "SELECT ne.id_negocio, n.url, n.nombre
			FROM negocio_empleado ne
			INNER JOIN negocio n ON ne.id_negocio = n.id_negocio
			WHERE ne.id_empleado = :id_usuario LIMIT 1";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue('id_usuario', $post['delete_id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$values = $row;
			// Si la preferencia actual es igual al negocio
			$query = "SELECT up.preferencia
				FROM usuario_preferencia up
				WHERE up.id_usuario = :id_usuario AND up.id_preferencia = :id_preferencia AND preferencia = :preferencia";
			$params = array(
				':id_usuario' => $post['delete_id'],
				':id_preferencia' => $this->business['preference_id'],
				':preferencia' => $this->business['id']
				);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				$query = "UPDATE usuario_preferencia 
					SET preferencia = :preferencia 
					WHERE id_usuario = :id_usuario AND id_preferencia = :id_preferencia";
				$params = array(
					':preferencia' => $values['id_negocio'],
					':id_usuario' => $post['delete_id'],
					':id_preferencia' => $this->business['preference_id']
					);
				try{
					$stmt = $this->con->prepare($query);
					$stmt->execute($params);
				}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
				}
			}
			if($this->user['id'] == $post['delete_id']){
				$_SESSION['business']['id_negocio'] = $values['id_negocio'];
				$_SESSION['business']['url'] = $values['url'];
				$_SESSION['notification']['success'] = 'Has sido removido del negocio "'.$this->business['name'].'" exitosamente. No tendrás mas acceso a ese negocio.'; 
				$_SESSION['notification']['info'] = 'Estás en el panel del negocio "'.$values['nombre'].'", el cual es tu nuevo negocio predeterminado. Puedes cambiar esta preferencia después.';
				header('Location: '.HOST.'/negocio/');
				die();
			}
		}else{ // Si no hay negocios restantes, se elimina la preferencia también
			$query = "DELETE FROM usuario_preferencia WHERE id_usuario = :id_usuario AND id_preferencia = :id_preferencia";
			$params = array(':id_usuario' => $post['delete_id'], ':id_preferencia' => $this->business['preference_id']);
			try{
				$stmt = $this->con->prepare($query);
				$stmt->execute($params);
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($this->user['id'] == $post['delete_id']){
				unset($_SESSION['business']);
				$_SESSION['notification']['success'] = 'Has sido removido del negocio "'.$this->business['name'].'" exitosamente. No tendrás mas acceso a ese negocio.';
				header('Location: '.HOST.'/socio/perfil/');
				die();
			}
		}
		$_SESSION['notification']['success'] = 'Empleado eliminado exitosamente.';
		header('Location: '.HOST.'/negocio/personal/');
		die();
		return;
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
		file_put_contents(ROOT.'\assets\error_logs\personnel_list.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>