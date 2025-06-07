<?php
require_once './includes/db.php';
require_once './includes/auth.php';

if (isFornecedor()) {
    header('Location: /fornecedor/dashboard');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    
    if (loginFornecedor($email, $senha, $pdo)) {
        header('Location: /fornecedor/dashboard');
        exit;
    } else {
        $erro = "Email ou senha inválidos";
    }
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
    <form method="POST">
        <h2>Área do Fornecedor</h2>
        <?php if ($erro): ?>
            <div class="erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>


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