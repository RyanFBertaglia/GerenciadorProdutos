<?php

namespace Api\Model;
use PDO;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class AdminModel implements UserInterface {
    
    public function __construct(private PDO $pdo) {
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
        $_SESSION['erro'] = "Email ou senha inválidos.";
        header("Location: /erro");
        exit;
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

    public function getAllUsers() {
        $stmt = $this->pdo->query("SELECT * FROM usuarios");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateUserSaldo($id, $valor) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET saldo = saldo + ? WHERE id = ?");
        return $stmt->execute([$valor, $id]);
    }
}
    