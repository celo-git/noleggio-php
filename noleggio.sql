-- Script per creare la tabella noleggio
CREATE TABLE IF NOT EXISTS noleggio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    tipologia_noleggio_id INT NOT NULL,
    automezzo_id INT DEFAULT NULL,
    data_inizio DATE NOT NULL,
    data_fine DATE NOT NULL,
    importo DECIMAL(10,2) DEFAULT NULL,
    destinazione VARCHAR(255) DEFAULT NULL,
    accompagnatore VARCHAR(100) DEFAULT NULL,
    preventivo TINYINT(1) NOT NULL DEFAULT 0,
    pagato TINYINT(1) NOT NULL DEFAULT 0,
    ivato TINYINT(1) NOT NULL DEFAULT 0,
    autista1_id INT DEFAULT NULL,
    autista2_id INT DEFAULT NULL,
    -- puoi aggiungere altri campi come note, ecc.
    FOREIGN KEY (cliente_id) REFERENCES cliente(id) ON DELETE CASCADE,
    FOREIGN KEY (tipologia_noleggio_id) REFERENCES tipologie_noleggio(id) ON DELETE RESTRICT,
    FOREIGN KEY (automezzo_id) REFERENCES automezzo(id) ON DELETE SET NULL,
    FOREIGN KEY (autista1_id) REFERENCES autista(id) ON DELETE SET NULL,
    FOREIGN KEY (autista2_id) REFERENCES autista(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
