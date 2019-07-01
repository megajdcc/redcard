<?php require_once $_SERVER['DOCUMENT_ROOT'].'/assets/libs/init.php'; 
$con = new assets\libs\connection();

if( !@$_SERVER["HTTP_X_REQUESTED_WITH"] ){
	http_response_code(403);
	include(ROOT.'/errores/403.php');
	die();
}

$json = new assets\libs\jsonSearch($con);

if($_SERVER["REQUEST_METHOD"] == "POST"){


	if(filter_input(INPUT_POST, 'id_pais', FILTER_VALIDATE_INT)){
		echo $json->getStates($_POST['id_pais']);
	}
	if(filter_input(INPUT_POST, 'id_estado', FILTER_VALIDATE_INT)){
		echo $json->getCities($_POST['id_estado']);
	}

	if(isset($_POST['bookmark_add'])){
		$json->add_bookmark($_POST['bookmark_add']);
	}
	if(isset($_POST['bookmark_del'])){
		$json->del_bookmark($_POST['bookmark_del']);
	}

	if(isset($_POST['recommend_add'])){
		$json->add_recommend($_POST['recommend_add']);
	}
	if(isset($_POST['recommend_del'])){
		$json->del_recommend($_POST['recommend_del']);
	}

	if(isset($_POST['wishlist_add'])){
		$json->add_wishlist($_POST['wishlist_add']);
	}
	if(isset($_POST['wishlist_del'])){
		$json->del_wishlist($_POST['wishlist_del']);
	}
	if(isset($_POST['the_url'])){
		echo $json->set_friendly_url($_POST['the_url']);
	}
	if(isset($_POST['cert_username'])){
		echo $json->get_username_cert($_POST['cert_username']);
	}
	if(isset($_POST['load_username'])){
		echo $json->get_username_load($_POST['load_username']);
	}
	if(isset($_POST['currency']) && isset($_POST['total']) && isset($_POST['commission'])){
		echo $json->calculate_esmarties($_POST['currency'], $_POST['total'], $_POST['commission']);
	}
}

if($_SERVER["REQUEST_METHOD"] == "GET"){
	if(filter_input(INPUT_GET, 'referral')){
		echo $json->getUsers($_GET['referral']);
	}

	if(filter_input(INPUT_GET, 'hotel')){
		echo $json->getHotel($_GET['hotel']);
	}

	if(filter_input(INPUT_GET, 'restaurantes')){
		echo $json->getRestaurantes($_GET['restaurantes']);
	}
	// if(filter_input(INPUT_GET, 'certificate')){
	// 	echo $json->get_certificates('%'.$_GET['certificate'].'%');
	// }
	if(filter_input(INPUT_GET, 'business')){
		echo $json->get_businesses('%'.$_GET['business'].'%');
	}
}
?>