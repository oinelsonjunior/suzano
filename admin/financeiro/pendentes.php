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

    // Pendentes do mês
    $stmt = $conn->prepare("
        SELECT m.id, m.valor_total, m.valor_repasse, m.valor_faccao, m.patente_no_mes,
               c.apelido, c.nome
        FROM mensalidades m
        JOIN cadastro_integrante c ON c.id = m.integrante_id
        WHERE m.pago = 0 AND m.isento = 0
          AND m.mes = ? AND m.ano = ?
        ORDER BY c.apelido ASC
    ");
    $stmt->bind_param("ii", $mes, $ano);
    $stmt->execute();
    $pendentes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Totais para os KPIs
    $total_pendentes   = count($pendentes);
    $valor_em_aberto   = array_sum(array_column($pendentes, 'valor_total'));

    // Total geral do mês (para calcular % de inadimplência)
    $stmt2 = $conn->prepare("
        SELECT COUNT(*) as total, SUM(pago=1 AND isento=0) as pagos
        FROM mensalidades WHERE mes = ? AND ano = ?
    ");
    $stmt2->bind_param("ii", $mes, $ano);
    $stmt2->execute();
    $totais = $stmt2->get_result()->fetch_assoc();
    $total_geral  = (int)$totais['total'];
    $total_pagos  = (int)$totais['pagos'];
    $pc_inadimp   = $total_geral > 0 ? round(($total_pendentes / $total_geral) * 100) : 0;
    $pc_pago      = $total_geral > 0 ? round(($total_pagos     / $total_geral) * 100) : 0;

    function patente_label($p) {
        $map = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X'];
        return $map[$p] ?? '—';
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
    <base href="../">
    <head>
        <meta charset="utf-8" />
        <title>Abutre's MC | Pendentes</title>
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
                                        Pendentes &mdash; <?= $nome_mes ?>/<?= $ano ?>
                                    </h3>
                                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                                        <li class="m-nav__item m-nav__item--home">
                                            <a href="index" class="m-nav__link m-nav__link--icon">
                                                <i class="m-nav__link-icon la la-home"></i>
                                            </a>
                                        </li>
                                        <li class="m-nav__separator">—</li>
                                        <li class="m-nav__item">
                                            <a href="financeiro/dashboard" class="m-nav__link">
                                                <span class="m-nav__link-text">Financeiro</span>
                                            </a>
                                        </li>
                                        <li class="m-nav__separator">—</li>
                                        <li class="m-nav__item">
                                            <span class="m-nav__link-text">Pendentes</span>
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <a href="financeiro/dashboard?mes=<?= $mes ?>&ano=<?= $ano ?>"
                                       class="btn btn-sm btn-secondary m-btn m-btn--icon m-btn--pill">
                                        <span>
                                            <i class="la la-arrow-left"></i>
                                            <span>Voltar ao Dashboard</span>
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

                                        <div class="col-md-12 col-lg-4 col-xl-4">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Inadimplentes</h4><br>
                                                    <span class="m-widget24__desc">Membros com pagamento em aberto</span>
                                                    <span class="m-widget24__stats m--font-warning">
                                                        <?= $total_pendentes ?>
                                                    </span>
                                                    <div class="m--space-10"></div>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-warning" style="width: <?= $pc_inadimp ?>%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Do total do mês</span>
                                                    <span class="m-widget24__number"><?= $pc_inadimp ?>%</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-lg-4 col-xl-4">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Valor em Aberto</h4><br>
                                                    <span class="m-widget24__desc">Total a receber no mês</span>
                                                    <span class="m-widget24__stats m--font-danger">
                                                        R$ <?= number_format($valor_em_aberto, 2, ',', '.') ?>
                                                    </span>
                                                    <div class="m--space-10"></div>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-danger" style="width: <?= $pc_inadimp ?>%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Inadimplência do mês</span>
                                                    <span class="m-widget24__number"><?= $pc_inadimp ?>%</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-lg-4 col-xl-4">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Já Pagaram</h4><br>
                                                    <span class="m-widget24__desc">Membros em dia no mês</span>
                                                    <span class="m-widget24__stats m--font-success">
                                                        <?= $total_pagos ?>
                                                    </span>
                                                    <div class="m--space-10"></div>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-success" style="width: <?= $pc_pago ?>%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Do total do mês</span>
                                                    <span class="m-widget24__number"><?= $pc_pago ?>%</span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- TABELA DE PENDENTES -->
                            <div class="m-portlet m-portlet--mobile">
                                <div class="m-portlet__head">
                                    <div class="m-portlet__head-caption">
                                        <div class="m-portlet__head-title">
                                            <h3 class="m-portlet__head-text">
                                                <i class="la la-exclamation-triangle m--font-warning"></i>
                                                Lista de Pendentes — <?= $nome_mes ?>/<?= $ano ?>
                                            </h3>
                                        </div>
                                    </div>
                                    <div class="m-portlet__head-tools">
                                        <a href="financeiro/list.php?mes=<?= $mes ?>&ano=<?= $ano ?>"
                                           class="btn btn-sm btn-outline-info m-btn m-btn--pill">
                                            <span>
                                                <i class="la la-list"></i>
                                                Ver todos do mês
                                            </span>
                                        </a>
                                    </div>
                                </div>
                                <div class="m-portlet__body">

                                    <?php if (empty($pendentes)): ?>
                                        <div class="m--align-center" style="padding: 40px 0;">
                                            <i class="la la-check-circle m--font-success" style="font-size: 4rem;"></i>
                                            <h3 class="m--margin-top-10 m--font-success">Todos pagaram em <?= $nome_mes ?>!</h3>
                                            <p class="m--font-metal">Nenhuma mensalidade pendente neste mês.</p>
                                        </div>
                                    <?php else: ?>

                                        <div class="table-responsive">
                                            <table class="table table-hover m-table m-table--head-bg-warning">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Apelido</th>
                                                        <th>Nome</th>
                                                        <th>Patente</th>
                                                        <th class="m--align-right">Valor</th>
                                                        <th class="m--align-right">Repasse</th>
                                                        <th class="m--align-right">Facção</th>
                                                        <th class="m--align-center">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($pendentes as $i => $r): ?>
                                                    <tr>
                                                        <td class="m--font-metal"><?= $i + 1 ?></td>
                                                        <td>
                                                            <span class="m--font-boldest">
                                                                <?= htmlspecialchars($r['apelido']) ?>
                                                            </span>
                                                        </td>
                                                        <td class="m--font-metal">
                                                            <?= htmlspecialchars($r['nome']) ?>
                                                        </td>
                                                        <td>
                                                            <span class="m-badge m-badge--info m-badge--wide">
                                                                Pat. <?= patente_label($r['patente_no_mes']) ?>
                                                            </span>
                                                        </td>
                                                        <td class="m--align-right m--font-danger m--font-boldest">
                                                            R$ <?= number_format($r['valor_total'], 2, ',', '.') ?>
                                                        </td>
                                                        <td class="m--align-right m--font-metal">
                                                            R$ <?= number_format($r['valor_repasse'], 2, ',', '.') ?>
                                                        </td>
                                                        <td class="m--align-right m--font-metal">
                                                            R$ <?= number_format($r['valor_faccao'], 2, ',', '.') ?>
                                                        </td>
                                                        <td class="m--align-center" style="white-space: nowrap;">
                                                            <!-- Marcar como pago -->
                                                            <a href="financeiro/pagar.php?id=<?= $r['id'] ?>&mes=<?= $mes ?>&ano=<?= $ano ?>"
                                                               class="btn btn-sm btn-success m-btn m-btn--icon m-btn--pill"
                                                               title="Confirmar pagamento"
                                                               onclick="return confirm('Confirmar pagamento de <?= htmlspecialchars(addslashes($r['apelido'])) ?>?')">
                                                                <span>
                                                                    <i class="la la-check"></i>
                                                                    <span>Pago</span>
                                                                </span>
                                                            </a>
                                                            &nbsp;
                                                            <!-- Isentar -->
                                                            <a href="financeiro/isento_form.php?id=<?= $r['id'] ?>&mes=<?= $mes ?>&ano=<?= $ano ?>"
                                                               class="btn btn-sm btn-secondary m-btn m-btn--icon m-btn--pill"
                                                               title="Marcar como isento"
                                                               onclick="return confirm('Isentar <?= htmlspecialchars(addslashes($r['apelido'])) ?> neste mês?')">
                                                                <span>
                                                                    <i class="la la-minus-circle"></i>
                                                                    <span>Isentar</span>
                                                                </span>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr class="m-table__foot">
                                                        <td colspan="4" class="m--font-boldest">
                                                            Total em aberto (<?= $total_pendentes ?> membro<?= $total_pendentes != 1 ? 's' : '' ?>)
                                                        </td>
                                                        <td class="m--align-right m--font-danger m--font-boldest">
                                                            R$ <?= number_format($valor_em_aberto, 2, ',', '.') ?>
                                                        </td>
                                                        <td class="m--align-right m--font-metal">
                                                            R$ <?= number_format(array_sum(array_column($pendentes, 'valor_repasse')), 2, ',', '.') ?>
                                                        </td>
                                                        <td class="m--align-right m--font-metal">
                                                            R$ <?= number_format(array_sum(array_column($pendentes, 'valor_faccao')), 2, ',', '.') ?>
                                                        </td>
                                                        <td></td>
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