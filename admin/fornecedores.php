<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once './includes/db.php';
require_once './includes/auth.php';

$produtosPendentes = $_SESSION['produtosPendentes'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_fornecedor'])) {
    $fornecedor_id = (int)$_POST['fornecedor_id'];
    
    try {
        $pdo->beginTransaction();
        
        $stmtVerifica = $pdo->prepare("SELECT id FROM fornecedores WHERE id = ?");
        $stmtVerifica->execute([$fornecedor_id]);
        $fornecedor = $stmtVerifica->fetch(PDO::FETCH_ASSOC);
        
        if (!$fornecedor) {
            throw new Exception("Fornecedor não encontrado.");
        }
        
        $stmtDesativa = $pdo->prepare("DELETE FROM fornecedores WHERE id = ?");
        $stmtDesativa->execute([$fornecedor_id]);
        
        $stmtProdutos = $pdo->prepare("UPDATE produtos SET status = 'rejeitado' WHERE supplier = ?");
        $stmtProdutos->execute([$fornecedor_id]);
        
        $pdo->commit();
        
        $_SESSION['sucesso'] = "Fornecedor desativado com sucesso!";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['erro'] = "Erro: " . $e->getMessage();
    }
    
    header('Location: /admin/fornecedores');
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        f.id,
        f.nome,
        f.email,
        f.data_cadastro,
        
        -- Product statistics
        COALESCE(ps.total_produtos, 0) as total_produtos,
        COALESCE(ps.produtos_pendentes, 0) as produtos_pendentes,
        COALESCE(ps.produtos_rejeitados, 0) as produtos_rejeitados,
        COALESCE(ps.produtos_aprovados, 0) as produtos_aprovados,
        
        -- Sales statistics
        COALESCE(ss.total_vendidos, 0) as total_vendidos,
        COALESCE(ss.faturamento_total, 0.00) as faturamento_total,
        COALESCE(ss.total_pedidos, 0) as total_pedidos,
        ss.ultima_venda,
        
        -- Calculated metrics
        CASE 
            WHEN COALESCE(ps.total_produtos, 0) > 0 
            THEN ROUND((COALESCE(ps.produtos_aprovados, 0) * 100.0 / ps.total_produtos), 2)
            ELSE 0 
        END as taxa_aprovacao_percent,
        
        CASE 
            WHEN COALESCE(ss.total_pedidos, 0) > 0 
            THEN ROUND(ss.faturamento_total / ss.total_pedidos, 2)
            ELSE 0 
        END as ticket_medio,
        
        CASE 
            WHEN ss.ultima_venda IS NOT NULL 
            THEN DATEDIFF(CURDATE(), ss.ultima_venda)
            ELSE NULL 
        END as dias_desde_ultima_venda,
        
        CASE 
            WHEN COALESCE(ss.faturamento_total, 0) >= 10000 THEN 'Alto Desempenho'
            WHEN COALESCE(ss.faturamento_total, 0) >= 5000 THEN 'Médio Desempenho'
            WHEN COALESCE(ss.faturamento_total, 0) > 0 THEN 'Baixo Desempenho'
            ELSE 'Sem Vendas'
        END as categoria_performance
        
    FROM fornecedores f
    
    -- Product statistics subquery
    LEFT JOIN (
        SELECT 
            supplier,
            COUNT(*) as total_produtos,
            SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as produtos_pendentes,
            SUM(CASE WHEN status = 'rejeitado' THEN 1 ELSE 0 END) as produtos_rejeitados,
            SUM(CASE WHEN status = 'aprovado' THEN 1 ELSE 0 END) as produtos_aprovados
        FROM produtos 
        GROUP BY supplier
    ) ps ON f.id = ps.supplier
    
    -- Sales statistics subquery
    LEFT JOIN (
        SELECT 
            p.supplier,
            COUNT(DISTINCT o.id) as total_pedidos,
            SUM(oi.quantity) as total_vendidos,
            SUM(oi.quantity * oi.value) as faturamento_total,
            MAX(o.dataPedido) as ultima_venda
        FROM produtos p
        JOIN OrderItems oi ON p.idProduct = oi.idProduct
        JOIN Orders o ON oi.idOrder = o.id
        WHERE o.status = 'Confirmado'
        GROUP BY p.supplier
    ) ss ON f.id = ss.supplier
    
    ORDER BY faturamento_total DESC, total_produtos DESC
");
$stmt->execute();
$fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sucesso = $_SESSION['sucesso'] ?? null;
$erro = $_SESSION['erro'] ?? null;
unset($_SESSION['sucesso'], $_SESSION['erro']);

function getPerformanceCategoryClass($category) {
    switch($category) {
        case 'Alto Desempenho': return 'high-performance';
        case 'Médio Desempenho': return 'medium-performance';
        case 'Baixo Desempenho': return 'low-performance';
        default: return 'no-sales';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../static/style/fornecedores.css">
    <link rel="stylesheet" href="../static/style/admin/main.css">
    <title>Admin - Fornecedores Detalhados</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            border: 0;
            margin: 0;
            padding: 0;
            grid-template-columns: 200px 1fr;
            grid-template-areas: "aside main";
            font-family: arial;
        }
        aside {
            grid-area: aside;
        }
        .container {
            grid-area: main;
        }
        
    </style>
</head>
<body>
    <?php include './static/elements/sidebar-admin.php'; ?>
    <div class="container">

        <main>
            <div class="header">
                <h1>Fornecedores - Visão Detalhada</h1>
            </div>

            <?php if ($sucesso): ?>
                <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
            <?php endif; ?>

            <?php if ($erro): ?>
                <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Fornecedor</th>
                            <th>Contato</th>
                            <th>Produtos</th>
                            <th>Status Produtos</th>
                            <th>Performance</th>
                            <th>Vendas</th>
                            <th>Faturamento</th>
                            <th>Última Venda</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fornecedores as $fornecedor): ?>
                            <tr>
                                <td class="supplier-info">
                                    <strong><?= htmlspecialchars($fornecedor['nome']) ?></strong>
                                    <small>Cadastrado em: <?= date('d/m/Y', strtotime($fornecedor['data_cadastro'])) ?></small>
                                </td>
                                <td class="contact-info"><?= htmlspecialchars($fornecedor['email']) ?></td>
                                <td>
                                    <span class="badge badge-primary"><?= $fornecedor['total_produtos'] ?> total</span>
                                </td>
                                <td class="status-cell">
                                    <span class="badge badge-success" title="Aprovados"><?= $fornecedor['produtos_aprovados'] ?></span>
                                    <span class="badge badge-warning" title="Pendentes"><?= $fornecedor['produtos_pendentes'] ?></span>
                                    <span class="badge badge-danger" title="Rejeitados"><?= $fornecedor['produtos_rejeitados'] ?></span>
                                </td>
                                <td>
                                    <div class="performance-metrics">
                                        <div class="metric-item">
                                            <span class="metric-label">Taxa Aprovação:</span>
                                            <span class="metric-value approval-rate"><?= number_format($fornecedor['taxa_aprovacao_percent'], 1) ?>%</span>
                                        </div>
                                        <div class="metric-item">
                                            <span class="metric-label">Ticket Médio:</span>
                                            <span class="metric-value">R$ <?= number_format($fornecedor['ticket_medio'], 2, ',', '.') ?></span>
                                        </div>
                                        <?php if ($fornecedor['dias_desde_ultima_venda'] !== null): ?>
                                        <div class="metric-item">
                                            <span class="metric-label">Dias s/ venda:</span>
                                            <span class="metric-value days-since-sale"><?= $fornecedor['dias_desde_ultima_venda'] ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <span class="performance-category <?= getPerformanceCategoryClass($fornecedor['categoria_performance']) ?>">
                                            <?= $fornecedor['categoria_performance'] ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="sales-info"><?= $fornecedor['total_vendidos'] ?></td>
                                <td class="revenue-info">R$ <?= number_format($fornecedor['faturamento_total'], 2, ',', '.') ?></td>
                                <td>
                                    <?= $fornecedor['ultima_venda'] ? date('d/m/Y', strtotime($fornecedor['ultima_venda'])) : 'N/D' ?>
                                </td>
                                <td>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="fornecedor_id" value="<?= $fornecedor['id'] ?>">
                                        <input type="hidden" name="excluir_fornecedor" value="1">
                                        <button type="button" class="btn btn-danger" onclick="confirmarExclusao(<?= $fornecedor['id'] ?>, '<?= htmlspecialchars(addslashes($fornecedor['nome'])) ?>')">
                                            Excluir
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($fornecedores)): ?>
                            <tr>
                                <td colspan="9" class="empty-state">Nenhum fornecedor cadastrado</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Modal de Confirmação -->
            <div id="modalConfirmacao" class="modal">
                <div class="modal-content">
                    <h3>Confirmar Exclusão</h3>
                    <p id="modalMensagem">Tem certeza que deseja excluir este fornecedor?</p>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="fecharModal()">Cancelar</button>
                        <button type="button" class="btn btn-danger" onclick="confirmarExclusaoSubmit()">Confirmar</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let formParaEnviar = null;
        let fornecedorNome = '';
        
        function confirmarExclusao(id, nome) {
            fornecedorNome = nome;
            const modal = document.getElementById('modalConfirmacao');
            const mensagem = document.getElementById('modalMensagem');
            
            mensagem.innerHTML = `
                Tem certeza que deseja excluir o fornecedor <strong>${nome}</strong>?<br><br>
                <small style="color: #dc2626;">Todos os produtos relacionados também serão desativados. Esta ação não pode ser desfeita.</small>
            `;
            
            // Encontra o formulário correspondente
            formParaEnviar = document.querySelector(`form input[value="${id}"]`).closest('form');
            
            modal.style.display = 'block';
        }
        
        function fecharModal() {
            document.getElementById('modalConfirmacao').style.display = 'none';
            formParaEnviar = null;
        }
        
        function confirmarExclusaoSubmit() {
            if (formParaEnviar) {
                formParaEnviar.submit();
            }
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('modalConfirmacao');
            if (event.target === modal) {
                fecharModal();
            }
        }
        
        // Fechar modal com Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                fecharModal();
            }
        });


        
        document.addEventListener("DOMContentLoaded", () => {
        const body = document.body;
        const aside = document.querySelector("aside");
        body.classList.toggle("collapsed");
    });

    </script>
</body>
</html>