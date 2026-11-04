<?php
// ============================================================
// setores/get.php — Retorna setores permitidos ao usuario logado
// Admin ve todos. Usuario comum ve so os que tem permissao.
// GET -> JSON array de setores
// ============================================================

require_once __DIR__ . '/../config.php';
secure_session_start();
header('Content-Type: application/json; charset=utf-8');

require_auth();

$pdo = getDB();
$usuarioId = $_SESSION['usuario_id'];
$papel = $_SESSION['usuario_papel'];

if ($papel === 'admin') {
    $stmt = $pdo->query('SELECT id, slug, nome, icone FROM setores ORDER BY id');
} else {
    $stmt = $pdo->prepare('
        SELECT s.id, s.slug, s.nome, s.icone
        FROM setores s
        INNER JOIN permissoes p ON p.setor_id = s.id
        WHERE p.usuario_id = ?
        ORDER BY s.id
    ');
    $stmt->execute([$usuarioId]);
}

echo json_encode($stmt->fetchAll());
