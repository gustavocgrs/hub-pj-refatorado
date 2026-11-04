-- ============================================================
-- seed.sql — Dados iniciais do Hub PJ
-- Os 9 setores. O admin é criado via install.php (com hash).
-- ============================================================

INSERT INTO setores (slug, nome, icone) VALUES
  ('comercial',           'Setor Comercial',              '🤝'),
  ('contabil',            'Setor Contábil',               '📘'),
  ('certificado-digital', 'Setor de Certificado Digital',  '🔐'),
  ('marketing',           'Setor de Marketing',            '📣'),
  ('sucesso-do-cliente',  'Setor de Sucesso do Cliente',   '💬'),
  ('fiscal',              'Setor Fiscal',                  '🧾'),
  ('financeiro',          'Setor Financeiro',              '💹'),
  ('paralegal',           'Setor Paralegal',               '🏛️'),
  ('rh',                  'Setor Pessoal (RH)',            '👥');
