<?php
$host = "localhost";
$usuario = "root";
$senha_db = ""; // Senha do seu MySQL (vazia no XAMPP padrão)
$banco = "cadastro"; // Mesmo banco ou crie um específico para fornecedores

$conexao = new mysqli($host, $usuario, $senha_db, $banco);

if ($conexao->connect_error) {
    die("Erro de conexão: " . $conexao->connect_error);
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

$nome = htmlspecialchars($_POST['nome']);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$telefone = preg_replace('/[^0-9]/', '', $_POST['telefone']);
$cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
$senha = $_POST['senha'];
$confirmar_senha = $_POST['confirmar_senha'];

// Validações
if ($senha !== $confirmar_senha) {
    die("Erro: As senhas não coincidem!");
}

if (!validarCPF($cpf)) {
    die("Erro: CPF inválido!");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Erro: E-mail inválido!");
}

$sql_verifica = "SELECT id FROM fornecedores WHERE email = ? OR cpf = ?";
$stmt_verifica = $conexao->prepare($sql_verifica);
$stmt_verifica->bind_param("ss", $email, $cpf);
$stmt_verifica->execute();
$stmt_verifica->store_result();

if ($stmt_verifica->num_rows > 0) {
    die("Erro: E-mail ou CPF já cadastrados!");
}

// Criptografar senha
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

$sql_insere = "INSERT INTO fornecedores (nome, email, telefone, cpf, senha) VALUES (?, ?, ?, ?, ?)";
$stmt_insere = $conexao->prepare($sql_insere);

if ($stmt_insere === false) {
    die("Erro na preparação da query: " . $conexao->error);
}

$stmt_insere->bind_param("sssss", $nome, $email, $telefone, $cpf, $senha_hash);

if ($stmt_insere->execute()) {
    echo "<h1>Fornecedor cadastrado com sucesso!</h1>";
    echo "<p>Nome: $nome</p>";
    echo "<p>E-mail: $email</p>";
    echo "<a href='cadastro_fornecedor.php'>Cadastrar novo fornecedor</a>";
} else {
    echo "Erro ao cadastrar: " . $stmt_insere->error;
}

$stmt_verifica->close();
$stmt_insere->close();
$conexao->close();
?>