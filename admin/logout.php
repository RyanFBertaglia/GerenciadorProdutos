<?php
require_once '../includes/auth.php';

if (isLoggedIn()) {
    $usuarioId = $_SESSION['usuario']['id'];
}

logout();

header('Location: /admin/login.php');
exit;