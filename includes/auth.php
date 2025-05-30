<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Constantes para configuração
define('MIN_PASSWORD_LENGTH', 6);
define('TOKEN_EXPIRACAO', '+1 hour'); // Tempo de expiração do token de recuperação

/**
 * Verifica se o usuário está logado
 */
function isLoggedIn() {
    return isset($_SESSION['usuario']) && !empty($_SESSION['usuario']);
}

/**
 * Protege uma página requerendo login
 */
function protectPage() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        $_SESSION['erro'] = "Você precisa estar logado para acessar esta página";
        header('Location: /user/login.php');
        exit;
    }
}

/**
 * Realiza o login do usuário
 */
function login($email, $senha, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // Atualiza dados na sessão (remove a senha)
        unset($usuario['senha']);
        $_SESSION['usuario'] = $usuario;
        
        return true;
    }
    
    return false;
}

/**
 * Realiza o logout do usuário
 */
function logout() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Gera token para recuperação de senha
 */
function gerarTokenRecuperacao($email, $pdo) {
    $token = bin2hex(random_bytes(32));
    $expiracao = date('Y-m-d H:i:s', strtotime(TOKEN_EXPIRACAO));
    
    $stmt = $pdo->prepare("UPDATE usuarios 
        SET token_recuperacao = ?, expiracao_token = ? 
        WHERE email = ?");
    
    $stmt->execute([$token, $expiracao, $email]);
    
    return $token;
}

/**
 * Valida token de recuperação de senha
 */
function validarTokenRecuperacao($token, $pdo) {
    $stmt = $pdo->prepare("SELECT id FROM usuarios 
        WHERE token_recuperacao = ? 
        AND expiracao_token > NOW()");
    
    $stmt->execute([$token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Atualiza senha do usuário
 */
function atualizarSenha($usuarioId, $novaSenha, $pdo) {
    if (strlen($novaSenha) < MIN_PASSWORD_LENGTH) {
        throw new Exception("A senha deve ter pelo menos " . MIN_PASSWORD_LENGTH . " caracteres");
    }
    
    $senhaHash = password_hash($novaSenha, PASSWORD_BCRYPT);
    
    $stmt = $pdo->prepare("UPDATE usuarios 
        SET senha = ?, token_recuperacao = NULL, expiracao_token = NULL 
        WHERE id = ?");
    
    return $stmt->execute([$senhaHash, $usuarioId]);
}

/**
 * Obtém dados do usuário logado
 */
function getUserData($campo = null) {
    if (!isLoggedIn()) return null;
    
    if ($campo && isset($_SESSION['usuario'][$campo])) {
        return $_SESSION['usuario'][$campo];
    }
    
    return $_SESSION['usuario'];
}

/**
 * Redireciona usuários já logados
 */
function redirectIfLoggedIn($url = '/') {
    if (isLoggedIn()) {
        header("Location: $url");
        exit;
    }
}