<?php 
$title = "Login | VolontarIO";
include('header.php');
$messaggio = "";


if(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
$username = $_POST['username'];
$password = $_POST['password'];
$check_username = $connessione->prepare("SELECT username, id, pass FROM users WHERE username =  ?");
$check_username->bind_param("s", $username);
$check_username->execute();
$result = $check_username->get_result();
if ($result->num_rows == 0) {
    $messaggio = "<p class='error'>La password o l'username non sono corretti 1</p>";
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
    }
}
}
?>
<main>
    <h1>Login</h1>
    <form action="/login" method="post">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username"></input>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password"></input>
        <button type="submit">Accedi</button>
    </form>
        <?= $messaggio ?>
        <p>Non sei registrato? <a href="/register">Registrati!</a></p>
</main>
<?php
include('footer.php');
?>
