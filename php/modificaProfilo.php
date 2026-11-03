<?php
include('header.php');

use DB\DBAccess;

$id_visitatore = $_SESSION['user_id'] ?? 0;

if($id_visitatore === 0) {
    header('Location: /volontario/login');
    exit;
    }
    
$id_utente_target = (int)($_GET['id'] ?? 0);
if($id_utente_target != $id_visitatore) {
    header('Location: /volontario/');
    exit;
    }
        
$dbAccess = new DBAccess();
if (!$dbAccess->openDBConnection()) {
    
    header('Location: /volontario/error-500');
    exit;
}
            
$utente = $dbAccess->getUtentePerId($id_utente_target);
$dbAccess->closeDBConnection();
            
if(!$utente) {
    http_response_code(404);
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
    'password'    => $utente['password'],
    ];

$form = "
[error_message]
<form class='accessForm' id='modifyForm' action='/volontario/modifica-profilo?id=$id_utente_target' method='post' enctype='multipart/form-data'>
    " . csrf_field() . "
        <fieldset>
        <legend>Modifica il tuo profilo</legend>   
        <label for='nome'>Nome (attuale: " . e($dati_utente['nome']) . "):</label>
        <input type='text' name='nome' id='nome' placeholder='" . e($dati_utente['nome']) . "' value='' aria-describedby='aiuto-nome'>
        <span id='aiuto-nome' class='aiuto'>Inserire il nuovo nome per modificarlo</span>

        <label for='cognome'>Cognome (attuale: " . e($dati_utente['cognome']) . "):</label>
        <input type='text' name='cognome' id='cognome' placeholder='" . e($dati_utente['cognome']) . "' value='' aria-describedby='aiuto-cognome'>
        <span id='aiuto-cognome' class='aiuto'>Inserire il nuovo cognome per modificarlo</span>

        <label for='dataNascita'>Data di nascita:</label>
        <input type='date' name='dataNascita' id='dataNascita' max='". date('Y-m-d', strtotime('-18 years')) ."' value='" . e($dati_utente['nascita']) . "' aria-describedby='aiuto-data'>
        <span id='aiuto-data' class='aiuto'>Modifica la tua data di nascita</span>

        <label for='descrizione'>Breve descrizione:</label>
        <textarea name='descrizione' id='descrizione' maxlength='255' aria-describedby='aiuto-desc'>" . e($dati_utente['descrizione']) . "</textarea>
        <span id='aiuto-desc' class='aiuto'>Aggiorna la tua presentazione (max 255 caratteri)</span>
    
    <label for='citta-input'>Città di residenza:</label>
    <input type='text' id='citta-input' name='citta_nome' autocomplete='off' role='combobox' aria-label='citta-residenza' aria-autocomplete='list' aria-expanded='false' aria-controls='suggerimenti-citta' aria-describedby='aiuto-citta'>
    <ul id='suggerimenti-citta' role='listbox' tabindex='-1' hidden ></ul>
    <input type='hidden' name='idCitta' id='idCitta-input'>
    <div id='status-citta' class='sr-only' aria-live='polite'></div>
    <span id='aiuto-citta' class='aiuto'>
        Digita il comune e usa le frecce su e giù per selezionare.
    </span>

    <label for='email'>Email:</label>
    <input type='email' name='email' id='email' placeholder='" . e($dati_utente['email']) . "' aria-describedby='aiuto-email'>
    <span id='aiuto-email' class='aiuto'>Inserisci un nuovo indirizzo email valido</span>

        <label for='vecchia_password'>Password attuale:</label>
        <input type='password' name='vecchia_password' id='vecchia_password' autocomplete='current-password' aria-describedby='aiuto-vecchia-password'>
        <span id='aiuto-vecchia-password' class='aiuto'>Obbligatoria per cambiare email o password</span>

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
        </fieldset>
    
</form>";

$html = file_get_contents(ROOT_PATH . '/templates/modificaProfilo.html');
$html = str_replace('[Form]', $form, $html);

$nome = "";
$cognome = "";
$dataNascita = "";
$descrizione = "";
$idCitta = "";
$email = "";

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nome        = !empty($_POST['nome'])        ? trim($_POST['nome'])        : $dati_utente['nome'];
    $cognome     = !empty($_POST['cognome'])     ? trim($_POST['cognome'])     : $dati_utente['cognome'];
    $dataNascita = !empty($_POST['dataNascita']) ? $_POST['dataNascita'] : $dati_utente['nascita'];
    $descrizione = !empty($_POST['descrizione']) ? trim($_POST['descrizione']) : $dati_utente['descrizione'];
    $idCitta     = !empty($_POST['idCitta'])     ? $_POST['idCitta']     : $dati_utente['idCitta'];
    $email       = !empty($_POST['email'])       ? $_POST['email']       : $dati_utente['email'];

    $nuova_password   = $_POST['password'] ?? '';
    $pass_again       = $_POST['pass_again'] ?? '';
    $vecchia_password = $_POST['vecchia_password'] ?? '';
    $cambia_password  = $nuova_password !== '';
    $cambia_email     = $email !== $dati_utente['email'];

    $errori = [];

    if(!empty($_POST['nome']) && !preg_match("/^[a-zA-Z\sàèìòù']+$/", $nome)) {
        $errori[] = "Il nome non è valido.";
    }
    if(!empty($_POST['cognome']) && !preg_match("/^[a-zA-Z\sàèìòù']+$/", $cognome)) {
        $errori[] = "Il cognome non è valido.";
    }
    if(!empty($_POST['username']) && !preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
        $errori[] = "L'username deve essere di 4-20 caratteri (lettere, numeri, underscore).";
    }
    if(!empty($_POST['email']) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errori[] = "Formato email non valido.";
    }
    if(!empty($_POST['dataNascita'])) {
            $data_di_nascita = new DateTime($dataNascita);
            $now = new DateTime();
            $eta = $now->diff($data_di_nascita)->y;
            if($eta < 18) {
                $errori[] = "Devi essere maggiorenne per registrarti."; 
            }
    }
    if(($cambia_password || $cambia_email) && !password_verify($vecchia_password, $dati_utente['password'])) {
        $errori[] = "La password attuale non è corretta.";
    }
    if($cambia_password && !preg_match("/^(?=.*[A-Z])(?=.*\\d).{8,}$/", $nuova_password)) {
        $errori[] = "La password deve essere lunga almeno 8 caratteri e deve contenere almeno una maiuscola e un numero.";
    }
    if($cambia_password && $nuova_password !== $pass_again) {
        $errori[] = "Le password sono diverse";
    }
    $password = $cambia_password ? password_hash($nuova_password, PASSWORD_DEFAULT) : $dati_utente['password'];
    
    $immagine_finale = $dati_utente['immagine']; 
    if (empty($errori) && isset($_FILES['immagine']) && $_FILES['immagine']['error'] !== UPLOAD_ERR_NO_FILE) {
        $nuovo_nome = aggiungiNuovaImmagineProfilo($_FILES['immagine'], $id_utente_target);
        if ($nuovo_nome) {
            $immagine_finale = $nuovo_nome;
        } else {
            $errori[] = "L'immagine deve essere un file PNG di massimo 2MB.";
        }
    }
    if(!empty($errori)) {
        $password = "";
        $pass_again = "";
        $messaggio = "<div class='error' role='alert'><ul>";
        
        foreach ($errori as $errore) {
            $messaggio .= "<li>" . $errore . "</li>";
        }
        $messaggio .= "</ul></div>";
        $html = str_replace('[error_message]', $messaggio, $html);

    } else if ($dbAccess->openDBConnection()) {
         $successo = $dbAccess->modificaProfiloUtente(
            $id_utente_target, $nome, $cognome, $dataNascita, 
            $descrizione, $immagine_finale, $idCitta, $email, $password
        );
        $dbAccess->closeDBConnection();
        
        if($successo) {
            header("Location: /volontario/profilo?id=$id_utente_target");
            exit;
        }
    }
} else {
    $html = str_replace('[error_message]', "", $html);
}
$html = str_replace('[Form]', $form, $html);
$html = str_replace('[idUtenteTarget]', $id_utente_target, $html);
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
    '[MainContent]'   => $html 
];

foreach($placeholders_layout as $chiave => $valore) {
    $pagina = str_replace($chiave, $valore, $pagina);
}

echo $pagina;
?>
