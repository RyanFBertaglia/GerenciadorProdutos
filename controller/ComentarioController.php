<?php
namespace backend\Controller;
require_once __DIR__ . '/../model/Comentario.php';

use backend\Models\Comentario;

class ComentarioController {
    private $comentarioModel;
    
    public function __construct($pdo) {
        $this->comentarioModel = new Comentario($pdo);
    }
    
    // Exibir página de comentários
    public function exibirComentarios() {
        // Verificar se o ID do post foi passado
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header('Location: /posts?erro=Post não encontrado');
            exit();
        }
        
        $idPost = (int)$_GET['id'];
        
        // Buscar informações do post
        $post = $this->comentarioModel->buscarPost($idPost);
        
        if (!$post) {
            header('Location: /posts?erro=Post não encontrado');
            exit();
        }
        
        // Buscar comentários do post
        $comentarios = $this->comentarioModel->buscarComentariosPorPost($idPost);
        $totalComentarios = $this->comentarioModel->contarComentarios($idPost);
        
        // Incluir a view
        include __DIR__ . '/../view/posts/comentarios.php';
    }
    
    // Processar novo comentário
    public function adicionarComentario() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $idPost = (int)$_POST['idPost'];
            $email = trim($_POST['email']);
            $comentario = trim($_POST['comentario']);
            
            // Validações
            $erros = [];
            
            if (empty($email)) {
                $erros[] = "Email é obrigatório";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erros[] = "Email inválido";
            }
            
            if (empty($comentario)) {
                $erros[] = "Comentário é obrigatório";
            } elseif (strlen($comentario) < 5) {
                $erros[] = "Comentário deve ter pelo menos 5 caracteres";
            }
            
            if (empty($erros)) {
                // Tentar criar o comentário
                if ($this->comentarioModel->criarComentario($idPost, $email, $comentario)) {
                    header("Location: /comentar?id=$idPost&sucesso=Comentário adicionado com sucesso!");
                } else {
                    header("Location: /comentar?id=$idPost&erro=Erro ao adicionar comentário");
                }
            } else {
                // Redirecionar com erros
                $mensagemErro = implode(', ', $erros);
                header("Location: /comentar?id=$idPost&erro=$mensagemErro");
            }
            exit();
        }
    }
    
    // Deletar comentário (opcional)
    public function deletarComentario() {
        if (isset($_GET['deletar']) && !empty($_GET['deletar'])) {
            $idComentario = (int)$_GET['deletar'];
            $idPost = (int)$_GET['id'];
            
            if ($this->comentarioModel->deletarComentario($idComentario)) {
                header("Location: /comentar?id=$idPost&sucesso=Comentário deletado com sucesso!");
            } else {
                header("Location: /comentar?id=$idPost&erro=Erro ao deletar comentário");
            }
            exit();
        }
    }
}