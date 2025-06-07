<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once './includes/db.php';
require_once './includes/auth.php';

protectPage();

// Dados do POST
$usuario_id = $_SESSION['usuario']['id'];
$item_id = $_POST['id'] ?? null;
$novaQuantidade = $_POST['quantidade'] ?? null;

// Validação
if (!$item_id || !is_numeric($novaQuantidade) || $novaQuantidade < 1) {
    $_SESSION['erro'] = "Dados inválidos";
    header("Location: /user/carrinho");
    exit;
}

try {
    // Buscar o item do carrinho com produto e estoque
    $stmt = $pdo->prepare("
        SELECT c.id, c.produto_id, p.stock 
        FROM carrinho c 
        JOIN produtos p ON c.produto_id = p.idProduct 
        WHERE c.id = ? AND c.usuario_id = ?
    ");
    $stmt->execute([$item_id, $usuario_id]);
    $item = $stmt->fetch();

    if (!$item) {
        $_SESSION['erro'] = "Item do carrinho não encontrado";
        header("Location: /user/carrinho");
        exit;
    }

    if ($novaQuantidade > $item['stock']) {
        $_SESSION['erro'] = "Quantidade solicitada maior que o estoque disponível";
        header("Location: /user/carrinho");
        exit;
    }

    // Atualizar a quantidade
    $stmt = $pdo->prepare("UPDATE carrinho SET quantidade = ? WHERE id = ?");
    $stmt->execute([$novaQuantidade, $item_id]);

    $_SESSION['sucesso'] = "Quantidade atualizada com sucesso!";
    header("Location: /user/carrinho");
    exit;

} catch (PDOException $e) {
    $_SESSION['erro'] = "Erro no banco de dados: " . $e->getMessage();
    header("Location: /user/carrinho");
    exit;
}
