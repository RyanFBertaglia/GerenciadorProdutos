<?php

namespace Api\Controller;
use Api\Model\UserInterface;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class AuthController {
    
    public function __construct(private UserInterface $userModel) {
        $this->userModel = $userModel;
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
        session_start();
        session_unset();
        session_destroy();   
        header('Location: /');
        exit;
    }

    protected function protectPage() {
        session_start();
        if (!isset($_SESSION['usuario'])) {
            header('Location: /login');
            exit;
        }
    }

    protected function protectFornecedorPage() {
        session_start();
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['role'] !== 'fornecedor') {
            header('Location: /fornecedor/login');
            exit;
        }
    }

    protected function protectAdminPage() {
        session_start();
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['role'] !== 'admin') {
            header('Location: /admin/login');
            exit;
        }
    }

}