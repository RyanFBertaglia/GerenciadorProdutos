<?php
namespace Api\Model;

interface UserInterface {
    public function login($email, $senha);
    public function cadastro(array $userData);
    public function getUserById($id);
    public function updateUser($id, array $userData);
    public function deleteUser($id);
}