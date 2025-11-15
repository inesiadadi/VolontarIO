CREATE DATABASE tecnoweb; -- Crea database
CREATE USER 'user'@'localhost' IDENTIFIED BY 'user'; -- Crea user utilizzabile poi sui file .php con mysqli
GRANT ALL PRIVILEGES ON tecnoweb.* TO 'user'@'localhost'; -- Da tutti i permessi a user
FLUSH PRIVILEGES; -- Ricarica tutti i permessi