<?php
require_once 'config.php';
require_once 'includes/auth.php';
require __DIR__ . '/vendor/autoload.php';

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

    case '/erro':
        require './static/elements/erro-cadastro.php';
        break;

    // ROTAS DO FORNECEDOR
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

    case '/fornecedor/orders':
        protectFornecedorPage();
        require './fornecedor/orders.php';
        break;

    case '/fornecedor/pedidos':
        protectFornecedorPage();
        require './fornecedor/pedidos.php';
        break; 

    case '/fornecedor/devolucoes':
        protectFornecedorPage();
        require './fornecedor/devolucoes.php';
        break;

    case '/fornecedor/clientes':
        protectFornecedorPage();
        require './static/elements/pedidos.php';
        break;

    // ROTAS COMPRA
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

    case '/user/pedidos':
        protectPage();
        require './produtos/meus-pedidos.php';
        break;

    case '/checkout':
        protectPage();
        require './finalizar/checkout.php';
        break;

    case '/adicionar':
        protectPage();
        require './carrinho/adicionar.php';
        break;

    case '/remover':
        require './carrinho/remover.php';
        break;
    case '/carrinho/atualizar':
        require './carrinho/atualizar.php';
        break;


    // ROTAS DE AUTENTICAÇÃO

    case '/login':
        if (isLoggedIn()) {
            redirect('home');
        }
        require './user/login-cliente.php';
        break; 

    case '/login-fornecedor':
        if (isFornecedor()) {
            redirect('fornecedor/dashboard');
        }
        require './user/login-fornecedor.php';
        break;

    case '/login-admin':
        if (isAdmin()) {
            redirect('/admin/dashboard');
            exit;
        }
        require './user/login-admin.php';
        break;

    case '/logout':
        logout();
        redirect('home');
        break;

    case '/cadastro-usuario':
        if (isLoggedIn()) {
            redirect('home');
        }
        require './user/cadastrar-cliente.php';
        break;

    case '/cadastro-fornecedor':
        if (isLoggedIn()) {
            redirect('fornecedor/dashboard');
        }
        require './user/cadastrar-fornecedor.php';
        break;    
    
    // ROTAS ADMIN

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

    case '/admin/fornecedores':
        protectAdminPage();
        require './admin/fornecedores.php';
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

function logout() {
    if(session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_unset();
    session_destroy();
}

?>