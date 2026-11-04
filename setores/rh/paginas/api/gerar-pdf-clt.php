<?php
// ============================================================
// gerar-pdf-clt.php — Relatório CLT completo
// ============================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método não permitido'); }
$raw = file_get_contents('php://input');
if (!$raw) { http_response_code(400); exit('Payload vazio'); }
$data = json_decode($raw, true);
if (!$data) { http_response_code(400); exit('JSON inválido'); }

function s($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function cur($v) { return 'R$ ' . number_format((float)$v, 2, ',', '.'); }
function pct($v) { return number_format((float)$v * 100, 2, ',', '.') . '%'; }
function validImg($u) { return preg_match('/^data:image\/(png|jpeg);base64,[A-Za-z0-9+\/=]+$/', $u); }

$gross   = (float)($data['gross']   ?? 0);
$deps    = (int)  ($data['deps']    ?? 0);
$months  = (int)  ($data['months']  ?? 12);
$m       = $data['monthly']    ?? [];
$t       = $data['thirteenth'] ?? [];
$v       = $data['vacation']   ?? [];
$a       = $data['annual']     ?? [];
$detail  = $data['inssDetail'] ?? [];
$verdict = $data['verdict']    ?? '';
$cComp   = $data['chartComp']  ?? '';
$cAnual  = $data['chartAnual'] ?? '';
if (!validImg($cComp))  $cComp  = '';
if (!validImg($cAnual)) $cAnual = '';

$dataGer = date('d/m/Y H:i');
$LOGO    = 'https://pjcontrato.com.br/wp-content/uploads/2026/01/cropped-PJ-Logo-Verde.png';

function panelRows($items) {
    $h = '';
    foreach ($items as $it) {
        $l = s($it[0]); $v = s($it[1]); $bold = !empty($it[2]);
        $h .= '<div class="row' . ($bold ? ' bold' : '') . '"><span class="rl">' . $l . '</span><span class="rv">' . $v . '</span></div>';
    }
    return $h;
}

header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"/>
  <title>Relatório CLT — <?= cur($gross) ?>/mês</title>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    *{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
    @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap');
    body{font-family:'Manrope','Segoe UI',Arial,sans-serif;font-size:11px;color:#173d34;background:#fff;padding:28px 32px;max-width:960px;margin:0 auto;}

    /* AVISO */
    .notice{text-align:center;padding:14px;background:#f0faf3;border:1px solid #b7e7c3;border-radius:10px;margin-bottom:20px;font-size:12px;color:#003d32;}
    .notice strong{display:block;font-size:14px;margin-bottom:4px;}
    .btn-print{display:inline-block;margin-top:10px;padding:10px 24px;background:#13ab25;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:800;cursor:pointer;font-family:inherit;}

    /* HEADER */
    .header{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;padding-bottom:14px;border-bottom:3px solid #13ab25;}
    .h-title{font-size:18px;font-weight:800;color:#003d32;margin-bottom:4px;}
    .h-meta{font-size:9px;color:#6f8780;}
    .logo-block{display:flex;align-items:center;gap:8px;}
    .logo-block img{height:48px;width:auto;}
    .logo-txt{text-align:right;}
    .logo-name{font-size:14px;font-weight:800;color:#003d32;}
    .logo-sub{font-size:9px;color:#6f8780;}

    /* SECTION TITLE */
    .sec{font-size:12px;font-weight:800;color:#003d32;margin:16px 0 8px;padding-bottom:4px;border-bottom:1px solid #e0ebe5;}

    /* KPI GRID */
    .kpi-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:10px;}
    .kpi{padding:10px 12px;border:1px solid #e0ebe5;border-left:3px solid #13ab25;border-radius:8px;background:#f7fbf8;}
    .kpi-lbl{font-size:7.5px;font-weight:800;text-transform:uppercase;color:#6f8780;}
    .kpi-val{font-size:15px;font-weight:800;color:#003d32;margin:2px 0;}
    .kpi-sub{font-size:8px;color:#6f8780;}
    .kpi-amber .kpi-val{color:#d98208;}
    .kpi-red   .kpi-val{color:#c0392b;}
    .kpi-blue  .kpi-val{color:#1a6cb5;}
    .kpi-green .kpi-val{color:#13ab25;}

    /* CALC PANELS */
    .panels-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:12px;}
    .panel{border:1px solid #e0ebe5;border-radius:10px;background:#fff;overflow:hidden;}
    .panel-hdr{background:#f7fbf8;padding:8px 12px;font-size:10px;font-weight:800;color:#003d32;border-bottom:1px solid #e0ebe5;}
    .row{display:flex;justify-content:space-between;padding:6px 12px;border-bottom:1px solid #f5f9f6;font-size:10px;}
    .row:last-child{border-bottom:none;}
    .row.bold{font-weight:800;background:#f0faf3;}
    .rl{color:#6f8780;}
    .rv{font-weight:700;}

    /* INSS TABLE */
    .tbl{width:100%;border-collapse:collapse;font-size:9.5px;margin-bottom:6px;}
    .tbl th{text-align:left;padding:6px 8px;font-size:8px;font-weight:800;text-transform:uppercase;color:#6f8780;border-bottom:2px solid #e0ebe5;}
    .tbl td{padding:7px 8px;border-bottom:1px solid #f0f5f2;}
    .tbl tr.active td{background:#f7fbf8;font-weight:700;color:#003d32;}
    .tbl tr.active td:first-child{border-left:3px solid #13ab25;}
    .tbl tr.inactive{opacity:.4;}
    .tbl tfoot td{padding:8px;font-weight:800;border-top:2px solid #e0ebe5;}

    /* CHARTS */
    .charts-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;}
    .chart-panel{border:1px solid #e0ebe5;border-radius:10px;padding:10px;background:#fff;}
    .chart-panel p{font-size:9px;font-weight:800;color:#6f8780;margin-bottom:6px;text-transform:uppercase;}
    .chart-panel img{width:100%;height:auto;display:block;}

    /* RESUMO ANUAL */
    .resumo-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:10px;}
    .res-item{padding:10px;border-radius:8px;background:#f7fbf8;border-left:3px solid #13ab25;}
    .res-item strong{display:block;font-size:7.5px;font-weight:800;text-transform:uppercase;color:#6f8780;margin-bottom:3px;}
    .res-item span{font-size:13px;font-weight:800;color:#003d32;}
    .res-item.blue{border-left-color:#1a6cb5;}
    .res-item.blue span{color:#1a6cb5;}
    .res-item.amber span{color:#d98208;}
    .res-item.purple span{color:#7b3fa0;}

    /* VERDICT */
    .verdict{padding:12px 16px;border:1px dashed #13ab25;border-radius:10px;background:#f7fbf8;line-height:1.75;font-size:10px;color:#4a6860;margin:12px 0;}
    .v-title{font-weight:800;color:#13ab25;display:block;margin-bottom:3px;}

    /* CTA */
    .cta{margin:16px 0;border:2px solid #13ab25;border-radius:12px;overflow:hidden;}
    .cta-hdr{background:#13ab25;padding:12px 18px;display:flex;align-items:center;gap:12px;}
    .cta-hdr img{height:36px;width:auto;}
    .cta-hdr h3{font-size:13px;font-weight:800;color:#fff;margin-bottom:1px;}
    .cta-hdr p{font-size:8.5px;color:rgba(255,255,255,.85);}
    .cta-body{padding:12px 18px;background:#f7fbf8;}
    .cta-desc{font-size:10px;color:#4a6860;line-height:1.7;margin-bottom:10px;}
    .cta-bullets{display:grid;grid-template-columns:1fr 1fr;gap:6px;}
    .cta-b{font-size:9.5px;font-weight:700;color:#003d32;}
    .cta-b::before{content:"✓ ";color:#13ab25;font-weight:800;}
    .cta-foot{border-top:1px solid #d4edda;padding:10px 18px;display:flex;justify-content:space-between;align-items:center;background:#fff;}
    .cta-contact{font-size:9.5px;color:#6f8780;}
    .cta-contact strong{color:#003d32;}
    .cta-foot img{height:28px;opacity:.65;}

    /* FOOTER */
    .footer{display:flex;justify-content:space-between;align-items:center;font-size:8px;color:#a0b8b0;margin-top:16px;padding-top:10px;border-top:1px solid #e0ebe5;}
    .footer img{height:26px;opacity:.5;}

    @media print{
      body{padding:0;max-width:100%;}
      .notice{display:none!important;}
      .panel,.kpi,.res-item,.cta{break-inside:avoid;}
      @page{margin:14mm 12mm;size:A4 portrait;}
    }
    @media screen{body{padding-top:16px;}}
  </style>
</head>
<body>

<div class="notice">
  <strong>Relatório CLT pronto para exportar</strong>
  Chrome/Edge: destino → <b>Salvar como PDF</b> &nbsp;|&nbsp; Firefox: <b>Imprimir para arquivo</b>
  <br><button class="btn-print" onclick="window.print()">🖨️ Abrir impressão / Salvar PDF</button>
</div>

<!-- HEADER -->
<div class="header">
  <div>
    <div class="h-title">Relatório CLT — Cálculo Completo</div>
    <div class="h-meta">
      Salário bruto: <strong><?= cur($gross) ?>/mês</strong> &nbsp;|&nbsp;
      Dependentes: <strong><?= $deps ?></strong> &nbsp;|&nbsp;
      Meses: <strong><?= $months ?></strong> &nbsp;|&nbsp;
      Gerado em: <?= $dataGer ?>
    </div>
  </div>
  <div class="logo-block">
    <div class="logo-txt"><div class="logo-name">Contrato PJ</div><div class="logo-sub">Serviços</div></div>
    <img src="<?= $LOGO ?>" alt="Logo" />
  </div>
</div>

<!-- KPIs MENSAIS -->
<div class="sec">Indicadores mensais</div>
<div class="kpi-grid">
  <div class="kpi kpi-green">
    <div class="kpi-lbl">Líquido mensal</div>
    <div class="kpi-val"><?= cur($m['net'] ?? 0) ?></div>
    <div class="kpi-sub">Carga: <?= pct($m['carga'] ?? 0) ?></div>
  </div>
  <div class="kpi kpi-amber">
    <div class="kpi-lbl">INSS mensal</div>
    <div class="kpi-val"><?= cur($m['inss'] ?? 0) ?></div>
    <div class="kpi-sub">Tabela progressiva 2026</div>
  </div>
  <div class="kpi kpi-red">
    <div class="kpi-lbl">IRRF mensal</div>
    <div class="kpi-val"><?= cur($m['irrf'] ?? 0) ?></div>
    <div class="kpi-sub"><?= ($m['irrf'] ?? 0) == 0 ? 'Isento (bruto ≤ R$ 5.000)' : 'Tabela progressiva' ?></div>
  </div>
  <div class="kpi kpi-blue">
    <div class="kpi-lbl">FGTS mensal</div>
    <div class="kpi-val"><?= cur($m['fgts'] ?? 0) ?></div>
    <div class="kpi-sub">8% — custo empregador</div>
  </div>
  <div class="kpi">
    <div class="kpi-lbl">Custo empresa/mês</div>
    <div class="kpi-val"><?= cur(($m['gross'] ?? 0) + ($m['fgts'] ?? 0)) ?></div>
    <div class="kpi-sub">Salário + FGTS</div>
  </div>
  <div class="kpi kpi-blue">
    <div class="kpi-lbl">Custo anual empregador</div>
    <div class="kpi-val"><?= cur($a['custoTotal'] ?? 0) ?></div>
    <div class="kpi-sub"><?= $months ?> meses · todos encargos</div>
  </div>
</div>

<!-- PAINÉIS: MENSAL · 13° · FÉRIAS -->
<div class="sec">Detalhamento por competência</div>
<div class="panels-3">

  <div class="panel">
    <div class="panel-hdr">📋 Folha Mensal</div>
    <?= panelRows([
      ["Salário bruto",          cur($m['gross'] ?? 0)],
      ["(−) INSS",               "− ".cur($m['inss'] ?? 0)],
      ["(−) IRRF",               "− ".cur($m['irrf'] ?? 0)],
      ["= Líquido mensal",       cur($m['net'] ?? 0), true],
      ["FGTS (empregador)",      cur($m['fgts'] ?? 0)],
      ["Custo total/mês empresa",cur(($m['gross']??0)+($m['fgts']??0)), true],
    ]) ?>
  </div>

  <div class="panel">
    <div class="panel-hdr">🎁 13° Salário</div>
    <?= panelRows([
      ["Base proporcional (".$months."/12)", cur($t['gross'] ?? 0)],
      ["(−) INSS sobre 13°",    "− ".cur($t['inss'] ?? 0)],
      ["(−) IRRF sobre 13°",    "− ".cur($t['irrf'] ?? 0)],
      ["= 13° líquido",         cur($t['net'] ?? 0), true],
      ["FGTS sobre 13°",        cur($t['fgts'] ?? 0)],
    ]) ?>
  </div>

  <div class="panel">
    <div class="panel-hdr">🏖️ Férias</div>
    <?= panelRows([
      ["Base proporcional (".$months."/12)",  cur($v['base'] ?? 0)],
      ["(+) Abono 1/3 constitucional",        cur($v['abono'] ?? 0)],
      ["= Total férias",                       cur($v['total'] ?? 0)],
      ["(−) INSS sobre férias",               "− ".cur($v['inss'] ?? 0)],
      ["(−) IRRF (só sobre base)",            "− ".cur($v['irrf'] ?? 0)],
      ["= Férias líquidas",                    cur($v['net'] ?? 0), true],
      ["FGTS sobre férias",                    cur($v['fgts'] ?? 0)],
    ]) ?>
  </div>

</div>

<!-- TABELA INSS -->
<div class="sec">Detalhamento INSS — Tabela progressiva 2026</div>
<table class="tbl">
  <thead>
    <tr><th>Faixa</th><th>De</th><th>Até</th><th>Alíquota</th><th>Base tributável</th><th>Contribuição</th></tr>
  </thead>
  <tbody>
  <?php foreach ($detail as $b): ?>
    <tr class="<?= ($b['active'] ?? false) ? 'active' : 'inactive' ?>">
      <td><?= s($b['label'] ?? '') ?> (<?= number_format(($b['rate'] ?? 0)*100,2,',','.') ?>%)</td>
      <td><?= cur($b['from'] ?? 0) ?></td>
      <td><?= cur($b['to'] ?? 0) ?></td>
      <td style="font-weight:800;"><?= number_format(($b['rate']??0)*100,2,',','.') ?>%</td>
      <td><?= ($b['active'] ?? false) ? cur($b['taxable'] ?? 0) : '—' ?></td>
      <td style="color:<?= ($b['active']??false)?'#d98208':'inherit' ?>;font-weight:<?= ($b['active']??false)?800:400 ?>;">
        <?= ($b['active'] ?? false) ? cur($b['contribution'] ?? 0) : '—' ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="4" style="color:#6f8780;font-size:9px;">Total INSS</td>
      <td><?= cur(min($gross, 8475.55)) ?></td>
      <td style="color:#d98208;"><?= cur($m['inss'] ?? 0) ?></td>
    </tr>
  </tfoot>
</table>

<!-- CHARTS -->
<?php if ($cComp || $cAnual): ?>
<div class="charts-row">
  <?php if ($cComp): ?>
  <div class="chart-panel">
    <p>Composição do salário bruto</p>
    <img src="<?= $cComp ?>" alt="Composição">
  </div>
  <?php endif; ?>
  <?php if ($cAnual): ?>
  <div class="chart-panel">
    <p>Custo anual do empregador</p>
    <img src="<?= $cAnual ?>" alt="Custo anual">
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- RESUMO ANUAL -->
<div class="sec">Resumo anual — Custo total do empregador</div>
<div class="resumo-grid">
  <div class="res-item blue">
    <strong>Custo anual total</strong>
    <span><?= cur($a['custoTotal'] ?? 0) ?></span>
  </div>
  <div class="res-item">
    <strong>Líquido total funcionário</strong>
    <span><?= cur($a['netAnual'] ?? 0) ?></span>
  </div>
  <div class="res-item amber">
    <strong>Total FGTS</strong>
    <span><?= cur($a['fgtsTotal'] ?? 0) ?></span>
  </div>
  <div class="res-item purple">
    <strong>Custo por R$ 1 líquido</strong>
    <span>R$ <?= number_format($a['custo1R'] ?? 0, 2, ',', '.') ?></span>
  </div>
</div>

<!-- VERDICT -->
<?php if ($verdict): ?>
<div class="verdict">
  <span class="v-title">Análise executiva</span>
  <?= $verdict ?>
</div>
<?php endif; ?>

<!-- CTA -->
<div class="cta">
  <div class="cta-hdr">
    <img src="<?= $LOGO ?>" alt="Logo" />
    <div>
      <h3>Quer uma análise completa de folha com um especialista?</h3>
      <p>Equipe especializada em departamento pessoal — Contrato PJ Serviços</p>
    </div>
  </div>
  <div class="cta-body">
    <div class="cta-desc">Este relatório é gerado automaticamente. Para uma análise personalizada com auditoria da folha de pagamento, conformidade trabalhista e otimização de encargos, fale com nossa equipe.</div>
    <div class="cta-bullets">
      <div class="cta-b">Auditoria de folha sem custo</div>
      <div class="cta-b">Conformidade eSocial e FGTS</div>
      <div class="cta-b">Otimização de encargos CLT</div>
      <div class="cta-b">Sigilo e compliance garantidos</div>
    </div>
  </div>
  <div class="cta-foot">
    <div class="cta-contact"><strong>contato@contabilrh.com.br</strong> &nbsp;|&nbsp; <strong>pjcontrato.com.br</strong></div>
    <img src="<?= $LOGO ?>" alt="Logo" />
  </div>
</div>

<!-- FOOTER -->
<div class="footer">
  <span>Documento gerado automaticamente — Hub PJ / Contrato PJ Serviços</span>
  <img src="<?= $LOGO ?>" alt="Logo" />
  <span>Este relatório não substitui consultoria trabalhista profissional</span>
</div>

</body>
</html>
