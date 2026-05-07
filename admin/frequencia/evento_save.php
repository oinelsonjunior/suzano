<?php
session_start();
require_once '../inc/connection.php';

$tipo       = isset($_POST['tipo'])       ? (int)$_POST['tipo']          : 0;
$nome       = trim($_POST['nome']         ?? '');
$data       = trim($_POST['data_evento']  ?? '');
$observacao = trim($_POST['observacao']   ?? '');

// Validação básica
if ($tipo < 1 || $tipo > 6 || $nome === '' || $data === '') {
    header("Location: ../frequencia/evento_form.php");
    exit;
}

// 1. Inserir o evento
$stmt = $conn->prepare("
    INSERT INTO eventos (faccao, tipo, nome, data_evento, observacao)
    VALUES (1, ?, ?, ?, ?)
");
$stmt->bind_param("isss", $tipo, $nome, $data, $observacao);
$stmt->execute();
$evento_id = $conn->insert_id;

// 2. Gerar registros de frequência para todos os ativos e suspensos
//    (afastados e desligados não são convocados)
$res = $conn->query("
    SELECT id, patente, status
    FROM cadastro_integrante
    WHERE faccao = 1 AND status IN (1, 4)
");

$stmt2 = $conn->prepare("
    INSERT IGNORE INTO frequencias (evento_id, integrante_id, presente, patente_no_evento, status_no_evento)
    VALUES (?, ?, 0, ?, ?)
");

while ($row = $res->fetch_assoc()) {
    $stmt2->bind_param("iiii", $evento_id, $row['id'], $row['patente'], $row['status']);
    $stmt2->execute();
}

// 3. Redirecionar direto para a chamada do evento criado
header("Location: ../frequencia/chamada.php?id=$evento_id");
exit;
