<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 

if(!isset($_SESSION['user']) || !isset($_SESSION['business'])){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}
if($_SESSION['business']['id_rol'] != 4 && $_SESSION['business']['id_rol'] != 5 && $_SESSION['business']['id_rol'] != 6){
	http_response_code(404);
	include(ROOT.'/errores/404.php');
	die();
}

header('Location: '.HOST.'/negocio/certificados/reservar');
die();
?>