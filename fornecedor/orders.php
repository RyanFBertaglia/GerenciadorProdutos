<?php
require_once './includes/db.php';
require_once './includes/auth.php';
protectFornecedorPage();

// Simulação de login do fornecedor
$fornecedorId = $_SESSION['usuario']['id'];

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
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 25px 0;
        font-size: 0.9em;
        font-family: arial;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        border-radius: 8px;
        overflow: hidden;
    }

    /* Cabeçalho da tabela */
    table thead tr {
        background-color: #4a90e2;
        color: #ffffff;
        text-align: left;
        font-weight: bold;
    }

    table th,
    table td {
        padding: 12px 15px;
    }

    table tbody tr {
        border: none;
    }


    table tbody tr:nth-of-type(even) {
        background-color: #f9f9f9;
    }


    table button {
        padding: 8px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    button[name="acao"][value="enviar"] {
        background-color: #3498db;
        color: white;
    }

    button[name="acao"][value="enviar"]:hover {
        background-color: #2980b9;
    }

    /* Botão "Marcar como Entregue" */
    button[name="acao"][value="entregar"] {
        background-color: #2ecc71;
        color: white;
    }

    button[name="acao"][value="entregar"]:hover {
        background-color: #27ae60;
    }

    table em {
        color: #7f8c8d;
        font-style: italic;
    }

    td:nth-child(4) {
        font-weight: 600;
        text-transform: capitalize;
    }

    /* Status específicos com cores */
    td:nth-child(4):contains("Pendente") {
        color: #e67e22;
    }

    td:nth-child(4):contains("Enviado") {
        color: #3498db;
    }

    td:nth-child(4):contains("Entregue") {
        color: #2ecc71;
    }

    @media screen and (max-width: 768px) {
        table {
            display: block;
            overflow-x: auto;
        }
        
        table th, table td {
            padding: 8px 10px;
        }
        
        table button {
            padding: 6px 8px;
            font-size: 0.8em;
        }
    }
</style>
</head>
<body>
    <?php include './static/elements/sidebar-fornecedor.php'; ?>
    <main>
    <br><br><br>
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
    </main>
</body>
</html>
