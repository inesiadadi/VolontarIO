
<?php 
require_once ROOT_PATH . '/db/include/functions.php';
require_once ROOT_PATH . '/db/include/dbConnection.php';
// require_once "connessione.php";
session_start(); // Per poter usare l'array $_SESSION
$header_html = file_get_contents(ROOT_PATH . '/templates/components/header.html');

if(isset($_SESSION['logged'])) {
    $menu_utente = "
    <div class='dropdown'>
        <img src='profile_green.svg' alt='Menu Icona' class='dropbtn'>
        <div class='dropdown-content'>
            <a href='/volontario/profilo'>Area personale</a>
            <a href='/volontario/recensioni'>Recensioni</a>
            <a href='/volontario/richieste'>Richieste effettuate</a>
            <a href='/volontario/candidature'>Candidature</a>
            <a href='/volontario/logout'>Log out</a>
        </div>
    </div>";
    $header_html = str_replace("[accountAccess]", $menu_utente, $header_html);
} else {
    $bottoni_ospite = "
        <a href='/volontario/login' class='btn btn-login'>Accedi</a>
        <a href='/volontario/register' class='btn btn-register'>Registrati</a>";
    $header_html = str_replace("[accountAccess]", $bottoni_ospite, $header_html);
}
?>