<?php
  if (session_status() === PHP_SESSION_NONE) {
      session_start();
  }
?>
<link rel="stylesheet" href="../static/style/menu.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<button class="menu-toggle"><i class="fas fa-bars"></i></button>
<aside>
  <br><br><br>
  <div class="nav-item" onclick="window.location.href='/'">
    <i class="fas fa-home"></i><span>Início</span>
  </div>
  <div class="nav-item" onclick="window.location.href='/produto'">
    <i class="fas fa-search"></i><span>Explorar Produtos</span>
  </div>
  <div class="nav-item" onclick="window.location.href='/user/carrinho'">
    <i class="fas fa-shopping-cart"></i><span>Carrinho</span>
  </div>
  <div class="nav-item" onclick="window.location.href='/fornecedor/dashboard'">
    <i class="fas fa-store"></i><span>Vender</span>
  </div>
  <div class="nav-item" onclick="window.location.href='/user/minha-conta'">
    <i class="fas fa-user"></i><span>Minha Conta</span>
  </div>
  <div class="nav-item" onclick="window.location.href='ajuda.html'">
    <i class="fas fa-question-circle"></i><span>Ajuda</span>
  </div>

  <?php if (isset($_SESSION['usuario'])): ?>
    <div class="nav-item" onclick="window.location.href='/logout'">
      <i class="fas fa-sign-out-alt"></i><span>Logout</span>
    </div>
  <?php endif; ?>
</aside>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const toggle = document.querySelector(".menu-toggle"),
          body   = document.body,
          aside  = document.querySelector("aside");

    function handleMenu() {
      if (window.innerWidth <= 768) {
        aside.classList.toggle("show");
      } else {
        body.classList.toggle("collapsed");
      }
    }

    toggle && toggle.addEventListener("click", handleMenu);
    window.addEventListener("resize", () => {
      if (window.innerWidth > 768) {
        aside.classList.remove("show");
      }
    });
    document.addEventListener("click", (e) => {
      if (window.innerWidth <= 768 && aside.classList.contains("show")) {
        if (!aside.contains(e.target) && !e.target.closest(".menu-toggle")) {
          aside.classList.remove("show");
        }
      }
    });
  });
</script>
