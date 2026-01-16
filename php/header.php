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
        <button class="dropbtn" aria-label="Menu Utente">
            <img src="src/images/profile_green.svg" alt="Avatar">
        </button>
        <div class="dropdown-content">
            <a href="/volontario/profilo?id=' . $_SESSION['user_id'] . '">Area personale</a>
            <a href="/volontario/recensioni?id=' . $_SESSION['user_id'] . '">Recensioni</a>
            <a href="/volontario/richieste?id=' . $_SESSION['user_id'] . '">Richieste effettuate</a>
            <a href="/volontario/candidature?id=' . $_SESSION['user_id'] . '">Candidature</a>
        ';
if($_SESSION['role'] == "admin") {
    $menu_utente .= '<a href="/volontario/pannello">Pannello amministratore</a>';
}
    $menu_utente .=    '
            <a href="/volontario/logout?id=' . $_SESSION['user_id'] . '">Log out</a>
        </div>
    </div>';
    $header_html = str_replace("[accountAccess]", $menu_utente, $header_html);
} else {
    $bottoni_ospite = "
        <div class='auth-buttons'>
            <a href='/volontario/login' class='btnh btn-login-header'>Accedi</a>
            <a href='/volontario/register' class='btnh btn-register-header'>Registrati</a>
        </div>";
    $header_html = str_replace("[accountAccess]", $bottoni_ospite, $header_html);
}
?>