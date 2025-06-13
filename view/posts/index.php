<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = require __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../model/Posts.php';
require_once __DIR__ . '/../../controller/PostController.php';

use backend\Models\Posts;
use backend\Controller\PostController;

$postsModel = new Posts($pdo);
$controller = new PostController($postsModel);

try {
    $posts = $controller->getAll();
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Posts Cadastrados</title>
    <link rel="stylesheet" href="./public/static/css/dash.css">
</head>
<body>
<div class="container">
    <h1>Posts Cadastrados</h1>

    <?php foreach ($posts as $post): ?>
        <div class="post">
            <div class="post-header">
                Publicado por: <?= htmlspecialchars($post['email']) ?>
            </div>

            <?php if (!empty($post['titulo'])): ?>
                <h2><?= htmlspecialchars($post['titulo']) ?></h2>
            <?php endif; ?>

            <div class="post-content">
                <?= nl2br(htmlspecialchars($post['descricao'])) ?>
            </div>

            <?php
            $imagens = json_decode($post['imagens'] ?? '[]', true);
            if (!empty($imagens)):
            ?>
                <div class="gallery">
                    <?php foreach ($imagens as $caminho): ?>
                        <div class="post-image">
                        <img src="./<?= htmlspecialchars($caminho) ?>" alt="Imagem do post" style="max-width: 200px;">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="post-date">
                Postado em: <?= date('d/m/Y H:i:s', strtotime($post['data_postagem'])) ?>
            </div>
        </div>
    <?php endforeach; ?>

</div>
</body>
</html>
<?php
} catch (Exception $e) {
    $_SESSION['erro'] = "Erro: " . $e->getMessage();
    header("Location: /erro");
}
?>
