<?php
require_once '../../includes/db.php';

$stmt = $pdo->prepare("

UPDATE BankAccount 
SET balance = balance - 10.00
WHERE idFornecedor = ? 
  AND tipo = 'fornecedor' 
  AND status = 'A';

SELECT ROW_COUNT();
");
$fornecedor = 9;
$afetadas = $stmt->execute([$fornecedor]);
echo $afetadas;