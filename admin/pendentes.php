<?php
require_once './includes/db.php';
require_once './includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


protectAdminPage();

// Buscar produtos pendentes de aprovação
$stmt = $pdo->prepare("
    SELECT p.*, u.nome as fornecedor_nome 
    FROM produtos p
    JOIN fornecedores u ON p.supplier = u.id
    WHERE p.status = 'pendente'
    ORDER BY p.idProduct DESC
");
$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Produtos Pendentes</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

    <div class="container">
        <h1>Produtos Pendentes de Aprovação</h1>
        
        <?php if (empty($produtos)): ?>
            <p>Não há produtos pendentes de aprovação.</p>
        <?php else: ?>
            <div class="produtos-grid">
                <?php foreach ($produtos as $produto): ?>
                <div class="produto-card">
                    <h3><?= htmlspecialchars($produto['description']) ?></h3>
                    <p>Fornecedor: <?= htmlspecialchars($produto['fornecedor_nome']) ?></p>
                    <p>Preço: R$ <?= number_format($produto['price'], 2, ',', '.') ?></p>
                    <p>Estoque: <?= $produto['stock'] ?></p>
                    
                    <?php if ($produto['image']): ?>
                        <img src="/assets/uploads/<?= htmlspecialchars($produto['image']) ?>" alt="<?= htmlspecialchars($produto['description']) ?>" width="200">
                    <?php endif; ?>
                    
                    <div class="acoes-admin">
                        <form action="/aprovar-produto" method="post" style="display: inline;">
                            <input type="hidden" name="id" value="<?= $produto['idProduct'] ?>">
                            <button type="submit" class="btn btn-success">Aprovar</button>
                        </form>
                        
                        <button type="button" class="btn btn-danger" onclick="document.getElementById('rejeitar-<?= $produto['idProduct'] ?>').style.display='block'">
                            Rejeitar
                        </button>
                        
                        <div id="rejeitar-<?= $produto['idProduct'] ?>" style="display: none; margin-top: 10px;">
                            <form action="/rejeitar-produto" method="post">
                                <input type="hidden" name="id" value="<?= $produto['idProduct'] ?>">
                                <div class="form-group">
                                    <label>Motivo da Rejeição:</label>
                                    <textarea name="motivo" class="form-control" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger">Confirmar Rejeição</button>
                                <button type="button" class="btn btn-secondary" onclick="document.getElementById('rejeitar-<?= $produto['idProduct'] ?>').style.display='none'">
                                    Cancelar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>