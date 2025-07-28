<?php

namespace Api\Controller;

use Api\Model\AdminModel;
use Api\Model\ProdutosModel;

class AdminController {

    private $adminId = null;
    
    public function __construct(private ProdutosModel $produtosModel, private ?AdminModel $adminModel = NULL) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $adminId = $_SESSION['admin']['id'] ?? null;
        $this->adminId = $adminId;
        $this->produtosModel = $produtosModel;
        $this->adminModel = $adminModel;
    }

    
    public function aprovarPedido($id) {
        return $this->produtosModel->aprovarProduto($id, $this->adminId);
    }

    public function rejeitarPedido($idProduto, $motivo) {
        return $this->produtosModel->rejeitarProduto($motivo, $this->adminId, $idProduto);
    }

    public function listarPedidos() {
        return $this->produtosModel->listarProdutosPendentes();
    }

    public function contarProdutosPendentes() {
        return $this->produtosModel->contarProdutosPendentes();
    }

    public function excluirProduto($id) {
        return $this->produtosModel->excluirProduto($id);    
    }

    public function buscarProdutoPorId($id) {
        return $this->produtosModel->buscarProdutoPorId($id);
    }

    public function listarAtividadesRecentes() {
        return $this->produtosModel->listarAtividadesRecentes();
    }

    public function getAllUsers($limit, $offset) {
        return $this->adminModel->getAllUsers($limit, $offset);
    }

    public function addSaldo($userId, $amount) {
        return $this->adminModel->updateUserSaldo($userId, $amount);
    }

    public function getTotalUsers() {
        return $this->adminModel->getTotalUsers();
    }
}