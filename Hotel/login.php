<?php  

require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php';

$con = new assets\libs\connection();

use \Hotel\models\Includes;


$includes = new Includes($con);

$properties['title'] = 'Detalles de solicitud | Travel Points';
$properties['description'] = '';

?>

