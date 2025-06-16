<?php

//Incluir produtos service
interface AdminInterface {
  public function buscarPendencias();
  public function atualizarStatusProduto($id);
}
?>