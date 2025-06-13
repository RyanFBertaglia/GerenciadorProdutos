<?php
namespace backend\Models;

use \PDO;

class Posts {

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findById($idPost) {
        $stmt = $this->pdo->prepare("SELECT * FROM postagens WHERE idPost = :idPost LIMIT 1");
        $stmt->bindParam(':idPost', $idPost);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function findByUser($user) {
        $stmt = $this->pdo->prepare("SELECT * FROM postagens WHERE email = :user");
        $stmt->bindParam(':user', $user);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function create(array $data) {
        $stmt = $this->pdo->prepare("INSERT INTO postagens (email, titulo, descricao, imagens) 
                                     VALUES (:email, :titulo, :descricao, :imagens)");
    
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':titulo', $data['titulo']);
        $stmt->bindParam(':descricao', $data['descricao']);
        
        $imagensJson = json_encode($data['imagens'] ?? []);
        $stmt->bindParam(':imagens', $imagensJson);
    
        return $stmt->execute();
    }
    

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM postagens ORDER BY data_postagem DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

}