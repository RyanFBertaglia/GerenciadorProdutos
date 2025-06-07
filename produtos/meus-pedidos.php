<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once './includes/db.php';
require_once './includes/auth.php';

if (!isLoggedIn()) {
    header('Location: /login');
    exit;
}

$usuario_id = $_SESSION['usuario']['id'];

// Busca pedidos do usuário
$stmt = $pdo->prepare("SELECT * FROM Orders WHERE idUser = ? ORDER BY dataPedido DESC");
$stmt->execute([$usuario_id]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Meus Pedidos</title>
</head>
<body>
    <h1>Meus Pedidos</h1>

    <?php if (empty($pedidos)): ?>
        <p>Você ainda não fez nenhum pedido.</p>
    <?php else: ?>
        <?php foreach ($pedidos as $pedido): ?>
            <div style="border:1px solid #ccc; margin-bottom:10px; padding:10px;">
                <p><strong>ID do Pedido:</strong> <?= htmlspecialchars($pedido['id']) ?></p>
                <p><strong>Data:</strong> <?= htmlspecialchars($pedido['dataPedido']) ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($pedido['status']) ?></p>
                <p><strong>Total:</strong> R$ <?= number_format($pedido['total'], 2, ',', '.') ?></p>
                <p><strong>Itens:</strong></p>
                <ul>
                    <?php
                    $stmtItens = $pdo->prepare("SELECT oi.quantity, oi.value, p.description FROM OrderItems oi JOIN produtos p ON oi.idProduct = p.idProduct WHERE oi.idOrder = ?");
                    $stmtItens->execute([$pedido['id']]);
                    $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($itens as $item):
                    ?>
                        <li><?= htmlspecialchars($item['description']) ?> - Quantidade: <?= $item['quantity'] ?> - Valor unitário: R$ <?= number_format($item['value'], 2, ',', '.') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <p><a href="/">Voltar ao painel</a></p>
</body>
</html>
