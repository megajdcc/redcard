<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 
if(isset($_SESSION['promotor']) ){
	unset($_SESSION['promotor']);
}
if(!$_SESSION['promotor']){ header('Location: '.HOST.'/Hotel/login'); die(); }