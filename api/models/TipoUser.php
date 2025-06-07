<?php

interface TipoUser {
  public function login() : boolean;
  public function buscarPorId();
  public function criar();
}
