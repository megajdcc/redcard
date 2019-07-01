<?php 
namespace assets\libs;
use PDO;

class publicBusinessProfile {
	private $con;
	private $user = array('id' => null, 'bookmark' => false, 'recommend' => false, 'wishlist' => array());
	private $business = array(
		'id' => null,
		'name' => null,
		'description' => null,
		'brief' => null,
		'category_id' => null,
		'category' => null,
		'commission' => null,
		'url' => null,
		'email' => null,
		'phone' => null,
		'website' => null,
		'address' => null,
		'postal_code' => null,
		'city' => null,
		'state' => null,
		'country' => null,
		'latitude' => null,
		'longitude' => null,
		'logo' => null,
		'photo' => null,
		'views' => null,
		'join_date' => null,
		'status' => null,
		'min' => null,
		'max' => null,
		'video' => null,
		'amenity' => array(),
		'iso' => null,
		'featured' => null
	);
	private $categories = array(
		1 => 'restaurant',
		2 => 'bar',
		3 => 'shopping',
		4 => 'health',
		5 => 'leizure',
		6 => 'hotel',
		7 => 'tour',
		8 => 'specialist',
		9 => 'others'
	);
	private $rating = array(
		'total' => null, 
		'average' => array(
			'total' => null, 
			'service' => null, 
			'product' => null, 
			'ambient' => null
		)
	);

	private $reserva = array('forreserva' =>false,'id_negocio' => 0);

	public function __construct(connection $con){
		$this->con = $con->con;
		return;
	}

	public function load_data($url){
		if(!$this->set_url($url)){
			return false;
		}

		$query = "SELECT n.id_negocio, n.nombre, n.descripcion, n.breve, n.id_categoria, nc.categoria, n.comision, n.url, n.sitio_web, n.direccion, n.codigo_postal, c.ciudad, e.estado, p.pais, n.latitud, n.longitud, n.vistas, n.creado, n.situacion FROM negocio n 
			LEFT JOIN ciudad c ON n.id_ciudad = c.id_ciudad 
			LEFT JOIN estado e ON c.id_estado = e.id_estado 
			LEFT JOIN pais p ON e.id_pais = p.id_pais 
			LEFT JOIN negocio_categoria nc ON n.id_categoria = nc.id_categoria 
			WHERE n.url = :url AND situacion != 0";
		
		try{

			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':url', $this->business['url'], PDO::PARAM_STR);
			$stmt->execute();
		
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}


		$sql = "SELECT n.id_negocio from negocio as n where n.id_categoria = 1 and n.url = :url and situacion != 0";
			try {

				$stm = $this->con->prepare($sql);
				$stm->bindParam(':url',$this->business['url'],PDO::PARAM_STR);
				$stm->execute();
			
			} catch (\PDOException $e) {
				$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
				
			}

			if($row = $stm->fetch()){

				if($row['id_negocio'] > 0){
					$this->reserva['id_negocio'] = $row['id_negocio']; 
					$this->reserva['forreserva'] = true;
				}
			}

		if($row = $stmt->fetch()){
			$this->business['id'] = $row['id_negocio'];
			$this->business['name'] = $row['nombre'];
			$this->business['description'] = $row['descripcion'];
			$this->business['brief'] = $row['breve'];
			$this->business['category_id'] = $row['id_categoria'];
			$this->business['category'] = $row['categoria'];
			$this->business['commission'] = $row['comision'];
			$this->business['url'] = $row['url'];
			// $this->business['email'] = $row['email'];
			// $this->business['phone'] = $row['telefono'];
			$this->business['website'] = $row['sitio_web'];
			$this->business['address'] = $row['direccion'];
			$this->business['postal_code'] = $row['codigo_postal'];
			$this->business['city'] = $row['ciudad'];
			$this->business['state'] = $row['estado'];
			$this->business['country'] = $row['pais'];
			$this->business['latitude'] = $row['latitud'];
			$this->business['longitude'] = $row['longitud'];
			// $this->business['logo'] = $row['logo'];
			// $this->business['photo'] = $row['foto'];
			$this->business['views'] = $row['vistas'];
			$this->business['join_date'] = $row['creado'];
			$this->business['status'] = $row['situacion'];
		}else{
			return false;
		}
		$query = "SELECT email FROM negocio_email WHERE id_negocio = :id_negocio LIMIT 1";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['email'] = $row['email'];
		}
		$query = "SELECT telefono FROM negocio_telefono WHERE id_negocio = :id_negocio LIMIT 1";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['phone'] = $row['telefono'];
		}
		$query = "SELECT preferencia FROM negocio_preferencia WHERE id_negocio = :id_negocio AND id_preferencia = 2";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['iso'] = $row['preferencia'];
		}
		$query = "SELECT preferencia FROM negocio_preferencia WHERE id_negocio = :id_negocio AND id_preferencia = 3";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['logo'] = $row['preferencia'];
		}
		$query = "SELECT preferencia FROM negocio_preferencia WHERE id_negocio = :id_negocio AND id_preferencia = 4";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['photo'] = $row['preferencia'];
		}
		$query = "SELECT preferencia FROM negocio_preferencia WHERE id_negocio = :id_negocio AND id_preferencia = 6";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['min'] = $row['preferencia'];
		}
		$query = "SELECT preferencia FROM negocio_preferencia WHERE id_negocio = :id_negocio AND id_preferencia = 7";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['max'] = $row['preferencia'];
		}
		$query = "SELECT preferencia FROM negocio_preferencia WHERE id_negocio = :id_negocio AND id_preferencia = 8";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['video'] = $row['preferencia'];
		}
		$query = "SELECT preferencia FROM negocio_preferencia WHERE id_negocio = :id_negocio AND id_preferencia = 5";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		if($row = $stmt->fetch()){
			$this->business['featured'] = $row['preferencia'];
		}
		$query = "SELECT np.id_preferencia, np.preferencia FROM negocio_preferencia np INNER JOIN preferencia p ON np.id_preferencia = p.id_preferencia WHERE np.id_negocio = :id_negocio AND p.llave = 'amenity'";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$this->business['amenity'][$row['id_preferencia']] = $row['preferencia'];
		}
		$this->set_all_ratings();
		if(isset($_SESSION['user']['id_usuario'])){
			$this->user['id'] = $_SESSION['user']['id_usuario'];
			if(array_key_exists($this->business['id'], $_SESSION['user']['follow_business'])){
				$this->user['bookmark'] = true;
			}
			if(array_key_exists($this->business['id'], $_SESSION['user']['recommend_business'])){
				$this->user['recommend'] = true;
			}
		}
		return true;
	}

	public function increase_views(){
		$query = "UPDATE negocio SET vistas = vistas + 1 WHERE id_negocio = :id_negocio";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
	}

	private function set_url($string = null){
		if($string){
			$string = strtolower(trim($string));
			
			if(!preg_match('/^[a-z0-9-]+$/ui',$string)){
				return false;
			}
			
			$query = "SELECT 1 FROM negocio WHERE url = :url";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':url', $string, PDO::PARAM_STR);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			if($row = $stmt->fetch()){
				$this->business['url'] = $string;
				return true;
			}
		}
		return false;
	}

	private function set_all_ratings(){
		$service = $product = $ambient = 0;
		$query = "SELECT calificacion_servicio, calificacion_producto, calificacion_ambiente FROM opinion o 
			LEFT JOIN negocio_venta nv ON o.id_venta = nv.id_venta 
			LEFT JOIN negocio n ON nv.id_negocio = n.id_negocio
			WHERE n.id_negocio = :id_negocio";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
			$count = $stmt->rowCount();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$service += $row['calificacion_servicio'];
			$product += $row['calificacion_producto'];
			$ambient += $row['calificacion_ambiente'];
		}
		if($count == 0){
			$this->rating['average']['service'] = 0;
			$this->rating['average']['product'] = 0;
			$this->rating['average']['ambient'] = 0;
			$this->rating['average']['total'] = 0;
			$this->rating['total'] = 0;
			return true;
		}
		$this->rating['average']['service'] = $service / $count;
		$this->rating['average']['product'] = $product / $count;
		$this->rating['average']['ambient'] = $ambient / $count;
		$this->rating['average']['total'] = ($service + $product + $ambient) / ($count * 3);
		$this->rating['total'] = $count;
		return true;
	}

	public function get_reviews(){
		$query = "SELECT u.username, u.imagen, u.nombre, u.apellido, o.opinion, o.calificacion_servicio, o.calificacion_producto, o.calificacion_ambiente, o.creado
			FROM opinion o 
			LEFT JOIN negocio_venta nv ON o.id_venta = nv.id_venta
			LEFT JOIN usuario u ON nv.id_usuario = u.id_usuario 
			WHERE nv.id_negocio = :id_negocio
			ORDER BY o.id_opinion DESC LIMIT 3";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
			$count = $stmt->rowCount();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$reviews = null; $key = 0;
		while($row = $stmt->fetch()){
			if(empty($row['imagen'])){
				$image = 'default.jpg';
			}else{
				$image = _safe($row['imagen']);
			}
			if(!empty($row['nombre']) && !empty($row['apellido'])){
				$displayName = _safe($row['nombre'].' '.$row['apellido']);
			}else{
				$displayName = _safe($row['username']);
			}
			$time = strtotime($row['creado']);
			$posted = date('d/m/Y \a \l\a\s g:i A', $time);
			$ago = $this->time_tag($time);
			$review = _safe($row['opinion']);
			$score = ($row['calificacion_servicio'] + $row['calificacion_producto'] + $row['calificacion_ambiente']) / 3;
			if($key == 0){
				$in = 'in';
				$class = '';
				$aria = 'true';
			}else{
				$in = '';
				$class = 'class="collapsed"';
				$aria = 'false';
			}
			$reviews .= 
			'<div class="review">
				<div class="review-image">
					<a href="'.HOST.'/socio/'._safe($row['username']).'">
						<img src="'.HOST.'/assets/img/user_profile/'.$image.'" alt="Foto de perfil de '.$displayName.'">
					</a>
				</div>
				<div class="review-inner"  role="tab" id="heading-'.$key.'">
					<div class="review-title">
						<a '.$class.' role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-'.$key.'" aria-expanded="'.$aria.'" aria-controls="collapse-'.$key.'">
							<h2>'.$displayName.'</h2>
							<span class="report">
								<span class="separator">&#8226;</span> <span title="'.$posted.'">hace '.$ago.'</span>
							</span>
							<div class="review-overall-rating">
								<span class="overall-rating-title">Calificaci&oacute;n total:</span>
								'.$this->get_rating_stars($score).'
							</div><!-- /.review-rating -->
						</a>
					</div><!-- /.review-title -->
					<div id="collapse-'.$key.'" class="panel-collapse collapse '.$in.'" role="tabpanel" aria-labelledby="heading-'.$key.'">
						<div class="review-content-wrapper">
							<div class="review-content">
								<p>'.$review.'</p>
							</div><!-- /.review-content -->
							<div class="review-rating">
								<dl>
									<dt>Servicio</dt>
									<dd>
										'.$this->get_rating_stars($row['calificacion_servicio']).'
									</dd>
									<dt>Producto</dt>
									<dd>
										'.$this->get_rating_stars($row['calificacion_producto']).'
									</dd>
									<dt>Ambiente</dt>
									<dd>
										'.$this->get_rating_stars($row['calificacion_ambiente']).'
									</dd>
								</dl>
							</div><!-- /.review-rating -->
						</div><!-- /.review-content-wrapper -->
					</div>
				</div><!-- /.review-inner -->
			</div><!-- /.review -->';
			$key++;
		}
		if($reviews == null){
			$html = '<div class="background-white p20 mb30">No hay opiniones sobre este negocio.</div>';
		}else{
			$html = 
			'<div class="reviews" id="accordion" role="tablist" aria-multiselectable="true">
				'.$reviews.'
			</div>
			<a href="'.HOST.'/'.$this->business['url'].'/opiniones" class="view-all">Ver todas <i class="fa fa-chevron-right"></i></a>';
		}
		return $html;
	}

	public function get_rating_stars($rating){
		$rating = round($rating*2)/2;
		$string = null;
		for ($i=0; $i < 5; $i++){
			if($i < $rating){
				if($rating == $i+0.5){
					$string .= '<i class="fa fa-star-half-o"></i> ';
				}else{
					$string .= '<i class="fa fa-star"></i> ';
				}
			}else{
				$string .= '<i class="fa fa-star-o"></i>
				';
			}
		}
		return $string;
	}

	public function get_claims(){
		$string = null;

		if($tag = $this->get_status()){
			return '<div class="detail-tag mt30">'.$tag.'</div>';
		}

		if($this->user['id']){
			if($this->user['bookmark']){
				$string .= '<div class="detail-banner-btn bookmark marked" id="bookmark" data-id="'.$this->business['id'].'" data-function="del"><i class="fa fa-bookmark-o"></i> <span data-toggle="Seguir">Siguiendo</span></div><!-- /.detail-claim -->';
			}else{
				$string .= '<div class="detail-banner-btn bookmark" id="bookmark" data-id="'.$this->business['id'].'" data-function="add"><i class="fa fa-bookmark-o"></i> <span data-toggle="Siguiendo">Seguir</span></div><!-- /.detail-claim -->';
			}
			if($this->user['recommend']){
				$string .= '<div class="detail-banner-btn heart marked" id="recommend" data-id="'.$this->business['id'].'" data-function="del">
				<i class="fa fa-heart-o"></i> <span data-toggle="Lo recomiendo">Recomendado</span></div><!-- /.detail-claim -->';
				
			}else{
				$string .= '<div class="detail-banner-btn heart" id="recommend" data-id="'.$this->business['id'].'" data-function="add">
				<i class="fa fa-heart-o"></i> <span data-toggle="Recomendado">Lo recomiendo</span></div><!-- /.detail-claim -->';
			}


		}else{
			$string .= '<a href="'.HOST.'/login" class="detail-banner-btn"><i class="fa fa-bookmark-o"></i>Seguir</a>
			<a href="'.HOST.'/login" class="detail-banner-btn"><i class="fa fa-heart-o"></i>Lo recomiendo</a>';
		}
		return $string;
	}

	public function get_btnreservacion(){

		return $this->reserva['forreserva'];
	
	}

	public function getIdnegocio(){
		return $this->reserva['id_negocio'];
	}

	public function isUser(){
		if(isset($_SESSION['user']['id_usuario']) and $_SESSION['user']['id_usuario'] > 0 ){
			return true;
		}else{
			return 'not';
		}

	}
	public function get_certificates(){
		$string = null;
		$now = date('Y/m/d H:i:s', time());
		$query = "SELECT id_certificado, url, imagen, nombre, descripcion, precio, iso, fecha_inicio, fecha_fin 
			FROM negocio_certificado WHERE id_negocio = :id_negocio AND situacion = 1 AND fecha_inicio < :now1 AND fecha_fin > :now2
			ORDER BY fecha_fin ASC LIMIT 5";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->bindValue(':now1', $now, PDO::PARAM_STR);
			$stmt->bindValue(':now2', $now, PDO::PARAM_STR);
			$stmt->execute();
			$count = $stmt->rowCount();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$start = date('d/m/Y', strtotime($row['fecha_inicio']));
			$end = date('d/m/Y', strtotime($row['fecha_fin']));
			$title = _safe($row['nombre']);
			$description = _safe($row['descripcion']);
			$cert_url = _safe($row['url']);
			$string .= 
			'<div class="post">
				<div class="post-image">
					<a href="'.HOST.'/certificado/'.$cert_url.'" target="_blank">
						<img src="'.HOST.'/assets/img/business/certificate/'.$row['imagen'].'" alt="'.$title.'">
					</a>
				</div><!-- /.post-image -->
				<div class="post-date">Fin '.$end.'</div><!-- /.post-date -->
				<div class="post-content max-250 mr20">
					<h2><a href="'.HOST.'/certificado/'.$cert_url.'" target="_blank">'.$title.'</a></h2>
					<p class="of-hidden-ellipsis">'.$description.'</p>
				</div><!-- /.post-content -->';
			if($this->user['id']){
				if(array_key_exists($row['id_certificado'], $_SESSION['user']['certificate_wishlist'])){
					$string .= 
					'<div class="post-more">
						<div class="wishlist-btn marked" data-id="'.$row['id_certificado'].'" data-function="del">
							<i class="fa fa-heart-o"></i>
							<span data-toggle="Wishlist">Added</span>
						</div>
					</div><!-- /.post-more -->';
				}else{
					$string .= 
					'<div class="post-more">
						<div class="wishlist-btn" data-id="'.$row['id_certificado'].'" data-function="add">
							<i class="fa fa-heart-o"></i>
							<span data-toggle="Added">Wishlist</span>
						</div>
					</div><!-- /.post-more -->';
				}
			}
			$string .= 
			'</div><!-- /.post -->';
		}
		if($string == null){
			$html = '<div class="background-white p20">No hay certificados de regalo disponibles en este momento.</div>';
		}else{
			$html = '<div class="posts posts-condensed">'.$string.'</div>';
		}
		return $html;
	}

	public function get_map(){
		$html = 
		'<div class="detail-map">
			<div class="map-position">
				<div id="listing-detail-map"
					 data-transparent-marker-image="'.HOST.'/assets/img/transparent-marker-image.png"
					 data-styles=\'[{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#444444"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2f2f2"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"poi.government","elementType":"labels.text.fill","stylers":[{"color":"#b43b3b"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"hue":"#ff0000"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},{"featureType":"road","elementType":"geometry.fill","stylers":[{"lightness":"8"},{"color":"#bcbec0"}]},{"featureType":"road","elementType":"labels.text.fill","stylers":[{"color":"#5b5b5b"}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#7cb3c9"},{"visibility":"on"}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"color":"#abb9c0"}]},{"featureType":"water","elementType":"labels.text","stylers":[{"color":"#fff1f1"},{"visibility":"off"}]}]\'
					 data-zoom="15"
					 data-latitude="'.$this->business['latitude'].'"
					 data-longitude="'.$this->business['longitude'].'"
					 data-icon="fa fa-map-marker">
				</div><!-- /#map-property -->
			</div><!-- /.map-property -->
		</div><!-- /.detail-map -->';
		return $html;
	}

	public function add_bookmark($id, $bid){
		$query = "INSERT INTO bookmark (id_usuario, id_negocio) VALUES (:id_usuario, :id_negocio)";
		$query_params = array(':id_usuario' => $id,':id_negocio' => $bid);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($query_params);
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		return true;
	}

	public function del_bookmark($id, $bid){
		$query = "DELETE FROM bookmark WHERE id_usuario = :id_usuario AND id_negocio = :id_negocio";
		$query_params = array(':id_usuario' => $id,':id_negocio' => $bid);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($query_params);
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		return true;
	}

	public function add_recommend($id, $bid){
		$query = "INSERT INTO recommend (id_usuario, id_negocio) VALUES (:id_usuario, :id_negocio)";
		$query_params = array(':id_usuario' => $id,':id_negocio' => $bid);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($query_params);
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		return true;
	}

	public function del_recommend($id, $bid){
		$query = "DELETE FROM recommend WHERE id_usuario = :id_usuario AND id_negocio = :id_negocio";
		$query_params = array(':id_usuario' => $id,':id_negocio' => $bid);
		try{
			$stmt = $this->con->prepare($query);
			$stmt->execute($query_params);
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		return true;
	}

	public function get_recommended(){
		$query = "SELECT COUNT(*) FROM recomendar_negocio WHERE id_negocio = :id_negocio";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$row = $stmt->fetch();
		return $row['COUNT(*)'];
	}

	public function get_name(){
		return _safe($this->business['name']);
	}

	public function get_name_unsafe(){
		return $this->business['name'];
	}

	public function get_description(){
		$range = $this->get_price_range();
		$social = $this->get_social_networks();
		if($range || $social){
			$html = '<div class="detail-description">';
		}else{
			$html = '<div>';
		}
		$html .= 
		'	<p>'._safe($this->business['description']).'</p>
		</div>';
		if($range){
			if($social){
				$html .= '<div class="detail-description detail-follow">';
			}else{
				$html .= '<div class="detail-follow">';
			}
			$html .= 
			'	<h5>Rango de precios</h5>
				<div class="follow-wrapper">'.$range.'</div>
			</div>';
		}
		if($social){
			$html .= $social;
		}
		return $html;
	}

	public function get_brief(){
		return _safe($this->business['brief']);
	}

	public function get_category(){
		return _safe($this->business['category']);
	}

	public function get_commission(){
		return _safe($this->business['commission']);
	}

	public function get_url(){
		return _safe($this->business['url']);
	}

	public function get_email(){
		if($this->business['email']){
			$email = _safe($this->business['email']);
			$html = 
			'<div class="detail-contact-email">
				<i class="fa fa-envelope-o"></i> <a href="mailto:'.$email.'">'.$email.'</a>
			</div>';
			return $html;
		}
		return false;
	}

	public function get_phone(){
		if($this->business['phone']){
			$phone = _safe($this->business['phone']);
			$html = 
			'<div class="detail-contact-phone">
				<i class="fa fa fa-mobile-phone"></i> <a href="tel:'.$phone.'">'.$phone.'</a>
			</div>';
			return $html;
		}
		return false;
	}

	public function get_website(){
		if($this->business['website']){
			$website = _safe($this->business['website']);
			$html = 
			'<div class="detail-contact-website">
				<i class="fa fa-globe"></i> <a href="'.$website.'" target="_blank">'.$website.'</a>
			</div>';
			return $html;
		}
		return false;
	}

	public function get_country(){
		return _safe($this->business['country']);
	}

	public function get_state(){
		return _safe($this->business['state']);
	}

	public function get_address(){
		if($this->business['address']){
			$address = _safe($this->business['address']);
			$html = 
			'<div class="etail-contact-address">
				<i class="fa fa-map-o"></i> '.$address.'
			</div>';
			return $html;
		}
		return false;
	}

	public function get_logo(){
		if($this->business['logo']){
			$logo = _safe($this->business['logo']);
			$html = 
			'<div class="detail-logo">
				<img src="'.HOST.'/assets/img/business/logo/'.$logo.'">
			</div><!-- /.detail-logo -->';
			return $html;
		}
		return false;
	}

	public function get_photo(){
		return _safe($this->business['photo']);
	}

	public function get_gallery(){
		$name = _safe($this->business['name']);
		$query = "SELECT imagen, titulo FROM negocio_imagen WHERE id_negocio = :id_negocio AND situacion = 1";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$images[] = $row;
		}
		$html = null;
		if(!empty($images)){
			$thumbs = null;
			foreach($images as $key => $value){
				$image = _safe($value['imagen']);
				$title = _safe($value['titulo']);
				if($value['imagen'] == $this->business['featured']){
					$featured = 
						'<div class="detail-gallery-preview">
							<a href="'.HOST.'/assets/img/business/gallery/'.$image.'">
								<img src="'.HOST.'/assets/img/business/gallery/'.$image.'" title="'.$title.' '.$name.'" alt="'.$title.' '.$name.'">
							</a>
						</div>';
					$thumbs .= 
						'<li class="detail-gallery-list-item active">
							<a data-target="'.HOST.'/assets/img/business/gallery/'.$image.'">
								<img src="'.HOST.'/assets/img/business/gallery/'.$image.'" title="'.$title.' '.$name.'" alt="'.$title.' '.$name.'">
							</a>
						</li>';
				}else{
					$thumbs .= 
						'<li class="detail-gallery-list-item active">
							<a data-target="'.HOST.'/assets/img/business/gallery/'.$image.'">
								<img src="'.HOST.'/assets/img/business/gallery/'.$image.'" title="'.$title.' '.$name.'" alt="'.$title.' '.$name.'">
							</a>
						</li>';
				}
			}
			$html .= 
				'<div class="detail-gallery mb80">
					'.$featured.'
					<ul class="detail-gallery-index">
					'.$thumbs.'
					</ul>
				</div><!-- /.detail-gallery -->';
		}
		return $html;
	}

	public function get_menu(){
		$html = 
			'<div class="widget">
				<ul class="menu-advanced">
					<li'.$this->set_active_sidebar_tab('perfil_negocio.php').'><a href="'.HOST.'/'.$this->business['url'].'"><i class="fa fa-home"></i> '.$this->get_name().'</a></li>
					<li'.$this->set_active_sidebar_tab('negocio_certificados.php').'><a href="'.HOST.'/'.$this->business['url'].'/certificados"><i class="fa fa-gift"></i> Certificados de regalo</a></li>
					<li'.$this->set_active_sidebar_tab('negocio_publicaciones.php').'><a href="'.HOST.'/'.$this->business['url'].'/publicaciones"><i class="fa fa-flag"></i> Publicaciones</a></li>
					<li'.$this->set_active_sidebar_tab('negocio_eventos.php').'><a href="'.HOST.'/'.$this->business['url'].'/eventos"><i class="fa fa-calendar"></i> Eventos</a></li>
					<li'.$this->set_active_sidebar_tab('negocio_opiniones.php').'><a href="'.HOST.'/'.$this->business['url'].'/opiniones"><i class="fa fa-comments-o"></i> Opiniones</a></li>
				</ul>
			</div>';
		return $html;
	}

	private function set_active_sidebar_tab($tab = null){
		if(basename($_SERVER['SCRIPT_NAME']) == $tab){
			$class = ' class="active"';
		}else{
			$class= '';
		}
		return $class;
	}

	public function get_video(){
		$html = null;
		if($this->business['video']){
			if($this->business['video'] != strip_tags($this->business['video'])){
				// Contiene HTML (iFrame)
				preg_match('/src="([^"]+)"/', $this->business['video'], $match);
				$url = $match[1];
				$html = 
				'<h2>Video</h2>
				<div class="detail-video">
					<iframe src="'.$url.'" width="653" height="367" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
				</div>';
			}else{
				// No contiene HTML (direct link)
				$url = $this->youtube_id_from_url($this->business['video']);
				$html = 
				'<h2>Video</h2>
				<div class="detail-video">
					<iframe src="https://www.youtube.com/embed/'.$url.'" width="653" height="367" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
				</div>';
			}
		}
		return $html;
	}

	public function youtube_id_from_url($url) {
		$pattern = 
			'%^# Match any youtube URL
			(?:https?://)?  # Optional scheme. Either http or https
			(?:www\.)?      # Optional www subdomain
			(?:             # Group host alternatives
			  youtu\.be/    # Either youtu.be,
			| youtube\.com  # or youtube.com
			  (?:           # Group path alternatives
				/embed/     # Either /embed/
			  | /v/         # or /v/
			  | /watch\?v=  # or /watch\?v=
			  )             # End path alternatives.
			)               # End host alternatives.
			([\w-]{10,12})  # Allow 10-12 for 11 char youtube id.
			%x'
			;
		$result = preg_match($pattern, $url, $matches);
		if ($result) {
			return $matches[1];
		}
		return false;
	}

	public function get_amenities(){
		$string = null;
		$query = "SELECT p.id_preferencia, p.preferencia FROM preferencia p WHERE p.llave = 'amenity'";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			if(array_key_exists($row['id_preferencia'], $this->business['amenity'])){
				$string .= '<li class="yes">'.$row['preferencia'].'</li>';
			}else{
				$string .= '<li class="no">'.$row['preferencia'].'</li>';
			}
		}
		return $string;
	}

	public function get_credit_cards(){
		$string = $html = null; $icon = array('VISA' => 'fa-cc-visa', 'Master Card' => 'fa-cc-mastercard', 'American Express' => 'fa-cc-amex', 'Paypal' => 'fa-cc-paypal');
		$query = "SELECT p.preferencia FROM negocio_preferencia ne INNER JOIN preferencia p ON ne.id_preferencia = p.id_preferencia WHERE p.llave = 'payment' AND ne.id_negocio = :id_negocio";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			$string .= '<li><a href="#"><i class="fa '.$icon[$row['preferencia']].'"></i></a></li>';
		}
		if($string){
			$html = 
			'<div class="detail-payments">
				<h3>Pagos Aceptados</h3>
				<ul>
					'.$string.'
				</ul>
			</div>';
		}
		return $html;
	}

	public function get_schedule(){
		$query = "SELECT dia, hora_apertura, hora_cierre FROM negocio_horario WHERE id_negocio = :id_negocio and forreserva != 1";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$schedule = null;
		while($row = $stmt->fetch()){
			switch($row['dia']){
				case 1: $day = 'Lunes'; break;
				case 2: $day = 'Martes'; break;
				case 3: $day = 'Mi&eacute;rcoles'; break;
				case 4: $day = 'Jueves'; break;
				case 5: $day = 'Viernes'; break;
				case 6: $day = 'S&aacute;bado'; break;
				case 7: $day = 'Domingo'; break;
				default: $day = ''; break;
			}
			if(is_null($row['hora_apertura']) && is_null($row['hora_cierre'])){
				$schedule .= 
				'<div class="day clearfix">
					<span class="name">'.$day.'</span><span class="hours">Cerrado</span>
				</div>';
			}else{
				$open = date('h:i A', strtotime($row['hora_apertura']));
				$close = date('h:i A', strtotime($row['hora_cierre']));
				$schedule .= 
				'<div class="day clearfix">
					<span class="name">'.$day.'</span><span class="hours">'.$open.' - '.$close.'</span>
				</div>';
			}
		}
		$html = null;
		if($schedule){
			$html = 
				'<div class="widget">
					<h2 class="widgettitle">Horario de trabajo</h2>
					<div class="p20 background-white">
						<div class="working-hours">
							'.$schedule.'
						</div>
					</div>
				</div>';
		}
		return $html;
	}

	public function get_social_networks(){
		$string = $html = null;
		$query = "SELECT p.preferencia, ne.preferencia as url FROM negocio_preferencia ne INNER JOIN preferencia p ON ne.id_preferencia = p.id_preferencia WHERE p.llave = 'network' AND ne.id_negocio = :id_negocio";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_negocio', $this->business['id'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		while($row = $stmt->fetch()){
			if($row['preferencia'] == 'Google+'){
				$class = 'google-plus';
			}else{
				$class = strtolower($row['preferencia']);
			}
			$string .= '<a class="follow-btn '.$class.'" href="'._safe($row['url']).'" target="_blank"><i class="fa fa-'.$class.'"></i></a>';
		}
		if($string){
			$html = 
			'<div class="detail-follow">
				<h5>Seguir</h5>
				<div class="follow-wrapper">
				'.$string.'
				</div>
			</div>';
		}
		return $html;
	}

	public function get_city(){
		return _safe($this->business['city']);
	}

	public function get_views(){
		return _safe($this->business['views']);
	}

	public function get_join_date(){
		return _safe($this->business['join-date']);
	}

	public function get_category_tag(){
		return $this->categories[$this->business['category_id']];
	}

	public function get_status(){
		switch ($this->business['status']) {
			case 2:
				$tag = 'Negocio suspendido';
				break;
			case 3:
				$tag = 'Cerrado por temporada';
				break;
			default:
				$tag = '';
				break;
		}
		return $tag;
	}

	public function get_location(){
		return _safe($this->business['address']).'<br>'._safe($this->business['city'].', '.$this->business['state'].', '.$this->business['country']);
	}

	public function get_total_ratings(){
		return $this->rating['total'];
	}

	public function get_average_total_ratings(){
		return round($this->rating['average']['total'],2);
	}

	public function get_rating_header(){
		if($this->rating['average']['total'] == 0){
			return 'No hay opiniones sobre este negocio';
		}else{
			return '<strong>'.$this->get_average_total_ratings().' / 5 </strong>de <a href="#reviews">'.$this->get_total_ratings().' opiniones</a>';
		}
	}

	public function get_average_service_ratings(){
		return $this->rating['average']['service'];
	}

	public function get_average_product_ratings(){
		return $this->rating['average']['product'];
	}

	public function get_average_ambient_ratings(){
		return $this->rating['average']['ambient'];
	}

	public function get_price_range(){
		$html = null;
		if($this->business['iso'] == 'EUR'){
			$sign = '€';
		}else{
			$sign = '$';
		}
		if($this->business['min']){
			$html .= 'Min: '.$sign._safe($this->business['min']);
		}
		if($this->business['min'] && $this->business['max']){
			$html .= ' - ';
		}
		if($this->business['max']){
			$html .= 'Max: '.$sign._safe($this->business['max']);
		}
		if($this->business['min'] || $this->business['max']){
			$html .= ' | '.$this->business['iso'];
		}
		return $html;
	}

	private function time_tag($time){
		$time = time() - $time; // to get the time since that moment
		$time = ($time<1)? 1 : $time;
		$tokens = array (
			31536000 => 'año',
			2592000 => 'mes',
			604800 => 'semana',
			86400 => 'día',
			3600 => 'hora',
			60 => 'minuto',
			1 => 'segundo'
		);
		foreach ($tokens as $unit => $text) {
			if ($time < $unit) continue;
			$numberOfUnits = floor($time / $unit);
			return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
		}
	}

	private function catch_errors($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\public_business_errors.txt', '['.date('d/M/Y h:i:s A').' on '.$method.' on line '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		return;
	}
}
?>