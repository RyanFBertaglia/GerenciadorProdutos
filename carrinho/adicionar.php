<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: /user/login.php');
    exit;
}

$usuario_id = $_SESSION['usuario']['id'];
$produto_id = $_POST['produto_id'];
$quantidade = $_POST['quantidade'] ?? 1;

// Verificar estoque
$stmt = $pdo->prepare("SELECT stock FROM produtos WHERE idProduct = ?");
$stmt->execute([$produto_id]);
$produto = $stmt->fetch();

if (!$produto || $produto['stock'] < $quantidade) {
    $_SESSION['erro'] = "Quantidade indisponível em estoque";
    header("Location: /produtos/detalhes.php?id=$produto_id");
    exit;
}

// Verificar se o produto já está no carrinho
$stmt = $pdo->prepare("
    SELECT * FROM carrinho 
    WHERE usuario_id = ? AND produto_id = ?
");
$stmt->execute([$usuario_id, $produto_id]);
$item = $stmt->fetch();

if ($item) {
    // Verificar se a nova quantidade não excede o estoque
    $novaQuantidade = $item['quantidade'] + $quantidade;
    if ($produto['stock'] < $novaQuantidade) {
        $_SESSION['erro'] = "Quantidade total no carrinho excede o estoque disponível";
        header("Location: /produtos/detalhes.php?id=$produto_id");
        exit;
    }
    
    // Atualizar quantidade
    $stmt = $pdo->prepare("
        UPDATE carrinho 
        SET quantidade = quantidade + ? 
        WHERE id = ?
    ");
    $stmt->execute([$quantidade, $item['id']]);
} else {
    // Adicionar novo item
    $stmt = $pdo->prepare("
        INSERT INTO carrinho (usuario_id, produto_id, quantidade)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$usuario_id, $produto_id, $quantidade]);
}

header('Location: /carrinho/');
?>