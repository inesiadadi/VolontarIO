<?php
define('BASE_PATH', '/volontario');
define('ROOT_PATH', __DIR__);

require_once ROOT_PATH . '/db/include/functions.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (BASE_PATH !== '/' && strpos($path, BASE_PATH) === 0) {
    $path = substr($path, strlen(BASE_PATH));
}

$path = rtrim($path, '/');

if ($path === '') {
    $path = '/';
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
    
        case '/bacheca-richieste':
        include ROOT_PATH . '/php/bacheca.php';
        break;

    case '/come-funziona':
        include ROOT_PATH . 'php/come_funziona.php';
        break;    

    default:
        http_response_code(404);
        include ROOT_PATH . '/php/error_404.php';
        break;
}
?>