<?php
namespace Api\Model;
use PDO;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class ProdutosModel {

    public function __construct(private PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function cadastrarProduto($nome, $descricao, $preco, $quantidade, $categoria) {
        $stmt = $this->pdo->prepare("INSERT INTO produtos (nome, descricao, preco, quantidade, categoria) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$nome, $descricao, $preco, $quantidade, $categoria]);
    }

    public function listarProdutos() {
        $stmt = $this->pdo->query("SELECT * FROM produtos");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function atualizarProduto($id, $nome, $descricao, $preco, $quantidade, $categoria) {
        $stmt = $this->pdo->prepare("UPDATE produtos SET nome = ?, descricao = ?, preco = ?, quantidade = ?, categoria = ? WHERE idProduct = ?");
        return $stmt->execute([$nome, $descricao, $preco, $quantidade, $categoria, $id]);
    }

    public function excluirProduto($id) {
        $stmt = $this->pdo->prepare("DELETE FROM produtos WHERE idProduct = ?");
        return $stmt->execute([$id]);
    }

    public function buscarProdutoPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM produtos WHERE idProductd = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function aprovarProduto($id, $adminId) {
        $stmt = $this->pdo->prepare("UPDATE produtos SET status = 'aprovado', aprovado_por = ?, data_aprovacao = NOW() WHERE idProduct = ? AND status = 'pendente'");
        return $stmt->execute([$adminId, $id]);
    }

    public function listarProdutosPendentes() {
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.nome as fornecedor_nome 
            FROM produtos p
            JOIN fornecedores u ON p.supplier = u.id
            WHERE p.status = 'pendente'
            ORDER BY p.idProduct DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function rejeitarProduto($motivo, $adminId, $idProduto) {

        $stmt = $pdo->prepare("
            UPDATE produtos 
            SET status = 'rejeitado', 
                motivo_rejeicao = ?,
                aprovado_por = ?, 
                data_aprovacao = NOW() 
            WHERE idProduct = ? AND status = 'pendente'
        ");
        
        return $stmt->execute([$motivo, $adminId, $idProduto]);
    }

    public function contarProdutosPendentes() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM produtos WHERE status = 'pendente'");
        return $stmt->fetchColumn();
    }

    public function contarProdutosAprovados() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM produtos WHERE status = 'aprovado'");
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    public function contarFornecedores() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM fornecedores");
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function listarAtividadesRecentes() {
        $stmt = $this->pdo->query("
            SELECT p.description, p.status, p.data_aprovacao, u.nome as admin_nome
            FROM produtos p
            LEFT JOIN usuarios u ON p.aprovado_por = u.id
            WHERE p.status != 'pendente'
            ORDER BY p.data_aprovacao DESC
            LIMIT 5
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}