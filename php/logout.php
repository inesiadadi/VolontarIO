<?php
require_once ROOT_PATH . '/db/include/functions.php';
require_once ROOT_PATH . '/db/include/dbConnection.php';
// require_once "connessione.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = array();
session_destroy();
header("Location: /volontario/");
exit();
?>
