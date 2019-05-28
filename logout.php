<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 

if(!$_SESSION['user']){ header('Location: '.HOST.'/'); die(); }
unset($_SESSION['user']);
unset($_SESSION['business']);
unset($_SESSION['notification']);
unset($_SESSION['notificacion']);

unset($_SESSION['perfil']);
header('Location: '.HOST.'/');

if(isset($_SESSION['nombrehotel']) and isset($_SESSION['codigohotel'])){
	unset($_SESSION['nombrehotel']);
	unset($_SESSION['id_hotel']);
	unset($_SESSION['codigohotel']);
}


unset($_SESSION);
die(); ?>