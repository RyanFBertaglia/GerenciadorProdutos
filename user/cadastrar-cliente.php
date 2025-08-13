<?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    use Api\Controller\AuthController;
    use Api\Model\ClienteModel;
    use Api\Includes\Database;
    use Api\Services\ValidarDados;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $pdo = Database::getInstance();

        ValidarDados::verificaExistencia($_POST['cpf'], $_POST['email'], $pdo);

        $usuarioModel = new ClienteModel($pdo);
        $authController = new AuthController($usuarioModel);
        $authController->cadastro([
            'nome' => $_POST['nome'] ?? '',
            'email' => $_POST['email'] ?? '',
            'senha' => $_POST['senha'] ?? '',
            'cpf' => $_POST['cpf'] ?? '',
            'telefone' => $_POST['phone'] ?? '',
            'endereco' => $_POST['endereco_completo'] ?? '',
            'data_nascimento' => $_POST['birthdate'] ?? ''
        ]);
        $_SESSION['erro'] = "";
        header('Location: /login');
        exit;
        
    }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro Completo</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../static/style/main.css">
    <link rel="stylesheet" href="../static/style/register.css">
    <link rel="icon" href="./static/img/logo-azul.png" type="image/x-icon">
    <style>
        body {
            height: 100%;
        }
        .address-fields {
            display: none;
            margin-top: 10px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .cep-search {
            display: flex;
            gap: 10px;
        }
        .cep-search button {
            padding: 8px 15px;
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        #address-fields label {
            color: black;
        }
    
    </style>
</head>
<body>
    <?php include './static/elements/sidebar-main.php'; ?>

    <div class="container-fluid">
        <form action="/cadastro-usuario" method="POST" onsubmit="return validarFormulario()">
            <img src="./static/img/predio.png" alt="Prédio" class="building-img" style="display: block; margin: 0 auto 0px;">
            <h2 style="text-align: center;">Cadastro Completo</h2>

            <div class="form-group">
                <label for="nome">Nome Completo:</label>
                <input type="text" name="nome" id="nome" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" name="senha" id="senha" required minlength="6">
            </div>
            <div class="form-group">
                <label for="cpf">CPF:</label>
                <input type="text" name="cpf" id="cpf" required placeholder="000.000.000-00">
                <div id="cpf-error" class="error"></div>
            </div>
            <div class="form-group">
                <label for="phone">Telefone:</label>
                <input type="tel" name="phone" id="phone" required placeholder="(00) 00000-0000">
            </div>

            <!-- Campo de Endereço com Busca por CEP -->
            <div class="form-group">
                <label for="cep">CEP:</label>
                <div class="cep-search">
                    <input type="text" name="cep" id="cep" required placeholder="00000-000">
                    <button type="button" onclick="buscarCEP()">Buscar</button>
                </div>
                <div id="cep-error" class="error"></div>
            </div>

            <div id="address-fields" class="address-fields">
                <div class="form-group">
                    <label for="logradouro">Logradouro:</label>
                    <input type="text" name="logradouro" id="logradouro" required>
                </div>
                <div class="form-group">
                    <label for="numero">Número:</label>
                    <input type="text" name="numero" id="numero" required>
                </div>
                <div class="form-group">
                    <label for="complemento">Complemento:</label>
                    <input type="text" name="complemento" id="complemento">
                </div>
                <div class="form-group">
                    <label for="bairro">Bairro:</label>
                    <input type="text" name="bairro" id="bairro" required>
                </div>
                <div class="form-group">
                    <label for="cidade">Cidade:</label>
                    <input type="text" name="cidade" id="cidade" required>
                </div>
                <div class="form-group">
                    <label for="uf">Estado:</label>
                    <input type="text" name="uf" id="uf" required maxlength="2">
                </div>
            </div>

            <input type="hidden" name="endereco_completo" id="endereco_completo">

            <div class="form-group">
                <label for="birthdate">Data de Nascimento:</label>
                <input type="date" name="birthdate" id="birthdate" required>
            </div>

            <div class="btn-container">
                <button type="submit" class="btn">Cadastrar</button>
                <a href="/login" class="btn">Já possui conta? Login</a>
            </div>
        </form>
    </div>

    <script>
        // Função para buscar CEP
        function buscarCEP() {
            const cep = document.getElementById('cep').value.replace(/\D/g, '');
            const cepError = document.getElementById('cep-error');
            
            if (cep.length !== 8) {
                cepError.textContent = 'CEP deve ter 8 dígitos';
                return;
            }
            
            cepError.textContent = '';
            
            fetch(`./includes/buscar-cep.php?cep=${cep}`)
                .then(response => response.json())
                .then(data => {
                    if (data.erro) {
                        cepError.textContent = 'CEP não encontrado';
                        document.getElementById('address-fields').style.display = 'none';
                    } else {
                        document.getElementById('logradouro').value = data.logradouro;
                        document.getElementById('bairro').value = data.bairro;
                        document.getElementById('cidade').value = data.localidade;
                        document.getElementById('uf').value = data.uf;
                        document.getElementById('address-fields').style.display = 'block';
                    }
                })
                .catch(error => {
                    cepError.textContent = 'Erro ao buscar CEP';
                    console.error('Error:', error);
                });
        }

        function validarFormulario() {
    const addressFieldsVisible = document.getElementById('address-fields').style.display === 'block';
    
    if (addressFieldsVisible) {
        const numero = document.getElementById('numero').value;
        if (!numero) {
            alert('Por favor, informe o número do endereço');
            return false;
        }

        const enderecoCompleto = `${document.getElementById('cep').value}, ` +
                               `${document.getElementById('logradouro').value}, ` +
                               `${numero}, ` +
                               `${document.getElementById('bairro').value}, ` +
                               `${document.getElementById('cidade').value}-${document.getElementById('uf').value}`;

        document.getElementById('endereco_completo').value = enderecoCompleto;
    }
    
    return true;
}
    </script>
    <script src="./static/js/validation.js"></script>
</body>
</html>