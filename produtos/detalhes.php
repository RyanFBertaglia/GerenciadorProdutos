<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE idProduct = ?");
$stmt->execute([$id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    die("Produto não encontrado");
}
?>

<h1><?= htmlspecialchars($produto['description']) ?></h1>
<img src="../uploads/<?= htmlspecialchars($produto['image']) ?>" alt="<?= htmlspecialchars($produto['description']) ?>">
<p>Fornecedor: <?= htmlspecialchars($produto['supplier']) ?></p>
<p>Preço: R$ <?= number_format($produto['price'], 2, ',', '.') ?></p>
<p>Estoque disponível: <?= $produto['stock'] ?> unidades</p>

<?php if ($produto['stock'] > 0): ?>
<form action="/carrinho/adicionar.php" method="post">
    <input type="hidden" name="produto_id" value="<?= $produto['idProduct'] ?>">
    <input type="number" name="quantidade" value="1" min="1" max="<?= $produto['stock'] ?>">
    <button type="submit">Adicionar ao Carrinho</button>
</form>
<?php else: ?>
<p>Produto esgotado</p>
<?php endif; ?>