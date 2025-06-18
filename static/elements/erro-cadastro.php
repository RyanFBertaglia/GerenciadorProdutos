<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$erro = $_SESSION['erro'] ?? '';
$dados = $_SESSION['dados_form'] ?? [];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro no Cadastro</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
            text-align: center;
        }
        
        .header {
            background: linear-gradient(to right, #ff416c, #ff4b2b);
            color: white;
            padding: 30px 20px;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header i {
            font-size: 60px;
            margin-bottom: 20px;
            display: block;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .error-message {
            background-color: #ffebee;
            border-left: 4px solid #f44336;
            padding: 20px;
            margin-bottom: 30px;
            text-align: left;
            border-radius: 4px;
        }
        
        .error-message h2 {
            color: #f44336;
            margin-bottom: 10px;
            font-size: 20px;
        }
        
        .back-button {
            display: inline-block;
            background: linear-gradient(to right, #2193b0, #6dd5ed);
            color: white;
            text-decoration: none;
            padding: 14px 35px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 18px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(33, 147, 176, 0.3);
            border: none;
            cursor: pointer;
        }
        
        .back-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(33, 147, 176, 0.4);
        }
        
        .back-button i {
            margin-right: 10px;
        }
        
        .form-data {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
            text-align: left;
        }
        
        .form-data h3 {
            margin-bottom: 15px;
            color: #555;
        }
        
        .data-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .data-label {
            font-weight: 600;
            color: #666;
        }
        
        .data-value {
            color: #333;
        }
        
        @media (max-width: 480px) {
            .container {
                border-radius: 12px;
            }
            
            .header {
                padding: 25px 15px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .content {
                padding: 30px 20px;
            }
            
            .back-button {
                padding: 12px 30px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <i class="fas fa-exclamation-triangle"></i>
            <h1>Ocorreu um problema</h1>
            <p>Não foi possível completar seu cadastro</p>
        </div>
        
        <div class="content">
            <div class="error-message">
                <h2><i class="fas fa-times-circle"></i> Erro no cadastro</h2>
                <p><?php echo htmlspecialchars($erro); ?></p>
            </div>
            
            <?php if (!empty($dados)): ?>
            <div class="form-data">
                <h3>Dados informados:</h3>
                <?php foreach ($dados as $label => $value): ?>
                    <div class="data-item">
                        <span class="data-label"><?php echo htmlspecialchars($label); ?>:</span>
                        <span class="data-value"><?php echo htmlspecialchars($value); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <button onclick="history.back()" class="back-button">
                <i class="fas fa-arrow-left"></i> Voltar e Corrigir
            </button>
        </div>
    </div>
    
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>