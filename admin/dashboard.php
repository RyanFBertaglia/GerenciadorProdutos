<?php
require_once './includes/db.php';
require_once './includes/auth.php';

protectAdminPage();

$stmt = $pdo->query("SELECT COUNT(*) as total FROM fornecedores");
$totalFornecedores = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM produtos WHERE status = 'aprovado'");
$totalProdutos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM produtos WHERE status = 'pendente'");
$produtosPendentes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./static/style/admin/dashboard.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar bg-dark admin-sidebar" id="barraLateral">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="/admin/dashboard">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/pendentes">
                                <i class="bi bi-card-checklist me-2"></i>
                                Produtos Pendentes
                                <?php if ($produtosPendentes > 0): ?>
                                    <span class="badge bg-danger float-end"><?= $produtosPendentes ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/fornecedores">
                                <i class="bi bi-people me-2"></i>
                                Fornecedores
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/produto">
                                <i class="bi bi-box-seam me-2"></i>
                                Produtos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/usuarios.php">
                                <i class="bi bi-person-lines-fill me-2"></i>
                                Usuários
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link" href="/logout">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Sair
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <span class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-person-fill"></i> <?= htmlspecialchars($_SESSION['admin']['nome']) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Cards de Estatísticas -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary">
                            <div class="card-body text-center">
                                <i class="bi bi-people card-icon"></i>
                                <h5 class="card-title">Fornecedores</h5>
                                <h2 class="card-text"><?= $totalFornecedores ?></h2>
                                <a href="/admin/fornecedores" class="btn btn-light btn-sm">Ver todos</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card text-white bg-success">
                            <div class="card-body text-center">
                                <i class="bi bi-box-seam card-icon"></i>
                                <h5 class="card-title">Produtos Ativos</h5>
                                <h2 class="card-text"><?= $totalProdutos ?></h2>
                                <a href="/produto" class="btn btn-light btn-sm">Ver todos</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card text-white bg-warning">
                            <div class="card-body text-center">
                                <i class="bi bi-hourglass card-icon"></i>
                                <h5 class="card-title">Pendentes</h5>
                                <h2 class="card-text"><?= $produtosPendentes ?></h2>
                                <a href="/admin/pendentes" class="btn btn-light btn-sm">Aprovar</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção de Atividades Recentes -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Atividades Recentes</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $stmt = $pdo->query("
                                    SELECT p.description, p.status, p.data_aprovacao, u.nome as admin_nome
                                    FROM produtos p
                                    LEFT JOIN usuarios u ON p.aprovado_por = u.id
                                    WHERE p.status != 'pendente'
                                    ORDER BY p.data_aprovacao DESC
                                    LIMIT 5
                                ");
                                $atividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                
                                <?php if (empty($atividades)): ?>
                                    <p>Nenhuma atividade recente</p>
                                <?php else: ?>
                                    <div class="list-group">
                                        <?php foreach ($atividades as $atividade): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?= htmlspecialchars($atividade['description']) ?></h6>
                                                <small class="text-<?= $atividade['status'] === 'aprovado' ? 'success' : 'danger' ?>">
                                                    <?= ucfirst($atividade['status']) ?>
                                                </small>
                                            </div>
                                            <p class="mb-1">
                                                <?php if ($atividade['status'] === 'aprovado'): ?>
                                                    Aprovado por <?= htmlspecialchars($atividade['admin_nome']) ?>
                                                <?php else: ?>
                                                    Rejeitado por <?= htmlspecialchars($atividade['admin_nome']) ?>
                                                <?php endif; ?>
                                            </p>
                                            <small class="text-muted">
                                                <?= date('d/m/Y H:i', strtotime($atividade['data_aprovacao'])) ?>
                                            </small>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>