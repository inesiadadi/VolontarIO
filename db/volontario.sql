CREATE DATABASE volontario;

CREATE TABLE Citta (
    idCitta INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    provincia CHAR(2) NOT NULL,
    INDEX idx_nome_citta (nome)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO Citta (nome, provincia)
SELECT denominazione_ita, sigla_provincia
FROM gi_comuni;
ORDER BY denominazione_ita ASC;

DROP TABLE gi_comuni;

CREATE TABLE Persona (
    idPersona INT AUTO_INCREMENT PRIMARY KEY,
    
    username VARCHAR(25) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    immagine VARCHAR(100) DEFAULT NULL,
    ruolo ENUM('user', 'admin') NOT NULL DEFAULT 'user',

    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    dataNascita DATE NOT NULL,
    descrizione TEXT,
    
    idCitta INT DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT residenza 
        FOREIGN KEY (idCitta) REFERENCES Citta(idCitta)
        ON DELETE SET NULL

) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;



CREATE TABLE Tipologia (
    idTipo INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;



CREATE TABLE Richiesta (
    codiceRichiesta INT AUTO_INCREMENT PRIMARY KEY,

    descrizione TEXT,
    dataRichiesta DATETIME NOT NULL,
    stato ENUM('aperta', 'completata', 'in_carico') NOT NULL DEFAULT 'aperta',

    idRichiedente INT NOT NULL,
    idOfferente INT DEFAULT NULL,
    idTipo INT NOT NULL,
    idCitta INT NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT richiedente 
        FOREIGN KEY (idRichiedente) REFERENCES Persona(idPersona)
        ON DELETE CASCADE,
    
    CONSTRAINT offerente
        FOREIGN KEY (idOfferente) REFERENCES Persona(idPersona)
        ON DELETE SET NULL,

    CONSTRAINT offerenteNotRichiedente
        CHECK (idRichiedente <> idOfferente),
    
    CONSTRAINT tipologia
        FOREIGN KEY (idTipo) REFERENCES Tipologia(idTipo),
    
    CONSTRAINT luogo
        FOREIGN KEY (idCitta) REFERENCES Citta(idCitta)

) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;



CREATE TABLE Valutazione (
    idValutazione INT AUTO_INCREMENT PRIMARY KEY,

    descrizione TEXT,
    voto TINYINT UNSIGNED NOT NULL,

    idRichiesta INT NOT NULL UNIQUE,

    CONSTRAINT richiestaValutata
        FOREIGN KEY (idRichiesta) REFERENCES Richiesta(codiceRichiesta)
        ON DELETE CASCADE,

    CONSTRAINT votoValido
        CHECK (voto >= 1 AND voto <= 10)

) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
