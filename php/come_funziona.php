<?php
include('header.php');

$html = file_get_contents(ROOT_PATH .'/templates/come_funziona.html');
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