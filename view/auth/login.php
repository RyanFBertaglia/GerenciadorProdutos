<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = require __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../model/Users.php';
require_once __DIR__ . '/../../controller/UserController.php';

use backend\Models\Users;
use backend\Controller\UserController;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    $users = new Users($pdo);
    $repo = new UserController($users);

    $repo->login($email, $senha);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Login</title>
</head>
<body>
    <?php if (!empty($erro)): ?>
        <p style="color:red"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form action="/login" method="POST">
        <input type="email" name="email" required placeholder="E-mail" />
        <input type="password" name="senha" required placeholder="Senha" />
        <button type="submit">Entrar</button>
    </form>
</body>
</html>
