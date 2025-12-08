<?php
$title = "Come funziona | VolontarIO";
$description = "Pagina esplicativa del funzionamento del sito";
$keywords = "Spiegazione how-to";
include('header.php');

$html_header = str_replace("%title%", $title, $html_header);
$html_header = str_replace("%description%", $description, $html_header);
$html_header = str_replace("%keywords%", $keywords, $html_header);
echo $html_header;

include __DIR__ . '/../src/html/come_funziona.html';
include('footer.php');
?>