USE dados;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    telefone VARCHAR(20) NOT NULL,
    endereco TEXT NOT NULL,
    data_nascimento DATE NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--Dados de teste

INSERT INTO usuarios (nome, email, senha, cpf, telefone, endereco, data_nascimento) VALUES
('João Silva', 'joao.silva@email.com', '$2y$10$ABC123DEF456GHI789JKL', '123.456.789-01', '(11) 98765-4321', 'Rua das Flores, 123 - São Paulo/SP', '1985-05-15'),
('Maria Oliveira', 'maria.oliveira@email.com', '$2y$10$XYZ789ABC456DEF123GHI', '987.654.321-09', '(21) 99876-5432', 'Avenida Brasil, 456 - Rio de Janeiro/RJ', '1990-08-22'),
('Carlos Souza', 'carlos.souza@email.com', '$2y$10$LMN456OPQ789RST123UVW', '456.789.123-45', '(31) 98765-1234', 'Rua Minas Gerais, 789 - Belo Horizonte/MG', '1982-11-30'),
('Ana Pereira', 'ana.pereira@email.com', '$2y$10$QWE123RTY456UIO789PAS', '789.123.456-78', '(41) 99876-4321', 'Avenida Paraná, 101 - Curitiba/PR', '1995-03-10'),
('Pedro Costa', 'pedro.costa@email.com', '$2y$10$DFG123HJK456LMN789OPQ', '321.654.987-32', '(51) 98765-6789', 'Rua dos Andradas, 202 - Porto Alegre/RS', '1988-07-18');