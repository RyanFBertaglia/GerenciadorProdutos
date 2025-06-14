<?php
namespace backend\Controller;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use backend\Models\Posts;
use Exception;

class PostController {
    private $posts;

    public function __construct(Posts $posts) {
        $this->posts = $posts;
    }

    public function getUserPosts($email) {
        return $this->posts->findByUser($email);
    }

    public function getAll() {
        return $this->posts->getAll();
    }

    public function save(array $data, array $arquivos) {
        try {
            if (empty($data['email'])) {
                throw new Exception("O email é obrigatório.");
            }
            
            if (empty($data['descricao'])) {
                throw new Exception("A descrição é obrigatória.");
            }

            $uploadDir = './uploads/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $imagens = [];

            if (!empty($arquivos['imagens']) && is_array($arquivos['imagens']['tmp_name'])) {
                $total = count($arquivos['imagens']['tmp_name']);
                $total = min($total, 5);

                for ($i = 0; $i < $total; $i++) {
                    if ($arquivos['imagens']['error'][$i] === UPLOAD_ERR_OK) {
                        $tmp = $arquivos['imagens']['tmp_name'][$i];
                        $name = basename($arquivos['imagens']['name'][$i]);
                        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                        $permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                        if (!in_array($ext, $permitidos)) {
                            throw new Exception("Tipo de imagem não permitido: $ext");
                        }

                        $novoNome = uniqid('img_') . "." . $ext;
                        $destino = $uploadDir . $novoNome;

                        if (move_uploaded_file($tmp, $destino)) {
                            $imagens[] = './uploads/' . $novoNome;
                        } else {
                            throw new Exception("Falha ao mover o arquivo $name.");
                        }
                    }
                }
            }

            $this->posts->create([
                'email' => $data['email'],
                'titulo' => $data['titulo'] ?? '',
                'descricao' => $data['descricao'],
                'imagens' => $imagens
            ]);

            header("Location: /sucesso");
            exit;
            
        } catch (Exception $e) {
            $_SESSION['erro'] = "Erro: " . $e->getMessage();
            header("Location: /erro");
            exit;
        }
    }
}
