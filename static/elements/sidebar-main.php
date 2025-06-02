<link rel="stylesheet" href="../static/style/menu.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<button class="menu-toggle">
    <i class="fas fa-bars"></i>
</button>

  <!-- Menu lateral -->
  <aside>
    <br><br><br>
    <div class="nav-item" onclick="window.location.href='/'">
      <i class="fas fa-home"></i>
      <span>Início</span>
    </div>
    <div class="nav-item" onclick="window.location.href='/produtos/'">
      <i class="fas fa-search"></i>
      <span>Explorar Produtos</span>
    </div>
    <div class="nav-item" onclick="window.location.href='/carrinho/'">
      <i class="fas fa-shopping-cart"></i>
      <span>Carrinho</span>
    </div>
    <div class="nav-item" onclick="window.location.href='/fornecedor/dashboard.php'">
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