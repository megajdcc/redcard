<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; # Desarrollado por Alan Casillas. alan.stratos@hotmail.com

if(!$_SESSION['user']){ header('Location: '.HOST.'/'); die(); }
unset($_SESSION['user']);
unset($_SESSION['business']);
unset($_SESSION['notification']);
header('Location: '.HOST.'/');
die(); ?>