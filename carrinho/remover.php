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

$id = $_GET['id'] ?? 0;

// Verificar se o item pertence ao usuário
$stmt = $pdo->prepare("
    DELETE FROM carrinho 
    WHERE id = ? AND usuario_id = ?
");
$stmt->execute([$id, $_SESSION['usuario']['id']]);

header('Location: /carrinho/');
?>