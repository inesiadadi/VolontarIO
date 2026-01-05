<?php 

include('header.php');

$html = file_get_contents(ROOT_PATH . '/templates/error_500.html');
$pagina = file_get_contents(ROOT_PATH . '/templates/blueprint.html');
$footer_html = file_exists(ROOT_PATH . '/templates/components/footer.html') ? file_get_contents(ROOT_PATH . '/templates/components/footer.html') : '';
$placeholders_layout = [
    '[Titolo Pagina]' => 'Errore 500',
    '[Description]'   => 'Pagina di rindirizzamento in seguito ad un errore 500, errore del server',
    '[Keywords]'      => 'errore, 500, error, server',
    '[pageNameId]'    => 'error500Page', 
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
