<?php

use DB\DBAccess;
include('header.php');
$html = file_get_contents(ROOT_PATH .'/templates/come_funziona.html');

if(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    $nome = trim(filter_input(INPUT_POST, 'nome_cognome', FILTER_SANITIZE_SPECIAL_CHARS));
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $numero = trim(filter_input(INPUT_POST, 'tel', FILTER_SANITIZE_SPECIAL_CHARS));
    $messaggio = trim(filter_input(INPUT_POST, 'messaggio', FILTER_SANITIZE_SPECIAL_CHARS));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errore = "<div role='alert'><p id='infoForm' class='error'>La email inserita nel form d'assistenza non è valida, riprovare</p></div>";
        $html = str_replace('[formMessage]', $errore,$html);
        $html = str_replace('[tel]', $numero, $html);
        $html = str_replace('[nome_cognome]', $nome, $html);
        $html = str_replace('[messaggio]', $messaggio, $html);
    } else if (strlen($nome) > 32) {
        $errore = "<div role='alert'><p id='infoForm' class='error'>Il nome inserito è troppo lungo, riprovare</p></div>";
        $html = str_replace('[formMessage]', $errore, $html);
        $html = str_replace('[email]', $email, $html);
        $html = str_replace('[tel]', $numero, $html);
        $html = str_replace('[messaggio]', $messaggio, $html);
    } else if(strlen($numero) > 13) {
        $errore = "<div role='alert'><p id='infoForm' class='error'>Errore nel numero di telefono, riprovare</p></div>";
        $html = str_replace('[formMessage]', $errore,$html);
        $html = str_replace('[tel]', $numero, $html);
        $html = str_replace('[nome_cognome]', $nome, $html);
        $html = str_replace('[messaggio]', $messaggio, $html);
    } else {
        $dbAccess = new DBAccess();
        if (!$dbAccess->openDBConnection()) {
            http_response_code(500);
            include ROOT_PATH . '/php/error_500.php'; // oppure /templates/error500.html se preferisci
            exit;
            } else {
            $dbAccess->saveMessaggio($nome, $email, $numero, $messaggio);
            $errore = "<div role='alert'><p id='infoForm' class='success'>Richiesta d'assistenza inviata con successo</p></div>";
            $html = str_replace('[formMessage]', $errore,$html);
            $html = str_replace('[formMessage]', "",$html);
            if(isset($_SESSION['logged'])) {
                $html = str_replace('[email]', $_SESSION['email'], $html);
            } else {
                $html = str_replace('[email]', "", $html);
            }
            $html = str_replace('[tel]', "", $html);
            $html = str_replace('[nome_cognome]', "", $html);
            $html = str_replace('[messaggio]', "", $html);

            
        }
    }
    } else {
        $html = str_replace('[formMessage]', "",$html);
        if(isset($_SESSION['logged'])) {
            $html = str_replace('[email]', $_SESSION['email'], $html);
        } else {
            $html = str_replace('[email]', "", $html);
        }
        $html = str_replace('[tel]', "", $html);
        $html = str_replace('[nome_cognome]', "", $html);
        $html = str_replace('[messaggio]', "", $html);

    }
    

$pagina = file_get_contents(ROOT_PATH .'/templates/blueprint.html');
$footer_html = file_exists(ROOT_PATH . '/templates/components/footer.html') ? file_get_contents('templates/components/footer.html') : '';
$placeholders_layout = [
    '[Titolo Pagina]' => 'Come funziona',
    '[Description]'   => 'Pagina esplicativa del funzionamento del sito',
    '[Keywords]'      => 'spiegazione, how-to',
    '[pageNameId]'    => 'comeFunzionaPage', 
    '[onload]'        => '',            
    '[Header]'        => $header_html,
    '[Footer]'        => $footer_html,
    '[MainContent]'   => $html 
];

foreach($placeholders_layout as $chiave => $valore) {
    $pagina = str_replace($chiave, $valore, $pagina);
}

echo $pagina;
?>