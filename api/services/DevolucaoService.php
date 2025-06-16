<?php
class DevolucaoService {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function processar($fornecedor_id, $pedido_id, $acao, $motivoRecusa) {
        $this->pdo->beginTransaction();
        
        try {
            // Verifica pedido
            $pedido = $this->verificarPedido($fornecedor_id, $pedido_id);
            
            // Atualiza status
            $this->atualizarStatusPedido($pedido_id, $acao, $motivoRecusa);
            
            // Processa reembolso se necessário
            if ($acao === 'aprovar') {
                $this->processarReembolso($fornecedor_id, $pedido);
            }
            
            $this->pdo->commit();
            
            return [
                'mensagem' => ($acao === 'aprovar') 
                    ? "Devolução aprovada! R$ " . number_format($pedido['total'], 2, ',', '.') . " reembolsados para o cliente." 
                    : "Devolução rejeitada. O pedido #{$pedido_id} foi mantido como confirmado."
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    private function verificarPedido($fornecedor_id, $pedido_id) {
        $stmt = $this->pdo->prepare("SELECT o.*, u.id as id_cliente FROM Orders o 
                                   JOIN OrderItems oi ON o.id = oi.idOrder
                                   JOIN produtos p ON oi.idProduct = p.idProduct
                                   JOIN usuarios u ON o.idUser = u.id
                                   WHERE o.id = ? AND p.supplier = ? AND o.status = 'Devolucao_Pendente'");
        $stmt->execute([$pedido_id, $fornecedor_id]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$pedido) {
            throw new Exception("Pedido não encontrado, não pertence a você ou não está pendente de devolução.");
        }
        
        return $pedido;
    }
    
    private function atualizarStatusPedido($pedido_id, $acao, $motivoRecusa) {
        $novo_status = ($acao === 'aprovar') ? 'Devolvido' : 'Confirmado';
        $stmt = $this->pdo->prepare("UPDATE Orders SET 
                                    status = ?, 
                                    dataConfirmacao = NOW(), 
                                    " . ($acao === 'rejeitar' ? "motivoRecusa = ?" : "motivoRecusa = NULL") . "
                                    WHERE id = ?");
        
        $params = [$novo_status];
        if ($acao === 'rejeitar') {
            $params[] = $motivoRecusa;
        }
        $params[] = $pedido_id;
        
        if (!$stmt->execute($params)) {
            throw new Exception("Erro ao atualizar status do pedido.");
        }
    }
    
    private function processarReembolso($fornecedor_id, $pedido) {
        // Implementação do reembolso...
        // (mesma lógica que você já tinha no código original)
    }
}