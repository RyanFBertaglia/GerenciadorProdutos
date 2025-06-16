<?php

interface FornecedorInterface {
    public function listaProduto(); // Usa produtos model
    public function buscaProduto($id);
    public function atualizarStatusProduto($id);
}
?>