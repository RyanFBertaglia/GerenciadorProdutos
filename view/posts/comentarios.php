<?php
$pdo = require __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../model/Comentario.php';

use backend\Models\Comentario;

$comentarioModel = new Comentario($pdo);

$idPost = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 1;

$comentarios = $comentarioModel->buscarComentariosPorPost($idPost);
$totalComentarios = $comentarioModel->contarComentarios($idPost);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comentários</title>
    <link rel="stylesheet" href="/view/static/style/comentarios.css">
</head>
<body>
    <div class="container">
        <div class="header">Comentários</div>
        
        <div class="total-comentarios">
            Total de comentários: <?php echo $totalComentarios; ?>
        </div>
        
        <?php if (empty($comentarios)): ?>
            <div class="sem-comentarios">
                Nenhum comentário encontrado. Seja o primeiro a comentar!
            </div>
        <?php else: ?>
            <?php foreach ($comentarios as $comentario): ?>
                <div class="comentario-item">
                    <div class="comentario-email">
                        Email: <?php echo htmlspecialchars($comentario['email']); ?>
                    </div>
                    <div class="comentario-texto">
                        <?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?>
                    </div>
                    <div class="comentario-data">
                        <?php 
                        if (isset($comentario['data_comentario'])) {
                            echo date('d/m/Y H:i', strtotime($comentario['data_comentario']));
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div style="text-align: center;">
            <a href="comentar?post_id=<?php echo $idPost; ?>" class="btn btn-comentar">
                Comentar
            </a>
            <a href="posts" class="btn btn-cancelar">Cancelar</a>

        </div>
    </div>

    <script>
        function atualizarComentarios() {
            location.reload();
        }
        
        setInterval(atualizarComentarios, 30000);
        
        window.addEventListener('beforeunload', function(e) {
        });
    </script>
</body>
</html>