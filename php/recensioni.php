<?php

use DB\DBAccess;

include('header.php');
$id_visitatore = $_SESSION['user_id'] ?? 0;

if ($id_visitatore === 0) {
    header("Location: /volontario/login"); // O redirect alla Home
    exit;
}

$id_utente_target = $id_visitatore;
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

$resultRec = $dbAccess->getRecensioniRicevute($id_utente_target);

$dbAccess->closeDBConnection();

$html_recensioni_output = "";

if (!empty($resultRec)) {
    $template_item = file_get_contents(ROOT_PATH . '/templates/components/review.html');

    foreach($resultRec as $rec) {
        $foto_autore = (!empty($rec['foto_autore']) && file_exists(BASE_PATH . '/src/images/' . $rec['foto_autore'])) ? BASE_PATH . '/src/images/' . $rec['foto_autore'] : BASE_PATH . '/src/images/default.png';
        $date_rec_obj = new DateTime($rec['dataRichiesta']);
        $data_visual = function_exists('formatDateItalian') ? formatDateItalian($rec['dataRichiesta']) : $date_rec_obj->format('d/m/Y');

        $temp = $template_item;
        $temp = str_replace('[IdAutore]',         $rec['id_autore'], $temp);
        $temp = str_replace('[AutoreRecensione]', htmlspecialchars($rec['nome_autore']), $temp);
        $temp = str_replace('[FotoAutore]',       $foto_autore, $temp);
        $temp = str_replace('[VotoRecensione]',   $rec['voto'], $temp);
        $temp = str_replace('[TestoRecensione]',  htmlspecialchars($rec['testo_recensione']), $temp);
        $temp = str_replace('[DataISO]',          $date_rec_obj->format('Y-m-d'), $temp);
        $temp = str_replace('[DataVisual]',       $data_visual, $temp);
        $temp = str_replace('[CategoriaServizio]', htmlspecialchars($rec['nome_categoria']), $temp);

        $html_recensioni_output .= $temp;
    }
} else {
    $html_recensioni_output = file_get_contents('templates/components/no_review.html');
}

$html_pagina_recensioni = file_get_contents('templates/reviewsPage.html');
$html_pagina_recensioni = str_replace('[Recensioni]', $html_recensioni_output, $html_pagina_recensioni);
$categorie = file_exists('templates/components/categories.html') ? file_get_contents('templates/components/categories.html') : '';
$html_pagina_recensioni = str_replace('[Categorie]', $categorie, $html_pagina_recensioni);

$pagina = file_get_contents('templates/blueprint.html');
$footer_html = file_exists('templates/components/footer.html') ? file_get_contents('templates/components/footer.html') : '';

$placeholders_layout = [
    '[Titolo Pagina]' => 'VolontarIO | Recensioni di ' . htmlspecialchars($rowUser['username']),
    '[Description]'   => 'Elenco delle recensioni ricevute da ' . htmlspecialchars($rowUser['nome']),
    '[Keywords]'      => 'recensioni, feedback, volontariato, ' . htmlspecialchars($rowUser['nome']),
    '[pageNameId]'    => 'reviewsPage', 
    '[onload]'        => '',            
    '[Header]'        => $header_html,
    '[Footer]'        => $footer_html,
    '[MainContent]'   => $html_pagina_recensioni
];

foreach($placeholders_layout as $chiave => $valore) {
    $pagina = str_replace($chiave, $valore, $pagina);
}

echo $pagina;
?>
