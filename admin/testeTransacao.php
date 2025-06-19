<?php
require_once '../includes/db.php';

$stmt->$pdo->execute("INSERT INTO `bankaccount` (`idAccount`, `idUser`, `idFornecedor`, `tipo`, `balance`, `status`, `created_at`) VALUES (NULL, '2', NULL, 'usuario', '3000', 'A', current_timestamp()), (NULL, NULL, '1', 'fornecedor', '300', 'A', current_timestamp())");
