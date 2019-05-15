  <?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

use Referidor\models\AfiliarReferidor;
use assets\libs\includes as Includes;

if(!isset($_SESSION['user'])){
 $login = new assets\libs\user_login($con);
 if($_SERVER["REQUEST_METHOD"] == "POST"){
  $login->set_data($_POST);
 }
}else{
 $affiliate = new AfiliarReferidor($con);
 if($_SERVER['REQUEST_METHOD'] == 'POST'){
  if(isset($_POST['send'])){
   $affiliate->set_data($_POST);
  }
 }
}

$includes = new Includes($con);
$properties['title'] = 'Afiliar Referidor | Travel Points';
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
       <h1>¡Afiliate como Referidor!</h1>
       <p>Env&iacute;anos una solicitud para se parte de nuestro selecto Grupo de Referidores.</p>
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
        <h1>¡Afiliate como Referidor!</h1>
        <p>Env&iacute;anos una solicitud para que seas parte de nuestro selecto grupo de referidores.</p>
       </div>
       <form method="post" action="<?php echo _safe(HOST.'/afiliar-referidor');?>" enctype="multipart/form-data">
        <div class="background-white p30 mb50">
         

                  <h3 class="page-title">Informaci&oacute;n de hotel</h3>
                  <div class="row">

                    <div class="col-lg-8">
                
                      <div class="form-group" data-toggle="tooltip" title="Los clientes Huespedes de Travel Points pueden afiliarse desde su propio perfil...">
                        <label for="business-name">Nombre del hotel <span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>

                        <input class="form-control" type="text" id="business-name" name="nombrehotel" value="<?php echo $affiliate->getNombre();?>" placeholder="Nombre del hotel" required />
                        <?php echo $affiliate->getNombreError();?>
                      </div><!-- /.form-group -->
                    
                        </div><!-- /.col-* -->
                        
                       
                      <div class="col-lg-4">

                        <div class="row">
                        <div class="col-sm-6 col-md-12 form-group" data-toggle="tooltip" title="El codigo Iata es utilizado para ayudar a agilizar los procesos de transporte aereo y turistico.">
                          <label for="category">C&oacute;digo IATA <span class="required"></span><i class="fa fa-question-circle text-secondary"></i></label>

                          <div class="input-group input-iata">
                            <select class="form-control" id="iata" name="iata" title="Seleccionar c&oacute;digo IATA" data-live-search="true">
                            <option value="null" selected>Seleccione</option>
                              
                              <?php echo $affiliate->getIata();?>
                            </select>
                            <?php echo $affiliate->getIataError();?>
                            <button type="button" class="input-group-addon new-iata btn btn-secondary" name="new-iata" data-toggle="tooltip" title="Agrega tu codigo IATA" data-placement="bottom"><i class="fa fa-pencil"></i></button>
                          </div>
                          
                        </div>
                      </div>

                         
                      </div><!-- /.col-* -->
                      </div><!-- /.row -->
                      
                      <div class="row">
                        <div class="col-lg-8">
                           
                           <div class="form-group" data-toggle="tooltip" title="Si no tienes sitio web, deja el espacio en blanco.">
                            <label for="website">Sitio web del hotel <i class="fa fa-question-circle text-secondary"></i></label>
                            <div class="input-group">
                              <span class="input-group-addon"><i class="fa fa-globe"></i></span>
                              <input class="form-control" pattern="([--:\w?@%&+~#=]*\.[a-z]{2,4}\/{0,2})((?:[?&](?:\w+)=(?:\w+))+|[--:\w?@%&+~#=]+)?" type="text" id="website" name="website" value="<?php echo $affiliate->getSitioWeb();?>" placeholder="Sitio web del hotel">
                            </div><!-- /.input-group -->
                            <?php echo $affiliate->getWebsiteError();?>
                          </div>

                        </div>
                      </div>
                
                      <h3 class="page-title">Ubicaci&oacute;n del hotel</h3>
                      <div class="row">
                        <div class="col-lg-8">
                          <div class="form-group">
                            <label for="address">Direcci&oacute;n del hotel <span class="required">*</span></label>
                            <div class="input-group">
                              <span class="input-group-addon"><i class="fa fa-map-o"></i></span>
                              <input class="form-control" type="text" id="address" name="direccion" value="<?php echo $affiliate->getDireccion();?>" placeholder="Direcci&oacute;n del hotel" required >
                            </div><!-- /.input-group -->
                            <?php echo $affiliate->getDirecccionError();?>
                          </div><!-- /.form-group -->
                        </div><!-- /.col-* -->
                        <div class="col-lg-4">
                          <div class="form-group">
                            <label for="postal-code">C&oacute;digo postal  del hotel <span class="required"></span></label>
                            <div class="input-group">
                              <span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
                              <input class="form-control" type="text" id="postal-code" name="codigopostal" value="<?php echo $affiliate->getCodigoPostal();?>" placeholder="C&oacute;digo postal del hotel" >
                            </div><!-- /.input-group -->
                            <?php echo $affiliate->getCodigoPostalError();?>
                          </div><!-- /.form-group -->
                        </div><!-- /.col-* -->
                      </div><!-- /.row -->
                      <div class="row">
                        <div class="col-lg-4">
                          <div class="form-group">
                            <label for="country-select">Pa&iacute;s <span class="required">*</span></label>
                            <select class="form-control" id="country-select" name="pais" title="Selecciona un pa&iacute;s" data-size="10" data-live-search="true" required>
                              <?php echo $affiliate->get_countries();?>
                            </select>
                          </div><!-- /.form-group -->
                        </div><!-- /.col-* -->
                        <div class="col-lg-4">
                          <div class="form-group">
                            <label for="state-select">Estado <span class="required">*</span></label>
                            <select class="form-control" id="state-select" name="estado" title="Luego un estado" data-size="10" data-live-search="true" required>
                              <?php echo $affiliate->get_states();?>
                            </select>
                          </div><!-- /.form-group -->
                        </div><!-- /.col-* -->
                        <div class="col-lg-4">
                          <div class="form-group">
                            <label for="city-select">Ciudad <span class="required"></span></label>
                            <select class="form-control" id="city-select" name="ciudad" title="Luego una ciudad" data-size="10" data-live-search="true" >
                              <?php echo $affiliate->get_cities();?>
                            </select>
                            <?php echo $affiliate->getCiudadError();?>
                          </div><!-- /.form-group -->
                        </div><!-- /.col-* -->
                      </div><!-- /.row -->
                      <hr>
            </div>
                       
         
       
               
        <div class="background-white p30 mb30">
        
         <h3 class="page-title">Tus Datos de contacto.</h3>
      
         <div class="row">
         
         
           <div class="col-lg-6">
            <div class="form-group" data-toggle="tooltip" title="Tu nombre">
               <label for="phone">Nombre:<span class="required">*</span></label>
               <div class="input-group">
                 <span class="input-group-addon"><i class="fa fa-file"></i></span>
                 <input class="form-control" type="text" id="nombre" name="nombre" value="<?php echo $affiliate->getNombre();?>" placeholder="Nombre" required>
               </div>
               
               <?php echo $affiliate->getNombreError();?>
            </div>
            <div class="form-group" data-toggle="tooltip" title="Tu Apellido">
               <label for="phone">Apellido:<span class="required">*</span></label>
               <div class="input-group">
                 <span class="input-group-addon"><i class="fa fa-file"></i></span>
                 <input class="form-control" type="text"  id="apellido" name="apellido" value="<?php echo $affiliate->getApellido();?>" placeholder="Apellido" required>
               </div>
               
               <?php echo $affiliate->getApellidoError();?>
            </div>
         
          </div>
          <div class="col-lg-6">

            <div class="form-group" data-toggle="tooltip" title="El número de teléfono fijo ejemp:+584128505504, 14128505504">
               <label for="phone">T&eacute;lefono fijo <span class="required"></span></label>
               <div class="input-group">
                 <span class="input-group-addon"><i class="fa fa-phone-square"></i></span>
                 <input class="form-control" type="text" pattern="[+][0-9]{12,15}[+]?" id="phone" name="telefonofijo" value="<?php echo $affiliate->getTelefono();?>" placeholder="N&uacute;mero de t&eacute;lefono fijo">
               </div>
               
               <?php echo $affiliate->getTelefonoError();?>
            </div>

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

 <!-- Modal para adjudicar recibo de pago... -->
    <div class="modal fade " id="new-iata" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-dialog-centered">
          <form  action="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" method="post" accept-charset="utf-8" enctype="multipart/form-data">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Ingresar codigo IATA</h5>
          </div>

          <div class="modal-body">
<!--            <div class="alert alert-success" role="alert" id="alerta" style="display:none">
              Comisión actualizada. Si desea puede actualizar de nuevo.
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                 <span aria-hidden="true">&times;</span>
               </button>
            </div> -->


              <style>
                .acept-solicitud{
                  display: flex;
                  justify-content: center;
                  flex-direction: column;
                  width: 100%;
                }
                .botoneras{
                  width: 100%;
                  display: flex;
                  justify-content: center;
                }
              </style>
            
                <section class="col-xs-12 acept-solicitud container" >
                  <style>
                    .page-title{
                        margin:0px 0px 5px 0px !important;
                    }
                    .recibopago{
                      margin-bottom: 2rem;
                    }
                  </style>

                  <section class="iata" id="datoshotel">
                    <style>
                      .notification{
                        display: none !important;
                      }
                    </style>
                    <div class="notification alert alert-icon alert-dismissible alert-info" role="alert">
                      
                      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <i class="fa fa-times" aria-hidden="true"></i>
                      </button>
                      
                      <label class="notifi"></label>
                    </div>
                    <div class="row">

                          <div class="col-lg-6 d-flex">
                        
                             <div class="form-group flex" data-toggle="tooltip" title="Insertar codigo Iata." data-placement="bottom">

                                <label for="business-name">Codigo:<span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
                                <div class="input-hotel">
                                   <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-code"></i></span>
                                    <input class ="codigoiata form-control" type="text" id="business-name" name="codigoiata" value="" placeholder="Codigo iata" required/>
                                  </div>
                                </div>
                             </div>

                              <div class="form-group flex" data-toggle="tooltip" title="Nombre con el que quedará registrado su ciudad o territorio." data-placement="bottom" >
                                <label for="business-name">Aeropuerto:<span class="required">*</span> <i class="fa fa-question-circle text-secondary"></i></label>
                                <div class="input-hotel">
                                   <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-fighter-jet"></i></span>
                                    <input class ="aeropuerto form-control" type="text" id="business-name" name="aeropuerto" value="" placeholder="aeropuerto" required/>
                                  </div>
                                </div>
                             </div>

                       
                            
                          </div>

                           <div class="col-lg-6 d-flex">
                        
                  
                    
                      <div class="form-group">
                        <label for="country-select">Pa&iacute;s <span class="required">*</span></label>

                        <select class="form-control paisiata" id="country-select-affiliate" name="paisiata" title="Selecciona un pa&iacute;s" data-size="10" data-live-search="true" required>
                          <?php echo $affiliate->get_countries();?>
                        </select>

                      </div>
                    
                    
                      <div class="form-group">

                        <label for="state-select">Estado <span class="required">*</span></label>

                        <select class="form-control estadoiata"  id="state-select-affiliate" name="estadoiata" title="Luego un estado" data-size="10" data-live-search="true" required>
                          <?php echo $affiliate->get_states();?>
                        </select>

                      </div>                    
                    
                      <div class="form-group">

                        <label for="city-select">Ciudad <span class="required"></span></label>
                        <select class="form-control ciudadiata" id="city-select-affiliate"  name="ciudadiata" title="Luego una ciudad" data-size="10" data-live-search="true">
                          <?php echo $affiliate->get_cities();?>
                        </select>
                        <?php //echo $iata->getCiudadError();?>

                      </div>
                    </div>
                  
                          
                        
                         
                    </div>
                  </section>                  
                </section>

          <strong> Si no sabes cual es tu codigo Iata del aeropuerto mas cercano al hotel, Puedes buscarlo <a href="https://es.wikipedia.org/wiki/Anexo:Aeropuertos_seg%C3%BAn_el_c%C3%B3digo_IATA" target="_blank">Aqui.!</a> </strong>
                
          </div>
            
          <div class="modal-footer">
            
            <button style="margin-left: auto;" type="button" data-path="<?php echo _safe($_SERVER['REQUEST_URI']); ?>" name="registrar" class="actualizar btn btn-success">Registrar</button>
            <button  type="button" class="cerrarmodal btn btn-secondary" >Cerrar</button>
          </div>
        </form>

        </div>
      </div>
    </div>


<script>

  $('.actualizar').click(function(){

    var path = $(this).attr('data-path');
    var codigo     = $('.codigoiata').val();
    var aeropuerto = $('.aeropuerto').val();
    var id_estado  = $('select[name="estadoiata"]').val();
    var id_ciudad  = $('select[name="ciudadiata"]').val();    

    $.ajax({
      url: '/admin/controller/grafica.php',
      type: 'POST',
      dataType: 'JSON',
      data: {newiata: true,codigo:codigo,aeropuerto:aeropuerto,estado:id_estado,ciudad:id_ciudad},
    })
    .done(function(data) {
      
      if(data.datos.iataexiste){
        
        $('.notifi').text("No puede registrar un codigo Iata que ya existe, Verifique.");
        $('.notifi').css({
          color: 'white',
        });
        
        $('.alert').show('slow', function() {
          $('.alert').removeClass('notification');
        });
      }

      if(data.datos.registroexitoso){

        $('.notifi').text("Se ha registrado exitosamente el codigo Iata, Ya lo puedes encontrar en el listado");
        $('.notifi').css({
          color: 'white',
        });
        $('.alert').removeClass('alert-info');
        
        $('.alert').addClass('alert-success');


        // var o = new Option(data.iata.id,data.iata.codigo);

        // $('#iata').append(o);

        $('#iata').append('<option value="' + data.iata.id + '">'+data.iata.codigo+'</option>');
        $('#iata').selectpicker('refresh');
        $('.alert').show('slow', function() {
          $('.alert').removeClass('notification');
        });

      }
        
    })
    .fail(function() {
      console.log("error");
    })
    .always(function() {
      console.log("complete");
    });
  });
  
  $('.new-iata').click(function(){
    $('#new-iata').modal('show');
  });

  $('.cerrarmodal').click(function(event) {
    $('#new-iata').modal('hide');
  });

</script>

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
