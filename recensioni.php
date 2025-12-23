<?php
require_once('db/include/functions.php'); // Assicurati che i percorsi siano corretti
require_once('db/include/dbConnection.php');

session_start();

// -------------------------------------------------------
// 1. DATI MOCK (Simulazione Database)
// -------------------------------------------------------
$recensioni_db = [
    [
        'id_autore' => 101,
        'autore'    => 'franchino17',
        'voto'      => 9,
        'foto'      => 'images/birbo.jpeg',
        'categoria' => 'Trasporti',
        'testo'     => 'Puntuale e gentilissimo, consigliato!',
        'data'      => '2023-10-12'
    ],
    [
        'id_autore' => 102,
        'autore'    => 'maurizia2',
        'voto'      => 8,
        'foto'      => '',
        'categoria' => 'Spesa',
        'testo'     => 'Molto disponibile, mi ha aiutato con le buste pesanti.',
        'data'      => '2023-09-05'
    ],
    [
        'id_autore' => 103,
        'autore'    => 'birbo19a',
        'voto'      => 4, 
        'foto'      => 'images/logo_volontarIO.png',
        'categoria' => 'Compagnia',
        'testo'     => 'Purtroppo è arrivato un po\' in ritardo.',
        'data'      => '2023-08-20'
    ],
    [
        'id_autore' => 104,
        'autore'    => 'luigi_verdi',
        'voto'      => 10,
        'foto'      => 'images/luigi.jpg',
        'categoria' => 'Commissioni',
        'testo'     => 'Eccezionale, ha sbrigato tutto in pochi minuti.',
        'data'      => '2023-11-15'
    ]
];

# $recensioni_db = [];

// -------------------------------------------------------
// 2. LOGICA FILTRO
// -------------------------------------------------------
// Recupero il valore dalla GET, se non c'è imposto 0 (tutti)
$min_vote = isset($_GET['minVote']) ? (int)$_GET['minVote'] : 0;

// Filtro l'array dei dati
$recensioni_filtrate = array_filter($recensioni_db, function($rec) use ($min_vote) {
    return $rec['voto'] >= $min_vote;
});


// -------------------------------------------------------
// 3. GENERAZIONE LISTA CARD HTML
// -------------------------------------------------------
$html_recensioni_output = "";

if (count($recensioni_filtrate) > 0) {
    // Carico il template della singola card
    $template_card = file_get_contents('templates/components/review.html');

    foreach ($recensioni_filtrate as $rec) {
        $foto_autore = !empty($rec['foto']) || !file_exists($rec['foto']) ? $rec['foto'] : 'images/default.png';
        $date_rec_obj = new DateTime($rec['data']);
        
        // Logica visualizzazione data (usa la tua funzione se disponibile)
        $data_visual = function_exists('formatDateItalian') ? formatDateItalian($rec['data']) : $date_rec_obj->format('d/m/Y');

        $temp = $template_card;
        $temp = str_replace('[IdAutore]',          $rec['id_autore'], $temp);
        $temp = str_replace('[AutoreRecensione]',  htmlspecialchars($rec['autore']), $temp);
        $temp = str_replace('[FotoAutore]',        $foto_autore, $temp);
        $temp = str_replace('[VotoRecensione]',    $rec['voto'], $temp);
        $temp = str_replace('[CategoriaServizio]', htmlspecialchars($rec['categoria']), $temp);
        $temp = str_replace('[TestoRecensione]',   htmlspecialchars($rec['testo']), $temp);
        $temp = str_replace('[DataISO]',           $date_rec_obj->format('Y-m-d'), $temp);
        $temp = str_replace('[DataVisual]',        $data_visual, $temp);
        
        $html_recensioni_output .= $temp;
    }
} else {
    // Messaggio se non ci sono risultati
    $html_recensioni_output = '<p style="grid-column: 1/-1; text-align: center; font-size: 1.2rem;">Nessuna recensione trovata per questo filtro.</p>';
}


// -------------------------------------------------------
// 4. GENERAZIONE PAGINA INTERNA (Sidebar + Grid)
// -------------------------------------------------------
$html_pagina_recensioni = file_get_contents('templates/reviewsPage.html');

// Sostituisco il placeholder con la lista generata
$html_pagina_recensioni = str_replace('[Recensioni]', $html_recensioni_output, $html_pagina_recensioni);

// Logica per mantenere l'opzione "selected" nella select HTML
// Sostituisco [sel_X] con la stringa "selected" se corrisponde al filtro attuale
$html_pagina_recensioni = str_replace('[sel_0]',  ($min_vote == 0)  ? 'selected' : '', $html_pagina_recensioni);
$html_pagina_recensioni = str_replace('[sel_2]',  ($min_vote == 2)  ? 'selected' : '', $html_pagina_recensioni);
$html_pagina_recensioni = str_replace('[sel_4]',  ($min_vote == 4)  ? 'selected' : '', $html_pagina_recensioni);
$html_pagina_recensioni = str_replace('[sel_6]',  ($min_vote == 6)  ? 'selected' : '', $html_pagina_recensioni);
$html_pagina_recensioni = str_replace('[sel_8]',  ($min_vote == 8)  ? 'selected' : '', $html_pagina_recensioni);
$html_pagina_recensioni = str_replace('[sel_10]', ($min_vote == 10) ? 'selected' : '', $html_pagina_recensioni);


// -------------------------------------------------------
// 5. CARICAMENTO BLUEPRINT GENERALE
// -------------------------------------------------------
$pagina = file_get_contents('templates/blueprint.html');

// Carico Header e Footer se esistono
$header_html = file_exists('templates/components/header.html') ? file_get_contents('templates/components/header.html') : '';
$footer_html = file_exists('templates/components/footer.html') ? file_get_contents('templates/components/footer.html') : '';

$placeholders_layout = [
    '[Titolo Pagina]' => 'VolontarIO | Le mie Recensioni',
    '[Description]'   => 'Elenco delle recensioni ricevute',
    '[Keywords]'      => 'recensioni, feedback, volontariato',
    '[pageNameId]'    => 'reviewsPage', 
    '[onload]'        => '',            
    '[Header]'        => $header_html,
    '[Footer]'        => $footer_html,
    '[MainContent]'   => $html_pagina_recensioni // Qui va tutto il blocco Main
];

foreach($placeholders_layout as $chiave => $valore) {
    $pagina = str_replace($chiave, $valore, $pagina);
}

echo $pagina;
?>
