<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once './includes/db.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE idProduct = ?");
$stmt->execute([$id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    die("Produto não encontrado");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Detalhes Produto</title>
    <link rel="stylesheet" href="../static/style/admin/main.css">
    <style>
    body {
        margin: 0;
        padding: 0;
        font-family: arial;
        grid-template-areas: "aside main";
        display: grid;
    }
    aside {
        grid-area: aside;
    }
    main {
        grid-area: main;
        padding: 20px;
        font-family: Arial, sans-serif;
        max-width: 1200px;
    }
    img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        display: block;
        max-height: 400px;
        object-fit: contain;
        background-color: white;
        padding: 10px;
    }

    /* Informações do Produto */
    p {
        font-size: 1.1rem;
        margin: 12px 0;
        line-height: 1.6;
    }

    p strong {
        color: #2c3e50;
        font-weight: 600;
    }

    /* Formulário */
    form {
        margin-top: 30px;
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    input[type="number"] {
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
        width: 80px;
        text-align: center;
    }

    input[type="number"]:focus {
        outline: none;
        border-color: #4a90e2;
        box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
    }

    /* Botão */
    button[type="submit"] {
        background-color: #4a90e2;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    button[type="submit"]:hover {
        background-color: #3a7bc8;
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    p:last-of-type {
        color: #ffedcb;
        font-weight: bold;
        font-size: 1.2rem;
        padding: 10px;
        background-color: #316a66;
        border-left: 4px solid #30c193;
        display: inline-block;
    }

    @media (max-width: 768px) {
        main {
            margin-left: 0;
            padding: 20px;
        }

        form {
            flex-direction: column;
            align-items: flex-start;
        }

        img {
            max-height: 300px;
        }
    }

    @media (max-width: 480px) {
        h1 {
            font-size: 1.5rem;
        }
        
        p {
            font-size: 1rem;
        }
    }
    </style>
</head>
<body>
<?php include './static/elements/sidebar-main.php'; ?>
    <main>
        <br><br>
    <h1><?= htmlspecialchars($produto['description']) ?></h1>
<img src="../static/uploads/<?= htmlspecialchars($produto['image']) ?>" alt="<?= htmlspecialchars($produto['description']) ?>">
<p>Fornecedor: <?= htmlspecialchars($produto['supplier']) ?></p>
<p>Preço: R$ <?= number_format($produto['price'], 2, ',', '.') ?></p>
<p>Estoque disponível: <?= $produto['stock'] ?> unidades</p>

<?php if ($produto['stock'] > 0): ?>
<form action="/adicionar" method="post">
    <input type="hidden" name="produto_id" value="<?= $produto['idProduct'] ?>">
    <input type="number" name="quantidade" value="1" min="1" max="<?= $produto['stock'] ?>">
    <button type="submit">Adicionar ao Carrinho</button>
</form>
<?php else: ?>
<p>Produto esgotado</p>
<?php endif; ?>
    </main>


</main>
</body>
</html>