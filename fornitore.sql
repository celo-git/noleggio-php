CREATE TABLE IF NOT EXISTS fornitore (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    telefono VARCHAR(50),
    email VARCHAR(100),
    indirizzo VARCHAR(255),
    note VARCHAR(255)
);