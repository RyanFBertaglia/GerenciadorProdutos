<?php

require_once __DIR__ . '/../JWT.php'; // ajuste o caminho se necessário
use api\JWT;


class AuthController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function login() {
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';
        $senha = $input['senha'] ?? '';
        $tipo = $input['tipo'] ?? '';

        if (!in_array($tipo, ['usuario', 'fornecedor', 'admin'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Tipo inválido']);
            return;
        }

        switch ($tipo) {
            case 'usuario':
                $tabela = 'usuarios';
                break;
            case 'fornecedor':
                $tabela = 'fornecedores';
                break;
            case 'admin':
                $tabela = 'admin';
                break;
        }

        $stmt = $this->db->prepare("SELECT * FROM $tabela WHERE email = ?");
        $stmt->execute([$email]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dados && password_verify($senha, $dados['senha'])) {
            $payload = [
                'id' => $dados['id'],
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'role' => $tipo,
                'exp' => time() + 3600
            ];

            $token = JWT::gerarJWT($payload);
            echo json_encode([
                'token' => $token,
                'usuario' => $payload
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Credenciais inválidas']);
        }
    }
}
