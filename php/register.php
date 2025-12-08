<?php
$title = "Register | VolontarIO";
$description = "Pagina di registrazione";
$keywords = "Sign-in Register Registrazione";
include('header.php');

$html_header = str_replace("%title%", $title, $html_header);
$html_header = str_replace("%description%", $description, $html_header);
$html_header = str_replace("%keywords%", $keywords, $html_header);
echo $html_header;

$html = file_get_contents(realpath(__DIR__ . '/' . '../src/html/register.html'));


if(isset($_SESSION['logged'])) {
    $already_logged = "<p>Sei già connesso, fai <a href='/logout' lang='en'>logout</a> per poter registrare un altro account.</p>";
    $html = str_replace('%form%', $already_logged, $html);
    } else {
        $form = "<form action='/register' method='post'>
                    <label for='username'>Username:</label>
                    <input type='text' name='username' id='username'></input>
                    <label for='password'>Passowrd:</label>
                    <input type='password' name='password' id='password'></input>
                    <label for='pass_again'>Ripeti la password:</label>
                    <input type='password' name='pass_again' id='pass_again'></input>
                    <button type='submit'>Registrati</button>
                </form>
                %error_message%
                <p>Sei già registrato? Vai direttamente alla <a href='/login'>pagina di connessione</a></p>";
        $html = str_replace('%form%', $form, $html);

    }

if(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $pass_again = $_POST["pass_again"];
    
    if($password != $pass_again) {
        $messaggio = "<p class='error'>Le password sono diverse</span>";
        $html = str_replace('%error_message%', $messaggio, $html);

    } else {

        //Il blocco qui sotto è fatto in modo da aver una query "safe" invece di avere $username direttamente sparato nella query, ancora manca qualche step per avere solo valori alfanumerici
        $check = $connessione->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $result = $check->get_result();
        
        if($result->num_rows > 0) {
            $messaggio = "<p class='error'>Account con questo username già esistente</span>";
            $html = str_replace('%error_message%', $messaggio, $html);

        } else {
            $check = $connessione->prepare("INSERT INTO users (username, pass) VALUES (?, ?)");
            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
            $check->bind_param("ss", $username, $pass_hash);
            $check->execute();
            
            $retreive_id = $connessione->prepare("SELECT id FROM users WHERE username = ?");
            $retreive_id->bind_param("s", $username);
            $retreive_id->execute();
            $result = $retreive_id->get_result();
            $row = $result->fetch_assoc();
    
            // Inizio della sessione, da vedere meglio ancora
            session_start();
            $_SESSION['logged'] = true;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $username;

            header('Location: /');
            exit();
        }
    }
}  else {
    $html = str_replace('%error_message%', '', $html);
    }
echo $html;
include('footer.php');
?>