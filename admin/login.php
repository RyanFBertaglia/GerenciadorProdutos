<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php'; // arquivo que cria e retorna o objeto PDO (ex: $pdo)

$erro = '';

// Verifica se formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    // Busca o admin pelo email
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($senha, $admin['senha'])) {
        unset($admin['senha']); // Nunca armazene a senha na sessão
        $_SESSION['admin'] = $admin;
        header('Location: painel_admin.php');
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
    <style>
        body { font-family: Arial; background-color: #f5f5f5; display: flex; justify-content: center; align-items: center; height: 100vh; }
        form { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin: 10px 0; }
        .erro { color: red; margin-bottom: 10px; }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Login do Administrador</h2>
        <?php if ($erro): ?>
            <div class="erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <input type="email" name="email" placeholder="E-mail" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>
