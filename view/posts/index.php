<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = require __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../model/Posts.php';
require_once __DIR__ . '/../../controller/PostController.php';
require_once __DIR__ . '/../../model/Comentario.php'; // Incluir modelo de comentários

use backend\Models\Posts;
use backend\Controller\PostController;
use backend\Models\Comentario;

$postsModel = new Posts($pdo);
$controller = new PostController($postsModel);
$comentarioModel = new Comentario($pdo); // Instanciar modelo de comentários

try {
    $posts = $controller->getAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts Cadastrados</title>
    <link rel="stylesheet" href="/view/static/style/posts.css">
</head>

<body>
<?php include './view/static/elements/nav.php'; ?>

<div class="container">
<br><br>

    <h1>Posts Cadastrados</h1>

    <?php if (empty($posts)): ?>
        <div class="no-image">Nenhum post encontrado.</div>
    <?php else: ?>
        <?php foreach ($posts as $post): 
            $totalComentarios = $comentarioModel->contarComentarios($post['idPost']);
        ?>
            <div class="post">
                <div class="post-header">
                    Publicado por: <?= htmlspecialchars($post['email'] ?? 'Usuário não informado') ?>
                </div>

                <?php if (!empty($post['titulo'])): ?>
                    <h2><?= htmlspecialchars($post['titulo']) ?></h2>
                <?php endif; ?>

                <div class="post-content">
                    <?= nl2br(htmlspecialchars($post['descricao'] ?? '')) ?>
                </div>

                <?php
                $imagens = [];
                if (!empty($post['imagens'])) {
                    $imagensDecoded = json_decode($post['imagens'], true);
                    if (is_array($imagensDecoded)) {
                        foreach ($imagensDecoded as $caminho) {
                            if (!empty(trim($caminho))) {
                                $caminhoCompleto = __DIR__ . '/../../' . trim($caminho);
                                if (file_exists($caminhoCompleto)) {
                                    $imagens[] = trim($caminho);
                                }
                            }
                        }
                    }
                }

                if (!empty($imagens)) {
                    $galleryId = 'gallery-' . ($post['id'] ?? uniqid());
                    $totalImagens = count($imagens);
                    
                    if ($totalImagens == 1) {
                ?>
                        <div class="single-image">
                            <img src="./<?= htmlspecialchars($imagens[0]) ?>" 
                                 alt="Imagem do post"
                                 onerror="this.parentElement.innerHTML='<div class=\'error-image\'>Erro ao carregar imagem</div>'">
                        </div>
                <?php 
                    } else {
                ?>
                        <div class="gallery-container" id="<?= $galleryId ?>">
                            <?php foreach ($imagens as $index => $caminho): 
                                $activeClass = $index === 0 ? ' active' : '';
                            ?>
                                <div class="mySlides<?= $activeClass ?>">
                                    <div class="numbertext"><?= ($index + 1) ?> / <?= $totalImagens ?></div>
                                    <img src="./<?= htmlspecialchars($caminho) ?>" 
                                         alt="Imagem <?= ($index + 1) ?> do post"
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtc2l6ZT0iMTgiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5JbWFnZW0gbsOjbyBlbmNvbnRyYWRhPC90ZXh0Pjwvc3ZnPg=='">
                                </div>
                            <?php endforeach; ?>

                            <a class="prev" onclick="plusSlides('<?= $galleryId ?>', -1)">&#10094;</a>
                            <a class="next" onclick="plusSlides('<?= $galleryId ?>', 1)">&#10095;</a>

                            <div class="caption-container">
                                <p class="caption">Imagem 1 de <?= $totalImagens ?></p>
                            </div>

                            <div class="row">
                                <?php foreach ($imagens as $index => $caminho): 
                                    $activeClass = $index === 0 ? ' active' : '';
                                ?>
                                    <div class="column">
                                        <img class="demo cursor<?= $activeClass ?>" 
                                             src="./<?= htmlspecialchars($caminho) ?>" 
                                             onclick="currentSlide('<?= $galleryId ?>', <?= ($index + 1) ?>)"
                                             alt="Miniatura <?= ($index + 1) ?>"
                                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0iI2RkZCIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LXNpemU9IjEwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+RXJybzwvdGV4dD48L3N2Zz4='">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                <?php 
                    }
                } else {
                ?>
                    <div class="no-image">Nenhuma imagem disponível para este post</div>
                <?php 
                }
                ?>

                <div class="buttons">
                    <button class="btn" onclick="window.location.href='/comentarios?post_id=<?= $post['idPost'] ?>'">
                        Ver comentários<?php if($totalComentarios > 0): ?> (<?= $totalComentarios ?>)<?php endif; ?>
                    </button>
                    <button class="btn" onclick="window.location.href='/comentar?post_id=<?= $post['idPost'] ?>'">
                        Responder
                    </button>
                </div>
                
                <?php if (!empty($post['data_postagem'])): 
                    $dataFormatada = date('d/m/Y H:i:s', strtotime($post['data_postagem']));
                ?>
                    <div class="post-date">
                        Postado em: <?= htmlspecialchars($dataFormatada) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script src="/view/static/js/galeria.js"></script>

<script>
// Função adicional para confirmação de ações
function confirmarAcao(acao, postId) {
    if (confirm('Deseja ' + acao + ' neste post?')) {
        if (acao === 'ver comentários') {
            window.location.href = '/comentarios?post_id=' + postId;
        } else if (acao === 'responder') {
            window.location.href = '/comentar?post_id=' + postId;
        }
    }
}

// Adicionar eventos de teclado para navegação
document.addEventListener('keydown', function(e) {
    // Tecla C para comentários do primeiro post
    if (e.key === 'c' || e.key === 'C') {
        const firstPost = document.querySelector('.post');
        if (firstPost) {
            const postId = firstPost.querySelector('button[onclick*="comentarios"]')
                          ?.getAttribute('onclick')
                          ?.match(/post_id=(\d+)/)?.[1];
            if (postId) {
                window.location.href = '/comentarios?post_id=' + postId;
            }
        }
    }
    
    // Tecla R para responder ao primeiro post
    if (e.key === 'r' || e.key === 'R') {
        const firstPost = document.querySelector('.post');
        if (firstPost) {
            const postId = firstPost.querySelector('button[onclick*="comentar"]')
                          ?.getAttribute('onclick')
                          ?.match(/post_id=(\d+)/)?.[1];
            if (postId) {
                window.location.href = '/comentar?post_id=' + postId;
            }
        }
    }
});

// Preload de páginas para melhor performance
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('button[onclick*="comentarios"], button[onclick*="comentar"]');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            const url = this.getAttribute('onclick').match(/window\.location\.href='([^']+)'/)?.[1];
            if (url) {
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = url;
                document.head.appendChild(link);
            }
        });
    });
});
</script>
</body>
</html>
<?php
} catch (Exception $e) {
    $_SESSION['erro'] = "Erro: " . $e->getMessage();
    error_log("Erro na exibição de posts: " . $e->getMessage());
    ?>
    <div style='color:red; padding:20px; border: 1px solid #dc3545; border-radius: 4px; background-color: #f8d7da; margin: 20px;'>
        <strong>Erro:</strong> Não foi possível carregar os posts. Tente novamente mais tarde.
    </div>
    <?php
}
?>  