<?php
// ============================================================
// check-session.php — Verifica se ha sessao ativa
// GET -> retorna dados do usuario + csrf_token, ou 401
// ============================================================

require_once __DIR__ . '/../config.php';
secure_session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['usuario_id'])) {
    json_response(['logado' => false], 401);
}

json_response([
    'logado'     => true,
    'csrf_token' => csrf_token(),
    'usuario'    => [
        'id'    => $_SESSION['usuario_id'],
        'nome'  => $_SESSION['usuario_nome'],
        'email' => $_SESSION['usuario_email'],
        'papel' => $_SESSION['usuario_papel'],
    ],
]);
