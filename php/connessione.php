<?php 

$host = '127.0.0.1';
$db   = 'tecnoweb';
$user = 'user';
$pass = 'user';
$connessione = new mysqli('p:'.$host, $user, $pass, $db); 
// Il "p:" sta per persistent, serve per mantenere la connessione mysqli, perché senno' ogni volta che carichi una pagina rifà la connessione mysqli (cio' comporta problemi di performance)


if($connessione->connect_errno) {
    die($connessione->connect_errno);
}
?>
    