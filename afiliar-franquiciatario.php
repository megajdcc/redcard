<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

use Franquiciatario\models\AfiliarFranquiciatario;
use assets\libs\includes as Includes;

if(!isset($_SESSION['user'])){
 $login = new assets\libs\user_login($con);
 if($_SERVER["REQUEST_METHOD"] == "POST"){
  $login->set_data($_POST);
 }
}else{
 $affiliate = new AfiliarFranquiciatario($con);
 if($_SERVER['REQUEST_METHOD'] == 'POST'){
  if(isset($_POST['send'])){
   $affiliate->set_data($_POST);
  }
 }
}

$includes = new Includes($con);
$properties['title'] = 'Afiliar Franquiciatario | Travel Points';
$properties['description'] = '';

echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_main_navbar(); ?>
 <div class="main">
  <div class="main-inner">
   <div class="container">
    <?php echo $con->get_notify();?>
<?php if(!isset($_SESSION['user'])){ ?>
    <div class="row">
     <div class="col-sm-7 col-md-8 mb50">
      <div class="page-title">
       <h1>¡Afiliate como Franquiciatario!</h1>
       <p>Env&iacute;anos una solicitud para se parte de nuestro selecto Grupo de Franquiciatario.</p>
      </div>
      <p>Solo los socios pueden Solicitar este perfil. <a href="<?php echo HOST;?>/hazte-socio">Hazte socio</a> o inicia sesi&oacute;n.</p>
     </div>
     <div class="col-sm-5 col-md-4">
     <?php echo $login->get_notification(); ?>
      <div class="page-title">
       <h2 class="mb0">Iniciar sesi&oacute;n</h2>
      </div><!-- /.page-title -->
      <?php echo $login->get_login_error(); ?>
      <form method="post" action="<?php echo _safe(HOST.'/login');?>">
       <div class="form-group">
        <label for="email">Correo electr&oacute;nico</label>
        <input type="text" class="form-control" name="email" id="email" value="<?php echo $login->get_email();?>" placeholder="Correo electr&oacute;nico" required />
        <?php echo $login->get_email_error();?>
       </div><!-- /.form-group -->
       <div class="form-group">
        <label for="password">Contrase&ntilde;a</label>
        <input type="password" class="form-control" name="password" id="password" placeholder="Contrase&ntilde;a" required />
        <?php echo $login->get_password_error();?>
       </div><!-- /.form-group -->
       <button type="submit" class="btn btn-primary pull-right">¡Entrar!</button>
      </form>
     </div>
    </div>
<?php }else{ ?>
    <div class="row">
     <div class="col-sm-12">
      <div class="content">
       <?php echo $affiliate->get_notification();?>
       <div class="page-title">
        <h1>¡Afiliate como Franquiciatario!</h1>
        <p>Env&iacute;anos una solicitud para que seas parte de nuestro selecto grupo de Franquiciatarios.</p>
       </div>
       <form method="post" action="<?php echo _safe(HOST.'/afiliar-franquiciatario');?>" enctype="multipart/form-data">
        <div class="background-white p30 mb50">
         <h3 class="page-title">Informaci&oacute;n del Hotel</h3>
         <div class="row">

          <div class="col-lg-8 d-flex">
        
           <div class="form-group flex" >
            <label for="business-name">Nombre del Hotel:<span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
            <div class="input-hotel">
              <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-hotel"></i></span>

            <input class ="hotel form-control" type="text" id="business-name" name="nombre" value="<?php echo $affiliate->getHotel();?>" placeholder="Nombre del hotel" required readonly/>
            </div>
              <button type ="button"  data-toggle="modal" data-target=".capturarhotel"  data-placement="top" name="buscarhotel" class="buscar form-control"><i class="fa fa-search"></i>Buscar</button>
            </div>
            
           </div><!-- /.form-group -->

            <div class="form-group">
            <label for="website">Sitio web del hotel</label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-globe"></i></span>
             <input class="sitioweb form-control" type="text" id="website" name="website" placeholder="Sitio web del hotel" readonly>
            </div><!-- /.input-group -->
           
           </div><!-- /.form-group -->

           <div class="form-group">
            <label for="address">Direcci&oacute;n del hotel <span class="required"></span></label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-map-o"></i></span>
             <input class="direccion form-control" type="text" id="address" name="direccion" value="" placeholder="Direcci&oacute;n del hotel" readonly >
            </div><!-- /.input-group -->
           
           </div><!-- /.form-group -->
            
          </div><!-- /.col-* -->
          
          <div class="col-lg-4">
            <div class="row">
              <div class="form-group">
              <label for="country-select">Pa&iacute;s <span class="required"></span></label>
              <input  type="text" class="pais form-control" id="country-select" placeholder="Pais" name="pais" data-size="10" readonly>
               
              </input>
              </div><!-- /.form-group -->

               <div class="form-group">
                <label for="state-select">Estado <span class="required"></span></label>
                <input  type="text" class="estado form-control" id="state-select" placeholder="Estado" name="estado" data-size="10"  readonly>
                
                </input>
               </div><!-- /.form-group -->
               <div class="form-group">
                <label for="city-select">Ciudad <span class="required"></span></label>
                <input type="text" class="ciudad form-control" id="city-select" placeholder="Ciudad" name="ciudad"  data-size="10" readonly>
                 
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
             <input class="form-control" type="text"  pattern="[a-zA-z]+" id="nombre_banco" name="nombre_banco" value="<?php echo $affiliate->getBanco();?>" placeholder="Nombre del banco" required >
            </div><!-- /.input-group -->
            <?php echo $affiliate->getBancoError();?>
           </div><!-- /.form-group -->

           <div class="form-group">
            <label for="cuenta">Cuenta<span class="required">*</span></label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
             <input class="form-control" type="text" pattern="[0-9a-zA-z]+" id="cuenta" name="cuenta" value="<?php echo $affiliate->getCuenta();?>" placeholder="Cuenta." required >
            </div><!-- /.input-group -->
            <?php echo $affiliate->getCuentaError();?>
           </div><!-- /.form-group -->

           <div class="form-group" data-toggle="tooltip" title="Solo se permiten digitos númericos, correspondientes a su clabe.">
            <label for="clabe">Clabe<span class="required">*</span><i class="fa fa-question-circle"></i></label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
             <input class="form-control" type="text" maxlength="18" id="clabe" pattern="[0-9]{18}" name="clabe" value="<?php echo $affiliate->getClabe();?>" placeholder="Clabe" required >
            </div><!-- /.input-group -->
            <?php?>
           </div><!-- /.form-group -->

           <div class="form-group" data-toggle="tooltip" title="Una serie alfanuméricas de 8 u 11 digitos, que sirve para identificar al banco receptor cuando se realiza una transferencia">
            <label for="swift">Swift / Bic<span class="required">*</span><i class="fa fa-question-circle"></i></label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-wpforms"></i></span>
             <input class="form-control" type="text" id="swift" maxlength="11" pattern="[A-Za-z0-9]{8,11}" name="swift" value="<?php echo $affiliate->getSwift() ?>" placeholder="Swift" required >
            </div>
            <?php echo $affiliate->getSwiftError();?>
           </div>

          </div>



          <div class="col-lg-6 col-sm-4">
           <h5 class="page-title">Deposito a tarjeta</h5>
           <div class="form-group">
            <label for="nombre">Nombre del banco<span class="required">*</span></label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-bank"></i></span>
             <input class="form-control" type="text" pattern="[a-zA-z]*" id="bancotarjeta" name="bancotarjeta" value="<?php echo $affiliate->getBancoNombreTarjeta();?>" placeholder="Nombre del banco" required >
            </div>
            <?php echo $affiliate->getNombreBancoTarjetaError();?>
           </div>
           <div class="form-group" data-toggle="tooltip" title="Número de la targeta de Credito, conlleva 16 digitos solo numéricos.">
            <label for="nombre">N&uacute;mero de tarjeta<span class="required">*</span><i class="fa fa-question-circle"></i></label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-cc"></i></span>
             <input class="form-control" type="text" pattern="[0-9]{16}" maxlength="16" minlength="16" id="numero_targeta" name="numerotarjeta" value="<?php echo $affiliate->getTarjeta();?>" placeholder="N&uacute;mero de Tarjeta" required>
            </div>
            <?php echo $affiliate->getNumeroTarjetaError();?>
           </div>
        
          
            <h5 class="page-title">Transferencia PayPal</h5>
           <div class="form-group">
            <label for="nombre">Email de Paypal<span class="required">*</span></label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-cc-paypal"></i></span>
             <input class="form-control" type="email" id="email_paypal" name="email_paypal" value="<?php echo $affiliate->getEmailPaypal();?>" placeholder="Nombre del banco" required >
            </div>
            <?php echo $affiliate->getEmailPaypalError();?>
           </div>
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
             <input class="form-control" type="text" pattern="[+][0-9]{12,15}[+]?" id="phone" name="telefonofijo" value="<?php echo $affiliate->getTelefono();?>" placeholder="N&uacute;mero de t&eacute;lefono fijo" required >
            </div>

            <?php echo $affiliate->getTelefonoError();?>
           </div>
          </div>
          <div class="col-lg-6">
          <div class="form-group" data-toggle="tooltip" title="El número de teléfono movil ejemp: +584128505504, 14128505504">
            <label for="phone">T&eacute;lefono novil <span class="required">*</span><i class="fa fa-question-circle"></i></label>
            <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-mobile-phone"></i></span>
             <input class="form-control" type="text" id="movil"  pattern="[+][0-9]{11,15}[+]?" name=" telefonomovil" value="<?php echo $affiliate->getMovil();?>" placeholder="N&uacute;mero de t&eacute;lefono movil" required>
            </div>
            <?php echo $affiliate->getMovilError();?>
           </div>
          </div>
          </div>
        </div>


        <div class="row">
         <div class="col-xs-6">
          <p>Los campos marcados son obligatorios <span class="required">*</span></p>
         </div>
         <div class="col-xs-6 right">
          <button class="enviar btn btn-success btn-xl" type="submit" value="" name="send"><i class="fa fa-paper-plane"></i>Enviar mi solicitud</button>
         </div>
        </div>
       </form>
      </div><!-- /.content -->
     </div><!-- /.col-* -->
    </div><!-- /.row -->

<?php } ?>


   </div><!-- /.container -->
  </div><!-- /.main-inner -->
 </div><!-- /.main -->
 <?php echo $footer = $includes->get_main_footer(); ?>

  <div class="modal fade capturarhotel " id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Hoteles</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <article class="cat">
      <table  id="example" class="display" cellspacing="0" width="100%" >
        <thead>
        <tr>
        <th>Codigo</th>
        <th>Nombre</th>
        <th>Direccion</th>
        <th>Sitio Web</th>
      
        </tr>
        </thead>
        
        <tbody >
          
            <?php echo $affiliate->getHoteles(); ?>
          
        </tbody>
      </table>
      </article> 

  
       </div>
       
      <div class="modal-footer">

        <button  style="margin-left: auto;" type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
   
      </div>
    </div>
  </div>
</div>
  <script>  

  
  $(document).ready(function() {
       var t = $('#example').DataTable( {
      'paging':         false,
      'scrollY':        '150px',
        'scrollCollapse': true,
         'language': {
                        'lengthMenu': 'Mostar _MENU_ registros por pagina',
                        'info': '',
                        'infoEmpty': 'No se ha encontrado ningun hotel',
                        'infoFiltered': '(filtrada de _MAX_ registros)',
                        'search': 'Buscar:',
                       'paginate': {
                            'next':       'Siguiente',
                            'previous':   'Anterior'
                        },
                    },
        'columnDefs': [ {
            'searchable': true,
            'orderable': true,
            'targets': 0
        } ],
        'order': [[ 0, 'asc' ]]
    });


  $('.capt').click(function() {
        var hotelid = $('.capt').attr('data-hotel');  
        var codigo = $('.codigohotel').attr('data-codigo');
        var nombre = $('.nombrehotel').attr('data-nombre');
        var direccion = $('.direccionhotel').attr('data-direccion');
        var sitio = $('.sitiowebhotel').attr('data-sitio');
        var estado = $('.direccionhotel').attr('data-estado');
        var ciudad = $('.direccionhotel').attr('data-ciudad');
        var pais = $('.direccionhotel').attr('data-pais');

        $('.hotel').val(nombre);
        
        $('.direccion').val(direccion);
        $('.pais').val(pais);
        $('.estado').val(estado);
        $('.sitioweb').val(sitio);
        $('.ciudad').val(ciudad);

        $('.enviar').val(codigo);

        $('.modal').modal('hide');


  });

  });


      

</script>
