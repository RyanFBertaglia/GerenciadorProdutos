USE dados;

CREATE TABLE produtos (
    idProduct INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description VARCHAR(255) NOT NULL,
    supplier INT NOT NULL,
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
    49.99, 'Camiseta básica branca', 2, 100, 'camiseta.jpg', 'pendente'
);

-- Produto aprovado
INSERT INTO produtos (
    price, description, supplier, stock, image, status, aprovado_por, data_aprovacao
) VALUES (
    99.90, 'Tênis esportivo leve', 1, 50, 'tenis.jpg', 'aprovado', 1, NOW()
);

-- Produto rejeitado
INSERT INTO produtos (
    price, description, supplier, stock, image, status, motivo_rejeicao
) VALUES (
    29.50, 'Boné estiloso com defeito', 1, 20, 'bone.jpg', 'rejeitado', 'Produto com costura defeituosa'
);
