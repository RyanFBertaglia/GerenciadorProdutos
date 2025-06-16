<?php
class OrderItemModel {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getItensPorPedido($pedido_id) {
        $stmt = $this->pdo->prepare("
            SELECT 
                oi.quantity,
                oi.value,
                p.nome AS nome_produto,
                p.description,
                p.image AS foto_produto,
                p.price,
                (oi.quantity * oi.value) AS subtotal
            FROM OrderItems oi 
            JOIN produtos p ON oi.idProduct = p.idProduct 
            WHERE oi.idOrder = ?
        ");
        $stmt->execute([$pedido_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}