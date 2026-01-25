<?php
namespace DB;


class DBAccess {
    private const HOST_DB = "127.0.0.1";
    private const DATABASE_NAME = "volontario";
    private const USERNAME = "root";
    private const PASSWORD = "";
    //private const PORT = 8889;
    //private const PASSWORD = "root";
    private $connection;

    public function openDBConnection() {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $this->connection = mysqli_connect(
                DBAccess::HOST_DB, 
                DBAccess::USERNAME, 
                DBAccess::PASSWORD, 
                DBAccess::DATABASE_NAME,
                //DBAccess::PORT
            );

            mysqli_set_charset($this->connection, "utf8mb4");
            return true;

        } catch (\mysqli_sql_exception $e) {
            return false;
        }
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

    public function getCandidatureEffettuate($idVolontario) {
        $this->checkUpdateRequests();
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
        
    public function getTipologie() {
        $sql = "SELECT * FROM Tipologia ORDER BY nome ASC";
        $result = $this->connection->query($sql);

        $categories = [];
        while($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }

        return $categories;
    }

    public function aggiungiNuovaRichiesta($descrizione, $dataRichiesta, $idRichiedente, $idTipo, $idCitta) {
        $sql = "INSERT INTO Richiesta (descrizione, dataRichiesta, stato, idRichiedente, idTipo, idCitta, created_at, updated_at) VALUES (?, ?, 'aperta', ?, ?, ?, NOW(), NOW())";

        $query = $this->connection->prepare($sql);
        $query->bind_param('ssiii', $descrizione, $dataRichiesta, $idRichiedente, $idTipo, $idCitta);
        if (!$query->execute()) {
                // Questo ti dirà l'errore esatto del database (es. "Incorrect datetime value")
                error_log("Errore SQL: " . $this->connection->error);
                return false;
        }
        return true;
    }

    public function rimuoviRichiesta($idRichiesta) {
        $sql = "DELETE FROM Richiesta WHERE codiceRichiesta = ?";
        $query = $this->connection->prepare($sql);
        $query->bind_param('i', $idRichiesta);
        if (!$query->execute()) {
                // Questo ti dirà l'errore esatto del database (es. "Incorrect datetime value")
                error_log("Errore SQL: " . $this->connection->error);
                return false;
        }
        return true;
    }
    
    public function addReview() {}

    public function modificaProfiloUtente($idPersona, $nome, $cognome, $dataNascita, $descrizione, $immagine, $idCitta, $email, $password) {
        $sql = "UPDATE Persona 
                SET nome = ?, 
                    cognome = ?, 
                    dataNascita = ?, 
                    descrizione = ?, 
                    immagine = ?, 
                    idCitta = ?, 
                    email = ?, 
                    password = ?, 
                    updated_at = NOW() 
                WHERE idPersona = ?";

        $stmt = $this->connection->prepare($sql);

        $stmt->bind_param("sssssissi", 
            $nome, 
            $cognome, 
            $dataNascita, 
            $descrizione, 
            $immagine, 
            $idCitta, 
            $email, 
            $password, 
            $idPersona
        );

        $successo = $stmt->execute();
        $stmt->close(); // Buona pratica chiudere lo statement
        
        return $successo;
    }

    ##BACHECA##
    public function accettaRichiesta($idRichiesta, $idOfferente) {
        $sql = "UPDATE Richiesta 
                SET idOfferente = ?, stato = 'in_carico' 
                WHERE codiceRichiesta = ? 
                AND idRichiedente != ? 
                AND stato = 'aperta'";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("iii", $idOfferente, $idRichiesta, $idOfferente);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
    
    public function getRichieste($date = null, $cittaNome = '', $catIds = []) {
        $sql = "SELECT 
                    R.codiceRichiesta AS id,
                    R.descrizione,
                    R.dataRichiesta AS data_pubblicazione,
                    R.idRichiedente AS id_utente_creatore,
                    P.username AS nome_utente,
                    P.immagine AS foto_utente,
                    T.nome AS nome_categoria,
                    C.nome AS nome_citta
                FROM Richiesta R
                JOIN Persona P ON R.idRichiedente = P.idPersona
                JOIN Tipologia T ON R.idTipo = T.idTipo
                JOIN Citta C ON R.idCitta = C.idCitta
                WHERE R.stato = 'aperta'";
        $params = [];
        $types = "";
        if (!empty($date)) {
            $sql .= " AND DATE(R.dataRichiesta) = ?";
            $types .= "s";
            $params[] = $date;
        }
        if (!empty($cittaNome)) {
            $sql .= " AND C.nome LIKE ?";
            $types .= "s";
            $params[] = "%" . $cittaNome . "%"; 
        }
        if (!empty($catIds)) {
            $placeholders = implode(',', array_fill(0, count($catIds), '?'));
            
            $sql .= " AND R.idTipo IN ($placeholders)";
            $types .= str_repeat('i', count($catIds));
            
            foreach ($catIds as $id) {
                $params[] = $id;
            }
        }
        $sql .= " ORDER BY R.dataRichiesta DESC";
        $stmt = $this->connection->prepare($sql);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $richieste = [];
        while($row = $result->fetch_assoc()) {
            $row['luogo'] = $row['nome_citta']; 
            $richieste[] = $row;
        }
        return $richieste;
    }  

    public function getComuniPerRichieste($char) {
        $ricerca = $char . '%';
        $query = "SELECT * FROM Citta WHERE nome LIKE ? ORDER BY nome ASC LIMIT 10";

        $sql = $this->connection->prepare($query);
        $sql->bind_param('s', $ricerca);
        $sql->execute();
        $result = $sql->get_result();

        $comuni = [];
        while($row = $result->fetch_assoc()) {
            $comuni[] = $row;
        }

        return $comuni;
    } 
    ## FUNZIONI DB ##

     public function loginAccount($username) {
        $check_username = $this->connection->prepare("SELECT username, idPersona, ruolo, password FROM Persona WHERE username =  ?");
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
            $id = $this->connection->insert_id;
            // DA RICORDARE: bisogna avere almeno un user nel database senno' return 0 = false
            return $id;
        } else {
            return false;
        }
    }
    
    ## PANNELLO ADMIN

    public function getMessaggi() {
        $query = $this->connection->prepare("SELECT id, nome_cognome, email, numero, messaggio, data_invio FROM assistenza");
        $query->execute() or die ("Errore in dbConnection" . mysqli_error($this->connection));
        $result = $query->get_result();
        if(mysqli_num_rows($result) == 0) {
            $result = false;
        }
        return $result;
    }
    public function deleteMessaggio($idMessaggio) {
        $query = $this->connection->prepare("DELETE FROM assistenza WHERE id = ?");
        $query->bind_param("i", $idMessaggio);
        $query->execute();
        }
    public function saveMessaggio($nome_cognome, $email, $numero, $messaggio) {
        $query = $this->connection->prepare("INSERT INTO assistenza (nome_cognome, email, numero, messaggio) VALUES (?, ?, ?, ?)");
        $query->bind_param("ssis", $nome_cognome, $email, $numero, $messaggio);
        $query->execute() or die("Errore in dbConnection: " . mysqli_error($this->connection));
    }
};

?>
