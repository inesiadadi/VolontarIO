<?php
namespace DB;

class DBAccess {
    private const HOST_DB = "localhost";
    private const DATABASE_NAME = "volontario";
    private const USERNAME = "root";
    private const PASSWORD = "root";
    private $connection;

    public function openDBConnection() {
        // Da rimuovere quando si passerà al server di 
        // TecWeb perché questa funzione mostra gli errori di Database
        mysqli_report(MYSQLI_REPORT_ERROR);
        $this->connection = mysqli_connect(DBAccess::HOST_DB, 
                                           DBAccess::USERNAME, 
                                           DBAccess::PASSWORD, 
                                           DBAccess::DATABASE_NAME);
        if(mysqli_connect_errno()) return false;

        mysqli_set_charset($this->connection, "utf8mb4");
        return true;
    }

    public function closeDBConnection() {
        if($this->connection) mysqli_close($this->connection);
    }

    public function executeQuery($query) {
        if($result = mysqli_query($this->connection, $query)) {
            if ($result === true) return true;
            if(mysqli_num_rows($result) === 0) return array();
            $answer = array();
            while($row = mysqli_fetch_assoc($result)) array_push($answer, $row);
            $result->free();
            return $answer;
        }
        return null;
    }

    public function getStateDBConnection() { return $this->connection; }



    ## FUNZIONI DB ##

     public function loginAccount($username) {
        $check_username = $this->connection->prepare("SELECT username, idPersona, password FROM Persona WHERE username =  ?");
        $check_username->bind_param("s", $username);
        $check_username->execute() or die("Errore in dbConnection: " . mysqli_error($this->connection));
        $result = $check_username->get_result();
        if(mysqli_num_rows($result) != 0) {
            return $result;
        } else {
            return false;
        }
    }


    public function registerAccount($username, $password, $email, $nome, $cognome, $birthdate) {
        
        //Il blocco qui sotto è fatto in modo da aver una query "safe" invece di avere $username direttamente sparato nella query, ancora manca qualche step per avere solo valori alfanumerici
        $check_username_already_exist = $this->connection->prepare("SELECT username, email FROM Persona WHERE username = ? OR email = ?");
        $check_username_already_exist->bind_param("ss", $username, $email);
        $check_username_already_exist->execute() or die ("Errore in dbConnection: " . mysqli_error($this->connection));
        $result = $check_username_already_exist->get_result();
        if(mysqli_num_rows($result) == 0) {
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);
            $create_account = $this->connection->prepare("INSERT INTO Persona (username, email, password, nome, cognome, dataNascita) VALUES (?, ?, ?, ?, ?, ?)");
            $create_account->bind_param("ssssss", $username, $email, $password_hashed, $nome, $cognome, $birthdate);
            $create_account->execute() or die("Errore in dbConnection: " . mysqli_error($this->connection));
            return true;
        } else {
            return false;
        }
    }
};
?>