<?php
// ============================================================
// gerar-pdf.php — Gera relatorio de projecao tributaria
// Recebe JSON via POST, retorna HTML printavel
// ============================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metodo nao permitido');
}

$raw = file_get_contents('php://input');
if (!$raw) { http_response_code(400); exit('Payload vazio'); }

$data = json_decode($raw, true);
if (!$data) { http_response_code(400); exit('JSON invalido'); }

function s($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$dataGeracao = date('d/m/Y H:i');
$faturamento = s($data['faturamento'] ?? '—');
$regime      = s($data['regime'] ?? '—');
$verdict     = s($data['verdict'] ?? '');

$kpis        = $data['kpis']      ?? [];
$listIIIfr   = $data['listIIIfr'] ?? [];
$listIII     = $data['listIII']   ?? [];
$listV       = $data['listV']     ?? [];
$listEco     = $data['listEco']   ?? [];
$resumoItems = $data['resumo']    ?? [];
$chartIIIfr  = $data['chartIIIfr'] ?? '';
$chartIII    = $data['chartIII']   ?? '';
$chartV      = $data['chartV']     ?? '';

function validarDataUrl($url) {
    return preg_match('/^data:image\/(png|jpeg|gif|webp);base64,[A-Za-z0-9+\/=]+$/', $url);
}
if ($chartIIIfr && !validarDataUrl($chartIIIfr)) $chartIIIfr = '';
if ($chartIII   && !validarDataUrl($chartIII))   $chartIII   = '';
if ($chartV     && !validarDataUrl($chartV))     $chartV     = '';

function renderBadge($text) {
    $map = [
        'Recomendado'     => 'badge-green',
        'Alternativa'     => 'badge-amber',
        'Nao recomendado' => 'badge-red',
        'Não recomendado' => 'badge-red',
    ];
    $cls = $map[$text] ?? 'badge-amber';
    return '<span class="badge ' . $cls . '">' . s($text) . '</span>';
}

function renderRows($items) {
    $h = '';
    foreach ($items as $it) {
        $h .= '<div class="row"><span class="row-label">' . s($it['l'] ?? '') . '</span>'
            . '<span class="row-value">' . s($it['v'] ?? '') . '</span></div>';
    }
    return $h;
}

function panelClass($badge) {
    return $badge === 'Recomendado' ? 'panel best' : 'panel';
}

$LOGO_URL = 'https://pjcontrato.com.br/wp-content/uploads/2026/01/cropped-PJ-Logo-Verde.png';

header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Projeção Tributária — <?= $faturamento ?>/mês</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap');

    /* Forçar impressão de fundos coloridos */
    * {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
      color-adjust: exact !important;
    }

    body {
      font-family: 'Manrope', 'Segoe UI', Arial, sans-serif;
      font-size: 11px;
      color: #173d34;
      background: #fff;
      padding: 28px 32px;
      max-width: 900px;
      margin: 0 auto;
    }

    /* ===== HEADER ===== */
    .doc-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 14px;
      padding-bottom: 14px;
      border-bottom: 3px solid #13ab25;
    }
    .doc-title { font-size: 19px; font-weight: 800; line-height: 1.2; margin-bottom: 5px; color: #003d32; }
    .doc-meta  { font-size: 9.5px; color: #6f8780; }
    .logo-block {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .logo-block img { height: 52px; width: auto; display: block; }
    .logo-texts { text-align: right; }
    .logo-name  { font-size: 15px; font-weight: 800; color: #003d32; line-height: 1.2; }
    .logo-sub   { font-size: 9px; color: #6f8780; }

    /* ===== SEÇÕES ===== */
    .section-title {
      font-size: 13px;
      font-weight: 800;
      color: #003d32;
      margin: 18px 0 10px;
      padding-bottom: 5px;
      border-bottom: 1px solid #e0ebe5;
    }

    /* ===== KPIs ===== */
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 8px;
      margin-bottom: 6px;
    }
    .kpi {
      padding: 11px 13px;
      border: 1px solid #e0ebe5;
      border-left: 3px solid #13ab25;
      border-radius: 8px;
      background: #f7fbf8;
    }
    .kpi-label { font-size: 8px; font-weight: 800; text-transform: uppercase; color: #6f8780; }
    .kpi-value { font-size: 16px; font-weight: 800; margin: 3px 0 2px; color: #003d32; }
    .kpi-sub   { font-size: 8.5px; color: #6f8780; }

    /* ===== BADGES ===== */
    .badge {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 99px;
      font-size: 8px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .5px;
      margin-bottom: 7px;
    }
    .badge-green { background: #e8f7ec; color: #13ab25; }
    .badge-amber { background: #fff4dc; color: #d98208; }
    .badge-red   { background: #fdecea; color: #c0392b; }

    /* ===== PAINÉIS COMPARATIVOS ===== */
    .compare-grid-3 {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 10px;
      margin-bottom: 10px;
    }
    .panel {
      padding: 12px;
      border: 1px solid #e0ebe5;
      border-radius: 10px;
      background: #fff;
    }
    .panel.best {
      border: 2px solid #13ab25;
      background: #f7fbf8;
    }
    .panel-title {
      font-size: 11px;
      font-weight: 800;
      color: #003d32;
      margin-bottom: 8px;
    }
    .row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 5px 0;
      border-bottom: 1px solid #f0f5f2;
      font-size: 10px;
    }
    .row:last-of-type { border-bottom: none; }
    .row-label { color: #6f8780; }
    .row-value { font-weight: 800; color: #173d34; }

    /* ===== ECONOMIA ===== */
    .eco-panel { max-width: 440px; }

    /* ===== RESUMO ===== */
    .resumo-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 8px;
      margin-bottom: 8px;
    }
    .resumo-item {
      padding: 10px;
      border-radius: 8px;
      background: #f7fbf8;
      border-left: 3px solid #13ab25;
    }
    .resumo-item strong {
      display: block;
      font-size: 7.5px;
      font-weight: 800;
      text-transform: uppercase;
      color: #6f8780;
      margin-bottom: 4px;
    }
    .resumo-item span { font-size: 12px; font-weight: 800; color: #003d32; }

    /* ===== RESULTADO FINAL (sem quebra) ===== */
    .resultado-bloco { page-break-inside: avoid; break-inside: avoid; }
    .resultado-bloco .section-title { page-break-after: avoid; break-after: avoid; }

    /* ===== VEREDICTO ===== */
    .verdict {
      padding: 14px 16px;
      border: 1px dashed #13ab25;
      border-radius: 10px;
      background: #f7fbf8;
      line-height: 1.75;
      font-size: 10.5px;
      color: #4a6860;
      margin: 12px 0;
    }
    .verdict-title { font-weight: 800; color: #13ab25; display: block; margin-bottom: 4px; }

    /* ===== CTA ===== */
    .cta-block {
      margin: 22px 0 18px;
      border: 2px solid #13ab25;
      border-radius: 12px;
      background: #f7fbf8;
      page-break-inside: avoid;
      break-inside: avoid;
      overflow: hidden;
    }
    .cta-header {
      background: #13ab25;
      padding: 14px 20px;
      display: flex;
      align-items: center;
      gap: 14px;
    }
    .cta-header img {
      height: 40px;
      width: auto;
      display: block;
      flex-shrink: 0;
    }
    .cta-header-texts { flex: 1; }
    .cta-header h3 {
      font-size: 14px;
      font-weight: 800;
      color: #fff;
      margin-bottom: 2px;
      line-height: 1.2;
    }
    .cta-header p {
      font-size: 9px;
      color: rgba(255,255,255,.85);
    }
    .cta-body {
      padding: 16px 20px;
    }
    .cta-desc {
      font-size: 10.5px;
      line-height: 1.75;
      color: #4a6860;
      margin-bottom: 14px;
    }
    .cta-destaques {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
      margin-bottom: 14px;
    }
    .cta-item {
      display: flex;
      align-items: flex-start;
      gap: 8px;
      font-size: 10px;
      font-weight: 700;
      color: #003d32;
    }
    .cta-check {
      color: #13ab25;
      font-size: 12px;
      font-weight: 800;
      flex-shrink: 0;
      margin-top: 1px;
    }
    .cta-footer {
      border-top: 1px solid #d4edda;
      padding: 12px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #fff;
    }
    .cta-contato {
      font-size: 10px;
      color: #6f8780;
    }
    .cta-contato strong { color: #003d32; }
    .cta-footer img {
      height: 32px;
      width: auto;
      opacity: .7;
    }

    /* ===== RODAPÉ ===== */
    .doc-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 8px;
      color: #a0b8b0;
      margin-top: 16px;
      padding-top: 10px;
      border-top: 1px solid #e0ebe5;
    }
    .footer-logo { height: 28px; width: auto; opacity: .55; }

    /* ===== AVISO DE IMPRESSÃO (some no print) ===== */
    .print-notice {
      text-align: center;
      padding: 14px;
      background: #f0faf3;
      border: 1px solid #b7e7c3;
      border-radius: 10px;
      margin-bottom: 22px;
      font-size: 12px;
      color: #003d32;
    }
    .print-notice strong { display: block; font-size: 14px; margin-bottom: 4px; }
    .btn-imprimir {
      display: inline-block;
      margin-top: 10px;
      padding: 10px 24px;
      background: #13ab25;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 800;
      cursor: pointer;
      font-family: inherit;
    }
    .btn-imprimir:hover { background: #0f9120; }

    /* ===== PRINT ===== */
    @media print {
      body { padding: 0; max-width: 100%; }
      .print-notice { display: none !important; }
      .panel { break-inside: avoid; }
      .kpi-grid { break-inside: avoid; }
      .resumo-grid { break-inside: avoid; }
      .verdict { break-inside: avoid; }
      .cta-block { break-inside: avoid; }
      @page { margin: 14mm 12mm; size: A4 portrait; }
    }
    @media screen { body { padding-top: 16px; } }
  </style>
</head>
<body>

  <!-- Aviso de impressão (some no print) -->
  <div class="print-notice">
    <strong>Relatório pronto para exportar</strong>
    Clique em <b>Salvar como PDF</b> na janela de impressão do navegador.<br>
    Chrome/Edge: destino → <b>"Salvar como PDF"</b> &nbsp;|&nbsp; Firefox: <b>"Imprimir para arquivo"</b>
    <br>
    <button class="btn-imprimir" onclick="window.print()">🖨️ Abrir impressão / Salvar PDF</button>
  </div>

  <!-- HEADER com logo -->
  <div class="doc-header">
    <div>
      <div class="doc-title">Projeção Tributária — Simples Nacional</div>
      <div class="doc-meta">
        Faturamento projetado: <strong><?= $faturamento ?>/mês</strong> &nbsp;|&nbsp;
        Regime: <strong><?= $regime ?></strong> &nbsp;|&nbsp;
        Gerado em: <?= $dataGeracao ?>
      </div>
    </div>
    <div class="logo-block">
      <div class="logo-texts">
        <div class="logo-name">Contrato PJ</div>
        <div class="logo-sub">Serviços</div>
      </div>
      <img src="<?= $LOGO_URL ?>" alt="Contrato PJ Serviços" />
    </div>
  </div>

  <!-- KPIs -->
  <div class="section-title">Indicadores do cenário recomendado</div>
  <div class="kpi-grid">
    <?php foreach ($kpis as $k): ?>
    <div class="kpi">
      <div class="kpi-label"><?= s($k['l'] ?? '') ?></div>
      <div class="kpi-value"><?= s($k['v'] ?? '') ?></div>
      <div class="kpi-sub"><?= s($k['s'] ?? '') ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- COMPARATIVO -->
  <div class="section-title">Comparativo de cenários</div>
  <div class="compare-grid-3">

    <div class="<?= panelClass($data['badgeIIIfr'] ?? '') ?>">
      <?= renderBadge($data['badgeIIIfr'] ?? '') ?>
      <div class="panel-title">Anexo III (com Fator R)</div>
      <?= renderRows($listIIIfr) ?>
    </div>

    <div class="<?= panelClass($data['badgeIII'] ?? '') ?>">
      <?= renderBadge($data['badgeIII'] ?? '') ?>
      <div class="panel-title">Anexo III (sem Fator R)</div>
      <?= renderRows($listIII) ?>
    </div>

    <div class="<?= panelClass($data['badgeV'] ?? '') ?>">
      <?= renderBadge($data['badgeV'] ?? '') ?>
      <div class="panel-title">Anexo V</div>
      <?= renderRows($listV) ?>
    </div>

  </div>

  <!-- ECONOMIA -->
  <?php if (!empty($listEco)): ?>
  <div class="section-title">Economia potencial</div>
  <div class="eco-panel">
    <div class="panel"><?= renderRows($listEco) ?></div>
  </div>
  <?php endif; ?>

  <!-- RESULTADO FINAL + VEREDICTO -->
  <?php if (!empty($resumoItems) || $verdict): ?>
  <div class="resultado-bloco">
    <?php if (!empty($resumoItems)): ?>
    <div class="section-title">Resultado final</div>
    <div class="resumo-grid">
      <?php foreach ($resumoItems as $it): ?>
      <div class="resumo-item">
        <strong><?= s($it['l'] ?? '') ?></strong>
        <span><?= s($it['v'] ?? '') ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($verdict): ?>
    <div class="verdict">
      <span class="verdict-title">Conclusão</span>
      <?= $verdict ?>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- CTA -->
  <div class="cta-block">
    <div class="cta-header">
      <img src="<?= $LOGO_URL ?>" alt="Contrato PJ Serviços" />
      <div class="cta-header-texts">
        <h3>Quer uma análise feita por um especialista?</h3>
        <p>Agende uma reunião gratuita com a equipe da Contrato PJ Serviços</p>
      </div>
    </div>
    <div class="cta-body">
      <div class="cta-desc">
        Esta simulação é gerada automaticamente com base nos parâmetros do Simples Nacional.
        Para uma projeção personalizada — considerando sua atividade específica, histórico de faturamento
        e as melhores estratégias de distribuição de lucros — fale com nossos especialistas.
      </div>
      <div class="cta-destaques">
        <div class="cta-item"><span class="cta-check">✓</span> Análise personalizada sem custo</div>
        <div class="cta-item"><span class="cta-check">✓</span> Comparativo real do seu enquadramento</div>
        <div class="cta-item"><span class="cta-check">✓</span> Orientação sobre Fator R e pró-labore ideal</div>
        <div class="cta-item"><span class="cta-check">✓</span> Sigilo e compliance garantidos</div>
      </div>
    </div>
    <div class="cta-footer">
      <div class="cta-contato">
        <strong>contato@contabilrh.com.br</strong> &nbsp;|&nbsp; <strong>pjcontrato.com.br</strong>
      </div>
      <img src="<?= $LOGO_URL ?>" alt="Contrato PJ" />
    </div>
  </div>

  <!-- RODAPÉ com logo -->
  <div class="doc-footer">
    <span>Documento gerado automaticamente — Hub PJ / Contrato PJ Serviços</span>
    <img class="footer-logo" src="<?= $LOGO_URL ?>" alt="Contrato PJ" />
    <span>Este relatório não substitui análise profissional contábil</span>
  </div>

</body>
</html>
