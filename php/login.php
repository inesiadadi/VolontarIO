<?php 

include('header.php');

use DB\DBAccess;
$connection = new DBAccess();

$html = file_get_contents(ROOT_PATH . '/templates/login.html');

if(isset($_SESSION['logged'])) {
        $already_logged = "<p>Sei già connesso, fai <a href='/logout' lang='en'>logout</a> per poter registrare un altro account.</p>";
        $html = str_replace('[form]', $already_logged, $html);
} else {
        $form = "[error_message]
                    <form id='logForm' action='/login' method='post'>        
                    <label for='username'>Username:</label>
                    <input type='text' name='username' id='username'></input>
                    <label for='password'>Password:</label>
                    <input type='password' name='password' id='password'></input>
                    <button type='submit'>Accedi</button>
                </form>
                <p>Non sei registrato? <a href='/register'>Registrati!</a></p>";
        $html = str_replace('[form]', $form, $html);
    }

if(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $result = "";
    $connessioneOK = $connection->openDBConnection();
    if($connessioneOK) {
        $result = $connection->loginAccount($username);        
    } else {
        $messaggio = "<p class='error'>I sistemi sono momentamente fuori servizio, ci scusiamo per il disagio.</p>";
        $html = str_replace('%error_message%', $messaggio, $html);
    }
    $connection->closeDBConnection();

if ($connessioneOK && !$result) {
    $messaggio = "<p class='error'>La password o l'username non sono corretti 1</p>";
    $html = str_replace('%error_message%', $messaggio, $html);

} else {
    $row = $result->fetch_assoc();
    if(password_verify($password, $row['password'])) {

        // Sessione, da vedere ancora meglio
        session_start();
        session_regenerate_id(true);
        $_SESSION['logged'] = true;
        $_SESSION['username'] = $row['username'];
        $_SESSION['user_id'] = $row['id'];
        header('Location: /');
        exit();
    } else {
        $messaggio = "<p class='error'>La password o l'username non sono corretti</p>";
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
