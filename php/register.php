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
                <form class='accessForm form-input' id='signForm' action='/volontario/register' method='post'>
                    <fieldset>
                        <legend id='legendForm'>Registra un account</legend>
                        <label for='username'>Username:</label>
                        <input type='text' name='username' id='username' required aria-describedby='aiuto-username' value='[username]'>
                        <span id='aiuto-username' class='aiuto'>4-20 caratteri (lettere, numeri, <span lang='en'>underscore</span>)</span>


                        <label for='email'>Email:</label>
                        <input type='email' name='email' id='email' required aria-describedby='aiuto-email' value='[email]'>          
                        <span id='aiuto-email' class='aiuto'>Inserisci un'email valida, es: aala@ajmai.me</span>


                        <label for='dataNascita'>Data di nascita:</label>
                        <input type='date' name='dataNascita' id='dataNascita' required autocomplete='bday' aria-describedby='aiuto-dataNascita' max='" . date('Y-m-d', strtotime('-18 years'))."' value='[dataNascita]'>
                        <span id='aiuto-dataNascita' class='aiuto'>Inserisci la tua data di nascita (devi essere maggiorenne), es: 01/09/2007</span>

                    
                        <label for='nome'>Nome:</label>
                        <input type='text' name='nome' id='nome' required aria-describedby='aiuto-nome' value='[nome]'>
                        <span id='aiuto-nome' class='aiuto'>Solo lettere, minimo 2 caratteri, es: Mario</span>


                        <label for='cognome'>Cognome:</label>
                        <input type='text' name='cognome' id='cognome' required aria-describedby='aiuto-cognome' value='[cognome]'>
                        <span id='aiuto-cognome' class='aiuto'>Solo lettere, minimo 2 caratteri, es: Rossi</span>


                        <label for='password'>Password:</label>
                        <input type='password' name='password' id='password' required aria-describedby='aiuto-password' value='[password]'>
                        <span id='aiuto-password' class='aiuto'>Minimo 8 caratteri con almeno una maiuscola e un numero</span>


                        <label for='pass_again'>Ripeti la password:</label>
                        <input type='password' name='pass_again' id='pass_again' required aria-describedby='aiuto-pass_again' value='[pass_again]'>
                        <span id='aiuto-pass_again' class='aiuto'>Ripeti la password scelta</span>
                    </fieldset>

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
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    
    $nome = trim($_POST["nome"] ?? "");
    $cognome = trim($_POST["cognome"] ?? "");
    $dataNascita = $_POST["dataNascita"] ?? "";

    $password = $_POST["password"] ?? "";
    $pass_again = $_POST["pass_again"] ?? "";
    
    $errori = [];

    if(!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
            $errori[] = "L'username deve essere di 4-20 caratteri (lettere, numeri, <span lang='en'>underscore</span>).";
    }
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errori[] = "Formato email non valido.";
    }
    if(!preg_match("/^[a-zA-Z\sàèìòù']+$/", $nome)) {
            $errori[] = "Il nome non è valido.";
    }
    if(!preg_match("/^[a-zA-Z\sàèìòù']+$/", $cognome)) {
            $errori[] = "Il cognome non è valido.";
    }
    if(empty($dataNascita)) {
            $errori[] = "Inserire la data di nascita.";
        } else {
            $data_di_nascita = new DateTime($dataNascita);
            $now = new DateTime();
            $eta = $now->diff($data_di_nascita)->y;
            if($eta < 18) {
                $errori[] = "Devi essere maggiorenne per registrarti.";
            }
        }
    if(!preg_match("/^(?=.*[A-Z])(?=.*\d).{8,}$/", $password)) {
        $errori[] = "La password deve essere lunga almeno 8 caratteri e deve contenere almeno una maiuscola e un numero.";
    }
    if($password != $pass_again) {
        $password = "";
        $pass_again = "";
        $errori[] = "Le password sono diverse";
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
            $_SESSION['email'] = $email;
            $_SESSION['role'] = "user";
            $_SESSION['nome'] = $nome;
            $_SESSION['cognome'] = $cognome;
            
            header('Location: /volontario/');
            exit();

        } else {
            $messaggio = "<p class='error'>Account con questo username già esistente</span>";
            $html = str_replace('[error_message]', $messaggio, $html);
        }
    }
}  else {
    if (!$connection->openDBConnection()) {
        header('Location: /volontario/error-500');
        exit;
    }

    $html = str_replace("[error_message]", "", $html);
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
