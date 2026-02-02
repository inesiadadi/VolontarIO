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
    http_response_code(500);
    header('Location: error_500.php');
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
$id = (int) $dati_utente['id'];
$email = htmlspecialchars($dati_utente['email'], ENT_QUOTES, 'UTF-8');

if ($id_visitatore === $id) {
    $azioni_html = '<a href="/volontario/modifica-profilo?id=' . $id . '" class="btn modifyProfile">Modifica profilo</a>';
} else {
    $azioni_html = '<a href="mailto:' . $email . '" class="btn contactUser">Contatta utente</a>';
}

// Sezione reputazione
$html_reputazione = '';
$numRec = (int) ($dati_utente['num_recensioni'] ?? 0);
if ($numRec > 0) {
    $tpl = file_get_contents(ROOT_PATH . '/templates/components/valuation.html');
    $media = (float) ($dati_utente['media_voto'] ?? 0);
    if ($media < 0) $media = 0;
    if ($media > 10) $media = 10;
    
    $percent = $media * 10;
    if ($percent < 0) $percent = 0;
    if ($percent > 100) $percent = 100;

    $mediaStr   = htmlspecialchars(number_format($media, 1, '.', ''), ENT_QUOTES, 'UTF-8');
    $numRecStr  = htmlspecialchars((string)$numRec, ENT_QUOTES, 'UTF-8');
    $percentStr = htmlspecialchars(number_format($percent, 0, '.', '') . '%', ENT_QUOTES, 'UTF-8');

    $tpl = str_replace('[MediaVoto]', $mediaStr, $tpl);
    $tpl = str_replace('[NumRecensioni]', $numRecStr, $tpl);
    $tpl = str_replace('[PercentualeStelle]', $percentStr, $tpl);

    if($numRec == 1) {
        $tpl = str_replace('[NumeroRecensioni]', 'recensione', $tpl);
    } else {
        $tpl = str_replace('[NumeroRecensioni]', 'recensioni', $tpl);
    }
    $html_reputazione = $tpl;
}

// Sezione recensioni
$html_recensioni_output = "";
if (!empty($resultRec)) {
    $template_item = file_get_contents(ROOT_PATH . '/templates/components/review.html');
    if(count($resultRec) >= 3) $size = 3;
    else $size = count($resultRec);
    for($i = 0; $i < $size; $i++) {
        $rec = $resultRec[$i];
        $foto_file = $rec['foto_autore'] ?? '';
        $foto_fs = ROOT_PATH . '/src/images/' . $foto_file;
        $foto_autore = (!empty($foto_file) && file_exists($foto_fs))
            ? BASE_PATH . '/src/images/' . rawurlencode($foto_file)
            : BASE_PATH . '/src/images/default.png';

        try {
            $date_rec_obj = new DateTime($rec['dataRichiesta'] ?? 'now');
        } catch (Exception $e) {
            $date_rec_obj = new DateTime('now');
        }

        $reviewKey = (int)($rec['id_autore'] ?? 0) . '-' . $date_rec_obj->format('Ymd');
        $reviewKeyEsc = htmlspecialchars($reviewKey, ENT_QUOTES, 'UTF-8');

        $idAutore   = (string)(int)($rec['id_autore'] ?? 0);
        $autore     = htmlspecialchars($rec['nome_autore'] ?? 'Utente', ENT_QUOTES, 'UTF-8');
        $testo      = htmlspecialchars($rec['testo_recensione'] ?? '', ENT_QUOTES, 'UTF-8');
        $categoria  = htmlspecialchars($rec['nome_categoria'] ?? '', ENT_QUOTES, 'UTF-8');
        $voto = htmlspecialchars((string)($rec['voto'] ?? ''), ENT_QUOTES, 'UTF-8');

        $dataISO    = htmlspecialchars($date_rec_obj->format('Y-m-d'), ENT_QUOTES, 'UTF-8');
        $dataVisual = htmlspecialchars($date_rec_obj->format('d/m/Y'), ENT_QUOTES, 'UTF-8');

        $temp = $template_item;
        $temp = str_replace('[ReviewKey]',        $reviewKeyEsc, $temp);
        $temp = str_replace('[IdAutore]',         $idAutore, $temp);
        $temp = str_replace('[AutoreRecensione]', $autore, $temp);
        $temp = str_replace('[FotoAutore]',       htmlspecialchars($foto_autore, ENT_QUOTES, 'UTF-8'), $temp);
        $temp = str_replace('[VotoRecensione]',   $voto, $temp);
        $temp = str_replace('[TestoRecensione]',  $testo, $temp);
        $temp = str_replace('[DataISO]',          $dataISO, $temp);
        $temp = str_replace('[DataVisual]',       $dataVisual, $temp);
        $temp = str_replace('[CategoriaServizio]', $categoria, $temp);
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
