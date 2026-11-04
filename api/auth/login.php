<?php
// ============================================================
// login.php — Autentica usuario e cria sessao
// POST { email, senha }
// ============================================================

require_once __DIR__ . '/../config.php';
secure_session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['erro' => 'Metodo nao permitido'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$email = is_string($input['email'] ?? null) ? trim($input['email']) : '';
$senha = is_string($input['senha'] ?? null) ? $input['senha'] : '';

if ($email === '' || $senha === '') {
    json_response(['erro' => 'Email e senha sao obrigatorios'], 400);
}

$pdo = getDB();

$stmt = $pdo->prepare('SELECT id, nome, email, senha, papel FROM usuarios WHERE email = ?');
$stmt->execute([$email]);
$usuario = $stmt->fetch();

if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    json_response(['erro' => 'Email ou senha incorretos'], 401);
}

// Regenerar ID de sessao para prevenir session fixation
session_regenerate_id(true);

$_SESSION['usuario_id']    = $usuario['id'];
$_SESSION['usuario_nome']  = $usuario['nome'];
$_SESSION['usuario_email'] = $usuario['email'];
$_SESSION['usuario_papel'] = $usuario['papel'];

// Gerar token CSRF para a sessao
$csrfToken = csrf_token();

json_response([
    'ok' => true,
    'usuario' => [
        'id'    => $usuario['id'],
        'nome'  => $usuario['nome'],
        'email' => $usuario['email'],
        'papel' => $usuario['papel'],
    ],
    'csrf_token' => $csrfToken,
]);
