<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use Api\Controller\AuthController;
use Api\Model\ClienteModel;
use Api\Includes\Database;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pdo = Database::getInstance();
    $usuarioModel = new ClienteModel($pdo);
    $authController = new AuthController($usuarioModel);

    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $authController->login($email, $senha);
    header('Location: /');
    $_SESSION['erro'] = "";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="./static/style/main.css">
    <link rel="stylesheet" href="./static/style/login.css">
</head>
<body>

<?php include './static/elements/sidebar-main.php'; ?>

    <div class="container-fluid">
        <form method="POST">
            <h2>Login</h2>

            <input type="email" name="email" placeholder="E-mail" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <div class="btn-container">
                <button type="submit" class="btn">Entrar</button>
                <a href="/cadastro-usuario" class="btn">Ainda não tem uma conta? Cadastrar-se</a>
            </div>
        </form>
    </div> 

</body>
</html>
