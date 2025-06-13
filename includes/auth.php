<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['logado']) && ($_SESSION['logado'] == true);
}

function protectPage() {
    if (!isLoggedIn()) {
        $_SESSION['erro'] = "Você precisa estar logado para acessar esta página";
        header('Location: /login');
        exit;
    }
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