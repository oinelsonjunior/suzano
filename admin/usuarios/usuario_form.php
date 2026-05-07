<?php
session_start();
require_once('../inc/general.php');

// Verificar permissão
$stmt_me = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt_me->bind_param("s", $_SESSION['admin_login']);
$stmt_me->execute();
$me = $stmt_me->get_result()->fetch_assoc();
$eh_superadmin = ((int)$me['id'] === 1);

$id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editando = $id > 0;
$usuario  = [];

if ($editando) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();

    if (!$usuario) {
        header("Location: index.php");
        exit;
    }

    // Admin normal não pode editar outros admins
    if (!$eh_superadmin && $usuario['role'] === 'admin') {
        header("Location: index.php");
        exit;
    }
}

// Integrantes disponíveis para vincular (todos, para o admin poder escolher)
$integrantes = $conn->query("
    SELECT id, apelido, nome, patente, status
    FROM cadastro_integrante
    WHERE faccao = 1
    ORDER BY apelido ASC
")->fetch_all(MYSQLI_ASSOC);

$patentes      = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X'];
$status_labels = [1=>'Ativo',2=>'Afastado',3=>'Desligado',4=>'Suspenso'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8"/>
    <base href="../">
    <title>Abutre's MC | <?= $editando ? 'Editar' : 'Novo' ?> Usuário</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
    <script>WebFont.load({google:{"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},active:function(){sessionStorage.fonts=true;}});</script>
    <link href="css/style.bundle.css" rel="stylesheet" type="text/css"/>
</head>
<body class="m-page--wide m-header--fixed m-header--fixed-mobile m-footer--push m-aside--offcanvas-default">
<div class="m-grid m-grid--hor m-grid--root m-page">

    <header class="m-grid__item m-header" data-minimize="minimize" data-minimize-offset="200" data-minimize-mobile-offset="200">
        <div class="m-header__top">
            <div class="m-container m-container--responsive m-container--xxl m-container--full-height m-page__container">
                <div class="m-stack m-stack--ver m-stack--desktop">
                    <div class="m-stack__item m-brand">
                        <div class="m-stack m-stack--ver m-stack--general m-stack--inline">
                            <div class="m-stack__item m-stack__item--middle m-brand__logo">
                                <a href="index" class="m-brand__logo-wrapper">
                                    <img alt="" src="images/logo.png"/>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php include_once('../inc/topbar.php'); ?>
                </div>
            </div>
        </div>
        <?php include_once('../inc/header_bottom.php'); ?>
    </header>

    <div class="m-grid__item m-grid__item--fluid m-grid m-grid--hor-desktop m-grid--desktop m-body">
        <div class="m-grid__item m-grid__item--fluid m-grid m-grid--ver m-container m-container--responsive m-container--xxl m-page__container">
            <div class="m-grid__item m-grid__item--fluid m-wrapper">

                <div class="m-subheader">
                    <div class="d-flex align-items-center">
                        <div class="mr-auto">
                            <h3 class="m-subheader__title" style="text-transform:uppercase;">
                                <?= $editando ? 'Editar Usuário' : 'Novo Usuário' ?>
                            </h3>
                            <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                                <li class="m-nav__item m-nav__item--home">
                                    <a href="index" class="m-nav__link m-nav__link--icon">
                                        <i class="m-nav__link-icon la la-home"></i>
                                    </a>
                                </li>
                                <li class="m-nav__separator">—</li>
                                <li class="m-nav__item">
                                    <a href="index.php" class="m-nav__link">
                                        <span class="m-nav__link-text">Usuários</span>
                                    </a>
                                </li>
                                <li class="m-nav__separator">—</li>
                                <li class="m-nav__item">
                                    <span class="m-nav__link-text"><?= $editando ? 'Editar' : 'Novo' ?></span>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <a href="index.php" class="btn btn-sm btn-secondary m-btn m-btn--icon m-btn--pill">
                                <span><i class="la la-arrow-left"></i><span>Voltar</span></span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="m-content">
                    <div class="row">
                        <div class="col-xl-6 offset-xl-3">
                            <div class="m-portlet m-portlet--mobile">
                                <div class="m-portlet__head">
                                    <div class="m-portlet__head-caption">
                                        <div class="m-portlet__head-title">
                                            <h3 class="m-portlet__head-text">
                                                <i class="la la-user m--font-brand"></i>
                                                <?= $editando ? 'Editar Usuário' : 'Cadastrar Usuário' ?>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="m-portlet__body">
                                    <form method="POST" action="usuarios/usuario_save.php">
                                        <input type="hidden" name="id" value="<?= $id ?>">

                                        <div class="form-group m-form__group">
                                            <label class="form-control-label">
                                                Nome de usuário <span class="m--font-danger">*</span>
                                            </label>
                                            <input type="text" name="username" class="form-control m-input"
                                                   value="<?= htmlspecialchars($usuario['username'] ?? '') ?>"
                                                   required maxlength="20">
                                        </div>

                                        <div class="form-group m-form__group">
                                            <label class="form-control-label">
                                                E-mail <span class="m--font-danger">*</span>
                                            </label>
                                            <input type="email" name="email" class="form-control m-input"
                                                   value="<?= htmlspecialchars($usuario['email'] ?? '') ?>"
                                                   required maxlength="50">
                                        </div>

                                        <div class="form-group m-form__group">
                                            <label class="form-control-label">
                                                Senha <?= $editando ? '(deixe em branco para não alterar)' : '<span class="m--font-danger">*</span>' ?>
                                            </label>
                                            <input type="password" name="password" class="form-control m-input"
                                                   <?= $editando ? '' : 'required' ?> minlength="6"
                                                   placeholder="Mínimo 6 caracteres">
                                        </div>

                                        <div class="form-group m-form__group">
                                            <label class="form-control-label">
                                                Perfil de acesso <span class="m--font-danger">*</span>
                                            </label>
                                            <select name="role" class="form-control m-input" required
                                                    <?= (!$eh_superadmin) ? 'disabled' : '' ?>>
                                                <option value="membro" <?= ($usuario['role'] ?? 'membro') === 'membro' ? 'selected' : '' ?>>
                                                    Membro — acesso apenas ao próprio histórico
                                                </option>
                                                <?php if ($eh_superadmin): ?>
                                                <option value="admin" <?= ($usuario['role'] ?? '') === 'admin' ? 'selected' : '' ?>>
                                                    Admin — acesso completo ao sistema
                                                </option>
                                                <?php endif; ?>
                                            </select>
                                            <?php if (!$eh_superadmin): ?>
                                            <input type="hidden" name="role" value="membro">
                                            <span class="m-form__help">
                                                Apenas o Super Admin pode criar admins.
                                            </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="form-group m-form__group">
                                            <label class="form-control-label">
                                                Integrante vinculado <span class="m--font-danger">*</span>
                                            </label>
                                            <select name="id_cadastro" class="form-control m-input" required>
                                                <option value="">— Selecione o integrante —</option>
                                                <?php foreach ($integrantes as $ing): ?>
                                                <option value="<?= $ing['id'] ?>"
                                                    <?= ($usuario['id_cadastro'] ?? 0) == $ing['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($ing['apelido']) ?>
                                                    (<?= htmlspecialchars($ing['nome']) ?>)
                                                    — Pat. <?= $patentes[$ing['patente']] ?? '—' ?>
                                                    — <?= $status_labels[$ing['status']] ?? '—' ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <span class="m-form__help">
                                                O usuário só terá acesso aos dados deste integrante.
                                            </span>
                                        </div>

                                        <div class="form-group m-form__group">
                                            <label class="form-control-label">Status</label>
                                            <select name="enabled" class="form-control m-input">
                                                <option value="1" <?= ($usuario['enabled'] ?? 1) == 1 ? 'selected' : '' ?>>Ativo</option>
                                                <option value="0" <?= ($usuario['enabled'] ?? 1) == 0 ? 'selected' : '' ?>>Desativado</option>
                                            </select>
                                        </div>

                                        <div class="m-form__actions d-flex justify-content-between m--margin-top-20">
                                            <a href="index.php" class="btn btn-secondary m-btn m-btn--pill">Cancelar</a>
                                            <button type="submit" class="btn btn-brand m-btn m-btn--icon m-btn--pill">
                                                <span>
                                                    <i class="la la-save"></i>
                                                    <span><?= $editando ? 'Salvar Alterações' : 'Criar Usuário' ?></span>
                                                </span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php require_once('../inc/footer.php'); ?>
</div>
<div class="m-scroll-top m-scroll-top--skin-top" data-toggle="m-scroll-top" data-scroll-offset="500" data-scroll-speed="300">
    <i class="la la-arrow-up"></i>
</div>
<script src="js/vendors.bundle.js"></script>
<script src="js/scripts.bundle.js"></script>
</body>
</html>