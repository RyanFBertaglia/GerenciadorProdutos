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
$sucesso = $_SESSION['sucesso'] ?? null;
$erro = $_SESSION['erro'] ?? null;
unset($_SESSION['sucesso'], $_SESSION['erro']);

// Processa confirmação de entrega
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_entrega'])) {
    $pedido_id = (int)$_POST['pedido_id'];
    
    try {
        $pdo->beginTransaction();
        
        $stmtVerifica = $pdo->prepare("SELECT * FROM Orders WHERE id = ? AND idUser = ? AND status = 'Entregue' FOR UPDATE");
        $stmtVerifica->execute([$pedido_id, $usuario_id]);
        $pedido = $stmtVerifica->fetch(PDO::FETCH_ASSOC);
        
        if (!$pedido) {
            throw new Exception("Pedido não encontrado ou não está com status 'Entregue'.");
        }
        
        // Confirma a entrega
        $stmtConfirma = $pdo->prepare("UPDATE Orders SET status = 'Confirmado', dataConfirmacao = NOW() WHERE id = ?");
        $result = $stmtConfirma->execute([$pedido_id]);
        
        if (!$result) {
            throw new Exception("Erro ao confirmar entrega do pedido.");
        }
        
        // Credita valor na conta do fornecedor APÓS confirmação
        $stmtConta = $pdo->prepare("SELECT * FROM BankAccount WHERE idFornecedor = ? AND tipo = 'fornecedor' AND status = 'A' FOR UPDATE");
        $stmtConta->execute([$pedido['idFornecedor']]);
        $conta = $stmtConta->fetch(PDO::FETCH_ASSOC);

        if (!$conta) {
            throw new Exception("Conta bancária do fornecedor não encontrada.");
        }

        $novoSaldo = $conta['balance'] + $pedido['total'];
        $stmtUpdateSaldo = $pdo->prepare("UPDATE BankAccount SET balance = ? WHERE idAccount = ?");
        $stmtUpdateSaldo->execute([$novoSaldo, $conta['idAccount']]);
        
        $pdo->commit();
        $_SESSION['sucesso'] = "Entrega do pedido #{$pedido_id} confirmada com sucesso! Pagamento liberado para o fornecedor.";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['erro'] = "Erro ao confirmar entrega: " . $e->getMessage();
    }
    
    header('Location: /user/pedidos');
    exit;
}

// Processa solicitação de devolução
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['solicitar_devolucao'])) {
    $pedido_id = (int)$_POST['pedido_id'];
    $motivo = trim($_POST['motivo_devolucao']);
    
    try {
        $pdo->beginTransaction();
        
        // Verifica se o pedido pode ser devolvido
        $stmtVerifica = $pdo->prepare("SELECT * FROM Orders WHERE id = ? AND idUser = ? AND status = 'Confirmado' FOR UPDATE");
        $stmtVerifica->execute([$pedido_id, $usuario_id]);
        $pedido = $stmtVerifica->fetch(PDO::FETCH_ASSOC);
        
        if (!$pedido) {
            throw new Exception("Pedido não encontrado ou não pode ser devolvido.");
        }
        
        // Verifica prazo de 30 dias
        $dataLimite = date('Y-m-d H:i:s', strtotime($pedido['dataConfirmacao'] . ' +30 days'));
        if (date('Y-m-d H:i:s') > $dataLimite) {
            throw new Exception("Prazo para devolução expirado (30 dias após confirmação).");
        }
        
        // Solicita devolução
        $stmtDevolucao = $pdo->prepare("UPDATE Orders SET 
                                       status = 'Devolucao_Pendente', 
                                       dataDevolucao = NOW(), 
                                       motivoDevolucao = ? 
                                       WHERE id = ?");
        $result = $stmtDevolucao->execute([$motivo, $pedido_id]);
        
        if (!$result) {
            throw new Exception("Erro ao solicitar devolução.");
        }
        
        $pdo->commit();
        $_SESSION['sucesso'] = "Solicitação de devolução do pedido #{$pedido_id} enviada com sucesso! Aguarde a análise do fornecedor.";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['erro'] = "Erro ao solicitar devolução: " . $e->getMessage();
    }
    
    header('Location: /user/pedidos');
    exit;
}

// Busca pedidos do usuário
$stmt = $pdo->prepare("
    SELECT 
        o.*,
        f.nome AS nome_fornecedor,
        f.email AS email_fornecedor,
        f.telefone AS telefone_fornecedor
    FROM Orders o
    JOIN fornecedores f ON o.idFornecedor = f.id
    WHERE o.idUser = ? 
    ORDER BY o.dataPedido DESC
");
$stmt->execute([$usuario_id]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Para cada pedido, buscar os itens com dados dos produtos
foreach ($pedidos as &$pedido) {
    $stmtItens = $pdo->prepare("
        SELECT 
            oi.quantity,
            oi.value,
            p.nome AS nome_produto,
            p.description,
            p.image AS foto_produto,
            p.price,
            (oi.quantity * oi.value) AS subtotal
        FROM OrderItems oi 
        JOIN produtos p ON oi.idProduct = p.idProduct 
        WHERE oi.idOrder = ?
    ");
    $stmtItens->execute([$pedido['id']]);
    $pedido['itens'] = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Meus Pedidos</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 900px; 
            margin: 20px auto; 
            padding: 0 15px; 
        }
        .pedido {
            border: 1px solid #ccc; 
            margin-bottom: 20px; 
            padding: 15px;
            border-radius: 8px;
            background-color: #f9f9f9;
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
        .status.processando { background-color: #d1ecf1; color: #0c5460; }
        .status.enviado { background-color: #d4edda; color: #155724; }
        .status.entregue { background-color: #cce5ff; color: #004085; }
        .status.confirmado { background-color: #d4edda; color: #155724; }
        .status.devolucao_pendente { background-color: #fff3cd; color: #856404; }
        .status.devolvido { background-color: #d4edda; color: #155724; }
        .status.devolucao_rejeitada { background-color: #f8d7da; color: #721c24; }

        .pedido-itens {
            margin: 10px 0;
        }

        .produto-item {
            display: flex;
            align-items: center;
            padding: 10px;
            margin: 10px 0;
            background: #f5f5f5;
            border-radius: 8px;
            border: none;
        }

        .produto-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .produto-info {
            flex: 1;
        }

        .produto-info > div:first-child {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 16px;
        }

        .produto-info > div:nth-child(2) {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .produto-info > div:last-child {
            font-size: 14px;
        }

        .btn-confirmar {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-devolucao {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 10px;
        }
        .btn-devolucao:hover {
            background-color: #c82333;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 500px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: vertical;
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
        .confirmacao-info {
            background-color: #e8f5e8;
            padding: 8px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 14px;
        }
        .form-devolucao {
            margin-top: 15px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        body {
            height: 100vh;
            margin: 0;
            padding: 0;
            display: grid;
            grid-template-columns: 200px 1fr;
            grid-template-areas: "aside main";
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
        }
        main {
            grid-area: main;
            padding: 3%;
        }
        aside {
            grid-area: aside;
        }
    </style>
    <link rel="stylesheet" href="../static/style/main.css">
</head>
<body>
<?php include './static/elements/sidebar-main.php'; ?>
<main>
    <br><br><br>
    <h1>Meus Pedidos</h1>

    <?php if ($sucesso): ?>
        <div class="alert-success"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <?php if ($erro): ?>
        <div class="alert-error"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <?php if (empty($pedidos)): ?>
        <p>Você ainda não fez nenhum pedido.</p>
    <?php else: ?>
        <?php foreach ($pedidos as $pedido): 
            // Normaliza o status para exibição
            $status_display = str_replace('_', ' ', $pedido['status']);
            $status_class = strtolower($pedido['status']);
        ?>
            <div class="pedido">
                <div class="pedido-header">
                    <div>
                        <h3>Identificador do Pedido #<?= htmlspecialchars($pedido['id']) ?></h3>
                        <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($pedido['dataPedido'])) ?></p>
                    </div>
                    <div>
                        <span class="status <?= $status_class ?>">
                            <?= htmlspecialchars($status_display) ?>
                        </span>
                    </div>
                </div>

                <p><strong>Fornecedor:</strong> <?= htmlspecialchars($pedido['nome_fornecedor']) ?></p>
                <p><strong>Total:</strong> R$ <?= number_format($pedido['total'], 2, ',', '.') ?></p>

                <div class="pedido-itens">
                    <strong>Itens do Pedido:</strong>
                    <?php foreach ($pedido['itens'] as $item): ?>
                        <div class="produto-item">
                            <img src="../static/uploads/<?= htmlspecialchars($item['foto_produto']) ?>" 
                                 alt="<?= htmlspecialchars($item['nome_produto']) ?>">
                            
                            <div class="produto-info">
                                <div><?= htmlspecialchars($item['nome_produto']) ?></div>
                                <div><?= htmlspecialchars($item['description']) ?></div>
                                <div>
                                    <span>Qtd: <?= $item['quantity'] ?></span> | 
                                    <span>Unit: R$ <?= number_format($item['value'], 2, ',', '.') ?></span> | 
                                    <span style="font-weight: bold;">Total: R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php 
                // Seções de ação baseadas no status
                switch($pedido['status']):
                    case 'Entregue': ?>
                        <div class="action-panel">
                            <p><strong>📦 Pedido Entregue!</strong></p>
                            <p>Confirme o recebimento para finalizar o pedido:</p>
                            
                            <form method="post">
                                <input type="hidden" name="pedido_id" value="<?= $pedido['id'] ?>">
                                <input type="hidden" name="confirmar_entrega" value="1">
                                <button type="submit" class="btn-confirmar" onclick="return confirm('Tem certeza de que deseja confirmar o recebimento deste pedido?')">
                                    ✓ Confirmar Recebimento
                                </button>
                            </form>
                        </div>
                        <?php break;
                    
                    case 'Confirmado': ?>
                        <div class="confirmacao-info">
                            <p><strong>✅ Pedido Confirmado!</strong></p>
                            <?php if (isset($pedido['dataConfirmacao'])): ?>
                                <p>Recebimento confirmado em: <?= date('d/m/Y H:i', strtotime($pedido['dataConfirmacao'])) ?></p>
                                
                                <?php 
                                $dataLimite = strtotime($pedido['dataConfirmacao'] . ' +30 days');
                                $agora = time();
                                if ($agora <= $dataLimite): 
                                ?>
                                    <div class="form-devolucao">
                                        <form method="post">
                                            <input type="hidden" name="pedido_id" value="<?= $pedido['id'] ?>">
                                            <p><strong>Solicitar Devolução</strong></p>
                                            <p><strong>Motivo:</strong></p>
                                            <textarea name="motivo_devolucao" rows="4" placeholder="Descreva o motivo da devolução..." required></textarea>
                                            <br>
                                            <button type="submit" name="solicitar_devolucao" class="btn-devolucao" onclick="return confirm('Tem certeza de que deseja solicitar a devolução deste pedido?')">
                                                Solicitar Devolução
                                            </button>
                                            <small>
                                                Prazo até: <?= date('d/m/Y', $dataLimite) ?>
                                            </small>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">Prazo para devolução expirado</p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php break;
                    
                    case 'Devolucao_Pendente': ?>
                        <div class="action-panel">
                            <p><strong>🔄 Devolução Solicitada</strong></p>
                            <p>Motivo: <?= htmlspecialchars($pedido['motivoDevolucao']) ?></p>
                            <p>Solicitada em: <?= date('d/m/Y H:i', strtotime($pedido['dataDevolucao'])) ?></p>
                            <p><em>Aguardando análise do fornecedor...</em></p>
                        </div>
                        <?php break;
                    
                    case 'Devolucao_Rejeitada': ?>
                        <div class="action-panel">
                            <p><strong>❌ Devolução Rejeitada</strong></p>
                            <p><strong>Motivo da rejeição:</strong> <?= htmlspecialchars($pedido['motivoRecusa']) ?></p>
                            <?php if ($pedido['dataRejeicaoDevolucao']): ?>
                                <p><strong>Data da resposta:</strong> <?= date('d/m/Y H:i', strtotime($pedido['dataRejeicaoDevolucao'])) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php break;
                    
                    case 'Devolvido': ?>
                        <div class="action-panel">
                            <p><strong>✅ Devolução Aprovada</strong></p>
                            <p>Valor restituído: R$ <?= number_format($pedido['total'], 2, ',', '.') ?></p>
                            <?php if ($pedido['dataAprovacaoDevolucao']): ?>
                                <p><strong>Data da aprovação:</strong> <?= date('d/m/Y H:i', strtotime($pedido['dataAprovacaoDevolucao'])) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php break;
                    
                    default: ?>
                        <div class="action-panel">
                            <p>Status atual: <strong><?= htmlspecialchars($status_display) ?></strong></p>
                        </div>
                <?php endswitch; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <p><a href="/">Voltar ao painel</a></p>
</main>
</body>
</html>