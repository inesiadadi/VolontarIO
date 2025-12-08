<?php 
$title = "Homepage | VolontarIO";
$description = "Homepage del sito";
$keywords = "Home Homepage";
include('header.php');

$html_header = str_replace("%title%", $title, $html_header);
$html_header = str_replace("%description%", $description, $html_header);
$html_header = str_replace("%keywords%", $keywords, $html_header);
echo $html_header;

?>
<main>
    home
<?php
    if($_SESSION['logged'] === true) {
        echo "<p>Bentornato " . $_SESSION['username'] . "</p>";
    }
?>
</main>
<?php
include('footer.php');
?>
