<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



use Api\Controller\AuthController;
use Api\Model\FornecedorModel;
use Api\Includes\Database;
use Api\Services\ValidarDados;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = Database::getInstance();
    ValidarDados::verificaExistenciaFornecedor($_POST['cpf'], $_POST['email'], $pdo);

    $fornecedorModel = new FornecedorModel($pdo);
    $authController = new AuthController($fornecedorModel);
    $authController->cadastro([
        'nome' => $_POST['nome'] ?? '',
        'email' => $_POST['email'] ?? '',
        'senha' => $_POST['senha'] ?? '',
        'cpf' => $_POST['cpf'] ?? '',
        'telefone' => $_POST['telefone'] ?? ''
    ]);
    
    $_SESSION['erro'] = "";
    header('Location: /login-fornecedor');
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Fornecedor</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="./static/style/main.css">
    <link rel="stylesheet" href="./static/style/register.css">
    <link rel="icon" href="./static/img/logo-azul.png" type="image/x-icon">

    <style>
        body{
            min-height: 105vh;
        }
        
    </style>
</head>
<body>
    <?php include './static/elements/sidebar-main.php'; ?>

    <div class="container-fluid">
        <form action="/cadastro-fornecedor" method="POST" onsubmit="return validarFormulario()">
            <img src="../static/img/predio.png" alt="Prédio" class="building-img" style="display: block; margin: 0 auto 0px;">
            <h2 style="text-align: center;">Cadastro de Fornecedor</h2>

            <div class="form-group">
                <label for="nome">Nome Completo:</label>
                <input type="text" name="nome" id="nome" required>
            </div>

            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" name="email" id="email" required>
            </div>

            <div class="form-group">
                <label for="telefone">Telefone:</label>
                <input type="text" name="telefone" id="telefone" required placeholder="(00) 00000-0000">
            </div>

            <div class="form-group">
                <label for="cpf">CPF:</label>
                <input type="text" name="cpf" id="cpf" required placeholder="000.000.000-00">
                <div id="cpf-error" class="erro"></div>
            </div>

            <div class="form-group">
                <label for="senha">Senha (mínimo 6 caracteres):</label>
                <input type="password" name="senha" id="senha" required minlength="6">
            </div>

            <div class="form-group">
                <label for="confirmar_senha">Confirmar Senha:</label>
                <input type="password" name="confirmar_senha" id="confirmar_senha" required>
                <div id="senha-error" class="erro"></div>
            </div>

            <div class="btn-container">
                <button type="submit" class="btn">Cadastrar</button>
                <a href="/login-fornecedor" class="btn">Já possui conta? Login</a>
            </div>
        </form>
    </div>

    <script src="../static/js/validation.js"></script>
</body>
</html>
