<?php
namespace DB;

class DBAccess {
    private const HOST_DB = "localhost";
    private const DATABASE_NAME = "volontario";
    private const USERNAME = "root";
    private const PASSWORD = "";
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

    //Scomoda, meglio definire una funzione per query, in modo da migliorarne la gestione
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
    
    //Probabilmente devo definire una query per poter 
    public function getUtentePerId($idPersona) {
        $sql = "SELECT P.*, C.nome AS nome_citta, C.provincia 
                FROM Persona P 
                LEFT JOIN Citta C ON P.idCitta = C.idCitta 
                WHERE P.idPersona = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPersona);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    public function getStatisticheRecensioni($idVolontario) {
        $sql = "SELECT COUNT(V.idValutazione) as num_recensioni, AVG(V.voto) as media_voto 
                FROM Valutazione V
                JOIN Richiesta R ON V.idRichiesta = R.codiceRichiesta
                WHERE R.idOfferente = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idVolontario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    public function getRecensioniRicevute($idVolontario) {
        $sql = "SELECT 
                    V.voto, 
                    V.descrizione AS testo_recensione, 
                    R.dataRichiesta, 
                    T.nome AS nome_categoria,
                    P.idPersona AS id_autore, 
                    P.username AS nome_autore, 
                    P.immagine AS foto_autore
                FROM Valutazione V
                JOIN Richiesta R ON V.idRichiesta = R.codiceRichiesta
                JOIN Tipologia T ON R.idTipo = T.idTipo
                JOIN Persona P ON R.idRichiedente = P.idPersona 
                WHERE R.idOfferente = ?
                ORDER BY R.dataRichiesta DESC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idVolontario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $recensioni = [];
        while($row = $result->fetch_assoc()) {
            $recensioni[] = $row;
        }
        return $recensioni;
    }

    public function getRichiesteEffettuate($idVolontario) {
        $sql = "SELECT R.*, T.nome AS tipologia, C.nome as citta
                FROM Richiesta R
                JOIN Tipologia T ON R.idTipo = T.idTipo
                JOIN Citta C ON R.idCitta = C.idCitta
                WHERE idRichiedente = ?
                ORDER BY dataRichiesta DESC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idVolontario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $richieste = [];
        while($row = $result->fetch_assoc()) {
            $richieste[] = $row;
        }
        return $richieste;
    }

    public function checkUpdateRequests() {
        $query = "SELECT codiceRichiesta, stato, dataRichiesta FROM Richiesta";
        $result = $this->connection->query($query);

        while ($rec = $result->fetch_assoc()) {
            $aggiorna = false;
            $nuovoStato = $rec['stato'];
            $ora = new \DateTime();

            $dataRichiesta = new \DateTime($rec['dataRichiesta']);

            if ($rec['stato'] == 'aperta' && $dataRichiesta < $ora) {
                $nuovoStato = 'scaduta';
                $aggiorna = true;
            }
            else if ($rec['stato'] == 'in_carico' && $dataRichiesta < $ora) {
                $nuovoStato = 'completata';
                $aggiorna = true;
            }

            if ($aggiorna) {
                $stmt = $this->connection->prepare("UPDATE Richiesta SET stato = ? WHERE codiceRichiesta = ?");
                $stmt->bind_param("si", $nuovoStato, $rec['codiceRichiesta']);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    
}
?>