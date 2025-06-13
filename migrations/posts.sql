use dados;
USE dados;

CREATE TABLE postagens (
    idPost INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT NOT NULL,
    imagens JSON DEFAULT NULL,
    data_postagem TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


