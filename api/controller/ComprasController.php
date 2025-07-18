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
        return $this->carrinhoModel->atualizarQuantidade($quantidade, $id, $this->usuarioId);
    }

    public function removerItem($id) {
        return $this->carrinhoModel->removerItem($id, $this->usuarioId);
    }
}

?>