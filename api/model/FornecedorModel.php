<?php

namespace Api\Model;
use PDO;

class FornecedorModel implements UserInterface {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function login($email, $senha) {
        $stmt = $this->pdo->prepare("SELECT * FROM fornecedores WHERE email = ?");
        $stmt->execute([$email]);
        $fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($fornecedor && password_verify($senha, $fornecedor['senha'])) {
            session_start();
            $_SESSION['fornecedor'] = $fornecedor;
            return true;
        }
        
        return false;
    }

    public function cadastro(array $userData) {
        $stmt = $this->pdo->prepare("INSERT INTO fornecedores (nome, email, senha, cpf, telefone) VALUES (?, ?, ?, ?, ?)");
        
        $userData['senha'] = password_hash($userData['senha'], PASSWORD_DEFAULT);
        
        if (!$stmt->execute([$userData['nome'], $userData['email'], $userData['senha'], $userData['cnpj']])) {
            throw new \Exception("Erro ao cadastrar fornecedor: " . implode(", ", $stmt->errorInfo()));
        }
        
        return true;
    }
}