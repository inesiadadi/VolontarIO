<?php 

require_once('db/include/functions.php');
require_once('db/include/dbConnection.php');

include('header.php');

use DB\DBAccess;
$connection = new DBAccess();

$html = file_get_contents(realpath(__DIR__ . '/' . '../src/html/login.html'));

if(isset($_SESSION['logged'])) {
    $already_logged = "<p>Sei già connesso, fai <a href='/logout' lang='en'>logout</a> per poter registrare un altro account.</p>";
    $html = str_replace('[form]', $already_logged, $html);
} else {
    $form = "
        [error_message]
        <form id='logForm' class='accessForm' action='/login' method='post'>
                <legend id='legendForm'>Accedi al tuo account</legend>
                <label for='username'><span lang='en'>Username</span>:</label>
                <input type='text' name='username' id='username' required aria-required='true' aria-describedby='aiuto-username' autocomplete='username'/>
                <span id='aiuto-username' class='aiuto'>Inserisci il tuo <span lang='en'>username</span></span>
                <label for='password'><span lang='en'>Password</span>:</label>
                <input type='password' name='password' id='password' required aria-required='true' aria-describedby='aiuto-password' autocomplete='current-password' />
                <span id='aiuto-password' class='aiuto'>Inserisci la tua <span lang='en'>password</span></span>
                <button type='submit'>Accedi</button>
        </form>
        <p>Non sei registrato? <a href='/register'>Registrati!</a></p>";
    
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
        header('Location: /');
        exit();
    } else {
        $messaggio = "<div role='alert'><p class='error'>La password o l'username non sono corretti</p></div>";
        $html = str_replace('[error_message]', $messaggio, $html);

    }
}
} else {
    $html = str_replace('[error_message]', '', $html);

}

$pagina = file_get_contents('templates/blueprint.html');

$footer_html = file_exists('templates/components/footer.html') ? file_get_contents('templates/components/footer.html') : '';

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
