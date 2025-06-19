<?php

namespace Api\Controller;
use Api\Model\UserInterface;

class AuthController {
    public function __construct(private ?UserInterface $userModel = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function login($email, $senha) {
        $this->userModel->login($email, $senha);
    }

    public function cadastro(array $userData) {
        $this->userModel->cadastro($userData);
    }

    public function getUserById($id) {
        return $this->userModel->getUserById($id);
    }

    public function updateUser($id, array $userData) {
        return $this->userModel->updateUser($id, $userData);
    }

    public function deleteUser($id) {
        return $this->userModel->deleteUser($id);
    }
    
    public function logout() {
        session_unset();
        session_destroy();
        header('Location: /');
        exit;
    }

    protected function protectPage() {
        if (!isset($_SESSION['usuario'])) {
            header('Location: /login');
            exit;
        }
    }

    protected function protectFornecedorPage() {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['role'] !== 'fornecedor') {
            header('Location: /fornecedor/login');
            exit;
        }
    }

    protected function protectAdminPage() {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['role'] !== 'admin') {
            header('Location: /admin/login');
            exit;
        }
    }
}