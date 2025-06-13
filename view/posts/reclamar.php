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

    if (isset($_SESSION['email'])) {
        $_POST['email'] = $_SESSION['email'];
    } else {
        $_SESSION['erro'] = "Acesso negado";
        header("Location: /erro");
    }

    $posts = new Posts($pdo);
    $repo = new PostController($posts);

    $repo->save($_POST, $_FILES);
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Criar Post</title>
  <link rel="stylesheet" href="/static/css/style.css">
</head>
<body>
  <?php if (isset($_GET['erro'])): ?>
    <div class="erro"><?= htmlspecialchars($_GET['erro']) ?></div>
  <?php endif; ?>

  <form action="/reclamar" method="POST" enctype="multipart/form-data" id="mainForm">
    <h1>Novo Post</h1>

    <div class="form-group">
      <label for="titulo">Título (opcional):</label>
      <textarea id="titulo" name="titulo" rows="1" placeholder="Título do post"></textarea>
    </div>

    <div class="form-group">
      <label for="descricao">Descrição:</label>
      <textarea id="descricao" name="descricao" rows="5" required placeholder="Descreva o local ou problema"></textarea>
      <div class="limit-info">
        <small>Limite: 1000 caracteres</small>
        <small id="char-counter">0/1000</small>
      </div>
    </div>

    <div class="form-group upload-group">
      <label>Anexar Imagens (opcional):</label>
      <input type="file" id="imagens" name="imagens[]" accept="image/png, image/jpeg, image/gif" multiple>
      <small>Máximo 4 imagens, até 5 MB cada</small>
      <div id="file-list" class="file-list"></div>
    </div>

    <button type="submit" class="submit-button" id="submitButton">Enviar</button>
</form>


  <script>
    document.addEventListener('DOMContentLoaded', function() {
    const descricao = document.getElementById('descricao');
    const charCounter = document.getElementById('char-counter');
    const maxLength = 1000;
    console.log("kjfalsdfj");
    if (!descricao || !charCounter) return;

    atualizarContador();

    descricao.addEventListener('input', atualizarContador);

    function atualizarContador() {
        const comprimento = descricao.value.length;

        charCounter.textContent = `${comprimento}/${maxLength}`;

        if (comprimento > maxLength * 0.9) {
            charCounter.style.color = '#ff6b6b';
        } else {
            charCounter.style.color = '';
        }
    }
});
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('imagem');
    const fileList = document.getElementById('file-list');
    const uploadLabel = document.querySelector('.file-upload-label');
    const submitButton = document.getElementById('submitButton');
    const fileLimit = 4;
    const maxSize = 5 * 1024 * 1024; // 5MB

    showEmptyMessage();

    uploadLabel.addEventListener('click', function(e) {
        e.preventDefault();
        fileInput.click();
    });

    fileInput.addEventListener('change', updateFileList);

    function updateFileList() {
        fileList.innerHTML = '';
        
        if (!fileInput.files.length) {
            showEmptyMessage();
            return;
        }

        if (fileInput.files.length > fileLimit) {
            alert(`Você pode enviar no máximo ${fileLimit} imagens.`);
            fileInput.value = '';
            showEmptyMessage();
            return;
        }

        Array.from(fileInput.files).forEach((file, index) => {
            if (file.size > maxSize) {
                alert(`O arquivo "${file.name}" excede o limite de 5MB.`);
                fileInput.value = '';
                showEmptyMessage();
                return;
            }

            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert(`O arquivo "${file.name}" não é um tipo de imagem válido.`);
                fileInput.value = '';
                showEmptyMessage();
                return;
            }

            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            
            fileItem.innerHTML = `
                <span class="file-icon">📄</span>
                <div class="file-info">
                    <span class="file-name">${file.name}</span>
                    <span class="file-size">${formatFileSize(file.size)}</span>
                </div>
                <span class="file-remove" data-index="${index}">×</span>
            `;
            
            fileList.appendChild(fileItem);
        });

        document.querySelectorAll('.file-remove').forEach(btn => {
            btn.addEventListener('click', function() {
                removeFile(parseInt(this.getAttribute('data-index')));
            });
        });
        updateCounter();
    }

    function removeFile(index) {
        const dt = new DataTransfer();
        const files = fileInput.files;
        
        for (let i = 0; i < files.length; i++) {
            if (i !== index) dt.items.add(files[i]);
        }
        
        fileInput.files = dt.files;
        updateFileList();
    }

    function showEmptyMessage() {
        fileList.innerHTML = '<div class="empty-message">Nenhuma imagem selecionada</div>';
        updateCounter();
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function updateCounter() {
        const count = fileInput.files ? fileInput.files.length : 0;
        uploadLabel.textContent = `Adicionar Imagens (${count}/${fileLimit})`;
    }
});
  </script>
</body>
</html>
