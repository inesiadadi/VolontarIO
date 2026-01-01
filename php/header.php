<?php 
require_once "connessione.php";
session_start();

$html_header = file_get_contents(realpath(__DIR__ . '/../src/html/header.html'));

if(isset($_SESSION['logged'])) {
    $menu_utente = "
    <div class='dropdown'>
        <img src='profile_green.svg' alt='Menu Icona' class='dropbtn'>
        <div class='dropdown-content'>
            <a href='area_personale.php'>Area personale</a>
            <a href='recensioni.php'>Recensioni</a>
            <a href='richieste.php'>Richieste effettuate</a>
            <a href='logout.php'>Log out</a>
        </div>
    </div>";
    $html_header = str_replace("[accountAccess]", $menu_utente, $html_header);
} else {
    $bottoni_ospite = "
        <a href='login.php' class='btn btn-login'>Accedi</a>
        <a href='register.php' class='btn btn-register'>Registrati</a>";
    $html_header = str_replace("[accountAccess]", $bottoni_ospite, $html_header);
}
echo $html_header;
?>

