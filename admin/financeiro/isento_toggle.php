<?php
require_once '../inc/connection.php';

$id  = isset($_GET['id'])  ? (int)$_GET['id']  : 0;
$mes = isset($_GET['mes']) ? (int)$_GET['mes']  : (int)date('m');
$ano = isset($_GET['ano']) ? (int)$_GET['ano']  : (int)date('Y');

if ($id <= 0) {
    header("Location: ../financeiro/dashboard?mes=$mes&ano=$ano");
    exit;
}

// Verifica status atual
$stmt = $conn->prepare("SELECT isento FROM mensalidades WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    header("Location: ../financeiro/dashboard?mes=$mes&ano=$ano");
    exit;
}

if ($row['isento'] == 1) {
    // Remover isenção
    $stmt = $conn->prepare("UPDATE mensalidades SET isento = 0 WHERE id = ?");
} else {
    // Aplicar isenção — zera pagamento junto
    $stmt = $conn->prepare("UPDATE mensalidades SET isento = 1, pago = 0, data_pagamento = NULL WHERE id = ?");
}

$stmt->bind_param("i", $id);
$stmt->execute();

$origem = $_GET['origem'] ?? 'pendentes';

if ($origem === 'list') {
    header("Location: list.php?mes=$mes&ano=$ano");
} else {
    header("Location: pendentes.php?mes=$mes&ano=$ano");
}
exit;