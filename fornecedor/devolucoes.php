<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once './includes/db.php';
require_once './includes/auth.php';

protectFornecedorPage();

$fornecedor_id = $_SESSION['fornecedor']['id'];
$sucesso = $_SESSION['sucesso'] ?? null;
$erro = $_SESSION['erro'] ?? null;
unset($_SESSION['sucesso'], $_SESSION['erro']);

// Processa aprovação/rejeição de devolução
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['aprovar_devolucao']) || isset($_POST['rejeitar_devolucao'])) {
        $pedido_id = (int)$_POST['pedido_id'];
        $acao = isset($_POST['aprovar_devolucao']) ? 'aprovar' : 'rejeitar';
        $motivoRecusa = trim($_POST['motivoRecusa'] ?? '');

        try {
            $pdo->beginTransaction();
        
            // Verifica se o pedido pertence ao fornecedor e está com status pendente
            $stmtVerifica = $pdo->prepare("SELECT o.*, u.id as id_cliente FROM Orders o 
                                         JOIN OrderItems oi ON o.id = oi.idOrder
                                         JOIN produtos p ON oi.idProduct = p.idProduct
                                         JOIN usuarios u ON o.idUser = u.id
                                         WHERE o.id = ? AND p.supplier = ? AND o.status = 'Devolucao_Pendente' FOR UPDATE");
            $stmtVerifica->execute([$pedido_id, $fornecedor_id]);
            $pedido = $stmtVerifica->fetch(PDO::FETCH_ASSOC);
        
            if (!$pedido) {
                throw new Exception("Pedido não encontrado, não pertence a você ou não está pendente de devolução.");
            }
        
            // Define o novo status padronizado
            $novo_status = ($acao === 'aprovar') ? 'Devolvido' : 'Devolucao_Rejeitada';
            
            // Atualiza status do pedido
            if ($acao === 'aprovar') {
                $stmtAtualiza = $pdo->prepare("UPDATE Orders SET 
                                              status = ?, 
                                              dataAprovacaoDevolucao = NOW(),
                                              motivoRecusa = NULL
                                              WHERE id = ?");
                $stmtAtualiza->execute([$novo_status, $pedido_id]);
            } else {
                $stmtAtualiza = $pdo->prepare("UPDATE Orders SET 
                                              status = ?, 
                                              dataRejeicaoDevolucao = NOW(),
                                              motivoRecusa = ?
                                              WHERE id = ?");
                $stmtAtualiza->execute([$novo_status, $motivoRecusa, $pedido_id]);
            }
        
            // Se aprovado, processa o reembolso
            if ($acao === 'aprovar') {
                // Busca conta do fornecedor
                $stmtContaFornecedor = $pdo->prepare("SELECT * FROM BankAccount 
                                                    WHERE idFornecedor = ? AND tipo = 'fornecedor' AND status = 'A'
                                                    LIMIT 1 FOR UPDATE");
                $stmtContaFornecedor->execute([$fornecedor_id]);
                $contaFornecedor = $stmtContaFornecedor->fetch(PDO::FETCH_ASSOC);
        
                if (!$contaFornecedor) {
                    throw new Exception("Sua conta bancária não foi encontrada ou está inativa.");
                }
        
                // Busca conta do cliente
                $stmtContaCliente = $pdo->prepare("SELECT * FROM BankAccount 
                                                 WHERE idUser = ? AND tipo = 'usuario' AND status = 'A'
                                                 LIMIT 1 FOR UPDATE");
                $stmtContaCliente->execute([$pedido['id_cliente']]);
                $contaCliente = $stmtContaCliente->fetch(PDO::FETCH_ASSOC);
        
                if (!$contaCliente) {
                    throw new Exception("Conta bancária do cliente não encontrada ou inativa.");
                }
        
                // Verifica saldo do fornecedor
                if ($contaFornecedor['balance'] < $pedido['total']) {
                    throw new Exception("Saldo insuficiente em sua conta para realizar o reembolso.");
                }
        
                // Realiza a transferência (débito fornecedor, crédito cliente)
                $stmtDebito = $pdo->prepare("UPDATE BankAccount 
                                            SET balance = balance - ? 
                                            WHERE idAccount = ?");
                $stmtDebito->execute([$pedido['total'], $contaFornecedor['idAccount']]);
        
                $stmtCredito = $pdo->prepare("UPDATE BankAccount 
                                            SET balance = balance + ? 
                                            WHERE idAccount = ?");
                $stmtCredito->execute([$pedido['total'], $contaCliente['idAccount']]);
            }
        
            $pdo->commit();
            
            $msg = ($acao === 'aprovar') 
                   ? "Devolução aprovada! R$ " . number_format($pedido['total'], 2, ',', '.') . " reembolsados para o cliente." 
                   : "Devolução rejeitada. O motivo foi informado ao cliente.";
            $_SESSION['sucesso'] = $msg;
        
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['erro'] = "Erro: " . $e->getMessage();
        }
        
        header('Location: /fornecedor/dashboard');
        exit;
    }
}

// Consulta devoluções pendentes
$stmt = $pdo->prepare("
    SELECT o.*, u.nome AS nome_cliente 
    FROM Orders o
    JOIN OrderItems oi ON oi.idOrder = o.id
    JOIN produtos p ON oi.idProduct = p.idProduct
    JOIN usuarios u ON o.idUser = u.id
    WHERE p.supplier = ? AND o.status = 'Devolucao_Pendente'
    GROUP BY o.id
    ORDER BY o.dataDevolucao ASC
");
$stmt->execute([$fornecedor_id]);
$pedidos_pendentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta histórico de devoluções
$stmt = $pdo->prepare("
    SELECT o.*, u.nome AS nome_cliente
    FROM Orders o
    JOIN OrderItems oi ON oi.idOrder = o.id
    JOIN produtos p ON oi.idProduct = p.idProduct
    JOIN usuarios u ON o.idUser = u.id
    WHERE p.supplier = ?
      AND o.status IN ('Devolvido', 'Devolucao_Rejeitada') 
      AND o.dataDevolucao IS NOT NULL
    GROUP BY o.id
    ORDER BY COALESCE(o.dataAprovacaoDevolucao, o.dataRejeicaoDevolucao) DESC
");
$stmt->execute([$fornecedor_id]);
$historico_devolucoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Devoluções</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: grid;
            grid-template-columns: 200px 1fr;
            grid-template-rows: auto 1fr;
            grid-template-areas: "sidebar main";
            min-height: 100vh;
        }
        aside {
            grid-area: aside;
        }
        
        
        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .section {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background-color: #f9f9f9;
        }
        
        .pedido {
            border: 1px solid #ccc;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            background-color: white;
        }
        
        .pedido-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .status.pendente { background-color: #fff3cd; color: #856404; }
        .status.devolvido { background-color: #d4edda; color: #155724; }
        .status.confirmado { background-color: #f8d7da; color: #721c24; }
        
        .itens-lista {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        .btn-aprovar {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-rejeitar {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 10px;
        }
        
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: vertical;
            margin: 10px 0;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        h1, h2 {
            color: #333;
        }
        
        h2 {
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-top: 0;
        }
        
        .info-cliente {
            background-color: #e7f5fe;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        
        .motivo-box {
            background-color: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <link rel="stylesheet" href="../static/style/admin/main.css">
</head>
<body>
    <?php include './static/elements/sidebar-fornecedor.php'; ?>


    <main>
        <br><br>
        <h1>Gerenciar Solicitações de Devolução</h1>
        
        <?php if ($sucesso): ?>
            <div class="alert-success"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="alert-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <div class="container">
            <div class="section">
                <h2>Devoluções Pendentes</h2>
                
                <?php if (empty($pedidos_pendentes)): ?>
                    <p>Não há devoluções pendentes no momento.</p>
                <?php else: ?>
                    <?php foreach ($pedidos_pendentes as $pedido): ?>
                        <div class="pedido">
                            <div class="info-cliente">
                                <p><strong>Cliente:</strong> <?= htmlspecialchars($pedido['nome_cliente']) ?></p>
                                <p><strong>Pedido #<?= htmlspecialchars($pedido['id']) ?></strong></p>
                                <p><strong>Data da solicitação:</strong> <?= date('d/m/Y H:i', strtotime($pedido['dataDevolucao'])) ?></p>
                                <span class="status pendente">Pendente</span>
                            </div>
                            
                            <div class="motivo-box">
                                <p><strong>Motivo da devolução:</strong></p>
                                <p><?= htmlspecialchars($pedido['motivoDevolucao']) ?></p>
                            </div>
                            
                            <div class="itens-lista">
                                <strong>Itens do Pedido:</strong>
                                <ul>
                                    <?php
                                    $stmtItens = $pdo->prepare("SELECT oi.quantity, oi.value, p.description 
                                                               FROM OrderItems oi 
                                                               JOIN produtos p ON oi.idProduct = p.idProduct
                                                               WHERE oi.idOrder = ? AND p.supplier = ?");
                                    $stmtItens->execute([$pedido['id'], $fornecedor_id]);
                                    $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($itens as $item):
                                    ?>
                                        <li>
                                            <?= htmlspecialchars($item['description']) ?> - 
                                            Quantidade: <?= $item['quantity'] ?> - 
                                            Valor unitário: R$ <?= number_format($item['value'], 2, ',', '.') ?> - 
                                            <strong>Subtotal: R$ <?= number_format($item['value'] * $item['quantity'], 2, ',', '.') ?></strong>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            
                            <p><strong>Total do pedido:</strong> R$ <?= number_format($pedido['total'], 2, ',', '.') ?></p>
                            
                            <form method="post">
                                <input type="hidden" name="pedido_id" value="<?= $pedido['id'] ?>">
                                
                                <label for="motivoRecusa"><strong>Motivo da Resposta:</strong> (Obrigatório para rejeição)</label>
                                <textarea name="motivoRecusa" rows="3" placeholder="Digite o motivo da aprovação/rejeição..."></textarea>
                                
                                <button type="submit" name="aprovar_devolucao" class="btn-aprovar" onclick="return confirm('Tem certeza que deseja APROVAR esta devolução? O reembolso será processado.')">
                                    Aprovar Devolução
                                </button>
                                
                                <button type="submit" name="rejeitar_devolucao" class="btn-rejeitar" onclick="return confirm('Tem certeza que deseja REJEITAR esta devolução?')">
                                    Rejeitar Devolução
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="section">
                <h2>Histórico de Devoluções</h2>
                
                <?php if (empty($historico_devolucoes)): ?>
                    <p>Nenhuma devolução processada até o momento.</p>
                <?php else: ?>
                    <?php foreach ($historico_devolucoes as $devolucao): ?>
                        <div class="pedido">
                            <div class="info-cliente">
                                <p><strong>Cliente:</strong> <?= htmlspecialchars($devolucao['nome_cliente']) ?></p>
                                <p><strong>Pedido #<?= htmlspecialchars($devolucao['id']) ?></strong></p>
                                <p><strong>Status:</strong> 
                                    <span class="status <?= strtolower($devolucao['status']) ?>">
                                        <?= htmlspecialchars($devolucao['status']) ?>
                                    </span>
                                </p>
                                <p><strong>Data da resposta:</strong> <?= date('d/m/Y H:i', strtotime($devolucao['dataConfirmacao'])) ?></p>
                            </div>
                            
                            <div class="motivo-box">
                                <p><strong>Motivo da devolução:</strong></p>
                                <p><?= htmlspecialchars($devolucao['motivoDevolucao']) ?></p>
                            </div>
                            
                            <?php if (!empty($devolucao['motivoRecusa'])): ?>
                                <div class="motivo-box" style="background-color: #f8d7da;">
                                    <p><strong>Motivo da rejeição:</strong></p>
                                    <p><?= htmlspecialchars($devolucao['motivoRecusa']) ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="itens-lista">
                                <strong>Itens do Pedido:</strong>
                                <ul>
                                    <?php
                                    $stmtItens = $pdo->prepare("SELECT oi.quantity, oi.value, p.description 
                                                               FROM OrderItems oi 
                                                               JOIN produtos p ON oi.idProduct = p.idProduct
                                                               WHERE oi.idOrder = ? AND p.supplier = ?");
                                    $stmtItens->execute([$devolucao['id'], $fornecedor_id]);
                                    $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($itens as $item):
                                    ?>
                                        <li>
                                            <?= htmlspecialchars($item['description']) ?> - 
                                            Quantidade: <?= $item['quantity'] ?> - 
                                            Valor unitário: R$ <?= number_format($item['value'], 2, ',', '.') ?> - 
                                            <strong>Subtotal: R$ <?= number_format($item['value'] * $item['quantity'], 2, ',', '.') ?></strong>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            
                            <p><strong>Total do pedido:</strong> R$ <?= number_format($devolucao['total'], 2, ',', '.') ?></p>
                            
                            <?php if ($devolucao['status'] === 'Devolvido'): ?>
                                <p><strong>Valor reembolsado:</strong> R$ <?= number_format($devolucao['total'], 2, ',', '.') ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>