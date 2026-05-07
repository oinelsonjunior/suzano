<?php
require_once '../inc/connection.php';

$id  = isset($_POST['id'])  ? (int)$_POST['id']  : 0;
$mes = isset($_POST['mes']) ? (int)$_POST['mes']  : (int)date('m');
$ano = isset($_POST['ano']) ? (int)$_POST['ano']  : (int)date('Y');

if ($id <= 0) {
    header("Location: ../eventos/index.php");
    exit;
}

// O CASCADE DELETE definido na FK de frequencias cuida de apagar os registros filhos automaticamente
$stmt = $conn->prepare("DELETE FROM eventos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: ../eventos/index.php?mes=$mes&ano=$ano");
exit;
