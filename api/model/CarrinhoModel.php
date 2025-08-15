<?php

namespace Api\Model;
use PDO;
use PDOException;

class CarrinhoModel {

    public function __construct(private PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getItensCarrinho($usuario_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT c.id, c.quantidade, p.idProduct, p.description, p.price, p.image, p.stock 
                FROM carrinho c
                JOIN produtos p ON c.produto_id = p.idProduct
                WHERE c.usuario_id = ?
            ");
            $stmt->execute([$usuario_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $_SESSION['erro'] = $e->getMessage();
            header('Location: /erro');
        }
    }

    public function removerItem($id, $usuario_id) {
        try {
            // Verifica se o item pertence ao usuário
            $stmt = $this->pdo->prepare("DELETE FROM carrinho WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$id, $usuario_id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Database error in removerItem: " . $e->getMessage());
            return false;
        }
    }


    public function calcularTotal($itens) {
        return array_reduce($itens, fn($soma, $item) => $soma + ($item['price'] * $item['quantidade']), 0);
    }

    public function limparCarrinho($usuario_id) {
        $stmt = $this->pdo->prepare("DELETE FROM carrinho WHERE usuario_id = ?");
        return $stmt->execute([$usuario_id]);
    }

    public function verificarSaldoUsuario($usuario_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM BankAccount WHERE idUser = ? FOR UPDATE");
        $stmt->execute([$usuario_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizarSaldo($idAccount, $novoSaldo) {
        $stmt = $this->pdo->prepare("UPDATE BankAccount SET balance = ? WHERE idAccount = ?");
        return $stmt->execute([$novoSaldo, $idAccount]);
    }

    public function criarPedido($usuario_id, $subtotal, $idFornecedor) {
        $stmt = $this->pdo->prepare("INSERT INTO Orders (idUser, dataPedido, status, total, idFornecedor) VALUES (?, NOW(), 'Pendente', ?, ?)");
        $stmt->execute([$usuario_id, $subtotal, $idFornecedor]);
        return $this->pdo->lastInsertId();
    }

    public function adicionarItemPedido($orderId, $idProduct, $quantidade, $price) {
        $stmt = $this->pdo->prepare("INSERT INTO OrderItems (idOrder, idProduct, quantity, value) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$orderId, $idProduct, $quantidade, $price]);
    }

    public function atualizarVendidos($idProduct, $quantidade) {
        $stmt = $this->pdo->prepare("UPDATE produtos SET vendidos = vendidos + ? WHERE idProduct = ?");
        return $stmt->execute([$quantidade, $idProduct]);
    }

    public function reduzirEstoque($idProduct, $quantidade) {
        $stmt = $this->pdo->prepare("UPDATE produtos SET stock = stock - ? WHERE idProduct = ?");
        return $stmt->execute([$quantidade, $idProduct]);
    }

    public function verificarEstoque($idProduct) {
        $stmt = $this->pdo->prepare("SELECT stock FROM produtos WHERE idProduct = ?");
        $stmt->execute([$idProduct]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizarQuantidade($id, $quantidade) {
        $stmt = $this->pdo->prepare("
            UPDATE carrinho 
            SET quantidade = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$quantidade, $id]);
    }

    public function criarPagamento($usuario_id, $orderId, $total) {
        $stmt = $this->pdo->prepare("INSERT INTO Payments (idUser, idOrder, status, datePayment, total) VALUES (?, ?, 'Pago', NOW(), ?)");
        return $stmt->execute([$usuario_id, $orderId, $total]);
    }

    public function addProduct($id, $quantidade, $user) {
        if ($this->userPossuiProduto($id, $user)) {
            return $this->aumentarQuantidade($id, $quantidade, $user);
        } else {
            return $this->inserirProduto($id, $quantidade, $user);
        }
    }
    
    public function inserirProduto($id, $quantidade, $user) {
        $stmt = $this->pdo->prepare("INSERT INTO carrinho (usuario_id, produto_id, quantidade) VALUES (?, ?, ?)");
        return $stmt->execute([$user, $id, $quantidade]);
    }
    
    public function aumentarQuantidade($id, $quantidade, $user) {
        $stmt = $this->pdo->prepare("UPDATE carrinho SET quantidade = quantidade + ? WHERE usuario_id = ? AND produto_id = ?");
        return $stmt->execute([$quantidade, $user, $id]);
    }
    
    public function userPossuiProduto($id, $user) {
        $stmt = $this->pdo->prepare("SELECT 1 FROM carrinho WHERE usuario_id = ? AND produto_id = ?");
        $stmt->execute([$user, $id]);
        return $stmt->fetchColumn() !== false;
    }
    
}
?>