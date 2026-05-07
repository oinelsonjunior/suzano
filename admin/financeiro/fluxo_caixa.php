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

    // --- Entradas: mensalidades pagas no mês ---
    $stmt = $conn->prepare("
        SELECT DATE(data_pagamento) as data, SUM(valor_total) as total
        FROM mensalidades
        WHERE pago = 1 AND mes = ? AND ano = ?
        GROUP BY DATE(data_pagamento)
        ORDER BY DATE(data_pagamento) ASC
    ");
    $stmt->bind_param("ii", $mes, $ano);
    $stmt->execute();
    $entradas = [];
    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
        $entradas[$r['data']] = (float)$r['total'];
    }

    // --- Saídas do mês ---
    $stmt2 = $conn->prepare("
        SELECT DATE(data_saida) as data, SUM(valor) as total
        FROM caixa_saida
        WHERE MONTH(data_saida) = ? AND YEAR(data_saida) = ?
        GROUP BY DATE(data_saida)
        ORDER BY DATE(data_saida) ASC
    ");
    $stmt2->bind_param("ii", $mes, $ano);
    $stmt2->execute();
    $saidas = [];
    foreach ($stmt2->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
        $saidas[$r['data']] = (float)$r['total'];
    }

    // --- Montar linhas do fluxo ---
    $datas = array_unique(array_merge(array_keys($entradas), array_keys($saidas)));
    sort($datas);

    $saldo_acum = 0;
    $rows       = [];
    $labels     = [];
    $dados_entrada = [];
    $dados_saida   = [];
    $dados_saldo   = [];

    foreach ($datas as $data) {
        $entrada = $entradas[$data] ?? 0;
        $saida   = $saidas[$data]   ?? 0;
        $saldo_acum += ($entrada - $saida);

        $labels[]        = date('d/m', strtotime($data));
        $dados_entrada[] = $entrada;
        $dados_saida[]   = $saida;
        $dados_saldo[]   = $saldo_acum;

        $rows[] = [
            'data'    => $data,
            'entrada' => $entrada,
            'saida'   => $saida,
            'saldo'   => $saldo_acum,
        ];
    }

    // --- KPIs ---
    $total_entrada = array_sum($entradas);
    $total_saida   = array_sum($saidas);
    $saldo_final   = $total_entrada - $total_saida;

    // --- Detalhamento das saídas do mês ---
    $stmt3 = $conn->prepare("
        SELECT id, descricao, valor, data_saida
        FROM caixa_saida
        WHERE MONTH(data_saida) = ? AND YEAR(data_saida) = ?
        ORDER BY data_saida DESC
    ");
    $stmt3->bind_param("ii", $mes, $ano);
    $stmt3->execute();
    $saidas_detalhe = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
    <base href="../">
    <head>
        <meta charset="utf-8" />
        <title>Abutre's MC | Fluxo de Caixa</title>
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
                                        Fluxo de Caixa &mdash; <?= $nome_mes ?>/<?= $ano ?>
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
                                            <span class="m-nav__link-text">Fluxo de Caixa</span>
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <a href="financeiro/saida_list"
                                       class="btn btn-sm btn-danger m-btn m-btn--icon m-btn--pill">
                                        <span>
                                            <i class="la la-plus"></i>
                                            <span>Ver Saídas</span>
                                        </span>
                                    </a>
                                    <a href="financeiro/saida_form.php?mes=<?= $mes ?>&ano=<?= $ano ?>"
                                       class="btn btn-sm btn-danger m-btn m-btn--icon m-btn--pill">
                                        <span>
                                            <i class="la la-plus"></i>
                                            <span>Nova Saída</span>
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

                                        <div class="col-md-12 col-lg-4 col-xl-4">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Entradas</h4><br>
                                                    <span class="m-widget24__desc">Mensalidades pagas em <?= $nome_mes ?></span>
                                                    <span class="m-widget24__stats m--font-success">
                                                        R$ <?= number_format($total_entrada, 2, ',', '.') ?>
                                                    </span>
                                                    <div class="m--space-10"></div>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-success" style="width: 100%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Total de receitas do mês</span>
                                                    <span class="m-widget24__number">100%</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-lg-4 col-xl-4">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Saídas</h4><br>
                                                    <span class="m-widget24__desc">Despesas registradas em <?= $nome_mes ?></span>
                                                    <span class="m-widget24__stats m--font-danger">
                                                        R$ <?= number_format($total_saida, 2, ',', '.') ?>
                                                    </span>
                                                    <div class="m--space-10"></div>
                                                    <?php $pc_saida = $total_entrada > 0 ? round(($total_saida / $total_entrada) * 100) : 0; ?>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-danger" style="width: <?= min($pc_saida, 100) ?>%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Sobre as entradas do mês</span>
                                                    <span class="m-widget24__number"><?= $pc_saida ?>%</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-lg-4 col-xl-4">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Saldo do Mês</h4><br>
                                                    <span class="m-widget24__desc">Entradas menos saídas</span>
                                                    <span class="m-widget24__stats <?= $saldo_final >= 0 ? 'm--font-brand' : 'm--font-danger' ?>">
                                                        R$ <?= number_format($saldo_final, 2, ',', '.') ?>
                                                    </span>
                                                    <div class="m--space-10"></div>
                                                    <?php $pc_saldo = $total_entrada > 0 ? round(($saldo_final / $total_entrada) * 100) : 0; ?>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar <?= $saldo_final >= 0 ? 'm--bg-brand' : 'm--bg-danger' ?>"
                                                             style="width: <?= min(abs($pc_saldo), 100) ?>%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Sobre as entradas</span>
                                                    <span class="m-widget24__number"><?= $pc_saldo ?>%</span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- GRÁFICO -->
                            <div class="m-portlet m-portlet--mobile">
                                <div class="m-portlet__head">
                                    <div class="m-portlet__head-caption">
                                        <div class="m-portlet__head-title">
                                            <h3 class="m-portlet__head-text">
                                                Evolução do Saldo — <?= $nome_mes ?>/<?= $ano ?>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="m-portlet__body">
                                    <?php if (empty($rows)): ?>
                                        <div class="m--align-center" style="padding: 40px 0;">
                                            <i class="la la-bar-chart m--font-metal" style="font-size: 4rem;"></i>
                                            <h3 class="m--margin-top-10 m--font-metal">Nenhuma movimentação neste período.</h3>
                                        </div>
                                    <?php else: ?>
                                        <div style="position: relative; height: 280px;">
                                            <canvas id="graficoFluxo"></canvas>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- TABELA DE MOVIMENTAÇÕES + SAÍDAS DETALHADAS -->
                            <div class="row">

                                <!-- Movimentações diárias -->
                                <div class="col-xl-8">
                                    <div class="m-portlet m-portlet--full-height">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">
                                                        Movimentações por Dia — <?= $nome_mes ?>/<?= $ano ?>
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">
                                            <?php if (empty($rows)): ?>
                                                <div class="m--align-center m--margin-top-20 m--font-metal">
                                                    Nenhuma movimentação neste período.
                                                </div>
                                            <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover m-table m-table--head-bg-metal">
                                                    <thead>
                                                        <tr>
                                                            <th>Data</th>
                                                            <th class="m--align-right">Entrada</th>
                                                            <th class="m--align-right">Saída</th>
                                                            <th class="m--align-right">Saldo Acumulado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($rows as $r): ?>
                                                        <tr>
                                                            <td class="m--font-metal">
                                                                <?= date('d/m/Y', strtotime($r['data'])) ?>
                                                            </td>
                                                            <td class="m--align-right <?= $r['entrada'] > 0 ? 'm--font-success m--font-boldest' : 'm--font-metal' ?>">
                                                                <?= $r['entrada'] > 0 ? 'R$ ' . number_format($r['entrada'], 2, ',', '.') : '—' ?>
                                                            </td>
                                                            <td class="m--align-right <?= $r['saida'] > 0 ? 'm--font-danger m--font-boldest' : 'm--font-metal' ?>">
                                                                <?= $r['saida'] > 0 ? 'R$ ' . number_format($r['saida'], 2, ',', '.') : '—' ?>
                                                            </td>
                                                            <td class="m--align-right m--font-boldest <?= $r['saldo'] >= 0 ? 'm--font-brand' : 'm--font-danger' ?>">
                                                                R$ <?= number_format($r['saldo'], 2, ',', '.') ?>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="m-table__foot m--font-boldest">
                                                            <td>Total</td>
                                                            <td class="m--align-right m--font-success">
                                                                R$ <?= number_format($total_entrada, 2, ',', '.') ?>
                                                            </td>
                                                            <td class="m--align-right m--font-danger">
                                                                R$ <?= number_format($total_saida, 2, ',', '.') ?>
                                                            </td>
                                                            <td class="m--align-right <?= $saldo_final >= 0 ? 'm--font-brand' : 'm--font-danger' ?>">
                                                                R$ <?= number_format($saldo_final, 2, ',', '.') ?>
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Saídas detalhadas do mês -->
                                <div class="col-xl-4">
                                    <div class="m-portlet m-portlet--full-height">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">
                                                        <i class="la la-arrow-circle-down m--font-danger"></i>
                                                        Saídas — <?= $nome_mes ?>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="m-portlet__head-tools">
                                                <a href="financeiro/saida_form.php?mes=<?= $mes ?>&ano=<?= $ano ?>"
                                                   class="btn btn-sm btn-outline-danger m-btn m-btn--pill">
                                                    <i class="la la-plus"></i> Nova
                                                </a>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">
                                            <?php if (empty($saidas_detalhe)): ?>
                                                <div class="m--align-center m--margin-top-20 m--font-metal">
                                                    Nenhuma saída registrada neste mês.
                                                </div>
                                            <?php else: ?>
                                            <div class="m-widget4">
                                                <?php foreach ($saidas_detalhe as $s): ?>
                                                <div class="m-widget4__item">
                                                    <div class="m-widget4__img m-widget4__img--icon">
                                                        <i class="la la-arrow-circle-down m--font-danger" style="font-size: 2rem;"></i>
                                                    </div>
                                                    <div class="m-widget4__info">
                                                        <span class="m-widget4__title">
                                                            <?= htmlspecialchars($s['descricao']) ?>
                                                        </span>
                                                        <br>
                                                        <span class="m-widget4__sub">
                                                            <?= date('d/m/Y', strtotime($s['data_saida'])) ?>
                                                        </span>
                                                    </div>
                                                    <span class="m-widget4__ext">
                                                        <span class="m-widget4__number m--font-danger">
                                                            R$ <?= number_format($s['valor'], 2, ',', '.') ?>
                                                        </span>
                                                    </span>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
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
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <?php if (!empty($rows)): ?>
        <script>
        new Chart(document.getElementById('graficoFluxo'), {
            type: 'line',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [
                    {
                        label: 'Entradas',
                        data: <?= json_encode($dados_entrada) ?>,
                        borderColor: '#34bfa3',
                        backgroundColor: 'rgba(52,191,163,0.08)',
                        tension: 0.3,
                        fill: false,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    {
                        label: 'Saídas',
                        data: <?= json_encode($dados_saida) ?>,
                        borderColor: '#f4516c',
                        backgroundColor: 'rgba(244,81,108,0.08)',
                        tension: 0.3,
                        fill: false,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    {
                        label: 'Saldo Acumulado',
                        data: <?= json_encode($dados_saldo) ?>,
                        borderColor: '#716aca',
                        backgroundColor: 'rgba(113,106,202,0.08)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        borderDash: [5, 3]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { family: 'Poppins', size: 12 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ' R$ ' + ctx.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function(v) {
                                return 'R$ ' + v.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                            },
                            font: { family: 'Poppins', size: 11 }
                        },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        ticks: { font: { family: 'Poppins', size: 11 } },
                        grid: { display: false }
                    }
                }
            }
        });
        </script>
        <?php endif; ?>

    </body>
</html>