-- Script per la tabella delle tipologie di noleggio
CREATE TABLE IF NOT EXISTS tipologie_noleggio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
