<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../inc/connection.php';

$id               = isset($_POST['id'])               ? (int)$_POST['id']               : 0;
$nome             = trim($_POST['nome']               ?? '');
$apelido          = trim($_POST['apelido']            ?? '');
$patente          = isset($_POST['patente'])          ? (int)$_POST['patente']          : 1;
$status           = isset($_POST['status'])           ? (int)$_POST['status']           : 1;
$padrinho         = isset($_POST['padrinho'])         ? (int)$_POST['padrinho']         : 0;
$veiculo          = trim($_POST['veiculo']            ?? 'Não');
$cnh              = trim($_POST['cnh']                ?? '');
$email            = trim($_POST['email']              ?? '');
$nascimento       = trim($_POST['nascimento']         ?? '');
$data_apresentacao= trim($_POST['data_apresentacao'] ?? '');
$celular          = trim($_POST['celular']            ?? '');
$telefone         = trim($_POST['telefone']           ?? '');
$comercial        = trim($_POST['comercial']          ?? '');
$recados          = trim($_POST['recados']            ?? '');
$cep              = trim($_POST['cep']                ?? '');
$endereco         = trim($_POST['endereco']           ?? '');
$num_endereco     = trim($_POST['num_endereco']       ?? '');
$complemento      = trim($_POST['complemento']        ?? '');
$bairro           = trim($_POST['bairro']             ?? '');
$cidade           = trim($_POST['cidade']             ?? '');
$estado           = trim($_POST['estado']             ?? '');

// --- Upload de foto ---
$foto_path   = null;   // novo arquivo enviado
$remover_foto = isset($_POST['remover_foto']) && $_POST['remover_foto'] == '1';

if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $file     = $_FILES['foto'];
    $max_size = 2 * 1024 * 1024; // 2MB
    $allowed  = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mime     = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (in_array($mime, $allowed) && $file['size'] <= $max_size) {
        $ext       = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime];
        $upload_dir = __DIR__ . '/../../uploads/fotos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        // Nome do arquivo será definido após termos o $id
        $foto_tmp = $file['tmp_name'];
        $foto_ext = $ext;
    }
}

// Validação mínima
if ($nome === '' || $apelido === '') {
    header("Location: ../integrantes/integrante_form.php" . ($id > 0 ? "?id=$id" : ''));
    exit;
}

// Datas — converter '' para NULL
$nascimento        = $nascimento        ?: null;
$data_apresentacao = $data_apresentacao ?: date('Y-m-d H:i:s');

if ($id > 0) {
    // UPDATE — sem alterar faccao
    $stmt = $conn->prepare("
        UPDATE cadastro_integrante SET
            nome = ?, apelido = ?, patente = ?, status = ?,
            padrinho = ?, veiculo = ?, cnh = ?, email = ?,
            nascimento = ?, data_apresentacao = ?,
            celular = ?, telefone = ?, comercial = ?, recados = ?,
            cep = ?, endereco = ?, num_endereco = ?, complemento = ?,
            bairro = ?, cidade = ?, estado = ?
        WHERE id = ?
    ");
    $stmt->bind_param(
        "ssiiisssssissssssssssi",
        $nome,
        $apelido,
        $patente,
        $status,
        $padrinho,
        $veiculo,
        $cnh,
        $email,
        $nascimento,
        $data_apresentacao,
        $celular,
        $telefone,
        $comercial,
        $recados,
        $cep,
        $endereco,
        $num_endereco,
        $complemento,
        $bairro,
        $cidade,
        $estado,
        $id
    );
    $stmt->execute();

    // Processar foto no UPDATE
    if (isset($foto_tmp)) {
        // Apagar foto antiga se existir
        $res_old = $conn->query("SELECT foto FROM cadastro_integrante WHERE id = $id");
        $old_row = $res_old->fetch_assoc();
        if ($old_row['foto'] && file_exists(__DIR__ . '/../../' . $old_row['foto'])) {
            unlink(__DIR__ . '/../../' . $old_row['foto']);
        }
        $foto_nome = 'uploads/fotos/' . $id . '_' . time() . '.' . $foto_ext;
        move_uploaded_file($foto_tmp, __DIR__ . '/../../' . $foto_nome);
        $conn->query("UPDATE cadastro_integrante SET foto = '$foto_nome' WHERE id = $id");
    } elseif ($remover_foto) {
        $res_old = $conn->query("SELECT foto FROM cadastro_integrante WHERE id = $id");
        $old_row = $res_old->fetch_assoc();
        if ($old_row['foto'] && file_exists(__DIR__ . '/../../' . $old_row['foto'])) {
            unlink(__DIR__ . '/../../' . $old_row['foto']);
        }
        $conn->query("UPDATE cadastro_integrante SET foto = NULL WHERE id = $id");
    }

    header("Location: ../integrante_view?id=$id");

} else {
    // INSERT
    $stmt = $conn->prepare("
    INSERT INTO cadastro_integrante (
        nome, apelido, patente, status, padrinho,
        veiculo, cnh, email,
        nascimento, data_apresentacao,
        celular, telefone, comercial, recados,
        cep, endereco, num_endereco, complemento,
        bairro, cidade, estado, faccao
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");
    $stmt->bind_param(
        "ssiiisssssisssssssssi",
        $nome,
        $apelido,
        $patente,
        $status,
        $padrinho,
        $veiculo,
        $cnh,
        $email,
        $nascimento,
        $data_apresentacao,
        $celular,
        $telefone,
        $comercial,
        $recados,
        $cep,
        $endereco,
        $num_endereco,
        $complemento,
        $bairro,
        $cidade,
        $estado
    );
    $stmt->execute();
    $novo_id = $conn->insert_id;

    // Processar foto no INSERT
    if (isset($foto_tmp) && $novo_id > 0) {
        $foto_nome = 'uploads/fotos/' . $novo_id . '_' . time() . '.' . $foto_ext;
        $upload_dir = __DIR__ . '/../../uploads/fotos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        move_uploaded_file($foto_tmp, __DIR__ . '/../../' . $foto_nome);
        $conn->query("UPDATE cadastro_integrante SET foto = '$foto_nome' WHERE id = $novo_id");
    }

    header("Location: ../integrante_view?id=$novo_id");
}
exit;