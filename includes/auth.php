<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function protectPage() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        $_SESSION['erro'] = "Você precisa estar logado para acessar esta página";
        header('Location: /login');
        exit;
    }
}

function redirectIfLoggedIn($url = '/') {
    if (isLoggedIn()) {
        header("Location: $url");
        exit;
    }
}

function protectFornecedorPage() {
    if (!isFornecedor()) { 
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'] ?? '/';
        $_SESSION['erro'] = "Acesso restrito a fornecedores";
        header('Location: /login-fornecedor');
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

function isLoggedIn() {
    return isset($_SESSION['usuario']) && !empty($_SESSION['usuario']);
}
function isAdmin() {
    return isset($_SESSION['admin']) && !empty($_SESSION['admin']);
}
function isFornecedor() {
    return isset($_SESSION['fornecedor']) && !empty($_SESSION['fornecedor']);
}


