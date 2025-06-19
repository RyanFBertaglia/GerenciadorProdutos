USE dados;

CREATE TABLE Orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idUser INT NOT NULL,
    dataPedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pendente', 'Processando', 'Enviado', 'Entregue', 'Confirmado', 'Devolucao_Pendente', 'Devolvido', 'Devolucao_Rejeitada') DEFAULT 'Pendente',
    idFornecedor INT NOT NULL,
    dataConfirmacao TIMESTAMP NULL,
    dataDevolucao TIMESTAMP NULL,
    dataAprovacaoDevolucao TIMESTAMP NULL,
    dataRejeicaoDevolucao TIMESTAMP NULL,
    motivoDevolucao TEXT NULL,
    motivoRecusa TEXT NULL,
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (idUser) REFERENCES usuarios(id),
    FOREIGN KEY (idFornecedor) REFERENCES fornecedores(id)
);



CREATE TABLE IF NOT EXISTS OrderItems (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idOrder INT NOT NULL,
    idProduct INT NOT NULL,
    quantity INT NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (idOrder) REFERENCES Orders(id) ON DELETE CASCADE,
    FOREIGN KEY (idProduct) REFERENCES produtos(idProduct)
);


CREATE TABLE IF NOT EXISTS Payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idUser INT NOT NULL,
    idOrder INT NOT NULL,
    status VARCHAR(50) DEFAULT 'Pendente',
    datePayment TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (idUser) REFERENCES usuarios(id),
    FOREIGN KEY (idOrder) REFERENCES Orders(id)
);
