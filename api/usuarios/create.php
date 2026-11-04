<?php
// ============================================================
// usuarios/create.php — Cria usuario com permissoes (somente admin)
// POST { nome, email, senha, papel?, setores: [1,2,3] }
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

$nome    = is_string($input['nome'] ?? null) ? trim($input['nome']) : '';
$email   = is_string($input['email'] ?? null) ? trim($input['email']) : '';
$senha   = is_string($input['senha'] ?? null) ? $input['senha'] : '';
$papel   = $input['papel'] ?? 'usuario';
$setores = $input['setores'] ?? [];

if ($nome === '' || $email === '' || $senha === '') {
    json_response(['erro' => 'Nome, email e senha sao obrigatorios'], 400);
}

if (strlen($senha) < 8) {
    json_response(['erro' => 'Senha deve ter no minimo 8 caracteres'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['erro' => 'Email invalido'], 400);
}

if (!in_array($papel, ['admin', 'usuario'])) {
    $papel = 'usuario';
}

if (!is_array($setores)) {
    $setores = [];
}

$pdo = getDB();

// Verificar se email ja existe
$check = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
$check->execute([$email]);
if ($check->fetch()) {
    json_response(['erro' => 'Email ja cadastrado'], 409);
}

// Validar que setores existem
$validSetores = $pdo->query('SELECT id FROM setores')->fetchAll(PDO::FETCH_COLUMN);
foreach ($setores as $setorId) {
    if (!in_array((int)$setorId, $validSetores)) {
        json_response(['erro' => 'Setor invalido: ' . (int)$setorId], 400);
    }
}

$hash = password_hash($senha, PASSWORD_BCRYPT);

$stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha, papel) VALUES (?, ?, ?, ?)');
$stmt->execute([$nome, $email, $hash, $papel]);
$novoId = $pdo->lastInsertId();

if (!empty($setores)) {
    $stmtPerm = $pdo->prepare('INSERT INTO permissoes (usuario_id, setor_id) VALUES (?, ?)');
    foreach ($setores as $setorId) {
        $stmtPerm->execute([$novoId, (int)$setorId]);
    }
}

json_response([
    'ok' => true,
    'usuario' => [
        'id'    => (int)$novoId,
        'nome'  => $nome,
        'email' => $email,
        'papel' => $papel,
        'setores_count' => count($setores),
    ],
]);
