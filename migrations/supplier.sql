USE dados;

CREATE TABLE fornecedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    telefone VARCHAR(20) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--Dados de teste

INSERT INTO fornecedores (nome, email, senha, cpf, telefone) VALUES
('Fornecedor A Ltda', 'fornecedor.a@empresa.com', '$2y$10$ABC123DEF456GHI789JKL', '123.456.789/0001-01', '(11) 2222-3333'),
('Distribuidora B S.A.', 'contato@distribuidorab.com', '$2y$10$XYZ789ABC456DEF123GHI', '987.654.321/0001-02', '(21) 3333-4444'),
('Indústria C Eireli', 'vendas@industriac.com.br', '$2y$10$LMN456OPQ789RST123UVW', '456.789.123/0001-03', '(31) 4444-5555'),
('Atacadista D ME', 'sac@atacadistad.com.br', '$2y$10$QWE123RTY456UIO789PAS', '789.123.456/0001-04', '(41) 5555-6666'),
('Comércio E Ltda', 'comercial@comercioe.com', '$2y$10$DFG123HJK456LMN789OPQ', '321.654.987/0001-05', '(51) 6666-7777');