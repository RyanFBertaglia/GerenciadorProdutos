<?php
// Configurações do banco de dados
$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "mancha";


$conexao = new mysqli($host, $usuario, $senha, $banco);


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
$senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
$cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
$phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
$address = htmlspecialchars($_POST['address']);
$birthdate = $_POST['birthdate'];


if (!validarCPF($cpf)) {
    die("CPF inválido!");
}


$sql_verifica = "SELECT id FROM usuarios WHERE email = ?";
$stmt_verifica = $conexao->prepare($sql_verifica);
$stmt_verifica->bind_param("s", $email);
$stmt_verifica->execute();
$stmt_verifica->store_result();


if ($stmt_verifica->num_rows > 0) {
    die("Este e-mail já está cadastrado!");
}


$sql_insere = "INSERT INTO usuarios (nome, email, senha, cpf, telefone, endereco, data_nascimento)
               VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt_insere = $conexao->prepare($sql_insere);


if ($stmt_insere === false) {
    die("Erro na preparação da query: " . $conexao->error);
}


$stmt_insere->bind_param("sssssss", $nome, $email, $senha, $cpf, $phone, $address, $birthdate);


if ($stmt_insere->execute()) {
    echo "<h1>Cadastro realizado com sucesso!</h1>";
    echo "<a href='formulario.php'>Voltar</a>";
} else {
    echo "Erro ao cadastrar: " . $stmt_insere->error;
}


// Fechar conexões
$stmt_verifica->close();
$stmt_insere->close();
$conexao->close();
?>
