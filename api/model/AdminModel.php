<?php

namespace Api\Model;
use PDO;

require_once __DIR__ . '/../config/db.php';


class AdminModel implements UserInterface {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function login($email, $senha) {
        $stmt = $this->pdo->prepare("SELECT * FROM admin WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            session_start();
            $_SESSION['admin'] = $usuario;
            return true;
        }
        return false;
    }

    public function cadastro(array $userData) {
        $_SESSION['erro'] = 'Não é possível cadastrar um administrador via API.';
        header('Location: /error');
        exit;
    }

    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM admin WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateUser($id, array $userData) {
        $fields = [];
        $values = [];
        
        foreach ($userData as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        
        $values[] = $id;
        $sql = "UPDATE admin SET " . implode(", ", $fields) . " WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    public function deleteUser($id) {
        $stmt = $this->pdo->prepare("DELETE FROM admin WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
    