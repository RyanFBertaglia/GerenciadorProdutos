<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once './includes/db.php';

$stmt = $pdo->query("SELECT * FROM produtos WHERE stock > 0 AND status = 'aprovado'");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Produtos</title>
  <link rel="stylesheet" href="./static/style/tipografia.css">
  <link rel="stylesheet" href="./static/style/main.css">
  <link rel="stylesheet" href="./static/style/loja.css">
  <style>
        body {
            font-family: arial; 
        }
  </style>

</head>
<body>

    <?php include './static/elements/sidebar-main.php'; ?>

    <main class="produtos-container" class="container-fluid">
    <div class="produtos-grid">
        <?php foreach ($produtos as $produto): ?>
        <div class="produto-card">
            <img src="./static/uploads/<?= htmlspecialchars($produto['image']) ?>" alt="<?= htmlspecialchars($produto['description']) ?>">
            <h3><?= htmlspecialchars($produto['nome']) ?></h3>
            <p>Fornecedor: <?= htmlspecialchars($produto['supplier']) ?></p>
            <p>R$ <?= number_format($produto['price'], 2, ',', '.') ?></p>
            <p>Disponível: <?= $produto['stock'] ?> unidades</p>
            <a href="/produto/detalhes?id=<?= $produto['idProduct'] ?>">Ver Detalhes</a>        </div>
        <?php endforeach; ?>
    </div>
    </main>
    
</body>
</html>