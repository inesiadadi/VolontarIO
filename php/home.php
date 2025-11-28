<?php 
$title = "Homepage | VolontarIO";
include('header.php');
session_start();
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
