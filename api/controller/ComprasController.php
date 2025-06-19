<?php

namespace Api\Controller;
use Api\Model\CarrinhoModel;

class ComprasController {

    private $usuarioId = null;
    
    public function __construct(private CarrinhoModel $carrinhoModel) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $usuarioId = $_SESSION['usuario']['id'] ?? null;
        $this->usuarioId = $usuarioId;
        $this->carrinhoModel = $carrinhoModel;
    }

    public function visualizarCarrinho() {
        $itens = $this->carrinhoModel->getItensCarrinho($this->usuarioId);
        $total = $this->carrinhoModel->calcularTotal($itens);
        
        return [
            'itens' => $itens,
            'total' => $total,
            'quantidade' => count($itens)
        ];
    }

    public function finalizarCompra() {
        $itens = $this->carrinhoModel->getItensCarrinho($this->usuarioId);
        
        if (empty($itens)) {
            return ['erro' => 'Carrinho vazio'];
        }

        $conta = $this->carrinhoModel->verificarSaldoUsuario($this->usuarioId);
        $total = $this->carrinhoModel->calcularTotal($itens);
        
        if ($conta['balance'] < $total) {
            return ['erro' => 'Saldo insuficiente'];
        }

        foreach ($itens as $item) {
            $estoque = $this->carrinhoModel->verificarEstoque($item['idProduct']);
            if ($estoque['stock'] < $item['quantidade']) {
                return ['erro' => 'Estoque insuficiente para ' . $item['description']];
            }
        }

        $itensPorFornecedor = $this->agruparPorFornecedor($itens);
        $pedidos = [];

        foreach ($itensPorFornecedor as $idFornecedor => $itensDoFornecedor) {
            $subtotal = $this->carrinhoModel->calcularTotal($itensDoFornecedor);
            $orderId = $this->carrinhoModel->criarPedido($this->usuarioId, $subtotal, $idFornecedor);
            
            foreach ($itensDoFornecedor as $item) {
                $this->carrinhoModel->adicionarItemPedido($orderId, $item['idProduct'], $item['quantidade'], $item['price']);
                $this->carrinhoModel->reduzirEstoque($item['idProduct'], $item['quantidade']);
                $this->carrinhoModel->atualizarVendidos($item['idProduct'], $item['quantidade']);
            }
            
            $this->carrinhoModel->criarPagamento($this->usuarioId, $orderId, $subtotal);
            $pedidos[] = $orderId;
        }

        $novoSaldo = $conta['balance'] - $total;
        $this->carrinhoModel->atualizarSaldo($conta['idAccount'], $novoSaldo);
        $this->carrinhoModel->limparCarrinho($this->usuarioId);

        return [
            'sucesso' => true,
            'pedidos' => $pedidos,
            'total_pago' => $total,
            'saldo_restante' => $novoSaldo
        ];
    }

    public function limparCarrinho() {
        return $this->carrinhoModel->limparCarrinho($this->usuarioId);
    }

    public function verificarSaldo() {
        $conta = $this->carrinhoModel->verificarSaldoUsuario($this->usuarioId);
        return $conta ? $conta['balance'] : 0;
    }

    public function calcularTotal() {
        $itens = $this->carrinhoModel->getItensCarrinho($this->usuarioId);
        return $this->carrinhoModel->calcularTotal($itens);
    }

    public function verificarDisponibilidade() {
        $itens = $this->carrinhoModel->getItensCarrinho($this->usuarioId);
        $indisponiveis = [];

        foreach ($itens as $item) {
            $estoque = $this->carrinhoModel->verificarEstoque($item['idProduct']);
            if ($estoque['stock'] < $item['quantidade']) {
                $indisponiveis[] = $item['description'];
            }
        }

        return empty($indisponiveis) ? true : $indisponiveis;
    }

    public function atualizarQuantidade($id, $quantidade) {
        $stmt = $this->carrinhoModel->pdo->prepare("
            UPDATE carrinho 
            SET quantidade = ? 
            WHERE id = ? AND usuario_id = ?
        ");
        return $stmt->execute([$quantidade, $id, $this->usuarioId]);
    }

    public function removerItem($id) {
        return $this->carrinhoModel->removerItem($id, $this->usuarioId);
    }
}

?>