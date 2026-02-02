<?php


include('header.php');

use DB\DBAccess;

$id_visitatore = $_SESSION['user_id'] ?? 0;

if ($id_visitatore === 0) {
    header('Location: /volontario/login');
    exit;
}

$id_utente_target = $_GET['id'] ?? 0;

if($id_utente_target === 0 || $id_utente_target != $id_visitatore) {
    header('Location: /volontario/');
    exit;
}

$dbAccess = new DBAccess();
if (!$dbAccess->openDBConnection()) {
    http_response_code(500);
    include ROOT_PATH . '/php/error_500.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rinuncia'])) {
    $dbAccess->rinunciaCandidatura($_POST['codiceRichiesta']);
    header("Location: /volontario/candidature");
    exit;
}

$rowUser = $dbAccess->getUtentePerId($id_utente_target);

if (!$rowUser) {
    $dbAccess->closeDBConnection();
    http_response_code(404);
    include ROOT_PATH . '/php/error_404.php';
    exit;
}

$dbAccess->checkUpdateRequests();
$resultRec = $dbAccess->getCandidatureEffettuate($id_utente_target);

$dbAccess->closeDBConnection();

$html_candidature_output = "";

if(!empty($resultRec)) {
    $template_item = file_get_contents(ROOT_PATH . '/templates/components/myCandidature.html');

    foreach($resultRec as $rec) {
        $temp = $template_item;
        $date_rec_obj = new DateTime($rec['dataRichiesta']);
        $data_visual = function_exists('formatDateItalian') ? formatDateItalian($rec['dataRichiesta']) : $date_rec_obj->format('d/m/Y');
        $orario_visual = isset($rec['orario']) ? substr($rec['orario'], 0, 5) : "ND";

        $temp = str_replace('[DataVisual]',       $data_visual, $temp); 
        $temp = str_replace('[OrarioRichiesta]',  $orario_visual, $temp);
        $temp = str_replace('[CategoriaServizio]', htmlspecialchars($rec['tipologia']), $temp);
        $temp = str_replace('[DescrizioneRichiesta]', htmlspecialchars($rec['descrizione']), $temp);
        $temp = str_replace('[LuogoRichiesta]', htmlspecialchars($rec['citta']), $temp);
        $temp = str_replace('[StatoRichiesta]', htmlspecialchars($rec['stato']), $temp);

        if ($rec['stato'] == "in_carico") {
            $temp = str_replace(
                '[PulsantieraAzioni]',
                '<form action="/volontario/candidature" method="post">
                    <input type="hidden" name="codiceRichiesta" value="'. $rec['codiceRichiesta'] .'">
                    <button type="submit" name="rinuncia" class="profileBtn delete-btn">Rinuncia incarico</button>
                 </form>',
                $temp
            );
        } 
        else if ($rec['stato'] == "completata") {
            $temp = str_replace(
                '[PulsantieraAzioni]',
                '<button class="disabledBtn" disabled>Completata</button>',
                $temp
            );
        }
        else {
             $temp = str_replace(
                '[PulsantieraAzioni]',
                '<button class="disabledBtn" disabled>'.htmlspecialchars($rec['stato']).'</button>',
                $temp
            );
        }

        $html_candidature_output .= $temp;
    }
} else {
    $no_req_path = ROOT_PATH . '/templates/components/no_candidature.html';
    if(file_exists($no_req_path)) {
        $html_candidature_output = file_get_contents($no_req_path);
    } else {
        $html_candidature_output = '<p class="no-results">Nessuna candidatura presente.</p>';
    }
}

$html_pagina_candidature = file_get_contents(ROOT_PATH . '/templates/candidaturePage.html');
$categorie = file_exists(ROOT_PATH . '/templates/components/checkboxCategories.html') ? file_get_contents(ROOT_PATH . '/templates/components/checkboxCategories.html') : '';

$html_pagina_candidature = str_replace('[CheckboxesCategorie]', $categorie, $html_pagina_candidature);
$html_pagina_candidature = str_replace('[Candidature]', $html_candidature_output, $html_pagina_candidature);
$html_pagina_candidature = str_replace('[idUtenteTarget]', $id_utente_target, $html_pagina_candidature);

$pagina = file_get_contents(ROOT_PATH . '/templates/blueprint.html');
$footer_html = file_exists(ROOT_PATH . '/templates/components/footer.html') ? file_get_contents(ROOT_PATH . '/templates/components/footer.html') : '';

$placeholders_layout = [
    '[Titolo Pagina]' => 'VolontarIO | Candidature di ' . htmlspecialchars($rowUser['username']),
    '[Description]'   => 'Elenco delle candidature di ' . htmlspecialchars($rowUser['nome']),
    '[Keywords]'      => 'candidature, volontariato, ' . htmlspecialchars($rowUser['nome']),
    '[pageNameId]'    => 'candidaturePage', 
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
