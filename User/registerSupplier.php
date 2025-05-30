<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Fornecedor</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../static/style/register.css">
</head>
<body>
<aside>
        <div class="menu-icon">
            <i class="fas fa-bars"></i>
        </div>
        <div class="nav-item">
            <i class="fas fa-home"></i>
            <span>Início</span>
        </div>
        <div class="nav-item">
            <i class="fas fa-folder"></i>
            <span>Carrinho</span>
        </div>
        <div class="nav-item">
            <i class="fas fa-users"></i>
            <span>Vender</span>
        </div>
        <div class="nav-item">
            <i class="fas fa-user"></i>
            <span>Minha conta</span>
        </div>
        <div class="nav-item">
            <i class="fas fa-question-circle"></i>
            <span>Ajuda</span>
        </div>
    </aside>
    <main>
    <img src="../static/img/predio.png" alt="Prédio" class="building-img" />
    <h1>Cadastro de Fornecedor</h1>
    <form action="./register/saveSupplier.php" method="POST" onsubmit="return validarFormulario()">
        <div class="form-group">
            <label for="nome">Nome Completo:</label>
            <input type="text" name="nome" id="nome" required>
        </div>

        <div class="form-group">
            <label for="email">E-mail:</label>
            <input type="email" name="email" id="email" required>
        </div>

        <div class="form-group">
            <label for="telefone">Telefone:</label>
            <input type="text" name="telefone" id="telefone" required placeholder="(00) 00000-0000">
        </div>

        <div class="form-group">
            <label for="cpf">CPF:</label>
            <input type="text" name="cpf" id="cpf" required placeholder="000.000.000-00">
            <div id="cpf-error" class="error"></div>
        </div>

        <div class="form-group">
            <label for="senha">Senha (mínimo 6 caracteres):</label>
            <input type="password" name="senha" id="senha" required minlength="6">
        </div>

        <div class="form-group">
            <label for="confirmar_senha">Confirmar Senha:</label>
            <input type="password" name="confirmar_senha" id="confirmar_senha" required>
            <div id="senha-error" class="error"></div>
        </div>

        <button class="button-class" type="submit">Cadastrar</button>
    </form>

    <script src="../static/js/validation.js"></script>
    </main>
    
</body>
</html>