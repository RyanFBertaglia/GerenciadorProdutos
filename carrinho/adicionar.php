<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once './includes/db.php';
require_once './includes/auth.php';

protectPage();

// Dados do POST
$usuario_id = $_SESSION['usuario']['id'];
$produto_id = $_POST['produto_id'] ?? null;
$quantidade = $_POST['quantidade'] ?? 1;



// Verificar estoque
try {
    $stmt = $pdo->prepare("SELECT stock FROM produtos WHERE idProduct = ?");
    $stmt->execute([$produto_id]);
    $produto = $stmt->fetch();

    if (!$produto) {
        $_SESSION['erro'] = "Produto não encontrado";
        header("Location: /erro");
        exit;
    }

    if ($produto['stock'] < $quantidade) {
        $_SESSION['erro'] = "Quantidade indisponível em estoque";
        
        // Armazena os dados do POST na sessão para recuperar no redirecionamento
        $_SESSION['form_data'] = [
            'produto_id' => $produto_id,
            'quantidade' => $quantidade
        ];
        
        header("Location: /produto/detalhes?id=$produto_id");
        exit;
    }

    // Verificar se o produto já está no carrinho
    $stmt = $pdo->prepare("SELECT * FROM carrinho WHERE usuario_id = ? AND produto_id = ?");
    $stmt->execute([$usuario_id, $produto_id]);
    $item = $stmt->fetch();

    if ($item) {
        $novaQuantidade = $item['quantidade'] + $quantidade;
        if ($produto['stock'] < $novaQuantidade) {
            $_SESSION['erro'] = "Quantidade total no carrinho excede o estoque disponível";
            header("Location: /erro");
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE carrinho SET quantidade = quantidade + ? WHERE id = ?");
        $stmt->execute([$quantidade, $item['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO carrinho (usuario_id, produto_id, quantidade) VALUES (?, ?, ?)");
        $stmt->execute([$usuario_id, $produto_id, $quantidade]);
    }

    // Mensagem de sucesso
    $_SESSION['sucesso'] = "Produto adicionado ao carrinho!";
    header('Location: /user/carrinho'); // Rota do router para carrinho
    exit;

} catch (PDOException $e) {
    $_SESSION['erro'] = "Erro no banco de dados: " . $e->getMessage();
    header("Location: /erro");
    exit;
}