<?php 

namespace admin\libs;

use PDO;
/**
 * @author Crespo jhonatan...
 */
class DetallesSolicitudReferidor{
	
	private $con;

	//Campos...
	private $registro = array(

		//datos de la solicitud
		'solicitud'            => null,
		'id_usuario'           => null,
		'username'             => null,
		'nombre_usuario'       => null,
		'apellido_usuario'     => null,
		'id_referidor'         => null,
		'comentario'           => null,
		'condicion'            => null,
		'creado'               => null,

		
		//datos del referidor
		'nombrecompleto'       => null,
		'nombre'               => null,
		'apellido'             => null,
		'email'                => null,
		'telefonofijo'         => null,
		'telefonomovil'        => null,
		
		//datos pago comisiones 
		'id_datospagocomision' => null,
		'banco'                => null,
		'cuenta'               => null,
		'clabe'                => null,
		'swift'                => null,
		'banco_tarjeta'        => null,
		'numero_tarjeta'       => null,
		'email_paypal'         => null,
		
		//Datos del hotel a Franquiciar
		//
		
		'nombrehotel'          => null,
		'direccion'            => null,
		'pais'                 => null,
		'ciudad'               => null,
		'estado'               => null,
		'sitioweb'             => null,
		'comision'             => null,
		'codigo'               => null,
		'id_iata'              => null,
		'codigopostal'         =>null,
		'id_ciudad'            => null,
		'id_estado'            =>null

	);

	private $error = array(
		'error' => null
	);

	function __construct($con = null, $solicitud = null){
		
		$this->con = $con;
		
		if(!empty($solicitud)){
			$this->registro['solicitud'] = $solicitud;
			$this->cargarDatos();
		}
	}

	private function cargarDatos(){

		$query = "select 
						u.nombre as nombre_usuario,
						u.apellido as apellido_usuario, 
						u.username, 
						u.id_usuario, 
						srf.id as solicitud, 
						srf.comentario,
						srf.condicion,
						srf.creado, 
						srf.hotel,
						srf.sitioweb,
						srf.direccion,
						srf.codigopostal,
						srf.id_iata,

						i.codigo,

						CONCAT(u.nombre,' ',u.apellido) as nombrecompleto, 
						u.email,
						rf.id as id_referidor, 
						rf.telefonomovil, 
						rf.telefonofijo, 
						rf.comision, 
						rf.aprobada, 
						rf.codigo_hotel,
						rf.nombre,
						rf.apellido,
						dpc.id as id_datospagocomision,
						dpc.banco,
						dpc.cuenta,
						dpc.clabe,
						dpc.swift,
						dpc.banco_tarjeta,
						dpc.numero_tarjeta,
						dpc.email_paypal,
						c.ciudad,
						c.id_ciudad, 
						p.pais,
						e.estado,
						e.id_estado
				from referidor as rf
				left join datospagocomision as dpc on rf.id_datospagocomision = dpc.id 
				inner join solicitudreferidor as srf on rf.id = srf.id_referidor 
				inner join ciudad as c on srf.id_ciudad = c.id_ciudad
				inner join estado as e on srf.id_estado = e.id_estado
				inner join pais as p on e.id_pais = p.id_pais
				join iata i on srf.id_iata = i.id
				
				
				inner join usuario as u on srf.id_usuario = u.id_usuario				
		where srf.id = :solicitud";

		try {
			$stm = $this->con->prepare($query);
			$stm->bindValue(':solicitud', $this->registro['solicitud'], PDO::PARAM_INT);
			$stm->execute();
		} catch (PDOException $e) {
			$this->registrarerror(__METHOD__,__LINE__,$e->getMessage());
			return false;
		}
		
		$valores = $stm->fetch(PDO::FETCH_ASSOC);

		$this->registro['id_usuario']           = $valores['id_usuario'];
		$this->registro['username']             = $valores['username'];
		$this->registro['apellido_usuario']     = $valores['apellido_usuario'];
		$this->registro['nombre_usuario']       = $valores['nombre_usuario'];
		$this->registro['id_referidor']         = $valores['id_referidor'];
		$this->registro['comentario']           = $valores['comentario'];
		$this->registro['condicion']            = $valores['condicion'];
		$this->registro['nombrecompleto']       = $valores['nombrecompleto'];
		$this->registro['email']                = $valores['email'];
		$this->registro['telefonofijo']         = $valores['telefonofijo'];
		$this->registro['telefonomovil']        = $valores['telefonomovil'];
		$this->registro['id_datospagocomision'] = $valores['id_datospagocomision'];
		$this->registro['banco']                = $valores['banco'];
		$this->registro['cuenta']               =$valores['cuenta'];
		$this->registro['nombre']               = $valores['nombre'];
		$this->registro['apellido']             = $valores['apellido'];
		$this->registro['codigo']             = $valores['codigo'];
		$this->registro['id_iata']             = $valores['id_iata'];
		
		$this->registro['clabe']                = $valores['clabe'];
		$this->registro['swift']                = $valores['swift'];
		$this->registro['banco_tarjeta']        = $valores['banco_tarjeta'];
		$this->registro['numero_tarjeta']       = $valores['numero_tarjeta'];
		$this->registro['email_paypal']         = $valores['email_paypal'];
		
		$this->registro['nombrehotel']          = $valores['hotel'];
		$this->registro['direccion']            = $valores['direccion'];
		$this->registro['sitioweb']             = $valores['sitioweb'];
		$this->registro['pais']                 = $valores['pais'];
		$this->registro['estado']               = $valores['estado'];
		$this->registro['ciudad']               = $valores['ciudad'];
		$this->registro['comision']             = $valores['comision'];
		$this->registro['codigopostal']             = $valores['codigopostal'];

		$this->registro['id_ciudad']             = $valores['id_ciudad'];
		$this->registro['id_estado']             = $valores['id_estado'];

		
		$this->registro['creado']               = $valores['creado'];
	}

	// GETTERS Y SETTERS 
	 
	public function EliminarSolicitud(){

	
			$query = "delete from solicitudreferidor where id=:id_solicitud";
		try{
			$stmt = $this->con->prepare($query);
			$stmt->bindValue(':id_solicitud', $this->registro['solicitud'], PDO::PARAM_INT);
			$stmt->execute();
		}catch(\PDOException $ex){
			$this->error_log(__METHOD__,__LINE__,$ex->getMessage());
			return false;
		}
		$_SESSION['notification']['success'] = 'Solicitud eliminada exitosamente';
		header('Location: '.HOST.'/admin/perfiles/solicitudes');
		die();
		return;

	}
	
	public function getCodigo(){
		return $this->registro['codigo'];
	}
	 public function getComision(){
	 	return $this->registro['comision'];
	 }
	
	public function getUsername(){
		return $this->registro['username'];

	}
	public function getNombreHotel(){
		return $this->registro['nombrehotel'];
	}
	public function getNombre(){
		return $this->registro['nombre'];
	}
	public function getApellido(){
		return $this->registro['apellido'];
	}
	public function getDireccion(){
		return $this->registro['direccion'];

	}
	public function getSitioWeb(){

		$sitioweb = 'Ninguno';

		if(empty($this->registro['sitioweb'])){
			return $sitioweb;
		}else{
			return $this->registro['sitioweb'];
		}

		

	}

	public function getPais(){
		return $this->registro['pais'];
	}


	public function getIata(){
		$iata = _safe($this->registro['codigo']);
		$id_iata = $this->registro['id_iata'];

		return '<option value="'.$id_iata.'" selected>'.$iata.'</option>';
	}

	public function getEstado(){
		return $this->registro['estado'];
	}

	public function getCiudad(){
		return $this->registro['ciudad'];
	}
	public function getComentario(){
		return htmlentities($this->registro['comentario'], ENT_QUOTES, 'UTF-8');

	}

	public function getCondicion(){
		return $this->registro['condicion'];

	}

	public function getNombreSolicitante(){
		return htmlentities($this->registro['nombrecompleto'], ENT_QUOTES, 'UTF-8');
	}

	public function getEmailSolicitante(){
		return htmlentities($this->registro['email'], ENT_QUOTES, 'UTF-8');
	}

	public function getTelefonofijo(){
		return $this->registro['telefonofijo'];
	}
	
	public function getTelefonomovil(){
		return $this->registro['telefonomovil'];
	}

	public function getBanco(){
		return $this->registro['banco'];
	}

	public function getCuenta(){
		return $this->registro['cuenta'];
	}

	public function getClabe(){
		return $this->registro['clabe'];
	}

	public function getSwift(){
		return $this->registro['swift'];
	}

	public function getBancoTarjeta(){
		return $this->registro['banco_tarjeta'];
	}

	public function getNumeroTarjeta(){
		return $this->registro['numero_tarjeta'];
	}

	public function getEmailPaypal(){
		return $this->registro['email_paypal'];
	}
	public function getFecha(){
		return date('d/m/Y g:i A', strtotime($this->registro['creado']));
	}

	public function getAlias(){

		if($this->registro['nombre_usuario'] && $this->registro['apellido_usuario']){
			return _safe($this->registro['nombre_usuario'].' '.$this->registro['apellido_usuario']);
		}else{
			return _safe($this->registro['username']);
		}

	}

	private function getHeader(){
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

	$title_tag = '<label class="cert-date mr20">#'.$this->registro['solicitud'].'</label>'.$status_tag.'<label class="cert-date">'.$this->getFecha().'</label><span class="pull-right">Solicitud enviada por <a class="mr20" href="'.HOST.'/socio/'.$this->getUsername().'" target="_blank">'.$this->getAlias().'</a>'.$form.'</span>';
		if(!empty($this->registro['comentario'])){
			$html = 
			'<div class="page-title">'.$title_tag.'</div>
			<label>Comentario de Travel Points para el solicitante</label>
			<p>'.nl2br(_safe($this->registro['comentario'])).'</p>';
		}else{
			$html = $title_tag;
		}
		return $html;

}

	public function Mostrar(){

			if($this->getCondicion() != 1){?>

		<form id="formulariosolicitud" method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" enctype="multipart/form-data">
		<div class="background-white p30 mb30">
				<?php echo $this->getHeader(); ?>
		</div>
        <div class="background-white p30 mb50">
         <h3 class="page-title">Informaci&oacute;n de la solicitud del Referidor... </h3>
         <div class="row">

          <div class="col-lg-8 d-flex">
        
           <div class="form-group flex" >
            <label for="business-name">Nombre del Hotel:<span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
            <div class="input-hotel">
             <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-hotel"></i></span>

            <input class ="hotel form-control" type="text" id="business-name" name="nombre" value="<?php echo $this->getNombreHotel();?>" placeholder="Nombre del hotel" readonly/>
            </div>
            </div>
            
           </div><!-- /.form-group -->

            <div class="form-group">
            <label for="website">Sitio web del hotel</label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-globe"></i></span>
             <input class="sitioweb form-control" type="text" id="website" name="website" placeholder="Sitio web del hotel" value="<?php echo $this->getSitioWeb(); ?>" readonly>
            </div><!-- /.input-group -->
           
           </div><!-- /.form-group -->

           <div class="form-group">
            <label for="address">Direcci&oacute;n del hotel <span class="required"></span></label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-map-o"></i></span>
             <input class="direccion form-control" type="text" id="address" name="direccion" value="<?php echo $this->getDireccion(); ?>" placeholder="Direcci&oacute;n del hotel" readonly >
            </div><!-- /.input-group -->
           
           </div><!-- /.form-group -->
            
          </div><!-- /.col-* -->
          
          <div class="col-lg-4">
            <div class="row">

            	<div class="form-group">
              		<label for="country-select">Codigo IATA <span class="required"></span></label>
              		<select name="iata" class="form-control" readonly>
              			<?php echo $this->getIata(); ?>
              		</select>
              </div><!-- /.form-group -->

              <div class="form-group">
              <label for="country-select">Pa&iacute;s <span class="required"></span></label>
              <input  type="text" class="pais form-control" value="<?php echo $this->getPais(); ?>" id="country-select" placeholder="Pais" name="pais" data-size="10" readonly>
               
              </input>
              </div><!-- /.form-group -->

               <div class="form-group">
                <label for="state-select">Estado <span class="required"></span></label>
                <input  type="text" class="estado form-control" id="state-select" value="<?php echo $this->getEstado(); ?>" placeholder="Estado" name="estado" data-size="10"  readonly>
                
                </input>
               </div><!-- /.form-group -->
               <div class="form-group">
                <label for="city-select">Ciudad <span class="required"></span></label>
                <input type="text" class="ciudad form-control" id="city-select" value="<?php echo $this->getCiudad(); ?>" placeholder="Ciudad" name="ciudad"  data-size="10" readonly>
                 
                </input>
                
                </div><!-- /.form-group -->
            </div><!-- /.form-group -->
           </div>
          </div>
           <div class="col-sm-12">
         
          </div><!-- /.col-* -->

         </div><!-- /.row -->
         
        
         
        </div><!-- /.box -->
       
        
        
        <div class="background-white p30 mb50">
         <h3 class="page-title">Tus Datos de contacto.</h3>
         
         <div class="row">
         
         
          <div class="col-lg-6">

          	<div class="form-group" data-toggle="tooltip" title="Tu nombre">
               <label for="phone">Nombre:<span class="required">*</span></label>
               <div class="input-group">
                 <span class="input-group-addon"><i class="fa fa-file"></i></span>
                 <input class="form-control" type="text" id="nombre" name="nombre" value="<?php echo $this->getNombre();?>" placeholder="Nombre" required>
               </div>
               
             
            </div>
            <div class="form-group" data-toggle="tooltip" title="Tu Apellido">
               <label for="phone">Apellido:<span class="required">*</span></label>
               <div class="input-group">
                 <span class="input-group-addon"><i class="fa fa-file"></i></span>
                 <input class="form-control" type="text"  id="apellido" name="apellido" value="<?php echo $this->getApellido();?>" placeholder="Apellido" required>
               </div>
               
             
            </div>
         
         </div>
          <div class="col-lg-6">

				<div class="form-group" data-toggle="tooltip" title="El número de teléfono fijo ejemp:+584128505504, 14128505504">
					<label for="phone">T&eacute;lefono fijo <span class="required"></span></label>
					<div class="input-group">
					<span class="input-group-addon"><i class="fa fa-phone-square"></i></span>
					<input class="form-control" type="text" pattern="[+][0-9]{12,15}[+]?" id="phone" name="telefonofijo" value="<?php echo $this->getTelefonofijo();?>" placeholder="N&uacute;mero de t&eacute;lefono fijo">
					</div><!-- /.input-group -->
					
				</div><!-- /.form-group -->

          <div class="form-group" data-toggle="tooltip" title="El número de teléfono movil ejemp: +584128505504, 14128505504">
            <label for="phone">T&eacute;lefono novil <span class="required">*</span><i class="fa fa-question-circle"></i></label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-mobile-phone"></i></span>
             <input class="form-control" type="text" id="movil"  pattern="[+][0-9]{11,15}[+]?" name="telefonomovil" value="<?php echo $this->gettelefonomovil();?>" placeholder="N&uacute;mero de t&eacute;lefono movil" required>
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

		  

         <input type="hidden" name="id" value="<?php echo $this->registro['solicitud']; ?>">
         <input type="hidden" name="perfil" value="referidor">
		<div class="center">
						<button class="btn btn-success mr20" id="aceptarsolicitud" type="submit" name="accept_request">Aceptar solicitud</button>
						<!-- <button class="btn btn-warning mr20" id="corregirsolicitud" type="submit" name="check_request">Regresar a correcci&oacute;n</button> -->
						<button class="btn btn-danger" id="rechazarsolicitud" type="submit" name="reject_request">Rechazar solicitud</button>
		</div>

       
       </form>

		<?php  }else{?>
			<form id="formulariosolicitud" method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" enctype="multipart/form-data">
		<div class="background-white p30 mb30">
				<?php echo $this->getHeader(); ?>
		</div>
        <div class="background-white p30 mb50">
         <h3 class="page-title">Informaci&oacute;n de la solicitud del Referidor... </h3>
         <div class="row">

          <div class="col-lg-8 d-flex">
        
           <div class="form-group flex" >
            <label for="business-name">Nombre del Hotel:<span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
            <div class="input-hotel">
             <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-hotel"></i></span>

            <input class ="hotel form-control" type="text" id="business-name" name="nombre" value="<?php echo $this->getNombreHotel();?>" placeholder="Nombre del hotel" readonly/>
            </div>
            </div>
            
           </div><!-- /.form-group -->

            <div class="form-group">
            <label for="website">Sitio web del hotel</label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-globe"></i></span>
             <input class="sitioweb form-control" type="text" id="website" name="website" placeholder="Sitio web del hotel" value="<?php echo $this->getSitioWeb(); ?>" readonly>
            </div><!-- /.input-group -->
           
           </div><!-- /.form-group -->

           <div class="form-group">
            <label for="address">Direcci&oacute;n del hotel <span class="required"></span></label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-map-o"></i></span>
             <input class="direccion form-control" type="text" id="address" name="direccion" value="<?php echo $this->getDireccion(); ?>" placeholder="Direcci&oacute;n del hotel" readonly >
            </div><!-- /.input-group -->
           
           </div><!-- /.form-group -->
            
          </div><!-- /.col-* -->
          
          <div class="col-lg-4">
            <div class="row">
              <div class="form-group">
              <label for="country-select">Pa&iacute;s <span class="required"></span></label>
              <input  type="text" class="pais form-control" value="<?php echo $this->getPais(); ?>" id="country-select" placeholder="Pais" name="pais" data-size="10" readonly>
               
              </input>
              </div><!-- /.form-group -->

               <div class="form-group">
                <label for="state-select">Estado <span class="required"></span></label>
                <input  type="text" class="estado form-control" id="state-select" value="<?php echo $this->getEstado(); ?>" placeholder="Estado" name="estado" data-size="10"  readonly>
                
                </input>
               </div><!-- /.form-group -->
               <div class="form-group">
                <label for="city-select">Ciudad <span class="required"></span></label>
                <input type="text" class="ciudad form-control" id="city-select" value="<?php echo $this->getCiudad(); ?>" placeholder="Ciudad" name="ciudad"  data-size="10" readonly>
                 
                </input>
                
                </div><!-- /.form-group -->
            </div><!-- /.form-group -->
           </div>
          </div>
           <div class="col-sm-12">
         
          </div><!-- /.col-* -->

         </div><!-- /.row -->
         
        
         
        </div><!-- /.box -->
       
        
        <div class="background-white p30 mb30">
         <h3 class="page-title">Datos para el pago de comisiones</h3>
         
        
         <div class="row">

          <div class="col-lg-6 col-sm-4">
          <h5 class="page-title">Transferencia Bancaria</h5>
           <div class="form-group" >
            <label for="nombre">Nombre del banco<span class="required">*</span></label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-bank"></i></span>
             <input class="form-control" type="text"  pattern="[a-zA-z]+" id="nombre_banco" name="nombre_banco" value="<?php echo $this->getBanco();?>" placeholder="Nombre del banco" readonly >
            </div><!-- /.input-group -->
            
           </div><!-- /.form-group -->

           <div class="form-group">
            <label for="cuenta">Cuenta<span class="required">*</span></label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
             <input class="form-control" type="text" pattern="[0-9a-zA-z]+" id="cuenta" name="cuenta" value="<?php echo $this->getCuenta();?>" placeholder="Cuenta." readonly >
            </div><!-- /.input-group -->
          
           </div><!-- /.form-group -->

           <div class="form-group" data-toggle="tooltip" title="Solo se permiten digitos númericos, correspondientes a su clabe.">
            <label for="clabe">Clabe<span class="required">*</span><i class="fa fa-question-circle"></i></label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
             <input class="form-control" type="text" maxlength="18" id="clabe" pattern="[0-9]{18}" name="clabe" value="<?php echo $this->getClabe();?>" placeholder="Clabe" readonly >
            </div><!-- /.input-group -->
            
           </div><!-- /.form-group -->

           <div class="form-group" data-toggle="tooltip" title="Una serie alfanuméricas de 8 u 11 digitos, que sirve para identificar al banco receptor cuando se realiza una transferencia">
            <label for="swift">Swift / Bic<span class="required">*</span><i class="fa fa-question-circle"></i></label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
             <input class="form-control" type="text" id="swift" maxlength="11" pattern="[A-Za-z0-9]{8,11}" name="swift" value="<?php echo $this->getSwift();?>" placeholder="Swift" readonly >
            </div><!-- /.input-group -->
            
           </div><!-- /.form-group -->

          </div><!-- /.col-* -->



          <div class="col-lg-6 col-sm-4">
           <h5 class="page-title">Deposito a tarjeta</h5>
           <div class="form-group">
            <label for="nombre">Nombre del banco<span class="required">*</span></label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-bank"></i></span>
             <input class="form-control" type="text" pattern="[a-zA-z]*" id="bancotarjeta" name="bancotarjeta" value="<?php echo $this->getBancoTarjeta();?>" placeholder="Nombre del banco" readonly >
            </div><!-- /.input-group -->
            
           </div><!-- /.form-group -->
           <div class="form-group" data-toggle="tooltip" title="Número de la targeta de Credito, conlleva 16 digitos solo numéricos.">
            <label for="nombre">N&uacute;mero de tarjeta<span class="required">*</span><i class="fa fa-question-circle"></i></label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-cc"></i></span>
             <input class="form-control" type="text" pattern="[0-9]{16}" maxlength="16" minlength="16" id="numero_targeta" name="numerotarjeta" value="<?php echo $this->getNumeroTarjeta();?>" placeholder="N&uacute;mero de Tarjeta" readonly>
            </div><!-- /.input-group -->
           
           </div><!-- /.form-group -->
        
          
            <h5 class="page-title">Transferencia PayPal</h5>
           <div class="form-group">
            <label for="nombre">Email de Paypal<span class="required">*</span></label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-cc-paypal"></i></span>
             <input class="form-control" type="email" id="email_paypal" name="email_paypal" value="<?php echo $this->getEmailPaypal();?>" placeholder="Nombre del banco" readonly >
            </div><!-- /.input-group -->
            
           </div><!-- /.form-group -->
          </div>
          
         </div>
        
        <div class="background-white p30 mb50">
         <h3 class="page-title">Tus Datos de contacto.</h3>
          <small class="">Ya tenemos tus datos personales solo confirmanos tus números de contacto.</small>
         <div class="row">
         
         
           <div class="col-lg-6">
          <div class="form-group" data-toggle="tooltip" title="El número de teléfono fijo ejemp:+584128505504, 14128505504">
            <label for="phone">T&eacute;lefono fijo <span class="required">*</span></label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-phone-square"></i></span>
             <input class="form-control" type="text" pattern="[+][0-9]{12,15}[+]?" id="phone" name="telefonofijo" value="<?php echo $this->getTelefonofijo();?>" placeholder="N&uacute;mero de t&eacute;lefono fijo" readonly >
            </div><!-- /.input-group -->
            
           </div><!-- /.form-group -->
          </div>
          <div class="col-lg-6">
          <div class="form-group" data-toggle="tooltip" title="El número de teléfono movil ejemp: +584128505504, 14128505504">
            <label for="phone">T&eacute;lefono novil <span class="required">*</span><i class="fa fa-question-circle"></i></label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-mobile-phone"></i></span>
             <input class="form-control" type="text" id="movil"  pattern="[+][0-9]{11,15}[+]?" name="telefonomovil" value="<?php echo $this->gettelefonomovil();?>" placeholder="N&uacute;mero de t&eacute;lefono movil" readonly>
            </div><!-- /.input-group -->
           
           </div><!-- /.form-group -->
          </div>
          </div>
        </div>

       
       </form>
		<?php  }


}

	public function aceptarsolicitud(array $post){

		// $this->setBanco($post['nombre_banco']);
		// $this->setClabe($post['clabe']);
		// $this->setCuenta($post['cuenta']);
		// $this->setSwift($post['swift']);
		// $this->setBancoTarjeta($post['bancotarjeta']);
		// $this->setNumeroTarjeta($post['numerotarjeta']);
		// $this->setEmailPaypal($post['email_paypal']);
		$this->setNombre($post['nombre']);
		$this->setApellido($post['apellido']);
		$this->setTelefonofijo($post['telefonofijo']);
		$this->setTelefonomovil($post['telefonomovil']);
		$this->setComentario($post['comentario']);

		if($this->con->inTransaction()){
			$this->con->rollBack();
		}

		$this->con->beginTransaction();
	
		$query1 = "update referidor set nombre = :nombre, apellido =:apellido, telefonomovil = :telefonomovil, telefonofijo=:telefonofijo, aprobada=:aprobada where id = :id_referidor";

			try {
				$stm1 = $this->con->prepare($query1);
				$stm1->execute(array(':nombre'=>$this->registro['nombre'],
									':apellido'=>$this->registro['apellido'],
									':telefonomovil' =>$this->getTelefonomovil(),
									':telefonofijo' =>$this->getTelefonofijo(),
									':aprobada' => 1,
									':id_referidor' => $this->registro['id_referidor']));

			} catch (PDOException $ex) {
					$this->registrarerror(__METHOD__,__LINE__,$e->getMessage());
					$this->con->rollBack();
					return false;
			}

			$query2 = "update solicitudreferidor set condicion = :condicion, comentario =:comentario where id =:solicitud";


			try {

				$stm2 = $this->con->prepare($query2);
				$stm2->execute(array(':condicion' =>1, 
										':comentario' =>$this->getComentario(),
										':solicitud' =>$this->registro['solicitud']));

				$this->con->commit();

				//SE MANDA LA NOTIFICACION AL USUARIO
				
				$header = 'Tu solicitud de perfil ha sido aceptada por Travel Points ';
				$headeringles = 'Your profile request has been accepted by Travel Points';

				$link = 'Puedes ver tu perfil aquí: <a style="outline:none; color:#0082b7; text-decoration:none;" href="'.HOST.'/referidor/">'.HOST.'/Hotel/"></a>.';

				$linkingles = 'You can see your profile here: <a style="outline:none; color:#0082b7; text-decoration:none;" href="'.HOST.'/referidor/">'.HOST.'/Hotel/"></a>.';
				
				$body_alt = 'Tu solicitud de perfil ha sido aprobada por Travel Points. Puedes entrar al panel desde aquí: '.HOST.'/referidor/';
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
					$mail->addAddress($this->getEmailSolicitante());
					$mail->AddCC($this->getEmailSolicitante());
											
											// Hacerlo formato HTML
											$mail->isHTML(true);
											// Formato del correo
											$mail->Subject = 'Tu solicitud del perfil de referidor ha sido aceptada. | Your profile request has been accepted.';
											$mail->Body    = $this->email_template($header,$headeringles, $link, $linkingles);
											$mail->AltBody = $body_alt;
											// Enviar
											if(!$mail->send()){
												$_SESSION['notificacion']['info'] = 'El correo de aviso no se pudo enviar debido a una falla en el servidor.';
											}

											$_SESSION['notificacion']['success'] = 'Solicitud aceptada exitosamente. El perfil ha sido creado.';
											
											return;
				
			} catch (PDOException $ex1) {
				$this->registrarerror(__METHOD__,__LINE__,$ex1->getMessage());
				$this->con->rollBack();
				return false;
			}

	}

	private function email_template($header,$headeringles, $link,$linkingles){
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
											<img alt="Travel Points" src="'.HOST.'/assets/img/LOGOV.png" style="padding-bottom: 0; display: inline !important;width:250px; height=auto;">
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

	public function getModal(){?>

		<!-- Modal para adjudicar referidor -->
		<div class="modal fade aceptar modales" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel"><label class="cert-date mr20">Solicitud # <?php echo $this->registro['solicitud'];?> <label class="cert-date form"><?php echo $this->getFecha();?></label></label></h5>
						<h5 class="modal-title"><label class="cert-date form">Hotel <?php echo $this->getNombreHotel(); ?></label></h5>
						<small class="iata cert-date">Codigo Iata <?php echo $this->registro['codigo']; ?></small>
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
													<button type="button" name="generarcodigo" data-iata="<?php echo _safe($this->registro['codigo']); ?>" data-hotel="<?php echo $this->getNombreHotel(); ?>" class="btn btn-outline-secondary generarcodigo">Generar</button>
												</div>
											</div>
										</div>


										<div class="comision col-lg-7">
											<div class="form-group">
												<label for="comision">Comisión a adjudicar.</label>
												
												<input id="ex8" type="text" id="comision" data-slider-id="ex1Slider" data-slider-min="0" data-slider-max="8" data-slider-step="1" data-slider-value="<?php echo $this->registro['comision'];?>">
												<span class="form" id="val-slider"><?php echo $this->getComision();?></span>
											</div>
										</div>
									</div>
								</section>
							</form>		
					</div>
						
					<div class="modal-footer">
						
						<button  style="margin-left: auto;" type="button" data-solicitud="<?php echo $this->registro['solicitud']; ?>" data-perfil="Referidor" data-path="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" name="adjudicar" class="adjudicar btn btn-success">Registrar</button>
						<button  type="button" class="cerrar btn btn-secondary">Cerrar</button>
						
					</div>
				</div>
			</div>
		</div>
	
	<?php }



	public function adjudicar($comision,$codigohotel = null){



		$this->setComision($comision);
		
		if($this->con->inTransaction()){
			$this->con->rollBack();
		}

		$this->con->beginTransaction();
	

		$query  = "update referidor set comision =:comision,codigo_hotel=:codigo where id =:id_referidor";
		
		try {
			$stm = $this->con->prepare($query);
			$stm->execute(array(':comision' => $this->registro['comision'],':codigo'=>$codigohotel,':id_referidor' => $this->registro['id_referidor']));
	
			
		} catch (PDOException $ex) {
			$this->registrarerror(__METHOD__,__LINE__,$ex->getMessage());
			$this->con->rollback();
			return false;
		}


		$sql = "SELECT * from hotel where codigo = :codigo";

		try {

			
			
			$stm = $this->con->prepare($sql);

			$stm->execute(array(':codigo'=>$codigohotel));

		
		} catch (PDOException $e) {
			$this->registrarerror(__METHOD__,__LINE__,$e->getMessage());
			$this->con->rollback();
			return false;
		}
		
		if($stm->rowCount() == 0){
			$sql1 = "INSERT INTO hotel(nombre,codigo,direccion,sitio_web,id_ciudad,codigo_postal,comision,aprobada,id_iata,id_estado)
							values(:nombre,:codigo,:direccion,:sitioweb,:ciudad,:codigopostal,:comision,:aprobada,:iata,:estado)";
			
			try {
				
				$stm = $this->con->prepare($sql1);

				$datos = array(
					':nombre'       =>	$this->getNombreHotel(),
					':codigo'       =>	$codigohotel,
					':direccion'    =>	$this->registro['direccion'],
					':sitioweb'     =>	$this->getSitioWeb(),
					':ciudad'       =>	$this->registro['id_ciudad'],
					':codigopostal' =>	$this->registro['codigopostal'],
					':comision'     =>	0,
					':aprobada'     =>  1,
					':iata'         =>	$this->registro['id_iata'],
					':estado'       =>	$this->registro['id_estado']
				);

				

				$stm->execute($datos);

				$this->con->commit();

			} catch (PDOException $exx) {
				$this->registrarerror(__METHOD__,__LINE__,$exx->getMessage());
				$this->con->rollback();
				return false;
			}
			
		}
		return;
	}

	private function setComision($comision){
		$this->registro['comision'] = $comision;
	}
	private function setBanco($banco){
		$this->registro['banco'] = $banco;
	}

	private function setCuenta($cuenta){
		$this->registro['cuenta'] = $cuenta;
	}

	private function setClabe($clabe){
		$this->registro['clabe'] = $clabe;
	}

	private function setSwift($swift){
		$this->registro['swift'] = $swift;
	}

	private function setBancoTarjeta($banco){
		$this->registro['banco_tarjeta'] = $banco;
	}

	private function setNumeroTarjeta($numero){
		$this->registro['numero_tarjeta'] = $numero;
	}

	private function setEmailPaypal($email){
		$this->registro['email'] = $email;
	}

	private function setTelefonofijo($telefono){
		$this->registro['telefonofijo'] = $telefono;
	}

	private function setTelefonomovil($movil){

		$this->registro['telefonomovil'] = $movil;

	}

	private function setComentario($comentario){
		$this->registro['comentario'] = $comentario;
	}

	private function setNombre($string){
		$this->registro['nombre']= $string;
	}

	private function setApellido($string){
		$this->registro['apellido']= $string;
	}

	private function registrarerror($method, $line, $error){
		file_put_contents(ROOT.'\assets\error_logs\solicitudperfilerror.txt', '['.date('d/M/Y g:i:s A').' | Method: '.$method.' | Line: '.$line.'] '.$error.PHP_EOL,FILE_APPEND);
		$this->error['error'] = 'Parece que tenemos errores técnicos, disculpa las molestias. Intentalo más tarde.';
		return;
	}

}
 ?>