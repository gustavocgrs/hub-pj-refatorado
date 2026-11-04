/* ============================================================
   nav.js — Lógica de navegação do Hub PJ
   Dropdown de setores e fechamento ao clicar fora.
   Aguarda o evento "components:loaded" caso a nav seja
   injetada dinamicamente via components.js.
   ============================================================ */

function initNav() {
  var navDropdown = document.getElementById("navDropdown");
  var navDropdownToggle = document.getElementById("navDropdownToggle");

  if (!navDropdown || !navDropdownToggle) return;

  navDropdownToggle.addEventListener("click", function (e) {
    e.stopPropagation();
    navDropdown.classList.toggle("open");
  });

  document.addEventListener("click", function (e) {
    if (!navDropdown.contains(e.target)) {
      navDropdown.classList.remove("open");
    }
  });
}

/* Se a nav já existe no DOM, inicializa direto.
   Senão, espera o loader de componentes. */
if (document.getElementById("navDropdown")) {
  initNav();
} else {
  document.addEventListener("components:loaded", initNav);
}
