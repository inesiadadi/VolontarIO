<?php 
require_once ROOT_PATH . '/db/include/functions.php';
require_once ROOT_PATH . '/db/include/dbConnection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$header_html = file_get_contents(ROOT_PATH . '/templates/components/header.html');


if(isset($_SESSION['logged'])) {
    $menu_utente = '
    <div class="dropdown">
        <img src="profile_green.svg" alt="Menu Icona" class="dropbtn">
        <div class="dropdown-content">
            <a href="/volontario/profilo?id=' . $_SESSION['user_id'] . '">Area personale</a>
            <a href="/volontario/recensioni?id=' . $_SESSION['user_id'] . '">Recensioni</a>
            <a href="/volontario/richieste?id=' . $_SESSION['user_id'] . '">Richieste effettuate</a>
            <a href="/volontario/candidature?id=' . $_SESSION['user_id'] . '">Candidature</a>
            <a href="/volontario/logout?id=' . $_SESSION['user_id'] . '">Log out</a>
        </div>
    </div>';
    $header_html = str_replace("[accountAccess]", $menu_utente, $header_html);
} else {
    $bottoni_ospite = "
        <a href='/volontario/login' class='btn btn-login'>Accedi</a>
        <a href='/volontario/register' class='btn btn-register'>Registrati</a>";
    $header_html = str_replace("[accountAccess]", $bottoni_ospite, $header_html);
}
?>