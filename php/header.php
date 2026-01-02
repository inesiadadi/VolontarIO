<?php

require_once ROOT_PATH . '/db/include/functions.php';
require_once ROOT_PATH . '/db/include/dbConnection.php';
// require_once "connessione.php";
session_start(); // Per poter usare l'array $_SESSION
$header_html = file_get_contents(ROOT_PATH . '/templates/components/header.html');
if(isset($_SESSION['logged'])) {
    $logout = 
    	$log = "<a href='/logout' lang='en'>Logout</a>";
    $header_html = str_replace("[accountAccess]", $logout, $header_html);
} else {
	$log = "<a href='/volontario/login' class='btn-header btn-login' >Accedi</a>
	        <a href='/volontario/register' class='btn-header btn-register' >Registrati</a>";
    $header_html = str_replace("[accountAccess]", $log, $header_html);
}
?>