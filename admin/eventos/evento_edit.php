<?php
    session_start();
    require_once ('../inc/general.php');

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        header("Location: ../eventos/index.php");
        exit;
    }

    $tipos_evento = [
        1 => 'Sede Estadual (padrão)',
        2 => 'Sede Estadual (reunião geral)',
        3 => 'Sede Suzano (reunião)',
        4 => 'Sede Suzano (confraternização)',
        5 => 'Evento Fora',
        6 => 'Evento Obrigatório',
    ];

    $tipo_badge = [
        1 => 'm-badge--metal',
        2 => 'm-badge--brand',
        3 => 'm-badge--info',
        4 => 'm-badge--success',
        5 => 'm-badge--warning',
        6 => 'm-badge--danger',
    ];

    // Buscar evento
    $stmt = $conn->prepare("SELECT * FROM eventos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $evento = $stmt->get_result()->fetch_assoc();

    if (!$evento) {
        header("Location: ../eventos/index.php");
        exit;
    }

    // Resumo de presença
    $stmt2 = $conn->prepare("
        SELECT COUNT(*) as total, SUM(presente=1) as presentes
        FROM frequencias WHERE evento_id = ?
    ");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $freq = $stmt2->get_result()->fetch_assoc();
    $pc_pres = $freq['total'] > 0 ? round(($freq['presentes'] / $freq['total']) * 100) : 0;

    // Mês/ano para retorno ao calendário
    $mes_ev = (int)date('m', strtotime($evento['data_evento']));
    $ano_ev = (int)date('Y', strtotime($evento['data_evento']));
?>
<!DOCTYPE html>
<html lang="pt-br">
    <base href="../">
    <head>
        <meta charset="utf-8" />
        <title>Abutre's MC | Editar Evento</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
        <script>
            WebFont.load({
                google: {"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},
                active: function() { sessionStorage.fonts = true; }
            });
        </script>
        <link href="css/style.bundle.css" rel="stylesheet" type="text/css" />
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
                                        Editar Evento
                                    </h3>
                                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                                        <li class="m-nav__item m-nav__item--home">
                                            <a href="index" class="m-nav__link m-nav__link--icon">
                                                <i class="m-nav__link-icon la la-home"></i>
                                            </a>
                                        </li>
                                        <li class="m-nav__separator">—</li>
                                        <li class="m-nav__item">
                                            <a href="eventos/index.php?mes=<?= $mes_ev ?>&ano=<?= $ano_ev ?>" class="m-nav__link">
                                                <span class="m-nav__link-text">Eventos</span>
                                            </a>
                                        </li>
                                        <li class="m-nav__separator">—</li>
                                        <li class="m-nav__item">
                                            <span class="m-nav__link-text">Editar</span>
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <a href="frequencia/chamada.php?id=<?= $id ?>"
                                       class="btn btn-sm btn-info m-btn m-btn--icon m-btn--pill">
                                        <span><i class="la la-check-square-o"></i><span>Chamada</span></span>
                                    </a>
                                    &nbsp;
                                    <a href="eventos/index.php?mes=<?= $mes_ev ?>&ano=<?= $ano_ev ?>"
                                       class="btn btn-sm btn-secondary m-btn m-btn--icon m-btn--pill">
                                        <span><i class="la la-arrow-left"></i><span>Voltar</span></span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="m-content">
                            <div class="row">

                                <!-- Formulário de edição -->
                                <div class="col-xl-7">
                                    <div class="m-portlet m-portlet--mobile">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">
                                                        <i class="la la-pencil m--font-warning"></i>
                                                        Editar Evento
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">
                                            <form method="POST" action="eventos/evento_update.php">
                                                <input type="hidden" name="id" value="<?= $id ?>">
                                                <input type="hidden" name="mes" value="<?= $mes_ev ?>">
                                                <input type="hidden" name="ano" value="<?= $ano_ev ?>">

                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">
                                                        Tipo de Evento <span class="m--font-danger">*</span>
                                                    </label>
                                                    <select name="tipo" class="form-control m-input" required>
                                                        <?php foreach ($tipos_evento as $tid => $tnome): ?>
                                                            <option value="<?= $tid ?>"
                                                                <?= $tid == $evento['tipo'] ? 'selected' : '' ?>>
                                                                <?= $tnome ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">
                                                        Nome / Descrição <span class="m--font-danger">*</span>
                                                    </label>
                                                    <input type="text" name="nome" class="form-control m-input"
                                                           value="<?= htmlspecialchars($evento['nome']) ?>" required>
                                                </div>

                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">
                                                        Data do Evento <span class="m--font-danger">*</span>
                                                    </label>
                                                    <input type="date" name="data_evento" class="form-control m-input"
                                                           value="<?= $evento['data_evento'] ?>" required>
                                                </div>

                                                <div class="form-group m-form__group">
                                                    <label class="form-control-label">Observação</label>
                                                    <textarea name="observacao" class="form-control m-input" rows="2"><?= htmlspecialchars($evento['observacao'] ?? '') ?></textarea>
                                                </div>

                                                <div class="m-form__actions d-flex justify-content-between m--margin-top-20">
                                                    <button type="submit"
                                                            class="btn btn-warning m-btn m-btn--icon m-btn--pill">
                                                        <span><i class="la la-save"></i><span>Salvar Alterações</span></span>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Painel lateral: info + exclusão -->
                                <div class="col-xl-5">

                                    <!-- Resumo do evento -->
                                    <div class="m-portlet m-portlet--mobile">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">Resumo do Evento</h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">
                                            <div class="m-widget1">
                                                <div class="m-widget1__item">
                                                    <div class="row m-row--no-padding align-items-center">
                                                        <div class="col">
                                                            <h5 class="m-widget1__title">Data</h5>
                                                        </div>
                                                        <div class="col m--align-right">
                                                            <span class="m-widget1__number m--font-info" style="font-size:14px;">
                                                                <?= date('d/m/Y', strtotime($evento['data_evento'])) ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="m-widget1__item">
                                                    <div class="row m-row--no-padding align-items-center">
                                                        <div class="col">
                                                            <h5 class="m-widget1__title">Tipo</h5>
                                                        </div>
                                                        <div class="col m--align-right">
                                                            <span class="m-badge <?= $tipo_badge[$evento['tipo']] ?> m-badge--wide">
                                                                <?= $tipos_evento[$evento['tipo']] ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="m-widget1__item">
                                                    <div class="row m-row--no-padding align-items-center">
                                                        <div class="col">
                                                            <h5 class="m-widget1__title">Convocados</h5>
                                                        </div>
                                                        <div class="col m--align-right">
                                                            <span class="m-widget1__number m--font-metal" style="font-size:14px;">
                                                                <?= $freq['total'] ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="m-widget1__item">
                                                    <div class="row m-row--no-padding align-items-center">
                                                        <div class="col">
                                                            <h5 class="m-widget1__title">Presença</h5>
                                                        </div>
                                                        <div class="col m--align-right">
                                                            <span class="m-widget1__number <?= $pc_pres >= 70 ? 'm--font-success' : ($pc_pres >= 50 ? 'm--font-warning' : 'm--font-danger') ?>" style="font-size:14px;">
                                                                <?= $freq['presentes'] ?>/<?= $freq['total'] ?>
                                                                (<?= $pc_pres ?>%)
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php if ($freq['total'] > 0): ?>
                                            <div class="progress m-progress--sm m--margin-top-10">
                                                <div class="progress-bar <?= $pc_pres >= 70 ? 'm--bg-success' : ($pc_pres >= 50 ? 'm--bg-warning' : 'm--bg-danger') ?>"
                                                     style="width:<?= $pc_pres ?>%"></div>
                                            </div>
                                            <?php endif; ?>

                                            <div class="m--margin-top-15">
                                                <a href="frequencia/chamada.php?id=<?= $id ?>"
                                                   class="btn btn-info btn-block m-btn m-btn--icon m-btn--pill">
                                                    <span><i class="la la-check-square-o"></i><span>Abrir Chamada</span></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Zona de exclusão -->
                                    <div class="m-portlet m-portlet--mobile" style="border: 1px solid #f4516c;">
                                        <div class="m-portlet__head" style="background:#fff5f6; border-bottom:1px solid #f4516c;">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text m--font-danger">
                                                        <i class="la la-exclamation-triangle"></i>
                                                        Zona de Perigo
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">
                                            <p class="m--font-metal" style="font-size:13px;">
                                                Excluir este evento removerá permanentemente
                                                <strong>todos os <?= $freq['total'] ?> registros de presença</strong> associados.
                                                Esta ação não pode ser desfeita.
                                            </p>
                                            <button type="button"
                                                    class="btn btn-danger btn-block m-btn m-btn--icon m-btn--pill"
                                                    onclick="confirmarExclusao()">
                                                <span><i class="la la-trash"></i><span>Excluir Evento</span></span>
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de confirmação de exclusão -->
            <div class="modal fade" id="modalExclusao" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header" style="background:#fff5f6; border-bottom:1px solid #f4516c;">
                            <h5 class="modal-title m--font-danger">
                                <i class="la la-exclamation-triangle"></i> Confirmar Exclusão
                            </h5>
                        </div>
                        <div class="modal-body">
                            <p>Tem certeza que deseja excluir o evento:</p>
                            <p><strong><?= htmlspecialchars($evento['nome']) ?></strong></p>
                            <p class="m--font-danger" style="font-size:12px;">
                                <?= $freq['total'] ?> registro<?= $freq['total'] != 1 ? 's' : '' ?> de presença
                                também será<?= $freq['total'] != 1 ? 'ão' : '' ?> excluído<?= $freq['total'] != 1 ? 's' : '' ?>.
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary m-btn m-btn--pill" data-dismiss="modal">
                                Cancelar
                            </button>
                            <form method="POST" action="eventos/evento_delete.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $id ?>">
                                <input type="hidden" name="mes" value="<?= $mes_ev ?>">
                                <input type="hidden" name="ano" value="<?= $ano_ev ?>">
                                <button type="submit" class="btn btn-danger m-btn m-btn--pill">
                                    <i class="la la-trash"></i> Sim, excluir
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php require_once ('../inc/footer.php'); ?>
        </div>

        <div class="m-scroll-top m-scroll-top--skin-top" data-toggle="m-scroll-top" data-scroll-offset="500" data-scroll-speed="300">
            <i class="la la-arrow-up"></i>
        </div>
        <script src="js/vendors.bundle.js" type="text/javascript"></script>
        <script src="js/scripts.bundle.js" type="text/javascript"></script>
        <script>
        function confirmarExclusao() {
            $('#modalExclusao').modal('show');
        }
        </script>
    </body>
</html>
