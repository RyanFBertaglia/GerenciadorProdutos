USE dados;

-- Criação da tabela BankAccount
CREATE TABLE BankAccount (
    idAccount INT AUTO_INCREMENT PRIMARY KEY,
    idUser INT DEFAULT NULL,
    idFornecedor INT DEFAULT NULL,
    tipo ENUM('usuario', 'fornecedor') NOT NULL,
    balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('A', 'I') NOT NULL DEFAULT 'A',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Conta do usuário João
INSERT INTO BankAccount (idUser, tipo, balance, status) VALUES
(1, 'usuario', 500.00, 'A');

-- Conta do fornecedor A
INSERT INTO BankAccount (idFornecedor, tipo, balance, status) VALUES
(1, 'fornecedor', 1000.00, 'A');
