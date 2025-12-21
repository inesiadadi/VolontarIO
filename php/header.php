<?php 
require_once "connessione.php";
session_start(); // Per poter usare l'array $_SESSION
$html_header = file_get_contents(realpath(__DIR__ . '/' . '../src/html/header.html'));
if(isset($_SESSION['logged'])) {
    $logout = 
    	$log = "<a href='/logout' lang='en'>Logout</a>";
    $html_header = str_replace("[accountAccess]", $logout, $html_header);
} else {
	$log = "<a href='/login' class='btn btn-login' >Accedi</a>
	        <a href='/register' class='btn btn-register' >Registrati</a>";
    $html_header = str_replace("[accountAccess]", $log, $html_header);
}
?>

