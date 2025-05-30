USE dados;

ALTER TABLE produtos ADD COLUMN status ENUM('pendente', 'aprovado', 'rejeitado') DEFAULT 'pendente';
ALTER TABLE produtos ADD COLUMN motivo_rejeicao VARCHAR(255) DEFAULT NULL;
ALTER TABLE produtos ADD COLUMN aprovado_por INT DEFAULT NULL;
ALTER TABLE produtos ADD COLUMN data_aprovacao DATETIME DEFAULT NULL;
ALTER TABLE produtos ADD FOREIGN KEY (aprovado_por) REFERENCES usuarios(id);