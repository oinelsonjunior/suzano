<?php
    session_start();
    require_once ('../inc/general.php');

    $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
    $ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');

    $meses_pt = [
        1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',
        5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',
        9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'
    ];
    $nome_mes = $meses_pt[$mes] ?? $mes;

    // Todos os registros do mês
    $stmt = $conn->prepare("
        SELECT m.id, m.pago, m.isento, m.valor_total, m.valor_repasse, m.valor_faccao,
               m.data_pagamento, m.patente_no_mes, m.status_no_mes,
               c.apelido, c.nome
        FROM mensalidades m
        JOIN cadastro_integrante c ON c.id = m.integrante_id
        WHERE m.mes = ? AND m.ano = ?
        ORDER BY c.apelido ASC
    ");
    $stmt->bind_param("ii", $mes, $ano);
    $stmt->execute();
    $registros = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // KPIs
    $total      = count($registros);
    $pagos      = array_filter($registros, fn($r) => $r['pago'] == 1 && $r['isento'] == 0);
    $pendentes  = array_filter($registros, fn($r) => $r['pago'] == 0 && $r['isento'] == 0);
    $isentos    = array_filter($registros, fn($r) => $r['isento'] == 1);

    $total_pagos     = count($pagos);
    $total_pendentes = count($pendentes);
    $total_isentos   = count($isentos);
    $total_obrigados = $total - $total_isentos;

    $valor_arrecadado = array_sum(array_column(array_values($pagos), 'valor_total'));
    $valor_em_aberto  = array_sum(array_column(array_values($pendentes), 'valor_total'));

    $pc_pago     = $total_obrigados > 0 ? round(($total_pagos     / $total_obrigados) * 100) : 0;
    $pc_pendente = $total_obrigados > 0 ? round(($total_pendentes / $total_obrigados) * 100) : 0;
    $pc_isento   = $total > 0           ? round(($total_isentos   / $total)           * 100) : 0;

    function patente_label($p) {
        $map = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X'];
        return $map[$p] ?? '—';
    }

    function status_label($s) {
        $map = [1=>'Ativo', 2=>'Afastado', 3=>'Desligado', 4=>'Suspenso'];
        return $map[$s] ?? '—';
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
    <base href="../">
    <head>
        <meta charset="utf-8" />
        <title>Abutre's MC | Mensalidades</title>
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

            <!-- HEADER -->
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

            <!-- BODY -->
            <div class="m-grid__item m-grid__item--fluid m-grid m-grid--hor-desktop m-grid--desktop m-body">
                <div class="m-grid__item m-grid__item--fluid m-grid m-grid--ver m-container m-container--responsive m-container--xxl m-page__container">
                    <div class="m-grid__item m-grid__item--fluid m-wrapper">

                        <!-- Subheader -->
                        <div class="m-subheader">
                            <div class="d-flex align-items-center">
                                <div class="mr-auto">
                                    <h3 class="m-subheader__title" style="text-transform:uppercase;">
                                        Mensalidades &mdash; <?= $nome_mes ?>/<?= $ano ?>
                                    </h3>
                                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                                        <li class="m-nav__item m-nav__item--home">
                                            <a href="index" class="m-nav__link m-nav__link--icon">
                                                <i class="m-nav__link-icon la la-home"></i>
                                            </a>
                                        </li>
                                        <li class="m-nav__separator">—</li>
                                        <li class="m-nav__item">
                                            <a href="financeiro/dashboard?mes=<?= $mes ?>&ano=<?= $ano ?>" class="m-nav__link">
                                                <span class="m-nav__link-text">Financeiro</span>
                                            </a>
                                        </li>
                                        <li class="m-nav__separator">—</li>
                                        <li class="m-nav__item">
                                            <span class="m-nav__link-text">Mensalidades</span>
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <a href="financeiro/pendentes.php?mes=<?= $mes ?>&ano=<?= $ano ?>"
                                       class="btn btn-sm btn-warning m-btn m-btn--icon m-btn--pill">
                                        <span>
                                            <i class="la la-exclamation-circle"></i>
                                            <span>Pendentes (<?= $total_pendentes ?>)</span>
                                        </span>
                                    </a>
                                    &nbsp;
                                    <a href="financeiro/exportar_excel.php?mes=<?= $mes ?>&ano=<?= $ano ?>"
                                       class="btn btn-sm btn-success m-btn m-btn--icon m-btn--pill">
                                        <span>
                                            <i class="la la-file-excel-o"></i>
                                            <span>Exportar Excel</span>
                                        </span>
                                    </a>
                                    &nbsp;
                                    <a href="financeiro/dashboard?mes=<?= $mes ?>&ano=<?= $ano ?>"
                                       class="btn btn-sm btn-secondary m-btn m-btn--icon m-btn--pill">
                                        <span>
                                            <i class="la la-arrow-left"></i>
                                            <span>Dashboard</span>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="m-content">

                            <!-- FILTRO -->
                            <div class="m-portlet m-portlet--mobile">
                                <div class="m-portlet__body">
                                    <form class="form-inline" method="GET">
                                        <label class="mr-2 font-weight-bold">Período:</label>
                                        <select name="mes" class="form-control form-control-sm mr-2">
                                            <?php foreach ($meses_pt as $num => $nome): ?>
                                                <option value="<?= $num ?>" <?= $num == $mes ? 'selected' : '' ?>>
                                                    <?= $nome ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="number" name="ano" class="form-control form-control-sm mr-2"
                                               value="<?= $ano ?>" min="2020" max="2099" style="width:90px;">
                                        <button class="btn btn-sm btn-dark m-btn m-btn--icon m-btn--pill" type="submit">
                                            <span>
                                                <i class="la la-search"></i>
                                                <span>Filtrar</span>
                                            </span>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- KPIs -->
                            <div class="m-portlet m-portlet--mobile">
                                <div class="m-portlet__body m-portlet__body--no-padding">
                                    <div class="row m-row--no-padding m-row--col-separator-xl">

                                        <div class="col-md-12 col-lg-3 col-xl-3">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Pagos</h4><br>
                                                    <span class="m-widget24__desc">R$ <?= number_format($valor_arrecadado, 2, ',', '.') ?> arrecadados</span>
                                                    <span class="m-widget24__stats m--font-success"><?= $total_pagos ?></span>
                                                    <div class="m--space-10"></div>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-success" style="width: <?= $pc_pago ?>%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Dos obrigados</span>
                                                    <span class="m-widget24__number"><?= $pc_pago ?>%</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-lg-3 col-xl-3">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Pendentes</h4><br>
                                                    <span class="m-widget24__desc">R$ <?= number_format($valor_em_aberto, 2, ',', '.') ?> em aberto</span>
                                                    <span class="m-widget24__stats m--font-warning"><?= $total_pendentes ?></span>
                                                    <div class="m--space-10"></div>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-warning" style="width: <?= $pc_pendente ?>%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Dos obrigados</span>
                                                    <span class="m-widget24__number"><?= $pc_pendente ?>%</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-lg-3 col-xl-3">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Isentos</h4><br>
                                                    <span class="m-widget24__desc">Patente VI ou manual</span>
                                                    <span class="m-widget24__stats m--font-metal"><?= $total_isentos ?></span>
                                                    <div class="m--space-10"></div>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-metal" style="width: <?= $pc_isento ?>%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Do total de membros</span>
                                                    <span class="m-widget24__number"><?= $pc_isento ?>%</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-lg-3 col-xl-3">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Total de Membros</h4><br>
                                                    <span class="m-widget24__desc">No mês</span>
                                                    <span class="m-widget24__stats m--font-info"><?= $total ?></span>
                                                    <div class="m--space-10"></div>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-info" style="width: 100%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Obrigados a pagar</span>
                                                    <span class="m-widget24__number"><?= $total_obrigados ?></span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- TABELA -->
                            <div class="m-portlet m-portlet--mobile">
                                <div class="m-portlet__head">
                                    <div class="m-portlet__head-caption">
                                        <div class="m-portlet__head-title">
                                            <h3 class="m-portlet__head-text">
                                                Todos os Registros &mdash; <?= $nome_mes ?>/<?= $ano ?>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="m-portlet__body">

                                    <?php if (empty($registros)): ?>
                                        <div class="m--align-center" style="padding: 40px 0;">
                                            <i class="la la-inbox m--font-metal" style="font-size: 4rem;"></i>
                                            <h3 class="m--margin-top-10 m--font-metal">Nenhum registro encontrado para este período.</h3>
                                        </div>
                                    <?php else: ?>

                                    <div class="table-responsive">
                                        <table class="table table-hover m-table m-table--head-bg-info">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Apelido</th>
                                                    <th>Nome</th>
                                                    <th class="m--align-center">Patente</th>
                                                    <th class="m--align-center">Status</th>
                                                    <th class="m--align-center">Situação</th>
                                                    <th class="m--align-right">Total</th>
                                                    <th class="m--align-right">Repasse</th>
                                                    <th class="m--align-right">Facção</th>
                                                    <th class="m--align-center">Pagamento</th>
                                                    <th class="m--align-center">Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($registros as $i => $r): ?>
                                                <tr>
                                                    <td class="m--font-metal"><?= $i + 1 ?></td>
                                                    <td class="m--font-boldest">
                                                        <?= htmlspecialchars($r['apelido']) ?>
                                                    </td>
                                                    <td class="m--font-metal">
                                                        <?= htmlspecialchars($r['nome']) ?>
                                                    </td>
                                                    <td class="m--align-center">
                                                        <span class="m-badge m-badge--info m-badge--wide">
                                                            <?= patente_label($r['patente_no_mes']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="m--align-center">
                                                        <?php $st = status_label($r['status_no_mes']); ?>
                                                        <?php if ($r['status_no_mes'] == 1): ?>
                                                            <span class="m-badge m-badge--success m-badge--wide"><?= $st ?></span>
                                                        <?php elseif ($r['status_no_mes'] == 2): ?>
                                                            <span class="m-badge m-badge--warning m-badge--wide"><?= $st ?></span>
                                                        <?php elseif ($r['status_no_mes'] == 3): ?>
                                                            <span class="m-badge m-badge--danger m-badge--wide"><?= $st ?></span>
                                                        <?php else: ?>
                                                            <span class="m-badge m-badge--metal m-badge--wide"><?= $st ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="m--align-center">
                                                        <?php if ($r['isento']): ?>
                                                            <span class="m-badge m-badge--metal m-badge--wide">Isento</span>
                                                        <?php elseif ($r['pago']): ?>
                                                            <span class="m-badge m-badge--success m-badge--wide">Pago</span>
                                                        <?php else: ?>
                                                            <span class="m-badge m-badge--warning m-badge--wide">Pendente</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="m--align-right <?= (!$r['isento'] && !$r['pago']) ? 'm--font-danger m--font-boldest' : '' ?>">
                                                        R$ <?= number_format($r['valor_total'], 2, ',', '.') ?>
                                                    </td>
                                                    <td class="m--align-right m--font-metal">
                                                        R$ <?= number_format($r['valor_repasse'], 2, ',', '.') ?>
                                                    </td>
                                                    <td class="m--align-right m--font-metal">
                                                        R$ <?= number_format($r['valor_faccao'], 2, ',', '.') ?>
                                                    </td>
                                                    <td class="m--align-center m--font-metal" style="font-size: 12px;">
                                                        <?= $r['data_pagamento'] ? date('d/m/Y', strtotime($r['data_pagamento'])) : '—' ?>
                                                    </td>
                                                    <td class="m--align-center" style="white-space: nowrap;">

                                                        <?php if (!$r['isento']): ?>
                                                            <?php if ($r['pago']): ?>
                                                                <a href="financeiro/pagar.php?id=<?= $r['id'] ?>&mes=<?= $mes ?>&ano=<?= $ano ?>&origem=list"
                                                                   class="btn btn-sm btn-warning m-btn m-btn--icon m-btn--pill"
                                                                   title="Desfazer pagamento"
                                                                   onclick="return confirm('Desfazer pagamento de <?= htmlspecialchars(addslashes($r['apelido'])) ?>?')">
                                                                    <span>
                                                                        <i class="la la-undo"></i>
                                                                        <span>Desfazer</span>
                                                                    </span>
                                                                </a>
                                                            <?php else: ?>
                                                                <a href="financeiro/pagar.php?id=<?= $r['id'] ?>&mes=<?= $mes ?>&ano=<?= $ano ?>&origem=list"
                                                                   class="btn btn-sm btn-success m-btn m-btn--icon m-btn--pill"
                                                                   title="Confirmar pagamento"
                                                                   onclick="return confirm('Confirmar pagamento de <?= htmlspecialchars(addslashes($r['apelido'])) ?>?')">
                                                                    <span>
                                                                        <i class="la la-check"></i>
                                                                        <span>Pago</span>
                                                                    </span>
                                                                </a>
                                                            <?php endif; ?>
                                                        <?php endif; ?>

                                                        <?php if (!$r['pago']): ?>
                                                            <?php if ($r['isento']): ?>
                                                                <a href="financeiro/isento_form.php?id=<?= $r['id'] ?>&mes=<?= $mes ?>&ano=<?= $ano ?>&origem=list"
                                                                   class="btn btn-sm btn-secondary m-btn m-btn--icon m-btn--pill"
                                                                   title="Remover isenção"
                                                                   onclick="return confirm('Remover isenção de <?= htmlspecialchars(addslashes($r['apelido'])) ?>?')">
                                                                    <span>
                                                                        <i class="la la-times-circle"></i>
                                                                        <span>Remover Isenção</span>
                                                                    </span>
                                                                </a>
                                                            <?php else: ?>
                                                                <a href="financeiro/isento_form.php?id=<?= $r['id'] ?>&mes=<?= $mes ?>&ano=<?= $ano ?>&origem=list"
                                                                   class="btn btn-sm btn-info m-btn m-btn--icon m-btn--pill"
                                                                   title="Marcar como isento"
                                                                   onclick="return confirm('Isentar <?= htmlspecialchars(addslashes($r['apelido'])) ?> neste mês?')">
                                                                    <span>
                                                                        <i class="la la-minus-circle"></i>
                                                                        <span>Isentar</span>
                                                                    </span>
                                                                </a>
                                                            <?php endif; ?>
                                                        <?php endif; ?>

                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr class="m-table__foot m--font-boldest">
                                                    <td colspan="6">
                                                        Total — <?= $total ?> registros
                                                        (<?= $total_pagos ?> pagos,
                                                         <?= $total_pendentes ?> pendentes,
                                                         <?= $total_isentos ?> isentos)
                                                    </td>
                                                    <td class="m--align-right m--font-success">
                                                        R$ <?= number_format($valor_arrecadado, 2, ',', '.') ?>
                                                    </td>
                                                    <td class="m--align-right m--font-metal">
                                                        R$ <?= number_format(array_sum(array_column(array_values($pagos), 'valor_repasse')), 2, ',', '.') ?>
                                                    </td>
                                                    <td class="m--align-right m--font-metal">
                                                        R$ <?= number_format(array_sum(array_column(array_values($pagos), 'valor_faccao')), 2, ',', '.') ?>
                                                    </td>
                                                    <td colspan="2"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <?php endif; ?>

                                </div>
                            </div>

                        </div>
                        <!-- /m-content -->

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

    </body>
</html>