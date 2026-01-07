<?php
include('header.php');
use DB\DBAccess;


$id_utente_loggato = $_SESSION['user_id'] ?? 0;
$is_logged_in = ($id_utente_loggato > 0);

$dbAccess = new DBAccess();
if (!$dbAccess->openDBConnection()) {
    http_response_code(500);
    echo file_get_contents(ROOT_PATH . '/templates/components/error_500.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accetta_id']) && $is_logged_in) {
    $id_to_accept = intval($_POST['accetta_id']);
    $dbAccess->accettaRichiesta($id_to_accept, $id_utente_loggato);
}

$filtro_data   = isset($_GET['date']) ? $_GET['date'] : '';
$filtro_orario = isset($_GET['time']) ? $_GET['time'] : '';
$filtro_citta  = isset($_GET['city']) ? trim($_GET['city']) : '';
$filtro_cats   = isset($_GET['category']) ? $_GET['category'] : [];

$richieste = $dbAccess->getRichieste($filtro_data, $filtro_citta, $filtro_cats);
$dbAccess->closeDBConnection();

$html_categories = file_get_contents(ROOT_PATH . '/templates/components/checkboxCategories.html');
foreach ($filtro_cats as $id_cat) {
    $id_cat = intval($id_cat);
    $search = 'value="' . $id_cat . '"';
    $replace = 'value="' . $id_cat . '" checked';
    $html_categories = str_replace($search, $replace, $html_categories);
}

$html_richieste_output = "";
$msg_vuoto = "";


if (!empty($richieste)) {
    $template_item = file_get_contents(ROOT_PATH . '/templates/components/request.html');

    foreach($richieste as $req) {
        if ($is_logged_in && $req['id_utente_creatore'] == $id_utente_loggato) {
            continue; 
        }

        $temp = $template_item;
        
        $data_visual = function_exists('formatDateItalian') ? formatDateItalian($req['data_pubblicazione']) : date('d/m/Y', strtotime($req['data_pubblicazione']));              
        $orario_visual = date('H:i', strtotime($req['data_pubblicazione']));
        $foto_creatore = (!empty($req['foto_utente']) && file_exists(BASE_PATH . '/src/images/' . $req['foto_utente'])) ? BASE_PATH . '/src/images/' . $req['foto_utente'] : BASE_PATH . '/src/images/default.png';

        $temp = str_replace('[IdRichiesta]', $req['id'], $temp);
        $temp = str_replace('[Descrizione]', htmlspecialchars($req['descrizione']), $temp);
        $temp = str_replace('[NomeCreatore]', htmlspecialchars($req['nome_utente']), $temp);
        $temp = str_replace('[FotoCreatore]', $foto_creatore, $temp);
        $temp = str_replace('[Categoria]', htmlspecialchars($req['nome_categoria']), $temp);
        $temp = str_replace('[Luogo]', htmlspecialchars($req['nome_citta'] ?? 'Italia'), $temp); 
        $temp = str_replace('[DataVisual]', $data_visual, $temp);
        $temp = str_replace('[Orario]', $orario_visual, $temp);


        if (!$is_logged_in) {
            $btn_html = '
            <form action="/volontario/login" method="get">
                <button type="submit" class="btn-action btn-guest">Accedi per aiutare</button>
            </form>';
        } elseif (isset($req['stato']) && $req['stato'] === 'in_carico' && $req['idOfferente'] == $id_utente_loggato) {
            $btn_html = '<button class="btn-action" disabled>Prenotato!</button>';
        } else {
            $btn_html = '
            <form action="/volontario/bacheca" method="post">
                <input type="hidden" name="accetta_id" value="' . $req['id'] . '">
                <button type="submit" class="btn-action">Offri Aiuto</button>
            </form>';
        }
        $temp = str_replace('[ActionButton]', $btn_html, $temp);
        $html_richieste_output .= $temp;
    }
} 

if (empty($html_richieste_output)) {
    $msg_vuoto = '
    <div class="noRequestMsg" id="filterEmptyState">
        <span aria-hidden="true">🌱</span>
        <p>Non ci sono ancora richieste per questo filtro.</p>
    </div>';
}


$html_bacheca_page = file_get_contents(ROOT_PATH . '/templates/bachecaPage.html'); 
$html_bacheca_page = str_replace('[CheckboxesCategorie]', $html_categories, $html_bacheca_page);
$html_bacheca_page = str_replace('[ValoreCitta]', htmlspecialchars($filtro_citta), $html_bacheca_page);
$html_bacheca_page = str_replace('[ValoreData]',  htmlspecialchars($filtro_data), $html_bacheca_page);
$html_bacheca_page = str_replace('[ValoreOrario]', htmlspecialchars($filtro_orario), $html_bacheca_page);
$html_bacheca_page = str_replace('[ListaRichieste]', $html_richieste_output, $html_bacheca_page);
$html_bacheca_page = str_replace('[MessaggioVuoto]', $msg_vuoto, $html_bacheca_page);

$pagina = file_get_contents(ROOT_PATH . '/templates/blueprint.html');
$footer_html = file_exists(ROOT_PATH . '/templates/components/footer.html') ? file_get_contents(ROOT_PATH . '/templates/components/footer.html') : '';

$placeholders_layout = [
    '[Titolo Pagina]' => 'Bacheca Richieste',
    '[Description]'   => 'Visualizza e filtra le richieste di aiuto della community.',
    '[Keywords]'      => 'volontariato, bacheca, aiuto, richieste',
    '[pageNameId]'    => 'bacheca', 
    '[onload]'        => '',            
    '[Header]'        => $header_html,
    '[Footer]'        => $footer_html,
    '[MainContent]'   => $html_bacheca_page
];

foreach($placeholders_layout as $chiave => $valore) {
    $pagina = str_replace($chiave, $valore, $pagina);
}

echo $pagina;
?>