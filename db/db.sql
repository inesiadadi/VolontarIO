CREATE DATABASE tecnoweb; -- Crea database
CREATE USER 'user'@'localhost' IDENTIFIED BY 'user'; -- Crea user utilizzabile poi sui file .php con mysqli
GRANT ALL PRIVILEGES ON tecnoweb.* TO 'user'@'localhost'; -- Da tutti i permessi a user
FLUSH PRIVILEGES; -- Ricarica tutti i permessi



CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    pass VARCHAR(255) NOT NULL
); -- Crea table users per tutti gli account

INSERT INTO users (username, pass) VALUES ('user', 'user'); -- Crea account base user | Da rifare perché la password non hashata
INSERT INTO users (username, pass) VALUES ('admin', 'admin'); -- Crea account base admin | Da rifare perché la password non hashata