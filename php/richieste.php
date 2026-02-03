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
    include ROOT_PATH . '/php/error_500.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rimuovi'])) {
    echo "Codice richiesta da eliminare: " . $_POST['codiceRichiesta'];
    $dbAccess->rimuoviRichiesta($_POST['codiceRichiesta']);
    header("Location: /volontario/richieste?id=" . $id_utente_target);
}

// Ottieni dati utente
$rowUser = $dbAccess->getUtentePerId($id_utente_target);

if (!$rowUser) {
    $dbAccess->closeDBConnection();
    http_response_code(404);
    include ROOT_PATH . '/php/error_404.php';
    exit;
}

//servirà una query per ottenere tutte le richieste fatte da uno specifico utente
$dbAccess->checkUpdateRequests();
$resultRec = $dbAccess->getRichiesteEffettuate($id_utente_target);

$dbAccess->closeDBConnection();

$html_richieste_output = "";

// Bisogna creare tutta la griglia per richiesta che viene creata
if(!empty($resultRec)) {
    $template_item = file_get_contents(ROOT_PATH . '/templates/components/myRequest.html');

    foreach($resultRec as $rec) {
        $temp = $template_item;
        $date_rec_obj = new DateTime($rec['dataRichiesta']);
        $data_visual = function_exists('formatDateItalian') ? formatDateItalian($rec['dataRichiesta']) : $date_rec_obj->format('d/m/Y');
        
        $temp = str_replace('[DataVisual]',       $data_visual, $temp); 
        $temp = str_replace('[OrarioRichiesta]', date('H:i', strtotime($rec['dataRichiesta'])), $temp);
        $temp = str_replace('[CategoriaServizio]', htmlspecialchars($rec['tipologia']), $temp);
        $temp = str_replace('[DescrizioneRichiesta]', htmlspecialchars($rec['descrizione']), $temp);
        $temp = str_replace('[LuogoRichiesta]', htmlspecialchars($rec['citta']), $temp);
        $temp = str_replace('[StatoRichiesta]', htmlspecialchars($rec['stato']), $temp);

        if ($rec['stato'] == "aperta") {
            $temp = str_replace(
                '[PulsantieraAzioni]',
                '<a href="/volontario/modificaRichiesta?id=' . $rec['codiceRichiesta'] . '"class="modify">Modifica Richiesta</a>',
                $temp
            );
        } 
        else if ($rec['stato'] == "in_carico") {
            // Stato in carico: pulsante cliccabile verso il profilo dell'offerente
            $profiloUrl = "/volontario/profilo?id=" . $rec['idOfferente'];
            $temp = str_replace(
                '[PulsantieraAzioni]',
                '<a href="' . $profiloUrl . '" class="btn-action btn-accept">Visualizza volontario</a>
                <form action="/volontario/richieste?id="' . $id_utente_target . ' method="post" >
                    <input type="hidden" name="codiceRichiesta" value='. $rec['codiceRichiesta'] .'>
                    <button type="submit" name="rimuovi" class="btn-action btn-delete">Rimuovi richiesta</button>
                 </form>',
                $temp
            );
        } 
        else if ($rec['stato'] === "completata") {

            $codiceRichiesta = (int)$rec['codiceRichiesta'];
            $dbAccess->openDBConnection();
            if ($dbAccess->esisteRecensionePerRichiesta($codiceRichiesta)) {

                $temp = str_replace(
                    '[PulsantieraAzioni]',
                    "<span class='profileBtn disabled'>Valutazione effettuata</span>",
                    $temp
                );

            } else {

                $reviewUrl = "/volontario/nuova-recensione?codiceRichiesta=" . $codiceRichiesta;

                $temp = str_replace(
                    '[PulsantieraAzioni]',
                    "<a href='{$reviewUrl}' class='profileBtn'>Recensisci volontario</a>",
                    $temp
                );
            }
        }
        else if ($rec['stato'] == "scaduta") {
            // Stato aperto: pulsante disabilitato
            $temp = str_replace(
                '[PulsantieraAzioni]',
                '<span class="disabledBtn" disabled>Nessun volontario</span>',
                $temp
            );
        } 

        $html_richieste_output .= $temp;
    }
}
$html_pagina_richieste = file_get_contents(ROOT_PATH . '/templates/requestsPage.html');
$categorie = file_exists(ROOT_PATH . '/templates/components/checkboxCategories.html') ? file_get_contents(ROOT_PATH . '/templates/components/checkboxCategories.html') : '';
$html_pagina_richieste = str_replace('[CheckboxesCategorie]', $categorie, $html_pagina_richieste);
$html_pagina_richieste = str_replace('[Richieste]', $html_richieste_output, $html_pagina_richieste);
$html_pagina_richieste = str_replace('[idUtenteTarget]', $id_utente_target, $html_pagina_richieste);

$pagina = file_get_contents(ROOT_PATH . '/templates/blueprint.html');
$footer_html = file_exists(ROOT_PATH . '/templates/components/footer.html') ? file_get_contents(ROOT_PATH . '/templates/components/footer.html') : '';

$placeholders_layout = [
    '[Titolo Pagina]' => 'Richieste di ' . htmlspecialchars($rowUser['username']) . ' | VolontarIO',
    '[Description]'   => 'Elenco delle richieste effettuate da ' . htmlspecialchars($rowUser['nome']),
    '[Keywords]'      => 'richieste, aiuti, necessità ' . htmlspecialchars($rowUser['nome']),
    '[pageNameId]'    => 'requestsPage', 
    '[onload]'        => '',            
    '[Header]'        => $header_html,
    '[Footer]'        => $footer_html,
    '[MainContent]'   => $html_pagina_richieste 
];

foreach($placeholders_layout as $chiave => $valore) {
    $pagina = str_replace($chiave, $valore, $pagina);
}

echo $pagina;
?>
