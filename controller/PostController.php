<?php
namespace backend\Controller;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use backend\Models\Posts;

class PostController {
    private $users;

    public function __construct(Posts $posts) {
        $this->posts = $posts;
    }

    /*function save(array $data) {
        try {
            $this->posts->create($data);
        } catch(Exception $e) {
            $_SESSION['erro'] = "Erro: ". $e->getMessage();
            header("Location: /erro");
            exit;
        }
    }*/

    function getUserPosts($email) {
        return $this->posts->findByUser($email);
    }

    function getAll() {
        return $this->posts->getAll();
    }

    public function save(array $data, array $arquivos) {
        try {
            $email = $data['email'] ?? '';
            $titulo = $data['titulo'] ?? '';
            $descricao = $data['descricao'] ?? '';
            $imagens = [];
    
            if (empty($email) || empty($descricao)) {
                throw new Exception("Email e descrição são obrigatórios.");
            }
    
            // Processamento das imagens (até 5)
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
    
                        // Diretório de uploads no mesmo nível que este arquivo
                        $destino = "./uploads/" . uniqid() . "." . $ext;
    
                        if (!is_dir("./uploads")) {
                            mkdir("./uploads", 0777, true);
                        }
    
                        if (move_uploaded_file($tmp, $destino)) {
                            // Armazena o caminho relativo para depois usar no site
                            $imagens[] = $destino;
                        } else {
                            throw new Exception("Falha ao mover a imagem $name.");
                        }
                    }
                }
            }
    
            $this->posts->create([
                'email' => $email,
                'titulo' => $titulo,
                'descricao' => $descricao,
                'imagens' => $imagens
            ]);
    
            header("Location: /sucesso");
            exit;
        } catch (Exception $e) {
            $_SESSION['erro'] = "Erro: ". $e->getMessage();
            header("Location: /erro");
            exit;
        }
    }
    


}