-- Script per la tabella degli automezzi
CREATE TABLE IF NOT EXISTS automezzo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    modello VARCHAR(100) NOT NULL,
    marca VARCHAR(100) NOT NULL,
    targa VARCHAR(20) NOT NULL UNIQUE,
    anno YEAR NOT NULL,
    colore VARCHAR(50) NOT NULL,
    stato TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
