<?php
require_once '../../includes/db.php';

$nome = "User teste";
$email = "admin@gmail.com";
$senhaHash = password_hash("teste", PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO admin (nome, email, senha) VALUES (?, ?, ?)");
$stmt->execute([$nome, $email, $senhaHash]);

echo "Admin criado com sucesso!";
