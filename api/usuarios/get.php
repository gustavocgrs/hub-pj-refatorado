<?php
// ============================================================
// usuarios/get.php — Lista usuarios (somente admin)
// GET -> JSON array de usuarios com suas permissoes
// ============================================================

require_once __DIR__ . '/../config.php';
secure_session_start();
header('Content-Type: application/json; charset=utf-8');

require_admin();

$pdo = getDB();

$usuarios = $pdo->query('
    SELECT id, nome, email, papel, criado_em
    FROM usuarios
    ORDER BY nome
')->fetchAll();

$stmtPerm = $pdo->prepare('
    SELECT s.slug, s.nome
    FROM permissoes p
    INNER JOIN setores s ON s.id = p.setor_id
    WHERE p.usuario_id = ?
    ORDER BY s.nome
');

foreach ($usuarios as &$u) {
    $stmtPerm->execute([$u['id']]);
    $u['setores'] = $stmtPerm->fetchAll();
}

echo json_encode($usuarios);
