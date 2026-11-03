<?php

use DB\DBAccess;

include('header.php');
$id_visitatore = $_SESSION['user_id'] ?? 0;

if ($id_visitatore === 0) {
    header("Location: /volontario/login"); // O redirect alla Home
    exit;
} else if(($_SESSION['role'] ?? '') !== "admin") {
    header("Location: /volontario/");
    exit;
}

// Connessione DB
$dbAccess = new DBAccess();
if (!$dbAccess->openDBConnection()) {
    header('Location: /volontario/error-500');
    exit;
}

$html = file_get_contents(ROOT_PATH . '/templates/pannello.html');
$table = "";

$connessioneOK = $dbAccess->openDBConnection();

if($connessioneOK) {
    if(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
        $id = $_POST["id"];
        $dbAccess->deleteMessaggio($id);
        header("Location: /volontario/pannello");
        exit;
    }
    $result = $dbAccess->getMessaggi();
    if($result === false) {
        $table = "
        <div id='noContactTable' class='noReviewsMsg'>
        <p>Non ci sono richieste d'assistenza.</p>
        </div>
        ";
    } else {

        $table = '<p class="sr-only" id="aiuto-tabella">Nella tabella sono presenti le richieste d\'assistenza, per ogni riga c\'é una richiesta d\'assistenza con il corrispondente nome e cognome dell\'utente, il numero d\'assistenza se inserito dall\'utente, il messaggio di richiesta e i bottoni per rispondere o eliminare il messaggio.</p>
                <table class="contact-table" aria-describedby="aiuto-tabella">
                <caption>Lista richieste di assistenza</caption>
                <thead>
                    <tr>
                        <th scope="col">Nome</th>
                        <th data-label="Telefono" scope="col">Telefono</th>
                        <th data-label="Messaggio" scope="col">Messaggio</th>
                        <th data-label="Azioni" scope="col" class="noStampaTabella">Azioni</th>
                        <th data-label="Email" scope="col" class="soloStampaTabella" hidden>Email</th>
                    </tr>
                </thead>
                <tbody>';
        while($row = $result->fetch_assoc()) {
            $nome_cognome = htmlspecialchars($row['nome_cognome']);
            $email = htmlspecialchars($row['email']);
            $numero = htmlspecialchars($row['numero']);
            $messaggio = nl2br(htmlspecialchars($row['messaggio']));
            $ora = date('H:i', strtotime($row['data_invio']));
            $data = date('d/m/Y', strtotime($row['data_invio']));
            if($numero == 0) {
                $numero = "Nessun numero";
            }
            $id = $row['id'];

            $table .= '<tr>
                    <th data-label="Nome" scope="row">' . $nome_cognome . '</th>

                    <td data-label="Numero">' . $numero . '</td>
                    <td data-label="Messaggio"><div class="message-cell-content" tabindex="0">'. $messaggio . '</div></td>
                    <td data-label="Azioni" class="noStampaTabella">
                        <div class="actions">
                            <a href="mailto:' . $email . '?subject=Risposta%20assistenza%20VolontarIO" 
                               class="btnh btn-login-header" title="Rispondi via email"
                               aria-label="Rispondi a ' . $nome_cognome . ' via mail">
                               <span aria-hidden="true">✉️</span> Rispondi
                            </a>

                            <form action="/volontario/pannello" method="POST">
                                ' . csrf_field() . '
                                <input type="hidden" name="id" value="' . $id . '">
                                <button type="submit" name="delete_btn" class="btnh btn-register-header"
                                aria-label="Elimina messaggio di ' . $nome_cognome .'mandato il '. $data .' alle '. $ora .'">
                                    <span aria-hidden="true">🗑️</span> Elimina
                                </button>
                            </form>
                        </div>
                    </td>
                    <td class="soloStampaTabella" hidden>'. $email .'</td>
                </tr>';
        }
        $table .= '</tbody></table>';
    }
} else {
    # Errore da fare
}

$html = str_replace("[table]", $table,$html);

// Layout generale
$pagina = file_get_contents(realpath(__DIR__ . '/' . '../templates/blueprint.html'));
$footer_html = file_exists(ROOT_PATH . '/templates/components/footer.html') ? file_get_contents(ROOT_PATH . '/templates/components/footer.html') : '';

$placeholders_layout = [
    '[Titolo Pagina]' => 'Pannello d\'assistenza',
    '[Description]'   => "Visualizza i messaggi d'assistenza degli utenti di VolontarIO",
    '[Keywords]'      => 'volontariato, profilo, assistenza, pannello',
    '[pageNameId]'    => 'pannelPage',
    '[onload]'        => '',
    '[Header]'        => $header_html,
    '[Footer]'        => $footer_html,
    '[MainContent]'   => $html
];

foreach ($placeholders_layout as $chiave => $valore) {
    $pagina = str_replace($chiave, $valore, $pagina);
}

echo $pagina;
?>