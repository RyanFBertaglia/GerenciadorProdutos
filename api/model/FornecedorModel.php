<?php

namespace Api\Model;

use Api\Services\ValidarDados;

use PDO;
use Exception;

class FornecedorModel implements UserInterface {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function login($email, $senha) {
        ValidarDados::validarEmail($email);
        ValidarDados::validarSenha($senha);

        $stmt = $this->pdo->prepare("SELECT * FROM fornecedores WHERE email = ?");
        $stmt->execute([$email]);
        $fornecedor = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fornecedor && password_verify($senha, $fornecedor['senha'])) {
            session_start();
            $_SESSION['fornecedor'] = $fornecedor;
            return true;
        }
        $_SESSION['erro'] = "Email ou senha inválidos.";
        header("Location: /erro");
        exit;
    }

    public function cadastro(array $userData) {

        ValidarDados::validarNome($userData['nome']);
        ValidarDados::validarEmail($userData['email']);
        ValidarDados::validarSenha($userData['senha']);
        ValidarDados::validarCpf($userData['cpf']);
        ValidarDados::validarTelefone($userData['telefone'] ?? '');

        $stmt = $this->pdo->prepare("INSERT INTO fornecedores (nome, email, senha, cpf, telefone) VALUES (?, ?, ?, ?, ?)");

        $userData['senha'] = password_hash($userData['senha'], PASSWORD_DEFAULT);

        if (!$stmt->execute([$userData['nome'], $userData['email'], $userData['senha'], $userData['cpf'], $userData['telefone']])) {
            throw new \Exception("Erro ao cadastrar fornecedor: " . implode(", ", $stmt->errorInfo()));
        }

        $this->createFornecedorBankAccount($this->pdo->lastInsertId(), [
            'tipo' => 'fornecedor',
            'status' => 'A'
        ]);

        return true;
    }

    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM fornecedores WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createFornecedorBankAccount($fornecedorId, $bankAccountData) {
        $stmt = $this->pdo->prepare("INSERT INTO BankAccount (idFornecedor, tipo, status) VALUES (?, ?, ?)");
        return $stmt->execute([$fornecedorId, 'fornecedor', 'A']);
    }

    public function updateUser($id, array $userData) {
        $fields = [];
        $values = [];

        foreach ($userData as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        $values[] = $id;
        $sql = "UPDATE fornecedores SET " . implode(", ", $fields) . " WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    public function deleteUser($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM fornecedores WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAllFornecedores()
    {
        $stmt = $this->pdo->query("SELECT * FROM fornecedores");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFornecedorByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM fornecedores WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listarProdutos($fornecedorId)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM produtos 
            WHERE supplier = ?
            ORDER BY 
                CASE status 
                    WHEN 'pendente' THEN 1
                    WHEN 'rejeitado' THEN 2
                    WHEN 'aprovado' THEN 3
                END,
                idProduct DESC
        ");
        $stmt->execute([$fornecedorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarProdutosAprovados($fornecedorId)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM produtos WHERE supplier = ? AND status = 'aprovado'");
        $stmt->execute([$fornecedorId]);
        return $stmt->fetchColumn();
    }

    public function obterVendas($fornecedorId)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                SUM(vendidos) as total_vendidos,
                SUM(vendidos * price) as total_vendas
            FROM produtos 
            WHERE supplier = ? AND status = 'aprovado'
        ");
        $stmt->execute([$fornecedorId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function excluirProduto($produtoId, $fornecedorId)
    {
        $stmt = $this->pdo->prepare("SELECT image FROM produtos WHERE idProduct = ? AND supplier = ?");
        $stmt->execute([$produtoId, $fornecedorId]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($produto) {
            if (!empty($produto['image']) && file_exists("../assets/uploads/" . $produto['image'])) {
                unlink("../assets/uploads/" . $produto['image']);
            }

            $stmt = $this->pdo->prepare("DELETE FROM produtos WHERE idProduct = ?");
            return $stmt->execute([$produtoId]);
        }

        return false;
    }

    public function getPendenteById($produtoId, $fornecedorId)
    {
        $stmtVerifica = $this->pdo->prepare("SELECT o.*, u.id as id_cliente FROM Orders o 
        JOIN OrderItems oi ON o.id = oi.idOrder
        JOIN produtos p ON oi.idProduct = p.idProduct
        JOIN usuarios u ON o.idUser = u.id
        WHERE o.id = ? AND p.supplier = ? AND o.status = 'Devolucao_Pendente' FOR UPDATE");
        $stmtVerifica->execute([$produtoId, $fornecedorId]);
        $pedido = $stmtVerifica->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) {
            throw new Exception("Pedido não encontrado, não pertence a você ou não está pendente de devolução.");
        }
        return $pedido;
    }

    public function getAllPendentes($fornecedorId)
    {
        $stmt = $this->pdo->prepare("
            SELECT o.*, u.nome AS nome_cliente 
            FROM Orders o
            JOIN OrderItems oi ON oi.idOrder = o.id
            JOIN produtos p ON oi.idProduct = p.idProduct
            JOIN usuarios u ON o.idUser = u.id
            WHERE p.supplier = ? AND o.status = 'Devolucao_Pendente'
            GROUP BY o.id
            ORDER BY o.dataDevolucao ASC
        ");
        $stmt->execute([$fornecedorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHistoricoDevolucoes($idFornecedor) {
        $stmt = $this->pdo->prepare("
        SELECT o.*, u.nome AS nome_cliente
        FROM Orders o
        JOIN OrderItems oi ON oi.idOrder = o.id
        JOIN produtos p ON oi.idProduct = p.idProduct
        JOIN usuarios u ON o.idUser = u.id
        WHERE p.supplier = ?
          AND o.status IN ('Devolvido', 'Devolucao_Rejeitada') 
          AND o.dataDevolucao IS NOT NULL
        GROUP BY o.id
        ORDER BY COALESCE(o.dataAprovacaoDevolucao, o.dataRejeicaoDevolucao) DESC
    ");
        $stmt->execute([$idFornecedor]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function reembolsar($idCliente, $fornecedorId, $valor)
    {

        $stmtContaFornecedor = $this->pdo->prepare("SELECT * FROM BankAccount 
                                                WHERE idFornecedor = ? AND tipo = 'fornecedor' AND status = 'A'
                                                LIMIT 1 FOR UPDATE");
        $stmtContaFornecedor->execute([$fornecedorId]);
        $contaFornecedor = $stmtContaFornecedor->fetch(PDO::FETCH_ASSOC);

        if (!$contaFornecedor) {
            throw new Exception("Sua conta bancária não foi encontrada ou está inativa.");
        }

        $stmtContaCliente = $this->pdo->prepare("SELECT * FROM BankAccount 
                                                 WHERE idUser = ? AND tipo = 'usuario' AND status = 'A'
                                                 LIMIT 1 FOR UPDATE");
        $stmtContaCliente->execute([$idCliente]);
        $contaCliente = $stmtContaCliente->fetch(PDO::FETCH_ASSOC);

        if (!$contaCliente) {
            throw new Exception("Conta bancária do cliente não encontrada ou inativa.");
        }


        $stmtDebito = $this->pdo->prepare("UPDATE BankAccount 
                                            SET balance = balance - ? 
                                            WHERE idAccount = ?");
        $stmtDebito->execute([$valor, $contaFornecedor['idAccount']]);

        $stmtCredito = $this->pdo->prepare("UPDATE BankAccount 
                                            SET balance = balance + ? 
                                            WHERE idAccount = ?");
        return $stmtCredito->execute([$valor, $contaCliente['idAccount']]);
    }

    public function atualizarStatusPedido($pedidoId, $acao, $motivoRecusa = null)
    {
        if ($acao === 'Devolvido') {
            $sql = "UPDATE Orders SET 
                    status = 'Devolvido', 
                    dataAprovacaoDevolucao = NOW() 
                    WHERE id = ? AND status = 'Devolucao_Pendente'";
            $params = [$pedidoId];
            
        } elseif ($acao === 'Devolucao_Rejeitada') {
            if (empty(trim($motivoRecusa))) {
                throw new Exception("Motivo de rejeição é obrigatório.");
            }
            
            $sql = "UPDATE Orders SET 
                    status = 'Devolucao_Rejeitada', 
                    dataRejeicaoDevolucao = NOW(),
                    motivoRecusa = ?
                    WHERE id = ? AND status = 'Devolucao_Pendente'";
            $params = [$motivoRecusa, $pedidoId];
            
        } else {
            throw new Exception("Ação inválida. Use 'aprovar' ou 'rejeitar'.");
        }

        $stmt = $this->pdo->prepare($sql);
        $resultado = $stmt->execute($params);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception("Nenhum pedido foi atualizado. Verifique se o pedido existe e está com status 'Devolucao_Pendente'.");
        }
        
        return $resultado;
    }

    public function getItensPedido($idPedido, $idFornecedor) {
        $stmt = $this->pdo->prepare("
            SELECT oi.quantity, oi.value, p.description 
            FROM OrderItems oi 
            JOIN produtos p ON oi.idProduct = p.idProduct
            WHERE oi.idOrder = ? AND p.supplier = ?
        ");
        $stmt->execute([$idPedido, $idFornecedor]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPedidoById($idPedido, $idFornecedor) {
        $stmt = $this->pdo->prepare("
            SELECT o.*, u.nome 
            FROM Orders o 
            JOIN usuarios u ON o.idUser = u.id
            WHERE o.id = ? AND o.   idFornecedor = ?
        ");
        $stmt->execute([$idPedido, $idFornecedor]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
