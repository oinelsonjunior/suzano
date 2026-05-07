<?php
require_once '../inc/connection.php';

$mes      = isset($_POST['mes']) ? (int)$_POST['mes'] : (int)date('m');
$ano      = isset($_POST['ano']) ? (int)$_POST['ano'] : (int)date('Y');
$descricao = trim($_POST['descricao'] ?? '');
$valor     = (float)str_replace(',', '.', $_POST['valor'] ?? '0');
$data      = $_POST['data_saida'] ?? date('Y-m-d');

if ($descricao === '' || $valor <= 0) {
    header("Location: ../financeiro/saida_form.php?mes=$mes&ano=$ano");
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id > 0) {
    // EDIÇÃO
    $stmt = $conn->prepare("UPDATE caixa_saida SET descricao=?, valor=?, data_saida=? WHERE id=?");
    $stmt->bind_param("sdsi", $descricao, $valor, $data, $id);
} else {
    // CRIAÇÃO
    $stmt = $conn->prepare("INSERT INTO caixa_saida (descricao, valor, data_saida) VALUES (?, ?, ?)");
    $stmt->bind_param("sds", $descricao, $valor, $data);
}
$stmt->execute();

header("Location: ../financeiro/fluxo_caixa.php?mes=$mes&ano=$ano");
exit;