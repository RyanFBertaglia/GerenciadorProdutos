USE dados;

CREATE TABLE produtos (
    idProduct INT AUTO_INCREMENT PRIMARY KEY,
    price DECIMAL(10, 2) NOT NULL,
    description VARCHAR(255) NOT NULL,
    supplier VARCHAR(100) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) NOT NULL,
    status ENUM('pendente', 'aprovado', 'rejeitado') DEFAULT 'pendente',
    motivo_rejeicao VARCHAR(255) DEFAULT NULL,
    aprovado_por INT DEFAULT NULL,
    data_aprovacao DATETIME DEFAULT NULL,
    FOREIGN KEY (aprovado_por) REFERENCES usuarios(id)
);


-- Produto pendente
INSERT INTO produtos (
    price, description, supplier, stock, image, status
) VALUES (
    49.99, 'Camiseta básica branca', 'Fornecedor XYZ', 100, 'camiseta.jpg', 'pendente'
);

-- Produto aprovado
INSERT INTO produtos (
    price, description, supplier, stock, image, status, aprovado_por, data_aprovacao
) VALUES (
    99.90, 'Tênis esportivo leve', 'Fornecedor ABC', 50, 'tenis.jpg', 'aprovado', 1, NOW()
);

-- Produto rejeitado
INSERT INTO produtos (
    price, description, supplier, stock, image, status, motivo_rejeicao
) VALUES (
    29.50, 'Boné estiloso com defeito', 'Fornecedor DEF', 20, 'bone.jpg', 'rejeitado', 'Produto com costura defeituosa'
);
