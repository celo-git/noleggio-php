DROP TABLE IF EXISTS cliente;
-- Script per la tabella dei clienti
CREATE TABLE IF NOT EXISTS cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    codice_fiscale VARCHAR(20),
    partita_iva VARCHAR(20),
    telefono VARCHAR(30),
    email VARCHAR(100),
    indirizzo VARCHAR(255),
    password VARCHAR(255),
    stato TINYINT(1) NOT NULL DEFAULT 1,
    privacy_marketing TINYINT(1) NOT NULL DEFAULT 0,
    privacy_terzi TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    privacy_consenso TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
