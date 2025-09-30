DROP TABLE IF EXISTS fattura;
-- Tabella fattura aggiornata con campo IVA
CREATE TABLE fattura (
    id INT AUTO_INCREMENT PRIMARY KEY,
    noleggio_id INT NOT NULL,
    numero VARCHAR(50) NOT NULL,
    data DATE NOT NULL,
    importo DECIMAL(10,2) NOT NULL,
    iva DECIMAL(5,2) DEFAULT NULL, -- aliquota IVA percentuale (es: 22.00)
    iva_calcolata DECIMAL(10,2) DEFAULT NULL, -- importo IVA calcolato
    totale_con_iva DECIMAL(10,2) DEFAULT NULL, -- totale importo + IVA
    descrizione VARCHAR(255),
    FOREIGN KEY (noleggio_id) REFERENCES noleggio(id) ON DELETE CASCADE
);
