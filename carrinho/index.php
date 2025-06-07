<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Redirect to login if not authenticated
if (!isLoggedIn()) {
    header('Location: /login'); // Using router URL
    exit;
}

$usuario_id = $_SESSION['usuario']['id'];

try {
    // Get cart items with product details
    $stmt = $pdo->prepare("
        SELECT c.id, c.quantidade, p.idProduct, p.description, p.price, p.image, p.stock 
        FROM carrinho c
        JOIN produtos p ON c.produto_id = p.idProduct
        WHERE c.usuario_id = ?
    ");
    $stmt->execute([$usuario_id]);
    $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total
    $total = array_reduce($itens, function($sum, $item) {
        return $sum + ($item['price'] * $item['quantidade']);
    }, 0);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['erro'] = "Erro ao carregar carrinho";
    $itens = [];
    $total = 0;
}
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
  <link rel="stylesheet" href="../static/style/tipografia.css">
  <style>
    main {
        grid-area: main;
        font-family: 'Inter', sans-serif;
        padding: 2rem;
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
  </style>
</head>
<body>
<?php include '../static/elements/sidebar-main.php'; ?>

<main>
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
                            <img src="../uploads/<?= htmlspecialchars($item['image']) ?>" 
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
                        <form action="/carrinho/atualizar" method="post" style="display:inline;">
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
                        <a href="/carrinho/remover?id=<?= $item['id'] ?>" 
                           class="btn btn-danger"
                           onclick="return confirm('Remover este item do carrinho?')">
                            <i class="fas fa-trash"></i> Remover
                        </a>
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
</main>

<script>
    // Simple confirmation for removal
    document.querySelectorAll('.btn-danger').forEach(btn => {
        btn.addEventListener('click', (e) => {
            if (!confirm('Tem certeza que deseja remover este item?')) {
                e.preventDefault();
            }
        });
    });
</script>
</body>
</html>