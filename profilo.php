<?php
require_once('db/include/functions.php');
require_once('db/include/dbConnection.php');

session_start();

$dati_utente = [
    'id'             => 10,
    'nome'           => 'Mario',
    'cognome'        => 'Rossi',
    'username'       => 'mariorossi99',
    'foto'           => 'images/birbo.jpeg', 
    'nascita'        => '1995-05-15', 
    'citta'          => 'Milano',
    'ruolo'          => 'Volontario',
    'descrizione'    => "Ciao! Sono Mario, amo aiutare nel trasporto di spesa per anziani.\nFaccio volontariato da 5 anni e sono sempre disponibile nel weekend.",
    'media_voto'     => 4, 
    'num_recensioni' => 19
];

$recensioni_db = [
    [
        'id_autore' => 101,
        'autore'    => 'franchino17', 
        'voto'      => 9, 
        'foto'      => 'images/birbo.jpeg',
        'testo'     => 'Puntuale e gentilissimo, consigliato!', 
        'data'      => '2023-10-12'
    ],
    [
        'id_autore' => 102,
        'autore'    => 'maurizia2',  
        'voto'      => 8, 
        'testo'     => 'Molto disponibile.', 
        'data'      => '2023-09-05'
    ],
    [
        'id_autore' => 103,
        'autore'    => 'birbo19a',  
        'voto'      => 2.5, 
        'foto'      => 'images/logo_volontarIO.png',
        'testo'     => '', 
        'data'      => '2023-08-20'
    ],
        [
        'id_autore' => 101,
        'autore'    => 'franchino17', 
        'voto'      => 9, 
        'foto'      => 'images/birbo.jpeg',
        'testo'     => 'Puntuale e gentilissimo, consigliato!', 
        'data'      => '2023-10-12'
    ],
    [
        'id_autore' => 102,
        'autore'    => 'maurizia2',  
        'voto'      => 8, 
        'testo'     => 'Molto disponibile.', 
        'data'      => '2023-09-05'
    ]
];

# $recensioni_db = []; # decommentare per avere l'effetto no reviews

$id_visitatore = $_SESSION['user_id'] ?? 0; 

$data_obj = new DateTime($dati_utente['nascita']);
$data_visual = $data_obj->format('d/m/Y');

$descrizione_html = nl2br(htmlspecialchars($dati_utente['descrizione']));

$azioni_html = "";
if ($id_visitatore === $dati_utente['id']) {
    $azioni_html = '<a href="modifica.php" class="btn modifyProfile">Modifica Profilo</a>';
} else {
    $azioni_html = '<a href="chat.php?uid='.$dati_utente['id'].'" class="btn contactUser">Contatta</a>';
}

if($dati_utente['media_voto']) {
    $html_reputazione = file_get_contents('templates/components/valuation.html');

    $percentuale_stelle = ($dati_utente['media_voto'] * 10) . "%";

    $html_reputazione = str_replace('[MediaVoto]',         $dati_utente['media_voto'], $html_reputazione);
    $html_reputazione = str_replace('[NumRecensioni]',     $dati_utente['num_recensioni'], $html_reputazione);
    $html_reputazione = str_replace('[PercentualeStelle]', $percentuale_stelle, $html_reputazione);
} else {
    $html_reputazione = file_get_contents('templates/components/no_review.html');
}

$html_recensioni_output = "";

if (count($recensioni_db) > 0) {
    $html_recensioni_output = ""; // Nessun contenitore qui
    $template_item = file_get_contents('templates/components/review.html');
    
    foreach ($recensioni_db as $rec) {
        $foto_autore = !empty($rec['foto']) ? $rec['foto'] : 'images/default.png';
        $date_rec_obj = new DateTime($rec['data']);
        
        $temp = $template_item;
        $temp = str_replace('[IdAutore]',         $rec['id_autore'], $temp);
        $temp = str_replace('[AutoreRecensione]', htmlspecialchars($rec['autore']), $temp);
        $temp = str_replace('[FotoAutore]',       $foto_autore, $temp);
        $temp = str_replace('[VotoRecensione]',   $rec['voto'], $temp);
        $temp = str_replace('[TestoRecensione]',  htmlspecialchars($rec['testo']), $temp);
        $temp = str_replace('[DataISO]',          $date_rec_obj->format('Y-m-d'), $temp);
        $temp = str_replace('[DataVisual]',       formatDateItalian($rec['data']), $temp);
        
        $html_recensioni_output .= $temp;
    }
} else {
    $html_recensioni_output = file_get_contents('templates/components/no_review.html');
}

$html_profilo = file_get_contents('templates/profile.html');

$placeholders_profilo = [
    '[FotoProfilo]'        => $dati_utente['foto'],
    '[Nome]'               => htmlspecialchars($dati_utente['nome']),
    '[Cognome]'            => htmlspecialchars($dati_utente['cognome']),
    '[Username]'           => htmlspecialchars($dati_utente['username']),
    '[DataISO]'            => $dati_utente['nascita'],
    '[DataVisual]'         => $data_visual,
    '[Citta]'              => htmlspecialchars($dati_utente['citta']),
    '[Actions]'            => $azioni_html,
    '[Descrizione]'        => $descrizione_html,
    '[SezioneReputazione]' => $html_reputazione,
    '[SezioneRecensioni]'  => $html_recensioni_output
];

foreach($placeholders_profilo as $chiave => $valore) {
    $html_profilo = str_replace($chiave, $valore, $html_profilo);
}

$pagina = file_get_contents('templates/blueprint.html');

$header_html = file_exists('templates/components/header.html') ? file_get_contents('templates/components/header.html') : '';
$footer_html = file_exists('templates/components/footer.html') ? file_get_contents('templates/components/footer.html') : '';

$placeholders_layout = [
    '[Titolo Pagina]' => 'VolontarIO | Profilo di ' . htmlspecialchars($dati_utente['username']),
    '[Description]'   => 'Visualizza il profilo di ' . htmlspecialchars($dati_utente['nome']) . ' su VolontarIO',
    '[Keywords]'      => 'volontariato, profilo, ' . htmlspecialchars($dati_utente['citta']),
    '[pageNameId]'    => 'accountPage', 
    '[onload]'        => '',            
    '[Header]'        => $header_html,
    '[Footer]'        => $footer_html,
    '[MainContent]'   => $html_profilo 
];

foreach($placeholders_layout as $chiave => $valore) {
    $pagina = str_replace($chiave, $valore, $pagina);
}

echo $pagina;
?>
