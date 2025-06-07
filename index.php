<?php
require_once 'config.php';
require_once 'includes/auth.php';

$basePath = parse_url(Config::getBaseUrl(), PHP_URL_PATH) ?: '';
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace($basePath, '', $requestUri);
$uri = '/' . trim($uri, '/');

if ($uri === '/') {
    $uri = '/';
}

switch ($uri) {
    case '/':
    case '/home':
        require './home.php';
        break;

    case '/login':
        if (isLoggedIn()) {
            redirect('home');
        }
        require './user/login.php';
        break; 

    case '/login-fornecedor':
        if (isFornecedor()) {
            redirect('fornecedor/dashboard');
        }
        require './fornecedor/login.php';
        break;

    // === ROTAS DO FORNECEDOR ===
    case '/fornecedor/dashboard':
        protectFornecedorPage();
        require './fornecedor/dashboard.php';
        break;

    case '/fornecedor/cadastrar-produto':
        protectFornecedorPage();
        require './fornecedor/add-product.php';
        break;
    
    case '/fornecedor/produtos':
        protectFornecedorPage();
        require './fornecedor/produtos.php';
        break;

    // === ROTAS GERAIS ===
    case '/produto':
        require './produtos/index.php';
        break;

    case '/produto/detalhes':
        $id = getParam('id');
        if (!$id) {
            redirect('produtos', ['error' => 'Produto não encontrado']);
        }
        require './produtos/detalhes.php';
        break;

    case '/user/carrinho':
        protectPage();
        require './carrinho/index.php';
        break;

    case '/adicionar':
        protectPage();
        require './carrinho/adicionar.php';
        break;

    case '/remover':
        protectPage();
        require './carrinho/remover.php';
        break;

    // === ROTAS DE AUTENTICAÇÃO ===
    case '/logout':
        logout();
        redirect('login');
        break;

    case '/cadastro-usuario':
        if (isLoggedIn()) {
            redirect('home');
        }
        require './user/registerUser.php';
        break;

    case '/cadastro-fornecedor':
        if (isLoggedIn()) {
            redirect('fornecedor/dashboard');
        }
        require './user/registerSupplier.php';
        break;    
        
    case '/login-admin':
        if (isLoggedIn()) {
            redirect('./admin.dashboard');
        }
        require './admin/login.php';
        break;
    
    // === ROTAS ADMIN ===
    case '/admin/dashboard':
        protectAdminPage();
        require './admin/dashboard.php';
        break;

    case '/admin/pendentes':
        protectAdminPage();
        require './admin/pendentes.php';
        break;

    case '/admin/fornecedores':
        protectAdminPage();
        require './admin/fornecedores.php';
        break;  

    case '/admin/usuarios':
        protectAdminPage();
        require './admin/usuarios.php';
        break;

    case '/admin/aprovar-produto':
        protectAdminPage();
        require './admin/aprovar-produto.php';
        break;
    
    case '/admin/rejeitar-produto':
        protectAdminPage();
        require './admin/rejeitar-produto.php';
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