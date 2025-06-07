<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once './includes/db.php';
require_once './includes/auth.php';

if (isAdmin()) {
    header('Location: /admin/dashboard');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        try {
            // Prepare statement with email verification
            $stmt = $pdo->prepare("
                SELECT id, nome, email, senha 
                FROM admin 
                WHERE email = ? 
                LIMIT 1
            ");
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($senha, $admin['senha'])) {
                // Regenerate session ID to prevent fixation
                session_regenerate_id(true);
                
                // Store only necessary admin data in session
                $_SESSION['admin'] = [
                    'id' => $admin['id'],
                    'nome' => $admin['nome'],
                    'email' => $admin['email'],
                    'last_login' => time()
                ];
                
                // Set secure session cookie
                setcookie(
                    session_name(),
                    session_id(),
                    [
                        'expires' => time() + 86400,
                        'path' => '/',
                        'domain' => $_SERVER['HTTP_HOST'],
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]
                );

                header('Location: /admin/dashboard');
                exit;
            } else {
                $erro = "Credenciais inválidas. Por favor, tente novamente.";
                error_log("Failed admin login attempt for email: $email");
            }
        } catch (PDOException $e) {
            error_log("Database error during admin login: " . $e->getMessage());
            $erro = "Erro no sistema. Por favor, tente mais tarde.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login do Administrador</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./static/style/main.css">
    <link rel="stylesheet" href="./static/style/login.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .erro {
            color: #dc3545;
            padding: 0.75rem;
            margin-bottom: 1rem;
            background: #f8d7da;
            border-radius: 4px;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 1rem;
        }
        .btn {
            width: 100%;
            padding: 0.75rem;
            background: #0d6efd;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn:hover {
            background: #0b5ed7;
        }
    </style>
</head>
<body>
    
    <?php include './static/elements/sidebar-main.php'; ?>
    <div class="container-fluid">
            
            
            

            <form method="POST" autocomplete="off">
            <?php if ($erro): ?>
                <div class="erro">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
            <h2><i class="fas fa-lock"></i> Acesso Administrativo</h2>
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> E-mail</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           required autofocus value="<?= isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : '' ?>">
                </div>

                <div class="form-group">
                    <label for="senha"><i class="fas fa-key"></i> Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" required>
                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
            </form>
    </div>

</body>
</html>