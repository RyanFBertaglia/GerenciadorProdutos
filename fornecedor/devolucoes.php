<?php

use Api\Controller\FornecedorController;
use Api\Model\FornecedorModel;
use Api\Includes\Database;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once './includes/auth.php';
protectFornecedorPage();

$fornecedor_id = $_SESSION['fornecedor']['id'];
$sucesso = $_SESSION['sucesso'] ?? null;
$erro = $_SESSION['erro'] ?? null;
unset($_SESSION['sucesso'], $_SESSION['erro']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['aprovar_devolucao']) || isset($_POST['rejeitar_devolucao'])) {

        $pedido_id = (int)$_POST['pedido_id'];
        $acao = isset($_POST['aprovar_devolucao']) ? 'aprovar' : 'rejeitar';
        $motivoRecusa = trim($_POST['motivoRecusa'] ?? '');


        try {
            $pdo = Database::getInstance();
            $pdo->beginTransaction();
            $fornecedorModel = new FornecedorModel($pdo);
            $fornecedorController = new FornecedorController($fornecedorModel);

            $motivo = !empty($motivoRecusa) ? $motivoRecusa : null;
            $pedido = $fornecedorController->devolucao($pedido_id, $acao, $motivo);

            $pdo->commit();

            $msg = ($acao === 'aprovar')
                ? "Devolução aprovada! R$ " . number_format($pedido['total'], 2, ',', '.') . " reembolsados para o cliente."
                : "Devolução rejeitada. O motivo foi informado ao cliente.";
            $_SESSION['sucesso'] = $msg;

        } catch (Exception $e) {
            $pdo->rollback();
            $_SESSION['erro'] = $e->getMessage();
        }
        header('Location: /fornecedor/dashboard');
        exit;
    }
}
$pdo = Database::getInstance();
$fornecedorModel = new FornecedorModel($pdo);

$pedidos_pendentes = $fornecedorModel->getAllPendentes($fornecedor_id);
$historico_devolucoes = $fornecedorModel->getHistoricoDevolucoes($fornecedor_id);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Devoluções</title>
    <style>
        body {
            grid-template-areas: "sidebar main";
        }

    </style>
    <link rel="stylesheet" href="../static/style/admin/main.css">
    <link rel="stylesheet" href="../static/style/fornecedorDevolucao.css">
</head>

<body>
    <?php include './static/elements/sidebar-fornecedor.php'; ?>


    <main>
        <br><br>
        <h1>Gerenciar Solicitações de Devolução</h1>

        <?php if ($sucesso): ?>
            <div class="alert-success"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="alert-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <div class="container">
            <div class="section">
                <h2>Devoluções Pendentes</h2>

                <?php if (empty($pedidos_pendentes)): ?>
                    <p>Não há devoluções pendentes no momento.</p>
                <?php else: ?>
                    <?php foreach ($pedidos_pendentes as $pedido): ?>
                        <div class="pedido">
                            <div class="info-cliente">
                                <p><strong>Cliente:</strong> <?= htmlspecialchars($pedido['nome_cliente']) ?></p>
                                <p><strong>Pedido #<?= htmlspecialchars($pedido['id']) ?></strong></p>
                                <p><strong>Data da solicitação:</strong> <?= date('d/m/Y H:i', strtotime($pedido['dataDevolucao'])) ?></p>
                                <span class="status pendente">Pendente</span>
                            </div>

                            <div class="motivo-box">
                                <p><strong>Motivo da devolução:</strong></p>
                                <p><?= htmlspecialchars($pedido['motivoDevolucao']) ?></p>
                            </div>

                            <div class="itens-lista">
                                <strong>Itens do Pedido:</strong>
                                <ul>
                                    <?php
                                    $itens = $fornecedorModel->getItensPedido($pedido['id'], $fornecedor_id);
                                    foreach ($itens as $item):
                                    ?>
                                        <li>
                                            <?= htmlspecialchars($item['description']) ?> -
                                            Quantidade: <?= $item['quantity'] ?> -
                                            Valor unitário: R$ <?= number_format($item['value'], 2, ',', '.') ?> -
                                            <strong>Subtotal: R$ <?= number_format($item['value'] * $item['quantity'], 2, ',', '.') ?></strong>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <p><strong>Total do pedido:</strong> R$ <?= number_format($pedido['total'], 2, ',', '.') ?></p>

                            <form method="post">
                                <input type="hidden" name="pedido_id" value="<?= $pedido['id'] ?>">

                                <label for="motivoRecusa"><strong>Motivo da Resposta:</strong> (Obrigatório para rejeição)</label>
                                <textarea name="motivoRecusa" rows="3" placeholder="Digite o motivo da aprovação/rejeição..."></textarea>

                                <button type="submit" name="aprovar_devolucao" class="btn-aprovar" onclick="return confirm('Tem certeza que deseja APROVAR esta devolução? O reembolso será processado.')">
                                    Aprovar Devolução
                                </button>

                                <button type="submit" name="rejeitar_devolucao" class="btn-rejeitar" onclick="return confirm('Tem certeza que deseja REJEITAR esta devolução?')">
                                    Rejeitar Devolução
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="section">
                <h2>Histórico de Devoluções</h2>

                <?php if (empty($historico_devolucoes)): ?>
                    <p>Nenhuma devolução processada até o momento.</p>
                <?php else: ?>
                    <?php foreach ($historico_devolucoes as $devolucao): ?>
                        <div class="pedido">
                            <div class="info-cliente">
                                <p><strong>Cliente:</strong> <?= htmlspecialchars($devolucao['nome_cliente']) ?></p>
                                <p><strong>Pedido #<?= htmlspecialchars($devolucao['id']) ?></strong></p>
                                <p><strong>Status:</strong>
                                    <span class="status <?= strtolower($devolucao['status']) ?>">
                                        <?= htmlspecialchars($devolucao['status']) ?>
                                    </span>
                                </p>
                                <p><strong>Data da resposta:</strong> <?= date('d/m/Y H:i', strtotime($devolucao['dataConfirmacao'])) ?></p>
                            </div>

                            <div class="motivo-box">
                                <p><strong>Motivo da devolução:</strong></p>
                                <p><?= htmlspecialchars($devolucao['motivoDevolucao']) ?></p>
                            </div>

                            <?php if (!empty($devolucao['motivoRecusa'])): ?>
                                <div class="motivo-box" style="background-color: #f8d7da;">
                                    <p><strong>Motivo da rejeição:</strong></p>
                                    <p><?= htmlspecialchars($devolucao['motivoRecusa']) ?></p>
                                </div>
                            <?php endif; ?>

                            <div class="itens-lista">
                                <strong>Itens do Pedido:</strong>
                                <ul>
                                    <?php
                                    $itens = $fornecedorModel->getItensPedido($devolucao['id'], $fornecedor_id);
                                    foreach ($itens as $item):
                                    ?>
                                        <li>
                                            <?= htmlspecialchars($item['description']) ?> -
                                            Quantidade: <?= $item['quantity'] ?> -
                                            Valor unitário: R$ <?= number_format($item['value'], 2, ',', '.') ?> -
                                            <strong>Subtotal: R$ <?= number_format($item['value'] * $item['quantity'], 2, ',', '.') ?></strong>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <p><strong>Total do pedido:</strong> R$ <?= number_format($devolucao['total'], 2, ',', '.') ?></p>

                            <?php if ($devolucao['status'] === 'Devolvido'): ?>
                                <p><strong>Valor reembolsado:</strong> R$ <?= number_format($devolucao['total'], 2, ',', '.') ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>

</html>