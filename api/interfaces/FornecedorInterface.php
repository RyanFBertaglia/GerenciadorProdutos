<?php

interface FornecedorInterface {
  public function cadastrarProduto(); // Usa produtos model
  public function listaProdutos();
  public function atualizarStatusProduto($id);
}
?>