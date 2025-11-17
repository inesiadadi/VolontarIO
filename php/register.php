<?php
$title = "Registrazione";
include('header.php');

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<main>
    Registrazione
</main>
<?php
include('footer.php');
?>