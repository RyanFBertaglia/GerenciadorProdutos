<?php
$pdo = require __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../model/Comentario.php';


use backend\Models\Comentario;

$comentarioModel = new Comentario($pdo);

$idPost = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 1;

$mensagem = '';
$tipoMensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['email'];
    $comentario = trim($_POST['comentario'] ?? '');
    
    if (empty($email)) {
        $mensagem = 'Por favor, informe seu email.';
        $tipoMensagem = 'erro';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = 'Por favor, informe um email válido.';
        $tipoMensagem = 'erro';
    } elseif (empty($comentario)) {
        $mensagem = 'Por favor, escreva seu comentário.';
        $tipoMensagem = 'erro';
    } elseif (strlen($comentario) < 10) {
        $mensagem = 'O comentário deve ter pelo menos 10 caracteres.';
        $tipoMensagem = 'erro';
    } else {
        if ($comentarioModel->criarComentario($idPost, $email, $comentario)) {
            $mensagem = 'Comentário enviado com sucesso!';
            $tipoMensagem = 'sucesso';
            $comentario = '';
        } else {
            $mensagem = 'Erro ao enviar comentário. Tente novamente.';
            $tipoMensagem = 'erro';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comentar</title>
    <link rel="stylesheet" href="/view/static/style/comentar.css">
    <style>
        
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Comentar</div>
        
        <?php if ($mensagem): ?>
            <div class="mensagem <?php echo $tipoMensagem; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" action="" id="comentarioForm">
                
                
                <div class="form-group">
                    <label for="comentario">Escrever comentário:</label>
                    <textarea 
                        id="comentario" 
                        name="comentario" 
                        required
                        placeholder="Escreva seu comentário aqui..."
                        maxlength="1000"
                    ><?php echo htmlspecialchars($comentario ?? ''); ?></textarea>
                    <div class="contador">
                        <span id="contadorTexto">0</span>/1000 caracteres
                    </div>
                </div>
                
                <div class="botoes">
                    <button type="submit" class="btn btn-enviar">Enviar</button>
                    <a href="comentarios?post_id=<?php echo $idPost; ?>" class="btn btn-cancelar">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const textarea = document.getElementById('comentario');
        const contador = document.getElementById('contadorTexto');
        
        function atualizarContador() {
            const tamanho = textarea.value.length;
            contador.textContent = tamanho;
            
            if (tamanho > 900) {
                contador.style.color = '#ff0000';
            } else if (tamanho > 800) {
                contador.style.color = '#ff8800';
            } else {
                contador.style.color = '#666';
            }
        }
        
        textarea.addEventListener('input', atualizarContador);
        textarea.addEventListener('keyup', atualizarContador);
        
        atualizarContador();
        
        document.getElementById('comentarioForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const comentario = document.getElementById('comentario').value.trim();
            
            if (!email) {
                alert('Por favor, informe seu email.');
                e.preventDefault();
                return;
            }
            
            if (!comentario) {
                alert('Por favor, escreva seu comentário.');
                e.preventDefault();
                return;
            }
            
            if (comentario.length < 10) {
                alert('O comentário deve ter pelo menos 10 caracteres.');
                e.preventDefault();
                return;
            }
            
            if (!confirm('Deseja enviar este comentário?')) {
                e.preventDefault();
            }
        });
        
        document.getElementById('email').focus();
        
        function salvarRascunho() {
            const email = document.getElementById('email').value;
            const comentario = document.getElementById('comentario').value;
            
            window.rascunhoEmail = email;
            window.rascunhoComentario = comentario;
        }
        
        setInterval(salvarRascunho, 30000);
        
        window.addEventListener('beforeunload', function(e) {
            const comentario = document.getElementById('comentario').value.trim();
            if (comentario && comentario.length > 10) {
                e.preventDefault();
                e.returnValue = 'Você tem um comentário não salvo. Deseja realmente sair?';
            }
        });
    </script>
</body>
</html>