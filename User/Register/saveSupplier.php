<?php
session_start();

$host = "localhost";
$usuario = "root";
$senha_db = ""; 
$banco = "loja";

$conexao = new mysqli($host, $usuario, $senha_db, $banco);

if ($conexao->connect_error) {
    $_SESSION['erro'] = "Erro de conexão: " . $conexao->connect_error;
    header("Location: /static/elements/erro-cadastro.php");
    exit();
}

function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;
}

$nome = htmlspecialchars($_POST['nome'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$telefone = preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? '');
$cpf = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
$senha = $_POST['senha'] ?? '';
$confirmar_senha = $_POST['confirmar_senha'] ?? '';

// Validações
if ($senha !== $confirmar_senha) {
    $_SESSION['erro'] = "As senhas não coincidem!";
    $_SESSION['dados'] = $_POST;
    header("Location: /static/elements/erro-cadastro.php");
    exit();
}

if (!validarCPF($cpf)) {
    $_SESSION['erro'] = "CPF inválido!";
    $_SESSION['dados'] = $_POST;
    header("Location: /static/elements/erro-cadastro.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['erro'] = "E-mail inválido!";
    $_SESSION['dados'] = $_POST;
    header("Location: /static/elements/erro-cadastro.php");
    exit();
}

$sql_verifica = "SELECT id FROM fornecedores WHERE email = ? OR cpf = ?";
$stmt_verifica = $conexao->prepare($sql_verifica);

if (!$stmt_verifica) {
    $_SESSION['erro'] = "Erro na preparação da consulta: " . $conexao->error;
    header("Location: /static/elements/erro-cadastro.php");
    exit();
}

$stmt_verifica->bind_param("ss", $email, $cpf);

if (!$stmt_verifica->execute()) {
    $_SESSION['erro'] = "Erro na execução da consulta: " . $stmt_verifica->error;
    header("Location: /static/elements/erro-cadastro.php");
    exit();
}

$stmt_verifica->store_result();

if ($stmt_verifica->num_rows > 0) {
    $_SESSION['erro'] = "E-mail ou CPF já cadastrados!";
    $_SESSION['dados'] = $_POST;
    header("Location: /static/elements/erro-cadastro.php");
    exit();
}

// Criptografar senha
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

if (!$senha_hash) {
    $_SESSION['erro'] = "Erro ao criptografar a senha!";
    header("Location: /static/elements/erro-cadastro.php");
    exit();
}

$sql_insere = "INSERT INTO fornecedores (nome, email, telefone, cpf, senha) VALUES (?, ?, ?, ?, ?)";
$stmt_insere = $conexao->prepare($sql_insere);

if ($stmt_insere === false) {
    $_SESSION['erro'] = "Erro na preparação da query: " . $conexao->error;
    header("Location: /static/elements/erro-cadastro.php");
    exit();
}

$stmt_insere->bind_param("sssss", $nome, $email, $telefone, $cpf, $senha_hash);

if ($stmt_insere->execute()) {
    $_SESSION['erro'] = "";
    

    redirect('fornecedor/dashboard', [
        'nome' => $nome,
        'email' => $email,
    ]);
    exit();
} else {
    $_SESSION['erro'] = "Erro ao cadastrar: " . $stmt_insere->error;
    header("Location: /static/elements/erro-cadastro.php");
    exit();
}

$stmt_verifica->close();
$stmt_insere->close();
$conexao->close();
?>