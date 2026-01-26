<?php 

include('header.php');

use DB\DBAccess;
$connection = new DBAccess();

$html = file_get_contents(ROOT_PATH . '/templates/login.html');
if(isset($_SESSION['logged'])) {
    $already_logged = "<p id='alreadyLogged'>Sei già connesso, fai <a href='/volontario/logout' lang='en'>logout</a> per poter registrare un altro account.</p>";
    $html = str_replace('[form]', $already_logged, $html);
} else {
    $form = "
        [error_message]
        <form id='logForm' class='accessForm form-input' action='/volontario/login' method='post'>
                <legend id='legendForm'>Accedi al tuo account</legend>
                <label for='username'><span lang='en'>Username</span>:</label>
                <input type='text' name='username' id='username' required aria-required='true' aria-describedby='aiuto-username' autocomplete='username'/>
                <span id='aiuto-username' class='aiuto'>Inserisci il tuo <span lang='en'>username</span></span>
                <label for='password'><span lang='en'>Password</span>:</label>
                <input type='password' name='password' id='password' required aria-required='true' aria-describedby='aiuto-password' autocomplete='current-password' />
                <span id='aiuto-password' class='aiuto'>Inserisci la tua <span lang='en'>password</span></span>
                <button type='submit'>Accedi</button>
        </form>
        <p>Non sei registrato? <a href='/volontario/register'>Registrati!</a></p>";
    
        $html = str_replace('[form]', $form, $html);
    }

if(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $result = null;
    $connessioneOK = $connection->openDBConnection();
    if($connessioneOK) {
        $result = $connection->loginAccount($username);        
    } else {
        $messaggio = "<div role='alert'><p class='error'>I sistemi sono momentamente fuori servizio, ci scusiamo per il disagio.</p></div>";
        $html = str_replace('[error_message]', $messaggio, $html);
    }
    $connection->closeDBConnection();

if ($connessioneOK && $result === false) {
    $messaggio = "<div role='alert'><p class='error'>La password o l'username non sono corretti</p></div>";
    $html = str_replace('[error_message]', $messaggio, $html);

} else if ($connessioneOK && $result !== null) {
    $row = $result->fetch_assoc();
    if(password_verify($password, $row['password'])) {

        // Sessione, da vedere ancora meglio
        session_start();
        session_regenerate_id(true);
        $_SESSION['logged'] = true;
        $_SESSION['username'] = $row['username'];
        $_SESSION['user_id'] = $row['idPersona'];
        $_SESSION['role'] = $row['ruolo'];
        header('Location: /volontario/');
        exit();
    } else {
        $messaggio = "<div role='alert'><p class='error'>La password o l'username non sono corretti</p></div>";
        $html = str_replace('[error_message]', $messaggio, $html);

    }
}
} else {
    if (!$connection->openDBConnection()) {
        http_response_code(500);
        header('Location: error_500.php');
        exit;
    }

    $html = str_replace("[error_message]", "", $html);
}

$pagina = file_get_contents(ROOT_PATH . '/templates/blueprint.html');

$footer_html = file_exists(ROOT_PATH . '/templates/components/footer.html') ? file_get_contents(ROOT_PATH . '/templates/components/footer.html') : '';

$placeholders_layout = [
    '[Titolo Pagina]' => 'Login',
    '[Description]'   => 'Pagina d\'accesso di VolontarIO',
    '[Keywords]'      => 'volontariato, profilo, sign-in, login, accesso',
    '[pageNameId]'    => 'loginPage', 
    '[onload]'        => '',            
    '[Header]'        => $header_html,
    '[Footer]'        => $footer_html,
    '[MainContent]'   => $html 
];

foreach($placeholders_layout as $chiave => $valore) {
    $pagina = str_replace($chiave, $valore, $pagina);
}

echo $pagina;

?>
