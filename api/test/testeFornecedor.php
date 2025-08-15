<?php
require_once '../../includes/db.php';

$nome = "User teste";
$email = "admin@gmail.com";
$senhaHash = password_hash("testeFornecedor", PASSWORD_DEFAULT);
$cpf = "749.123.456/02";
$telefone = "(41) 5555-6666";

$stmt = $pdo->prepare("INSERT INTO fornecedores (nome, email, senha, cpf, telefone) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$nome, $email, $senhaHash, $cpf, $telefone]);

echo "Admin criado com sucesso!";
