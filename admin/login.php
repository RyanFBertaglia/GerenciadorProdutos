<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($senha, $admin['senha'])) {
        unset($admin['senha']);
        $_SESSION['admin'] = $admin;
        header('Location: dashboard.php');
        exit;
    } else {
        $erro = "Email ou senha incorretos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login do Administrador</title>
    <link rel="stylesheet" href="../static/style/main.css">
    <link rel="stylesheet" href="../static/style/login.css">
</head>
<body>
    <?php include '../static/elements/sidebar-main.php'; ?>
    <div class="container-fluid">
        <form method="POST">
            <h2>Login do Administrador</h2>
            <?php if ($erro): ?>
                <div class="erro"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>
            <input type="email" name="email" placeholder="E-mail" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <div class="btn-container">
            <button type="submit" class="btn">Entrar</button>
            </div>
        </form>
    </div> 
</body>
</html>
