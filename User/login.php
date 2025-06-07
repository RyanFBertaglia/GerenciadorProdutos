<?php
require_once './includes/db.php';
require_once './includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
redirectIfLoggedIn('/produtos/index.php');

$erro = $_SESSION['erro'] ?? '';
unset($_SESSION['erro']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (login($email, $senha, $pdo)) {
        $redirectUrl = $_SESSION['redirect_url'] ?? '/';
        unset($_SESSION['redirect_url']);
        header("Location: $redirectUrl");
        exit;
    } else {
        $erro = "Email ou senha inválidos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="./static/style/login.css">
    <link rel="stylesheet" href="./static/style/main.css">
    <link rel="stylesheet" href="./static/style/login.css">    
</head>
<body>

<?php include './static/elements/sidebar-main.php'; ?>

<div class="container-fluid">
        <form method="POST">
            <h2>Login</h2>
            <?php if ($erro): ?>
                <div class="erro"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>
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
