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

}