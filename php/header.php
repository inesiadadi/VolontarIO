
<?php 
require_once ROOT_PATH . '/db/include/functions.php';
require_once ROOT_PATH . '/db/include/dbConnection.php';
// require_once "connessione.php";
session_start(); // Per poter usare l'array $_SESSION
$html_header = file_get_contents(ROOT_PATH . '/templates/components/header.html');

if(isset($_SESSION['logged'])) {
    $menu_utente = "
    <div class='dropdown'>
        <img src='profile_green.svg' alt='Menu Icona' class='dropbtn'>
        <div class='dropdown-content'>
            <a href='/profilo'>Area personale</a>
            <a href='/recensioni'>Recensioni</a>
            <a href='/richieste'>Richieste effettuate</a>
            <a href='/candidature'>Candidature</a>
            <a href='/logout'>Log out</a>
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