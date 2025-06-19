<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mancha Gestões</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="./static/style/paginaInicial.css">
  <link rel="stylesheet" href="./static/style/tipografia.css">
  <link rel="stylesheet" href="./static/style/main.css">
</head>
<body>

  <?php include './static/elements/sidebar-main.php'; ?>

  <main>
    <br><br>
    <div class="container">
    <h1>Comece a comprar e tenha</h1>
    <h1>20% de desconto em sua</h1>
    <h1>primeira compra!</h1>

    <p>Comece a anunciar, tenha uma boa avaliação e pagaremos todos os seus fretes!!</p>

    <button class="btn-start" onclick="window.location.href='/login'">Começar</button>
    </div>
    

    <a href="/admin/dashboard" class="website-link">
      Administração <i class="fas fa-globe"></i>
    </a>

    <img src="./static/img/predio.png" alt="Prédio" class="building-img" />
  </main>
  
</body>
</html>