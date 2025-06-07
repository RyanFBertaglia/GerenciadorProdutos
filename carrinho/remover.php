<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once './includes/db.php';
require_once './includes/auth.php';

protectAdminPage(); // Or use protectPage() if this should be available to regular users too

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Validate ID
if (!$id || $id < 1) {
    $_SESSION['erro'] = "ID do item inválido";
    header("Location: /user/carrinho"); // Using router-compatible URL
    exit;
}

try {
    // Verify item belongs to user before deletion
    $stmt = $pdo->prepare("
        DELETE FROM carrinho 
        WHERE id = ? AND usuario_id = ?
    ");
    $stmt->execute([$id, $_SESSION['usuario']['id']]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['sucesso'] = "Item removido do carrinho com sucesso!";
    } else {
        $_SESSION['erro'] = "Item não encontrado ou não pertence ao seu carrinho";
    }
} catch (PDOException $e) {
    $_SESSION['erro'] = "Erro ao remover item: " . $e->getMessage();
    error_log("Cart deletion error: " . $e->getMessage());
}

header('Location: /user/carrinho');
exit;