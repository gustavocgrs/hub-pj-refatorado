/* ============================================================
   utils.js — Utilitários globais do Hub PJ
   Toggle de tema claro/escuro + alert de links placeholder.
   Aguarda o evento "components:loaded" caso os elementos
   sejam injetados dinamicamente via components.js.
   ============================================================ */

function initUtils() {
  /* ---------- Tema claro / escuro ---------- */
  var themeToggle = document.getElementById("theme-toggle");

  if (themeToggle) {
    var savedTheme = localStorage.getItem("theme");

    if (savedTheme === "dark") {
      document.body.classList.add("dark");
      themeToggle.textContent = "Tema claro";
    }

    themeToggle.addEventListener("click", function () {
      document.body.classList.toggle("dark");
      var isDark = document.body.classList.contains("dark");
      localStorage.setItem("theme", isDark ? "dark" : "light");
      themeToggle.textContent = isDark ? "Tema claro" : "Tema escuro";
    });
  }

  /* ---------- Alert em links placeholder (#) ---------- */
  document.querySelectorAll('.btn[href="#"]').forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      alert("Esta ferramenta ainda nao esta disponivel.");
    });
  });
}

/* Se o theme-toggle já existe, inicializa direto.
   Senão, espera o loader de componentes. */
if (document.getElementById("theme-toggle")) {
  initUtils();
} else {
  document.addEventListener("components:loaded", initUtils);
}
