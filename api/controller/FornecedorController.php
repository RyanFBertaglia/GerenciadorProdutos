<?php

namespace Api\Controller;
use Api\Model\FornecedorModel;

class FornecedorController {
    
    public function __construct(private FornecedorModel $fornecedorModel, private $fornecedorId = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $fornecedorId = $_SESSION['fornecedor']['id'] ?? null;
        $this->fornecedorId = $fornecedorId;
        $this->fornecedorModel = $fornecedorModel;
    }

    public function listarProdutos() {
        return $this->fornecedorModel->listarProdutos($this->fornecedorId);
    }

    public function contarProdutosAprovados() {
        return $this->fornecedorModel->contarProdutosAprovados($this->fornecedorId);
    }

    public function obterVendas() {
        return $this->fornecedorModel->obterVendas($this->fornecedorId);
    }

    public function excluirProduto($produtoId) {
        return $this->fornecedorModel->excluirProduto($produtoId, $this->fornecedorId);
    }

    public function getPendenteById($produtoId) {
        return $this->fornecedorModel->getPendenteById($produtoId, $this->fornecedorId);
    }

    public function getAllPendentes() {
        return $this->fornecedorModel->getAllPendentes($this->fornecedorId);
    }

    public function reembolso($idCliente, $valor) {
        return $this->fornecedorModel->reembolsar($idCliente, $this->fornecedorId, $valor);
    }

    public function atualizaStatus($acao, $pedidoId, $motivo = null) {
        if($acao === 'aprovar') {
            return $this->fornecedorModel->atualizarStatusPedido($pedidoId, 'Devolvido');
        } else {
            return $this->fornecedorModel->atualizarStatusPedido($pedidoId, 'Devolucao_Rejeitada', $motivo);
        }        
    }

    public function devolucao($produtoId, $acao, $motivo = null) {
        $produto = $this->getPendenteById($produtoId);
        if($acao === 'aprovar') {
            $this->reembolso($produto['id_cliente'], $produto['total']);
            return $this->atualizaStatus($acao, $produtoId, $motivo);
        } else {
            return $this->atualizaStatus($acao, $produtoId, $motivo);
        }
    }
}