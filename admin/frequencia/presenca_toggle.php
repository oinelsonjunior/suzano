<?php
require_once '../inc/connection.php';

header('Content-Type: application/json');

$freq_id  = isset($_POST['freq_id'])  ? (int)$_POST['freq_id']  : 0;
$presente = isset($_POST['presente']) ? (int)$_POST['presente'] : 0;
$presente = $presente ? 1 : 0;

if ($freq_id <= 0) {
    echo json_encode(['ok' => false, 'erro' => 'ID inválido']);
    exit;
}

$stmt = $conn->prepare("UPDATE frequencias SET presente = ? WHERE id = ?");
$stmt->bind_param("ii", $presente, $freq_id);
$ok = $stmt->execute();

echo json_encode(['ok' => $ok, 'presente' => $presente]);
exit;
