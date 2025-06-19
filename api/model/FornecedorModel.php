<?php

namespace Api\Model;
use Api\Services\ValidarDados;

use PDO;

class FornecedorModel implements UserInterface {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function login($email, $senha) {
        ValidarDados::validarEmail($email);
        ValidarDados::validarSenha($senha);
        
        $stmt = $this->pdo->prepare("SELECT * FROM fornecedores WHERE email = ?");
        $stmt->execute([$email]);
        $fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($fornecedor && password_verify($senha, $fornecedor['senha'])) {
            session_start();
            $_SESSION['fornecedor'] = $fornecedor;
            return true;
        }
        $_SESSION['erro'] = "Email ou senha inválidos.";
        header("Location: /erro");
        exit; 
    }

    public function cadastro(array $userData) {
        ValidarDados::validarNome($userData['nome']);
        ValidarDados::validarEmail($userData['email']);
        ValidarDados::validarSenha($userData['senha']);
        ValidarDados::validarCpf($userData['cpf']);
        ValidarDados::validarTelefone($userData['telefone'] ?? '');

        $stmt = $this->pdo->prepare("INSERT INTO fornecedores (nome, email, senha, cpf, telefone) VALUES (?, ?, ?, ?, ?)");
        
        $userData['senha'] = password_hash($userData['senha'], PASSWORD_DEFAULT);
        
        if (!$stmt->execute([$userData['nome'], $userData['email'], $userData['senha'], $userData['cpf'], $userData['telefone']])) {
            throw new \Exception("Erro ao cadastrar fornecedor: " . implode(", ", $stmt->errorInfo()));
        }
        
        return true;
    }

    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM fornecedores WHERE id = ?");
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
        $sql = "UPDATE fornecedores SET " . implode(", ", $fields) . " WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    public function deleteUser($id) {
        $stmt = $this->pdo->prepare("DELETE FROM fornecedores WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAllFornecedores() {
        $stmt = $this->pdo->query("SELECT * FROM fornecedores");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFornecedorByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM fornecedores WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listarProdutos($fornecedorId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM produtos 
            WHERE supplier = ?
            ORDER BY 
                CASE status 
                    WHEN 'pendente' THEN 1
                    WHEN 'rejeitado' THEN 2
                    WHEN 'aprovado' THEN 3
                END,
                idProduct DESC
        ");
        $stmt->execute([$fornecedorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarProdutosAprovados($fornecedorId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM produtos WHERE supplier = ? AND status = 'aprovado'");
        $stmt->execute([$fornecedorId]);
        return $stmt->fetchColumn();
    }

    public function obterVendas($fornecedorId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                SUM(vendidos) as total_vendidos,
                SUM(vendidos * price) as total_vendas
            FROM produtos 
            WHERE supplier = ? AND status = 'aprovado'
        ");
        $stmt->execute([$fornecedorId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function excluirProduto($produtoId, $fornecedorId) {
        $stmt = $this->pdo->prepare("SELECT image FROM produtos WHERE idProduct = ? AND supplier = ?");
        $stmt->execute([$produtoId, $fornecedorId]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($produto) {
            if (!empty($produto['image']) && file_exists("../assets/uploads/" . $produto['image'])) {
                unlink("../assets/uploads/" . $produto['image']);
            }
            
            $stmt = $this->pdo->prepare("DELETE FROM produtos WHERE idProduct = ?");
            return $stmt->execute([$produtoId]);
        }
        
        return false;
    }
}