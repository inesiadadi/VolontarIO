<?php

include('header.php');

use DB\DBAccess;
$connection = new DBAccess();


$html = file_get_contents(ROOT_PATH . '/templates/register.html');


if(isset($_SESSION['logged'])) {
    $already_logged = "<p>Sei già connesso, fai <a href='/logout' lang='en'>logout</a> per poter registrare un altro account.</p>";
    $html = str_replace('[form]', $already_logged, $html);
} else {
    $form = " <div id='error'>[error_message]</div>
                <form class='accessForm' id='signForm' action='/volontario/register' method='post'>
                    <legend id='legendForm'>Registra un account</legend>
                    <label for='username'>Username:</label>
                    <input type='text' name='username' id='username' required aria-required='true' aria-describedby='aiuto-username' value='[username]'></input>
                    <span id='aiuto-username' class='aiuto'></span>


                    <label for='email'>Email:</label>
                    <input type='email' name='email' id='email' required aria-required='true' aria-describedby='aiuto-email' value='[email]'></input>                  
                    <span id='aiuto-email' class='aiuto'></span>


                    <label for='dataNascita'>Data di nascita:</label>
                    <input type='date' name='dataNascita' id='dataNascita' required aria-required='true' aria-describedby='aiuto-dataNascita' value='[dataNascita]'></input>
                    <span id='aiuto-dataNascita' class='aiuto'></span>

                    
                    <label for='nome'>Nome:</label>
                    <input type='text' name='nome' id='nome' required aria-required='true' aria-describedby='aiuto-nome' value='[nome]'></input>
                    <span id='aiuto-nome' class='aiuto'>A</span>


                    <label for='cognome'>Cognome:</label>
                    <input type='text' name='cognome' id='cognome' required aria-required='true' aria-describedby='aiuto-cognome' value='[cognome]'></input>
                    <span id='aiuto-cognome' class='aiuto'></span>


                    <label for='password'>Password:</label>
                    <input type='password' name='password' id='password' required aria-required='true' aria-describedby='aiuto-password' value='[password]'></input>
                    <span id='aiuto-password' class='aiuto'></span>


                    <label for='pass_again'>Ripeti la password:</label>
                    <input type='password' name='pass_again' id='pass_again' required aria-required='true' aria-describedby='aiuto-pass_again' value='[pass_again]'></input>
                    <span id='aiuto-pass_again' class=''></span>


                    <button type='submit'>Registrati</button>
                </form>
                <p>Sei già registrato? Vai direttamente alla <a href='/volontario/login'>pagina di connessione</a></p>";
        $html = str_replace('[form]', $form, $html);
    }

    $username = "";
    $email = "";
    $nome = "";
    $cognome = "";
    $dataNascita = "";
    $password = "";
    $pass_again = "";

if(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    $username = $_POST["username"];
    $email = $_POST["email"];
    
    $nome = $_POST["nome"];
    $cognome = $_POST["cognome"];
    $dataNascita = $_POST["dataNascita"];
    
    $password = $_POST["password"];
    $pass_again = $_POST["pass_again"];
    
    if($password != $pass_again) {
        $password = "";
        $pass_again = "";
        $messaggio = "<p class='error'>Le password sono diverse</span>";
        $html = str_replace('[error_message]', $messaggio, $html);

    } else {
        $connessioneOK = $connection->openDBConnection();
        if($connessioneOK) {
            $result = $connection->registerAccount($username, $password, $email, $nome, $cognome, $dataNascita);
            
        } else {
            $messaggio = "<p class='error'>I sistemi sono momentamente  fuori servizio, ci scusiamo per il disagio.</p>";
            $html = str_replace('[error_message]', $messaggio, $html);
            $result = false;
        }
        $connection->closeDBConnection();
        
        if($result) {
            // Inizio della sessione, da vedere meglio ancora
            session_start();
            $_SESSION['logged'] = true;
            $_SESSION['user_id'] = $result;
            $_SESSION['username'] = $username;
            
            header('Location: /');
            exit();

        } else {
            $messaggio = "<p class='error'>Account con questo username già esistente</span>";
            $html = str_replace('[error_message]', $messaggio, $html);
        }
    }
}  else {
    $html = str_replace('[error_message]', '', $html);
}
$html = str_replace("[username]", $username,$html);
$html = str_replace("[email]", $email,$html);
$html = str_replace("[dataNascita]", $dataNascita,$html);
$html = str_replace("[nome]", $nome,$html);
$html = str_replace("[cognome]", $cognome,$html);
$html = str_replace("[password]", $password,$html);
$html = str_replace("[pass_again]", $pass_again,$html);

$pagina = file_get_contents(ROOT_PATH . '/templates/blueprint.html');

$footer_html = file_exists(ROOT_PATH . '/templates/components/footer.html') ? file_get_contents(ROOT_PATH . '/templates/components/footer.html') : '';

$placeholders_layout = [
    '[Titolo Pagina]' => 'Registrazione',
    '[Description]'   => 'Pagina di registrazione di VolontarIO',
    '[Keywords]'      => 'volontariato, profilo, sign-in, register, registrazione',
    '[pageNameId]'    => 'registerPage', 
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
