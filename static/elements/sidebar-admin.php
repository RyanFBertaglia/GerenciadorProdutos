<?php
if (isset($_GET['acao']) && $_GET['acao'] === 'logout') {
    logout();
    header('Location: /admin/login');
}
?>

<link rel="stylesheet" href="../static/style/menu.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<button class="menu-toggle">
    <i class="fas fa-bars"></i>
</button>

<aside class="admin-sidebar">
    <br><br><br>
    <div class="nav-item" onclick="window.location.href='/admin/dashboard'">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
    </div>
    
    <div class="nav-item" onclick="window.location.href='/admin/pendentes'">
        <i class="bi bi-card-checklist"></i>
        <span>Produtos Pendentes</span>
        <?php if ($produtosPendentes > 0): ?>
            <span class="badge"><?= $produtosPendentes ?></span>
        <?php endif; ?>
    </div>
    
    <div class="nav-item" onclick="window.location.href='/admin/fornecedores'">
        <i class="bi bi-people"></i>
        <span>Fornecedores</span>
    </div>
    
    <div class="nav-item" onclick="window.location.href='/produto'">
        <i class="bi bi-box-seam"></i>
        <span>Produtos</span>
    </div>
    
    <div class="nav-item" onclick="window.location.href='/admin/usuarios.php'">
        <i class="bi bi-person-lines-fill"></i>
        <span>Usuários</span>
    </div>
    
    <div class="nav-item logout-item" onclick="window.location.href='/logout'">
        <i class="bi bi-box-arrow-right"></i>
        <span>Sair</span>
    </div>
</aside>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const menuToggle = document.querySelector(".menu-toggle");
        const body = document.body;
        const aside = document.querySelector("aside");

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