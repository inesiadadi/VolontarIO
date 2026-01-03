<?php 
include('header.php');

$html = file_get_contents('src/html/error_404.html');
$pagina = file_get_contents('templates/blueprint.html');
$footer_html = file_exists('templates/components/footer.html') ? file_get_contents('templates/components/footer.html') : '';
$placeholders_layout = [
    '[Titolo Pagina]' => 'Errore 404',
    '[Description]'   => 'Pagina di rindirizzamento in seguito ad un errore 404, una pagina non trovata',
    '[Keywords]'      => 'errore, 404, error, non trovata',
    '[pageNameId]'    => 'error404Page', 
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
