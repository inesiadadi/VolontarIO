<?php
// error_reporting(E_ALL); 
// ini_set('display_errors', 1);
// Per avere un echo degli errori php dentro la pagina

$title = "Registrazione | VolontarIO";
include('header.php');
$messaggio = "";

if(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $pass_again = $_POST["pass_again"];
    
    if($password != $pass_again) {
        $messaggio = "<p class='error'>Le password sono diverse</span>";
    } else {

        //Il blocco qui sotto è fatto in modo da aver una query "safe" invece di avere $username direttamente sparato nella query, ancora manca qualche step per avere solo valori alfanumerici
        $check = $connessione->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $result = $check->get_result();
        
        if($result->num_rows > 0) {
            $messaggio = "<p class='error'>Account con questo username già esistente</span>";
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

            header('Location: /');
            exit();
        }
    }
}   
// if(session_status() == PHP_SESSION_NONE) {
//     session_start();
// }
?>
<main>
    <h1>Registrazione</h1>
    <form action="/register" method="post">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username"></input>

        <label for="password">Passowrd:</label>
        <input type="password" name="password" id="password"></input>

        <label for="pass_again">Ripeti la password:</label>
        <input type="password" name="pass_again" id="pass_again"></input>

        <button type="submit">Registrati</button>
    </form>
    <?= $messaggio ?>
    <p>Sei già registrato? Vai direttamente alla <a href="/login">pagina di connessione</a></p>
</main>
<?php
include('footer.php');
?>