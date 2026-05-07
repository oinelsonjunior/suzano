<?php
session_start();
require_once '../inc/general.php';

$id            = isset($_POST['id'])            ? (int)$_POST['id']            : 0;
$integrante_id = isset($_POST['integrante_id']) ? (int)$_POST['integrante_id'] : 0;
$duracao_dias  = isset($_POST['duracao_dias'])  ? (int)$_POST['duracao_dias']  : 0;
$data_inicio   = trim($_POST['data_inicio']     ?? '');
$motivo        = trim($_POST['motivo']          ?? '');
$aplicado_por  = trim($_POST['aplicado_por']    ?? '');
$editando      = $id > 0;

// Validação
if ($integrante_id <= 0 || !in_array($duracao_dias, [30, 60, 90])
    || $data_inicio === '' || $motivo === '' || $aplicado_por === '') {
    $redir = $editando ? "?id=$id" : '';
    header("Location: ../disciplina/disciplina_form.php$redir");
    exit;
}

// Calcular data fim (início + duração - 1 dia)
$dt_inicio = new DateTime($data_inicio);
$dt_fim    = clone $dt_inicio;
$dt_fim->modify('+' . ($duracao_dias - 1) . ' days');
$data_fim  = $dt_fim->format('Y-m-d');

if ($editando) {
    // UPDATE — não altera integrante_id nem o status
    $stmt = $conn->prepare("
        UPDATE disciplina
        SET duracao_dias = ?, data_inicio = ?, data_fim = ?, motivo = ?, aplicado_por = ?
        WHERE id = ?
    ");
    $stmt->bind_param("issssi", $duracao_dias, $data_inicio, $data_fim, $motivo, $aplicado_por, $id);
    $stmt->execute();
    header("Location: ../disciplina/?ok=atualizada");
} else {
    // INSERT
    $stmt = $conn->prepare("
        INSERT INTO disciplina
            (integrante_id, duracao_dias, data_inicio, data_fim, motivo, aplicado_por, ativo)
        VALUES (?, ?, ?, ?, ?, ?, 1)
    ");
    $stmt->bind_param("iissss", $integrante_id, $duracao_dias, $data_inicio, $data_fim, $motivo, $aplicado_por);
    $stmt->execute();

    // Atualizar status do integrante para Suspenso (4)
    $conn->query("UPDATE cadastro_integrante SET status = 4 WHERE id = $integrante_id");
    header("Location: ../disciplina/?ok=criada");
}
exit;