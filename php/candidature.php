<?php 
include('header.php');

use DB\DBAccess;

/*
$id_visitatore = $_SESSION['user_id'] ?? 0;

if ($id_visitatore === 0) {
    header("Location: /volontario/login"); // O redirect alla Home
    exit;
}

$id_utente_target = $id_visitatore;
*/
// Rimuovere queste righe quando funziona il collegamento
$id_visitatore = $_SESSION['user_id'] ?? 0;

// ID utente target
$id_utente_target = isset($_GET['id']) ? intval($_GET['id']) : $id_visitatore;
if ($id_utente_target === 0) {
    header("Location: /volontario/login");
    exit;
}

$dbAccess = new DBAccess();
if (!$dbAccess->openDBConnection()) {
    http_response_code(500);
    include ROOT_PATH . '/php/error_500.php';
    exit;
}

// Ottieni dati utente
$rowUser = $dbAccess->getUtentePerId($id_utente_target);

if (!$rowUser) {
    $dbAccess->closeDBConnection();
    http_response_code(404);
    include ROOT_PATH . '/php/error_404.php';
    exit;
}

$resultRec = $dbAccess->getCandidatureEffettuate($id_utente_target);
$dbAccess->closeDBConnection();

$html_candidature_output = "";
if(!empty($resultRec)) {
    $template_item = file_get_contents(ROOT_PATH . '/templates/components/myRequest.html');
    foreach($resultRec as $rec) {
        $temp = $template_item;
        $date_rec_obj = new DateTime($rec['dataRichiesta']);
        $data_visual = function_exists('formatDateItalian') ? formatDateItalian($rec['dataRichiesta']) : $date_rec_obj->format('d/m/Y');

        $temp = str_replace('[DataISO]',          $date_rec_obj->format('Y-m-d'), $temp);
        $temp = str_replace('[DataVisual]',       $data_visual, $temp); 
        $temp = str_replace('[CategoriaServizio]', htmlspecialchars($rec['tipologia']), $temp);
        $temp = str_replace('[DescrizioneRichiesta]', htmlspecialchars($rec['descrizione']), $temp);
        $temp = str_replace('[LuogoRichiesta]', htmlspecialchars($rec['citta']), $temp);
        $temp = str_replace('[StatoRichiesta]', htmlspecialchars($rec['stato']), $temp);
        $temp = str_replace('[PulsantieraAzioni]', '<button class="profileBtn">Elimina richiesta</button>', $temp);
    }
} else {
    $html_candidature_output = file_get_contents(ROOT_PATH . '/templates/components/no_request.html');  
}

$html_pagina_candidature = file_get_contents(ROOT_PATH . '/templates/servicesPage.html');
$categorie = file_exists(ROOT_PATH . '/templates/components/categories.html') ? file_get_contents(ROOT_PATH . '/templates/components/categories.html') : '';
$html_pagina_candidature = str_replace('[Categorie]', $categorie, $html_pagina_candidature);
$html_pagina_candidature = str_replace('[Candidature]', $html_candidature_output, $html_pagina_candidature);

$pagina = file_get_contents(ROOT_PATH . '/templates/blueprint.html');
$footer_html = file_exists(ROOT_PATH . '/templates/components/footer.html') ? file_get_contents(ROOT_PATH . '/templates/components/footer.html') : '';

$placeholders_layout = [
    '[Titolo Pagina]' => 'VolontarIO | Candidature di ' . htmlspecialchars($rowUser['username']),
    '[Description]'   => 'Elenco delle candidature effettuate da ' . htmlspecialchars($rowUser['nome']),
    '[Keywords]'      => 'candidature, aiuti, necessità ' . htmlspecialchars($rowUser['nome']),
    '[pageNameId]'    => 'servicesPage', 
    '[onload]'        => '',            
    '[Header]'        => $header_html,
    '[Footer]'        => $footer_html,
    '[MainContent]'   => $html_pagina_candidature 
];

foreach($placeholders_layout as $chiave => $valore) {
    $pagina = str_replace($chiave, $valore, $pagina);
}

echo $pagina;
?>