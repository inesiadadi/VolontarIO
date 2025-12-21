<?php
$title = "Register | VolontarIO";
$description = "Pagina di registrazione";
$keywords = "Sign-in Register Registrazione";

include('header.php');
use DB\DBAccess;
$connessione = new DBAccess();

$html_header = str_replace("%title%", $title, $html_header);
$html_header = str_replace("%description%", $description, $html_header);
$html_header = str_replace("%keywords%", $keywords, $html_header);
echo $html_header;

$html = file_get_contents(realpath(__DIR__ . '/' . '../src/html/register.html'));


if(isset($_SESSION['logged'])) {
    $already_logged = "<p>Sei già connesso, fai <a href='/logout' lang='en'>logout</a> per poter registrare un altro account.</p>";
    $html = str_replace('%form%', $already_logged, $html);
    } else {
        $form = "<form id='logForm' action='/register' method='post'>
                    <label for='username'>Username:</label>
                    <input type='text' name='username' id='username' required value='[username]'></input>

                    <label for='email'>Email:</label>
                    <input type='email' name='email' id='email' required value='[email]'></input>                  
                    
                    <label for='birthdate'>Data di nascita:</label>
                    <input type='date' name='dataNascita' id='dataNascita' required value='[dataNascita]'></input>

                    
                    <label for='nome'>Nome:</label>
                    <input type='text' name='nome' id='nome' required value='[nome]'></input>
                    
                    <label for='cognome'>Cognome:</label>
                    <input type='text' name='cognome' id='cognome' required value='[cognome]'></input>

                    <label for='password'>Passowrd:</label>
                    <input type='password' name='password' id='password' required value='[password]'></input>
                    <label for='pass_again'>Ripeti la password:</label>
                    <input type='password' name='pass_again' id='pass_again' required value='[pass_again]'></input>
                    <button type='submit'>Registrati</button>
                </form>
                [error_message]
                <p>Sei già registrato? Vai direttamente alla <a href='/login'>pagina di connessione</a></p>";
        $html = str_replace('%form%', $form, $html);

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
        $connessioneOK = $connessione->openDBConnection();
        if($connessioneOK) {
            $result = $connessione->registerAccount($username, $password, $email, $nome, $cognome, $dataNascita);
            
        } else {
            $messaggio = "<p class='error'>I sistemi sono momentamente  fuori servizio, ci scusiamo per il disagio.</p>";
            $html = str_replace('[error_message]', $messaggio, $html);
            $result = false;
        }
        $connessione->closeConnection();
        
        if($result) {
            // Inizio della sessione, da vedere meglio ancora
            session_start();
            $_SESSION['logged'] = true;
            $_SESSION['user_id'] = $row['id'];
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
echo $html;
include('footer.php');
?>