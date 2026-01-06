<?php 
include('header.php');

$html = file_get_contents(realpath(__DIR__ . '/' . '../src/html/home.html'));
$pagina = file_get_contents('templates/blueprint.html');
$footer_html = file_exists('templates/components/footer.html') ? file_get_contents('templates/components/footer.html') : '';
$placeholders_layout = [
    '[Titolo Pagina]' => 'Homepage',
    '[Description]'   => 'Homepage di VolontarIO',
    '[Keywords]'      => 'volontariato, home, homepage, inizio',
    '[pageNameId]'    => 'homePage', 
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
