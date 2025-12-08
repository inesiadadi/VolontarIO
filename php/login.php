<?php 
$title = "Login | VolontarIO";
$description = "Pagina d'accesso";
$keywords = "Login Accesso";
include('header.php');

$html_header = str_replace("%title%", $title, $html_header);
$html_header = str_replace("%description%", $description, $html_header);
$html_header = str_replace("%keywords%", $keywords, $html_header);
echo $html_header;

$html = file_get_contents(realpath(__DIR__ . '/' . '../src/html/login.html'));

if(isset($_SESSION['logged'])) {
        $already_logged = "<p>Sei già connesso, fai <a href='/logout' lang='en'>logout</a> per poter registrare un altro account.</p>";
        $html = str_replace('%form%', $already_logged, $html);
} else {
        $form = "<form action='/login' method='post'>
                    <label for='username'>Username:</label>
                    <input type='text' name='username' id='username'></input>
                    <label for='password'>Password:</label>
                    <input type='password' name='password' id='password'></input>
                    <button type='submit'>Accedi</button>
                </form>
                %error_message%
                <p>Non sei registrato? <a href='/register'>Registrati!</a></p>";
        $html = str_replace('%form%', $form, $html);
    }

if(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
$username = $_POST['username'];
$password = $_POST['password'];
$check_username = $connessione->prepare("SELECT username, id, pass FROM users WHERE username =  ?");
$check_username->bind_param("s", $username);
$check_username->execute();
$result = $check_username->get_result();
if ($result->num_rows == 0) {

    $messaggio = "<p class='error'>La password o l'username non sono corretti 1</p>";
    $html = str_replace('%error_message%', $messaggio, $html);

} else {
    $row = $result->fetch_assoc();
    if(password_verify($password, $row['pass'])) {

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
        $html = str_replace('%error_message%', $messaggio, $html);

    }
}
} else {
    $html = str_replace('%error_message%', '', $html);

}
echo $html;
include('footer.php');
?>
