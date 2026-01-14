<?php
include('header.php');

use DB\DBAccess;

$id_visitatore = $_SESSION['user_id'] ?? 0;

if($id_visitatore === 0) {
    header('Location: /volontario/login');
    exit;
}

$id_utente_target = $_GET['id'] ?? 0;
if($id_utente_target != $id_visitatore) {
    header('Location: /volontario/');
    exit;
}

$dbAccess = new DBAccess();

if(!$dbAccess->openDBConnection()) {
    http_response_code(500);
    include ROOT_PATH . '/php/error_500.php';
    exit;
}
$utente = $dbAccess->getUtentePerId($id_utente_target);
$dbAccess->closeDBConnection();

if(!$utente) {
    http_code(404);
    include ROOT_PATH . '/php/error_404.php';
    exit;
}

$dati_utente = [
    'nome'        => $utente['nome'],
    'cognome'     => $utente['cognome'], 
    'email'       => $utente['email'],
    'immagine'    => $utente['immagine'] ?? 'default.png',
    'nascita'     => $utente['dataNascita'],
    'idCitta'     => $utente['idCitta'],
    'descrizione' => $utente['descrizione'] ?? '',
    'password'    => $utente['password']
];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nome        = !empty($_POST['nome'])        ? $_POST['nome']        : $dati_utente['nome'];
    $cognome     = !empty($_POST['cognome'])     ? $_POST['cognome']     : $dati_utente['cognome'];
    $dataNascita = !empty($_POST['dataNascita']) ? $_POST['dataNascita'] : $dati_utente['nascita'];
    $descrizione = !empty($_POST['descrizione']) ? $_POST['descrizione'] : $dati_utente['descrizione'];
    $idCitta     = !empty($_POST['idCitta'])     ? $_POST['idCitta']     : $dati_utente['idCitta'];
    $email       = !empty($_POST['email'])       ? $_POST['email']       : $dati_utente['email'];

    $password = (!empty($_POST['password'])) 
                ? password_hash($_POST['password'], PASSWORD_DEFAULT) 
                : $dati_utente['password'];

    $immagine_finale = $dati_utente['immagine']; 
    if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] === UPLOAD_ERR_OK) {
        $nuovo_nome = aggiungiNuovaImmagineProfilo($_FILES['immagine'], $id_utente_target);
        if ($nuovo_nome) {
            $immagine_finale = $nuovo_nome;
        }
    }

    if($dbAccess->openDBConnection()) {
        $successo = $dbAccess->modificaProfiloUtente(
            $id_utente_target, $nome, $cognome, $dataNascita, 
            $descrizione, $immagine_finale, $idCitta, $email, $password
        );
        $dbAccess->closeDBConnection();
        
        if($successo) {
            header("Location: /volontario/profilo?id=$id_utente_target&msg=success");
            exit;
        }
    }
}

$html_content = file_get_contents(ROOT_PATH . '/templates/modificaProfilo.html');

$form = "
<form class='accessForm' id='modifyForm' action='/volontario/modifica-profilo?id=$id_utente_target' method='post' enctype='multipart/form-data'>

    <label for='nome'>Nome (attuale: {$dati_utente['nome']}):</label>
    <input type='text' name='nome' id='nome' placeholder='{$dati_utente['nome']}' value='' aria-describedby='aiuto-nome'>
    <span id='aiuto-nome' class='aiuto'>Inserire il nuovo nome per modificarlo</span>

    <label for='cognome'>Cognome (attuale: {$dati_utente['cognome']}):</label>
    <input type='text' name='cognome' id='cognome' placeholder='{$dati_utente['cognome']}' value='' aria-describedby='aiuto-cognome'>
    <span id='aiuto-cognome' class='aiuto'>Inserire il nuovo cognome per modificarlo</span>

    <label for='dataNascita'>Data di nascita:</label>
    <input type='date' name='dataNascita' id='dataNascita' value='{$dati_utente['nascita']}' aria-describedby='aiuto-data'>
    <span id='aiuto-data' class='aiuto'>Modifica la tua data di nascita</span>

    <label for='descrizione'>Breve descrizione:</label>
    <textarea name='descrizione' id='descrizione' maxlength='255' aria-describedby='aiuto-desc'>{$dati_utente['descrizione']}</textarea>
    <span id='aiuto-desc' class='aiuto'>Aggiorna la tua presentazione (max 255 caratteri)</span>

    <div class='autocomplete-container'>
        <label for='citta-input'>Città di residenza:</label>
        <input type='text' id='citta-input' autocomplete='off' aria-describedby='aiuto-citta'>
        <ul id='suggerimenti-citta' class='lista-suggerimenti' role='listbox'></ul>
        <input type='hidden' name='idCitta' id='idCitta-input' value='{$dati_utente['idCitta']}'>
    </div>
    <span id='aiuto-citta' class='aiuto'>Inizia a scrivere il nome del comune e selezionalo dalla lista</span>

    <label for='email'>Email:</label>
    <input type='email' name='email' id='email' placeholder='{$dati_utente['email']}' aria-describedby='aiuto-email'>
    <span id='aiuto-email' class='aiuto'>Inserisci un nuovo indirizzo email valido</span>

    <label for='password'>Nuova Password:</label>
    <input type='password' name='password' id='password' aria-describedby='aiuto-password'>
    <span id='aiuto-password' class='aiuto'>Lascia vuoto se non desideri cambiare la password</span>

    <label for='pass_again'>Ripeti la nuova password:</label>
    <input type='password' name='pass_again' id='pass_again' aria-describedby='aiuto-pass-again'>
    <span id='aiuto-pass-again' class='aiuto'>Conferma la nuova password inserita sopra</span>

    <label for='foto-profilo'>Cambia Foto profilo (.png):</label>
    <input type='file' name='immagine' id='foto-profilo' accept='image/png' aria-describedby='aiuto-foto'>
    <span id='aiuto-foto' class='aiuto'>Carica un file PNG per aggiornare la tua immagine (max 2MB)</span>

    <button type='submit'>Conferma Modifiche</button>
</form>
<p>Se non vuoi modificare il profilo, torna all'<a href='/volontario/profilo?id=$id_utente_target'>Area Personale!</a></p>";

$html_content = str_replace('[Form]', $form, $html_content);

$pagina = file_get_contents(ROOT_PATH . '/templates/blueprint.html');
$footer_html = file_exists(ROOT_PATH . '/templates/components/footer.html') ? file_get_contents(ROOT_PATH . '/templates/components/footer.html') : '';

$placeholders_layout = [
    '[Titolo Pagina]' => 'Modifica Profilo',
    '[Description]'   => 'Pagina di modifica del profilo di VolontarIO',
    '[Keywords]'      => 'volontariato, profilo, modifica',
    '[pageNameId]'    => 'modifyPage',
    '[onload]'        => 'initCittaAutocomplete()',
    '[Header]'        => $header_html,
    '[Footer]'        => $footer_html,
    '[MainContent]'   => $html_content 
];

foreach($placeholders_layout as $chiave => $valore) {
    $pagina = str_replace($chiave, $valore, $pagina);
}

echo $pagina;
?>
