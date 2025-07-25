<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
use Api\Includes\Database;
$pdo = Database::getInstance();

$fornecedor_id = $_SESSION['fornecedor']['id'];
$erro = $sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entregar'])) {
    $pedidoId = (int)$_POST['pedido_id'];

    try {
        $pdo->beginTransaction();

        $stmtVerifica = $pdo->prepare("SELECT * FROM Orders WHERE id = ? AND idFornecedor = ? AND status <> 'Entregue' FOR UPDATE");
        $stmtVerifica->execute([$pedidoId, $fornecedor_id]);
        $pedido = $stmtVerifica->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) {
            throw new Exception("Pedido não encontrado ou já entregue.");
        }

        $stmtUpdate = $pdo->prepare("UPDATE Orders SET status = 'Entregue' WHERE id = ?");
        $stmtUpdate->execute([$pedidoId]);

        $pdo->commit();
        $sucesso = "Pedido marcado como entregue. Aguardando confirmação do cliente para liberação do pagamento.";

    } catch (Exception $e) {
        $pdo->rollBack();
        $erro = "Erro: " . $e->getMessage();
    }
}
// Busca pedidos do fornecedor
$stmt = $pdo->prepare("SELECT * FROM Orders WHERE idFornecedor = ? ORDER BY dataPedido DESC");
$stmt->execute([$fornecedor_id]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pedidos do Fornecedor</title>
    <link rel="stylesheet" href="../static/style/admin/main.css">
    <link rel="stylesheet" href="../static/style/pedidos.css">
    <style>
        body {
        margin: 0;
        padding: 0;
        font-family: arial;
        grid-template-areas: "aside main";
        display: grid;
    }
    </style>
</head>

<body>
    <?php include './static/elements/sidebar-fornecedor.php'; ?>
    <main>
    <h1>Pedidos Recebidos</h1>


<?php if ($erro): ?>
    <p style="color: red;"><?= htmlspecialchars($erro) ?></p>
<?php endif; ?>

<?php if ($sucesso): ?>
    <p style="color: green;"><?= htmlspecialchars($sucesso) ?></p>
<?php endif; ?>

<?php if (empty($pedidos)): ?>
    <p>Nenhum pedido encontrado.</p>
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

            <?php if ($pedido['status'] !== 'Entregue' && $pedido['status'] !== 'Devolvido' && $pedido['status'] !== 'Confirmado'): ?>
                <form method="post" style="margin-top:10px;">
                    <input type="hidden" name="pedido_id" value="<?= $pedido['id'] ?>">
                    <button type="submit" name="entregar">Marcar como Entregue</button>
                </form>
            <?php else: ?>
                <p style="color: green; font-weight: bold;">Pedido entregue</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<p><a href="/fornecedor/dashboard">Voltar ao painel do fornecedor</a></p>
    </main>
    
</body>
</html>
