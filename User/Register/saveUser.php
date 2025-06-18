<?php
session_start();


$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "loja";

$conexao = new mysqli($host, $usuario, $senha, $banco);


if ($conexao->connect_error) {
    $_SESSION['erro'] = "Erro de conexão com o banco de dados: " . $conexao->connect_error;
    header("Location: /erro");
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
$senha = $_POST['senha'] ?? '';
$cpf = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
$phone = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');
$birthdate = $_POST['birthdate'] ?? '';

$endereco_completo = htmlspecialchars($_POST['endereco_completo'] ?? '');

if (empty($endereco_completo)) {
    $cep = $_POST['cep'] ?? '';
    $logradouro = $_POST['logradouro'] ?? '';
    $numero = $_POST['numero'] ?? '';
    $bairro = $_POST['bairro'] ?? '';
    $cidade = $_POST['cidade'] ?? '';
    $uf = $_POST['uf'] ?? '';
    
    if (!empty($cep) && !empty($logradouro) && !empty($numero) && !empty($bairro) && !empty($cidade) && !empty($uf)) {
        $endereco_completo = "$cep, $logradouro, $numero, $bairro, $cidade-$uf";
    }
}

// Validação de CPF
if (!validarCPF($cpf)) {
    $_SESSION['erro'] = "CPF inválido!";
    $_SESSION['dados'] = $_POST; // Armazena dados para possível recuperação
    header("Location: /erro");
    exit();
}

// Verifica se email já existe
$sql_verifica = "SELECT id FROM usuarios WHERE email = ?";
$stmt_verifica = $conexao->prepare($sql_verifica);

if (!$stmt_verifica) {
    $_SESSION['erro'] = "Erro na preparação da consulta: " . $conexao->error;
    header("Location: /erro");
    exit();
}

$stmt_verifica->bind_param("s", $email);
if (!$stmt_verifica->execute()) {
    $_SESSION['erro'] = "Erro na execução da consulta: " . $stmt_verifica->error;
    header("Location: /erro");
    exit();
}

$stmt_verifica->store_result();

if ($stmt_verifica->num_rows > 0) {
    $_SESSION['erro'] = "Este e-mail já está cadastrado!";
    $_SESSION['dados'] = $_POST;
    header("Location: /erro");
    exit();
}

// Verifica se CPF já existe
$sql_verificaCpf = "SELECT id FROM usuarios WHERE cpf = ?";
$stmt_verificaCpf = $conexao->prepare($sql_verificaCpf);

if (!$stmt_verificaCpf) {
    $_SESSION['erro'] = "Erro na preparação da consulta: " . $conexao->error;
    header("Location: /erro");
    exit();
}

$stmt_verificaCpf->bind_param("s", $cpf);
if (!$stmt_verificaCpf->execute()) {
    $_SESSION['erro'] = "Erro na execução da consulta: " . $stmt_verificaCpf->error;
    header("Location: /static/elements/erro-cadastro");
    exit();
}

$stmt_verificaCpf->store_result();

if ($stmt_verificaCpf->num_rows > 0) {
    $_SESSION['erro'] = "Este CPF já está cadastrado!";
    $_SESSION['dados'] = $_POST;
    header("Location: /erro");
    exit();
}

// Criptografa a senha
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);
if (!$senha_hash) {
    $_SESSION['erro'] = "Erro ao criptografar a senha!";
    header("Location: /erro");
    exit();
}

$sql_insere = "INSERT INTO usuarios (nome, email, senha, cpf, telefone, endereco, data_nascimento)
               VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt_insere = $conexao->prepare($sql_insere);

if ($stmt_insere === false) {
    $_SESSION['erro'] = "Erro na preparação da query: " . $conexao->error;
    header("Location: /erro");
    exit();
}

$stmt_insere->bind_param("sssssss", $nome, $email, $senha_hash, $cpf, $phone, $endereco_completo, $birthdate);

if ($stmt_insere->execute()) {
    $_SESSION['erro'] = "";
    header("Location: /login");
    exit;
} else {
    $_SESSION['erro'] = "Erro ao cadastrar: " . $stmt_insere->error;
    header("Location: /erro");
    exit();
}

$stmt_verifica->close();
$stmt_insere->close();
$conexao->close();
?>