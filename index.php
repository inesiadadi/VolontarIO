<?php
// Percorso base URL per il browser
define('BASE_PATH', '/volontario'); // Modifica se cambi la cartella del progetto

// Percorso root filesystem per includere file senza dipendere dal file corrente
define('ROOT_PATH', __DIR__);

// Include funzioni helper (contiene url(), formatDateItalian, ecc.)
require_once ROOT_PATH . '/db/include/functions.php';

// Prende la parte di URL dopo il dominio
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Rimuove BASE_PATH se presente (utile se il progetto è in sottocartella)
if (BASE_PATH !== '/' && strpos($path, BASE_PATH) === 0) {
    $path = substr($path, strlen(BASE_PATH));
}

// Normalizza: rimuove slash finali
$path = rtrim($path, '/');

// Se è vuoto, diventa "/"
if ($path === '') {
    $path = '/';
}
// Router funziona a metà, da mettere apposto quando si capirà meglio come funziona il server tecweb
// GRANDISSIMO problema di sicurezza (Si puo' scaricare qualsiasi file della cartella, da capire come far funzionare tutto cio' con .htaccess)
switch ($path) {
    case '/':
        include ROOT_PATH . '/php/home.php';
        break;

    case '/register':
        include ROOT_PATH . '/php/register.php';
        break;

    case '/login':
        include ROOT_PATH . '/php/login.php';
        break;

    case '/come-funziona':
        include ROOT_PATH . '/php/come_funziona.php';
        break;

    case '/logout':
        include ROOT_PATH . '/php/logout.php';
        break;

    case '/profilo':
        include ROOT_PATH . '/php/profilo.php';
        break;
    
    case '/recensioni':
        include ROOT_PATH . '/php/recensioni.php';
        break;
    
    case '/richieste':
        include ROOT_PATH . '/php/richieste.php';
        break;

    default:
        http_response_code(404);
        include ROOT_PATH . '/php/error_404.php';
        break;
}
?>
