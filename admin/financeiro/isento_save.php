<?php
session_start();
require_once '../inc/general.php';

$id     = isset($_POST['id'])     ? (int)$_POST['id']     : 0;
$mes    = isset($_POST['mes'])    ? (int)$_POST['mes']     : (int)date('m');
$ano    = isset($_POST['ano'])    ? (int)$_POST['ano']     : (int)date('Y');
$origem = trim($_POST['origem']   ?? 'pendentes');
$acao   = trim($_POST['acao']     ?? '');
$motivo = trim($_POST['motivo']   ?? '');

// $id aqui é o ID da users (admin logado), disponível via general.php
// Vamos buscar o id correto do usuário logado
$stmt_admin = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$email_sessao = $_SESSION['admin_login'];
$stmt_admin->bind_param("s", $email_sessao);
$stmt_admin->execute();
$admin_row = $stmt_admin->get_result()->fetch_assoc();
$admin_id  = (int)($admin_row['id'] ?? 0);

if ($id <= 0 || !in_array($acao, ['isentar', 'remover'])) {
    header("Location: ../financeiro/pendentes.php?mes=$mes&ano=$ano");
    exit;
}

// Buscar mensalidade
$stmt = $conn->prepare("SELECT id, integrante_id, mes, ano, isento FROM mensalidades WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$mens = $stmt->get_result()->fetch_assoc();

if (!$mens) {
    header("Location: ../financeiro/pendentes.php?mes=$mes&ano=$ano");
    exit;
}

if ($acao === 'isentar') {
    if ($motivo === '') {
        // Sem motivo — redireciona de volta ao form
        header("Location: ../financeiro/isento_form.php?id=$id&mes=$mes&ano=$ano&origem=$origem");
        exit;
    }

    // 1. Atualizar mensalidade
    $stmt = $conn->prepare("UPDATE mensalidades SET isento = 1, pago = 0, data_pagamento = NULL WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // 2. Persistir na tabela isencoes
    $integrante_id = (int)$mens['integrante_id'];
    $stmt2 = $conn->prepare("
        INSERT INTO isencoes (mensalidade_id, integrante_id, ano, mes, motivo, admin_id)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE motivo = VALUES(motivo), admin_id = VALUES(admin_id), created_at = NOW()
    ");
    $stmt2->bind_param("iiiisi", $id, $integrante_id, $mens['ano'], $mens['mes'], $motivo, $admin_id);
    $stmt2->execute();

} else {
    // Remover isenção
    $stmt = $conn->prepare("UPDATE mensalidades SET isento = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    // Mantém o registro histórico em isencoes — não apaga
}

// Redirecionar de volta
if ($origem === 'list') {
    header("Location: list.php?mes=$mes&ano=$ano");
} else {
    header("Location: pendentes.php?mes=$mes&ano=$ano");
}
exit;
