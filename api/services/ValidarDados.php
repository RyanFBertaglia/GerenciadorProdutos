<?php

namespace Api\Services;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class ValidarDados {
    public static function validarEmail($email) {
        if (empty($email)) {
            $_SESSION['erro'] = "Email não pode ser vazio!";
            header("Location: /erro");
            exit();
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['erro'] = "Email inválido!";
            header("Location: /erro");
            exit();
        }
        return true;
    }

    public static function validarSenha($senha) {
        if (empty($senha)) {
            $_SESSION['erro'] = "Senha não pode ser vazia!";
            header("Location: /erro");
            exit();
        }
        if (strlen($senha) < 6) {
            $_SESSION['erro'] = "Senha deve ter pelo menos 6 caracteres!";
            header("Location: /erro");
            exit();
        }

        return true;
    }

    public static function validarCPF($cpf) {
        if (empty($cpf)) {
            $_SESSION['erro'] = "CPF não pode ser vazio!";
            header("Location: /erro");
            exit();
        }

        if (preg_match('/(\d)\1{10}/', $cpf)) {
            $_SESSION['erro'] = "CPF inválido!";
            header("Location: /erro");
            exit();
        }
        return true;
    }

    public static function validarTelefone($telefone) {
        if (empty($telefone)) {
            $_SESSION['erro'] = "Telefone não pode ser vazio!";
            header("Location: /erro");
            exit();
        }

        return true;
    }

    public static function validarNome($nome) {
        if (empty($nome)) {
            $_SESSION['erro'] = "Nome não pode ser vazio!";
            header("Location: /erro");
            exit();
        }
        if (strlen($nome) < 3) {
            $_SESSION['erro'] = "Nome deve ter pelo menos 3 caracteres!";
            header("Location: /erro");
            exit();
        }
        return true;
    }

    public static function validarDataNascimento($dataNascimento) {
        if (empty($dataNascimento)) {
            $_SESSION['erro'] = "Data de nascimento não pode ser vazia!";
            header("Location: /erro");
            exit();
        }
        
        $data = \DateTime::createFromFormat('Y-m-d', $dataNascimento);
        
        if (!$data || $data->format('Y-m-d') !== $dataNascimento) {
            $_SESSION['erro'] = "Data de nascimento inválida!";
            header("Location: /erro");
            exit();
        }
        
        $hoje = new \DateTime();
        if ($data > $hoje) {
            $_SESSION['erro'] = "Data de nascimento não pode ser no futuro!";
            header("Location: /erro");
            exit();
        }
        
        return true;
    }

    public static function verificaExistencia($cpf, $email, $pdo) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE cpf = ? OR email = ?");
        $stmt->execute([$cpf, $email]);
        $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($usuario) {
            if ($usuario['cpf'] === $cpf) {
                $_SESSION['erro'] = "CPF já cadastrado!";
            } elseif ($usuario['email'] === $email) {
                $_SESSION['erro'] = "Email já cadastrado!";
            }
            header("Location: /erro");
            exit();
        }
        
        return true;
    }
}