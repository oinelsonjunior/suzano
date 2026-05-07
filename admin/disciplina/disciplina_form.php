<?php
session_start();
require_once('../inc/general.php');

// Modo edição
$edit_id       = isset($_GET['id'])            ? (int)$_GET['id']            : 0;
$editando      = $edit_id > 0;
$susp_edit     = null;

if ($editando) {
    $se = $conn->prepare("SELECT * FROM disciplina WHERE id = ?");
    $se->bind_param("i", $edit_id);
    $se->execute();
    $susp_edit = $se->get_result()->fetch_assoc();
    if (!$susp_edit) { header("Location: ../disciplina/"); exit; }
}

// Integrante pré-selecionado (vindo do integrante_view ou edição)
$integrante_id = $editando
    ? (int)$susp_edit['integrante_id']
    : (isset($_GET['integrante_id']) ? (int)$_GET['integrante_id'] : 0);

// Lista de integrantes ativos e suspensos
$integrantes = $conn->query("
    SELECT id, apelido, nome, patente, status
    FROM cadastro_integrante
    WHERE faccao = 1 AND status IN (1, 4)
    ORDER BY apelido ASC
")->fetch_all(MYSQLI_ASSOC);

$patentes = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',
             6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8"/>
    <base href="../"><title>Abutre's MC | <?= $editando ? 'Editar Suspensão' : 'Nova Suspensão' ?></title>
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
                            <h3 class="m-subheader__title" style="text-transform:uppercase;"><?= $editando ? "Editar Suspensão" : "Nova Suspensão" ?></h3>
                            <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                                <li class="m-nav__item m-nav__item--home">
                                    <a href="../index" class="m-nav__link m-nav__link--icon">
                                        <i class="m-nav__link-icon la la-home"></i>
                                    </a>
                                </li>
                                <li class="m-nav__separator">—</li>
                                <li class="m-nav__item">
                                    <a href="disciplina/" class="m-nav__link">
                                        <span class="m-nav__link-text">Disciplina</span>
                                    </a>
                                </li>
                                <li class="m-nav__separator">—</li>
                                <li class="m-nav__item"><span class="m-nav__link-text"><?= $editando ? "Editar" : "Nova Suspensão" ?></span></li>
                            </ul>
                        </div>
                        <div>
                            <a href="disciplina/" class="btn btn-sm btn-secondary m-btn m-btn--icon m-btn--pill">
                                <span><i class="la la-arrow-left"></i><span>Voltar</span></span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="m-content">
                    <div class="row">
                        <div class="col-xl-7 offset-xl-2">

                            <div class="m-portlet m-portlet--mobile">
                                <div class="m-portlet__head">
                                    <div class="m-portlet__head-caption">
                                        <div class="m-portlet__head-title">
                                            <h3 class="m-portlet__head-text">
                                                <i class="la la-gavel m--font-danger"></i>
                                                <?= $editando ? "Editar Medida Disciplinar" : "Registrar Medida Disciplinar" ?>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="m-portlet__body">
                                    <form method="POST" action="disciplina/disciplina_save.php">
                                        <input type="hidden" name="id" value="<?= $edit_id ?>">

                                        <div class="form-group m-form__group">
                                            <label class="form-control-label">
                                                Integrante <span class="m--font-danger">*</span>
                                            </label>
                                            <select name="integrante_id" class="form-control m-input" required <?= $editando ? "disabled" : "" ?>>
                                                <option value="">— Selecione —</option>
                                                <?php foreach ($integrantes as $ing): ?>
                                                <option value="<?= $ing['id'] ?>"
                                                    <?= $ing['id'] == $integrante_id ? 'selected' : '' ?>
                                                    <?= $editando ? 'disabled' : '' ?> >
                                                    <?= htmlspecialchars($ing['apelido']) ?>
                                                    (<?= htmlspecialchars($ing['nome']) ?>)
                                                    — Pat. <?= $patentes[$ing['patente']] ?? '—' ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                                    <?php if ($editando): ?>
                                                    <input type="hidden" name="integrante_id" value="<?= $integrante_id ?>">
                                                    <span class="m-form__help">O integrante não pode ser alterado em uma edição.</span>
                                                    <?php endif; ?>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">
                                                        Duração <span class="m--font-danger">*</span>
                                                    </label>
                                                    <select name="duracao_dias" id="duracao" class="form-control m-input"
                                                            required onchange="calcularFim()">
                                                        <option value="">— Selecione —</option>
                                                        <option value="30" <?= ($susp_edit['duracao_dias'] ?? 0) == 30 ? 'selected' : '' ?>>30 dias</option>
                                                        <option value="60" <?= ($susp_edit['duracao_dias'] ?? 0) == 60 ? 'selected' : '' ?>>60 dias</option>
                                                        <option value="90" <?= ($susp_edit['duracao_dias'] ?? 0) == 90 ? 'selected' : '' ?>>90 dias</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">
                                                        Data de início <span class="m--font-danger">*</span>
                                                    </label>
                                                    <input type="date" name="data_inicio" id="data_inicio"
                                                           class="form-control m-input"
                                                           value="<?= $editando ? $susp_edit['data_inicio'] : date('Y-m-d') ?>"
                                                           required onchange="calcularFim()">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Data de fim (calculada)</label>
                                                    <input type="text" id="data_fim_display"
                                                           class="form-control m-input" readonly
                                                           placeholder="Selecione duração e início"
                                                           style="background:#f8f9fa;">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group m-form__group">
                                            <label class="form-control-label">
                                                Motivo da Suspensão <span class="m--font-danger">*</span>
                                            </label>
                                            <textarea name="motivo" class="form-control m-input" rows="3"
                                                      placeholder="Descreva o motivo detalhadamente..."
                                                      required><?= htmlspecialchars($susp_edit['motivo'] ?? '') ?></textarea>
                                        </div>

                                        <div class="form-group m-form__group">
                                            <label class="form-control-label">
                                                Aplicado por <span class="m--font-danger">*</span>
                                            </label>
                                            <input type="text" name="aplicado_por" class="form-control m-input"
                                                   value="<?= htmlspecialchars($susp_edit['aplicado_por'] ?? $apelido) ?>"
                                                   placeholder="Nome de quem aplicou a suspensão"
                                                   required>
                                            <span class="m-form__help">
                                                Pré-preenchido com seu apelido — altere se necessário.
                                            </span>
                                        </div>

                                        <div class="m-alert m-alert--icon m-alert--air m-alert--danger" role="alert">
                                            <div class="m-alert__icon"><i class="la la-exclamation-triangle"></i></div>
                                            <div class="m-alert__text">
                                                <?= $editando ? "As alterações serão salvas. O status do integrante não será alterado." : "Ao salvar, o status do integrante será alterado automaticamente para <strong>Suspenso</strong>." ?>
                                            </div>
                                        </div>

                                        <div class="m-form__actions d-flex justify-content-between m--margin-top-20">
                                            <a href="disciplina/" class="btn btn-secondary m-btn m-btn--pill">
                                                Cancelar
                                            </a>
                                            <button type="submit" class="btn btn-danger m-btn m-btn--icon m-btn--pill">
                                                <span><i class="la la-save"></i><span><?= $editando ? "Salvar Alterações" : "Registrar Suspensão" ?></span></span>
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
<script src="../js/vendors.bundle.js"></script>
<script src="../js/scripts.bundle.js"></script>
<script>
function calcularFim() {
    var duracao = parseInt(document.getElementById('duracao').value);
    var inicio  = document.getElementById('data_inicio').value;
    var display = document.getElementById('data_fim_display');

    if (!duracao || !inicio) { display.value = ''; return; }

    var d = new Date(inicio);
    d.setDate(d.getDate() + duracao - 1);

    var dia = String(d.getDate()).padStart(2, '0');
    var mes = String(d.getMonth() + 1).padStart(2, '0');
    var ano = d.getFullYear();

    display.value = dia + '/' + mes + '/' + ano;
}

// Calcular ao carregar se já tiver data
document.addEventListener('DOMContentLoaded', calcularFim);
</script>
</body>
</html>