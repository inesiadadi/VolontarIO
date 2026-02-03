<?php
include('header.php');

use DB\DBAccess;

$id_visitatore = $_SESSION['user_id'] ?? 0;

if($id_visitatore === 0) {
    header('Location: /volontario/login');
    exit;
}

$id_utente_target = $id_visitatore;

$dbAccess = new DBAccess();
if (!$dbAccess->openDBConnection()) {
    
    header('Location: /volontario/error-500');
    exit;
}

$utente = $dbAccess->getUtentePerId($id_utente_target);
if(!$utente) {
    http_response_code(404);
    include ROOT_PATH . '/php/error404.php';
    exit;
}

$categories = $dbAccess->getTipologie();

$dbAccess->closeDBConnection();

$html = file_get_contents(ROOT_PATH . '/templates/nuovaRichiesta.html');

$tipologie = "";
foreach($categories as $cat) {
    $tipologie .= '<option value="' . $cat['idTipo'] . '">' . $cat['nome'] . '</option>';
}

if(!isset($_SESSION['logged'])) {
    header('Location: /volontario/login/');
    exit;
} else {
    $data_minima = date('Y-m-d'); // Impedisce di selezionare date passate nel calendario browser

    $form = "
        <form id='richiestaForm' class='accessForm' action='/volontario/nuova-richiesta' method='post'>
            <fieldset>
                <legend>Crea una nuova richiesta</legend>
                <label for='categoria'>Categoria di aiuto:</label>
                <select name='idTipo' id='categoria' required aria-required='true'>
                    <option value='' disabled selected>Scegli una categoria...</option>
                    $tipologie
                </select>
                <label for='descrizione'>Breve descrizione:</label>
                <textarea name='descrizione' id='descrizione' maxlength='255' required aria-required='true' aria-describedby='aiuto-desc'></textarea>
                <span id='aiuto-desc' class='aiuto'>Spiegare brevemente di cosa si ha bisogno (massimo 255 caratteri)</span>
                <div class='autocomplete-container'>
                        <label for='citta-input'>Città di residenza:</label>

                        <input
                            type='text'
                            id='citta-input'
                            name='citta_nome'
                            autocomplete='off'
                            role='combobox'
                            aria-autocomplete='list'
                            aria-expanded='false'
                            aria-controls='suggerimenti-citta'
                            aria-describedby='aiuto-citta'
                        >

                        <ul
                            id='suggerimenti-citta'
                            role='listbox'
                            tabindex='-1'
                            hidden
                        ></ul>

                        <input type='hidden' name='idCitta' id='idCitta-input'>

                        <div id='status-citta' class='sr-only' aria-live='polite'></div>
                    </div>

                    <span id='aiuto-citta' class='aiuto'>
                        Digita il comune e usa le frecce su e giù per selezionare.
                    </span>
                <label for='data'>Data dell'intervento:</label>
                <input type='date' name='dataRichiesta' id='data' min='$data_minima' required aria-required='true' />
                <span class='aiuto'>Specificare la data in cui si richiede il servizio</span>

                <label for='ora'>Orario richiesto:</label>
                <input type='time' name='ora' id='ora' required aria-required='true' />
                <span class='aiuto'>Specificare l'orario in cui si richiede il servizio</span>
            </fieldset>
            <button type='submit' id='createRequest'>Invia Richiesta</button>
        </form>
        <p>Non hai più bisogno di aiuto? <a href='/volontario/bacheca-richieste'>Torna alla Bacheca</a></p>";

    $html = str_replace('[Form]', $form, $html);
}

$descrizione = '';
$idRichiedente = '';
$dataRichiesta = '';
$idTipo = '';
$idCitta = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descrizione = $_POST['descrizione'];
    $dataRichiesta = $_POST['dataRichiesta'] . " " . $_POST['ora'] . ":00";
    $idTipo = $_POST['idTipo'];
    $idCitta = $_POST['idCitta'];
    $idRichiedente = $_SESSION['user_id'];

    if(!$dbAccess->openDBConnection()) {
        
        include ROOT_PATH . '/php/error500.php';
        exit;
    }

    $query = $dbAccess->aggiungiNuovaRichiesta($descrizione, $dataRichiesta, $idRichiedente, $idTipo, $idCitta);

    $dbAccess->closeDBConnection();
}
$pagina = file_get_contents(ROOT_PATH . '/templates/blueprint.html');
$footer_html = file_get_contents(ROOT_PATH . '/templates/components/footer.html');

$placeholders_layout = [
    '[Titolo Pagina]' => 'Creazione nuova richiesta',
    '[Description]'  => 'Pagina di creazione di richieste di VolontarIO',
    '[Keywords]'     => 'creazione, richiesta, nuovo, aiuto',
    '[pageNameId]'   => 'newRequestPage',
    '[onload]'       => '',
    '[Header]'       => $header_html,
    '[Footer]'       => $footer_html,
    '[MainContent]'  => $html

];

foreach($placeholders_layout as $chiave => $valore) {
    $pagina = str_replace($chiave, $valore, $pagina);
}

echo $pagina;

?>
