<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // Molto semplicemente prende quello dopo il dominio

switch ($path) {
    case '/':
        include('home.php');
        break;

    case '/register':
        include('register.php');
        break;

    case '/login':
        include('login.php');
        break;
    
    case '/come-funziona':
        include('come_funziona.php');
        break;    

    default:
        http_response_code(404);
        include('error_404.php');
        break;
}
?>
