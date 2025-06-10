<?php
require_once './includes/db.php';
require_once './includes/auth.php';
protectFornecedorPage();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Painel do Fornecedor</title>
    <link rel="stylesheet" href="../static/style/admin/main.css">
    <style>
    body {
        margin: 0;
        padding: 0;
        font-family: arial;
        grid-template-areas: "aside main";
        display: grid;
    }
    main {
        padding: 4%;
    }
    .container {
        margin: 0 auto;
        padding: 20px;
    }
    
    .button-group {
        display: flex;
        gap: 50px;
        margin-top: 30px;
        justify-content: center;
    }
    
    .action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 15px 30px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        width: 400px;
        text-align: center;
    }
    
    .action-button i {
        margin-right: 10px;
        font-size: 1.2rem;
    }
    
    .pedidos {
        background-color: #3498db;
        color: white;
        border: 2px solid #2980b9;
    }
    
    .devolucoes {
        background-color: #e74c3c;
        color: white;
        border: 2px solid #c0392b;
    }
    
    .action-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    
    .pedidos:hover {
        background-color: #2980b9;
    }
    
    .devolucoes:hover {
        background-color: #c0392b;
    }
    
</style>
</head>
<body>
    <?php include './static/elements/sidebar-fornecedor.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <main>
    <div class="container">
        <h1>Acompanhar Pedidos</h1>
        <div class="button-group">
            <a href="/fornecedor/pedidos" class="action-button pedidos">
                <i class="bi bi-cart-check"></i> Pedidos
            </a>
            <a href="/fornecedor/devolucoes" class="action-button devolucoes">
                <i class="bi bi-arrow-return-left"></i> Devoluções
            </a>
        </div>
    </div>
</main>


</body>
</html>
