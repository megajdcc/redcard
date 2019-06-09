<?php
namespace admin\libs;
use assets\libs\connection;
use PDO;

class admin_list {
	private $con;
	private $user = array('id' => null);
	private $admin = array(
		'roles' => array(),
		'admin' => array(),
		'super_admins' => 0
	);
	private $error = array(
		'warning' => null,
		'error' => null
	);


	public function __construct(connection $con){
		$this->con = $con->con;
		$this->user['id'] = $_SESSION['user']['id_usuario'];
		$this->load_data();	
		return;
	}

	private function load_data(){
		$query = "SELECT id_rol, rol FROM roles WHERE llave = 'admin'";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->admin['roles'][$row['id_rol']] = $row['rol'];
		}
		$query = "SELECT id_usuario, username, email, imagen, nombre, apellido, telefono, id_rol, activo, creado
			FROM usuario u 
			WHERE id_rol = 1 OR id_rol = 2 OR id_rol = 3 OR id_rol = 9
			ORDER BY  id_rol, creado ASC";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->admin['admin'][$row['id_usuario']] = array(
				'username' => $row['username'],
				'email' => $row['email'],
				'image' => $row['imagen'],
				'name' => $row['nombre'],
				'last_name' => $row['apellido'],
				'phone' => $row['telefono'],
				'role_id' => $row['id_rol'],
				'active' => $row['activo'],
				'created_at' => $row['creado']
			);
			if($row['id_rol'] == 1){
				$this->admin['super_admins']++;
			}
		}
		return;
	}

	public function get_admins(){
		$admins = null;
		foreach ($this->admin['admin'] as $key => $value) {
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
			if($value['role_id'] == 1 && $this->admin['super_admins'] <= 1){
				$roles = $this->admin['roles'][$value['role_id']];
			}else{
				foreach ($this->admin['roles'] as $role_id => $role) {
					if($value['role_id'] != $role_id){
						$roles .= 
						'<form method="post" action="'._safe(HOST.'/admin/usuarios/administradores').'">
							<li><a href="#" class="change-admin-role">'.$role.'</a></li>
							<input type="hidden" value="'.$key.'" name="admin_id">
							<input type="hidden" value="'.$role_id.'" name="role_id">
						</form>';
					}
				}
				$roles = '<div class="role-dropdown">
							<div class="dropdown">
								<button class="btn btn-primary btn-xs dropdown-toggle mimic-header-nav-user-image" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
									<span>'.$this->admin['roles'][$value['role_id']].'</span> <i class="fa fa-chevron-down"></i>
								</button>
								<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
								'.$roles.'
								</ul>
							</div><!-- /.dropdown -->
						</div><!-- /.header-nav-user -->';
			}
			if($value['role_id'] == 1 && $this->admin['super_admins'] <= 1){
				$delete = '';
			}else{
				$delete = 
				'<form method="post" action="'._safe(HOST.'/admin/usuarios/administradores').'">
					<input type="hidden" value="'.$key.'" name="delete_id">
					<button class="btn btn-xs btn-danger delete-admin" type="submit" name="delete_admin"><i class="fa fa-times m0"></i></button>
				</form>';
			}
			$admins .= 
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
					'.$admins.'
					</tbody>
				</table>
			</div>';
		return $html;
	}

	public function change_admin_role(array $post){
		if(!array_key_exists($post['role_id'], $this->admin['roles']) || !array_key_exists($post['admin_id'], $this->admin['admin'])){
			$this->error['error'] = 'Error al tratar de cambiar el rol de un administrador.';
			return false;
		}else{
			$post['role_id'] = (int)$post['role_id'];
			$post['admin_id'] = (int)$post['admin_id'];
		}
		if($this->admin['admin'][$post['admin_id']]['role_id'] == 1 && $this->admin['super_admins'] <= 1){
			$this->error['warning'] = 'No puedes cambiar el rol del último super administrador.';
			return false;
		}
		$query = "UPDATE usuario SET id_rol = :id_rol WHERE id_usuario = :id_usuario";
		$params = array(
			':id_rol' => $post['role_id'],
			':id_usuario' => $post['admin_id']
		);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($this->user['id'] == $post['admin_id']){
			$_SESSION['user']['id_rol'] = $post['role_id'];
		}
		if($post['role_id'] == 8){
			$query = "UPDATE codigo_administrador SET situacion = 2 WHERE id_usuario = :id_usuario";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_usuario', $post['admin_id'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($this->user['id'] == $post['admin_id']){
				unset($_SESSION['user']['admin_authorize']);
				$_SESSION['notification']['success'] = 'Has eliminado tus privilegios de administrador exitosamente.';
				header('Location: '.HOST.'/socio/perfil');
				die();
			}
		}
		$_SESSION['notification']['success'] = 'Rol de administrador actualizado exitosamente.';
		header('Location: '.HOST.'/admin/usuarios/administradores');
		die();
		return;
	}

	public function delete_admin(array $post){
		if(!array_key_exists($post['delete_id'], $this->admin['admin'])){
			$this->error['error'] = 'Error al tratar de eliminar el administrador.';
			return false;
		}else{
			$post['delete_id'] = (int)$post['delete_id'];
		}
		if($this->admin['admin'][$post['delete_id']]['role_id'] == 1 && $this->admin['super_admins'] <= 1){
			$this->error['warning'] = 'No puedes dar de baja al último administrador del negocio';
			return false;
		}
		$query = "UPDATE usuario SET id_rol = 8 WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $post['delete_id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$query = "UPDATE codigo_administrador SET situacion = 2 WHERE id_usuario = :id_usuario";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_usuario', $post['delete_id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($this->user['id'] == $post['delete_id']){
			$_SESSION['user']['id_rol'] = 8;
			unset($_SESSION['user']['admin_authorize']);
			$_SESSION['notification']['success'] = 'Has eliminado tus privilegios de administrador exitosamente.';
			header('Location: '.HOST.'/socio/perfil');
			die();
		}
		$_SESSION['notification']['success'] = 'Administrador eliminado exitosamente.';
		header('Location: '.HOST.'/admin/usuarios/administradores');
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
		file_put_contents(ROOT.'\assets\error_logs\admin_list.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>