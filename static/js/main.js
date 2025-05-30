document.addEventListener("DOMContentLoaded", () => {
    const menuIcon = document.querySelector(".menu-icon");
    const menuToggle = document.querySelector(".menu-toggle");
    const body = document.body;
    const aside = document.querySelector("aside");

    // Função para alternar o menu
    function toggleMenu() {
        if (window.innerWidth <= 768) {
            // Mobile: toggle classe show no aside
            aside.classList.toggle("show");
        } else {
            // Desktop: toggle classe collapsed no body
            body.classList.toggle("collapsed");
        }
    }

    // Event listeners
    if (menuIcon) {
        menuIcon.addEventListener("click", toggleMenu);
    }
    
    if (menuToggle) {
        menuToggle.addEventListener("click", toggleMenu);
    }

    // Fechar menu mobile ao clicar em um item
    const navItems = document.querySelectorAll(".nav-item");
    navItems.forEach(item => {
        item.addEventListener("click", () => {
            if (window.innerWidth <= 768) {
                aside.classList.remove("show");
            }
        });
    });

    // Fechar menu mobile ao redimensionar para desktop
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