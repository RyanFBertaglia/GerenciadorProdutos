<?php
namespace App\Controller;
use App\Model\ProdutoModel;

class ProductsController {

    public function __construct(private ProdutosModel $produtosModel) {
        $this->produtosModel = $produtosModel;
    }

    public function cadastrarProduto($nome, $descricao, $preco, $quantidade, $categoria) {
        return $this->produtosModel->adicionarProduct($nome, $descricao, $preco, $quantidade, $categoria);
    }

    public function deletarProduto($id) {
        return $this->produtosModel->excluirProduto($id);
    }

    public function atualizarProduto($id, $nome, $descricao, $preco, $quantidade, $categoria) {
        return $this->produtosModel->atualizarProduto($id, $nome, $descricao, $preco, $quantidade, $categoria);
    }
}

?>