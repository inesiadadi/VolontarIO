<?php

use DB\DBAccess;

include_once 'dbConnection.php';

header('Content-Type: application/json');

$dbAccess = new DBAccess();
if(!$dbAccess->openDBConnection()) {
    http_response_code(500);
    exit;
}

$input = $_GET['q'] ?? '';

if(strlen($input) >= 2) {
    $comuni = $dbAccess->getComuniPerRichieste($input);
    echo json_encode($comuni);
} else {
    echo json_encode([]);
}
?>
