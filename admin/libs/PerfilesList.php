<?php 

namespace admin\libs;
use assets\libs\connection;
use PDO;

class PerfilesList {
	private $con;
	private $user = array('id' => null);
	private $perfiles = array();
	private $suspend = array(
		0 => 'Dar de baja',
		1 => 'Activar',
		2 => 'Suspender'
	);
	private $error = array(
		'warning' => null,
		'error' => null
	);
	private $pagination = array(
		'total' => null,
		'rpp' => null,
		'max' => null,
		'page' => null,
		'offset' => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->user['id'] = $_SESSION['user']['id_usuario'];
		return;
	}


	public function ListarReferidores(){
			$sql = "(select srf.condicion,CONCAT(u.nombre,' ',u.apellido) as referidor,h.nombre as nombrehotel, u.username,u.telefono,u.ultimo_login,u.email,'Referidor' as proviene, srf.id as nrosolicitud,u.imagen, u.nombre, u.apellido,rf.comision
					from solicitudreferidor as srf join usuario as u on srf.id_usuario = u.id_usuario join referidor as rf on srf.id_referidor = rf.id join hotel as h on rf.codigo_hotel = h.codigo
					where srf.condicion = 1)";
				
		$result = $this->con->prepare($sql);
		$result->execute();

		$urlimg =  HOST.'/assets/img/user_profile/';
		while ($fila = $result->fetch(PDO::FETCH_ASSOC)) {


			if(!empty($fila['franquiciatario'])){
				$nombre = $fila['franquiciatario'];
				
			}else{
				$nombre = $fila['username'];
			}
			
			$nombrehotel       = $fila['nombrehotel'];
			$apellido     = $fila['apellido'];
			$proviene     = $fila['proviene'];
			$nrosolicitud = $fila['nrosolicitud'];
			$foto         = $fila['imagen'];
			if(empty($foto) || is_null($foto)){
				$foto = 'default.jpg';
			}
			$comision     = $fila['comision'];
			$email = $fila['email'];
			$username = $fila['username'];
			$ultimologin = date('d/m/Y g:i A', strtotime($fila['ultimo_login']));
			$telefono = $fila['telefono'];
			?>
			<tr id="<?php echo $nrosolicitud; ?>">
				
			
				<td>
				
					<div class="user user-md">
						<a href="<?php echo HOST."/socio/".$username; ?>" target="_blank"><img src="<?php echo $urlimg.$foto;?>"></a>
					</div>
					
				</td>
				<td><?php echo $nombrehotel; ?></td>
				<td><?php  echo $nombre?></td>
			
				<td style="text-align: center;"><?php  echo $comision .' %';?>
					<button type="button"  data-toggle="tooltip" title="Actualizar Comisión." data-placement="left" data-comision="<?php echo $comision; ?>" data-solicitud="<?php echo $nrosolicitud; ?>" data-perfil="<?php echo $proviene; ?>" class="actualizarcomision  pull-right">
						<i class="fa fa-pencil-square-o"></i>
					</button>
					
				</td>
				
				<td><?php echo $ultimologin; ?></td>

			
				<td>
					<button type="button" data-toggle="tooltip" title="Editar" data-placement="left" data-solicitud="<?php echo $nrosolicitud; ?>" data-perfil="<?php echo $proviene; ?>" class="actualizarperfil btn-xs pull-right">
					 Ver / Editar <i class="fa fa-cogs"></i> 
					</button>
				</td>
            </tr>
			<?php
		}
	}

	public function ListarFranquiciatario(){
			$sql = "(select sfr.condicion,CONCAT(u.nombre,' ',u.apellido) as franquiciatario,h.nombre as nombrehotel, u.username,u.telefono,u.ultimo_login,u.email,'Franquiciatario' as proviene, sfr.id as nrosolicitud,u.imagen, u.nombre, u.apellido,fr.comision
					from solicitudfr as sfr join usuario as u on sfr.id_usuario = u.id_usuario join franquiciatario as fr on sfr.id_franquiciatario = fr.id join hotel as h on fr.codigo_hotel = h.codigo
					where sfr.condicion = 1)";
				
		$result = $this->con->prepare($sql);
		$result->execute();

		$urlimg =  HOST.'/assets/img/user_profile/';
		while ($fila = $result->fetch(PDO::FETCH_ASSOC)) {


			if(!empty($fila['franquiciatario'])){
				$nombre = $fila['franquiciatario'];
				
			}else{
				$nombre = $fila['username'];
			}
			
			$nombrehotel       = $fila['nombrehotel'];
			$apellido     = $fila['apellido'];
			$proviene     = $fila['proviene'];
			$nrosolicitud = $fila['nrosolicitud'];
			$foto         = $fila['imagen'];
			if(empty($foto) || is_null($foto)){
				$foto = 'default.jpg';
			}
			$comision     = $fila['comision'];
			$email = $fila['email'];
			$username = $fila['username'];
			$ultimologin = date('d/m/Y g:i A', strtotime($fila['ultimo_login']));
			$telefono = $fila['telefono'];
			?>
			<tr id="<?php echo $nrosolicitud; ?>">
				
			
				<td>
				
					<div class="user user-md">
						<a href="<?php echo HOST."/socio/".$username; ?>" target="_blank"><img src="<?php echo $urlimg.$foto;?>"></a>
					</div>
					
				</td>
				<td><?php echo $nombrehotel; ?></td>
				<td><?php  echo $nombre?></td>
			
				<td style="text-align: center;"><?php  echo $comision .' %';?>
					<button type="button"  data-toggle="tooltip" title="Actualizar Comisión." data-placement="left" data-comision="<?php echo $comision; ?>" data-solicitud="<?php echo $nrosolicitud; ?>" data-perfil="<?php echo $proviene; ?>" class="actualizarcomision  pull-right">
						<i class="fa fa-pencil-square-o"></i>
					</button>
					
				</td>
				
				<td><?php echo $ultimologin; ?></td>

			
				<td>
					<button type="button" data-toggle="tooltip" title="Editar" data-placement="left" data-solicitud="<?php echo $nrosolicitud; ?>" data-perfil="<?php echo $proviene; ?>" class="actualizarperfil btn-xs pull-right">
					 Ver / Editar <i class="fa fa-cogs"></i> 
					</button>
				</td>
            </tr>
			<?php
		}
	}

	public function ListarHoteles(){
				$sql = "SELECT h.imagen, h.id as idhotel,h.comision, h.nombre as nombrehotel, h.codigo, concat(c.ciudad,' ',e.estado,' ',p.pais) as direccion from hotel as h join ciudad as c on h.id_ciudad = c.id_ciudad join estado as e on h.id_estado = e.id_estado join pais as p on e.id_pais = p.id_pais";
				
		$result = $this->con->prepare($sql);
		$result->execute();

		$urlimg =  HOST.'/assets/img/hoteles/';
		while ($fila = $result->fetch(PDO::FETCH_ASSOC)) {
			
			$nombrehotel       = $fila['nombrehotel'];
			$direccion     = $fila['direccion'];
			
			$foto         = $fila['imagen'];
			if(empty($foto) || is_null($foto)){
				$foto = 'default.jpg';
			}
			$comision     = $fila['comision'];
			
			$direccion = $fila['direccion'];
			$idhotel = $fila['idhotel'];

			$sql = "SELECT count(*)  as cuenta from solicitudhotel where id_hotel = :hotel";

			$stm = $this->con->prepare($sql);
			$stm->bindParam(':hotel',$idhotel);

			$stm->execute();
			$fila2 = $stm->fetch(PDO::FETCH_ASSOC);


			$usershotel = $fila2['cuenta'];

			?>
			<tr id="<?php echo $idhotel; ?>">
				
			
				<td>
				
					<div class="user user-md detail-gallery-preview">
						<a href="<?php echo $urlimg.$foto;?>">
							<img class="img-thumbnail img-rounded" src="<?php echo $urlimg.$foto;?>">
						</a>
					</div>
					
				</td>
				<td><?php echo $nombrehotel; ?></td>
				<td><?php  echo $direccion?></td>
			
				<td style="text-align: center;"><?php  echo $comision .' %';?>
					<button type="button"  data-toggle="tooltip" title="Actualizar Comisión." data-placement="left" data-comision="<?php echo $comision; ?>" data-solicitud="<?php echo $idhotel; ?>" data-perfil="<?php echo 'Hotel'; ?>" class="actualizarcomision  pull-right">
						<i class="fa fa-pencil-square-o"></i>
					</button>
					
				</td>
				

			
				<td style="display:flex;padding-top: 20px; padding-right: 0px !important; justify-content: flex-end;padding-left: 0px;margin: 0px;">
					<?php if($usershotel > 0){
						$sql = "SELECT * from usuario u join solicitudhotel as su on u.id_usuario = su.id_usuario where su.id_hotel = :hotel";

						$stm = $this->con->prepare($sql);
						$stm->bindParam(':hotel',$idhotel);
						$stm->execute();

						$fila4 = $stm->fetch(PDO::FETCH_ASSOC);	


						if($fila4['activo'] == 1){
						$status = ' green';
						$btn = '<button class="btn btn-xs btn-danger user-ban" name="ban_user" value="" type="submit"><i class="fa fa-ban m0"></i></button>';
						}elseif($fila4['activo'] == 2){
						$status = ' yellow';
						$btn = '<button class="btn btn-xs btn-danger user-ban" name="ban_user" value="" type="submit"><i class="fa fa-ban m0"></i></button>';
						}else{
						$status = '';
						$btn = '<button class="btn btn-xs btn-success user-ban" name="unban_user" value="" type="submit"><i class="fa fa-check-circle m0"></i></button>';
						}

						$imagen = 'default.jpg';
						if(!empty($fila4['imagen'])){
							$imagen = $fila4['imagen'];
						}

						$nameuser = $fila4['username'];

						if(!empty($fila4['nombre']) && !empty($fila4['apellido'])){
							$nameuser = $fila4['nombre'].' '.$fila4['apellido'];
						}

						?>

						<style>
							.notification .green{
								display: flex !important;
								z-index: 100;
							}
						</style>
						<div class="user user-md">
							<a data-toggle="tooltip" title="<?php echo $nameuser; ?>" data-placement="left" href="<?php echo HOST.'/socio/'.$fila4['username']?>" target="_blank"><img src="<?php echo HOST.'/assets/img/user_profile/'.$imagen;?>"></a>
							<div class="notification<?php echo $status;?>"></div>
						</div>

						<button data-toggle="tooltip" title="Quitar." data-placement="left"  data-user="<?php echo $fila4['id_usuario'];?>" class="quitaruserhotel" style="background: transparent; outline: none; border: 1px; display:flex;justify-content: center;"><i class="fa fa-remove"></i></button>
					
					

				<?php }else{?>
					<button class="new-user-hotel btn btn-info" data-hotel="<?php echo $idhotel ?>" data-toggle="tooltip" title="Asignar usuario para este hotel.." data-placement="left">
				 	<i class="fa fa-black-tie"></i>Asignar
					</button>
				<?php  }?>	
					<button type="button" data-toggle="tooltip" title="Editar" data-placement="left" data-solicitud="<?php echo $idhotel; ?>" data-perfil="<?php echo 'Hotel'; ?>" class="actualizarperfil btn-xs">
					 Ver / Editar <i class="fa fa-cogs"></i> 
					</button>
				
				</td>
            </tr>
			<?php
		}
	}
	public function ListarPerfiles(){

		$sql = "select h.id as idhotel,h.codigo, h.nombre as nombrehotel,h.imagen,concat(c.ciudad,' ',e.estado,' ',p.pais) as direccion
    from hotel as h join ciudad as c on h.id_ciudad = c.id_ciudad 
    				join estado as e on h.id_estado = e.id_estado
                    join pais as p on e.id_pais = p.id_pais";
		$result = $this->con->prepare($sql);
		$result->execute();

		$urlimg =  HOST.'/assets/img/hoteles/';
		while ($fila = $result->fetch(PDO::FETCH_ASSOC)) {			
			
			$idhotel = $fila['idhotel'];
			$foto         = $fila['imagen'];
			if(empty($foto) || is_null($foto)){
				$foto = 'default.jpg';
			}
			

			$sql1 = "select max(u.ultimo_login) as ultimologin from usuario as u join solicitudfr as sfr on u.id_usuario = sfr.id_usuario
			join franquiciatario as f on sfr.id_franquiciatario = f.id 
			join hotel as h on f.id_hotel = h.id
			where h.id = :idhotel";
		

			$stm = $this->con->prepare($sql1);
			$stm->bindParam(':idhotel',$fila['idhotel']);

			$stm->execute();

			$fila2 = $stm->fetch(PDO::FETCH_ASSOC);


			$sql  = "select count(*) as cuenta from franquiciatario as fr where id_hotel = :hotel1";
			
			$sql2 = "select count(*) as cuenta from referidor as rf where id_hotel = :hotel2";


			$stm = $this->con->prepare($sql);
			$stm->execute(array(':hotel1' =>$idhotel));

			$stm1 = $this->con->prepare($sql2);
			$stm1->execute(array(':hotel2' =>$idhotel));



			$filafr = $stm->fetch(PDO::FETCH_ASSOC);
			$filarf = $stm1->fetch(PDO::FETCH_ASSOC);

			
			
			$franquiciatarios = $filafr['cuenta'];
	
			$referidores        = $filarf['cuenta'];


			if(empty($fila['ultimologin'])){
				$ultimologin = 'Sin inicio de sesi&oacute;n';
			}else{
				$ultimologin = date('d/m/Y g:i A', strtotime($fila2['ultimologin']));
			}
			
			?>
			<tr id="<?php echo $idhotel; ?>">
				
			
				<td>
				
					<div class="user user-md detail-gallery-preview">
						<a href="<?php echo $urlimg.$foto;?>">
							<img class="img-thumbnail img-rounded" src="<?php echo $urlimg.$foto;?>">
						</a>
					</div>
					
				</td>
				<td><?php echo $fila['nombrehotel'] ?></td>
				<td><?php echo $fila['direccion']; ?></td>
				<td>

					<?php  
						if($franquiciatarios == 0){
							
							echo '<button type="button" data-hotel="'.$idhotel.'" class="new-franquiciatario agr-franquiciatario btn btn-info"> <i class="fa fa-black-tie"></i> Asignar</button>';

						}else{
								echo '<button type="button" class="ver-franquiciatario btn btn-success" data-hotel="'.$idhotel.'"> <i class="fa fa-black-tie"></i> '.$franquiciatarios.' Ver</button>';
						}?>
					
						
					</td>
				
				
				<td><?php  
						if($referidores== 0){
							
							echo '<button type="button" data-hotel="'.$idhotel.'" class="new-referidor agr-referidor btn btn-secondary"> <i class="fa fa-black-tie"></i> Asignar</button>';

						}else{
								echo '<button type="button" data-hotel="'.$idhotel.'" class="ver-referidor btn btn-success"> <i class="fa fa-black-tie"></i>'.$referidores.' Ver</button>';
						}?></td>
			
				
				<td><?php echo $ultimologin; ?></td>

            </tr>
			<?php
		}

	}
	public function actualizarcomision(array $post){

			if($post['perfil'] == 'hotel'){

				$this->con->beginTransaction();


				$query = "update hotel set comision = :comision where id = :solicitud";

				try {

					$stm = $this->con->prepare($query);

				$stm->execute(array(':comision'=>$post['comision'],
									':solicitud'=> $post['solicitud']));
					$this->con->commit();

				} catch (PDOException $e) {
					$this->con->rollBack();
					return true;
				}
				
				return true;

			}else if($post['perfil'] == 'franquiciatario'){
				$this->con->beginTransaction();


				$query = "UPDATE franquiciatario set comision = :comision where id =:franquiciatario";

				try {

					$stm = $this->con->prepare($query);

				$stm->execute(array(':comision'=>$post['comision'],
									':franquiciatario'=> $post['solicitud']));
					$this->con->commit();

				} catch (PDOException $e) {
					$this->con->rollBack();
					return true;
				}
				
				return true;
			}else if($post['perfil'] == 'referidor'){

				$this->con->beginTransaction();
				$query = "update referidor set comision = :comision where id =:referidor";

				try {

					$stm = $this->con->prepare($query);

				$stm->execute(array(':comision'=>$post['comision'],
									':referidor'=> $post['solicitud']));
					$this->con->commit();

				} catch (PDOException $e) {
					$this->con->rollBack();
					return true;
				}
				
				return true;

			}

			return;


	}
	public function load_data($search = null, $page = null, $rpp = null){
		if($search){
			$search_query = "WHERE n.nombre LIKE :search1 OR n.breve LIKE :search2 OR n.descripcion LIKE :search3";
		}else{
			$search_query = '';
		}
		$query = "SELECT COUNT(*) FROM negocio n $search_query";
		try{
			$stmt = $this->con->prepare($query);
			if($search){
				$stmt->bindValue(':search1', '%'.$search.'%', PDO::PARAM_STR);
				$stmt->bindValue(':search2', '%'.$search.'%', PDO::PARAM_STR);
				$stmt->bindValue(':search3', '%'.$search.'%', PDO::PARAM_STR);
			}
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->pagination['total'] = $row['COUNT(*)'];
			$this->pagination['rpp'] = $rpp;
			$this->pagination['max'] = (int)ceil($this->pagination['total'] / $this->pagination['rpp']);
			$this->pagination['page'] = min($this->pagination['max'], $page);
			$this->pagination['offset'] = ($this->pagination['page'] - 1) * $this->pagination['rpp'];
			// Variables retornables
			$pagination['page'] = $this->pagination['page'];
			$pagination['total'] = $this->pagination['total'];
			// Cargar los certificados
			$query = "SELECT n.id_negocio, n.nombre, nc.categoria, n.comision, n.url, c.ciudad, p.pais, n.saldo, n.situacion, n.creado, l.preferencia as logo, u.username, u.nombre as u_nombre, u.apellido, 
				(SELECT ne.email
				FROM negocio_email ne
				WHERE ne.id_negocio = n.id_negocio
				LIMIT 1) as email,
				(SELECT nt.telefono
				FROM negocio_telefono nt
				WHERE nt.id_negocio = n.id_negocio
				LIMIT 1) as telefono
				FROM negocio n
				INNER JOIN solicitud_negocio s ON n.id_solicitud = s.id_solicitud
				INNER JOIN usuario u ON s.id_usuario = u.id_usuario 
				INNER JOIN negocio_categoria nc ON n.id_categoria = nc.id_categoria
				INNER JOIN ciudad c ON n.id_ciudad = c.id_ciudad
				INNER JOIN estado e ON c.id_estado = e.id_estado
				INNER JOIN pais p ON e.id_pais = p.id_pais
				INNER JOIN preferencia pr
				INNER JOIN negocio_preferencia l ON l.id_negocio = n.id_negocio AND l.id_preferencia = pr.id_preferencia AND pr.llave = 'business_logo'
				$search_query
				ORDER BY n.creado ASC
				LIMIT :limit OFFSET :offset";
			try{
				$stmt = $this->con->prepare($query);
				if($search){
					$stmt->bindValue(':search1', '%'.$search.'%', PDO::PARAM_STR);
					$stmt->bindValue(':search2', '%'.$search.'%', PDO::PARAM_STR);
					$stmt->bindValue(':search3', '%'.$search.'%', PDO::PARAM_STR);
				}
				$stmt->bindValue(':limit', $this->pagination['rpp'], PDO::PARAM_INT);
				$stmt->bindValue(':offset', $this->pagination['offset'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$this->perfiles[$row['id_negocio']] = array(
					'name' => $row['nombre'],
					'category' => $row['categoria'],
					'commission' => $row['comision'],
					'url' => $row['url'],
					'city' => $row['ciudad'],
					'country' => $row['pais'],
					'balance' => $row['saldo'],
					'logo' => $row['logo'],
					'email' => $row['email'],
					'phone' => $row['telefono'],
					'username' => $row['username'],
					'u_name' => $row['u_nombre'], 
					'last_name' => $row['apellido'],
					'status' => $row['situacion'],
					'created_at' => $row['creado']
				);
			}
		return $pagination;
		}
		return false;
	}

	public function getPerfiles(){
		$members = null;
		foreach ($this->perfiles as $key => $value) {
			$image = HOST.'/assets/img/business/logo/'._safe($value['logo']);
			if($value['status'] == 1){
				$status = '<span class="label label-sm label-success">Activo</span>';
			}elseif($value['status'] == 0){
				$status = '<span class="label label-sm label-danger">Cancelado</span>';
			}elseif($value['status'] == 2){
				$status = '<span class="label label-sm label-warning">Suspendido</span>';
			}else{
				$status = '';
			}
			$name = _safe($value['name']);
			$email = _safe($value['email']);
			$phone = _safe($value['phone']);
			$category = _safe($value['category']);
			$commission = _safe($value['commission']);
			$url = _safe($value['url']);
			$balance = number_format((float)$value['balance'], 2, '.', '');
			if($balance < 0){
				$balance = '<strong class="required">'.$balance.'</strong>';
			}else{
				$balance = '<strong class="text-primary">'.$balance.'</strong>';
			}
			$date = date('d/m/Y', strtotime($value['created_at']));
			if(!empty($value['city']) && !empty($value['country'])){
				$location = _safe($value['city'].', '.$value['country']);
			}else{
				$location = '';
			}
			$username = _safe($value['username']);
			if($value['u_name'] || $value['last_name']){
				$alias = _safe(trim($value['u_name'].' '.$value['last_name']));
			}else{
				$alias = $username;
			}
			$members .= 
				'<tr>
					<td>'.$key.'</td>
					<td>'.$this->get_dropdown_options($value['status'], $key).'</td>
					<td>'.$balance.'</td>
					<td>
						<div class="user user-md">
							<a href="'.HOST.'/'.$url.'" target="_blank"><img src="'.$image.'"></a>
						</div>
					</td>
					<td>'.$name.'</td>
					<td><a class="label label-xs label-info" href="'.HOST.'/admin/negocios/ventas?negocio='.$url.'" target="_blank">Ver historial</a></td>
					<td>'.$email.'</td>
					<td>'.$phone.'</td>
					<td>'.$category.'</td>
					<td>'.$commission.'%</td>
					<td>'.$location.'</td>
					<td><a href="'.HOST.'/socio/'.$username.'" target="_blank">'.$alias.'</a></td>
					<td>'.$date.'</td>
				</tr>';
		}
		$html = 
			'<div class="table-responsive">
				<table class="table table-hover">
					<thead>
					<tr>
						<th>#</th>
						<th>Situacion</th>
						<th>Saldo</th>
						<th>Logo</th>
						<th>Nombre</th>
						<th></th>
						<th>Correo electr&oacute;nico</th>
						<th>N. Telef&oacute;nico</th>
						<th>Categor&iacute;a</th>
						<th>Comisi&oacute;n</th>
						<th>Origen</th>
						<th>Registrante</th>
						<th>Registrado</th>
					</tr>
					</thead>
					<tbody>
					'.$members.'
					</tbody>
				</table>
			</div>';
		return $html;
	}

	public function get_perfiles_pdf($search = null){
		if($search){
			$search_query = "WHERE n.nombre LIKE :search1 OR n.breve LIKE :search2 OR n.descripcion LIKE :search3";
		}else{
			$search_query = '';
		}
		$query = "SELECT n.id_negocio, n.nombre, nc.categoria, n.comision, n.url, c.ciudad, p.pais, n.saldo, n.situacion, n.creado, l.preferencia as logo, u.username, u.nombre as u_nombre, u.apellido, 
			(SELECT ne.email
			FROM negocio_email ne
			WHERE ne.id_negocio = n.id_negocio
			LIMIT 1) as email,
			(SELECT nt.telefono
			FROM negocio_telefono nt
			WHERE nt.id_negocio = n.id_negocio
			LIMIT 1) as telefono
			FROM negocio n
			INNER JOIN solicitud_negocio s ON n.id_solicitud = s.id_solicitud
			INNER JOIN usuario u ON s.id_usuario = u.id_usuario 
			INNER JOIN negocio_categoria nc ON n.id_categoria = nc.id_categoria
			INNER JOIN ciudad c ON n.id_ciudad = c.id_ciudad
			INNER JOIN estado e ON c.id_estado = e.id_estado
			INNER JOIN pais p ON e.id_pais = p.id_pais
			INNER JOIN preferencia pr
			INNER JOIN negocio_preferencia l ON l.id_negocio = n.id_negocio AND l.id_preferencia = pr.id_preferencia AND pr.llave = 'business_logo'
			$search_query
			ORDER BY n.creado ASC";
		try{
			$stmt = $this->con->prepare($query);
			if($search){
				$stmt->bindValue(':search1', '%'.$search.'%', PDO::PARAM_STR);
				$stmt->bindValue(':search2', '%'.$search.'%', PDO::PARAM_STR);
				$stmt->bindValue(':search3', '%'.$search.'%', PDO::PARAM_STR);
			}
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$rows = null;
		while($row = $stmt->fetch()){
			$id = $row['id_negocio'];
			switch ($row['situacion']) {
				case 0:
					$status_tag = 'Baja';
					break;
				case 1:
					$status_tag = 'Activo';
					break;
				case 2:
					$status_tag = 'Suspendido';
					break;
				case 3:
					$status_tag = 'Cerrado por temporada';
					break;
				default:
					$status_tag = '';
					break;
			}
			$name = _safe($row['nombre']);
			$url = HOST.'/'._safe($row['url']);
			$balance = number_format((float)$row['saldo'], 2, '.', '');
			$email = _safe($row['email']);
			$phone = _safe($row['telefono']);
			$category = _safe($row['categoria']);
			$commission = _safe($row['comision']);
			if(!empty($row['ciudad']) && !empty($row['pais'])){
				$location = _safe($row['ciudad'].', '.$row['pais']);
			}else{
				$location = '';
			}
			if($row['u_nombre'] || $row['apellido']){
				$alias = _safe(trim($row['u_nombre'].' '.$row['apellido']));
			}else{
				$alias = _safe($row['username']);
			}
			$date = date('d/m/Y g:i A', strtotime($row['creado']));
			$rows .= 
				'<tr>
					<td>'.$id.'</td>
					<td>'.$status_tag.'</td>
					<td>'.$balance.'</td>
					<td>'.$name.'</td>
					<td>'.$email.'</td>
					<td>'.$phone.'</td>
					<td>'.$category.'</td>
					<td>'.$commission.'%</td>
					<td>'.$location.'</td>
					<td>'.$alias.'</td>
					<td>'.$date.'</td>
				</tr>';
		}
		$html = 
'<style type="text/css">
	#cabecera{
		background:#f7f8f9;
		padding:10px 20px;
		border-radius: 6px;
	}
	h1,h2{
		float:left;
	}
	table {
		width: 100%;
		border-spacing: 0;
		border-collapse: collapse;
		padding: 8px;
	}
	.table-bordered, th, td{
		border: 1px solid #ddd;
		padding: 5px;
	}

</style>
<page style="font-size: 8px">
	<div id="cabecera">
		<h1>Travel Points</h1>
		<h2>Reporte de negocios</h2>
	</div>
	<table class="table-bordered">
		<thead>
			<tr>
				<th>#</th>
				<th>Sit.</th>
				<th>Saldo</th>
				<th>Nombre</th>
				<th>Correo electr&oacute;nico</th>
				<th>N. Telef&oacute;nico</th>
				<th>Categor&iacute;a</th>
				<th>C.%</th>
				<th>Origen</th>
				<th>Registrante</th>
				<th>Registrado</th>
			</tr>
		</thead>
		<tbody>
		'.$rows.'
		</tbody>
	</table>
</page>';

		require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/vendor/autoload.php';
		require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/vendor/spipu/html2pdf/html2pdf.class.php';
		$html2pdf = new \HTML2PDF('P','A4','es');
		$html2pdf->WriteHTML($html);
		$html2pdf->Output('reporte.pdf');
		return;
	}

	private function getOpciones($status, $solicitud){
		switch ($status) {
			case 0:
				$status_tag = 'Baja';
				$class = 'btn-danger';
				break;
			case 1:
				$status_tag = 'Activo';
				$class = 'btn-success';
				break;
			case 2:
				$status_tag = 'Suspendido';
				$class = 'btn-danger';
				break;
			case 3:
				$status_tag = 'Cerrado por temporada';
				$class = 'btn-warning';
				break;
			default:
				$status_tag = '';
				$class = 'btn-default';
				break;
		}
		$options = null;
		foreach ($this->suspend as $key => $value) {
			if($key == $status){
				continue;
			}
			$options .= 
			'<form method="post" action="'._safe($_SERVER['REQUEST_URI']).'">
				<li><a href="#" class="change-business-status">'.$value.'</a></li>
				<input type="hidden" value="'.$key.'" name="suspend_id">
				<input type="hidden" value="'.$solicitud.'" name="business_id">
			</form>';
		}
		$html = 
			'<div class="role-dropdown">
				<div class="dropdown">
					<button class="btn '.$class.' btn-xs dropdown-toggle mimic-header-nav-user-image" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
						<span>'.$status_tag.'</span> <i class="fa fa-chevron-down"></i>
					</button>
					<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
					'.$options.'
					</ul>
				</div><!-- /.dropdown -->
			</div><!-- /.header-nav-user -->';
		return $html;
	}

	public function change_business_status(array $post){
		if(!array_key_exists($post['business_id'], $this->perfiles) || !array_key_exists($post['suspend_id'], $this->suspend)){
			$this->error['error'] = 'Error al tratar de actualizar el estado de un negocio.';
			return false;
		}else{
			$business_id = (int)$post['business_id'];
			$suspend_id = (int)$post['suspend_id'];
		}
		$query = "UPDATE negocio SET situacion = :situacion WHERE id_negocio = :id_negocio";
		$params = array(
			':situacion' => $suspend_id,
			':id_negocio' => $business_id
		);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($params);
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$query = "SELECT ne.email, s.email as email_solicitud
			FROM negocio_email ne 
			INNER JOIN negocio n ON ne.id_negocio = n.id_negocio
			INNER JOIN solicitud_negocio s ON n.id_solicitud = s.id_solicitud 
			WHERE ne.id_negocio = :id_negocio LIMIT 1";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $business_id, PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$business_email = $row['email'];
			$request_email = $row['email_solicitud'];
		}
		$business_name = $this->perfiles[$business_id]['name'];
		switch ($suspend_id) {
			case 0:
				$title = 'Tu negocio ha sido dado de baja';
				$header = 'Lamentamos informarte que tu negocio "'.$business_name.'" ha sido dado de baja.';
				break;
			case 1:
				$title = 'Tu negocio ha sido reactivado';
				$header = '¡Enhorabuena! Tu negocio "'.$business_name.'" ya está activo.';
				break;
			case 2:
				$title = 'Tu negocio ha sido suspendido';
				$header = 'Lamentamos informarte que tu negocio "'.$business_name.'" ha sido suspendido.';
				break;
			case 3:
				$title = 'Tu negocio ha cerrado por temporada';
				$header = 'Te informarmos que tu negocio "'.$business_name.'" ha sido suspendido temporalmente y etiquetado como cerrado por temporada.';
				break;
			default:
				$title = '';
				$header = '';
				break;
		}
		// SE MANDA LA NOTIFICACION AL USUARIO
		$body_alt = $header.' Para cualquier aclaración contacta a Travel Points. soporte@esmartclub.com';
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
		$mail->setFrom('notificacion@esmartclub.com', 'Travel Points');
		// El correo al que se enviará
		$mail->addAddress($business_email);
		if($business_email != $request_email){
			$mail->AddCC($request_email);
		}
		// Hacerlo formato HTML
		$mail->isHTML(true);
		// Formato del correo
		$mail->Subject = $title;
		$mail->Body    = $this->email_template($header);
		$mail->AltBody = $body_alt;
		// Enviar
		if(!$mail->send()){
			$_SESSION['notification']['info'] = 'El correo de aviso no se pudo enviar debido a una falla en el servidor.';
		}
		$_SESSION['notification']['success'] = 'Estado de negocio actualizado exitosamente.';
		header('Location: '.HOST.'/admin/negocios/');
		die();
		return;
	}

	private function email_template($header){
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
										<strong>'._safe($header).'</strong>
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
							&copy; Travel Points 2017 Todos los derechos reservados.
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
		file_put_contents(ROOT.'\assets\error_logs\business_list.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}
}
?>