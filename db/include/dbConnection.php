<?php
namespace DB;

class DBAccess {
    private const HOST_DB = "localhost";
    private const DATABASE_NAME = "volontario";
    private const USERNAME = "root";
    private const PASSWORD = "root";
    private $connection;

    public function openDBConnection() {
        mysqli_report(MYSQLI_REPORT_ERROR);
        $this->connection = mysqli_connect(DBAccess::HOST_DB, 
                                           DBAccess::USERNAME, 
                                           DBAccess::PASSWORD, 
                                           DBAccess::DATABASE_NAME);
        return mysqli_connect_errno() ? false : true;
    }

    public function closeDBConnection() {
        if($this->connection) mysqli_close($this->connection);
    }

    public function executeQuery($query) {
        $result = mysqli_query($this->connection, $query) or 
                  die("Errore: " . mysqli_error($this->connection));
        if(!mysqli_num_rows($result)) return array();
        $answer = array();
        while($row = mysqli_fetch_assoc($result)) array_push($answer, $row);
        $result->free();
        return $answer;
    }

    public function getStateDBConnection() { return $this->connection; }
};
?>