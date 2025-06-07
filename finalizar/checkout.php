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
$erro = $_SESSION['erro'] ?? null;
$sucesso = $_SESSION['sucesso'] ?? null;
unset($_SESSION['erro'], $_SESSION['sucesso']);

$total = 0;
$idFornecedor = null;
$itens = [];

try {
    // Busca itens do carrinho junto com dados do fornecedor
    $stmtCarrinho = $pdo->prepare("
        SELECT c.*, p.price, p.description, p.idProduct, p.supplier 
        FROM carrinho c 
        JOIN produtos p ON c.produto_id = p.idProduct 
        WHERE c.usuario_id = ?
    ");
    $stmtCarrinho->execute([$usuario_id]);
    $itens = $stmtCarrinho->fetchAll(PDO::FETCH_ASSOC);

    if (empty($itens)) {
        throw new Exception("Carrinho vazio.");
    }

    // Pega fornecedor do primeiro item (assumindo todos do mesmo fornecedor)
    $idFornecedor = $itens[0]['supplier'];

    // Calcula total
    $total = array_reduce($itens, fn($soma, $item) => $soma + ($item['price'] * $item['quantidade']), 0);

} catch (Exception $e) {
    $erro = $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comprar'])) {
    try {
        if (empty($itens)) {
            throw new Exception("Carrinho vazio.");
        }

        $pdo->beginTransaction();

        // Bloqueia conta do usuário para alteração
        $stmtSaldo = $pdo->prepare("SELECT * FROM BankAccount WHERE idUser = ? FOR UPDATE");
        $stmtSaldo->execute([$usuario_id]);
        $conta = $stmtSaldo->fetch(PDO::FETCH_ASSOC);

        if (!$conta || $conta['status'] !== 'A') {
            throw new Exception("Conta bancária não encontrada ou inativa.");
        }

        if ($conta['balance'] < $total) {
            throw new Exception("Saldo insuficiente.");
        }

        // Insere pedido (status inicial: 'Pendente')
        $stmtPedido = $pdo->prepare("INSERT INTO Orders (idUser, dataPedido, status, total, idFornecedor) VALUES (?, NOW(), 'Pendente', ?, ?)");
        $stmtPedido->execute([$usuario_id, $total, $idFornecedor]);
        $orderId = $pdo->lastInsertId();

        // Insere itens do pedido
        $stmtItem = $pdo->prepare("INSERT INTO OrderItems (idOrder, idProduct, quantity, value) VALUES (?, ?, ?, ?)");
        foreach ($itens as $item) {
            $stmtItem->execute([$orderId, $item['idProduct'], $item['quantidade'], $item['price']]);
        }

        // Atualiza vendidos no produto
        $stmtUpdateVendidos = $pdo->prepare("UPDATE produtos SET vendidos = vendidos + ? WHERE idProduct = ?");
        foreach ($itens as $item) {
            $stmtUpdateVendidos->execute([$item['quantidade'], $item['idProduct']]);
        }

        // Insere pagamento (status inicial: 'Pago' — você pode ajustar conforme regras)
        $stmtPagamento = $pdo->prepare("INSERT INTO Payments (idUser, idOrder, status, datePayment, total) VALUES (?, ?, 'Pago', NOW(), ?)");
        $stmtPagamento->execute([$usuario_id, $orderId, $total]);

        // Atualiza saldo do usuário (debita o valor total)
        $novoSaldo = $conta['balance'] - $total;
        $stmtUpdateSaldo = $pdo->prepare("UPDATE BankAccount SET balance = ? WHERE idAccount = ?");
        $stmtUpdateSaldo->execute([$novoSaldo, $conta['idAccount']]);

        // Limpa carrinho do usuário
        $stmtLimpar = $pdo->prepare("DELETE FROM carrinho WHERE usuario_id = ?");
        $stmtLimpar->execute([$usuario_id]);

        $pdo->commit();

        $sucesso = "Compra realizada com sucesso!";
        $itens = [];
        $total = 0;
        $idFornecedor = null;

    } catch (Exception $e) {
        $pdo->rollBack();
        $erro = "Erro no checkout: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Checkout</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 700px; margin: 20px auto; padding: 0 15px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .alert-error { color: red; }
        .alert-success { color: green; }
        button { padding: 10px 20px; font-size: 16px; }
    </style>
</head>
<body>
    <h1>Finalizar Compra</h1>

    <?php if ($erro): ?>
        <p class="alert-error"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <?php if ($sucesso): ?>
        <p class="alert-success"><?= htmlspecialchars($sucesso) ?></p>
    <?php endif; ?>

    <?php if (!empty($itens)): ?>
        <h2>Itens no Carrinho</h2>
        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Preço Unitário</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['description']) ?></td>
                    <td><?= (int)$item['quantidade'] ?></td>
                    <td>R$ <?= number_format($item['price'], 2, ',', '.') ?></td>
                    <td>R$ <?= number_format($item['price'] * $item['quantidade'], 2, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p><strong>Total: </strong> R$ <?= number_format($total, 2, ',', '.') ?></p>
        <p><strong>Fornecedor do pedido:</strong> <?= htmlspecialchars($idFornecedor) ?></p>

        <form action="" method="post">
            <button type="submit" name="comprar">Comprar Agora</button>
        </form>
    <?php else: ?>
        <p>Seu carrinho está vazio.</p>
    <?php endif; ?>

    <p><a href="/user/carrinho">Voltar ao carrinho</a></p>
</body>
</html>
