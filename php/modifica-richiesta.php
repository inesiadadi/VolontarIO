<?php
include('header.php');

use DB\DBAccess;

$id_visitatore = $_SESSION['user_id'] ?? 0;

if($id_visitatore === 0) {
    header('Location: /volontario/login');
    exit;
}

$id_richiesta = $_GET['id'] ?? 0;

if($id_richiesta === 0) {
    header('Location: /volontario/richieste');
    exit;
}

$dbAccess = new DBAccess();
if (!$dbAccess->openDBConnection()) {
    http_response_code(500);
    include ROOT_PATH . '/php/error_500.php';
    exit;
}

$richiesta = $dbAccess->getRichiestaPerId($id_richiesta);
$categories = $dbAccess->getTipologie(); 
$dbAccess->closeDBConnection();

if(!$richiesta) {
    http_response_code(404);
    include ROOT_PATH . '/php/error_404.php';
    exit;
}

if($richiesta['idRichiedente'] != $id_visitatore) {
    header('Location: /volontario/richieste');
    exit;
}

$timestamp = strtotime($richiesta['dataRichiesta']);
$data_db = date('Y-m-d', $timestamp);
$ora_db  = date('H:i', $timestamp);

$dati_richiesta = [
    'descrizione' => $richiesta['descrizione'],
    'idTipo'      => $richiesta['idTipologia'], 
    'nomeCitta'   => $richiesta['citta'], 
    'idCitta'     => $richiesta['idCitta'],
    'data'        => $data_db,
    'ora'         => $ora_db
];

$messaggio = "";

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $descrizione = !empty($_POST['descrizione'])   ? trim($_POST['descrizione']) : $dati_richiesta['descrizione'];
    $idTipo      = !empty($_POST['idTipo'])        ? $_POST['idTipo']            : $dati_richiesta['idTipo'];
    $idCitta     = !empty($_POST['idCitta'])       ? $_POST['idCitta']           : $dati_richiesta['idCitta'];
    $citta_nome  = !empty($_POST['citta_nome'])    ? trim($_POST['citta_nome'])        : $dati_richiesta['nomeCitta'];

    $data   = !empty($_POST['dataRichiesta']) ? $_POST['dataRichiesta']     : $dati_richiesta['data'];
    $ora    = !empty($_POST['ora'])           ? $_POST['ora']               : $dati_richiesta['ora'];
    $dataIntervento = $data . " " . $ora;

    $errori = [];

    if(strlen($descrizione) > 255) {
        $errori[] = "La descrizione è troppo lunga (max 255 caratteri).";
    }

    if(!$idTipo || filter_var($idTipo, FILTER_VALIDATE_INT) === false) {
        $errori[] = "Categoria non valida.";
    }

    if(!$idCitta || filter_var($idTipo, FILTER_VALIDATE_INT) === false) {
        $errori[] = "Città non valida.";
    }

    if(empty($data) || empty($ora)) {
        $errori[] = "Data o ora non validi.";
    } else {
        $data_ora = strtotime($data . ' ' . $ora);
        $adesso = time();
        if($data_ora < $adesso) {
            $errori[] = "Data o ora non validi (La richiesta non può essere nel passato).";
        }
    }

    if(!empty($errori)) {
        $messaggio = "<div class='error' role='alert'><ul>";
        foreach ($errori as $errore) {
            $messaggio .= "<li>" . $errore . "</li>";
        }
        $messaggio .= "</ul></div>";
        
        $dati_richiesta['descrizione'] = $descrizione;
        $dati_richiesta['nomeCitta']   = $citta_nome;
        $dati_richiesta['data']        = $data;
        $dati_richiesta['ora']         = $ora;

    } else if ($dbAccess->openDBConnection()) {
        
        $successo = $dbAccess->modificaRichiesta(
            $id_richiesta, 
            $descrizione, 
            $dataIntervento, 
            $idTipo, 
            $idCitta
        );
        
        $dbAccess->closeDBConnection();

        if($successo) {
            header("Location: /volontario/richieste?id=$id_visitatore");
            exit;
        } else {
             $messaggio = "<div class='error'>Errore generico nel salvataggio. Riprova.</div>";
        }
    }
}

$html = file_get_contents(ROOT_PATH . '/templates/modificaRichiesta.html');

$options_html = "";
foreach($categories as $cat) {
    $selected = ($cat['idTipo'] == $dati_richiesta['idTipo']) ? 'selected' : '';
    $options_html .= "<option value='" . e($cat['idTipo']) . "' $selected>" . e($cat['nome']) . "</option>";
}
$minDate = date('Y-m-d');

$form = "
$messaggio
<form class='accessForm' id='modifyRequestForm' action='/volontario/modifica-richiesta?id=$id_richiesta' method='post'>
     <fieldset>
        <legend>Dettagli richiesta</legend>   
        
        <label for='categoria'>Categoria di aiuto:</label>
        <select name='idTipo' id='categoria' required>
            $options_html
        </select>

        <label for='descrizione'>Descrizione:</label>
        <textarea name='descrizione' id='descrizione' maxlength='255' required aria-describedby='aiuto-desc'>" . e($dati_richiesta['descrizione']) . "</textarea>
        <span id='aiuto-desc' class='aiuto'>Aggiorna la descrizione (max 255 caratteri)</span>

        <div class='autocomplete-container'>
            <label for='citta-input'>Città di residenza:</label>
            <input
                type='text'
                id='citta-input'
                name='citta_nome'
                value='" . e($dati_richiesta['nomeCitta']) . "'
                autocomplete='off'
                role='combobox'
                aria-autocomplete='list'
                aria-expanded='false'
                aria-controls='suggerimenti-citta'
                aria-describedby='aiuto-citta'
            >
            <ul id='suggerimenti-citta' role='listbox' tabindex='-1' hidden></ul>
            <input type='hidden' name='idCitta' id='idCitta-input' value='" . e($dati_richiesta['idCitta']) . "'>
            <div id='status-citta' class='sr-only' aria-live='polite'></div>
        </div>
        <span id='aiuto-citta' class='aiuto'>Digita il comune e usa le frecce per selezionare.</span>

        <label for='data'>Data dell'intervento:</label>
        <input type='date' name='dataRichiesta' id='data' min='$minDate' value='" . e($dati_richiesta['data']) . "' required>
        <span class='aiuto'>Modifica la data richiesta</span>

        <label for='ora'>Orario richiesto:</label>
        <input type='time' name='ora' id='ora' value='" . e($dati_richiesta['ora']) . "' required>
        <span class='aiuto'>Modifica l'orario richiesto</span>

    </fieldset>
    <button type='submit'>Salva Modifiche</button>
</form>
<p>Vuoi annullare? <a href='/volontario/richieste?id=$id_visitatore'>Torna alle tue richieste</a></p>";

$html = str_replace('[Form]', $form, $html);

$pagina = file_get_contents(ROOT_PATH . '/templates/blueprint.html');
$footer_html = file_exists(ROOT_PATH . '/templates/components/footer.html') ? file_get_contents(ROOT_PATH . '/templates/components/footer.html') : '';

$placeholders_layout = [
    '[Titolo Pagina]' => 'Modifica Richiesta',
    '[Description]'   => 'Pagina di modifica richiesta',
    '[Keywords]'      => 'modifica, richiesta, aiuto',
    '[pageNameId]'    => 'modifyRequestPage',
    '[onload]'        => 'onload="initCittaAutocomplete()"', 
    '[Header]'        => $header_html,
    '[Footer]'        => $footer_html,
    '[MainContent]'   => $html 
];

foreach($placeholders_layout as $chiave => $valore) {
    $pagina = str_replace($chiave, $valore, $pagina);
}

echo $pagina;
?>