<link rel="stylesheet" href="../static/style/menu.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<button class="menu-toggle"><i class="fas fa-bars"></i></button>
<aside><br><br><br>
<div class="nav-item" onclick="window.location.href='/'"><i class="fas fa-home"></i><span>Início</span></div>
<div class="nav-item" onclick="window.location.href='/produto'"><i class="fas fa-search"></i><span>Explorar Produtos</span></div>
<div class="nav-item" onclick="window.location.href='/user/carrinho'"><i class="fas fa-shopping-cart"></i><span>Carrinho</span></div>
<div class="nav-item" onclick="window.location.href='/fornecedor/dashboard'"><i class="fas fa-store"></i><span>Vender</span></div>
<div class="nav-item" onclick="window.location.href='conta.html'"><i class="fas fa-user"></i><span>Minha conta</span></div>
<div class="nav-item" onclick="window.location.href='ajuda.html'"><i class="fas fa-question-circle"></i><span>Ajuda</span></div>
</aside>
<script>
document.addEventListener("DOMContentLoaded",()=>{
  const e=document.querySelector(".menu-toggle"),t=document.body,n=document.querySelector("aside");
  function o(){window.innerWidth<=768?n.classList.toggle("show"):t.classList.toggle("collapsed")}e&&e.addEventListener("click",o),window.addEventListener("resize",()=>{
    window.innerWidth>768&&n.classList.remove("show")}),document.addEventListener("click",(e=>{window.innerWidth<=768&&n.classList.contains("show")&&!n.contains(e.target)&&!e.target.closest(".menu-toggle")&&n.classList.remove("show")}))});</script>