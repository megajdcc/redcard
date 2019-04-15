<?php # Desarrollado por Mario Sacramento. info@infochanel.si

header('Content-Type: text/html; charset=utf-8');

date_default_timezone_set('America/Mexico_City');

session_start();
spl_autoload_register(function ($class) {
	// project-specific namespace prefix
	$prefix = '';
	// base directory for the namespace prefix
	$base_dir = dirname(dirname(__DIR__)) . '/';
	// does the class use the namespace prefix?
	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) {
		// no, move to the next registered autoloader
		return;
	}
	// get the relative class name
	$relative_class = substr($class, $len);
	// replace the namespace prefix with the base directory, replace namespace
	// separators with directory separators in the relative class name, append
	// with .php
	$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
	// if the file exists, require it
	if (file_exists($file)) {
		require $file;
	}
});

define('HOST', 'http://'.$_SERVER['HTTP_HOST']);
define('ROOT', $_SERVER['DOCUMENT_ROOT']);

function _safe($string){
	return htmlentities($string, ENT_QUOTES, 'UTF-8');
}
?>