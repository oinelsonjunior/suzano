<?php
require_once '../inc/connection.php';

$id         = isset($_POST['id'])          ? (int)$_POST['id']          : 0;
$tipo       = isset($_POST['tipo'])        ? (int)$_POST['tipo']        : 0;
$nome       = trim($_POST['nome']          ?? '');
$data       = trim($_POST['data_evento']   ?? '');
$observacao = trim($_POST['observacao']    ?? '');
$mes        = isset($_POST['mes'])         ? (int)$_POST['mes']         : (int)date('m');
$ano        = isset($_POST['ano'])         ? (int)$_POST['ano']         : (int)date('Y');

if ($id <= 0 || $tipo < 1 || $tipo > 6 || $nome === '' || $data === '') {
    header("Location: ../eventos/evento_edit.php?id=$id");
    exit;
}

$stmt = $conn->prepare("
    UPDATE eventos SET tipo = ?, nome = ?, data_evento = ?, observacao = ?
    WHERE id = ?
");
$stmt->bind_param("isssi", $tipo, $nome, $data, $observacao, $id);
$stmt->execute();

// Recalcular o mês/ano do evento após a edição (pode ter mudado a data)
$mes_novo = (int)date('m', strtotime($data));
$ano_novo = (int)date('Y', strtotime($data));

header("Location: ../eventos/index.php?mes=$mes_novo&ano=$ano_novo");
exit;
