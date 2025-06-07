<?php

require_once __DIR__ . '/../models/User.php';


class UserController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function store() {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['nome']) || !isset($input['email'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Campos obrigatórios ausentes']);
            return;
        }

        $user = new User($this->db);
        $id = $user->criar($input['nome'], $input['email']);

        echo json_encode(['message' => 'Usuário criado', 'id' => $id]);
    }

    public function show($id) {
        $user = new User($this->db);
        $dados = $user->buscarPorId($id);

        if ($dados) {
            echo json_encode($dados);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Usuário não encontrado']);
        }
    }
}
