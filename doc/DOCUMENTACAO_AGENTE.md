# DOCUMENTACAO COMPLETA — HUB PJ REFATORADO
### Guia instrucional para agentes de IA

> **Gerado em:** 2026-04-27
> **Destinado a:** Agentes de IA que precisam entender completamente o projeto, contribuir com novas funcionalidades ou dar manutencao no sistema.
> **Nivel de detalhe:** Exhaustivo — todos os arquivos, fluxos, decisoes arquiteturais e estado atual de implementacao estao documentados aqui.

---

## 1. VISAO GERAL DO PROJETO

### O que e o Hub PJ

O **Hub PJ Refatorado** e uma aplicacao web interna desenvolvida para a equipe de um escritorio de contabilidade PJ (Pessoa Juridica). Seu objetivo e centralizar ferramentas de produtividade e automatizacao organizadas por setor/departamento da empresa, com controle de acesso individual por usuario.

O sistema funciona como um **portal interno** (intranet) — nao e publico. Cada colaborador loga com seu email e senha e ve apenas os setores aos quais foi autorizado pelo administrador. Dentro de cada setor, ferramentas como calculadoras tributarias, formularios de contratos e futuramente dashboards e CRMs estao disponiveis.

### Proposta de valor

- **Organiza** ferramentas que antes eram planilhas isoladas ou processos manuais
- **Padroniza** calculos trabalhistas e tributarios (CLT, pro-labore, projecao tributaria)
- **Centraliza** integracoes externas (formularios de contratos enviados via webhook para Make.com)
- **Controla** quem acessa o que, por setor
- **Facilita** o dia a dia da equipe com exportacao de PDF e interface moderna

### Tecnologias utilizadas

| Camada | Tecnologia |
|--------|-----------|
| Backend | PHP 7.x+ (sem framework) |
| Banco de dados | MySQL via PDO |
| Frontend | HTML5 + CSS3 + Vanilla JavaScript (ES6+) |
| Graficos | Chart.js (usado nas calculadoras) |
| Fontes | Google Fonts — Manrope |
| Integracao externa | Make.com via webhook HTTP |
| Hospedagem | Apache (Hostinger) com mod_rewrite |
| Seguranca | BCRYPT, CSRF tokens, sessoes seguras, rate limiting por arquivo |

### Estado atual de implementacao

| Area | Status |
|------|--------|
| Sistema de autenticacao | Completo |
| Controle de permissoes por setor | Completo |
| Navegacao e layout base | Completo |
| Tema claro/escuro | Completo |
| Calculadora CLT | Completo |
| Calculadora de Projacao Tributaria | Completo |
| Calculadora de Pro-Labore | Completo |
| Formulario de Contratos (webhook) | Completo |
| Exportacao de PDF (relatorios) | Completo |
| Setor Contabil (ferramentas) | Em desenvolvimento |
| Setor Financeiro (ferramentas) | Em desenvolvimento |
| Setor Marketing (ferramentas) | Em desenvolvimento |
| Setor Paralegal (ferramentas) | Em desenvolvimento |
| Setor Certificado Digital (ferramentas) | Em desenvolvimento |
| CRM Comercial | Em desenvolvimento |
| Controle de Folha (RH) | Em desenvolvimento |

---

## 2. ESTRUTURA DE DIRETORIOS

```
hub-pj-refatorado/
├── api/                          # Backend PHP — toda logica server-side
│   ├── auth/
│   │   ├── login.php             # Autentica usuario, cria sessao, retorna CSRF
│   │   ├── logout.php            # Destroi sessao (requer CSRF)
│   │   └── check-session.php    # Verifica se usuario esta logado
│   ├── setores/
│   │   ├── get.php               # Lista setores disponiveis para o usuario logado
│   │   └── ferramentas.php      # Arquivo vazio (reservado para futuro)
│   ├── usuarios/
│   │   ├── get.php               # Lista todos os usuarios (admin only)
│   │   ├── create.php            # Cria usuario com permissoes (admin only)
│   │   └── update.php            # Atualiza permissoes de usuario (admin only)
│   ├── config.php                # Configuracao central: BD, sessao, CSRF, rate limit
│   └── webhook-proxy.php         # Proxy seguro para webhook Make.com
│
├── assets/
│   ├── css/
│   │   ├── hub.css               # Estilos globais, variaveis de tema, layout
│   │   └── components.css        # Componentes reutilizaveis (cards, inputs, KPIs)
│   ├── js/
│   │   ├── auth.js               # Verifica autenticacao em cada pagina protegida
│   │   ├── components.js         # Carrega nav.html e footer.html dinamicamente
│   │   ├── nav.js                # Toggle do dropdown de navegacao
│   │   └── utils.js              # Toggle de tema, alerts genericos
│   └── img/
│       └── .gitkeep              # Placeholder — pasta para imagens (vazia)
│
├── components/
│   ├── nav.html                  # Componente de navegacao principal
│   └── footer.html               # Componente de rodape
│
├── data/
│   ├── setores.json              # Arquivo vazio (dados vem do BD, nao daqui)
│   └── ferramentas.json          # Arquivo vazio (dados vem do BD, nao daqui)
│
├── db/
│   ├── schema.sql                # DDL: cria tabelas usuarios, setores, permissoes
│   ├── seed.sql                  # Dados iniciais: insere 9 setores
│   ├── install.php               # Script de instalacao unica (rodar 1x, deletar)
│   ├── criar-usuarios.php        # Cria 7 usuarios em lote (rodar 1x, deletar)
│   └── migrations/
│       └── 001_add_permissoes.sql # Vazio — placeholder para futura migration
│
├── setores/                      # Uma pasta por setor
│   ├── comercial/
│   │   ├── index.html
│   │   └── paginas/
│   │       ├── calculadora-projecao.html
│   │       ├── prototipo-crm.html         # Em desenvolvimento
│   │       └── api/
│   │           ├── gerar-pdf.php          # Gera relatorio tributario HTML/PDF
│   │           └── .htaccess
│   ├── rh/
│   │   ├── index.html
│   │   └── paginas/
│   │       ├── calculadora-clt.html
│   │       ├── calculadora-pro-labore.html
│   │       └── api/
│   │           ├── gerar-pdf-clt.php      # Gera relatorio CLT HTML/PDF
│   │           └── .htaccess
│   ├── fiscal/
│   │   ├── index.html
│   │   └── paginas/
│   │       ├── calculadora-lp-nova.html          # Referencia externa
│   │       └── calculadora-lucro-presumido.html   # Referencia externa
│   ├── contabil/
│   │   ├── index.html
│   │   └── paginas/               # Vazio — ferramentas em desenvolvimento
│   ├── financeiro/
│   │   ├── index.html
│   │   └── paginas/               # Vazio
│   ├── marketing/
│   │   ├── index.html
│   │   └── paginas/               # Vazio
│   ├── paralegal/
│   │   ├── index.html
│   │   └── paginas/               # Vazio
│   ├── certificado-digital/
│   │   ├── index.html
│   │   └── paginas/               # Vazio
│   └── sucesso-do-cliente/
│       ├── index.html
│       └── paginas/
│           └── formulario-de-contratos.html
│
├── tmp/
│   ├── ratelimit/                 # Arquivos de rate limiting por IP/usuario
│   └── .htaccess                  # Bloqueia acesso HTTP direto a esta pasta
│
├── index.html                     # Dashboard principal (carrega setores da API)
├── login.html                     # Pagina de login
├── 404.html                       # Pagina de erro 404 personalizada
├── .htaccess                      # Config Apache: HTTPS, headers de seguranca, rewrite
├── .gitignore                     # Ignora config.php, install.php, criar-usuarios.php, logs
└── DOCUMENTACAO_AGENTE.md         # Este arquivo
```

---

## 3. BANCO DE DADOS

### Schema (db/schema.sql)

O banco de dados possui 3 tabelas:

#### Tabela `usuarios`
```sql
CREATE TABLE usuarios (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  nome      VARCHAR(120) NOT NULL,
  email     VARCHAR(180) NOT NULL UNIQUE,
  senha     VARCHAR(255) NOT NULL,   -- hash BCRYPT
  papel     ENUM('admin','usuario') NOT NULL DEFAULT 'usuario',
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

#### Tabela `setores`
```sql
CREATE TABLE setores (
  id    INT AUTO_INCREMENT PRIMARY KEY,
  slug  VARCHAR(60) NOT NULL UNIQUE,  -- identificador usado em URLs
  nome  VARCHAR(120) NOT NULL,        -- nome exibido na interface
  icone VARCHAR(10)                   -- emoji representativo
);
```

#### Tabela `permissoes` (N:N entre usuarios e setores)
```sql
CREATE TABLE permissoes (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  setor_id   INT NOT NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (setor_id)   REFERENCES setores(id)  ON DELETE CASCADE,
  UNIQUE KEY uq_perm (usuario_id, setor_id)
);
```

### Dados iniciais (db/seed.sql)

Os 9 setores pre-cadastrados:

| slug | nome | icone |
|------|------|-------|
| comercial | Setor Comercial | 🤝 |
| contabil | Setor Contabil | 📘 |
| certificado-digital | Setor de Certificado Digital | 🔐 |
| marketing | Setor de Marketing | 📣 |
| sucesso-do-cliente | Setor de Sucesso do Cliente | 💬 |
| fiscal | Setor Fiscal | 🧾 |
| financeiro | Setor Financeiro | 💹 |
| paralegal | Setor Paralegal | 🏛️ |
| rh | Setor Pessoal (RH) | 👥 |

### Credenciais de conexao (api/config.php)

```php
$host = 'localhost';
$db   = 'u250794720_hubpj';
$user = 'u250794720_hubadmin';
// senha em config.php (nao versionado — excluido pelo .gitignore)
```

> **ATENCAO para agentes:** config.php nao esta no repositorio git por conter a senha do banco. Antes de qualquer modificacao no backend, certifique-se de que config.php existe no servidor com as credenciais corretas.

---

## 4. BACKEND — ARQUIVO A ARQUIVO

### api/config.php — Configuracao Central

Este e o arquivo mais importante do backend. Todos os outros arquivos PHP fazem `require_once` dele.

**Funcoes disponibilizadas:**

```php
getDB(): PDO
```
- Singleton: cria a conexao PDO uma unica vez por request
- Charset: utf8mb4
- Modo: ERRMODE_EXCEPTION + FETCH_ASSOC

```php
secure_session_start(): void
```
- Inicia sessao com flags de seguranca:
  - `httponly = true`
  - `secure = true` (apenas HTTPS)
  - `samesite = Strict`
- Deve ser chamada antes de qualquer output

```php
csrf_token(): string
```
- Gera um token CSRF de 32 bytes aleatorios (bin2hex(random_bytes(32)))
- Armazena em `$_SESSION['csrf_token']`
- Idempotente: retorna o mesmo token se ja existir na sessao

```php
csrf_validate(): void
```
- Le o header HTTP `X-CSRF-Token`
- Compara com `$_SESSION['csrf_token']` usando `hash_equals` (timing-safe)
- Encerra com HTTP 403 + JSON de erro se invalido

```php
rate_limit(string $key, int $max, int $window): void
```
- Baseado em arquivo: cria/le `/tmp/ratelimit/{key}.json`
- `$key`: identificador unico (ex: "login_" . $_SERVER['REMOTE_ADDR'])
- `$max`: numero maximo de requisicoes permitidas
- `$window`: janela de tempo em segundos
- Encerra com HTTP 429 se limite excedido

```php
json_response(array $data, int $status = 200): never
```
- Define Content-Type: application/json
- Define codigo HTTP
- Ecoa JSON e encerra o script

```php
require_auth(): array
```
- Verifica se `$_SESSION['usuario_id']` existe
- Encerra com 401 se nao autenticado
- Retorna array com dados do usuario da sessao se autenticado

```php
require_admin(): array
```
- Chama `require_auth()`
- Verifica se `$_SESSION['papel'] === 'admin'`
- Encerra com 403 se nao for admin

---

### api/auth/login.php

**Metodo:** POST
**Autenticacao:** Nao requerida (e o endpoint de login)
**Body esperado (JSON):**
```json
{ "email": "usuario@exemplo.com", "senha": "senha123" }
```

**Fluxo:**
1. Aplica rate limit: 5 tentativas / 5 minutos por IP
2. Le email e senha do body JSON
3. Busca usuario no BD pelo email
4. Valida senha com `password_verify` (BCRYPT)
5. Se invalido: retorna HTTP 401 + `{ "erro": "Credenciais invalidas" }`
6. Se valido:
   - Chama `session_regenerate_id(true)` — previne session fixation
   - Salva na sessao: `usuario_id`, `nome`, `email`, `papel`
   - Gera CSRF token
   - Retorna HTTP 200 + `{ "ok": true, "csrf_token": "...", "usuario": {...} }`

**Codigos de resposta:**
- `200` — Login bem-sucedido
- `400` — Dados ausentes
- `401` — Credenciais invalidas
- `429` — Rate limit excedido ("Muitas tentativas. Aguarde 5 minutos.")

---

### api/auth/check-session.php

**Metodo:** GET
**Autenticacao:** Nao requerida (verifica e retorna status)

**Fluxo:**
1. Inicia sessao
2. Verifica se `$_SESSION['usuario_id']` existe
3. Se nao autenticado: retorna `{ "logado": false }` com HTTP 401
4. Se autenticado: retorna HTTP 200 + `{ "logado": true, "csrf_token": "...", "usuario": { nome, email, papel } }`

> Este endpoint e chamado por **toda pagina protegida** ao carregar, via `assets/js/auth.js`.

---

### api/auth/logout.php

**Metodo:** POST
**Autenticacao:** Requerida (via sessao)
**Headers requeridos:** `X-CSRF-Token: {token}`

**Fluxo:**
1. Valida CSRF token
2. `session_unset()` — limpa variaveis de sessao
3. `session_destroy()` — destroi a sessao no servidor
4. Retorna `{ "ok": true }`

---

### api/setores/get.php

**Metodo:** GET
**Autenticacao:** Requerida

**Fluxo:**
- Se `papel === 'admin'`: busca TODOS os setores (`SELECT * FROM setores ORDER BY nome`)
- Se `papel === 'usuario'`: busca apenas setores com permissao para o usuario logado:
  ```sql
  SELECT s.* FROM setores s
  INNER JOIN permissoes p ON p.setor_id = s.id
  WHERE p.usuario_id = :uid
  ORDER BY s.nome
  ```
- Retorna array JSON de setores: `[{ "id": 1, "slug": "comercial", "nome": "Setor Comercial", "icone": "🤝" }]`

---

### api/usuarios/get.php

**Metodo:** GET
**Autenticacao:** Admin obrigatório (`require_admin()`)

**Fluxo:**
1. Busca todos os usuarios
2. Para cada usuario, busca seus setores associados
3. Retorna array com usuarios + setores aninhados:
   ```json
   [{
     "id": 1,
     "nome": "Renan",
     "email": "renan@contabilrh.com.br",
     "papel": "usuario",
     "criado_em": "2024-01-01 00:00:00",
     "setores": [{ "id": 1, "slug": "comercial", "nome": "Setor Comercial" }]
   }]
   ```

---

### api/usuarios/create.php

**Metodo:** POST
**Autenticacao:** Admin obrigatório
**Headers requeridos:** `X-CSRF-Token: {token}`
**Body esperado (JSON):**
```json
{
  "nome": "Joao Silva",
  "email": "joao@empresa.com",
  "senha": "senha123",
  "papel": "usuario",
  "setores": [1, 3, 5]
}
```

**Validacoes:**
- Nome, email e senha sao obrigatorios
- Senha minimo 8 caracteres
- Email deve ser valido (`filter_var`)
- Email deve ser unico no BD
- `papel` deve ser `'admin'` ou `'usuario'` (default: `'usuario'`)
- Cada ID em `setores` deve existir na tabela `setores`

**Fluxo:**
1. Valida todos os campos
2. Hash da senha: `password_hash($senha, PASSWORD_BCRYPT)`
3. Insere na tabela `usuarios`
4. Para cada setor no array: insere linha em `permissoes (usuario_id, setor_id)`
5. Retorna: `{ "ok": true, "usuario": { id, nome, email, papel }, "setores_count": 3 }`

---

### api/usuarios/update.php

**Metodo:** POST
**Autenticacao:** Admin obrigatório
**Headers requeridos:** `X-CSRF-Token: {token}`
**Body esperado (JSON):**
```json
{
  "usuario_id": 5,
  "papel": "admin",
  "setores": [1, 2, 4, 7]
}
```

**Fluxo:**
1. Se `papel` fornecido: atualiza campo `papel` na tabela `usuarios`
2. `DELETE FROM permissoes WHERE usuario_id = :uid` — remove todas as permissoes antigas
3. Para cada setor: insere nova linha em `permissoes`
4. Retorna: `{ "ok": true }`

> **Nota arquitetural:** A atualizacao de permissoes e sempre destrutiva + reinserecao. Nao ha atualizacao parcial.

---

### api/webhook-proxy.php

**Metodo:** POST
**Autenticacao:** Requerida
**Headers requeridos:** `X-CSRF-Token: {token}`
**Body:** Qualquer JSON valido (repassado ao webhook Make.com)

**Fluxo:**
1. Rate limit: 3 envios por minuto por usuario
2. Valida CSRF token
3. Le body da requisicao
4. Monta requisicao CURL para `https://hook.us1.make.com/mljmh1yxoio8sxtdgqo4figgzucevedg`
5. Repassa o mesmo JSON recebido
6. Retorna `{ "ok": true }` ou erro HTTP

> **IMPORTANTE para agentes:** A URL do webhook Make.com esta hardcoded neste arquivo. Se o webhook mudar, editar aqui. O frontend nunca ve esta URL.

---

## 5. FRONTEND — ARQUIVOS JAVASCRIPT

### assets/js/auth.js

Este arquivo deve ser incluido (via `<script>`) em **toda pagina protegida** (qualquer pagina que nao seja login.html).

**O que faz:**
1. Detecta o `basePath` via atributo `data-base` na tag `<body>` ou `<html>`
   - Profundidade 0 (raiz): `data-base="./"`
   - Profundidade 1 (setores/slug/): `data-base="../../"`
   - Profundidade 2 (setores/slug/paginas/): `data-base="../../../"`
2. Faz `fetch` para `{basePath}api/auth/check-session.php`
3. Se nao autenticado (401 ou `logado === false`): redireciona para `login.html`
4. Se autenticado:
   - Salva `csrf_token` em `sessionStorage['csrfToken']`
   - Adiciona classe `ready` ao elemento `#pageShell` (dispara fade-in via CSS)
   - Expoe dados do usuario em `window.currentUser`

**Como usar em novas paginas:**
```html
<body data-base="../../">  <!-- ajustar profundidade -->
  <div id="pageShell">
    <!-- conteudo da pagina -->
  </div>
  <script src="../../assets/js/auth.js"></script>
</body>
```

---

### assets/js/components.js

Carrega os componentes de navegacao e rodape dinamicamente.

**O que faz:**
1. Detecta `basePath` igual ao auth.js
2. Faz `fetch` de `{basePath}components/nav.html`
3. Injeta HTML resultante em `#nav-placeholder`
4. Faz `fetch` de `{basePath}components/footer.html`
5. Injeta em `#footer-placeholder`
6. Dispara evento customizado: `document.dispatchEvent(new Event('components:loaded'))`

**Como usar em novas paginas:**
```html
<div id="nav-placeholder"></div>
<!-- conteudo -->
<div id="footer-placeholder"></div>
<script src="../../assets/js/components.js"></script>
```

> **Dependencia:** nav.js e utils.js escutam `'components:loaded'` antes de agir. Sempre inclua components.js antes desses dois.

---

### assets/js/nav.js

Controla o dropdown do menu de setores na navegacao.

**O que faz:**
1. Espera pelo evento `'components:loaded'`
2. Adiciona listener de click no botao `[data-dropdown-toggle]`
3. Toggle da classe `open` no `.nav-dropdown`
4. Fecha dropdown ao clicar fora (listener no `document`)

---

### assets/js/utils.js

Utilitarios gerais de UI.

**O que faz:**
1. Espera pelo evento `'components:loaded'`
2. Le preferencia de tema do `localStorage['theme']` (default: 'light')
3. Aplica classe `dark` no `<body>` se tema for 'dark'
4. Adiciona listener no botao `[data-theme-toggle]` para alternar entre light/dark
5. Para links `href="#"`: exibe um `alert()` generico

---

## 6. FRONTEND — ESTILOS CSS

### assets/css/hub.css — Design System Global

#### Variaveis de tema (CSS Custom Properties)

```css
:root {  /* Tema claro (padrao) */
  --bg: #f7fbf8;
  --surface: #ffffff;
  --text: #173d34;
  --text-muted: #5a7a70;
  --border: #d1e8dc;
  --green-start: #13ab25;
  --green-end: #003d32;
  --shadow: 0 24px 60px rgba(0, 61, 50, .12);
  --radius: 16px;
  --radius-xl: 28px;
  --max-width: 1180px;
  --transition: 0.25s ease;
}

body.dark {  /* Tema escuro */
  --bg: #08110d;
  --surface: #0f1f16;
  --text: #eef8f1;
  --text-muted: #7ab892;
  --border: #1e3d2a;
  --green-start: #1ed760;
  --green-end: #00b050;
  --shadow: 0 24px 60px rgba(0, 0, 0, .4);
}
```

#### Classes principais

| Classe | Descricao |
|--------|-----------|
| `.page` | Container principal com `opacity: 0`. Quando `#pageShell` ganha classe `ready`, dispara fade-in |
| `.top-nav` | Barra de navegacao fixa (sticky) com `backdrop-filter: blur(12px)` |
| `.nav-pill` | Pastilha centralizada dentro da nav com links e dropdown |
| `.nav-dropdown` | Submenu oculto por padrao; fica visivel com `.nav-dropdown.open` |
| `.sector-card` | Card de setor no dashboard; tem icone, texto e seta (→) |
| `.hero` | Secao de cabecalho com gradiente e nome do setor |
| `.tools` | Grid de 2 colunas para ferramentas dentro de um setor |
| `.tool` | Card de ferramenta individual com titulo, descricao, chips e botao |
| `.chip` | Tag/badge de categoria (ex: "CLT", "Tributario") |
| `.btn` | Botao padrao com gradiente verde (--green-start → --green-end) |
| `.search-panel` | Painel com input de busca e icone de lupa |

#### Responsividade

Media queries definidas:
- `max-width: 1000px` — grid de ferramentas passa para 1 coluna
- `max-width: 900px` — nav colapsa
- `max-width: 680px` — ajustes gerais de padding
- `max-width: 560px` — mobile: hero e cards empilham verticalmente

---

### assets/css/components.css — Componentes Reutilizaveis

| Classe | Descricao |
|--------|-----------|
| `.back` | Botao voltar (← Voltar) padronizado |
| `.sector-list` | Grid de setores na home |
| `.kpi-card` | Card de metrica com valor grande, label e status |
| `.kpi-green` | Modificador: status positivo (verde) |
| `.kpi-amber` | Modificador: status de atencao (amarelo) |
| `.kpi-red` | Modificador: status negativo (vermelho) |
| `.panel` | Painel com borda, fundo de superficie e padding |
| `.input-grid` | Grid de 3 colunas para campos de formulario (responsivo) |
| `.calc-grid` | Grid para calculadoras (2 colunas) |
| `.chip-status` | Badge de status (ex: "Em desenvolvimento") |

---

## 7. COMPONENTES HTML

### components/nav.html

```html
<!-- Estrutura logica do componente -->
<nav class="top-nav">
  <div class="nav-pill">
    <a href="{basePath}index.html">Logo</a>
    <a href="{basePath}index.html">Inicio</a>
    <button data-dropdown-toggle>Setores ▼</button>
    <div class="nav-dropdown">
      <!-- Links para os 9 setores -->
      <a href="{basePath}setores/comercial/index.html">🤝 Comercial</a>
      <a href="{basePath}setores/contabil/index.html">📘 Contabil</a>
      <!-- ... todos os 9 setores ... -->
    </div>
    <a href="https://wa.me/...">Sugestao</a>
    <button data-theme-toggle>☀️/🌙</button>
  </div>
</nav>
```

> **Atencao para agentes:** Os hrefs dentro do nav.html usam caminhos relativos a raiz. Se a navegacao estiver quebrada em paginas mais profundas, verificar se `data-base` esta correto no HTML da pagina.

### components/footer.html

```html
<footer>
  <p>Central interna de ferramentas para uso exclusivo da equipe.</p>
</footer>
```

---

## 8. PAGINAS PRINCIPAIS

### index.html — Dashboard Principal

**Autenticacao:** Requerida (auth.js redireciona para login.html se nao logado)
**basePath:** `./` (raiz)

**Fluxo ao carregar:**
1. auth.js verifica sessao → armazena CSRF em sessionStorage
2. Faz `fetch('api/setores/get.php')` com credenciais de sessao
3. Renderiza grid de cards para cada setor retornado
4. Exibe barra de busca que filtra cards em tempo real (JS puro)
5. Botao de logout faz POST para `api/auth/logout.php` com CSRF e redireciona

**Estrutura HTML:**
```html
<body data-base="./">
  <div id="nav-placeholder"></div>
  <div id="pageShell" class="page">
    <section class="hero">...</section>
    <div class="search-panel">...</div>
    <div id="setores-grid"><!-- preenchido pelo JS --></div>
  </div>
  <div id="footer-placeholder"></div>
</body>
```

---

### login.html

**Autenticacao:** Nao requerida (verifica se ja esta logado e redireciona)

**Fluxo:**
1. Verifica se ja esta logado (check-session.php) → redireciona para index.html se sim
2. Exibe formulario com campos email e senha
3. Submit faz POST para `api/auth/login.php` com JSON
4. Em caso de sucesso: armazena CSRF em sessionStorage, redireciona para index.html
5. Em caso de erro: exibe mensagem de erro abaixo do formulario
6. Rate limit 429: exibe "Muitas tentativas. Aguarde 5 minutos."

---

### 404.html

Pagina de erro personalizada. Lista todos os 9 setores como links de navegacao alternativa. Nao requer autenticacao.

---

## 9. PAGINAS DE SETORES

Cada setor tem a mesma estrutura base em `setores/{slug}/index.html`:

```html
<body data-base="../../">
  <div id="nav-placeholder"></div>
  <div id="pageShell" class="page">
    <a class="back" href="../../index.html">← Voltar</a>
    <section class="hero">
      <span class="hero-icon">{emoji}</span>
      <h1>{nome do setor}</h1>
      <p>{descricao}</p>
    </section>
    <div class="tools">
      <div class="tool">
        <h3>{titulo da ferramenta}</h3>
        <p>{descricao}</p>
        <div class="chips">
          <span class="chip">{categoria}</span>
        </div>
        <!-- Estado A: funcional -->
        <a href="paginas/{ferramenta}.html" class="btn">Acessar</a>
        <!-- Estado B: em desenvolvimento -->
        <span class="chip-status">Em desenvolvimento</span>
        <!-- Estado C: link externo -->
        <a href="{url-externa}" target="_blank" class="btn">Acessar</a>
      </div>
    </div>
  </div>
  <div id="footer-placeholder"></div>
  <script src="../../assets/js/auth.js"></script>
  <script src="../../assets/js/components.js"></script>
  <script src="../../assets/js/nav.js"></script>
  <script src="../../assets/js/utils.js"></script>
</body>
```

---

## 10. FERRAMENTAS IMPLEMENTADAS — DOCUMENTACAO DETALHADA

### 10.1 Calculadora CLT (setores/rh/paginas/calculadora-clt.html)

**Proposito:** Calcular custo total de um funcionario CLT para a empresa, incluindo salario bruto, descontos (INSS, IRRF), encargos patronais (FGTS) e beneficios (13o, ferias).

**Inputs:**
- Salario bruto (R$)
- Numero de dependentes (para calculo do IRRF)
- Meses de calculo (1-12)

**Tabs de resultado:**
1. **Mensal**: INSS, IRRF, FGTS, Salario liquido
2. **Decimo Terceiro**: Calculo proporcional com encargos
3. **Ferias**: 1/3 constitucional + encargos
4. **Anual**: Soma de 12 meses + 13o + ferias

**Tabela INSS 2026 (aliquotas progressivas):**
| Faixa | Aliquota |
|-------|----------|
| Ate R$ 1.412,00 | 7,5% |
| R$ 1.412,01 a R$ 2.666,68 | 9% |
| R$ 2.666,69 a R$ 4.000,03 | 12% |
| R$ 4.000,04 a R$ 7.786,02 | 14% |

**Tabela IRRF 2026:**
| Base de calculo | Aliquota | Deducao |
|----------------|---------|--------|
| Ate R$ 2.259,20 | Isento | - |
| R$ 2.259,21 a R$ 2.826,65 | 7,5% | R$ 169,44 |
| R$ 2.826,66 a R$ 3.751,05 | 15% | R$ 381,44 |
| R$ 3.751,06 a R$ 4.664,68 | 22,5% | R$ 662,77 |
| Acima de R$ 4.664,68 | 27,5% | R$ 896,00 |

**Deducao por dependente IRRF 2026:** R$ 189,59/dependente

**Graficos gerados (Chart.js):**
- Grafico de barras: comparativo mensal (bruto vs. liquido vs. encargos)
- Grafico anual: distribuicao ao longo dos meses

**Exportacao PDF:**
- Botao "Exportar PDF" faz POST para `api/gerar-pdf-clt.php` com todos os dados calculados + graficos como `data:image/png;base64,...`
- Abre nova aba com HTML printavel (sem barra de navegacao, estilo de impressao)
- Usuario usa Ctrl+P para imprimir/salvar como PDF

---

### 10.2 Calculadora de Projecao Tributaria (setores/comercial/paginas/calculadora-projecao.html)

**Proposito:** Simular e comparar o custo tributario de uma empresa PJ em diferentes regimes (Simples Nacional por anexo, Lucro Presumido, Lucro Real) para identificar o regime mais vantajoso.

**Inputs:**
- Faturamento mensal (R$)
- Distribuicao percentual por atividade (Anexo I, II, III, V, Eco)
- Valor do pro-labore dos socios

**Outputs:**
- **4 cards de regime** com:
  - Nome do regime (ex: "Simples Nacional — Anexo III")
  - Aliquota efetiva
  - Imposto total mensal (R$)
  - Badge de recomendacao (verde/ambar/vermelho)
- **KPIs:** Regime recomendado, economia mensal vs. pior opcao, aliquota efetiva
- **Fator R:** Calculo automatico (pro-labore / receita bruta * 100)
- **Graficos:** Comparativo de carga tributaria por regime

**Calculo do Fator R:**
```
Fator R = (pro-labore + encargos) / receita bruta * 100
Se Fator R >= 28%: empresa pode usar Anexo III (menor tributacao para servicos)
Se Fator R < 28%: empresa enquadra no Anexo V (maior tributacao)
```

**Exportacao PDF:**
- Similar ao CLT: POST para `api/gerar-pdf.php` com dados + graficos base64

---

### 10.3 Calculadora de Pro-Labore (setores/rh/paginas/calculadora-pro-labore.html)

**Proposito:** Calcular os encargos sobre o pro-labore dos socios de uma empresa PJ.

**Inputs:**
- Valor bruto do pro-labore (R$)
- Numero de dependentes

**Outputs:**
- INSS patronal (20% sobre pro-labore)
- INSS desconto do socio (11% — aliquota fixa para pro-labore)
- IRRF (tabela progressiva 2026)
- Liquido para o socio

> **Diferenca CLT vs. Pro-Labore:** No pro-labore, o INSS do socio e 11% flat (sem progressividade). A empresa paga 20% patronal. Nao ha FGTS, 13o nem ferias obrigatorios para socios.

---

### 10.4 Formulario de Contratos (setores/sucesso-do-cliente/paginas/formulario-de-contratos.html)

**Proposito:** Padronizar a captura de dados de novos contratos de clientes e integrar automaticamente com fluxos externos (Make.com).

**Campos do formulario:**
- Nome do cliente
- Email do cliente
- Servicos contratados (checkboxes multiplos)
- Periodo de vigencia (data inicio / data fim)
- Valor mensal
- Observacoes adicionais

**Fluxo de envio:**
1. Usuario preenche o formulario e clica "Enviar"
2. JS coleta dados e monta objeto JSON
3. POST para `api/webhook-proxy.php` com header `X-CSRF-Token`
4. Backend valida CSRF, aplica rate limit (3/min) e repassa para Make.com
5. Make.com processa: pode criar tarefa, enviar email, preencher planilha, etc.
6. Frontend exibe confirmacao visual de envio

---

## 11. GERACAO DE RELATORIOS PDF

Os relatorios PDF funcionam via impressao do navegador, nao via biblioteca PDF. O fluxo e:

1. Usuario clica "Exportar PDF"
2. Frontend coleta todos os dados calculados + captura graficos como base64 (via `canvas.toDataURL('image/png')`)
3. POST para `api/gerar-pdf-clt.php` ou `api/gerar-pdf.php` com JSON
4. Backend gera HTML completo com estilos inline + graficos embutidos
5. Frontend abre HTML em nova aba (`window.open`)
6. HTML tem `@media print` configurado para impressao perfeita
7. Usuario Ctrl+P → "Salvar como PDF"

**Vantagens desta abordagem:**
- Zero dependencias de biblioteca PDF
- Facil customizacao via CSS
- Graficos Chart.js incluidos naturalmente
- Funciona em qualquer navegador moderno

**Desvantagem:**
- Requer interacao do usuario para salvar (nao e download automatico)

---

## 12. SEGURANCA — CAMADAS IMPLEMENTADAS

### 12.1 Autenticacao e Sessoes

- Senhas hasheadas com `PASSWORD_BCRYPT` (custo padrao 10)
- `session_regenerate_id(true)` no login (previne session fixation)
- Flags de sessao: `httponly`, `secure`, `samesite=Strict`
- Verificacao de sessao em toda requisicao autenticada

### 12.2 CSRF Protection

- Token de 32 bytes aleatorios (`random_bytes`)
- Enviado no corpo da resposta de login
- Armazenado em `sessionStorage` (nao acessivel por outros dominios)
- Validado via header `X-CSRF-Token` com `hash_equals` (timing-safe)
- Obrigatorio em todos os POST endpoints autenticados

### 12.3 Rate Limiting

- Implementado via arquivo em `/tmp/ratelimit/`
- Login: 5 tentativas / 5 minutos por IP
- Webhook: 3 envios / 1 minuto por usuario
- Retorna HTTP 429 com mensagem amigavel

### 12.4 SQL Injection

- Todos os queries usam `PDO::prepare` + `bindParam`/`execute` com array
- Zero concatenacao de strings em queries

### 12.5 Headers HTTP de Seguranca (.htaccess)

```apache
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-Content-Type-Options "nosniff"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net; ..."
```

### 12.6 Restricoes de Acesso (.htaccess)

- HTTPS forcado (redireciona HTTP → HTTPS)
- Acesso direto a `/tmp/` bloqueado
- Acesso direto a `config.php` bloqueado
- `install.php` e `criar-usuarios.php` bloqueados por .htaccess (devem ser deletados apos uso)

### 12.7 Autorizacao

- Dois niveis: `admin` e `usuario`
- Admin acessa tudo (todos os setores, gestao de usuarios)
- Usuario ve apenas setores com permissao na tabela `permissoes`
- Verificacao feita no backend, nao apenas no frontend

---

## 13. INSTALACAO DO SISTEMA

### Prerequisitos

- Servidor Apache com PHP 7.x+ e MySQL
- mod_rewrite habilitado
- Extensao PDO e PDO_MySQL habilitadas
- HTTPS configurado (obrigatorio pelas flags de sessao)

### Passos de instalacao

1. **Upload dos arquivos** para o servidor (exceto config.php — criar manualmente)

2. **Criar config.php** em `/api/config.php` com as credenciais reais:
   ```php
   <?php
   $host = 'localhost';
   $db   = 'nome_do_banco';
   $user = 'usuario_bd';
   $pass = 'senha_bd';
   // resto das funcoes de config...
   ```

3. **Criar banco de dados** e usuario MySQL com permissoes de SELECT, INSERT, UPDATE, DELETE

4. **Executar install.php** acessando `https://seudominio.com/db/install.php` uma unica vez
   - Cria tabelas
   - Insere 9 setores
   - Cria usuario admin (Tarcisio Miranda)

5. **Executar criar-usuarios.php** para criar os 7 usuarios padrao

6. **DELETAR** `db/install.php` e `db/criar-usuarios.php` do servidor imediatamente

7. **Verificar .htaccess** — garantir que mod_rewrite esta ativo e os headers de seguranca estao sendo aplicados

8. **Testar login** com email `tarcisiovmiranda00@gmail.com` e senha padrao

---

## 14. USUARIOS PRE-CADASTRADOS

> **ATENCAO:** Estas senhas sao as do script de instalacao inicial. Devem ser trocadas apos o primeiro acesso.

| Nome | Email | Papel | Setores |
|------|-------|-------|---------|
| Tarcisio Miranda | tarcisiovmiranda00@gmail.com | admin | Todos (9) |
| Renan | renan@contabilrh.com.br | usuario | Todos (9) |
| Joao Kleber | joao.kleber@contabilrh.com.br | usuario | Todos (9) |
| Ailla | ailla@contabilrh.com.br | usuario | Todos (9) |
| Bruna | bruna@contabilrh.com.br | usuario | Todos (9) |
| Yasmin | yasmin@contabilrh.com.br | usuario | Todos (9) |
| Gustavo | gustavo@contabilrh.com.br | usuario | Todos (9) |
| Melissa | melissa@contabilrh.com.br | usuario | Todos (9) |

Senha inicial de todos: `#11218192Jacare#`

---

## 15. PADROES E CONVENCOES DO PROJETO

### Convencoes de nomes

- Arquivos PHP: `kebab-case.php`
- Arquivos HTML: `kebab-case.html`
- Classes CSS: `kebab-case`
- Variaveis PHP: `snake_case`
- Variaveis JS: `camelCase`
- Constantes PHP: `UPPER_SNAKE_CASE`
- Slugs de setor: `kebab-case` (ex: `sucesso-do-cliente`)
- Idioma: Portugues brasileiro em tudo (interface, codigo, comentarios)

### Convencoes de API

- Todos os endpoints em `/api/`
- Respostas sempre em JSON
- Erros retornam objeto `{ "erro": "mensagem" }` com HTTP adequado
- Sucesso retorna objeto `{ "ok": true, ...dados }` com HTTP 200
- Autenticacao via sessao (nao JWT ou token Bearer)
- CSRF via header `X-CSRF-Token`

### Convencoes de frontend

- Toda pagina protegida inclui `auth.js` como ultimo `<script>` antes de fechar `</body>`
- `components.js` e sempre incluido antes de `nav.js` e `utils.js`
- Atributo `data-base` na tag `<body>` indica profundidade relativa a raiz
- IDs de elementos criticos: `#pageShell`, `#nav-placeholder`, `#footer-placeholder`

### Padrao de novo setor

Para adicionar um novo setor funcional:
1. Inserir linha em `setores` no banco: `INSERT INTO setores (slug, nome, icone) VALUES (...)`
2. Criar pasta `setores/{slug}/`
3. Criar `setores/{slug}/index.html` seguindo o template de outros setores
4. Criar `setores/{slug}/paginas/` para futuras ferramentas
5. Adicionar link no `components/nav.html`

### Padrao de nova ferramenta

Para adicionar uma nova ferramenta a um setor existente:
1. Criar `setores/{slug}/paginas/{nome-ferramenta}.html`
2. Incluir `auth.js`, `components.js`, `nav.js`, `utils.js` com `data-base` correto (`../../../`)
3. Atualizar `setores/{slug}/index.html` para linkar a nova ferramenta (remover chip "Em desenvolvimento")
4. Se precisar de backend: criar `setores/{slug}/paginas/api/{endpoint}.php`

---

## 16. FUTURAS ATUALIZACOES — BACKLOG E RECOMENDACOES

### Alta prioridade (ferramentas em andamento)

#### RH — Controle de Folha
- Gestao de multiplos funcionarios
- Calculo coletivo de folha mensal
- Exportacao de relatorio consolidado
- Historico de folhas anteriores

#### Comercial — CRM de Oportunidades
- Cadastro de leads e oportunidades
- Pipeline visual (Kanban ou lista)
- Status: Prospecao → Reuniao → Proposta → Fechado
- Integracao com formulario de contratos (Sucesso do Cliente)

#### Comercial — Painel de Metas
- Metas mensais por vendedor
- Progresso visual (barra de progresso)
- Comparativo periodo atual vs. anterior

#### Fiscal — Calendario de Obrigacoes
- Calendario de vencimentos de obrigacoes fiscais
- Filtro por empresa/cliente
- Alertas de prazo
- Integracao com sistema externo de gestao

#### Fiscal — Monitor de Pendencias
- Lista de obrigacoes pendentes
- Status: Pendente / Em andamento / Concluido
- Ordenacao por prazo

### Media prioridade (setores vazios)

#### Contabil
- **Fechamento Mensal:** Checklist de tarefas de fechamento por cliente
- **Central de Balancetes:** Upload e visualizacao de balancetes
- **Conciliacao Bancaria:** Ferramenta de conciliacao de extratos

#### Financeiro
- **Fluxo de Caixa:** Projecao de entradas e saidas
- **Central de Cobranca:** Gestao de clientes inadimplentes
- **Contas a Pagar:** Registro e controle de pagamentos

#### Marketing
- **Calendario Editorial:** Planejamento de conteudo
- **Biblioteca de Materiais:** Repositorio de artes e templates
- **Dashboard de Campanhas:** Metricas de redes sociais

#### Paralegal
- **Protocolos e Junta:** Controle de protocolos na Junta Comercial
- **Modelos Contratuais:** Biblioteca de modelos de contrato
- **Controle de Exigencias:** Acompanhamento de exigencias de orgaos

#### Certificado Digital
- **Agenda de Renovacoes:** Clientes com certificado proximo ao vencimento
- **Checklist Documental:** Documentos necessarios por tipo de certificado
- **Portal de Emissao:** Status de emissoes em andamento

#### Sucesso do Cliente
- **Saude da Carteira:** Score de saude por cliente
- **Central de Follow-up:** Lembretes e agendamentos de contato

### Melhorias tecnicas recomendadas

#### Seguranca
- Adicionar 2FA (autenticacao de dois fatores) para o admin
- Implementar refresh de CSRF token automatico
- Migrar rate limiting de arquivo para Redis ou banco de dados
- Audit log: registrar todas as acoes administrativas

#### Performance
- Cache de resultados de setores/permissoes (Redis ou APCu)
- Minificacao de CSS e JS para producao
- Lazy loading de componentes pesados (graficos)

#### UX/UI
- Sistema de notificacoes in-app
- Painel administrativo de gestao de usuarios (interface web, sem precisar de SQL)
- Busca global entre ferramentas
- Favoritos por usuario
- Historico de calculos (salvar resultados anteriores)

#### Backend
- Adicionar endpoint `PATCH /api/usuarios/{id}/senha` para troca de senha
- Implementar recuperacao de senha por email
- Versionamento da API (`/api/v1/`)
- Logs estruturados de erros

#### Banco de dados
- Executar migration `001_add_permissoes.sql` (arquivo vazio, aguarda definicao)
- Indices adicionais para performance em tabelas com muitos registros
- Backup automatico diario

---

## 17. INTEGRACAO COM MAKE.COM

O sistema integra com Make.com (antigo Integromat) via webhook HTTP.

**Fluxo atual:**
```
Formulario (frontend)
  → api/webhook-proxy.php (validacao + rate limit)
    → Make.com webhook (https://hook.us1.make.com/mljmh1yxoio8sxtdgqo4figgzucevedg)
      → Automatizacao Make.com (ex: criar tarefa, enviar email, preencher planilha)
```

**Para expandir a integracao:**
- Criar novos endpoints em `/api/webhook-proxy.php` ou endpoints separados por funcionalidade
- Cada webhook Make.com tem URL propria
- O proxy seguro garante que as URLs de webhook nunca ficam expostas no frontend

---

## 18. GLOSSARIO DE TERMOS DO NEGOCIO

| Termo | Significado no contexto |
|-------|------------------------|
| PJ | Pessoa Juridica — regime de trabalho via empresa |
| CLT | Consolidacao das Leis do Trabalho — regime trabalhista formal |
| INSS | Instituto Nacional do Seguro Social — contribuicao previdenciaria |
| IRRF | Imposto de Renda Retido na Fonte |
| FGTS | Fundo de Garantia do Tempo de Servico |
| Pro-Labore | Remuneracao dos socios de uma empresa |
| Simples Nacional | Regime tributario simplificado para pequenas empresas |
| Lucro Presumido | Regime tributario baseado em margem presumida de lucro |
| Lucro Real | Regime tributario baseado no lucro efetivo |
| Anexo I-V | Categorias de atividade do Simples Nacional com aliquotas diferentes |
| Fator R | Indice que determina o enquadramento no Simples (pro-labore / receita) |
| Make.com | Plataforma de automacao de fluxos de trabalho (anterior: Integromat) |
| Hub | Nome dado ao portal central de ferramentas internas |

---

## 19. ARQUIVOS QUE NAO ESTAO NO GIT

Os seguintes arquivos existem no servidor mas estao no `.gitignore`:

| Arquivo | Motivo |
|---------|--------|
| `api/config.php` | Contem credenciais do banco de dados |
| `db/install.php` | Script critico — deve ser deletado apos uso |
| `db/criar-usuarios.php` | Script critico — deve ser deletado apos uso |
| `tmp/ratelimit/*.json` | Dados temporarios de rate limiting |
| `*.log` | Logs de erro |

> **Para agentes:** Se precisar reconstruir o ambiente, solicite ao administrador (`gustavo@contabilrh.com.br`) as credenciais do banco e recreie o `config.php` seguindo a estrutura documentada na secao 4.

---

## 20. CHECKLIST PARA NOVOS AGENTES

Antes de fazer qualquer alteracao no projeto, confirme:

- [ ] Voce leu e entendeu este documento completo
- [ ] Voce sabe qual setor e ferramenta esta modificando
- [ ] Voce verificou se `config.php` existe no servidor (backend nao funciona sem ele)
- [ ] Voce entende o sistema de `data-base` para caminhos relativos
- [ ] Novas paginas PHP incluem `require_once '../../config.php'` (ajustar nivel)
- [ ] Novas paginas PHP chamam `require_auth()` se precisam de autenticacao
- [ ] Novos endpoints POST validam CSRF com `csrf_validate()`
- [ ] Queries novas usam prepared statements (nunca concatenacao de strings)
- [ ] Novos campos HTML de formulario tem validacao no backend, nao apenas no frontend
- [ ] CSS novo usa as variaveis CSS existentes (`--bg`, `--text`, etc.) em vez de cores hardcoded
- [ ] O tema escuro foi testado com as alteracoes visuais
- [ ] A pagina nova foi testada em mobile (max-width: 560px)

---

*Fim da documentacao. Ultima atualizacao: 2026-04-27.*
