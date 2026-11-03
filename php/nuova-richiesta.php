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

$html = file_get_contents(ROOT_PATH . '/templates/nuovaRichiesta.html');

$tipologie = "";
foreach($categories as $cat) {
    $tipologie .= '<option value="' . $cat['idTipo'] . '">' . $cat['nome'] . '</option>';
}
$data_minima = date('Y-m-d'); // Impedisce di selezionare date passate nel calendario browser

$form = "
    [error_message]
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

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descrizione = trim($_POST['descrizione'] ?? '');
    $dataRichiesta = $_POST['dataRichiesta'] . " " . $_POST['ora'] . ":00";
    $idTipo = filter_input(INPUT_POST, 'idTipo', FILTER_VALIDATE_INT);
    $idCitta = filter_input(INPUT_POST, 'idCitta', FILTER_VALIDATE_INT);
    $data = $_POST['dataRichiesta'];
    $ora = $_POST['ora'];
    $idRichiedente = $_SESSION['user_id'];
    $messaggio = [];

    if(empty($descrizione) || strlen($descrizione) >= 255) {
        $errori[] = "La descrizione è obbligatoria (<abbr title='massimo'>max</abbr> 255 caratteri)";
    }
    
    if(!$idTipo) {
        $errori[] = "Categoria non valida";
    }

    if(!$idCitta) {
        $errori[] = "Città non valida";
    }

    if(empty($data) || empty($ora)) {
        $errori[] = "Data o ora non validi";
    } else {
        $data_ora = strtotime($data . ' ' . $ora);
        $adesso = time();
        if($data_ora < $adesso) {
            $errori[] = "Data o ora non validi (La richiesta non può essere nel passato)";
        }
    }
    if(empty($errori)) {
        $query = $dbAccess->aggiungiNuovaRichiesta($descrizione, $dataRichiesta, $idRichiedente, $idTipo, $idCitta);
        $dbAccess->closeDBConnection();
        header("Location: /volontario/richieste?id=" . $idRichiedente);
    } else {
        $messaggio = "<div class='error' role='alert'><ul>";
        foreach ($errori as $errore) {
            $messaggio .= "<li>" . $errore . "</li>";
        }
        $messaggio .= "</ul></div>";
        $html = str_replace('[error_message]', $messaggio, $html);
    }


} else {
    $html = str_replace('[error_message]', "", $html);
}

$descrizione = '';
$idRichiedente = '';
$dataRichiesta = '';
$idTipo = '';
$idCitta = '';

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
