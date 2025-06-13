<?php
require_once 'config.php';
require_once 'includes/auth.php';

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($requestUri, '/');
if ($uri === '') {
    $uri = '/';
}


if ($uri === '/') {
    $uri = '/';
}

switch ($uri) {
    case '/':
    case '/home':
        require './view/home.php';
        break;

    case '/login':
        require __DIR__ . '/view/auth/login.php';
        break;

    case '/register':
        if (isLoggedIn()) {
            redirect('home');
        }
        require __DIR__ . '/view/auth/register.php';
        break; 

    case '/erro':
        require __DIR__ . '/view/static/elements/erro-cadastro.php';
        break;

    case '/reclamar':
        protectPage();
        require './view/posts/reclamar.php';
        break;

    case '/posts':
        protectPage();
        require './view/posts/index.php';
        break;

        
    // Página 404
    default:
        http_response_code(404);
        echo "<!DOCTYPE html>
        <html>
        <head><title>404 - Página não encontrada</title></head>
        <body>
            <h1>404 - Página não encontrada</h1>
            <p><a href='" . base_url() . "'>Voltar ao início</a></p>
        </body>
        </html>";
        break;
}
?>