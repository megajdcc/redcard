<?php
namespace admin\libs;
use assets\libs\connection;
use PDO;

class DetallesSolicitud {
	private $con;

	private $DetallesSolicitudFranquiciatario;
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
		'terminos-y-condiciones');
	private $user = array('id' => null);
	private $request = array();

	private $nrosolicitud;
	private $solicitudhotel = array();
	private $solicitudFranquiciatario = array();
	private $solicitudReferidor = array();

	private $images = array(
		'logo' => array('tmp' => null, 'name' => null, 'path' => null),
		'photo' => array('tmp' => null, 'name' => null, 'path' => null)
	);
	private $iatas = array();
	private $error = array(
		'name'        => null,
		'description' => null,
		'brief'       => null,
		'category'    => null,
		'commission'  => null,
		'url'         => null,
		'email'       => null,
		'phone'       => null,
		'website'     => null,
		'address'     => null,
		'postal_code' => null,
		'city'        => null,
		'state'       => null,
		'country'     => null,
		'location'    => null,
		'logo'        => null,
		'photo'       => null,
		'comment'     => null,
		'warning'     => null,
		'error'       => null
	);

	public function __construct(connection $con){
		$this->con = $con->con;
		$this->user['id'] = $_SESSION['user']['id_usuario'];
		return;
	}



	public function cargarDatosActualizacion(array $datos,int $solicitud){

			$pago = false;

			if($datos['pago']){
				//cargamos datos de pago de comision

				$pago = true;
				$banco         = $datos['nombre_banco'];
				$bancotarjeta  = $datos['nombre_banco_tarjeta'];
				$clabe         = $datos['clabe'];
				$swift         = $datos['swift'];
				$numerotarjeta = $datos['numero_targeta'];
				$cuenta        = $datos['cuenta'];
				$emailpaypal   = $datos['email_paypal'];

			}
			// cargamos datos de hotel
			$nombrehotel          = $datos['nombre'];
			$iata                 = $datos['iata'];
			$sitioweb             = $datos['website'];
			$direccion            = $datos['direccion'];
			$codigopostal         = $datos['codigopostal'];
			
			$pais                 = $datos['pais'];
			$estado               = $datos['estado'];
			$ciudad               = $datos['ciudad'];
			
			$latitud              = $datos['latitud'];
			$longitud             = $datos['longitud'];
			
			$nombre_responsable   = $datos['nombre_responsable'];
			$apellido_responsable = $datos['apellido_responsable'];
			$email                = $datos['email'];
			$cargo                = $datos['cargo'];
			
			$telefonofijo         = $datos['telefonofijo'];
			$movil                = $datos['movil'];

			if($this->con->inTransaction()){
				$this->con->rollBack();
			}

			$this->con->beginTransaction();

			$idresponsable = 0;
			$idhotel1 = 0;
			$iddatos = 0;

			if($pago){

				$sql = "select h.id as idhotel, h.id_datospagocomision as idpago,h.id_responsable_promocion as responsable from hotel as h join solicitudhotel as sh on h.id=sh.id_hotel where sh.id = :solicitud";


				try {
					$stm1 = $this->con->prepare($sql);
					

					$stm1->bindParam(':solicitud',$solicitud);
					$stm1->execute();
					$fila = $stm1->fetch(PDO::FETCH_ASSOC);
					$idhotel1      = $fila['idhotel'];
					
					$idresponsable = $fila['responsable'];

					$idpago        = $fila['idpago'];
					
				} catch (PDOException $e) {
					$this->error_log(__METHOD__,__LINE__,$e->getMessage());
						$this->con->rollBack();
						return false;
				}
				


				if($idpago != null){
				

					$sql = "update datospagocomision set banco=:banco,cuenta=:cuenta,clabe=:clabe,swift=:swift,banco_tarjeta=:bancotarjeta,numero_tarjeta=:numerotarjeta,email_paypal=:email where id=:id";

					$datos1 = array(':banco'=>$banco,
									':cuenta'       =>$cuenta,
									':clabe'        =>$clabe,
									':swift'        =>$swift,
									':bancotarjeta' =>$bancotarjeta,
									':numerotarjeta' =>$numerotarjeta,
									':email'        =>$emailpaypal,
									':id'           =>$idpago
									);
					try {
						$stm = $this->con->prepare($sql);				
						$stm->execute($datos1);

						
					} catch (PDOException $e) {
						
						$this->error_log(__METHOD__,__LINE__,$e->getMessage());
						$this->con->rollBack();
						return false;
					}
					
				}else{
					$sql = "INSERT INTO datospagocomision(banco,cuenta,clabe,swift,banco_tarjeta,numero_tarjeta,email_paypal)values(':banco',':cuenta',':clabe',':swift',':bancotarjeta',':numerotarjeta',':emailpaypal')";


					settype($clabe,'integer');
					settype($swift,'integer');
					settype($numerotarjeta,'integer');
					
					// echo var_dump($datos2);



					try {
							$stm = $this->con->prepare($sql);

							$stm->bindParam(':banco',$banco, PDO::PARAM_STR);
							$stm->bindParam(':cuenta',$cuenta, PDO::PARAM_STR);
							$stm->bindParam(':clabe',$clabe, PDO::PARAM_INT);
							$stm->bindParam(':swift',$swift, PDO::PARAM_INT);
							$stm->bindParam(':banco_tarjeta',$bancotarjeta, PDO::PARAM_STR);
							$stm->bindParam(':numero_tarjeta',$numerotarjeta, PDO::PARAM_INT);
							$stm->bindParam(':email_paypal',$emailpaypal, PDO::PARAM_STR);
				
						$result = $stm->execute();
						
							$iddatos = $this->con->lastInsertId();


					} catch (PDOException $e) {
							$this->error_log(__METHOD__,__LINE__,$e->getMessage());
							$this->con->rollBack();
							return false;
					}


				}
			
				if($iddatos == 0){
								
					$queryhotel = "update hotel set nombre=:nombre,direccion=:direccion,latitud=:latitud,longitud=:longitud,sitio_web=:sitioweb,id_ciudad=:ciudad,id_estado=:estado,codigo_postal=:codigopostal,id_iata=:iata where id = :idhotel";
									
									$datoshotel = array(
									':nombre'=>$nombrehotel,':direccion'=>$direccion,':latitud'=>$latitud,':longitud'=>$longitud,':sitioweb'=>$sitioweb,':ciudad'=>$ciudad,':estado'=>$estado,
									':codigopostal'=>$codigopostal,':iata'=>$iata,':idhotel'=>$idhotel1
									);

				}else{

					$queryhotel = "update hotel set nombre=:nombre,direccion=:direccion,latitud=:latitud,longitud=:longitud,sitio_web=:sitioweb,id_ciudad=:ciudad,id_estado=:estado,codigo_postal=:codigopostal,id_iata=:iata, id_datospagocomision=:pago where id = :idhotel";
									
									$datoshotel = array(
									':nombre'=>$nombrehotel,':direccion'=>$direccion,':latitud'=>$latitud,':longitud'=>$longitud,':sitioweb'=>$sitioweb,':ciudad'=>$ciudad,':estado'=>$estado,
									':codigopostal'=>$codigopostal,':iata'=>$iata,':pago'=>$iddatos,':idhotel'=>$idhotel1
									);
					

				}

				
				

			}else{

				$sql = "select h.id as idhotel,h.id_responsable_promocion as responsable from hotel as h join solicitudhotel as sh on h.id=sh.id_hotel where sh.id = :solicitud";

				$stm = $this->con->preapre($sql);

				$stm->bindParam(':solicitud',$solicitud);
				$stm->execute();
				$fila = $stm->fetch(PDO::FETCH_ASSOC);
				$idresponsable =	$fila['responsable'];
				$idhotel       = $fila['idhotel'];

				$queryhotel = "update hotel set nombre=:nombre,direccion=:direccion,latitud=:latitud,longitud=:longitud,sitio_web=:sitioweb,id_ciudad=:ciudad,id_estado=:estado,codigo_postal=:codigopostal,id_iata=:iata where id = :idhotel";

								$datoshotel = array(
									':nombre'=>$nombrehotel,':direccion'=>$direccion,':latitud'=>$latitud,':longitud'=>$longitud,':sitioweb'=>$sitioweb,':ciudad'=>$ciudad,':estado'=>$estado,
									':codigopostal'=>$codigopostal,':iata'=>$iata,':idhotel'=>$idhotel
									);


			}


			if($idresponsable > 0 || $idresponsable !=null){
				$sql2 = "select p.id as personaid from persona as p join responsableareapromocion as r on p.id = r.dni_persona where r.id = :responsable";


				try {
					$stm = $this->con->prepare($sql2);
					$stm->bindParam(':responsable',$idresponsable);
					$stm->execute();
				} catch (PDOException $e) {
					$this->error_log(__METHOD__,__LINE__,$e->getMessage());
					$this->con->rollBack();
					return false;
				}
				


				$fila = $stm->fetch(PDO::FETCH_ASSOC);
				$persona = $fila['personaid'];

				$sql4 = "update persona set nombre=:nombre,apellido=:apellido where id=:persona";


				try {
					$stm= $this->con->prepare($sql4);
					$datos = array(':nombre'=>$nombre_responsable,':apellido'=>$apellido_responsable,':persona'=>$persona);
					$stm->execute($datos);
				} catch (PDOException $e) {

					$this->error_log(__METHOD__,__LINE__,$e->getMessage());
					$this->con->rollBack();
					return false;
					
				}
				

				$sql6 = "update responsableareapromocion set cargo=:cargo,email=:email,telefono_fijo=:telefonofijo,telefono_movil=:movil where id=:responsable";

				try {
					$stm = $this->con->prepare($sql6);
					
					$datos8 = array(':cargo'=>$cargo,':email'=>$email,':telefonofijo' =>$telefonofijo,':movil'=>$movil,':responsable'=>$idresponsable);
					$stm->execute($datos8);
				} catch (PDOException $e) {
						$this->error_log(__METHOD__,__LINE__,$e->getMessage());
						$this->con->rollBack();
						return false;
				}
			}


			try {
				
				$stm = $this->con->prepare($queryhotel);


				$stm->execute($datoshotel);

				$this->con->commit();
				return true;
			} catch (PDOException $e) {

				$this->error_log(__METHOD__,__LINE__,$e->getMessage());
				$this->con->rollBack();
				return false;
				
			}
			

	}

	public function cargarDatosActualizacionReferidor(array $datos,int $solicitud){


			$this->DetallesSolicitudReferidor = new DetallesSolicitudReferidor($this->con,$solicitud);


			return $this->DetallesSolicitudReferidor->CargarDatosActualizacion($datos,$solicitud);

	}


	public function cargarDatosActualizacionFranquiciatario(array $datos,int $solicitud){


			$this->DetallesSolicitudFranquiciatario = new DetallesSolicitudFranquiciatario($this->con,$solicitud);


			return $this->DetallesSolicitudFranquiciatario->CargarDatosActualizacion($datos,$solicitud);

	}


	public function CargarFranquiciatarioAdmin($id){
		$this->DetallesSolicitudFranquiciatario =  new DetallesSolicitudFranquiciatario($this->con, $id);
		$this->DetallesSolicitudFranquiciatario->cargarDatosAdmin();
		return true;
	}

	public function CargarReferidorAdmin($id){
		$this->DetallesSolicitudReferidor =  new DetallesSolicitudReferidor($this->con, $id);
		$this->DetallesSolicitudReferidor->cargarDatosAdmin();
		return true;
	}
	public function CargarHotel($id,$proviene = null){

		$this->setSolicitud($id);

		$query = "select h.codigo,h.id as id_hotel, h.comision, sh.id, u.email as emailusuario, u.username, u.nombre as usuario_nombre,u.apellido as usuario_apellido, h.nombre as hotel, h.id_iata,i.codigo as iata, h.sitio_web,h.direccion, h.codigo_postal, h.id_ciudad,c.ciudad,est.id_estado,est.estado,
			pa.id_pais, pa.pais,h.longitud,h.latitud,per.id as id_persona,  per.nombre as nombre_responsable, per.apellido as apellido_responsable,rap.id as id_responsableareapromocion, rap.email,rap.cargo,
			rap.telefono_fijo, rap.telefono_movil,dpc.id as id_datospagocomision, dpc.banco, dpc.cuenta,dpc.clabe, dpc.swift,
			dpc.banco_tarjeta, dpc.numero_tarjeta, dpc.email_paypal,sh.condicion,sh.creado,sh.comentario 
			from hotel as h
			join iata as i on h.id_iata = i.id
			join ciudad as c on h.id_ciudad = c.id_ciudad
			join estado as est on c.id_estado = est.id_estado
			join pais as pa on est.id_pais = pa.id_pais
			join responsableareapromocion as rap on h.id_responsable_promocion = rap.id
			join persona as per on rap.dni_persona = per.id
			left join datospagocomision as dpc on h.id_datospagocomision = dpc.id
			join solicitudhotel as sh on h.id = sh.id_hotel 
			join usuario as u on sh.id_usuario = u.id_usuario
			where sh.id = :nrosolicitud";

		try {
				$stm = $this->con->prepare($query);
				$stm->bindValue(':nrosolicitud', $id, PDO::PARAM_INT);
				$stm->execute();


		} catch (PDOException $e) {
			$this->error_log(__METHOD__,__LINE__,$e->getMessage());
			return false;
		}

			$this->solicitudhotel = $stm->fetch(PDO::FETCH_ASSOC);



				$query = "select * from iata ";
				
				$stm =$this->con->prepare($query);
				
				$stm->execute();
				
				while($row = $stm->fetch()){
					$this->iatas[$row['id']] = $row['codigo'];
				}

			

			return true;
	}


	public function getDatos($perfil = null){
		if($perfil == 'Hotel'){
			return $this->solicitudhotel;
		}else if($perfil == 'Franquiciatario'){
			return $this->DetallesSolicitudFranquiciatario->getDatos();
		}else if($perfil == 'Referidor'){
			return $this->DetallesSolicitudReferidor->getDatos();
		}
		
	}
	public function CargarFranquiciatario($id,$proviene =null){

		$this->DetallesSolicitudFranquiciatario =  new DetallesSolicitudFranquiciatario($this->con, $id);
		return true;

	}


	private function CargarReferidor($id){

		$this->DetallesSolicitudReferidor =  new DetallesSolicitudReferidor($this->con, $id);
		return true;

	}


	private function setSolicitud(int $id){

		$this->nrosolicitud = $id;

	}



	public function getModal($perfil = null){

		switch ($perfil) {
			case 'Hotel':?>
				<div class="modal fade aceptar modales" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
					<div class="modal-dialog" role="document">
					<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel"><label class="cert-date mr20">Solicitud # <?php echo $this->getId();?> <label class="cert-date form"><?php echo $this->getFecha();?></label></label></h5>
						<h5 class="modal-title"><label class="cert-date form">Hotel <?php echo $this->getNombreHotel(); ?></label></h5>
						<small class="iata cert-date">Codigo Iata <?php echo $this->getIata(); ?></small>
						<button type="button" class="close" >
						<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="alert alert-success alert-aceptada" role="alert">
								La Solicitud ha sido aceptada correctamente, Si gusta genere y asigne la comisión...
						</div>
							<form  action="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" method="post" accept-charset="utf-8">
								<section class="col-xs-12 acept-solicitud container" >
									<div class="row">
										<div class="codigohotel col-lg-5" data-toggle="tooltip" title="Cree o genere el Codigo de hotel, puedes asociar las siglas del Codigo iata, mas las Siglas del hotel o como desees...">
											<div class="form-group">
												<label for="codigohotel" >Codigo de Hotel * <i class="fa fa-question-circle"></i></label>
												<div class="codigo">
													<input type="text" name="codigohotel" class="form-control" id="codigohotel" placeholder="Ejemp AGUHCN" required>
													<button type="button" name="generarcodigo" data-iata="<?php echo _safe($this->solicitudhotel['iata']); ?>" data-hotel="<?php echo $this->getNombreHotel(); ?>" class="btn btn-outline-secondary generarcodigo">Generar</button>
												</div>
											</div>
										</div>

										<div class="comision col-lg-7">
											<div class="form-group">
												<label for="comision">Comisión a adjudicar.</label>
												
												<input id="ex8" type="text" id="comision" data-slider-id="ex1Slider" data-slider-min="0" data-slider-max="40" data-slider-step="1" data-slider-value="<?php echo $this->solicitudhotel['comision'];?>">
												<span class="form" id="val-slider"><?php echo $this->getComision();?></span>
											</div>
										</div>
									</div>
								</section>
							</form>		
						</div>
							<div class="modal-footer">
						
								<button  style="margin-left: auto;" data-perfil="Hotel" type="button" data-path="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" name="adjudicar" class="adjudicar btn btn-success">Registrar</button>
								<button  type="button" class="cerrar btn btn-secondary">Cerrar</button>
						
							</div>
						</div>
					</div>
				</div>
			<?php 
				break;
			case 'Franquiciatario':
				$this->DetallesSolicitudFranquiciatario->getmodal();
			break;
				case 'Referidor':
				$this->DetallesSolicitudReferidor->getmodal();
			break;
			default:
				# code...
				break;
		}
		
		
	 } 

	
	public function Mostrar($perfil){
		switch ($perfil) {
			case 'Hotel':
					if($this->getCondicion() != 1){?>

					<form id="formulariosolicitud" method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" enctype="multipart/form-data" >
			<div class="background-white p30 mb30">
						<?php echo $this->getHeader(); ?>
					</div>
						<div class="background-white p30 mb50">
									<h3 class="page-title">Información de la solicitud hotel</h3>
									<div class="row">

										<div class="col-lg-8">
								
											<div class="form-group" data-toggle="tooltip" title="Los clientes Huespedes de Travel Points pueden afiliarse desde su propio perfil...">
												<label for="business-name">Nombre del hotel <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>

												<input class="form-control" type="text" id="business-name" name="nombrehotel" value="<?php echo $this->getNombreHotel();?>" placeholder="Nombre del hotel" required/>
												
											</div><!-- /.form-group -->
										
										</div><!-- /.col-* -->
										
										<div class="col-lg-4">
											<div class="row">
												<div class="col-sm-6 col-md-12 form-group" data-toggle="tooltip" title="El codigo Iata es utilizado para ayudar a agilizar los procesos de transporte aereo y turistico.">
													<label for="category">C&oacute;digo IATA <span class="required">*</span><i class="fa fa-question-circle text-secondary"></i></label>
													<select class="form-control" id="category" name="id_iata" title="Seleccionar c&oacute;digo IATA" required>
														<?php echo $this->getIatas();?>				
													</select>
												
												</div><!-- /.form-group -->
											</div>
										</div>
											<div class="col-sm-12">
											<div class="form-group" data-toggle="tooltip" title="Si no tienes sitio web, deja el espacio en blanco.">
												<label for="website">Sitio web del hotel <i class="fa fa-question-circle text-secondary"></i></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-globe"></i></span>
													<input class="form-control" pattern="([--:\w?@%&+~#=]*\.[a-z]{2,4}\/{0,2})((?:[?&](?:\w+)=(?:\w+))+|[--:\w?@%&+~#=]+)?" type="text" id="sitio_web" name="sitio_web" value="<?php echo $this->getSitioWeb();?>" placeholder="Sitio web del hotel">
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
													<input class="form-control" type="text" id="address" name="direccion" value="<?php echo $this->getDireccion();?>" placeholder="Direcci&oacute;n del hotel" required>
												</div><!-- /.input-group -->
											
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="postal-code">C&oacute;digo postal  del hotel <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
													<input class="form-control" type="text" id="postal-code" name="codigopostal" value="<?php echo $this->getCodigoPostal()?>" placeholder="C&oacute;digo postal del hotel" required >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div><!-- /.row -->
									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<label for="country-select">Pa&iacute;s <span class="required">*</span></label>
												<select class="form-control" id="country-select" name="id_pais" title="Selecciona un pa&iacute;s" data-size="10" data-live-search="true" required>
													<?php echo $this->getPaises(); ?>
												</select>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="state-select">Estado <span class="required">*</span></label>
												<select class="form-control" id="state-select" name="id_estado" title="Luego un estado" data-size="10" data-live-search="true" required>
												<?php echo $this->getEstados(); ?>
												</select>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="city-select">Ciudad <span class="required">*</span></label>
												<select class="form-control" id="city-select" name="id_ciudad" title="Luego una ciudad" data-size="10" data-live-search="true" required>
												<?php echo $this->getCiudades(); ?>
												</select>
											
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div><!-- /.row -->
									<hr>
									
											<div class="form-group">
											<label>Posici&oacute;n en el mapa <span class="required">*</span></label>
											<div class="detail-content">
											<div class="detail-map">
											<div class="map-position">
											<div id="listing-detail-map"
											data-transparent-marker-image="<?php echo HOST.'/assets/img/transparent-marker-image.png';?>"
											
											data-zoom="15"
											data-latitude="<?php echo $this->getLatitud(); ?>"
											data-longitude="<?php echo $this->getLongitud(); ?>"
											data-icon="fa fa-map-marker">
											</div><!-- /#map-property -->
											</div><!-- /.map-property -->
											</div><!-- /.detail-map -->
											</div>
											</div>

									<div class="row">
										<div class="col-sm-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
												<input class="form-control" type="text" id="input-latitude" name="latitud" pattern="[0-9]+" value="<?php echo $this->getLatitud(); ?>" placeholder="Latitud" required>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-sm-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
												<input class="form-control" type="text"  id="input-longitude" name="longitud" pattern="[0-9]+" value="<?php echo $this->getLongitud(); ?>" placeholder="Longitud" required>
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
													<input class="form-control" type="text"   id="nombre_responsable" name="nombre_responsable" value="<?php echo $this->getNombreResponsable(); ?>" placeholder="Nombre del responsable &aacute;rea de promoci&oacute;n" required >
												</div><!-- /.input-group -->
											
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->

										<div class="col-lg-6">
											<div class="form-group">
												<label for="apellido">Apellido<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-address-card-o"></i></span>
													<input class="form-control" type="text" id="apellido_responsable"  name="apellido_responsable" value="<?php echo $this->getApellidoResponsable(); ?>" placeholder="Apellido del responsable &aacute;rea de promoci&oacute;n" required >
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
													<input class="form-control" type="email" id="email" name="email" value="<?php echo $this->getEmail(); ?>" placeholder="Email del responsable" required>
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										

										<div class="col-lg-6">
											<div class="form-group">
												<label for="cargo">Cargo<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-black-tie"></i></span>
													<input class="form-control" type="text" id="cargo" name="cargo" value="<?php echo $this->getCargo(); ?>" placeholder="Cargo" required >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->

											

										</div><!-- /.col-* -->
										<div class="col-lg-6">
										<div class="form-group"  data-toggle="tooltip" title="El número de teléfono fijo ejemp:+584128505504, 14128505504">
												<label for="phone">T&eacute;lefono fijo <span class="required"></span><i class="fa fa-question-circle"></i></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-phone-square"></i></span>
													<input class="form-control" type="text" pattern="[+][0-9]{12,15}[+]?"  id="phone" name="telefonofijo" value="<?php echo $this->getTelefonoFijo(); ?>" placeholder="N&uacute;mero de t&eacute;lefono fijo">
												</div><!-- /.input-group -->
									
											</div><!-- /.form-group -->
										</div>
										<div class="col-lg-6">
										<div class="form-group" data-toggle="tooltip" title="El número de teléfono movil ejemp: +584128505504, 14128505504">
												<label for="phone">T&eacute;lefono novil <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-mobile-phone"></i></span>
													<input class="form-control" type="text" id="movil" pattern="[+][0-9]{11,15}[+]?" name="telefonomovil" value="<?php echo $this->getTelefonoMovil(); ?>" placeholder="N&uacute;mero de t&eacute;lefono movil" required >
												</div><!-- /.input-group -->
											
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
												<label for="nombre">Nombre del banco<span class="required"></span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-bank"></i></span>
													<input class="form-control" type="text" pattern="[a-zA-z]+" id="nombre_banco" name="nombre_banco" value="<?php echo $this->getBanco(); ?>" placeholder="Nombre del banco"  >
												</div><!-- /.input-group -->
											
											</div><!-- /.form-group -->

											<div class="form-group">
												<label for="cuenta">Cuenta<span class="required"></span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
													<input class="form-control" type="text" id="cuenta" pattern="[0-9a-zA-z]+" name="cuenta" value="<?php echo $this->getCuenta(); ?>" placeholder="Cuenta."  >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->

											<div class="form-group" data-toggle="tooltip" title="Solo se permiten digitos númericos, correspondientes a su clabe.">
												<label for="clabe">Clabe<span class="required"></span><i class="fa fa-question-circle text-secondary"></i></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
													<input class="form-control" type="text" id="clabe" maxlength="18" pattern="[0-9]{18}" name="clabe" value="<?php echo $this->getClabe(); ?>" placeholder="Clabe"  >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->

											<div class="form-group" data-toggle="tooltip" title="Una serie alfanuméricas de 8 u 11 digitos, que sirve para identificar al banco receptor cuando se realiza una transferencia">
												<label for="swift">Swift / Bic<span class="required"></span><i class="fa fa-question-circle text-secondary"></i></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
													<input class="form-control" type="text" id="swift" name="swift" minlength="8" maxlength="11" pattern="[0-9]{11}" value="<?php echo $this->getSwift(); ?>" placeholder="Swift" >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->

										</div><!-- /.col-* -->



										<div class="col-lg-6 col-sm-4">
											<h5 class="page-title">Deposito a tarjeta</h5>
											<div class="form-group">
												<label for="nombre">Nombre del banco<span class="required"></span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-bank"></i></span>
													<input class="form-control" type="text" id="nombre_banco_targeta" pattern="[a-zA-z]+" name="banco_tarjeta" value="<?php echo $this->getNombreBancoTarjeta(); ?>" placeholder="Nombre del banco" >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->
											<div class="form-group" data-toggle="tooltip" title="Número de la targeta de Credito, conlleva 16 digitos solo numéricos.">
												<label for="nombre">N&uacute;mero de tarjeta<span class="required"></span><i class="fa fa-question-circle text-secondary"></i></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-cc"></i></span>
													<input class="form-control" type="text" id="numero_tarjeta" pattern="[0-9]{16}" name="numero_tarjeta" value="<?php echo $this->getNumeroTarjeta(); ?>" placeholder="N&uacute;mero de Tarjeta" >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->
								
										
												<h5 class="page-title">Transferencia PayPal</h5>
											<div class="form-group">
												<label for="nombre">Email de Paypal<span class="required"></span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-cc-paypal"></i></span>
													<input class="form-control" type="email" id="email_paypal" name="email_paypal" value="<?php echo $this->getEmailPaypal(); ?>" placeholder="Email de paypal" >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->
										</div>
									</div>
								</div>
								<div class="background-white p30 mb30">
						<div class="form-group" data-toggle="tooltip" title="Escriba un comentario descriptivo del porqu&eacute; se acepta, se regresa a correcci&oacute;n o se rechaza la solicitud.">
							<label for="comment">Comentario de Travel Points para el solicitante <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
							<textarea class="form-control" id="comment" name="comentario" rows="3" placeholder="Comentario de Travel Points para el solicitante" required></textarea>
						</div><!-- /.form-group -->
					</div><!-- /.box -->
					<input type="hidden" name="id" value="<?php echo $this->getId(); ?>">
					 <input type="hidden" name="perfil" value="hotel">
					<div class="center">
						<button class="btn btn-success mr20" id="aceptarsolicitud" type="submit" name="accept_request">Aceptar solicitud</button>
						<!-- <button class="btn btn-warning mr20" id="corregirsolicitud" type="submit" name="check_request">Regresar a correcci&oacute;n</button> -->
						<button class="btn btn-danger" id="rechazarsolicitud" type="submit" name="reject_request">Rechazar solicitud</button>
					</div>
				</form>
		<?php  }else{?>
			<form method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" enctype="multipart/form-data">
			<div class="background-white p30 mb30">
						<?php echo $this->getHeader(); ?>
					</div>
						<div class="background-white p30 mb50">
									<h3 class="page-title">Información de la solicitud hotel</h3>
									<div class="row">

										<div class="col-lg-8">
								
											<div class="form-group" data-toggle="tooltip" title="Los clientes Huespedes de Travel Points pueden afiliarse desde su propio perfil...">
												<label for="business-name">Nombre del hotel <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>

												<input class="form-control" type="text" id="business-name" name="nombre" value="<?php echo $this->getNombreHotel();?>" placeholder="Nombre del hotel" readonly/>
												
											</div><!-- /.form-group -->
										
										</div><!-- /.col-* -->
										
										<div class="col-lg-4">
											<div class="row">
												<div class="col-sm-6 col-md-12 form-group" data-toggle="tooltip" title="El codigo Iata es utilizado para ayudar a agilizar los procesos de transporte aereo y turistico.">
													<label for="category">C&oacute;digo IATA <span class="required">*</span><i class="fa fa-question-circle text-secondary"></i></label>
													<select class="form-control" id="category" name="iata" title="Seleccionar c&oacute;digo IATA" readonly>
														<?php echo $this->getIata();?>				
													</select>
												
												</div><!-- /.form-group -->
											</div>
										</div>
											<div class="col-sm-12">
											<div class="form-group" data-toggle="tooltip" title="Si no tienes sitio web, deja el espacio en blanco.">
												<label for="website">Sitio web del hotel <i class="fa fa-question-circle text-secondary"></i></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-globe"></i></span>
													<input class="form-control" type="text" id="website" name="website" value="<?php echo $this->getSitioWeb();?>" placeholder="Sitio web del hotel" readonly>
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
													<input class="form-control" type="text" id="address" name="direccion" value="<?php echo $this->getDireccion();?>" placeholder="Direcci&oacute;n del hotel" readonly>
												</div><!-- /.input-group -->
											
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="postal-code">C&oacute;digo postal  del hotel <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
													<input class="form-control" type="text" id="postal-code" name="codigopostal" value="<?php echo $this->getCodigoPostal()?>" placeholder="C&oacute;digo postal del hotel" readonly >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div><!-- /.row -->
									<div class="row">
										<div class="col-lg-4">
											<div class="form-group">
												<label for="country-select">Pa&iacute;s <span class="required">*</span></label>
												<input class="form-control" type="text" id="city" value="<?php echo $this->getPais(); ?>" readonly>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="state-select">Estado <span class="required">*</span></label>
												<input class="form-control" type="text" id="estado" value="<?php echo $this->getEstado();?>" readonly>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-lg-4">
											<div class="form-group">
												<label for="city-select">Ciudad <span class="required">*</span></label>
												<input class="form-control" type="text" id="city" value="<?php echo $this->getCiudad(); ?>" readonly>
												
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
									</div><!-- /.row -->
									<hr>
											<div class="form-group">
											<label>Posici&oacute;n en el mapa <span class="required">*</span></label>
											<div class="detail-content">
											<div class="detail-map">
											<div class="map-position">
											<div id="listing-detail-map"
											data-transparent-marker-image="<?php echo HOST.'/assets/img/transparent-marker-image.png';?>"
											
											data-zoom="15"
											data-latitude="<?php echo $this->getLatitud(); ?>"
											data-longitude="<?php echo $this->getLongitud(); ?>"
											data-icon="fa fa-map-marker">
											</div><!-- /#map-property -->
											</div><!-- /.map-property -->
											</div><!-- /.detail-map -->
											</div>
											</div>

									<div class="row">
										<div class="col-sm-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
												<input class="form-control" type="text" id="input-latitude" name="latitud" value="<?php echo $this->getLatitud(); ?>" placeholder="Latitud" readonly>
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										<div class="col-sm-6">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
												<input class="form-control" type="text" id="input-longitude" name="longitud" value="<?php echo $this->getLongitud(); ?>" placeholder="Longitud" readonly>
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
													<input class="form-control" type="text" id="nombre_responsable" name="nombre_responsable" value="<?php echo $this->getNombreResponsable(); ?>" placeholder="Nombre del responsable &aacute;rea de promoci&oacute;n" readonly >
												</div><!-- /.input-group -->
											
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->

										<div class="col-lg-6">
											<div class="form-group">
												<label for="apellido">Apellido<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-address-card-o"></i></span>
													<input class="form-control" type="text" id="apellido_responsable" name="apellido_responsable" value="<?php echo $this->getApellidoResponsable(); ?>" placeholder="Apellido del responsable &aacute;rea de promoci&oacute;n" readonly >
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
													<input class="form-control" type="email" id="email" name="email" value="<?php echo $this->getEmail(); ?>" placeholder="Email del responsable" readonly >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->
										</div><!-- /.col-* -->
										

										<div class="col-lg-6">
											<div class="form-group">
												<label for="cargo">Cargo<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-black-tie"></i></span>
													<input class="form-control" type="text" id="cargo" name="cargo" value="<?php echo $this->getCargo(); ?>" placeholder="Cargo" readonly >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->

											

										</div><!-- /.col-* -->
										<div class="col-lg-6">
										<div class="form-group">
												<label for="phone">T&eacute;lefono fijo <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-phone-square"></i></span>
													<input class="form-control" type="text" id="phone" name="telefonofijo" value="<?php echo $this->getTelefonoFijo(); ?>" placeholder="N&uacute;mero de t&eacute;lefono fijo" readonly>
												</div><!-- /.input-group -->
									
											</div><!-- /.form-group -->
										</div>
										<div class="col-lg-6">
										<div class="form-group">
												<label for="phone">T&eacute;lefono novil <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-mobile-phone"></i></span>
													<input class="form-control" type="text" id="movil" name="movil" value="<?php echo $this->getTelefonoMovil(); ?>" placeholder="N&uacute;mero de t&eacute;lefono movil" readonly >
												</div><!-- /.input-group -->
											
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
													<input class="form-control" type="text" id="nombre_banco" name="nombre_banco" value="<?php echo $this->getBanco(); ?>" placeholder="Nombre del banco" readonly >
												</div><!-- /.input-group -->
											
											</div><!-- /.form-group -->

											<div class="form-group">
												<label for="cuenta">Cuenta<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
													<input class="form-control" type="text" id="cuenta" name="cuenta" value="<?php echo $this->getCuenta(); ?>" placeholder="Cuenta." readonly >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->

											<div class="form-group">
												<label for="clabe">Clabe<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
													<input class="form-control" type="text" id="clabe" name="clabe" value="<?php echo $this->getClabe(); ?>" placeholder="Clabe" readonly >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->

											<div class="form-group">
												<label for="swift">Swift / Bic<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
													<input class="form-control" type="text" id="swift" name="swift" value="<?php echo $this->getSwift(); ?>" placeholder="Swift" readonly >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->

										</div><!-- /.col-* -->



										<div class="col-lg-6 col-sm-4">
											<h5 class="page-title">Deposito a tarjeta</h5>
											<div class="form-group">
												<label for="nombre">Nombre del banco<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-bank"></i></span>
													<input class="form-control" type="text" id="nombre_banco_targeta" name="nombre_banco_tarjeta" value="<?php echo $this->getNombreBancoTarjeta(); ?>" placeholder="Nombre del banco" readonly >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->
											<div class="form-group">
												<label for="nombre">N&uacute;mero de tarjeta<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-cc"></i></span>
													<input class="form-control" type="text" id="numero_targeta" name="numero_targeta" value="<?php echo $this->getNumeroTarjeta(); ?>" placeholder="N&uacute;mero de Tarjeta" readonly>
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->
								
										
												<h5 class="page-title">Transferencia PayPal</h5>
											<div class="form-group">
												<label for="nombre">Email de Paypal<span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-cc-paypal"></i></span>
													<input class="form-control" type="email" id="email_paypal" name="email_paypal" value="<?php echo $this->getEmailPaypal(); ?>" placeholder="Nombre del banco" readonly >
												</div><!-- /.input-group -->
												
											</div><!-- /.form-group -->
										</div>
									</div>
								</div>
				</form>
		<?php  }

				break;
			case 'Franquiciatario':
				$this->DetallesSolicitudFranquiciatario->Mostrar();
			break;

			case 'Referidor':
				$this->DetallesSolicitudReferidor->Mostrar();
			break;


			default:
				# code...
				break;
		}
		

	  }

	public function load_data($id = null, $perfil = null,$proviene=null){

			switch ($perfil) {
				case 'Hotel':
					return $this->CargarHotel($id,$proviene);
					break;

				case 'Franquiciatario':
					return $this->CargarFranquiciatario($id,$proviene);
					break;

				case 'Referidor':
					return $this->CargarReferidor($id);
					break;
				
				default:
					return false;
					break;
			}					
	}


	public function crearcodigo($perfil, $codigohotel){


		if($this->con->inTransaction()){
			$this->con->rollback();

		}

		if($perfil == 'Hotel'){
			$this->con->beginTransaction();
				$query = "update hotel set codigo=:codigo where id=:id_hotel";
				
				try {
				
				$stm = $this->con->prepare($query);
				
				$stm->bindParam(':codigo',$codigohotel,PDO::PARAM_STR);
				$stm->bindParam(':id_hotel',$this->solicitudhotel['id_hotel'],PDO::PARAM_INT);
				
				$stm->execute();
				$this->con->commit();
				return true;
				
				} catch (PDOException $e) {
				
				$this->error_log(__METHOD__,__LINE__,$e->getMessage());
				
				$this->con->rollback();
				return true;
				
			}
		}else if($perfil == 'Franquiciatario'){
			return $this->DetallesSolicitudFranquiciatario->crearcodigo($codigohotel);
		}else if($perfil == 'Referidor'){
			return $this->DetallesSolicitudReferidor->crearcodigo($codigohotel);
		}

	


	}
	public function adjudicar($perfil, $comision = 0, $codigohotel = null,$id_hotel = 0){


			if($perfil == "Hotel"){
				$this->setComision($comision);

				if($this->con->inTransaction()){
					$this->con->rollback();
				}

				$this->con->beginTransaction();

				$query = "update hotel set comision =:comision, codigo=:codigo where id=:id_hotel";

				try {
					$stm = $this->con->prepare($query);
					if($id_hotel > 0){
						$stm->execute(array(':comision'=>$comision,':codigo'=>$codigohotel,':id_hotel'=>$id_hotel));
					}else{
						$stm->execute(array(':comision'=>$comision,':codigo'=>$codigohotel,':id_hotel'=>$this->solicitudhotel['id_hotel']));
					}
					
					$this->con->commit();

					$_SESSION['notification']['registro_hotel'] = "Se ha registrado y asignado codigo de hotel exitosamente...";
					return true;
				} catch (PDOException $e) {
					$this->con->rollback();
					$this->error_log(__METHOD__,__LINE__,$e->getMessage());
					return false;
				}
			}else if($perfil == "Franquiciatario"){
 					$this->DetallesSolicitudFranquiciatario->adjudicar($comision,$codigohotel,$id_hotel);
				return true;
			}
			else if($perfil == "Referidor"){
 					$this->DetallesSolicitudReferidor->adjudicar($comision,$codigohotel);
				return true;
			}
			
	}
	
	private function getEmailUsuario(){

		return	$email = $this->solicitudhotel['emailusuario'];	
	}

	public function aceptarSolicitud(array $post, $perfil = null){


				switch ($perfil) {
					case 'Hotel':
					$this->setNombreHotel($post['nombrehotel']);
					$this->setIata($post['id_iata']);
					$this->setSitioWeb($post['sitio_web']);
					$this->setDireccion($post['direccion']);
					$this->setCodigoPostal($post['codigopostal']);
					$this->setIdestado($post['id_estado']);
					$this->setIdpais($post['id_pais']);
					$this->setIdCiudad($post['id_ciudad']);
					$this->setLocation($post['latitud'],$post['longitud']);
					$this->setNombreResponsable($post['nombre_responsable']);
					$this->setApellidoResponsable($post['apellido_responsable']);
					$this->setEmail($post['email']);
					$this->setCargo($post['cargo']);
					$this->setTelefonofijo($post['telefonofijo']);
					$this->setTelefonomovil($post['telefonomovil']);
					$this->setBanco($post['nombre_banco']);
					
					$this->setCuenta($post['cuenta']);
					$this->setClabe($post['clabe']);
					$this->setSwift($post['swift']);
					$this->setNumeroTarjeta($post['numero_tarjeta']);
					$this->setBancoTarjeta($post['banco_tarjeta']);
					$this->setEmailPaypal($post['email_paypal']);
					$this->setId($post['id']);
					$this->setComentario($post['comentario']);

				if ($this->con->inTransaction()) {
					$this->con->commit();
				}

				$this->con->beginTransaction();

				$query1 ="update datospagocomision set banco=:banco, cuenta=:cuenta, clabe =:clabe, swift=:swift, banco_tarjeta=:banco_tarjeta, numero_tarjeta =:numero_tarjeta,email_paypal =:email_paypal where id=:id_datospagocomision";
				
				$query2 = "update persona set nombre=:nombre_responsable, apellido =:apellido_responsable where id=:id_persona";

				$query3 = "update responsableareapromocion set cargo=:cargo, email=:email, telefono_fijo =:telefonofijo, telefono_movil=:telefonomovil 
								where id=:id_responsableareapromocion";


				$query4 = "update hotel set nombre=:nombrehotel, id_iata=:id_iata, sitio_web=:sitioweb, direccion =:direccion, codigo_postal=:codigopostal,
							id_ciudad =:id_ciudad, longitud =:longitud, latitud =:latitud, aprobada =:aprobada where id =:id_hotel";

				$query5 = "update solicitudhotel set comentario =:comentario, condicion=:condicion where id =:nrosolicitud";

				$query6 = "update usuario set id_rol =:rol where id_usuario = (select id_usuario from solicitudhotel where id=:nrosolicitud)";
						
						try {
							$stm = 	$this->con->prepare($query1);

							
							$result = $stm->execute(array(':banco'=> $this->getBanco(),
															':cuenta'=> $this->getCuenta(),
															':clabe'=>$this->getClabe(),
															':swift'=>$this->getSwift(),
															':banco_tarjeta'=>$this->getNombreBancoTarjeta(),
															':numero_tarjeta'=>$this->getNumeroTarjeta(),
															':email_paypal'=>$this->getEmailPaypal(),
															':id_datospagocomision'=>$this->solicitudhotel['id_datospagocomision']));

						} catch (PDOException $e) {
							$this->error_log(__METHOD__,__LINE__,_safe($e->getMessage()));
							$this->con->rollback();

						}
						if($result){

							try {
								$stm1 = $this->con->prepare($query2);
								
								$result = $stm1->execute(array(':nombre_responsable'=>$this->getNombreResponsable(),
																':apellido_responsable'=>$this->getApellidoResponsable(),
																':id_persona'=>$this->solicitudhotel['id_persona']));


							} catch (PDOException $e) {

								$this->error_log(__METHOD__,__LINE__,_safe($e->getMessage()));
								$this->con->rollback();

							}

						if($result){
							try {

								$stm2 = $this->con->prepare($query3);
										
								$result = $stm2->execute(array(':cargo'=>$this->getCargo(),
																':email'=>$this->getEmail(),
																':telefonofijo'=>$this->getTelefonoFijo(),
																':telefonomovil'=>$this->getTelefonoMovil(),
																':id_responsableareapromocion' => $this->solicitudhotel['id_responsableareapromocion']));

							} catch (PDOException $e) {

										$this->error_log(__METHOD__,__LINE__,_safe($e->getMessage()));
										$this->con->rollback();

								}
							

								if($result){

									try {
										$stm3 = $this->con->prepare($query4);
										$datos = array(':nombrehotel'=>$this->getNombreHotel(),
														':id_iata'=>$this->solicitudhotel['id_iata'],
														':sitioweb'=>$this->getSitioWeb(),
														':direccion'=>$this->getDireccion(),
														':codigopostal'=>$this->getCodigoPostal(),
														':id_ciudad'=>$post['id_ciudad'],
														':longitud'=>$this->getLongitud(),
														':latitud'=>$this->getLatitud(),
														':aprobada'=>1,
														':id_hotel'=>$this->solicitudhotel['id_hotel']);
										
										$resultado = $stm3->execute($datos);

									} catch (PDOException $e) {
										$this->error_log(__METHOD__,__LINE__,_safe($e->getMessage()));
										$this->con->rollback();
									}
									
									if($resultado){
										try {
											$stm4 = $this->con->prepare($query5);
											$resultado = $stm4->execute(array(':comentario'=>$this->getComentario(),
															':condicion'=>1,
															':nrosolicitud'=>$this->getId()));
											$this->con->commit();

											$stm5 = $this->con->prepare($query6);
											$resultado = $stm5->execute(array(':rol'=>10,
																				':nrosolicitud'=>$this->getId()));
											$this->con->commit();


											//SE MANDA LA NOTIFICACION AL USUARIO
											$header = 'Tu solicitud de perfil ha sido aceptada por Travel Points ';
											$headeringles = 'Your profile request has been accepted by Travel Points';
											$link = 'Puedes ver tu perfil aquí: <a style="outline:none; color:#0082b7; text-decoration:none;" href="'.HOST.'/Hotel/">'.HOST.'/Hotel/"></a>.';

											$linkingles = 'You can see your profile here: <a style="outline:none; color:#0082b7; text-decoration:none;" href="'.HOST.'/Hotel/">'.HOST.'/Hotel/"></a>.';

											$body_alt = 'Tu solicitud de perfil ha sido aprobada por Travel Points. Puedes entrar al panel desde aquí: '.HOST.'Hotel/';
											require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/phpmailer/PHPMailerAutoload.php';
											$mail = new \PHPMailer;
											$mail->CharSet = 'UTF-8';
											// $mail->SMTPDebug = 3; // CONVERSACION ENTRE CLIENTE Y SERVIDOR
											$mail->isSMTP();
											$mail->Host = 'single-5928.banahosting.com';
											$mail->SMTPAuth = true;
											$mail->SMTPSecure = 'ssl';
											$mail->Port = 465;
											// El correo que hará el envío
											$mail->Username = 'notification@travelpoints.com.mx';
											$mail->Password = '20464273jd';
											$mail->setFrom('notification@travelpoints.com.mx', 'Travel Points');
											// El correo al que se enviará
											$mail->addAddress($this->getEmailUsuario());
										
												$mail->AddCC($this->getEmailUsuario());
											
											// Hacerlo formato HTML
											$mail->isHTML(true);
											// Formato del correo
											$mail->Subject = 'Tu solicitud del perfil de Hotel ha sido aceptada. | Your request for a hotel profile has been accepted. ';
											$mail->Body    = $this->email_template($header,$headeringles, $link,$linkingles);
											$mail->AltBody = $body_alt;
											// Enviar
											if(!$mail->send()){
												$_SESSION['notificacion']['info'] = 'El correo de aviso no se pudo enviar debido a una falla en el servidor.';
											}

											$_SESSION['notificacion']['success'] = 'Solicitud aceptada exitosamente. El perfil ha sido creado.';
											
											return;
										} catch (PDOException $e) {
											$this->error_log(__METHOD__,__LINE__,_safe($e->getMessage()));
											$this->con->rollback();	
									}
							}
							}							
						}
					}
		
					$this->error['warning'] = 'Uno o más campos tienen errores. Verifícalos cuidadosamente.';
					return false;

					break;

					case 'Franquiciatario':
						$this->DetallesSolicitudFranquiciatario->aceptarsolicitud($post);
					break;

					case 'Referidor':
						$this->DetallesSolicitudReferidor->aceptarsolicitud($post);
					break;

					default:
						# code...
						break;
				}			
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
			$mail->Host = 'single-5928.banahosting.com';
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = 'ssl';
			$mail->Port = 465;
			// El correo que hará el envío
			$mail->Username = 'notification@travelpoints.com.mx';
			$mail->Password = '20464273jd';
			$mail->setFrom('notification@travelpoints.com.mx', 'Travel Points');
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

	public function rechazarsolicitud(array $post, $perfil = null){


		switch ($perfil) {
			case 'Hotel':
					$this->set_comment($post['comment']);
					if(!array_filter($this->error)){
					$query = "UPDATE solicitudhotel SET situacion = 4, mostrar_usuario = 4, comentario = :comentario WHERE id = :nrosolicitud";
					try{
					
					$stmt = $this->con->prepare($query);
					
					$stmt->bindValue(':comentario', $this->request['comment'], PDO::PARAM_STR);
					
					$stmt->bindValue(':nrosolicitud', $this->nrosolicitud, PDO::PARAM_INT);
					
					$stmt->execute();
					
					}catch(\PDOException $ex){
					$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
					return false;
					}
					// SE MANDA LA NOTIFICACION AL USUARIO
					$header = 'Lamentamos informarte que la solicitud para afiliar tu negocio ha sido rechazada';
					$headeringles = 'We regret to inform you that the request for your hotel has been rejected';
					
					$link = '<a style="outline:none; color:#0082b7; text-decoration:none;" href="'.HOST.'/socio/hotel/solicitud/'.$this->nrosolicitud.'">Ver mi solicitud</a>.';
					
					$linkingles = '<a style="outline:none; color:#0082b7; text-decoration:none;" href="'.HOST.'/socio/hotel/solicitud/'.$this->nrosolicitud.'">See my request</a>.';
					
					$body_alt =
					'Lamentamos informarte que la solicitud para afiliar tu negocio ha sido rechazada. Puedes ver tu solicitud aquí: '.HOST.'/socio/hotel/solicitud/'.$this->nrosolicitud;
					
					require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/phpmailer/PHPMailerAutoload.php';
					$mail = new \PHPMailer;
					$mail->CharSet = 'UTF-8';
					// $mail->SMTPDebug = 3; // CONVERSACION ENTRE CLIENTE Y SERVIDOR
					$mail->isSMTP();
					$mail->Host = 'single-5928.banahosting.com';
					$mail->SMTPAuth = true;
					$mail->SMTPSecure = 'ssl';
					$mail->Port = 465;
					// El correo que hará el envío
					$mail->Username = 'notification@travelpoints.com.mx';
					$mail->Password = '20464273jd';
					$mail->setFrom('notification@travelpoints.com.mx', 'Travel Points');
					// El correo al que se enviará
					$mail->addAddress($this->getEmailUsuario());
					if($this->getEmailUsuario() != $this->getEmailUsuario()){
					$mail->AddCC($this->getEmailUsuario());
					}
					// Hacerlo formato HTML
					$mail->isHTML(true);
					// Formato del correo
					$mail->Subject = 'Solicitud para afiliar tu hotel  has sido rechazada';
					
					$mail->Body    = $this->email_template($header,$headeringles, $link, $linkingles);
					
					$mail->AltBody = $body_alt;
					// Enviar
					if(!$mail->send()){
					$_SESSION['notification']['info'] = 'El correo de aviso no se pudo enviar debido a una falla en el servidor.';
					}
					$_SESSION['notification']['success'] = 'Solicitud rechazada exitosamente';
					
					header('Location: '.HOST.'/admin/perfiles/solicitudes');
					die();
					return;	
					}
					$this->error['warning'] = 'Uno o más campos tienen errores. Verifícalos cuidadosamente.';
					return false;
				break;
			case 'Franquiciatario':
				$DetallesSolicitudFranquiciatario->rechazarsolicitud($post);	
				break;

			case 'Referidor':
				$DetallesSolicitudReferidor->rechazarsolicitud($post);	
				break;
			
			default:
				return false;
				break;
		}

		
	}
	public function EliminarSolicitudReferidor(){


			return $this->DetallesSolicitudReferidor->EliminarSolicitud('Admin');

	}
	public function EliminarSolicitudFranquiciatario(){


			return $this->DetallesSolicitudFranquiciatario->EliminarSolicitud('Admin');

	}

	public function EliminarSolicitud($perfil = null,$solicitud =0){

		if($perfil == 'Hotel'){

			$this->con->beginTransaction();


			$sql = 'select * from huespedhotel where id_hotel =(select id_hotel from solicitudhotel where id = :solicitud)';

			try {
				$stm = $this->con->prepare($sql);

				if($solicitud > 0){
					$stm->bindParam(':solicitud',$this->$solicitud);
				}else{
					$stmt->bindValue(':solicitud', $this->solicitudhotel['id'], PDO::PARAM_INT);
				}
				

				$stm->execute();

				if($stm->rowCount() > 0){
					$sql2 = "delete from huespedhotel where id_hotel =(select id_hotel from solicitudhotel where id = :solicitud)";
					$stm = $this->con->prepare($sql2);
					if($solicitud > 0){
						$stm->bindParam(':solicitud',$this->$solicitud);
					}else{
						$stm->bindValue(':solicitud', $this->solicitudhotel['id'], PDO::PARAM_INT);
					}

					$stm->execute();
				}

			} catch (PDOException $e) {
				$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			$query1 = "delete from hotel where id = (select id_hotel from solicitudhotel where id = :solicitud)";
			$query = "delete from solicitudhotel where id=:id_solicitud";
		try{
			$st = $this->con->prepare($query1);
			$stmt = $this->con->prepare($query);
			if($solicitud > 0){
				$st->bindValue(':solicitud',$solicitud, PDO::PARAM_INT);
				$stmt->bindValue(':id_solicitud',$solicitud, PDO::PARAM_INT);
			}else{
				$st->bindValue(':solicitud', $this->solicitudhotel['id'], PDO::PARAM_INT);
				$stmt->bindValue(':id_solicitud', $this->solicitudhotel['id'], PDO::PARAM_INT);
			}
			$result = $st->execute();
			if($result){
				$stmt->execute();
				$this->con->commit();
			}else{
				$this->con->rollBack();
				return false;
			}
			

		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}


		// SE MANDA LA NOTIFICACION AL USUARIO
			$header = 'Lamentamos informarte que la solicitud para afiliar tu negocio ha sido rechazada';
			$headeringles = 'We regret to inform you that the request for your hotel has been rejected';

			$link = '<a style="outline:none; color:#0082b7; text-decoration:none;" href="'.HOST.'/socio/hotel/solicitud/'.$this->nrosolicitud.'">Ver mi solicitud</a>.';

			$linkingles = '<a style="outline:none; color:#0082b7; text-decoration:none;" href="'.HOST.'/socio/hotel/solicitud/'.$this->nrosolicitud.'">See my request</a>.';

			$body_alt =
				'Lamentamos informarte que la solicitud para afiliar tu negocio ha sido rechazada. Puedes ver tu solicitud aquí: '.HOST.'/socio/hotel/solicitud/'.$this->nrosolicitud;

			if($solicitud == 0){
					require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libraries/phpmailer/PHPMailerAutoload.php';
					$mail = new \PHPMailer;
					$mail->CharSet = 'UTF-8';
					// $mail->SMTPDebug = 3; // CONVERSACION ENTRE CLIENTE Y SERVIDOR
					$mail->isSMTP();
					$mail->Host = 'single-5928.banahosting.com';
					$mail->SMTPAuth = true;
					$mail->SMTPSecure = 'ssl';
					$mail->Port = 465;
					// El correo que hará el envío
					$mail->Username = 'notification@travelpoints.com.mx';
					$mail->Password = '20464273jd';
					$mail->setFrom('notification@travelpoints.com.mx', 'Travel Points');
					// El correo al que se enviará
					$mail->addAddress($this->getEmailUsuario());
					if($this->getEmailUsuario() != $this->getEmailUsuario()){
					$mail->AddCC($this->getEmailUsuario());
					}
					// Hacerlo formato HTML
					$mail->isHTML(true);
					// Formato del correo
					$mail->Subject = 'Solicitud para afiliar tu hotel  has sido rechazada';
					
					$mail->Body    = $this->email_template($header,$headeringles, $link, $linkingles);
					
					$mail->AltBody = $body_alt;
					// Enviar
					// 
					
					if(!$mail->send()){
						$_SESSION['notification']['info'] = 'El correo de aviso no se pudo enviar debido a una falla en el servidor.';
					}
					
			}else{
				return true;
			}
			
			
			$_SESSION['notification']['success'] = 'Solicitud rechazada y eliminada exitosamente';
			
			header('Location: '.HOST.'/admin/perfiles/solicitudes');
			die();
			return;	
	}else if($perfil == 'Franquiciatario'){
		$this->DetallesSolicitudFranquiciatario->EliminarSolicitud();
	}else if($perfil == 'Referidor'){
		$this->DetallesSolicitudReferidor->EliminarSolicitud();
	}
		
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
															<img alt="Travel Points" src="'.HOST.'/assets/img/LOGOV.png" style="padding-bottom: 0; display: inline !important; width:250px; height:auto;">
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
													<td align="center" class="tablepadding" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
														<strong>'._safe($headeringles).'</strong>
													</td>
												</tr>
												<tr>
													<td class="tablepadding" align="center" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
														'.$link.'<br>
														Para cualquier aclaraci&oacute;n contacta a nuestro equipo de soporte.<br>
														<a style="outline:none; color:#0082b7; text-decoration:none;" href="mailto:soporte@infochannel.si">
															soporte@infochannel.si
														</a>
													</td>
												</tr>

												<tr>
													<td class="tablepadding" align="center" style="color: #444; padding:10px; font-size:14px; line-height:20px;">
														'.$linkingles.'<br>
														For any clarification, contact our support team.<br>
														<a style="outline:none; color:#0082b7; text-decoration:none;" href="mailto:soporte@infochannel.si">
															soporte@infochannel.si
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
													<a href="https://www.facebook.com/TravelPointsMX" target="_blank" style="text-decoration:none; outline:none;">
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
											&copy; Travel Points '.date('Y').' Todos los derechos reservados.
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

	private function setNombreHotel($string = null){
		if($string){
			$string = trim($string);
			$this->request['name'] = $string;
			return true;
		}
		$this->error['name'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setBanco($string = null){
		if($string){
			$string = trim($string);
			$this->solicitudhotel['banco'] = $string;
			return true;
		}
		$this->error['banco'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setBancoTarjeta($string = null){
		if($string){
			$string = trim($string);
			$this->solicitudhotel['banco_tarjeta'] = $string;
			return true;
		}
		$this->error['banco_tarjeta'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setNumeroTarjeta($string = null){
		if($string){
			$string = trim($string);
			$this->solicitudhotel['numero_tarjeta'] = $string;
			return true;
		}
		$this->error['numero_tarjeta'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setNombreResponsable($string = null){
		if($string){
			$this->solicitudhotel['nombre_responsable'] = trim($string);
			return true;
		}
		$this->error['nombre_responsable'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setApellidoResponsable($string = null){
		if($string){
			$this->solicitudhotel['apellido_responsable'] = trim($string);
			return true;
		}
		$this->error['apellido_responsable'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setCargo($string = null){
		if($string){
			$this->solicitudhotel['cargo'] = trim($string);
			return true;
		}
		$this->error['cargo'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setCuenta($string = null){
		if($string){
			$this->solicitudhotel['cuenta'] = trim($string);
			return true;
		}
		$this->error['cuenta'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setComision($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 6 || $string > 100){
				$this->error['comision'] = 'Ingresa un número entero entre 6 y 100.';
				return false;
			}
			$this->solicitudhotel['comision'] = $string;
			return true;
		}
		$this->error['comision'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setClabe($string = null){
		$this->solicitudhotel['clabe'] = $string;
	}

	private function setSwift($string = null){
		$this->solicitudhotel['swift'] = $string;
	}

	private function setEmail($string = null){
		if($string){
			$email = filter_var($string, FILTER_VALIDATE_EMAIL);
			if(!$email){
				$this->error['email'] = 'Escribe una dirección de correo electrónico correcta. Ejemplo: usuario@ejemplo.com.';
				$this->solicitudhotel['email'] = $string;
				return false;
			}
			$this->solicitudhotel['email'] = $email;
			return true;
		}
		$this->error['email'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setEmailPaypal($string = null){
		if($string){
			$email = filter_var($string, FILTER_VALIDATE_EMAIL);
			if(!$email){
				$this->error['email_paypal'] = 'Escribe una dirección de correo electrónico correcta. Ejemplo: usuario@ejemplo.com.';
				$this->solicitudhotel['email_paypal'] = $string;
				return false;
			}
			$this->solicitudhotel['email_paypal'] = $email;
			return true;
		}
		$this->error['email_paypal'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setTelefonofijo($string = null){
		if($string){
			$string = trim($string);
			if(!preg_match('/^[0-9() +-]+$/ui',$string)){
				$this->error['telefonofijo'] = 'Escribe un número telefono local correcto. Ejemplo: (123) 456-78-90';
				$this->solicitudhotel['telefono_fijo'] = $string;
				return false;
			}
			$this->solicitudhotel['telefono_fijo'] = $string;
			return true;
		}
		$this->error['telefono_fijo'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setTelefonomovil($string = null){
		if($string){
			$string = trim($string);
			if(!preg_match('/^[0-9() +-]+$/ui',$string)){
				$this->error['telefonomovil'] = 'Escribe un número telefóno movil correcto. Ejemplo: (123) 456-78-90';
				$this->solicitudhotel['telefono_movil'] = $string;
				return false;
			}
			$this->solicitudhotel['telefono_movil'] = $string;
			return true;
		}
		$this->error['telefono_movil'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setSitioWeb($string = null){
		if($string){
			if(!preg_match('_^(?:(?:https?|ftp)://)?(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,}))\.?)(?::\d{2,5})?(?:[/?#]\S*)?$_iuS',$string)){
				$this->error['sitio_web'] = 'Escribe un enlace correcto. Ejemplo: www.travelpoints.com.mx o http://travelpoints.com.mx';
				$this->solicitudhotel['sitio_web'] = $string;
				return false;
			}
			if(!preg_match("@^https?://@", $string)){
				$this->solicitudhotel['sitio_web'] = 'http://'.$string;
			}else{
				$this->solicitudhotel['sitio_web'] = $string;
			}
		}
		return true;
	}

	private function setDireccion($string = null){
		if($string){
			$string = trim($string);
			$this->solicitudhotel['direccion'] = $string;
			return true;
		}
		$this->error['direccion'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setCodigoPostal($string = null){
		if($string){
			$string = trim($string);
			$this->solicitudhotel['codigo_postal'] = $string;
			return true;
		}
		$this->error['postal_code'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setIata($string = null){
		if($string){
			$this->solicitudhotel['id_iata'] = $string;
			return true;
		}
		$this->error['iata'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setIdCiudad($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				$this->error['ciudad'] = 'Selecciona una ciudad.';
				return false;
			}
			$this->solicitudhotel['id_ciudad'] = $string;
			return true;
		}
		$this->error['ciudad'] = 'Este campo es obligatorio.';
		return false;
	}

	private function setIdestado($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				return false;
			}
			$this->solicitudhotel['id_estado'] = $string;
			return true;
		}
		return false;
	}

	private function setIdpais($string = null){
		if($string){
			$string = filter_var($string, FILTER_VALIDATE_INT);
			if(!$string || $string < 1){
				return false;
			}
			$this->solicitudhotel['id_pais'] = $string;
			return true;
		}
		return false;
	}

	private function setLocation($lat = null, $lon = null){
		if($lat & $lon){
			if(!filter_var($lat, FILTER_VALIDATE_FLOAT) || !filter_var($lon, FILTER_VALIDATE_FLOAT)){
				$this->error['location'] = 'Utiliza el marcador del mapa para ubicar tu negocio.';
				return false;
			}else{
				$this->solicitudhotel['latitud'] = trim($lat);
				$this->solicitudhotel['longitud'] = trim($lon);
				return true;
			}
		}
		$this->error['location'] = 'Es obligatorio ubicar tu negocio en el mapa.';
		return false;
	}

	private function setComentario($comment = null){
		if($comment){
			$this->solicitudhotel['comentario'] = trim($comment);
			return;
		}
		$this->error['comentario'] = 'Este campo es obligatorio.';
		return false;
	}

	public function getHeader(){

		switch ($this->getCondicion()) {
			case 0:
				$status_tag = '<span class="label label-md label-warning mr20">Solicitud pendiente</span>';
				$form = '';
			break;
			
			case 1:
				$status_tag = 
					'<span class="label label-md label-success mr20">Solicitud aceptada</span>';
				$form = 
					'
						<button class="btn btn-xs btn-danger" type="submit" id="delete-request" name="eliminarsolicitud"><i class="fa fa-times m0"></i></button>
					';
				break;
		
			case 3:
				$status_tag = '<span class="label label-md label-info mr20">Corregir solicitud</span>';
				$form = '';
				break;
			case 4:
				$status_tag = '<span class="label label-md label-danger mr20">Solicitud rechazada</span>';
				$form = 
					'
						<button class="btn btn-xs btn-danger" type="submit" id="delete-request" name="eliminarsolicitud"><i class="fa fa-times m0"></i></button>
					';
				break;
			default:
				$status_tag = '';
				break;
		}
		$title_tag = '<label class="cert-date mr20">#'.$this->getId().'</label>'.$status_tag.'<label class="cert-date">'.$this->getFecha().'</label><span class="pull-right">Solicitud enviada por <a class="mr20" href="'.HOST.'/socio/'.$this->getUsername().'" target="_blank">'.$this->getAlias().'</a>'.$form.'</span>';
		if(!empty($this->solicitudhotel['comentario'])){
			$html = 
			'<div class="page-title">'.$title_tag.'</div>
			<label>Comentario de Travel Points para el solicitante</label>
			<p>'.nl2br(_safe($this->solicitudhotel['comentario'])).'</p>';
		}else{
			$html = $title_tag;
		}
		return $html;
	}

	public function getId(){
		return $this->solicitudhotel['id'];
	}

	public function setId($id){
		return $this->solicitudhotel['id'];
	}
	public function getUsername(){
		return _safe($this->solicitudhotel['username']);
	}

	public function getAlias(){
		if($this->solicitudhotel['usuario_nombre'] && $this->solicitudhotel['usuario_apellido']){
			return _safe($this->solicitudhotel['usuario_nombre'].' '.$this->solicitudhotel['usuario_apellido']);
		}else{
			return _safe($this->solicitudhotel['username']);
		}
	}

	public function getNombreHotel(){
		return _safe($this->solicitudhotel['hotel']);
	}

	public function getNombreResponsable(){
		return _safe($this->solicitudhotel['nombre_responsable']);
	}

	public function getApellidoResponsable(){
		return _safe($this->solicitudhotel['apellido_responsable']);
	}

	public function getIata(){
		$iata = _safe($this->solicitudhotel['iata']);
		$id_iata = $this->solicitudhotel['id_iata'];

		return '<option value="'.$id_iata.'" selected>'.$iata.'</option>';
	}
	public function getIatas(){
		$html = null;
	
		foreach ($this->iatas as $key => $value) {
			if($this->solicitudhotel['id_iata'] == $key){
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
		return _safe($this->solicitudhotel['comision']) . " %";
	}

	public function getComisionError(){
		if($this->error['comision']){
			return '<p class="text-danger">'._safe($this->error['comision']).'</p>';
		}
	}

	public function getEmail(){
		return _safe($this->solicitudhotel['email']);
	}

	public function getEmailPaypal(){
		return _safe($this->solicitudhotel['email_paypal']);
	}

	public function getCargo(){
		return _safe($this->solicitudhotel['cargo']);
	}

	public function getEmailError(){
		if($this->error['email']){
			return '<p class="text-danger">'._safe($this->error['email']).'</p>';
		}
	}

	public function getTelefonoFijo(){
		return _safe($this->solicitudhotel['telefono_fijo']);
	}

	public function getTelefonoMovil(){
		return _safe($this->solicitudhotel['telefono_movil']);
	}

	public function get_phone_error(){
		if($this->error['phone']){
			return '<p class="text-danger">'._safe($this->error['phone']).'</p>';
		}
	}

	public function getSitioWeb(){
		return _safe($this->solicitudhotel['sitio_web']);
	}

	public function get_website_error(){
		if($this->error['website']){
			return '<p class="text-danger">'._safe($this->error['website']).'</p>';
		}
	}

	public function getDireccion(){
		return _safe($this->solicitudhotel['direccion']);
	}

	public function get_address_error(){
		if($this->error['address']){
			return '<p class="text-danger">'._safe($this->error['address']).'</p>';
		}
	}

	public function getCodigoPostal(){
		return _safe($this->solicitudhotel['codigo_postal']);
	}

	public function getBanco(){
		return _safe($this->solicitudhotel['banco']);
	}

	public function getCuenta(){
		return _safe($this->solicitudhotel['cuenta']);
	}

	public function getClabe(){
		return $this->solicitudhotel['clabe'];
	}

	public function getSwift(){
		return $this->solicitudhotel['swift'];
	}

	public function getNombreBancoTarjeta(){
		return _safe($this->solicitudhotel['banco_tarjeta']);
	}

	public function getNumeroTarjeta(){
		return _safe($this->solicitudhotel['numero_tarjeta']);
	}




	public function get_postal_code_error(){
		if($this->error['postal_code']){
			return '<p class="text-danger">'._safe($this->error['postal_code']).'</p>';
		}
	}

	public function getPais(){
		return _safe($this->solicitudhotel['pais']);
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
			$country = _safe($row['pais']);
			if($this->solicitudhotel['id_pais'] == $row['id_pais']){
				$html .= '<option value="'.$row['id_pais'].'" selected>'.$country.'</option>';
			}else{
				$html .= '<option value="'.$row['id_pais'].'">'.$country.'</option>';
			}
		}
		return $html;
	}

	public function get_country_error(){
		if($this->error['country']){
			return '<p class="text-danger">'._safe($this->error['country']).'</p>';
		}
	}

	public function getEstado(){
		return _safe($this->solicitudhotel['estado']);
	}

	public function getEstados(){
		$states = null;

		if($this->solicitudhotel['id_pais']){
			$query = "SELECT id_estado, estado FROM estado WHERE id_pais = :id_pais";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_pais', $this->solicitudhotel['id_pais'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$state = _safe($row['estado']);
				if($this->solicitudhotel['id_estado'] == $row['id_estado']){
					$states .= '<option value="'.$row['id_estado'].'" selected>'.$state.'</option>';
				}else{
					$states .= '<option value="'.$row['id_estado'].'">'.$state.'</option>';
				}
			}
		}
		return $states;
	}

	public function get_state_error(){
		if($this->error['state']){
			return '<p class="text-danger">'._safe($this->error['state']).'</p>';
		}
	}

	public function getCiudad(){
		return _safe($this->solicitudhotel['ciudad']);
	}

	public function getCiudades(){
		$cities = null;
		if($this->solicitudhotel['id_estado']){
			$query = "SELECT id_ciudad, ciudad FROM ciudad WHERE id_estado = :id_estado";
			try{
				$stmt = $this->con->prepare($query);
				$stmt->bindValue(':id_estado', $this->solicitudhotel['id_estado'], PDO::PARAM_INT);
				$stmt->execute();
			}catch(\PDOException $ex){
				$this->catch_errors(__METHOD__,__LINE__,$ex->getMessage());
				return false;
			}
			while($row = $stmt->fetch()){
				$city = _safe($row['ciudad']);
				if($this->solicitudhotel['id_ciudad'] == $row['id_ciudad']){
					$cities.= '<option value="'.$row['id_ciudad'].'" selected>'.$city.'</option>';
				}else{
					$cities.= '<option value="'.$row['id_ciudad'].'">'.$city.'</option>';
				}
			}
		}
		return $cities;
	}


	public function get_city_error(){
		if($this->error['city']){
			return '<p class="text-danger">'._safe($this->error['city']).'</p>';
		}
	}

	public function getLatitud(){
		return $this->solicitudhotel['latitud'];
	}

	public function getLongitud(){
		return $this->solicitudhotel['longitud'];
	}

	public function get_location_error(){
		if($this->error['location']){
			return '<p class="text-danger">'._safe($this->error['location']).'</p>';
		}
	}

	public function get_logo(){
		$html = 
			'<a href="'.HOST.'/assets/img/business_request/'.$this->request['logo'].'">
				<img class="img-thumbnail img-rounded gallery-img" src="'.HOST.'/assets/img/business_request/'.$this->request['logo'].'">
			</a>';
		return $html;
	}

	public function get_header_photo(){
		$html = 
			'<a href="'.HOST.'/assets/img/business_request/'.$this->request['header'].'">
				<img class="img-thumbnail img-rounded gallery-img" src="'.HOST.'/assets/img/business_request/'.$this->request['header'].'">
			</a>';
		return $html;
	}

	public function get_logo_error(){
		if($this->error['logo']){
			$error = '<p class="text-danger">'._safe($this->error['logo']).'</p>';
			return $error;
		}
	}

	public function get_photo_error(){
		if($this->error['photo']){
			$error = '<p class="text-danger">'._safe($this->error['photo']).'</p>';
			return $error;
		}
	}

	public function getComentario(){
		return _safe($this->solicitudhotel['comentario']);
	}

	public function get_comment_error(){
		if($this->error['comment']){
			return '<p class="text-danger">'._safe($this->error['comment']).'</p>';
		}
	}

	public function getCondicion(){
		return $this->solicitudhotel['condicion'];
	}

	public function getFecha(){
		return date('d/m/Y g:i A', strtotime($this->solicitudhotel['creado']));
	}

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
		if(isset($_SESSION['notification']['info'])){
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

	private function error_log($method, $line, $error){
		echo "jhonatan";
		file_put_contents(ROOT.'\assets\error_logs\solicitudperfilerror.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
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