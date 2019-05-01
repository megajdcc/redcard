<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com
$con = new assets\libs\connection();

if(!isset($_SESSION['user'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

if(!isset($_SESSION['perfil'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
	}


use Hotel\models\Usuarios;
use Hotel\models\Includes;

$usuarios = new Usuarios($con);


if($_SERVER["REQUEST_METHOD"] == "POST"){
 	// Peticiones al servidors 	
}


$includes = new Includes($con);

$properties['title'] = 'Socios | Travel Points';
$properties['description'] = '';


echo $header = $includes->get_no_indexing_header($properties);
echo $navbar = $includes->get_admin_navbar(); ?>


<?php echo $con->get_notify();?>

<div class="row">
	<div class="col-sm-12">
		<?php echo $usuarios->getNotificacion();?>
		<div class="background-white p20 mb50">
			<div class="page-title">
				<h1>Socios de Travel Points</h1>
			</div>
				<table  id="hotelusuarios" class="display" cellspacing="0" width="100%">
					<thead>
						<tr>
						
						<th>#</th>
						<th>Foto</th>
						<th>Username</th>
						<!-- <th>Correo electr&oacute;nico</th> -->
						<th>eSmartties</th>
						
						<th>Nombre</th>
						<th>Apellido</th>
						
						</tr>
					</thead>
					
					<tbody>
						
						<?php echo $usuarios->getUsuarios();?>
					</tbody>
				</table>
		</div>
	</div>
</div>

<?php echo $usuarios->Modal(); ?>

<script>

				 var t = $('#hotelusuarios').DataTable( {
					"paging"        :         false,
					"scrollY"       :        "400px",
					"scrollCollapse": true,
			         "language": {
			                        "lengthMenu": "Mostar _MENU_ registros por pagina",
			                        "info": "",
			                        "infoEmpty": "No se encontro ningun usuario",
			                        "infoFiltered": "(filtrada de _MAX_ registros)",
			                        "search": "Buscar: ",
			                        "paginate": {
			                            "next":       "Siguiente",
			                            "previous":   "Anterior"
			                        },
			                    },
			        "columnDefs": [ {
			            "searchable": true,
			            "orderable": true,
			            "targets": 0
			        } ],
			        "order": [[ 0, 'asc' ]]
			    } );
    
</script>
<?php echo $footer = $includes->get_admin_footer(); ?>