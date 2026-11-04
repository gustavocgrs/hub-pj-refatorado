<?php
// ============================================================
// usuarios/update.php — Atualiza permissoes de usuario (somente admin)
// POST { usuario_id, setores: [1,2,3], papel? }
// Header: X-CSRF-Token
// ============================================================

require_once __DIR__ . '/../config.php';
secure_session_start();
header('Content-Type: application/json; charset=utf-8');

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['erro' => 'Metodo nao permitido'], 405);
}

if (!csrf_validate()) exit;

$input = json_decode(file_get_contents('php://input'), true);

$usuarioId = (int)($input['usuario_id'] ?? 0);
$setores   = $input['setores'] ?? null;
$papel     = $input['papel'] ?? null;

if ($usuarioId <= 0) {
    json_response(['erro' => 'usuario_id obrigatorio'], 400);
}

$pdo = getDB();

$check = $pdo->prepare('SELECT id FROM usuarios WHERE id = ?');
$check->execute([$usuarioId]);
if (!$check->fetch()) {
    json_response(['erro' => 'Usuario nao encontrado'], 404);
}

if ($papel !== null && in_array($papel, ['admin', 'usuario'])) {
    $stmt = $pdo->prepare('UPDATE usuarios SET papel = ? WHERE id = ?');
    $stmt->execute([$papel, $usuarioId]);
}

if ($setores !== null && is_array($setores)) {
    // Validar que setores existem
    $validSetores = $pdo->query('SELECT id FROM setores')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($setores as $setorId) {
        if (!in_array((int)$setorId, $validSetores)) {
            json_response(['erro' => 'Setor invalido: ' . (int)$setorId], 400);
        }
    }

    $pdo->prepare('DELETE FROM permissoes WHERE usuario_id = ?')->execute([$usuarioId]);

    $stmtPerm = $pdo->prepare('INSERT INTO permissoes (usuario_id, setor_id) VALUES (?, ?)');
    foreach ($setores as $setorId) {
        $stmtPerm->execute([$usuarioId, (int)$setorId]);
    }
}

json_response(['ok' => true]);
