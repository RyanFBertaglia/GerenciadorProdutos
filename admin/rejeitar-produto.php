<?php
require_once './includes/db.php';
require_once './includes/auth.php';

protectAdminPage();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idProduto = $_POST['id'];
    $motivo = $_POST['motivo'];
    $adminId = $_SESSION['usuario']['id'];
    
    try {
        $stmt = $pdo->prepare("
            UPDATE produtos 
            SET status = 'rejeitado', 
                motivo_rejeicao = ?,
                aprovado_por = ?, 
                data_aprovacao = NOW() 
            WHERE idProduct = ? AND status = 'pendente'
        ");
        
        $stmt->execute([$motivo, $adminId, $idProduto]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['sucesso'] = "Produto rejeitado com sucesso!";
        } else {
            $_SESSION['erro'] = "Produto não encontrado ou já foi processado";
        }
    } catch (PDOException $e) {
        $_SESSION['erro'] = "Erro ao rejeitar produto: " . $e->getMessage();
    }
    
    header('Location: /admin/pendentes');
    exit;
}