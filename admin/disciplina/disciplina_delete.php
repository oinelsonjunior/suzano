<?php
session_start();
require_once '../inc/general.php';

$id     = isset($_GET['id'])     ? (int)$_GET['id']     : 0;
$origem = $_GET['origem'] ?? 'lista';

if ($id <= 0) {
    header("Location: ../disciplina/");
    exit;
}

// Buscar suspensão para saber o integrante
$stmt = $conn->prepare("SELECT integrante_id FROM disciplina WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$disc = $stmt->get_result()->fetch_assoc();

if (!$disc) {
    header("Location: ../disciplina/");
    exit;
}

$integrante_id = (int)$disc['integrante_id'];

// 1. Deletar a suspensão
$conn->prepare("DELETE FROM disciplina WHERE id = ?")->bind_param("i", $id) && true;
$stmt2 = $conn->prepare("DELETE FROM disciplina WHERE id = ?");
$stmt2->bind_param("i", $id);
$stmt2->execute();

// 2. Verificar se ainda há outra suspensão ativa para este integrante
$stmt3 = $conn->prepare("SELECT COUNT(*) as total FROM disciplina WHERE integrante_id = ? AND ativo = 1");
$stmt3->bind_param("i", $integrante_id);
$stmt3->execute();
$ainda_suspenso = (int)$stmt3->get_result()->fetch_assoc()['total'];

// 3. Se não houver mais suspensões ativas, voltar para Ativo (1)
if ($ainda_suspenso === 0) {
    $conn->query("UPDATE cadastro_integrante SET status = 1 WHERE id = $integrante_id AND status = 4");
}

// 4. Redirecionar conforme origem
if ($origem === 'integrante') {
    header("Location: ../integrante_view?id=$integrante_id&ok=suspensao_deletada");
} else {
    header("Location: ../disciplina/?ok=deletada");
}
exit;
