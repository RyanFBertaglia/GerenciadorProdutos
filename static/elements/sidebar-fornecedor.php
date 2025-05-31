<?php
if (isset($_GET['acao']) && $_GET['acao'] === 'logout') {
    logout();
    header('Location: /fornecedor/login.php');
    exit;
}
?>

<link rel="stylesheet" href="../static/style/menu.css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


<button class="menu-toggle">
    <i class="fas fa-bars"></i>
</button>

<!-- Menu lateral -->
<aside>
<br><br><br>

    <div class="nav-item" onclick="window.location.href='/fornecedor/dashboard.php'">
    <i class="bi bi-speedometer2"></i>
      <span>Dashboard</span>
    </div>
    <div class="nav-item" onclick="window.location.href='/fornecedor/add-product.php'">
      <i class="bi bi-plus-circle"></i>
      <span>Cadastrar Produto</span>
    </div>
    <div class="nav-item" onclick="window.location.href='/fornecedor/produtos.php'">
      <i class="bi bi-box-seam"></i>
      <span>Meus Produtos</span>
    </div>
    <div class="nav-item" onclick="window.location.href='?acao=logout'">
      <i class="bi bi-box-arrow-right"></i>
      <span>Logout</span>
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