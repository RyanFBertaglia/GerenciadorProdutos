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
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAllUsers($limit, $offset) {
    $stmt = $this->pdo->prepare("
        SELECT
            usuarios.id,
            usuarios.nome,
            usuarios.email,
            SUM(CASE
                WHEN orders.motivoDevolucao IS NULL THEN orders.total
                ELSE 0
            END) AS gasto,
            bankaccount.balance AS saldo
        FROM usuarios
        LEFT JOIN orders ON usuarios.id = orders.idUser
        LEFT JOIN bankaccount ON usuarios.id = bankaccount.idUser
        GROUP BY usuarios.id, usuarios.nome, usuarios.email, bankaccount.balance
        ORDER BY gasto DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public function updateUserSaldo($id, $valor) {
    $stmt = $this->pdo->prepare("SELECT idUser FROM bankaccount WHERE idUser = ? LIMIT 1");
    $stmt->execute([$id]);
    $conta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$conta) {
        $stmt = $this->pdo->prepare("INSERT INTO bankaccount (idUser, tipo, status, balance) VALUES (?, 'usuario', 'A', 0)");
        $stmt->execute([$id]);
    }

    $stmt = $this->pdo->prepare("UPDATE bankaccount SET balance = balance + ? WHERE idUser = ?");
    return $stmt->execute([$valor, $id]);
}
    
    public function getTotalUsers() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM usuarios");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

}
    