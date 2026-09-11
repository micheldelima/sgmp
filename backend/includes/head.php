<?php
// C:\xampp\htdocs\app-uepg\includes\head.php
$base_url = '/app-uepg/';
// Desativa CSP restritiva (para ambiente de teste)
header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' data: blob: https://cdn.jsdelivr.net;");
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<!-- Bootstrap CSS -->
<link href="<?= $base_url ?>backend/public/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link href="<?= $base_url ?>backend/public/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<!-- CSS Customizado -->
<link href="<?= $base_url ?>backend/public/assets/css/custom.css" rel="stylesheet">
<!-- Favicon -->
<link href="<?= $base_url ?>backend/public/assets/img/favicon.ico" rel="icon">
<!-- Axios -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<!-- Tema claro/escuro -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const toggle = document.getElementById("darkModeToggle");
    const isDark = localStorage.getItem("tema") === "dark";

    const aplicarTemaEscuro = () => {
      document.body.classList.add("bg-dark", "text-light");

      // Textos claros
      document.querySelectorAll(".text-muted").forEach(el => {
        el.classList.remove("text-muted");
        el.classList.add("text-white");
      });

      document.querySelectorAll(".form-text").forEach(el => {
        el.classList.add("text-white");
      });

      // Modais
      document.querySelectorAll(".modal-content").forEach(modal => {
        modal.classList.add("bg-dark", "text-light");
      });

      // Cards
      document.querySelectorAll(".card-body").forEach(card => {
        card.classList.add("bg-dark", "text-light");
      });

      toggle.checked = true;
    };

    const aplicarTemaClaro = () => {
      document.body.classList.remove("bg-dark", "text-light");

      // Textos escuros
      document.querySelectorAll(".text-white").forEach(el => {
        el.classList.remove("text-white");
        el.classList.add("text-muted");
      });

      document.querySelectorAll(".form-text").forEach(el => {
        el.classList.remove("text-white");
      });

      // Modais
      document.querySelectorAll(".modal-content").forEach(modal => {
        modal.classList.remove("bg-dark", "text-light");
      });

      // Cards
      document.querySelectorAll(".card-body").forEach(card => {
        card.classList.remove("bg-dark", "text-light");
      });

      toggle.checked = false;
    };

    if (isDark) {
      aplicarTemaEscuro();
    }

    toggle.addEventListener("change", function () {
      if (this.checked) {
        localStorage.setItem("tema", "dark");
        aplicarTemaEscuro();
      } else {
        localStorage.setItem("tema", "light");
        aplicarTemaClaro();
      }
    });
  });
</script>