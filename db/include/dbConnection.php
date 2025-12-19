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
};
?>