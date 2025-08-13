<?php
require_once './includes/db.php';
require_once './includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use Api\Controller\AdminController;
use Api\Model\ProdutosModel;
use Api\Includes\Database;

$database = Database::getInstance();
$produtosModel = new ProdutosModel($database);
$adminController = new AdminController($produtosModel);

protectAdminPage();

$produtos = $adminController->listarPedidos();
$produtosPendentes = count($produtos);
$_SESSION['produtosPendentes'] = $produtosPendentes;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['acao'] == 'aprovar') {
        $id = (int)$_POST['id'];
        $adminController->aprovarPedido($id);
    }
    if ($_POST['acao'] == 'rejeitar') {
        $id = (int)$_POST['id'];
        $motivo = $_POST['motivo'];
        $adminController->rejeitarPedido($id, $motivo);
    }
    header("admin/dashboard");
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Produtos Pendentes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../static/style/admin/main.css">
    <link rel="icon" href="./static/img/logo-azul.png" type="image/x-icon">

    <style>
        body {
            border: 0;
            margin: 0;
            padding: 0;
            grid-template-columns: 200px 1fr;
            grid-template-areas: "aside main";
            font-family: arial;
        }
        main {
            grid-area: main;
        }
        .admin-sidebar {
            grid-area: aside;
        }

        main {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

h1 {
    color: #2c3e50;
    text-align: center;
    margin-bottom: 30px;
    font-size: 2.2em;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
}

.produtos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.produto-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.produto-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.produto-card h3 {
    color: #2980b9;
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 1.4em;
}

.produto-card p {
    margin: 8px 0;
    color: #555;
    font-size: 1em;
}

.produto-card img {
    display: block;
    max-width: 100%;
    height: auto;
    margin: 15px auto;
    border-radius: 4px;
    border: 1px solid #ddd;
}

.acoes-admin {
    margin-top: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.3s ease;
}

.btn-success {
    background-color: #2ecc71;
    color: white;
}

.btn-success:hover {
    background-color: #27ae60;
}

.btn-danger {
    background-color: #e74c3c;
    color: white;
}

.btn-danger:hover {
    background-color: #c0392b;
}

.btn-secondary {
    background-color: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background-color: #7f8c8d;
}

.form-group {
    margin-bottom: 15px;
    width: 100%;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: #2c3e50;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: inherit;
    resize: vertical;
    min-height: 80px;
}

p {
    text-align: center;
    color: #7f8c8d;
    font-size: 1.1em;
    margin-top: 20px;
}

@media (min-width: 769px) {
    main {
        padding-left: 210px;
    }
}
    </style>
</head>
<body>
    <?php include './static/elements/sidebar-admin.php'; ?>
    <main>
        <h1>Produtos Pendentes de Aprovação</h1>
        
        <?php if (empty($produtos)): ?>
            <p>Não há produtos pendentes de aprovação.</p>
        <?php else: ?>
            <div class="produtos-grid">
                <?php foreach ($produtos as $produto): ?>
                <div class="produto-card">
                    <h3><?= htmlspecialchars($produto['description']) ?></h3>
                    <p>Fornecedor: <?= htmlspecialchars($produto['fornecedor_nome']) ?></p>
                    <p>Preço: R$ <?= number_format($produto['price'], 2, ',', '.') ?></p>
                    <p>Estoque: <?= $produto['stock'] ?></p>
                    
                    <?php if ($produto['image']): ?>
                        <img src="../static/uploads/<?= htmlspecialchars($produto['image']) ?>" alt="<?= htmlspecialchars($produto['description']) ?>" width="200">
                    <?php endif; ?>
                    
                    <div class="acoes-admin">
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="id" value="<?= $produto['idProduct'] ?>">
                            <input type="hidden" name="acao" value="<?= 'aprovar' ?>">
                            <button type="submit" class="btn btn-success">Aprovar</button>
                        </form>
                        
                        <button type="button" class="btn btn-danger" onclick="document.getElementById('rejeitar-<?= $produto['idProduct'] ?>').style.display='block'">
                            Rejeitar
                        </button>
                        
                        <div id="rejeitar-<?= $produto['idProduct'] ?>" style="display: none; margin-top: 10px;">
                            <form method="post">
                                <input type="hidden" name="id" value="<?= $produto['idProduct'] ?>">
                                <input type="hidden" name="acao" value="<?='rejeitar' ?>">
                                <div class="form-group">
                                    <label>Motivo da Rejeição:</label>
                                    <textarea name="motivo" class="form-control" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger">Confirmar Rejeição</button>
                                <button type="button" class="btn btn-secondary" onclick="document.getElementById('rejeitar-<?= $produto['idProduct'] ?>').style.display='none'">
                                    Cancelar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>