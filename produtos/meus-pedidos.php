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
        
        $stmtVerifica = $pdo->prepare("SELECT * FROM Orders WHERE id = ? AND idUser = ? AND status = 'Entregue'");
        $stmtVerifica->execute([$pedido_id, $usuario_id]);
        $pedido = $stmtVerifica->fetch(PDO::FETCH_ASSOC);
        
        if (!$pedido) {
            throw new Exception("Pedido não encontrado ou não está com status 'Entregue'.");
        }
        
        $stmtConfirma = $pdo->prepare("UPDATE Orders SET status = 'Confirmado', dataConfirmacao = NOW() WHERE id = ?");
        $result = $stmtConfirma->execute([$pedido_id]);
        
        if (!$result) {
            throw new Exception("Erro ao confirmar entrega do pedido.");
        }
        
        $pdo->commit();
        $_SESSION['sucesso'] = "Entrega do pedido #{$pedido_id} confirmada com sucesso!";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['erro'] = "Erro ao confirmar entrega: " . $e->getMessage();
    }
    
    header('Location: /user/pedidos');
    exit;
}

// Processa a rejeição da devolução pelo fornecedor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'confirmar_rejeicao') {
    $pedido_id = (int)$_POST['order_id'];
    $fornecedor_id = $_SESSION['usuario']['id']; // Supondo que o fornecedor também esteja autenticado na mesma sessão

    try {
        $pdo->beginTransaction();

        $stmtVerifica = $pdo->prepare("SELECT * FROM Orders WHERE id = ? AND idFornecedor = ? AND status = 'Devolucao_Pendente'");
        $stmtVerifica->execute([$pedido_id, $fornecedor_id]);
        $pedido = $stmtVerifica->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) {
            throw new Exception("Pedido não encontrado ou não está com solicitação de devolução pendente.");
        }

        $stmtAtualiza = $pdo->prepare("UPDATE Orders SET status = 'Devolucao_Rejeitada', dataRejeicao = NOW() WHERE id = ?");
        $result = $stmtAtualiza->execute([$pedido_id]);

        if (!$result) {
            throw new Exception("Erro ao rejeitar devolução.");
        }

        $pdo->commit();
        $_SESSION['sucesso'] = "Devolução do pedido #{$pedido_id} rejeitada com sucesso.";

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['erro'] = "Erro ao rejeitar devolução: " . $e->getMessage();
    }

    header('Location: /fornecedor/pedidos'); // ou o caminho correto da tela do fornecedor
    exit;
}


// Processa solicitação de devolução
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['solicitar_devolucao'])) {
    $pedido_id = (int)$_POST['pedido_id'];
    $motivo = trim($_POST['motivo_devolucao']);
    
    try {
        $pdo->beginTransaction();
        
        $stmtVerifica = $pdo->prepare("SELECT * FROM Orders WHERE id = ? AND idUser = ? AND status = 'Confirmado'");
        $stmtVerifica->execute([$pedido_id, $usuario_id]);
        $pedido = $stmtVerifica->fetch(PDO::FETCH_ASSOC);
        
        if (!$pedido) {
            throw new Exception("Pedido não encontrado ou não pode ser devolvido.");
        }
        
        $dataLimite = date('Y-m-d H:i:s', strtotime($pedido['dataConfirmacao'] . ' +30 days'));
        if (date('Y-m-d H:i:s') > $dataLimite) {
            throw new Exception("Prazo para devolução expirado (30 dias após confirmação).");
        }
        
        $stmtDevolucao = $pdo->prepare("UPDATE Orders SET status = 'Devolucao_Pendente', dataDevolucao = NOW(), motivoDevolucao = ? WHERE id = ?");
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

// Query modificada para obter todos os dados necessários
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
.status.devolucao_pendente { background-color: #fff3cd; color: #856404; }
.status.devolvido { background-color: #f8d7da; color: #721c24; }

/* MODIFICAÇÃO: Substituir .itens-lista por .pedido-itens */
.pedido-itens {
    margin: 10px 0;
    /* Remove background e padding para evitar caixa aninhada */
}

/* NOVA CLASSE: Estilo para cada item individual */
.produto-item {
    display: flex;
    align-items: center;
    padding: 10px;
    margin: 10px 0;
    background: #f5f5f5; /* Levemente diferente do fundo do pedido */
    border-radius: 8px;
    border: none; /* Remove bordas para evitar aninhamento */
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
    </style>
</head>
<body>
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
    <?php foreach ($pedidos as $pedido): ?>
        <div class="pedido">
            <div class="pedido-header">
                <div>
                    <h3>Identificador do Pedido #<?= htmlspecialchars($pedido['id']) ?></h3>
                    <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($pedido['dataPedido'])) ?></p>
                </div>
                <div>
                    <span class="status <?= strtolower(str_replace(' ', '_', $pedido['status'])) ?>">
                        <?= htmlspecialchars($pedido['status']) ?>
                    </span>
                </div>
            </div>

            <!-- Agora usando os dados do fornecedor -->
            <p><strong>Fornecedor:</strong> <?= htmlspecialchars($pedido['nome_fornecedor']) ?></p>
            <p><strong>Total:</strong> R$ <?= number_format($pedido['total'], 2, ',', '.') ?></p>

            <div class="pedido-itens">
                <strong>Itens do Pedido:</strong>
                <?php foreach ($pedido['itens'] as $item): ?>
                    <div class="produto-item" style="display: flex; align-items: center; padding: 10px; margin: 10px 0; background: #f9f9f9; border-radius: 8px; border: none;">
                        <!-- Foto do produto -->
                        <img src="./static/uploads/<?= htmlspecialchars($item['foto_produto']) ?>" 
                             alt="<?= htmlspecialchars($item['nome_produto']) ?>"
                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px; margin-right: 15px; flex-shrink: 0;">
                        
                        <!-- Dados do produto -->
                        <div class="produto-info" style="flex: 1;">
                            <div style="font-weight: bold; margin-bottom: 5px;"><?= htmlspecialchars($item['nome_produto']) ?></div>
                            <div style="font-size: 14px; color: #666; margin-bottom: 5px;"><?= htmlspecialchars($item['description']) ?></div>
                            <div style="font-size: 14px;">
                                <span>Qtd: <?= $item['quantity'] ?></span> | 
                                <span>Unit: R$ <?= number_format($item['value'], 2, ',', '.') ?></span> | 
                                <span style="font-weight: bold;">Total: R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

<?php if ($pedido['status'] === 'Entregue'): ?>
    <div style="margin-top: 15px; padding: 10px; background-color: #fff3cd; border-radius: 5px;">
        <p><strong>📦 Pedido Entregue!</strong></p>
        <p>Confirme o recebimento para finalizar o pedido:</p>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="pedido_id" value="<?= $pedido['id'] ?>">
            <input type="hidden" name="confirmar_entrega" value="1">
            <button type="submit" class="btn-confirmar" onclick="return confirm('Tem certeza de que deseja confirmar o recebimento deste pedido?')">
                ✓ Confirmar Recebimento
            </button>
        </form>
    </div>
<?php elseif ($pedido['status'] === 'Confirmado' && empty($pedido['dataDevolucao'])): ?>
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
                        <small style="display: block; margin-top: 5px; color: #666;">
                            Prazo até: <?= date('d/m/Y', $dataLimite) ?>
                        </small>
                    </form>
                </div>
            <?php else: ?>
                <p style="color: #999; font-size: 12px;">Prazo para devolução expirado</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php elseif ($pedido['status'] === 'Devolucao_Pendente'): ?>
    <div style="background-color: #fff3cd; padding: 10px; border-radius: 5px; margin-top: 15px;">
        <p><strong>🔄 Devolução Solicitada</strong></p>
        <p>Motivo: <?= htmlspecialchars($pedido['motivoDevolucao']) ?></p>
        <p>Solicitada em: <?= date('d/m/Y H:i', strtotime($pedido['dataDevolucao'])) ?></p>
        <p><em>Aguardando análise do fornecedor...</em></p>
    </div>
<?php elseif ($pedido['status'] === 'Confirmado' && !empty($pedido['motivoRecusa'])): ?>
    <div style="background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-top: 15px;">
        <p><strong>❌ Devolução Rejeitada</strong></p>
        <p><strong>Motivo da rejeição:</strong> <?= htmlspecialchars($pedido['motivoRecusa']) ?></p>
        <p><strong>Data da resposta:</strong> <?= date('d/m/Y H:i', strtotime($pedido['dataConfirmacao'])) ?></p>
    </div>
<?php elseif ($pedido['status'] === 'Devolvido'): ?>
    <div style="background-color: #d4edda; padding: 10px; border-radius: 5px; margin-top: 15px;">
        <p><strong>✅ Devolução Aprovada</strong></p>
        <p>Valor restituído: R$ <?= number_format($pedido['total'], 2, ',', '.') ?></p>
        <p><strong>Data da aprovação:</strong> <?= date('d/m/Y H:i', strtotime($pedido['dataConfirmacao'])) ?></p>
    </div>
<?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <p><a href="/">Voltar ao painel</a></p>
</body>
</html>