<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

protectFornecedorPage();

$fornecedorId = $_SESSION['usuario']['id'];
$mensagem = '';

// Processar remoção de produto
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $produtoId = $_GET['id'];
    
    // Verificar se o produto pertence ao fornecedor
    $stmt = $pdo->prepare("SELECT image FROM produtos WHERE idProduct = ? AND supplier = ?");
    $stmt->execute([$produtoId, $fornecedorId]);
    $produto = $stmt->fetch();
    
    if ($produto) {
        // Remover imagem se existir
        if (!empty($produto['image']) && file_exists("../assets/uploads/" . $produto['image'])) {
            unlink("../assets/uploads/" . $produto['image']);
        }
        
        // Remover do banco
        $stmt = $pdo->prepare("DELETE FROM produtos WHERE idProduct = ?");
        $stmt->execute([$produtoId]);
        
        $mensagem = "Produto removido com sucesso!";
    }
}

// Buscar produtos do fornecedor
$stmt = $pdo->prepare("
    SELECT p.*, 
           u.nome as aprovador_nome
    FROM produtos p
    LEFT JOIN usuarios u ON p.aprovado_por = u.id
    WHERE p.supplier = ?
    ORDER BY 
        CASE p.status 
            WHEN 'pendente' THEN 1
            WHEN 'rejeitado' THEN 2
            WHEN 'aprovado' THEN 3
        END,
        p.idProduct DESC
");
$stmt->execute([$fornecedorId]);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Meus Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pendente {
            background-color: #ffc107;
            color: black;
        }
        .status-aprovado {
            background-color: #28a745;
            color: white;
        }
        .status-rejeitado {
            background-color: #dc3545;
            color: white;
        }
        .product-image {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../static/style/admin/dash-fornecedor.css">
    <link rel="stylesheet" href="../static/style/main.css">
    <link rel="stylesheet" href="../static/style/tipografia.css">
    </head>
<body>
    <?php include '../static/elements/sidebar-fornecedor.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <main class="w-100 py-4 px-3">
                <br><br>
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom w-100">
                <h2>Meus Produtos</h2>
                    <a href="/fornecedor/add-product.php" class="btn btn-primary" id="novo-produto">
                        <i class="bi bi-plus-circle"></i> Novo Produto
                    </a>
                </div>

                <?php if ($mensagem): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Imagem</th>
                                <th>Nome</th>
                                <th>Descrição</th>
                                <th>Preço</th>
                                <th>Estoque</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtos as $produto): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($produto['image'])): ?>
                                        <img src="../static/uploads/<?= htmlspecialchars($produto['image']) ?>" 
                                             alt="<?= htmlspecialchars($produto['nome']) ?>" 
                                             class="product-image">
                                    <?php else: ?>
                                        <span class="text-muted">Sem imagem</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($produto['nome']) ?></td>
                                <td><?= htmlspecialchars($produto['description']) ?></td>
                                <td>R$ <?= number_format($produto['price'], 2, ',', '.') ?></td>
                                <td><?= $produto['stock'] ?></td>
                                <td>
                                    <span class="status-badge status-<?= $produto['status'] ?>">
                                        <?= ucfirst($produto['status']) ?>
                                    </span>
                                    <?php if ($produto['status'] === 'rejeitado' && !empty($produto['motivo_rejeicao'])): ?>
                                        <small class="d-block text-muted">Motivo: <?= htmlspecialchars($produto['motivo_rejeicao']) ?></small>
                                    <?php endif; ?>
                                    <?php if ($produto['status'] === 'aprovado' && !empty($produto['aprovador_nome'])): ?>
                                        <small class="d-block text-muted">Por: <?= htmlspecialchars($produto['aprovador_nome']) ?></small>
                                        <small class="d-block text-muted">Em: <?= date('d/m/Y H:i', strtotime($produto['data_aprovacao'])) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/fornecedor/editar-produto.php?id=<?= $produto['idProduct'] ?>" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="?action=delete&id=<?= $produto['idProduct'] ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       title="Remover"
                                       onclick="return confirm('Tem certeza que deseja remover este produto?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

</body>
</html>