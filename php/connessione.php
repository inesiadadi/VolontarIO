<?php 

namespace DB;
class DBAccess {
    private const HOST_DB = '127.0.0.1';
    private const DB   = 'volontario';
    private const USERNAME = 'user';
    private const PASSWORD = 'user';
    
    private $connessione;

    public function openDBConnection() {

		$this->connessione = mysqli_connect(DBAccess::HOST_DB, DBAccess::USERNAME, DBAccess::PASSWORD, DBAccess::DB);
		if(mysqli_connect_errno()) {
			return false;
		} else {
			return true;
		}
		
	}

	public function closeConnection() {
		mysqli_close($this->connessione);
	}
    
    public function loginAccount($username) {
        $check_username = $this->connessione->prepare("SELECT username, idPersona, password FROM Persona WHERE username =  ?");
        $check_username->bind_param("s", $username);
        $check_username->execute() or die("Errore in dbConnection: " . mysqli_error($this->connessione));
        $result = $check_username->get_result();
        if(mysqli_num_rows($result) != 0) {
            return $result;
        } else {
            return false;
        }
    }


    public function registerAccount($username, $password, $email, $nome, $cognome, $birthdate) {
        
        //Il blocco qui sotto è fatto in modo da aver una query "safe" invece di avere $username direttamente sparato nella query, ancora manca qualche step per avere solo valori alfanumerici
        $check_username_already_exist = $this->connessione->prepare("SELECT username, email FROM Persona WHERE username = ? OR email = ?");
        $check_username_already_exist->bind_param("ss", $username, $email);
        $check_username_already_exist->execute() or die ("Errore in dbConnection: " . mysqli_error($this->connessione));
        $result = $check_username_already_exist->get_result();
        if(mysqli_num_rows($result) == 0) {
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);
            echo $birthdate;
            $create_account = $this->connessione->prepare("INSERT INTO Persona (username, email, password, nome, cognome, dataNascita) VALUES (?, ?, ?, ?, ?, ?)");
            $create_account->bind_param("ssssss", $username, $email, $password_hashed, $nome, $cognome, $birthdate);
            $create_account->execute() or die("Errore in dbConnection: " . mysqli_error($this->connessione));
            return true;
        } else {
            return false;
        }
    }
}
?>
    