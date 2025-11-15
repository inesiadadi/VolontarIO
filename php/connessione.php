<?php 

$host = '127.0.0.1';
$db   = 'tecnoweb';
$user = 'user';
$pass = 'user';
$conn = new mysqli($host, $user, $pass, $db);

if($conn->connect_errno) {
    die($conn->connect_errno);
} else {
    echo "Connesso";
}
?>
    