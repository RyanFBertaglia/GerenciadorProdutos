<?php
// Secure session initialization
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once './includes/db.php';
require_once './includes/auth.php';

protectAdminPage();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['erro'] = "Método de requisição inválido";
    header('Location: /admin/pendentes');
    exit;
}

$idProduto = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$adminId = $_SESSION['admin']['id'];

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Update product status
    $stmt = $pdo->prepare("
        UPDATE produtos 
        SET status = 'aprovado', 
            aprovado_por = ?, 
            data_aprovacao = NOW() 
        WHERE idProduct = ? 
        AND status = 'pendente'
    ");
    
    $stmt->execute([$adminId, $idProduto]);
    
    if ($stmt->rowCount() > 0) {
        // Log the approval
        $logStmt = $pdo->prepare("
            INSERT INTO produtos_log 
            (produto_id, acao, admin_id, dados) 
            VALUES (?, 'aprovação', ?, ?)
        ");
        $logData = json_encode([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        ]);
        $logStmt->execute([$idProduto, $adminId, $logData]);
        
        $_SESSION['sucesso'] = "Produto aprovado com sucesso!";
    } else {
        $_SESSION['erro'] = "Produto não encontrado ou já foi processado";
    }
    
    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Approval error: " . $e->getMessage());
    $_SESSION['erro'] = "Erro ao processar aprovação";
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("System error: " . $e->getMessage());
    $_SESSION['erro'] = "Erro no sistema";
}

header('Location: /admin/pendentes');
exit;