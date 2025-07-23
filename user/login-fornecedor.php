<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';

use Api\Controller\AuthController;
use Api\Model\FornecedorModel;
use Api\Includes\Database;

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pdo = Database::getInstance();
    $fornecedorModel = new FornecedorModel($pdo);
    $authController = new AuthController($fornecedorModel);

    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $authController->login($email, $senha);
    $_SESSION['erro'] = "";
    header('Location: /fornecedor/dashboard');
    
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login Fornecedor</title>
    <style>

    </style>
    <link rel="stylesheet" href="../static/style/main.css">
    <link rel="stylesheet" href="../static/style/login.css">

</head>
<body>  

    <?php include './static/elements/sidebar-main.php'; ?>
    <div class="container-fluid">
    <form method="POST" action="/login-fornecedor">
        <h2>Área do Fornecedor</h2>

        <input type="email" name="email" placeholder="E-mail" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <div class="btn-container">
            <button type="submit" class="btn">Entrar</button>
            <a href="/cadastro-fornecedor" class="btn">Cadastrar-se como fornecedor</a>
        </div>
    </form>

    </div>



</body>
</html>