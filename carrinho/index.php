<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once './includes/auth.php';

if (!isLoggedIn()) {
    header('Location: /login');
    exit;
}

use Api\Model\CarrinhoModel;
use Api\Controller\ComprasController;
use Api\Includes\Database;

$database = Database::getInstance();
$carrinhoModel = new CarrinhoModel($database);
$carrinhoController = new ComprasController($carrinhoModel);

$usuario_id = $_SESSION['usuario']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        switch ($_POST['acao']) {

            case 'atualizar':
                $id = $_POST['id'] ?? 0;
                $quantidade = $_POST['quantidade'] ?? 1;
                
                if ($carrinhoController->atualizarQuantidade($id, $quantidade)) {
                    $_SESSION['sucesso'] = "Quantidade atualizada com sucesso!";
                } else {
                    $_SESSION['erro'] = "Erro ao atualizar quantidade";
                }
                break;
                
            case 'remover':
                $id = $_POST['id'] ?? 0;
                
                if ($carrinhoController->removerItem($id)) {
                    $_SESSION['sucesso'] = "Item removido do carrinho!";
                } else {
                    $_SESSION['erro'] = "Erro ao remover item";
                }
                break;
        }
        
        header('Location: /user/carrinho');
        exit;
    }
}

if (isset($_GET['acao']) && $_GET['acao'] === 'remover' && isset($_GET['id'])) {
    if ($carrinhoController->removerItem($_GET['id'])) {
        $_SESSION['sucesso'] = "Item removido do carrinho!";
    } else {
        $_SESSION['erro'] = "Erro ao remover item";
    }
    header('Location: /user/carrinho');
    exit;
}

['itens' => $itens, 'total' => $total, 'quantidade' => $quantidade] = $carrinhoController->visualizarCarrinho();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Carrinho de Compras</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../static/style/main.css">
  <link rel="stylesheet" href="./static/style/tipografia.css">
  <style>
    body {
        height: 100vh;
        margin: 0;
        display: grid;
        grid-template-columns: 200px 1fr;
        grid-template-areas: "aside main";
        font-family: 'Montserrat', sans-serif;
        transition: all 0.3s ease;
    }
    .main-content {
        padding-left: 10px; /* Largura da sidebar */
        transition: padding-left 0.3s ease;
        min-height: 100vh;
    }
    body.collapsed .main-content {
        padding-left: 0;
    }
    main {
        font-family: 'Inter', sans-serif;
        width: 100%;
        height: 100vh;
        overflow-y: auto;
        grid-column: 2;
        padding: 0;
        margin: 0;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 2rem 0;
    }
    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    .product-cell {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .product-img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
    }
    .actions {
        display: flex;
        gap: 1rem;
    }
    .btn {
        padding: 8px 16px;
        border-radius: 4px;
        text-decoration: none;
        display: inline-block;
    }
    .btn-danger {
        background-color: #dc3545;
        color: white;
    }
    .btn-primary {
        background-color: #0d6efd;
        color: white;
    }
    .empty-cart {
        text-align: center;
        padding: 2rem;
        font-size: 1.2rem;
    }


    body.collapsed aside {
        transform: translateX(-100%);
    }

    .btn-start {
    top: 4%;
    right: 4%;
    padding: 15px 40px;
    background: linear-gradient(to right, #4a90e2, #1b68d1);
    border: none;
    border-radius: 30px;
    font-size: 18px;
    font-weight: 600;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    position: fixed;
    box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
}

.btn-start:hover {
    background: linear-gradient(to right, #1b68d1, #4a90e2);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(74, 144, 226, 0.4);
}
  </style>
</head>
<body>
<?php include './static/elements/sidebar-main.php'; ?>

<main class="container-fluid main-content">
    <h1>Meu Carrinho</h1>

    <?php if (isset($_SESSION['erro'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['erro']) ?></div>
        <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['sucesso'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['sucesso']) ?></div>
        <?php unset($_SESSION['sucesso']); ?>
    <?php endif; ?>

    <?php if (empty($itens)): ?>
        <div class="empty-cart">
            <p>Seu carrinho está vazio</p>
            <a href="/produto" class="btn btn-primary">Continuar Comprando</a>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Preço Unitário</th>
                    <th>Subtotal</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $item): ?>
                <tr>
                    <td>
                        <div class="product-cell">
                            <img src="../static/uploads/<?= htmlspecialchars($item['image']) ?>" 
                                 alt="<?= htmlspecialchars($item['description']) ?>" 
                                 class="product-img">
                            <div>
                                <strong><?= htmlspecialchars($item['description']) ?></strong><br>
                                <?php if ($item['quantidade'] > $item['stock']): ?>
                                    <small class="text-danger">(Estoque insuficiente)</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <form action="/user/carrinho" method="post" style="display:inline;">
                        <input type="hidden" name="acao" value="atualizar">    
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <input type="number" name="quantidade" 
                                   value="<?= $item['quantidade'] ?>" 
                                   min="1" max="<?= $item['stock'] ?>" 
                                   style="width: 60px;">
                            <button type="submit" class="btn btn-sm">Atualizar</button>
                        </form>
                    </td>
                    <td>R$ <?= number_format($item['price'], 2, ',', '.') ?></td>
                    <td>R$ <?= number_format($item['price'] * $item['quantidade'], 2, ',', '.') ?></td>

                    <td class="actions">
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="acao" value="remover">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Remover este item do carrinho?')">
                                <i class="fas fa-trash"></i> Remover
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end"><strong>Total</strong></td>
                    <td><strong>R$ <?= number_format($total, 2, ',', '.') ?></strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        
        <div class="text-end">
            <a href="/produto" class="btn">Continuar Comprando</a>
            <a href="/checkout" class="btn btn-primary">Finalizar Compra</a>
        </div>
    <?php endif; ?>
    <button class="btn-start" onclick="window.location.href='/user/pedidos'">Acompanhar Pedidos</button>
</main>

</body>
</html>