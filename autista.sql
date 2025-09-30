-- Script per la tabella degli autisti
CREATE TABLE IF NOT EXISTS autista (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    telefono VARCHAR(30),
    email VARCHAR(100),
    patente VARCHAR(30),
    tipo_patente VARCHAR(10),
    stato TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
