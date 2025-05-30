<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: /user/login.php');
    exit;
}

$usuario_id = $_SESSION['usuario']['id'];

// Obter itens do carrinho
$stmt = $pdo->prepare("
    SELECT c.*, p.description, p.price, p.image, p.supplier 
    FROM carrinho c
    JOIN produtos p ON c.produto_id = p.idProduct
    WHERE c.usuario_id = ?
");
$stmt->execute([$usuario_id]);
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
?>

<h1>Meu Carrinho</h1>

<?php if (empty($itens)): ?>
    <p>Seu carrinho está vazio</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Fornecedor</th>
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
                    <img src="../uploads/<?= htmlspecialchars($item['image']) ?>" width="50">
                    <?= htmlspecialchars($item['description']) ?>
                </td>
                <td><?= htmlspecialchars($item['supplier']) ?></td>
                <td><?= $item['quantidade'] ?></td>
                <td>R$ <?= number_format($item['price'], 2, ',', '.') ?></td>
                <td>R$ <?= number_format($item['price'] * $item['quantidade'], 2, ',', '.') ?></td>
                <td>
                    <a href="remover.php?id=<?= $item['id'] ?>">Remover</a>
                </td>
            </tr>
            <?php 
                $total += $item['price'] * $item['quantidade'];
            endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">Total</td>
                <td>R$ <?= number_format($total, 2, ',', '.') ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    
    <a href="/finalizar/checkout.php">Finalizar Compra</a>
<?php endif; ?>