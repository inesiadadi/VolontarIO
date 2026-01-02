<?php 

// File per la gestione delle funzionalità di PhP

use DB\DBAccess;
require_once('dbConnection.php');


function formatDateItalian($date) {
    // Mappa dei mesi in italiano
    $mesi = [
        1 => 'Gennaio', 2 => 'Febbraio', 3 => 'Marzo', 4 => 'Aprile',
        5 => 'Maggio', 6 => 'Giugno', 7 => 'Luglio', 8 => 'Agosto',
        9 => 'Settembre', 10 => 'Ottobre', 11 => 'Novembre', 12 => 'Dicembre'
    ];

    // Creazione oggetto DateTime dalla stringa del DB
    $d = new DateTime($date);
    
    $giorno = $d->format('j');                // Giorno senza zero iniziale (1-31)
    $mese   = $mesi[(int)$d->format('n')];    // Nome del mese dall'array (n = 1-12)
    $anno   = $d->format('Y');                // Anno a 4 cifre

    return "$giorno $mese $anno";
}

function url(string $path = '', bool $preserveState = false): string {
    $path = '/' . ltrim($path, '/');

    if ($preserveState) {
        $qs = $_SERVER['QUERY_STRING'] ?? '';
        if ($qs) {
            $path .= '?' . $qs;
        }
    }

    return BASE_PATH . $path;
}

?>