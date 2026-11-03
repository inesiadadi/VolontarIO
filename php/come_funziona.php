<?php

use DB\DBAccess;
include('header.php');
$html = file_get_contents(ROOT_PATH .'/templates/come_funziona.html');

if(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    $nome = trim(filter_input(INPUT_POST, 'nome_cognome', FILTER_SANITIZE_SPECIAL_CHARS));
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $numero = trim(filter_input(INPUT_POST, 'tel', FILTER_SANITIZE_SPECIAL_CHARS));
    $messaggio = trim(filter_input(INPUT_POST, 'messaggio', FILTER_SANITIZE_SPECIAL_CHARS));
    $errori = [];
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errori[] = "L'email inserita non è valida.";
    }
    
    if (!preg_match("/^[a-zA-Zà-ùÀ-Ù']{2,}\s+[a-zA-Zà-ùÀ-Ù\s']{2,}$/", $nome)) {
        $errori[] = "Inserisci sia il nome che il cognome (solo lettere).";
    } elseif (strlen($nome) > 50) {
        $errori[] = "Il nome completo è troppo lungo (max 50 caratteri).";
    }

    if (!empty($numero) && !preg_match("/^(\+?[0-9\s\-]{10,14})$/", $numero)) {
        $errori[] = "Il numero di telefono non è valido.";
    }
    
    if(!preg_match("/^[\s\S]{50,999}$/s", $messaggio)) {
        $errori[] = "Il messaggio non rispetta il minimo o il massimo dei caratteri richiesti.";
    }
    
    
    if(empty($errori)) {
        $dbAccess = new DBAccess();
        if (!$dbAccess->openDBConnection()) {
            header('Location: /volontario/error-500');
            exit;
            } else {
            $dbAccess->saveMessaggio($nome, $email, $numero, $messaggio);
            $errore = "<div role='status'><p id='infoForm' class='success'>Richiesta d'assistenza inviata con successo</p></div>";
            $html = str_replace('[formMessage]', $errore,$html);
            $html = str_replace('[formMessage]', "",$html);
            if(isset($_SESSION['logged'])) {
                $html = str_replace('[email]', $_SESSION['email'], $html);
                $html = str_replace('[nome_cognome]', $_SESSION['nome'] . ' ' . $_SESSION['cognome'], $html);
            } else {
                $html = str_replace('[email]', "", $html);
                $html = str_replace('[nome_cognome]', "", $html);
            }
            $html = str_replace('[tel]', "", $html);
            $html = str_replace('[messaggio]', "", $html);

            
        }
    } else {
        $messaggio = "<div role='alert'><ul>";
        foreach($errori as $errore) {
            $messaggio .= "<li>" . $errore . "</li>";
        }
        $messaggio .= "</ul></div>";
        
        $html = str_replace('[formMessage]', $messaggio, $html);
    }
} else {
        $html = str_replace('[formMessage]', "",$html);
        if(isset($_SESSION['logged'])) {
            $html = str_replace('[email]', $_SESSION['email'], $html);
            $html = str_replace('[nome_cognome]', $_SESSION['nome'] . ' ' . $_SESSION['cognome'], $html);  
        } else {
            $html = str_replace('[email]', "", $html);
            $html = str_replace('[nome_cognome]', "", $html);
        }
        $html = str_replace('[tel]', "", $html);

        $html = str_replace('[messaggio]', "", $html);

}
    
$html = str_replace('[csrf]', csrf_field(), $html);

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