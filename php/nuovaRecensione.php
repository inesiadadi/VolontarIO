<?php

use DB\DBAccess;

include('header.php');

$id_visitatore = $_SESSION['user_id'] ?? 0;

if ($id_visitatore === 0) {
    header("Location: /volontario/login");
    exit;
}

$dbAccess = new DBAccess();
if (!$dbAccess->openDBConnection()) {
    http_response_code(500);
    include ROOT_PATH . '/php/error_500.php';
    exit;
}

function redirectHome(DBAccess $dbAccess): void
{
    $dbAccess->closeDBConnection();
    header("Location: /volontario/home");
    exit;
}

function redirectBacheca(DBAccess $dbAccess): void
{
    $dbAccess->closeDBConnection();
    header("Location: /volontario/bacheca-richieste");
    exit;
}

$idRichiesta = 0;
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $idRichiesta = (int)($_GET['idRichiesta'] ?? 0);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idRichiesta = (int)($_POST['idRichiesta'] ?? 0);
}

if ($idRichiesta <= 0) {
    $dbAccess->closeDBConnection();
    http_response_code(400);
    include ROOT_PATH . '/php/error_400.php';
    exit;
}

$richiesta = $dbAccess->getRichiestaPerId($idRichiesta);

if (!$richiesta) {
    $dbAccess->closeDBConnection();
    http_response_code(404);
    include ROOT_PATH . '/php/error_404.php';
    exit;
}

if ((int)$richiesta['idRichiedente'] !== (int)$id_visitatore) {
    redirectHome($dbAccess);
}

if (strtolower((string)$richiesta['stato']) !== 'completata') {
    redirectHome($dbAccess);
}

$idOfferente = (int)($richiesta['idOfferente'] ?? 0);
if ($idOfferente <= 0) {
    redirectHome($dbAccess);
}

if ($dbAccess->esisteRecensionePerRichiesta($idRichiesta)) {
    redirectBacheca($dbAccess);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voto = (int)($_POST['voto'] ?? 0);
    $descrizione = trim($_POST['descrizione'] ?? '');

    if ($voto < 1 || $voto > 5 || mb_strlen($descrizione) > 255) {
        $dbAccess->closeDBConnection();
        http_response_code(400);
        include ROOT_PATH . '/php/error_400.php';
        exit;
    }

    $ok = $dbAccess->inserisciRecensione(
        $idRichiesta,
        (int)$id_visitatore,
        (int)$idOfferente,
        (int)$voto,
        $descrizione
    );

    $dbAccess->closeDBConnection();

    if (!$ok) {
        http_response_code(500);
        include ROOT_PATH . '/php/error_500.php';
        exit;
    }

    header("Location: /volontario/bacheca-richieste");
    exit;
}

$utenteOfferente = $dbAccess->getUtentePerId($idOfferente);

$dbAccess->closeDBConnection();
$html = file_get_contents(ROOT_PATH . '/templates/nuovaRecensione.html');
if(!isset($_SESSION['logged'])) {
    header('Location: /volontario/login');
} else {
    $form = "
        <form id='recensioneForm' class='accessForm' action='/volontario/invia-recensione' method='post'>
            <fieldset>
                <legend>Lascia una recensione</legend>

                <p class='aiuto' id='aiuto-voto'>
                    Seleziona un voto da 1 a 5. Il voto è obbligatorio.
                </p>

            <div class='ratingGroup' role='radiogroup' aria-labelledby='label-voto' aria-describedby='aiuto-voto'>
              <span id='label-voto' class='ratingLabel'>Voto:</span>

              <div class='ratingList'>
                <label class='ratingRow'>
                  <span class='ratingNumber'>1</span>
                  <input type='radio' name='voto' value='1' required>
                </label>

                <label class='ratingRow'>
                  <span class='ratingNumber'>2</span>
                  <input type='radio' name='voto' value='2'>
                </label>

                <label class='ratingRow'>
                  <span class='ratingNumber'>3</span>
                  <input type='radio' name='voto' value='3'>
                </label>

                <label class='ratingRow'>
                  <span class='ratingNumber'>4</span>
                  <input type='radio' name='voto' value='4'>
                </label>

                <label class='ratingRow'>
                  <span class='ratingNumber'>5</span>
                  <input type='radio' name='voto' value='5'>
                </label>
                <label class='ratingRow'>
                  <span class='ratingNumber'>6</span>
                  <input type='radio' name='voto' value='6'>
                </label>

                <label class='ratingRow'>
                  <span class='ratingNumber'>7</span>
                  <input type='radio' name='voto' value='7'>
                </label>

                <label class='ratingRow'>
                  <span class='ratingNumber'>8</span>
                  <input type='radio' name='voto' value='8'>
                </label>

                <label class='ratingRow'>
                  <span class='ratingNumber'>9</span>
                  <input type='radio' name='voto' value='9'>
                </label>

                <label class='ratingRow'>
                  <span class='ratingNumber'>10</span>
                  <input type='radio' name='voto' value='10'>
                </label>
              </div>
            </div>

                <label for='descrizione'>Breve descrizione del servizio svolto:</label>
                <textarea
                    name='descrizione'
                    id='descrizione'
                    maxlength='255'
                    aria-describedby='aiuto-desc'
                ></textarea>
                <span id='aiuto-desc' class='aiuto'>
                    Racconta com'è andata (massimo 255 caratteri).
                </span>

                <input type='hidden' name='request_id' value='" . (int)($request_id ?? 0) . "'>
                <button type='submit'>Invia Recensione</button>
            </fieldset>

        </form>
        ";
    $html = str_replace('[Form]', $form, $html);
    }
    
$pagina = file_get_contents(ROOT_PATH . '/templates/blueprint.html');
$footer_html = file_get_contents(ROOT_PATH . '/templates/components/footer.html');

$placeholders_layout = [
    '[Titolo Pagina]' => 'Creazione nuova recensione',
    '[Description]'  => 'Pagina di creazione di recensioni di VolontarIO',
    '[Keywords]'     => 'creazione, recensione, nuovo, valutazione',
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
