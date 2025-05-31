<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

protectFornecedorPage();

$fornecedorId = $_SESSION['usuario']['id'];
$produtoId = $_GET['id'] ?? 0;

// Verifica se o produto pertence ao fornecedor
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE idProduct = ? AND id_fornecedor = ?");
$stmt->execute([$produtoId, $fornecedorId]);
$produto = $stmt->fetch();

if (!$produto) {
    $_SESSION['erro'] = "Produto não encontrado ou você não tem permissão";
    header('Location: /fornecedor/produtos.php');
    exit;
}

// Remove a imagem se existir
if (!empty($produto['image']) && file_exists("../assets/uploads/" . $produto['image'])) {
    unlink("../assets/uploads/" . $produto['image']);
}

// Remove do banco
$stmt = $pdo->prepare("DELETE FROM produtos WHERE idProduct = ?");
$stmt->execute([$produtoId]);

$_SESSION['sucesso'] = "Produto removido com sucesso!";
header('Location: /fornecedor/produtos.php');
exit;