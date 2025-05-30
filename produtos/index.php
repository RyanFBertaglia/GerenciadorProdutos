<?php
session_start();
require_once '../includes/db.php';

$stmt = $pdo->query("SELECT * FROM produtos WHERE stock > 0");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="produtos-grid">
    <?php foreach ($produtos as $produto): ?>
    <div class="produto-card">
        <img src="../uploads/<?= htmlspecialchars($produto['image']) ?>" alt="<?= htmlspecialchars($produto['description']) ?>">
        <h3><?= htmlspecialchars($produto['description']) ?></h3>
        <p>Fornecedor: <?= htmlspecialchars($produto['supplier']) ?></p>
        <p>R$ <?= number_format($produto['price'], 2, ',', '.') ?></p>
        <p>Disponível: <?= $produto['stock'] ?> unidades</p>
        <a href="detalhes.php?id=<?= $produto['idProduct'] ?>">Ver Detalhes</a>
    </div>
    <?php endforeach; ?>
</div>