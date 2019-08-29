<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 
$con = new assets\libs\connection();


use Hotel\models\Includes;
use Hotel\models\ReportesVentas;
use Hotel\models\Dashboard;


if(!isset($_SESSION['perfil']) && !isset($_SESSION['promotor']) && !isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
	}

$Dashboard = new Dashboard($con);
$includes = new Includes($con);
$reporte = new ReportesVentas($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST['pdf'])){
		
			$reporte->mostrarpdf($_POST);
			die();
		}

	}

$properties['title'] = 'Estado de Cuenta | Travel Points';
$properties['description'] = '';
echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>
<?php echo $con->get_notify();?>
<div class="row">
	<div class="col-sm-12">
		<?php echo $reporte->getNotificacion();?>
	
		<div class="background-white p20 mb30">

				<div class="page-title">
					<h1>Estado de Cuenta</h1>
				</div>

				<!-- HEADER FILTROS DE BUSQUEDA -->

				<div class="row">
					<div class=" col-sm-11">
						<form class="pull-right" name="form-filtro" method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>">
							<div class="col-sm-4">
								<div class="form-group">
									<label for="start">Fecha y hora de inicio</label>
									<div class="input-group date" id="event-start">
										<input class="form-control" type="text" id="star" name="date_start"  placeholder="Fecha y hora de inicio" required/>
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									</div>
								</div>
							</div>

							<div class="col-sm-4">
								<div class="form-group">
									<label for="end">Fecha y hora de fin</label>
									<div class="input-group date" id="event-end">

										<input class="form-control" type="text" id="en" name="date_end" required placeholder="Fecha y hora de fin" />
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									</div>
									
								</div>
							</div>
							<div class="col-sm-4">
									<label>Search</label>
									<div class="form-group">
										<button name="filtrar" type="submit" class="btn btn-info"><i class="fa fa-search"></i>Buscar</button>
										<button class="clear btn btn-info" type="button"><i class="fa fa-repeat"></i>Limpiar</button>
									</div>			
							</div>
						</form>
					</div>
					
					<div class="col-sm-1">
						<form class="pull-right" name="form-descargar" method="post" action="<?php echo _safe($_SERVER['REQUEST_URI']);?>" target="_blank">
					
							<label>Descargar</label>
							<div class="form-group">				
								<input type="hidden" name="date_start" class="f1" value="<?php echo $reporte->getFecha1();?>" >
								<input type="hidden" name="date_end" class="f2" value="<?php echo $reporte->getFecha2();?>" >
								<button class="btn btn-default text-danger" type="submit" name="pdf"><i class="fa fa-file-pdf-o"></i>PDF</button>
							</div>
				
						</form>
					</div>
						

				</div>

		</div>

		</form>
		



		<!--  TABLA DE REPORTE -->
<div class="background-white p20 mb50">
<!-- 	<div class="row">
		<div class=" col-lg-12 form-group" data-toggle="tooltip" title="Haga su busqueda inteligente, si desea ser especifico encierre su busqueda entre comillas dobles." >
			<label for="busqueda">Filtre por busqueda| </span><i class="fa fa-question-circle text-secondary"></i></label>
				<div class="input-group">
					<strong class="input-group-addon"><i class="fa fa-search"></i></strong>
					<input type="text" class="form-control busqueda" name="buscar" autocomplete="false" placeholder="Busqueda inteligente...">
				</div>
		</div>
	</div> -->

	<table  id="estadodecuenta" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
						
						<th>#</th>
						<th>Fecha</th>
						<th>Negocio</th>
						<th>Usuario</th>
						<th>Venta</th>
						<th>Comisión</th>
						<th>Balance</th>						
						</tr>
					</thead>
					
					<tbody>			
						<!-- <?php //echo $reporte->getEstadoCuenta();?> -->
					</tbody>
	</table>

			
		</div>
	</div>
</div>

<script>
	
$(document).ready(function() {


		
		 var t;

		cargarestadocuenta();

		$('form[name="form-filtro"]').bind('submit',function(e){
			e.preventDefault();
			t.ajax.reload(null,false);
			return false;
		});

		$('form[name="form-filtro"]').bind('submit',function(e){
				
			$('.f1').val(getRango1());
			$('.f2').val(getRango2());

			return true;
		});

		$('.clear').on('click',function(){
			$('input[name="date_start"]').val('');
			 $('input[name="date_end"]').val('');
			 t.ajax.reload(null,'');
		});

		function getRango1(){
			return $('input[name="date_start"]').val();
		}
		function getRango2(){
			return $('input[name="date_end"]').val();
		}


		function cargarestadocuenta(){

			t = $('#estadodecuenta').DataTable({
					paging        	:false,
					lengthChange	:false,
					scrollY      	:400,
					scrollCollapse	:true,
					ordering		:true,
					
					dom:'lrtip',
					ajax:{
						url:'/Hotel/controller/peticiones.php',
						type:'POST',
						dataType:'JSON',
						data:function(d){
							d.peticion ='cargarestadocuenta';
							d.rango1 = getRango1();
							d.rango2 = getRango2();
						}
					},
					
					columns:[
						 		{data:'id'},
						 		{data:'creado'},
						 		{data:'negocio'},
						 		{data:'nombre'},
						 		{data:'venta'},
						 		{data:'comision'},
						 		{data:'balance'}
						 		
					 		],
			         language:{
			                        "lengthMenu": "Mostar _MENU_ registros por pagina",
			                        "info": "",
			                        "infoEmpty": "Sin registro",
			                        "infoFiltered": "(filtrada de _MAX_ registros)",
			                        "search": "Buscar: ",
			                        "paginate": {
			                            "next":       "Siguiente",
			                            "previous":   "Anterior"
			                        },
			                    },
			        columnsDefs:[{
			        	orderable:true,targets:0
			        },
			        {
			        	orderable:false,targets:0
			        },
			        {
			        	orderable:false,targets:0
			        },
			        {
			        	orderable:false,targets:0
			        },
			        {
			        	orderable:false,targets:0
			        },
			        {
			        	orderable:false,targets:0
			        },
			        {
			        	orderable:false,
			        	targets:0,
			        	width:'50px'
			        },
			        {
			        	orderable:false,targets:0
			        }],
			        order:[[0,'asc']]
			    });

		}
		

		    $('input[name="buscar"]').on('keyup',function(e){

					t.search(this.value).draw();
			   });		
});
</script>
<?php echo $footer = $includes->get_admin_footer(); ?>