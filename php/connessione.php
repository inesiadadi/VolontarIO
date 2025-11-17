<?php 

$host = '127.0.0.1';
$db   = 'tecnoweb';
$user = 'user';
$pass = 'user';
$connessione = new mysqli('p:'.$host, $user, $pass, $db);

if($connessione->connect_errno) {
    die($connessione->connect_errno);
}
?>
    