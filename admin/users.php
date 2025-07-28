<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once './includes/db.php';
    require_once './includes/auth.php';

    $produtosPendentes = $_SESSION['produtosPendentes'];

    $currentPage = $_GET['page'] ?? 1;
    $limite = 6;
    $offset = ($currentPage - 1) * $limite;

    use Api\Controller\AdminController;
    use Api\Model\ProdutosModel;
    use Api\Includes\Database;
    use Api\Model\AdminModel;

    $pdo = Database::getInstance();
    $produtosModel = new ProdutosModel($pdo);
    $adminModel = new AdminModel($pdo);
    $adminController = new AdminController($produtosModel, $adminModel);

    $usuarios = $adminController->getAllUsers($limite, $offset);
    $totalUsuarios = $adminController->getTotalUsers();
    $totalPages = ceil($totalUsuarios / $limite);    
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $adminController->addSaldo($_POST['userId'], $_POST['amount']);
        header('Location: /admin/usuarios?page=' . $currentPage);
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Info</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: grid;
            grid-template-columns: 0px 1fr;
            grid-template-rows: auto 1fr;
            grid-template-areas: "aside main";
            min-height: 100vh;
        }
        main {
            grid-area: main;
            padding: 20px;
        }
        
    button {
        background-color: #4a90e2;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 4px;
        cursor: pointer;
    }

    a{
        text-decoration: none;
        color: #4a90e2;
        padding: 12px 15px;
        border-radius: 4px;
        transition: background-color 0.3s;
        background-color: #f1f1f1;
    }

    </style>
    <link rel="stylesheet" href="../static/style/admin/main.css">
    <link rel="stylesheet" href="../static/style/table.css">

</head>

<body>
    <?php include './static/elements/sidebar-admin.php'; ?>
    <main>
        <table>
            <thead>
                <tr>                    
                    <td>ID</td>
                    <td>Nome</td>
                    <td>Email</td>
                    <td>Gasto</td>
                    <td>Saldo</td>
                    <td>Add Saldo</td>
                </tr>
            </thead>
            <tbody>
                <?php foreach($usuarios as $usuario): ?>
                    <tr>
                    <td><?= htmlspecialchars($usuario['id']) ?></td>
                    <td><?= htmlspecialchars($usuario['nome']) ?></td>
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                    <td>R$ <?= number_format($usuario['gasto'], 2, ',', '.') ?></td>
                    <td>R$ <?= number_format($usuario['saldo'], 2, ',', '.') ?></td>
                    <td>
                    <button id="btn-adicionar-<?= $usuario['id'] ?>" onclick="mostrarFormulario(<?= $usuario['id'] ?>)">Adicionar</button>

                    <form method="POST" action="" id="form-<?= $usuario['id'] ?>" style="display: none; margin-top: 5px;">
                        <input type="hidden" name="userId" value="<?= $usuario['id'] ?>">
                        <input type="number" name="amount" step="0.01" required placeholder="Valor" style="width: 80px;">
                        <button type="submit">Enviar</button>
                        <button type="button" onclick="cancelarFormulario(<?= $usuario['id'] ?>)">Cancelar</button>
                    </form>
                </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <nav>
            <?php if ($currentPage > 1): ?>
                <a href="?page=<?= $currentPage - 1 ?>">Anterior</a>
            <?php endif; ?>
    
        Página <?= $currentPage ?> de <?= $totalPages ?>
    
        <?php if ($currentPage < $totalPages): ?>
            <a href="?page=<?= $currentPage + 1 ?>">Próxima</a>
        <?php endif; ?>
    </nav>

    </main>
    <script>
        function mostrarFormulario(id) {
            document.getElementById(`btn-adicionar-${id}`).style.display = 'none';
            document.getElementById(`form-${id}`).style.display = 'inline-block';
        }

        function cancelarFormulario(id) {
            document.getElementById(`form-${id}`).style.display = 'none';
            document.getElementById(`btn-adicionar-${id}`).style.display = 'inline-block';
        }
    </script>
</body>
</html>