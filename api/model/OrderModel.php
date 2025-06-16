<?php
class OrderModel {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getPedidosPendentes($fornecedor_id) {
        $stmt = $this->pdo->prepare("
            SELECT o.*, u.nome AS nome_cliente 
            FROM Orders o
            JOIN OrderItems oi ON oi.idOrder = o.id
            JOIN produtos p ON oi.idProduct = p.idProduct
            JOIN usuarios u ON o.idUser = u.id
            WHERE p.supplier = ? AND o.status = 'Devolucao_Pendente'
            GROUP BY o.id
            ORDER BY o.dataDevolucao ASC
        ");
        $stmt->execute([$fornecedor_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getHistoricoDevolucoes($fornecedor_id) {
        $stmt = $this->pdo->prepare("
            SELECT o.*, u.nome AS nome_cliente
            FROM Orders o
            JOIN OrderItems oi ON oi.idOrder = o.id
            JOIN produtos p ON oi.idProduct = p.idProduct
            JOIN usuarios u ON o.idUser = u.id
            WHERE p.supplier = ?
              AND o.status IN ('Devolvido', 'Confirmado') 
              AND o.dataDevolucao IS NOT NULL
            GROUP BY o.id
            ORDER BY o.dataConfirmacao DESC
        ");
        $stmt->execute([$fornecedor_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}