<?php

interface UserInterface {
    public function buscarCarrinho($id);
    public function comprarProduto($id); // Usa produtos model
    public function atualizarStatusProduto($id);
    public function confirmarEntrega($user, $order);
}
?>