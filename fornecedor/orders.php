<?php
require_once './includes/db.php';
require_once './includes/auth.php';
protectFornecedorPage();

// Simulação de login do fornecedor
$fornecedorId = 2; // $_SESSION['usuario']['id']

// Processar ações do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $orderId = $_POST['order_id'] ?? 0;

    if ($acao === 'enviar') {
        $stmt = $pdo->prepare("UPDATE Orders SET status = 'Enviado' WHERE id = ? AND idFornecedor = ?");
        $stmt->execute([$orderId, $fornecedorId]);
    } elseif ($acao === 'entregar') {
        $stmt = $pdo->prepare("UPDATE Orders SET status = 'Entregue' WHERE id = ? AND idFornecedor = ?");
        $stmt->execute([$orderId, $fornecedorId]);
    }
}

// Buscar pedidos do fornecedor
$stmt = $pdo->prepare("SELECT * FROM Orders WHERE idFornecedor = ?");
$stmt->execute([$fornecedorId]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Painel do Fornecedor</title>
</head>
<body>
    <h1>Pedidos Recebidos</h1>

    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuário</th>
                <th>Data</th>
                <th>Status</th>
                <th>Total (R$)</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $pedido): ?>
                <tr>
                    <td><?= $pedido['id'] ?></td>
                    <td><?= $pedido['idUser'] ?></td>
                    <td><?= $pedido['dataPedido'] ?></td>
                    <td><?= $pedido['status'] ?></td>
                    <td><?= number_format($pedido['total'], 2, ',', '.') ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?= $pedido['id'] ?>">
                            <?php if ($pedido['status'] === 'Pendente'): ?>
                                <button name="acao" value="enviar">Marcar como Enviado</button>
                            <?php elseif ($pedido['status'] === 'Enviado'): ?>
                                <button name="acao" value="entregar">Marcar como Entregue</button>
                            <?php else: ?>
                                <em>Sem ação</em>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
