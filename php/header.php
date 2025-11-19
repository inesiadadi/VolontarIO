<?php 
include('connessione.php'); // La connessione con il database è fatto in un file a parte, aggiunto nell'header (quindi aggiunto in ogni pagina).
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$title?></title> <!--$title corrisponde al valore che mettiamo a $title prima dell'include dell'header dentro ogni file php-->
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="/" lang="en">Home</a></li>
                <li><a href="/register">Registrazione</a></li>
                <li><a href="/login" lang="en">Login</a></li>
                <li><a href="/come-funziona">Come funziona</a></li> <!-- Da aggiungere -->
            </ul>
        </nav>
    </header>
    <!-- Ancora da lavorarci -->

