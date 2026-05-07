<?php
session_start();
require_once '../inc/general.php';

// Verificar permissão
$stmt_me = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt_me->bind_param("s", $_SESSION['admin_login']);
$stmt_me->execute();
$me = $stmt_me->get_result()->fetch_assoc();
$eh_superadmin = ((int)$me['id'] === 1);

$id   = isset($_GET['id'])   ? (int)$_GET['id']   : 0;
$acao = trim($_GET['acao']   ?? '');

if ($id <= 0 || !in_array($acao, ['ativar', 'desativar'])) {
    header("Location: ../usuarios/index.php");
    exit;
}

// Não pode desativar a si mesmo
if ($id === (int)$me['id']) {
    header("Location: ../usuarios/index.php");
    exit;
}

// Buscar usuário alvo
$stmt = $conn->prepare("SELECT id, role FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$alvo = $stmt->get_result()->fetch_assoc();

if (!$alvo) {
    header("Location: ../usuarios/index.php");
    exit;
}

// Admin normal não pode tocar em outros admins
if (!$eh_superadmin && $alvo['role'] === 'admin') {
    header("Location: ../usuarios/index.php");
    exit;
}

$enabled = $acao === 'ativar' ? 1 : 0;
$stmt2 = $conn->prepare("UPDATE users SET enabled = ? WHERE id = ?");
$stmt2->bind_param("ii", $enabled, $id);
$stmt2->execute();

$ok = $acao === 'ativar' ? 'ativado' : 'desativado';
header("Location: ../usuarios/index.php?ok=$ok");
exit;
