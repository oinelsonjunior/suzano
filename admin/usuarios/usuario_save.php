<?php
session_start();
require_once '../inc/general.php';

// Verificar permissão
$stmt_me = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt_me->bind_param("s", $_SESSION['admin_login']);
$stmt_me->execute();
$me = $stmt_me->get_result()->fetch_assoc();
$eh_superadmin = ((int)$me['id'] === 1);

$id          = isset($_POST['id'])          ? (int)$_POST['id']          : 0;
$username    = trim($_POST['username']      ?? '');
$email       = trim($_POST['email']         ?? '');
$password    = trim($_POST['password']      ?? '');
$role        = in_array($_POST['role'] ?? '', ['admin','membro']) ? $_POST['role'] : 'membro';
$id_cadastro = isset($_POST['id_cadastro']) ? (int)$_POST['id_cadastro'] : 0;
$enabled     = isset($_POST['enabled'])     ? (int)$_POST['enabled']     : 1;

// Admin normal só pode criar/editar membros
if (!$eh_superadmin) {
    $role = 'membro';
}

// Validação
if ($username === '' || $email === '' || $id_cadastro <= 0) {
    header("Location: ../usuarios/usuario_form.php" . ($id > 0 ? "?id=$id" : ''));
    exit;
}

if ($id > 0) {
    // UPDATE — senha só atualiza se fornecida
    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("
            UPDATE users SET username=?, email=?, password=?, role=?, id_cadastro=?, enabled=?
            WHERE id=?
        ");
        $stmt->bind_param("ssssiis", $username, $email, $hash, $role, $id_cadastro, $enabled, $id);
    } else {
        $stmt = $conn->prepare("
            UPDATE users SET username=?, email=?, role=?, id_cadastro=?, enabled=?
            WHERE id=?
        ");
        $stmt->bind_param("sssiis", $username, $email, $role, $id_cadastro, $enabled, $id);
    }
    $stmt->execute();
    header("Location: ../usuarios/index.php?ok=atualizado");
} else {
    // INSERT — senha obrigatória
    if ($password === '') {
        header("Location: ../usuarios/usuario_form.php");
        exit;
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("
        INSERT INTO users (username, email, password, role, id_cadastro, enabled, patente, faccao)
        VALUES (?, ?, ?, ?, ?, ?, '', 1)
    ");
    $stmt->bind_param("ssssis", $username, $email, $hash, $role, $id_cadastro, $enabled);
    $stmt->execute();
    header("Location: ../usuarios/index.php?ok=criado");
}
exit;
