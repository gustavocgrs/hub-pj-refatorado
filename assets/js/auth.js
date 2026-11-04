/* ============================================================
   auth.js — Protecao de paginas do Hub PJ
   Verifica se o usuario esta logado antes de mostrar o conteudo.
   Se nao estiver, redireciona para login.html.

   Uso: adicione ANTES de qualquer outro script:
     <script src="../../assets/js/auth.js"></script>

   A pagina deve ter: <div class="page" id="pageShell">
   O conteudo comeca oculto (opacity:0) e aparece apos autenticar.
   ============================================================ */

(function () {
  var basePath = document.documentElement.dataset.base || ".";
  var loginUrl = basePath + "/login.html";
  var checkUrl = basePath + "/api/auth/check-session.php";

  fetch(checkUrl)
    .then(function (r) {
      if (!r.ok) throw new Error("nao autenticado");
      return r.json();
    })
    .then(function (data) {
      if (!data.logado) {
        window.location.href = loginUrl;
        return;
      }
      /* Autenticado — mostrar a pagina */
      var shell = document.getElementById("pageShell");
      if (shell) shell.classList.add("ready");
    })
    .catch(function () {
      window.location.href = loginUrl;
    });
})();
