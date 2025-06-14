<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = require __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../model/Posts.php';
require_once __DIR__ . '/../../controller/PostController.php';

use backend\Models\Posts;
use backend\Controller\PostController;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_SESSION['email'])) {
            throw new Exception("Acesso negado. Faça login para continuar.");
        }

        $email = $_SESSION['email'];
        $titulo = $_POST['titulo'] ?? '';
        $descricao = $_POST['descricao'] ?? '';

        if (empty($descricao)) {
            throw new Exception("A descrição é obrigatória.");
        }

        $titulo = htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');
        $descricao = htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8');

        $posts = new Posts($pdo);
        $repo = new PostController($posts);

        $repo->save([
            'email' => $email,
            'titulo' => $titulo,
            'descricao' => $descricao
        ], $_FILES);

    } catch (Exception $e) {
        $_SESSION['erro'] = $e->getMessage();
        header("Location: /erro");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Criar Post</title>
  <link rel="stylesheet" href="/view/static/style/reclama.css">
</head>
<body>
<?php include './view/static/elements/nav.php'; ?>
    <br><br>

  <div class="container">
    <h1>Criar Novo Post</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="message error">
            <strong>Erros encontrados:</strong>
            <ul class="error-list">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php elseif (isset($success) && $success): ?>
        <div class="message success">
            Post criado com sucesso!
        </div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" id="mainForm">
        <div class="form-group">
            <label for="titulo">Título (opcional):</label>
            <textarea id="titulo" name="titulo" rows="1" placeholder="Título do post"><?php 
                echo isset($titulo) ? htmlspecialchars($titulo) : ''; 
            ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="descricao">Descrição:</label>
            <textarea id="descricao" name="descricao" rows="5" required maxlength="1000" placeholder="Descreva o local ou problema"><?php 
                echo isset($descricao) ? htmlspecialchars($descricao) : ''; 
            ?></textarea>
            <div class="limit-info">
                <small>Limite: 1000 caracteres</small>
                <small id="char-counter">0/1000</small>
            </div>
        </div>
        
        <div class="form-group upload-group">
            <label>Anexar Imagens (opcional, máximo 4):</label>
            
            <div class="upload-area" id="uploadArea">
                <div class="upload-icon">📁</div>
                <p class="upload-text">Arraste e solte suas imagens aqui ou clique para selecionar</p>
                <button type="button" class="btn" id="selectFilesBtn">Selecionar Imagens</button>
                <input type="file" id="imagens" name="imagens[]" accept="image/png,image/jpeg,image/jpg,image/gif,image/webp" multiple class="file-input">
            </div>
            
            <div class="counter">
                <span id="selectedCount">0</span> de 4 imagens selecionadas
            </div>
            
            <div class="preview-container" id="previewContainer"></div>
        </div>
        
        <button type="submit" class="submit-button" id="submitBtn">Enviar Post</button>
    </form>
  </div>
  
  <script src="/view/static/js/verificaReclamacao.js"></script>
</body>
</html>