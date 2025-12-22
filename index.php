<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // Molto semplicemente prende quello dopo il dominio


// Router funziona a metà, da mettere apposto quando si capirà meglio come funziona il server tecweb
// GRANDISSIMO problema di sicurezza (Si puo' scaricare qualsiasi file della cartella, da capire come far funzionare tutto cio' con .htaccess)
switch ($path) {
    case '/':
        include('php/home.php');
        break;

    case '/register':
        include('php/register.php');
        break;

    case '/login':
        include('php/login.php');
        break;
    
    case '/come-funziona':
        include('php/come_funziona.php');
        break;    
    case '/logout':
        include('php/logout.php');
        break;
    case '/profilo':
        include('php/profilo.php');
        break;
    default:
        http_response_code(404);
        include('php/error_404.php');
        break;
}
?>
