<?php 
// require_once "connessione.php";
session_start(); // Per poter usare l'array $_SESSION
$header_html = file_get_contents(realpath(__DIR__ . '/' . '../templates/components/header.html'));
if(isset($_SESSION['logged'])) {
    $logout = 
    	$log = "<a href='/logout' lang='en'>Logout</a>";
    $header_html = str_replace("[accountAccess]", $logout, $header_html);
} else {
	$log = "<a href='/login' class='btn-header btn-login' >Accedi</a>
	        <a href='/register' class='btn-header btn-register' >Registrati</a>";
    $header_html = str_replace("[accountAccess]", $log, $header_html);
}
?>

