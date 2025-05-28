<?php
require_once './Product.php';

$host = "localhost";
$user = "root";
$password = "";
$database = "seu_banco_de_dados";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Buscar produtos
$sql = "SELECT * FROM produtos";
$result = $conn->query($sql);

$produtos = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $produtos[] = new Product(
            $row['idProduct'],
            $row['price'],
            $row['description'],
            $row['supplier'],
            $row['stock'],
            $row['image']
        );
    }
}
$conn->close();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Produtos</title>
    <link rel="stylesheet" href="../static/style/products.css">
</head>
<body>
    <h1 style="text-align: center; margin-bottom: 30px;">Catálogo de Produtos</h1>
    
    <div class="container">
        <?php foreach ($produtos as $produto): ?>
            <div class="product-card">
                <img src="images/<?= $produto->getImage() ?>" alt="<?= $produto->getDescription() ?>" class="product-image">
                <div class="product-info">
                    <h3 class="product-title"><?= $produto->getDescription() ?></h3>
                    <p class="product-supplier">Fornecedor: <?= $produto->getSupplier() ?></p>
                    <p class="product-price">R$ <?= $produto->getPrice() ?></p>
                    <span class="stock <?= $produto->isAvailable() ? 'in-stock' : 'out-of-stock' ?>">
                        <?= $produto->isAvailable() ? 'Em estoque (' . $produto->getStock() . ')' : 'Esgotado' ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>