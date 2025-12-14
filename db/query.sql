/* Informazioni per la colonna di sinistra dell'utente + descrizione utente */
SELECT p.username, p.email, p.immagine, p.nome, p.cognome, p.dataNascita, p.descrizione, c.nome, c.provincia 
FROM Persona AS p JOIN Citta AS c ON p.idCitta = c.idCitta
WHERE p.username = "usernameUtente";


SELECT AVG(VOTO)



/* Query per l'inserimento dei comuni all'interno del DB */
INSERT INTO Citta (nome, provincia)
SELECT denominazione_ita, sigla_provincia
FROM gi_comuni;
ORDER BY denominazione_ita ASC;