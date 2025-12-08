<?php 
$title = "Errore 404 | VolontarIO";
$description = "Pagina di rindirizzamento in seguito ad un errore 404, una pagina non trovata";
$keywords = "Errore 404 Error Non Trovata";
include('header.php');

$html_header = str_replace("%title%", $title, $html_header);
$html_header = str_replace("%description%", $description, $html_header);
$html_header = str_replace("%keywords%", $keywords, $html_header);
echo $html_header;

include __DIR__ . '/../src/html/error_404.html';
include('footer.php');
?>
