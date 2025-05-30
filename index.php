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
  <link rel="stylesheet" href="./static/style/menu.css">
  <link rel="stylesheet" href="./static/style/tipografia.css">
  <link rel="stylesheet" href="./static/style/main.css">
</head>
<body>
  <!-- Botão toggle para mobile -->
  <button class="menu-toggle">
    <i class="fas fa-bars"></i>
  </button>

  <!-- Menu lateral -->
  <aside>
    <br><br><br>
    <div class="nav-item" onclick="window.location.href='inicio.html'">
      <i class="fas fa-home"></i>
      <span>Início</span>
    </div>
    <div class="nav-item" onclick="window.location.href='carrinho.html'">
      <i class="fas fa-shopping-cart"></i>
      <span>Carrinho</span>
    </div>
    <div class="nav-item" onclick="window.location.href='vender.html'">
      <i class="fas fa-store"></i>
      <span>Vender</span>
    </div>
    <div class="nav-item" onclick="window.location.href='conta.html'">
      <i class="fas fa-user"></i>
      <span>Minha conta</span>
    </div>
    <div class="nav-item" onclick="window.location.href='ajuda.html'">
      <i class="fas fa-question-circle"></i>
      <span>Ajuda</span>
    </div>
  </aside>

  <!-- Conteúdo principal -->
  <main>
    <div class="credit">
      <img src="./static/img/logo.jpg" alt="Logo Mancha" class="logo-img" />
      Feito por <strong>Mancha</strong>
    </div>

    <h1>Comece a comprar e tenha</h1>
    <h1>20% de desconto em sua</h1>
    <h1>primeira compra!</h1>

    <p>Comece a anunciar, tenha uma boa avaliação e pagaremos todos os seus fretes!!</p>

    <button class="btn-start" onclick="window.location.href='cadastro.html'">Começar</button>

    <a href="https://manchagestões.com" class="website-link" target="_blank">
      Nosso Website: ManchaGestões.com <i class="fas fa-globe"></i>
    </a>

    <img src="./static/img/predio.png" alt="Prédio" class="building-img" />
  </main>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
        const menuToggle = document.querySelector(".menu-toggle");
        const body = document.body;
        const aside = document.querySelector("aside");

        // Função para alternar o menu
        function toggleMenu() {
            if (window.innerWidth <= 768) {
                aside.classList.toggle("show");
            } else {
                body.classList.toggle("collapsed");
            }
        }
        
        if (menuToggle) {
            menuToggle.addEventListener("click", toggleMenu);
        }

        window.addEventListener("resize", () => {
            if (window.innerWidth > 768) {
                aside.classList.remove("show");
                body.classList.remove("collapsed");
            }
        });

        // Fechar menu mobile ao clicar fora dele
        document.addEventListener("click", (e) => {
            if (window.innerWidth <= 768 && 
                aside.classList.contains("show") && 
                !aside.contains(e.target) && 
                !menuToggle.contains(e.target)) {
                aside.classList.remove("show");
            }
        });
    });
  </script>
</body>
</html>