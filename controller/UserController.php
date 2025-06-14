<?php
namespace backend\Controller;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use backend\Models\Users;

    class UserController {
        private $users;

        public function __construct(Users $users) {
            $this->users = $users;
        }

        function login($email, $senha) {
            if($this->users->authenticate($email, $senha)) {
                $this->saveSession($email);
            } else {
                $_SESSION['erro'] = "Email ou senha incorretos.";
                header("Location: /erro");
                exit;
            }
        }

        function register(array $data) {  
            if($this->users->create($data)) {
                $this->saveSession($data['email']);
            } else {
                $_SESSION['erro'] = "Email ou senha incorretos.";
                header("Location: /erro");
                exit;
            }
        }

        function saveSession($email) {
            $user = $this->users->findByEmail($email);
            $_SESSION['logado'] = true;
            $_SESSION['email'] = $user['email'];
            header("Location: /home");
        }

    }