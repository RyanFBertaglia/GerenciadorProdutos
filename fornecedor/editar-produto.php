<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

protectFornecedorPage();

$fornecedorId = $_SESSION['usuario']['id'];
$produtoId = $_GET['id'] ?? 0;
$erro = '';
$sucesso = '';

$stmt = $pdo->prepare("SELECT * FROM produtos WHERE idProduct = ? AND supplier = ?");
$stmt->execute([$produtoId, $fornecedorId]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    $_SESSION['erro'] = "Produto não encontrado ou você não tem permissão para editá-lo";
    header('Location: /fornecedor/produtos.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validações básicas
        if (empty($_POST['nome']) || empty($_POST['description']) || empty($_POST['price'])) {
            throw new Exception("Preencha todos os campos obrigatórios");
        }

        if (!is_numeric($_POST['price']) || $_POST['price'] <= 0) {
            throw new Exception("Preço inválido");
        }

        if (!is_numeric($_POST['stock']) || $_POST['stock'] < 0) {
            throw new Exception("Estoque inválido");
        }

        $dados = [
            'nome' => $_POST['nome'],
            'description' => $_POST['description'],
            'price' => floatval($_POST['price']),
            'stock' => intval($_POST['stock']),
            'image' => $produto['image'],
            'status' => 'pendente',
            'motivo_rejeicao' => null,
            'aprovado_por' => null,
            'data_aprovacao' => null,
            'idProduct' => $produtoId,
            'supplier' => $fornecedorId
        ];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $extensao = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($extensao, $extensoesPermitidas)) {
                throw new Exception("Tipo de arquivo não permitido. Use JPG, PNG ou GIF.");
            }

            $novaImagem = uniqid() . '.' . $extensao;
            $destino = "../static/uploads/" . $novaImagem;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $destino)) {
                if (!empty($produto['image']) && file_exists("../static/uploads/" . $produto['image'])) {
                    unlink("../static/uploads/" . $produto['image']);
                }
                $dados['image'] = $novaImagem;
            } else {
                throw new Exception("Erro ao fazer upload da nova imagem");
            }
        }

        $sql = "UPDATE produtos SET 
            nome = :nome,
            description = :description,
            price = :price,
            stock = :stock,
            image = :image,
            status = :status,
            motivo_rejeicao = :motivo_rejeicao,
            aprovado_por = :aprovado_por,
            data_aprovacao = :data_aprovacao
            WHERE idProduct = :idProduct AND supplier = :supplier";

        $stmt = $pdo->prepare($sql);
        $sucesso = $stmt->execute($dados);

        if ($sucesso) {
            $_SESSION['sucesso'] = "Produto atualizado com sucesso! O status voltou para 'pendente' para revisão.";
            header('Location: /fornecedor/produtos.php');
            exit;
        } else {
            throw new Exception("Erro ao atualizar o produto");
        }

    } catch (Exception $e) {
        $erro = $e->getMessage();
        if (isset($novaImagem) && file_exists("../static/uploads/" . $novaImagem)) {
            unlink("../static/uploads/" . $novaImagem);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Produto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .product-image-preview {
            max-width: 200px;
            max-height: 200px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pendente {
            background-color: #ffc107;
            color: black;
        }
        .status-aprovado {
            background-color: #28a745;
            color: white;
        }
        .status-rejeitado {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <?php include '../static/elements/sidebar-fornecedor.php'; ?>
    <div class="container-fluid">
        <div class="row">
            

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h2>Editar Produto</h2>
                    <a href="/fornecedor/produtos.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>

                <?php if ($erro): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nome do Produto*</label>
                                        <input type="text" class="form-control" name="nome" 
                                               value="<?= htmlspecialchars($produto['nome']) ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Descrição*</label>
                                        <textarea class="form-control" name="description" rows="3" required><?= 
                                            htmlspecialchars($produto['description']) 
                                        ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Preço (R$)*</label>
                                        <input type="number" step="0.01" min="0.01" class="form-control" 
                                               name="price" value="<?= $produto['price'] ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Estoque Disponível</label>
                                        <input type="number" min="0" class="form-control" 
                                               name="stock" value="<?= $produto['stock'] ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Status Atual</label>
                                        <div>
                                            <span class="status-badge status-<?= $produto['status'] ?>">
                                                <?= ucfirst($produto['status']) ?>
                                            </span>
                                            <?php if ($produto['status'] === 'rejeitado' && !empty($produto['motivo_rejeicao'])): ?>
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <strong>Motivo da rejeição:</strong><br>
                                                        <?= htmlspecialchars($produto['motivo_rejeicao']) ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($produto['status'] === 'aprovado'): ?>
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <strong>Data da aprovação:</strong><br>
                                                        <?= date('d/m/Y H:i', strtotime($produto['data_aprovacao'])) ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Imagem Atual</label>
                                        <?php if (!empty($produto['image'])): ?>
                                            <img src="../static/uploads/<?= htmlspecialchars($produto['image']) ?>" 
                                                 class="product-image-preview d-block">
                                        <?php else: ?>
                                            <div class="text-muted">Nenhuma imagem cadastrada</div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Alterar Imagem (opcional)</label>
                                        <input type="file" class="form-control" name="image" accept="image/*">
                                        <small class="text-muted">Deixe em branco para manter a imagem atual</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Salvar Alterações
                                </button>
                                <a href="/fornecedor/produtos.php" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>