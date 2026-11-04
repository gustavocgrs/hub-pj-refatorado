<?php
// ============================================================
// logout.php — Encerra a sessao do usuario
// POST com header X-CSRF-Token
// ============================================================

require_once __DIR__ . '/../config.php';
secure_session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['erro' => 'Metodo nao permitido'], 405);
}

if (!csrf_validate()) exit;

session_unset();
session_destroy();

json_response(['ok' => true]);
