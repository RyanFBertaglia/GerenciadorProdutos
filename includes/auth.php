<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('MIN_PASSWORD_LENGTH', 6);
define('TOKEN_EXPIRACAO', '+1 hour');

function isLoggedIn() {
    return isset($_SESSION['usuario']) && !empty($_SESSION['usuario']);
}

function protectPage() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        $_SESSION['erro'] = "Você precisa estar logado para acessar esta página";
        header('Location: /login');
        exit;
    }
}

//Login user normal
function login($email, $senha, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        unset($usuario['senha']);
        $_SESSION['usuario'] = $usuario;
        $_SESSION['erro'] = "";
        return true;
    }
    
    return false;
}

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

function gerarTokenRecuperacao($email, $pdo) {
    $token = bin2hex(random_bytes(32));
    $expiracao = date('Y-m-d H:i:s', strtotime(TOKEN_EXPIRACAO));
    
    $stmt = $pdo->prepare("UPDATE usuarios 
        SET token_recuperacao = ?, expiracao_token = ? 
        WHERE email = ?");
    
    $stmt->execute([$token, $expiracao, $email]);
    
    return $token;
}

function validarTokenRecuperacao($token, $pdo) {
    $stmt = $pdo->prepare("SELECT id FROM usuarios 
        WHERE token_recuperacao = ? 
        AND expiracao_token > NOW()");
    
    $stmt->execute([$token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

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

function getUserData($campo = null) {
    if (!isLoggedIn()) return null;
    
    if ($campo && isset($_SESSION['usuario'][$campo])) {
        return $_SESSION['usuario'][$campo];
    }
    
    return $_SESSION['usuario'];
}

function redirectIfLoggedIn($url = '/') {
    if (isLoggedIn()) {
        header("Location: $url");
        exit;
    }
}

function protectAdminPage() {
    if (!isAdmin()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        $_SESSION['erro'] = "Você precisa estar logado como administrador para acessar esta página";
        header('Location: /login-admin');
        exit;
    }
}


function isAdmin() {
    return isset($_SESSION['admin']) && !empty($_SESSION['admin']);
}

function isFornecedor() {
    return isLoggedIn() && ($_SESSION['usuario']['tipo'] ?? null) === 'fornecedor';
}

function protectFornecedorPage() {
    if (!isFornecedor()) { 
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'] ?? '/';
        $_SESSION['erro'] = "Acesso restrito a fornecedores";
        header('Location: /login-fornecedor');
        exit;
    }
}

//Login do fornecedor
function loginFornecedor($email, $senha, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM fornecedores WHERE email = ?");
    $stmt->execute([$email]);
    $fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($fornecedor && password_verify($senha, $fornecedor['senha'])) {
        unset($fornecedor['senha']);
        $fornecedor['tipo'] = 'fornecedor';
        
        $_SESSION['usuario'] = $fornecedor;
        $_SESSION['erro'] = "";
        return true;
    }
    return false;
}


