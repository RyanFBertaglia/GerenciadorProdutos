<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../model/OrderModel.php';
require_once __DIR__ . '/../services/DevolucaoService.php';

class FornecedorController {
    private $orderModel;
    private $devolucaoService;
    
    public function __construct() {
        global $pdo;
        $this->orderModel = new OrderModel($pdo);
        $this->devolucaoService = new DevolucaoService($pdo);
    }
    
    public function dashboard() {
        protectFornecedorPage();
        
        $fornecedor_id = $_SESSION['usuario']['id'];
        $sucesso = $_SESSION['sucesso'] ?? null;
        $erro = $_SESSION['erro'] ?? null;
        unset($_SESSION['sucesso'], $_SESSION['erro']);
        
        return [
            'pedidos_pendentes' => $this->orderModel->getPedidosPendentes($fornecedor_id),
            'historico_devolucoes' => $this->orderModel->getHistoricoDevolucoes($fornecedor_id),
            'sucesso' => $sucesso,
            'erro' => $erro
        ];
    }
    
    public function processarDevolucao() {
        protectFornecedorPage();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fornecedor_id = $_SESSION['usuario']['id'];
            $pedido_id = (int)$_POST['pedido_id'];
            $acao = isset($_POST['aprovar_devolucao']) ? 'aprovar' : 'rejeitar';
            $motivoRecusa = trim($_POST['motivoRecusa'] ?? '');
            
            try {
                $resultado = $this->devolucaoService->processar(
                    $fornecedor_id,
                    $pedido_id,
                    $acao,
                    $motivoRecusa
                );
                
                $_SESSION['sucesso'] = $resultado['mensagem'];
            } catch (Exception $e) {
                $_SESSION['erro'] = "Erro: " . $e->getMessage();
            }
            
            header('Location: /fornecedor/dashboard');
            exit;
        }
    }
}