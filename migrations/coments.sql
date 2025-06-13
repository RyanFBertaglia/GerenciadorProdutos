CREATE TABLE comentarios (
    idComentario INT AUTO_INCREMENT PRIMARY KEY,
    idPost INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    comentario TEXT NOT NULL,
    data_comentario TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idPost) REFERENCES postagens(idPost) ON DELETE CASCADE
);
