<?php
session_start();
require_once '../inc/general.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: ../disciplina/");
    exit;
}

// Buscar suspensão
$stmt = $conn->prepare("SELECT id, integrante_id, ativo FROM disciplina WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$susp = $stmt->get_result()->fetch_assoc();

if (!$susp || $susp['ativo'] != 1) {
    header("Location: ../disciplina/");
    exit;
}

$hoje         = date('Y-m-d');
$encerrado_por = $apelido; // apelido do admin logado, vem do general.php

// 1. Encerrar a suspensão
$stmt2 = $conn->prepare("
    UPDATE disciplina
    SET ativo = 0, encerrado_em = ?, encerrado_por = ?
    WHERE id = ?
");
$stmt2->bind_param("ssi", $hoje, $encerrado_por, $id);
$stmt2->execute();

// 2. Verificar se há outra suspensão ativa para este integrante
$int_id = (int)$susp['integrante_id'];
$res = $conn->query("
    SELECT COUNT(*) as total
    FROM disciplina
    WHERE integrante_id = $int_id AND ativo = 1
");
$ainda_suspenso = (int)$res->fetch_assoc()['total'];

// 3. Só reativa se não houver outra suspensão ativa
if ($ainda_suspenso === 0) {
    $conn->query("UPDATE cadastro_integrante SET status = 1 WHERE id = $int_id");
}

header("Location: ../disciplina/?ok=encerrada");
exit;