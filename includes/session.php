<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('SESSION_TIMEOUT', 1800);

function confere_timeout()
{
    if (empty($_SESSION['logado'])) {
        header("Location: ./login.php");
        exit;
    }

    if (
        isset($_SESSION['session_start_time']) &&
        (time() - $_SESSION['session_start_time'] > SESSION_TIMEOUT)
    ) {
        session_unset();
        session_destroy();
        header("Location: ./login.php");
        exit;
    }

    $_SESSION['session_start_time'] = time();
}
