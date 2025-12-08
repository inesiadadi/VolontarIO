<?php 
include('connessione.php'); // La connessione con il database è fatto in un file a parte, aggiunto nell'header (quindi aggiunto in ogni pagina).
session_start(); // Per poter usare l'array $_SESSION
$html_header = file_get_contents(realpath(__DIR__ . '/' . '../src/html/header.html'));
if(isset($_SESSION['logged'])) {
    $logout = 
    	$log = "<div class='accountAccess'>
                <a href='/logout' lang='en'>Logout</a>
	            </div>";
    $html_header = str_replace("%accountAccess%", $logout, $html_header);
} else {
	$log = "<div class='accountAccess'>
	        <a href='/login'>Accedi</a>
	        <a href='/register'>Registrati</a>
	        </div>";
    $html_header = str_replace("%accountAccess%", $log, $html_header);
}
?>

