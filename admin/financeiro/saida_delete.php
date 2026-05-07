<?php
session_start();
require_once '../inc/general.php';

$id  = isset($_GET['id'])  ? (int)$_GET['id']  : 0;
$mes = isset($_GET['mes']) ? (int)$_GET['mes']  : 0;
$ano = isset($_GET['ano']) ? (int)$_GET['ano']  : (int)date('Y');

if ($id <= 0) {
    header("Location: ../financeiro/saida_list.php?ano=$ano&mes=$mes");
    exit;
}

$stmt = $conn->prepare("DELETE FROM caixa_saida WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: ../financeiro/saida_list.php?ano=$ano&mes=$mes&ok=deletada");
exit;
