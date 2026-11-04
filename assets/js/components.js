/* ============================================================
   components.js — Loader de componentes HTML reutilizáveis
   Injeta nav.html e footer.html nos placeholders das páginas.

   Uso: adicione no HTML:
     <div id="nav-placeholder"></div>   (onde a nav deve aparecer)
     <div id="footer-placeholder"></div> (onde o footer deve aparecer)

   Este script deve ser carregado ANTES de nav.js e utils.js,
   pois eles dependem dos elementos injetados.
   ============================================================ */

(function () {
  /**
   * Busca um arquivo HTML e injeta seu conteúdo no elemento alvo.
   * Retorna uma Promise para permitir encadeamento.
   */
  function loadComponent(id, url) {
    var el = document.getElementById(id);
    if (!el) return Promise.resolve();

    return fetch(url)
      .then(function (res) {
        if (!res.ok) throw new Error("Falha ao carregar " + url);
        return res.text();
      })
      .then(function (html) {
        el.innerHTML = html;
      });
  }

  /* Detecta a raiz do projeto relativa à página atual.
     Páginas na raiz:           basePath = "."
     Páginas em setores/x/:     basePath = "../.."
     Páginas em setores/x/paginas/: basePath = "../../.." */
  var basePath = document.documentElement.dataset.base || ".";

  Promise.all([
    loadComponent("nav-placeholder",    basePath + "/components/nav.html"),
    loadComponent("footer-placeholder", basePath + "/components/footer.html")
  ]).then(function () {
    /* Após injetar, dispara evento para que nav.js e utils.js
       possam se inicializar sobre os elementos recém-criados. */
    document.dispatchEvent(new Event("components:loaded"));
  });
})();
