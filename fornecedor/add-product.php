<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

protectFornecedorPage();

$fornecedorId = $_SESSION['usuario']['id'];
$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validações
        if (empty($_POST['description']) || empty($_POST['price']) || empty($_POST['nome'])) {
            throw new Exception("Preencha todos os campos obrigatórios");
        }

        if (!is_numeric($_POST['price']) || $_POST['price'] <= 0) {
            throw new Exception("Preço inválido");
        }

        // Upload da imagem
        $imagemNome = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $extensao = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($extensao, $extensoesPermitidas)) {
                throw new Exception("Tipo de arquivo não permitido. Use JPG, PNG ou GIF.");
            }

            $imagemNome = uniqid() . '.' . $extensao;
            $destino = "../static/uploads/" . $imagemNome;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $destino)) {
                throw new Exception("Erro ao fazer upload da imagem");
            }
        }

        // Inserir no banco
        $stmt = $pdo->prepare("INSERT INTO produtos 
            (nome, price, description, supplier, stock, image) 
            VALUES (?, ?, ?, ?, ?, ?)");

        $sucesso = $stmt->execute([
            $_POST['nome'],
            floatval($_POST['price']),
            $_POST['description'],
            $fornecedorId,
            intval($_POST['stock'] ?? 0),
            $imagemNome
        ]);

        if ($sucesso) {
            $_SESSION['sucesso'] = "Produto cadastrado com sucesso! Aguarde aprovação.";
            header('Location: /fornecedor/dashboard.php');
            exit;
        }
    } catch (Exception $e) {
        $erro = $e->getMessage();
        // Remove a imagem se houve erro após o upload
        if (!empty($imagemNome) && file_exists("../assets/uploads/" . $imagemNome)) {
            unlink("../static/uploads/" . $imagemNome);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Produto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Cadastrar Novo Produto</h2>
        
        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome*</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Descrição*</label>
                <input type="text" class="form-control" id="description" name="description" required>
            </div>
            
            <div class="mb-3">
                <label for="price" class="form-label">Preço (R$)*</label>
                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0.01" required>
            </div>
            
            <div class="mb-3">
                <label for="stock" class="form-label">Estoque Disponível</label>
                <input type="number" class="form-control" id="stock" name="stock" min="0" value="0">
            </div>
            
            <div class="mb-3">
                <label for="image" class="form-label">Imagem do Produto</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                <small class="text-muted">Formatos aceitos: JPG, PNG, GIF (Máx. 2MB)</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Cadastrar</button>
            <a href="/fornecedor/produtos.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>