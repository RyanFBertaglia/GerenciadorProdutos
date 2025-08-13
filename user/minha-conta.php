<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    use Api\Controller\AuthController;
    use Api\Model\ClienteModel;
    use Api\Includes\Database;
    use Api\Services\ValidarDados;

    function formatarTelefone($telefone) {
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        if (strlen($telefone) === 11) {
            return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7);
        } elseif (strlen($telefone) === 10) {
            return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6);
        }
        return $telefone;
    }

    $pdo = Database::getInstance();
    $usuarioModel = new ClienteModel($pdo);
    $userData = $usuarioModel->getUserById($_SESSION['usuario']['id']);

    // Formatar data para o input date
    $dataNascimento = '';
    if (!empty($userData['data_nascimento'])) {
        $dataNascimento = date('Y-m-d', strtotime($userData['data_nascimento']));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $authController = new AuthController($usuarioModel);

        if ($_POST['email'] !== $userData['email']) {
            ValidarDados::verificaExistenciaEmail($_POST['email'], $_SESSION['usuario']['id'], $pdo);
        }

        $updateData = [
            'id' => $_SESSION['usuario']['id'],
            'email' => $_POST['email'] ?? $userData['email'],
            'telefone' => $_POST['phone'] ?? $userData['telefone'],
            'endereco' => $_POST['endereco_completo'] ?? $userData['endereco']
        ];

        if (!empty($_POST['senha'])) {
            $updateData['senha'] = $_POST['senha'];
        }

        $authController->updateUser($updateData);
        $_SESSION['success_message'] = "Dados atualizados com sucesso!";
        header('Location: /user/minha-conta');
        exit;
    }

    $preFilledCep = '';
    $preFilledLogradouro = '';
    $preFilledNumero = '';
    $preFilledComplemento = '';
    $preFilledBairro = '';
    $preFilledCidade = '';
    $preFilledUf = '';
    
    if (!empty($userData['endereco'])) {
        $endereco = $userData['endereco'];
        $parts = explode(', ', $endereco);
        
        if (count($parts) >= 2 && preg_match('/^\d{5}-?\d{3}$/', str_replace('-', '', $parts[0]))) {
            $preFilledCep = $parts[0];
            $preFilledLogradouro = $parts[1];
            $preFilledNumero = $parts[2] ?? '';
            $preFilledBairro = $parts[3] ?? '';
            
            if (count($parts) >= 6) {
                $preFilledComplemento = $parts[4];
                $cidadeUf = explode('-', $parts[5]);
            } else {
                $cidadeUf = explode('-', $parts[4]);
            }
            
            $preFilledCidade = $cidadeUf[0] ?? '';
            $preFilledUf = $cidadeUf[1] ?? '';
        }
    }
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Minha Conta</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/static/style/main.css">
    <link rel="icon" href="./static/img/logo-azul.png" type="image/x-icon">
    <link rel="stylesheet" href="/static/style/register.css">
    <style>
        body {
            height:195%;
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
        .success-message {
            color: green;
            text-align: center;
            margin-bottom: 20px;
        }
        .readonly-field {
            background-color: #f0f0f0;
            cursor: not-allowed;
        }
        .error {
            color: red;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <?php include './static/elements/sidebar-main.php'; ?>
    <div class="container-fluid">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>

        <form action="/user/minha-conta" method="POST" onsubmit="return validarFormulario()">
            <h2 style="text-align: center;">Minha Conta</h2>

            <div class="form-group">
                <label for="nome">Nome Completo:</label>
                <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($userData['nome']); ?>" readonly class="readonly-field">
            </div>

            <div class="form-group">
                <label for="cpf">CPF:</label>
                <input type="text" name="cpf" id="cpf" value="<?php echo htmlspecialchars($userData['cpf']); ?>" readonly class="readonly-field">
            </div>

            <div class="form-group">
                <label for="birthdate">Data de Nascimento:</label>
                <input type="date" name="birthdate" id="birthdate" value="<?php echo htmlspecialchars($dataNascimento); ?>" readonly class="readonly-field">
            </div>

            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($userData['email']); ?>">
            </div>

            <div class="form-group">
                <label for="senha">Nova Senha (deixe em branco para manter):</label>
                <input type="password" name="senha" id="senha" minlength="6">
            </div>

            <div class="form-group">
                <label for="phone">Telefone:</label>
                <input type="tel" name="phone" id="phone" required placeholder="(00) 00000-0000" value="<?php echo htmlspecialchars(formatarTelefone($userData['telefone'])); ?>">
            </div>

            <div class="form-group">
                <label for="cep">CEP:</label>
                <div class="cep-search">
                    <input type="text" name="cep" id="cep" required placeholder="00000-000" value="<?php echo htmlspecialchars($preFilledCep); ?>">
                    <button type="button" onclick="buscarCEP()">Buscar</button>
                </div>
                <div id="cep-error" class="error"></div>
            </div>

            <div id="address-fields" class="address-fields">
                <div class="form-group">
                    <label for="logradouro">Logradouro:</label>
                    <input type="text" name="logradouro" id="logradouro" required value="<?php echo htmlspecialchars($preFilledLogradouro); ?>">
                </div>
                <div class="form-group">
                    <label for="numero">Número:</label>
                    <input type="text" name="numero" id="numero" required value="<?php echo htmlspecialchars($preFilledNumero); ?>">
                </div>
                <div class="form-group">
                    <label for="complemento">Complemento:</label>
                    <input type="text" name="complemento" id="complemento" value="<?php echo htmlspecialchars($preFilledComplemento); ?>">
                </div>
                <div class="form-group">
                    <label for="bairro">Bairro:</label>
                    <input type="text" name="bairro" id="bairro" required value="<?php echo htmlspecialchars($preFilledBairro); ?>">
                </div>
                <div class="form-group">
                    <label for="cidade">Cidade:</label>
                    <input type="text" name="cidade" id="cidade" required value="<?php echo htmlspecialchars($preFilledCidade); ?>">
                </div>
                <div class="form-group">
                    <label for="uf">Estado:</label>
                    <input type="text" name="uf" id="uf" required maxlength="2" value="<?php echo htmlspecialchars($preFilledUf); ?>">
                </div>
            </div>

            <input type="hidden" name="endereco_completo" id="endereco_completo" value="<?php echo htmlspecialchars($userData['endereco']); ?>">

            <div class="btn-container">
                <button type="submit" class="btn">Atualizar Dados</button>
                <a href="/" class="btn">Voltar</a>
            </div>
        </form>
    </div>

    <script>
        function buscarCEP() {
            const cep = document.getElementById('cep').value.replace(/\D/g, '');
            const cepError = document.getElementById('cep-error');
            
            if (cep.length !== 8) {
                cepError.textContent = 'CEP deve ter 8 dígitos';
                return;
            }
            
            cepError.textContent = '';
            const url = '/includes/buscar-cep.php?cep=' + encodeURIComponent(cep);
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.erro) {
                        cepError.textContent = 'CEP não encontrado.';
                        document.getElementById('address-fields').style.display = 'none';
                    } else {
                        document.getElementById('logradouro').value = data.logradouro || '';
                        document.getElementById('bairro').value = data.bairro || '';
                        document.getElementById('cidade').value = data.localidade || '';
                        document.getElementById('uf').value = data.uf || '';
                        document.getElementById('complemento').value = '';
                        document.getElementById('numero').value = '';
                        document.getElementById('address-fields').style.display = 'block';
                    }
                })
                .catch(error => {
                    cepError.textContent = 'Erro ao buscar CEP: ' + error.message;
                    document.getElementById('address-fields').style.display = 'none';
                });
        }

        function validarFormulario() {
            const addressFieldsVisible = document.getElementById('address-fields').style.display === 'block';
            
            if (addressFieldsVisible) {
                const cep = document.getElementById('cep').value;
                const logradouro = document.getElementById('logradouro').value;
                const numero = document.getElementById('numero').value;
                const complemento = document.getElementById('complemento').value;
                const bairro = document.getElementById('bairro').value;
                const cidade = document.getElementById('cidade').value;
                const uf = document.getElementById('uf').value;

                if (!numero) {
                    alert('Por favor, informe o número do endereço.');
                    return false;
                }

                const enderecoCompleto = `${cep}, ${logradouro}, ${numero}, ` +
                                        `${complemento ? complemento + ', ' : ''}` +
                                        `${bairro}, ${cidade}-${uf}`;

                document.getElementById('endereco_completo').value = enderecoCompleto;
            }
            
            return true;
        }

        window.onload = function () {
            const preFilledCep = "<?php echo htmlspecialchars($preFilledCep); ?>";
            if (preFilledCep) {
                document.getElementById('address-fields').style.display = 'block';
            }

            ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'].forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('blur', () => {
                        const cep = document.getElementById('cep').value || '';
                        const logradouro = document.getElementById('logradouro').value || '';
                        const numero = document.getElementById('numero').value || '';
                        const complemento = document.getElementById('complemento').value || '';
                        const bairro = document.getElementById('bairro').value || '';
                        const cidade = document.getElementById('cidade').value || '';
                        const uf = document.getElementById('uf').value || '';

                        const enderecoCompletoAtualizado =
                            `${cep}, ${logradouro}, ${numero}, ` +
                            `${complemento ? complemento + ', ' : ''}` +
                            `${bairro}, ${cidade}-${uf}`;

                        document.getElementById('endereco_completo').value = enderecoCompletoAtualizado;
                    });
                }
            });
        };
    </script>
</body>
</html>