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

        // Prepara statements que serão reutilizados
        $stmtPedido = $pdo->prepare("INSERT INTO Orders (idUser, dataPedido, status, total, idFornecedor) VALUES (?, NOW(), 'Pendente', ?, ?)");
        $stmtItem = $pdo->prepare("INSERT INTO OrderItems (idOrder, idProduct, quantity, value) VALUES (?, ?, ?, ?)");
        $stmtUpdateVendidos = $pdo->prepare("UPDATE produtos SET vendidos = vendidos + ? WHERE idProduct = ?");
        $stmtPagamento = $pdo->prepare("INSERT INTO Payments (idUser, idOrder, status, datePayment, total) VALUES (?, ?, 'Pago', NOW(), ?)");

        $pedidosCriados = [];

        // Processa cada produto individualmente
        for ($i = 0; $i < count($itens); $i++) {
            $item = $itens[$i];
            $subtotal = $item['price'] * $item['quantidade'];
            $idFornecedor = $item['supplier'];

            // Cria um pedido individual para este produto/fornecedor
            $result = $stmtPedido->execute([$usuario_id, $subtotal, $idFornecedor]);
            if (!$result) {
                throw new Exception("Erro ao criar pedido para produto {$item['description']}: " . implode(", ", $stmtPedido->errorInfo()));
            }
            
            $orderId = $pdo->lastInsertId();
            $pedidosCriados[] = $orderId;

            // Insere o item no pedido
            $result = $stmtItem->execute([$orderId, $item['idProduct'], $item['quantidade'], $item['price']]);
            if (!$result) {
                throw new Exception("Erro ao inserir item do pedido {$orderId}: " . implode(", ", $stmtItem->errorInfo()));
            }

            // Verifica se há estoque suficiente
            $stmtEstoque = $pdo->prepare("SELECT stock FROM produtos WHERE idProduct = ?");
            $stmtEstoque->execute([$item['idProduct']]);
            $produtoEstoque = $stmtEstoque->fetch(PDO::FETCH_ASSOC);
            
            if (!$produtoEstoque) {
                throw new Exception("Produto {$item['description']} não encontrado.");
            }
            
            if ($produtoEstoque['stock'] < $item['quantidade']) {
                throw new Exception("Estoque insuficiente para o produto {$item['description']}. Disponível: {$produtoEstoque['stock']}, Solicitado: {$item['quantidade']}");
            }

            // Atualiza vendidos no produto
            $result = $stmtUpdateVendidos->execute([$item['quantidade'], $item['idProduct']]);
            if (!$result) {
                throw new Exception("Erro ao atualizar produtos vendidos para {$item['description']}: " . implode(", ", $stmtUpdateVendidos->errorInfo()));
            }

            // Reduz o estoque do produto
            $stmtReduceEstoque = $pdo->prepare("UPDATE produtos SET stock = stock - ? WHERE idProduct = ?");
            $result = $stmtReduceEstoque->execute([$item['quantidade'], $item['idProduct']]);
            if (!$result) {
                throw new Exception("Erro ao reduzir estoque do produto {$item['description']}: " . implode(", ", $stmtReduceEstoque->errorInfo()));
            }

            // Cria pagamento individual para este pedido
            $result = $stmtPagamento->execute([$usuario_id, $orderId, $subtotal]);
            if (!$result) {
                throw new Exception("Erro ao criar pagamento para pedido {$orderId}: " . implode(", ", $stmtPagamento->errorInfo()));
            }
        }

        // Atualiza saldo do usuário (debita o valor total de todos os produtos)
        $novoSaldo = $conta['balance'] - $total;
        $stmtUpdateSaldo = $pdo->prepare("UPDATE BankAccount SET balance = ? WHERE idAccount = ?");
        $result = $stmtUpdateSaldo->execute([$novoSaldo, $conta['idAccount']]);
        if (!$result) {
            throw new Exception("Erro ao atualizar saldo da conta: " . implode(", ", $stmtUpdateSaldo->errorInfo()));
        }

        // Limpa carrinho do usuário
        $stmtLimpar = $pdo->prepare("DELETE FROM carrinho WHERE usuario_id = ?");
        $result = $stmtLimpar->execute([$usuario_id]);
        if (!$result) {
            throw new Exception("Erro ao limpar carrinho: " . implode(", ", $stmtLimpar->errorInfo()));
        }

        $pdo->commit();

        $totalPedidos = count($pedidosCriados);
        $sucesso = "Compra realizada com sucesso!";
        $itens = [];
        $total = 0;

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
    <link rel="stylesheet" href="../static/style/admin/main.css">
    <link rel="stylesheet" href="../static/style/table.css">
    <link rel="icon" href="./static/img/logo-azul.png" type="image/x-icon">


    <style>
    body {
        margin: 0;
        padding: 0;
        font-family: arial;
        grid-template-areas: "aside main";
        display: grid;
    }
    aside {
        grid-area: aside;
    }
    main {
        grid-area: main;
        padding: 20px;
        font-family: Arial, sans-serif;
        max-width: 1200px;
    }

    h1 {
        color: #333;
        margin-bottom: 25px;
    }

    h2 {
        color: #444;
        margin: 20px 0 15px;
    }

    .alert-error {
        background-color: #ffebee;
        color: #d32f2f;
        padding: 12px 15px;
        border-radius: 4px;
        border-left: 4px solid #f44336;
        margin: 15px 0;
    }

    .alert-success {
        background-color: #e8f5e9;
        color: #388e3c;
        padding: 12px 15px;
        border-radius: 4px;
        border-left: 4px solid #4caf50;
        margin: 15px 0;
    }

    .fornecedor-info {
        font-weight: bold;
        color: #2a75bb;
    }

    /* Botão */
    button[type="submit"] {
        background-color: #4a90e2;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        transition: all 0.3s ease;
        font-size: 0.9em;
        margin: 15px 0;
    }

    button[type="submit"]:hover {
        background-color: #3a7bc8;
        transform: translateY(-1px);
    }

    p strong {
        font-size: 1.1em;
        color: #333;
    }

    p a {
        color: #4a90e2;
        text-decoration: none;
        transition: color 0.2s ease;
        display: inline-block;
        margin-top: 15px;
        font-weight: bold;
    }

    p a:hover {
        color: #3a7bc8;
        text-decoration: underline;
    }

    p em {
        color: #666;
        font-style: italic;
    }

    p:not([class]):not(strong) {
        color: #555;
    }

    @media (max-width: 768px) {
        main {
            margin-left: 0;
            padding: 15px;
        }

        table {
            display: block;
            overflow-x: auto;
        }
    }
</style>
</head>
<body>
    <?php include './static/elements/sidebar-main.php'; ?>
    
    <main>
    <br><br>
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
                    <th>ID Fornecedor</th>
                    <th>Quantidade</th>
                    <th>Preço Unitário</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['description']) ?></td>
                    <td class="fornecedor-info"><?= htmlspecialchars($item['supplier']) ?></td>
                    <td><?= (int)$item['quantidade'] ?></td>
                    <td>R$ <?= number_format($item['price'], 2, ',', '.') ?></td>
                    <td>R$ <?= number_format($item['price'] * $item['quantidade'], 2, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p><strong>Total Geral: </strong> R$ <?= number_format($total, 2, ',', '.') ?></p>

        <form action="" method="post">
            <button type="submit" name="comprar">Comprar Agora</button>
        </form>
    <?php else: ?>
        <p>Seu carrinho está vazio.</p>
    <?php endif; ?>

    <p><a href="/user/carrinho">Voltar ao carrinho</a></p>
    </main>
</body>
</html>