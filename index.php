<?php
define('BASE_PATH', '/volontario');
define('ROOT_PATH', __DIR__);

// Debug: decommenta le righe sotto se vuoi vedere l'errore reale invece del 500
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once ROOT_PATH . '/db/include/functions.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (BASE_PATH !== '/' && strpos($path, BASE_PATH) === 0) {
    $path = substr($path, strlen(BASE_PATH));
}

$path = rtrim($path, '/');
if ($path === '') { $path = '/'; }

// Se il file esiste fisicamente (es. un'immagine o un CSS), lascia che il server lo serva
if ((strpos($path, '/src/') === 0 || $path === '/db/include/comuni.php') && is_file(ROOT_PATH . $path)) {
    return false;
}

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
    
    case '/candidature':
        include ROOT_PATH . '/php/candidature.php';
        break;

    case '/bacheca-richieste':
        include ROOT_PATH . '/php/bacheca.php';
        break;

    case '/come-funziona':
        include ROOT_PATH . '/php/come_funziona.php';
        break;   

    case '/nuova-richiesta':
        include ROOT_PATH . '/php/nuova-richiesta.php';
        break;  
    
    case '/modifica-profilo':
        include ROOT_PATH . '/php/modificaProfilo.php';
        break;

    case '/pannello':
        include ROOT_PATH . '/php/pannello.php';
        break;
    case '/error-500':

        http_response_code(500);
        include ROOT_PATH . '/php/error_500.php';
        break;
    
    case '/nuova-recensione':
        include ROOT_PATH . '/php/nuovaRecensione.php';
        break;

    case '/modifica-richiesta':
    include ROOT_PATH . '/php/modifica-richiesta.php';
    break;
    
    default:
        http_response_code(404);
        include ROOT_PATH . '/php/error_404.php';
        break;
}
?>
