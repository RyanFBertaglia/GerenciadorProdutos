<?php

namespace Api\Model;
use PDO;
use Api\Services\ValidarDados;
use Api\Model\UserInterface;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class ClienteModel implements UserInterface {

    public function __construct(private PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    public function login($email, $senha) {

        ValidarDados::validarEmail($email);
        ValidarDados::validarSenha($senha);

        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            session_start();
            $_SESSION['usuario'] = $usuario;
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
        ValidarDados::validarDataNascimento($userData['data_nascimento'] ?? '');


        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nome, email, senha, cpf) VALUES (?, ?, ?, ?)");
        
        $userData['senha'] = password_hash($userData['senha'], PASSWORD_DEFAULT);
        
        if (!$stmt->execute([$userData['nome'], $userData['email'], $userData['senha'], $userData['cpf']])) {
            $_SEESSION['erro'] = "Erro ao cadastrar usuário: " . implode(", ", $stmt->errorInfo());
            header("Location: /erro");
            exit;
        }
        
        return true;
    }

    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateUser($id, array $userData) {
        // Separate the user ID from the data to be updated
        $userId = $id;
        $updateData = $userData;

        // Remove the ID from the update data if it exists
        unset($updateData['id']);

        $fields = [];
        $values = [];

        foreach ($updateData as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        
        // Add the user ID to the end of the values array
        $values[] = $userId;

        $sql = "UPDATE usuarios SET " . implode(", ", $fields) . " WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    public function deleteUser($id) {
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }

}