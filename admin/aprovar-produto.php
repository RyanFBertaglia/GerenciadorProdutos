<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

protectAdminPage();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idProduto = $_POST['id'];
    $adminId = $_SESSION['usuario']['id'];
    
    try {
        $stmt = $pdo->prepare("
            UPDATE produtos 
            SET status = 'aprovado', 
                aprovado_por = ?, 
                data_aprovacao = NOW() 
            WHERE idProduct = ? AND status = 'pendente'
        ");
        
        $stmt->execute([$adminId, $idProduto]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['sucesso'] = "Produto aprovado com sucesso!";
        } else {
            $_SESSION['erro'] = "Produto não encontrado ou já foi processado";
        }
    } catch (PDOException $e) {
        $_SESSION['erro'] = "Erro ao aprovar produto: " . $e->getMessage();
    }
    
    header('Location: pendentes.php');
    exit;
}