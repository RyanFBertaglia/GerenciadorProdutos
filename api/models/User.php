<?php

namespace api\models;
use TipoUser;

class User implements TipoUser {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function criar($nome, $email) {
        $stmt = $this->db->prepare("INSERT INTO usuarios (nome, email) VALUES (?, ?)");
        $stmt->execute([$nome, $email]);
        return $this->db->lastInsertId();
    }

    public function buscarPorId($id) {
        $Id = (int)$id;
        $stmt = $this->db->prepare("SELECT id, nome, email FROM usuarios WHERE id = ?");
        $stmt->execute([$Id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function login($email, $senha) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
