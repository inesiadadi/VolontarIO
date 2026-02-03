<?php

use DB\DBAccess;

include('header.php');
// ID visitatore
$id_visitatore = $_SESSION['user_id'] ?? 0;

// ID utente target
$id_utente_target = isset($_GET['id']) ? intval($_GET['id']) : $id_visitatore;
if ($id_utente_target === 0) {
    header("Location: /volontario/login");
    exit;
}

// Connessione DB
$dbAccess = new DBAccess();
if (!$dbAccess->openDBConnection()) {
    header('Location: /volontario/error-500');
    exit;
}

// Ottieni dati utente
$rowUser = $dbAccess->getUtentePerId($id_utente_target);

if (!$rowUser) {
    $dbAccess->closeDBConnection();
    http_response_code(404);
    include ROOT_PATH . '/php/error_404.php'; // o templates/error_404.html
    exit;
}

// Statistiche recensioni
$rowStats = $dbAccess->getStatisticheRecensioni($id_utente_target);
$resultRec = $dbAccess->getRecensioniRicevute($id_utente_target);

$dbAccess->closeDBConnection();

// Prepara dati utente
$foto_fs = ROOT_PATH . '/src/images/uploads/' . ($rowUser['immagine'] ?? '');
$foto_url = (!empty($rowUser['immagine']) && file_exists($foto_fs)) 
    ? BASE_PATH . '/src/images/uploads/' . $rowUser['immagine'] 
    : BASE_PATH . '/src/images/default.png';

$dati_utente = [
    'id'             => $rowUser['idPersona'],
    'nome'           => $rowUser['nome'],
    'cognome'        => $rowUser['cognome'],
    'username'       => $rowUser['username'],
    'email'          => $rowUser['email'],
    'foto'           => $foto_url,
    'nascita'        => $rowUser['dataNascita'],
    'citta'          => !empty($rowUser['nome_citta']) ? $rowUser['nome_citta'] . " (" . $rowUser['provincia'] . ")" : "Non specificata",
    'ruolo'          => ucfirst($rowUser['ruolo']),
    'descrizione'    => $rowUser['descrizione'] ?? 'Nessuna descrizione inserita.',
    'media_voto'     => round($rowStats['media_voto'] ?? 0, 1),
    'num_recensioni' => $rowStats['num_recensioni']
];

$data_obj = new DateTime($dati_utente['nascita']);
$data_visual = $data_obj->format('d/m/Y');
$descrizione_html = nl2br(htmlspecialchars($dati_utente['descrizione']));

// Pulsanti Azioni
$azioni_html = "";
if ($id_visitatore === $dati_utente['id']) {
    $azioni_html = '<a href="/volontario/modifica-profilo?id='. $dati_utente['id'] . '" class="btn modifyProfile">Modifica Profilo</a>';
} else {
    $azioni_html = '<a href="mailto:' . $dati_utente['email'] . '" class="btn contactUser">Contatta Utente</a>';
}

// Sezione reputazione
$html_reputazione = "";
if ($dati_utente['num_recensioni'] > 0) {
    $html_reputazione = file_get_contents(ROOT_PATH . '/templates/components/valuation.html');
    $percentuale_stelle = ($dati_utente['media_voto'] * 10) . "%";

    $html_reputazione = str_replace('[MediaVoto]',         $dati_utente['media_voto'], $html_reputazione);
    $html_reputazione = str_replace('[NumRecensioni]',     $dati_utente['num_recensioni'], $html_reputazione);
    $html_reputazione = str_replace('[PercentualeStelle]', $percentuale_stelle, $html_reputazione);
}

// Sezione recensioni
$html_recensioni_output = "";
if (!empty($resultRec)) {
    $template_item = file_get_contents(ROOT_PATH . '/templates/components/review.html');

    foreach ($resultRec as $rec) {
        $foto_fs = ROOT_PATH . '/src/images/' . ($rec['foto_autore'] ?? '');
        $foto_autore = (!empty($rec['foto_autore']) && file_exists($foto_fs)) 
            ? BASE_PATH . '/src/images/' . $rec['foto_autore'] 
            : BASE_PATH . '/src/images/default.png';

        $date_rec_obj = new DateTime($rec['dataRichiesta']);

        $temp = $template_item;
        $temp = str_replace('[IdAutore]',         $rec['id_autore'], $temp);
        $temp = str_replace('[AutoreRecensione]', htmlspecialchars($rec['nome_autore']), $temp);
        $temp = str_replace('[FotoAutore]',       $foto_autore, $temp);
        $temp = str_replace('[VotoRecensione]',   $rec['voto'], $temp);
        $temp = str_replace('[TestoRecensione]',  htmlspecialchars($rec['testo_recensione']), $temp);
        $temp = str_replace('[DataISO]',          $date_rec_obj->format('Y-m-d'), $temp);
        $temp = str_replace('[DataVisual]',       $date_rec_obj->format('d/m/Y'), $temp);
        $temp = str_replace('[CategoriaServizio]', htmlspecialchars($rec['nome_categoria']), $temp);

        $html_recensioni_output .= $temp;
    }
} else {
    $html_recensioni_output = file_get_contents(ROOT_PATH . '/templates/components/no_review.html');
}

// Template profilo
$html_profilo = file_get_contents(ROOT_PATH . '/templates/profile.html');

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

foreach ($placeholders_profilo as $chiave => $valore) {
    $html_profilo = str_replace($chiave, $valore, $html_profilo);
}

// Layout generale
$pagina = file_get_contents(realpath(__DIR__ . '/' . '../templates/blueprint.html'));
$footer_html = file_exists(ROOT_PATH . '/templates/components/footer.html') ? file_get_contents(ROOT_PATH . '/templates/components/footer.html') : '';

$placeholders_layout = [
    '[Titolo Pagina]' => 'Profilo di ' . htmlspecialchars($dati_utente['username']),
    '[Description]'   => 'Visualizza il profilo di ' . htmlspecialchars($dati_utente['nome']) . ' su VolontarIO',
    '[Keywords]'      => 'volontariato, profilo, ' . htmlspecialchars($dati_utente['citta']),
    '[pageNameId]'    => 'accountPage',
    '[onload]'        => '',
    '[Header]'        => $header_html,
    '[Footer]'        => $footer_html,
    '[MainContent]'   => $html_profilo
];

foreach ($placeholders_layout as $chiave => $valore) {
    $pagina = str_replace($chiave, $valore, $pagina);
}

echo $pagina;
?>
