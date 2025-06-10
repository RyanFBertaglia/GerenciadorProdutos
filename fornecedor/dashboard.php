<?php
require_once './includes/db.php';
require_once './includes/auth.php';

protectFornecedorPage();

$fornecedorId = $_SESSION['usuario']['id'];

// Buscar produtos do fornecedor
$stmt = $pdo->prepare("
    SELECT * FROM produtos 
    WHERE supplier = ?
    ORDER BY 
        CASE status 
            WHEN 'pendente' THEN 1
            WHEN 'rejeitado' THEN 2
            WHEN 'aprovado' THEN 3
        END,
        idProduct DESC
");
$stmt->execute([$fornecedorId]);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Aprovados
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM produtos WHERE supplier = ? AND status = 'aprovado'");
$stmt->execute([$fornecedorId]);
$produtosAprovados = $stmt->fetch()['total'];

// Vendidos
$stmt = $pdo->prepare("
    SELECT 
        SUM(vendidos) as total_vendidos,
        SUM(vendidos * price) as total_vendas
    FROM produtos 
    WHERE supplier = ? AND status = 'aprovado'
");
$stmt->execute([$fornecedorId]);
$resultado = $stmt->fetch();

$vendidos = $resultado['total_vendidos'] ?? 0;
$vendas = $resultado['total_vendas'] ?? 0;


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Fornecedor</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">


    <link rel="stylesheet" href="../static/style/admin/main.css">
</head>
<body>
    <?php include './static/elements/sidebar-fornecedor.php'; ?>
    <main>
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h2 style="color: white;" >Meu Dashboard</h2>
                
                <div class="row my-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Produtos Aprovados</h5>
                                <h3><?= $produtosAprovados ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row my-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Produtos Vendidos</h5>
                                <h3><?= $vendidos ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row my-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">Total Faturado</h5>
                                <h3>R$ <?= $vendas ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <h3 style="color: white;">Últimos Produtos</h3>
                <div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Preço</th>
                <th>Vendidos</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (array_slice($produtos, 0, 5) as $produto): ?>
            <tr>
                <td><?= htmlspecialchars($produto['description']) ?></td>
                <td>R$ <?= number_format($produto['price'], 2, ',', '.') ?></td>
                <td><?= $produto['vendidos'] ?? 0 ?></td>
                <td>
                    <span class="status-badge status-<?= $produto['status'] ?>">
                        <?= ucfirst($produto['status']) ?>
                    </span>
                    <?php if ($produto['status'] === 'rejeitado' && !empty($produto['motivo_rejeicao'])): ?>
                        <small class="d-block text-muted"><?= htmlspecialchars($produto['motivo_rejeicao']) ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="/fornecedor/editar-produto.php?id=<?= $produto['idProduct'] ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <a href="/fornecedor/remover-produto.php?id=<?= $produto['idProduct'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza?')">
                        <i class="bi bi-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>