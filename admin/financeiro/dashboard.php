<?php
    session_start();
    require_once ('../inc/general.php');

    $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
    $ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');

    // --- Resumo do mês filtrado ---
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_registros,
            SUM(pago = 1 AND isento = 0) as total_pagos,
            SUM(pago = 0 AND isento = 0) as total_pendentes,
            SUM(isento = 1) as total_isentos,
            SUM(CASE WHEN pago = 1 AND isento = 0 THEN valor_total  ELSE 0 END) as total_arrecadado,
            SUM(CASE WHEN pago = 1 AND isento = 0 THEN valor_repasse ELSE 0 END) as total_repasse,
            SUM(CASE WHEN pago = 1 AND isento = 0 THEN valor_faccao  ELSE 0 END) as total_faccao
        FROM mensalidades
        WHERE mes = ? AND ano = ?
    ");
    $stmt->bind_param("ii", $mes, $ano);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    // Base real = apenas quem efetivamente deve pagar (exclui isentos)
    $total_obrigados = (int)$data['total_registros'] - (int)$data['total_isentos'];

    $percent_pago = $total_obrigados > 0
        ? round(($data['total_pagos']     / $total_obrigados) * 100)
        : 0;

    $percent_pendente = $total_obrigados > 0
        ? round(($data['total_pendentes'] / $total_obrigados) * 100)
        : 0;

    $percent_isento = $data['total_registros'] > 0
        ? round(($data['total_isentos']   / $data['total_registros']) * 100)
        : 0;

    // --- Caixa acumulado (todos os meses pagos) ---
    $res   = $conn->query("SELECT SUM(valor_faccao) as caixa_total FROM mensalidades WHERE pago = 1");
    $caixa = (float)($res->fetch_assoc()['caixa_total'] ?? 0);

    $res          = $conn->query("SELECT SUM(valor) as total_saidas FROM caixa_saida");
    $total_saidas = (float)($res->fetch_assoc()['total_saidas'] ?? 0);

    $caixa_real = $caixa - $total_saidas;

    // --- Últimos pagamentos do mês ---
    $stmt2 = $conn->prepare("
        SELECT ci.apelido, m.valor_total, m.data_pagamento
        FROM mensalidades m
        INNER JOIN cadastro_integrante ci ON ci.id = m.integrante_id
        WHERE m.pago = 1 AND m.mes = ? AND m.ano = ?
        ORDER BY m.data_pagamento DESC
        LIMIT 8
    ");
    $stmt2->bind_param("ii", $mes, $ano);
    $stmt2->execute();
    $ultimos_pagamentos = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

    // --- Inadimplentes do mês ---
    $stmt3 = $conn->prepare("
        SELECT ci.apelido, m.valor_total
        FROM mensalidades m
        INNER JOIN cadastro_integrante ci ON ci.id = m.integrante_id
        WHERE m.pago = 0 AND m.isento = 0 AND m.mes = ? AND m.ano = ?
        ORDER BY ci.apelido ASC
        LIMIT 8
    ");
    $stmt3->bind_param("ii", $mes, $ano);
    $stmt3->execute();
    $inadimplentes = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);

    // --- Isentos do mês ---
    $stmt4 = $conn->prepare("
        SELECT ci.apelido, m.patente_no_mes
        FROM mensalidades m
        INNER JOIN cadastro_integrante ci ON ci.id = m.integrante_id
        WHERE m.isento = 1 AND m.mes = ? AND m.ano = ?
        ORDER BY ci.apelido ASC
        LIMIT 8
    ");
    $stmt4->bind_param("ii", $mes, $ano);
    $stmt4->execute();
    $isentos_mes = $stmt4->get_result()->fetch_all(MYSQLI_ASSOC);

    // Nome do mês em português
    $meses_pt = [
        1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',
        5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',
        9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'
    ];
    $nome_mes = $meses_pt[(int)$mes] ?? $mes;

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
        <title>Abutre's MC | Financeiro</title>
        <meta name="description" content="Dashboard Financeiro">
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
                                    <h3 class="m-subheader__title" style="text-transform: uppercase;">
                                        Financeiro &mdash; <?= $nome_mes ?>/<?= $ano ?>
                                    </h3>
                                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                                        <li class="m-nav__item m-nav__item--home">
                                            <a href="index" class="m-nav__link m-nav__link--icon">
                                                <i class="m-nav__link-icon la la-home"></i>
                                            </a>
                                        </li>
                                        <li class="m-nav__separator">—</li>
                                        <li class="m-nav__item">
                                            <span class="m-nav__link-text">Financeiro</span>
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <a href="financeiro/list.php?mes=<?= $mes ?>&ano=<?= $ano ?>"
                                       class="btn btn-sm btn-info m-btn m-btn--icon m-btn--pill">
                                        <span>
                                            <i class="la la-list"></i>
                                            <span>Mensalidades</span>
                                        </span>
                                    </a>
                                    &nbsp;
                                    <a href="financeiro/pendentes.php?mes=<?= $mes ?>&ano=<?= $ano ?>"
                                       class="btn btn-sm btn-warning m-btn m-btn--icon m-btn--pill">
                                        <span>
                                            <i class="la la-exclamation-circle"></i>
                                            <span>Pendentes</span>
                                        </span>
                                    </a>
                                    &nbsp;
                                    <a href="financeiro/fluxo_caixa.php?mes=<?= $mes ?>&ano=<?= $ano ?>"
                                       class="btn btn-sm btn-brand m-btn m-btn--icon m-btn--pill">
                                        <span>
                                            <i class="la la-line-chart"></i>
                                            <span>Fluxo de Caixa</span>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="m-content">

                            <!-- FILTRO DE MÊS/ANO -->
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

                            <!-- FAIXA 1: KPIs de Caixa (acumulado) -->
                            <div class="m-portlet m-portlet--mobile">
                                <div class="m-portlet__head">
                                    <div class="m-portlet__head-caption">
                                        <div class="m-portlet__head-title">
                                            <span class="m-portlet__head-icon m--hide">
                                                <i class="flaticon-statistics"></i>
                                            </span>
                                            <h3 class="m-portlet__head-text">
                                                Caixa Geral (acumulado)
                                            </h3>
                                        </div>
                                    </div>
                                    <div class="m-portlet__head-tools">
                                        <a href="financeiro/fluxo_caixa.php?mes=<?= $mes ?>&ano=<?= $ano ?>"
                                           class="btn btn-sm btn-outline-brand m-btn m-btn--pill">
                                            <i class="la la-line-chart"></i> Fluxo de Caixa
                                        </a>
                                    </div>
                                </div>
                                <div class="m-portlet__body m-portlet__body--no-padding">
                                    <div class="row m-row--no-padding m-row--col-separator-xl">

                                        <div class="col-md-12 col-lg-4 col-xl-4">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Arrecadado (facção)</h4><br>
                                                    <span class="m-widget24__desc">Total bruto acumulado</span>
                                                    <span class="m-widget24__stats m--font-brand">
                                                        R$ <?= number_format($caixa, 2, ',', '.') ?>
                                                    </span>
                                                    <div class="m--space-10"></div>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-brand" role="progressbar" style="width: 100%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Total de entradas</span>
                                                    <span class="m-widget24__number">100%</span>
                                                </div>
                                            </div>
                                        </div>

                                        
                                        <div class="col-md-12 col-lg-4 col-xl-4">
                                            
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <a href="financeiro/saida_list"><h4 class="m-widget24__title">Total de Saídas</h4></a><br>
                                                    <span class="m-widget24__desc">Despesas registradas</span>
                                                    <span class="m-widget24__stats m--font-danger">
                                                        R$ <?= number_format($total_saidas, 2, ',', '.') ?>
                                                    </span>
                                                    <div class="m--space-10"></div>
                                                    <?php $pc_saidas = $caixa > 0 ? round(($total_saidas / $caixa) * 100) : 0; ?>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-danger" role="progressbar" style="width: <?= $pc_saidas ?>%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Sobre o arrecadado</span>
                                                    <span class="m-widget24__number"><?= $pc_saidas ?>%</span>
                                                </div>
                                            </div>
                                            
                                        </div>
                                        

                                        <div class="col-md-12 col-lg-4 col-xl-4">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Caixa Real</h4><br>
                                                    <span class="m-widget24__desc">Disponível no caixa</span>
                                                    <span class="m-widget24__stats m--font-success">
                                                        R$ <?= number_format($caixa_real, 2, ',', '.') ?>
                                                    </span>
                                                    <div class="m--space-10"></div>
                                                    <?php $pc_real = $caixa > 0 ? round(($caixa_real / $caixa) * 100) : 0; ?>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-success" role="progressbar" style="width: <?= $pc_real ?>%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Sobre o arrecadado</span>
                                                    <span class="m-widget24__number"><?= $pc_real ?>%</span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- FAIXA 2: Resumo do Mês + Gráficos -->
                            <div class="row">
                                <!-- Coluna esquerda: resumo mensal + donut -->
                                <div class="col-xl-4">
                                    <div class="m-portlet m-portlet--full-height">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">
                                                        Mensalidades — <?= $nome_mes ?>/<?= $ano ?>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="m-portlet__head-tools">
                                                <a href="financeiro/list.php?mes=<?= $mes ?>&ano=<?= $ano ?>"
                                                   class="btn btn-sm btn-outline-info m-btn m-btn--pill">
                                                    Ver todos
                                                </a>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">

                                            <!-- Donut -->
                                            <div style="max-width: 220px; margin: 0 auto 20px;">
                                                <canvas id="graficoMensalidades"></canvas>
                                            </div>

                                            <!-- Legenda / valores do mês -->
                                            <div class="m-widget1">
                                                <div class="m-widget1__item">
                                                    <div class="row m-row--no-padding align-items-center">
                                                        <div class="col">
                                                            <h5 class="m-widget1__title">Arrecadado no mês</h5>
                                                            <span class="m-widget1__desc">Valor total pago</span>
                                                        </div>
                                                        <div class="col m--align-right">
                                                            <span class="m-widget1__number m--font-brand">
                                                                R$ <?= number_format($data['total_arrecadado'], 2, ',', '.') ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="m-widget1__item">
                                                    <div class="row m-row--no-padding align-items-center">
                                                        <div class="col">
                                                            <h5 class="m-widget1__title">Repasse</h5>
                                                            <span class="m-widget1__desc">Enviado à confederação</span>
                                                        </div>
                                                        <div class="col m--align-right">
                                                            <span class="m-widget1__number m--font-danger">
                                                                R$ <?= number_format($data['total_repasse'], 2, ',', '.') ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="m-widget1__item">
                                                    <div class="row m-row--no-padding align-items-center">
                                                        <div class="col">
                                                            <h5 class="m-widget1__title">Ficou na facção</h5>
                                                            <span class="m-widget1__desc">Parte local</span>
                                                        </div>
                                                        <div class="col m--align-right">
                                                            <span class="m-widget1__number m--font-info">
                                                                R$ <?= number_format($data['total_faccao'], 2, ',', '.') ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <!-- Coluna central: status das mensalidades -->
                                <div class="col-xl-4">
                                    <div class="m-portlet m-portlet--full-height">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">
                                                        Status das Mensalidades
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">

                                            <!-- Pagos -->
                                            <div class="m-widget1__item">
                                                <div class="row m-row--no-padding align-items-center">
                                                    <div class="col">
                                                        <h5 class="m-widget1__title">
                                                            <i class="la la-check-circle m--font-success"></i>
                                                            Pagos
                                                        </h3>
                                                        <span class="m-widget1__desc"><?= $percent_pago ?>% dos obrigados</span>
                                                    </div>
                                                    <div class="col m--align-right">
                                                        <span class="m-widget1__number m--font-success">
                                                            <?= $data['total_pagos'] ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="progress m-progress--sm m--margin-top-10">
                                                    <div class="progress-bar m--bg-success" style="width: <?= $percent_pago ?>%"></div>
                                                </div>
                                            </div>

                                            <!-- Pendentes -->
                                            <div class="m-widget1__item">
                                                <div class="row m-row--no-padding align-items-center">
                                                    <div class="col">
                                                        <h5 class="m-widget1__title">
                                                            <i class="la la-clock-o m--font-warning"></i>
                                                            Pendentes
                                                        </h3>
                                                        <span class="m-widget1__desc"><?= $percent_pendente ?>% dos obrigados</span>
                                                    </div>
                                                    <div class="col m--align-right">
                                                        <span class="m-widget1__number m--font-warning">
                                                            <?= $data['total_pendentes'] ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="progress m-progress--sm m--margin-top-10">
                                                    <div class="progress-bar m--bg-warning" style="width: <?= $percent_pendente ?>%"></div>
                                                </div>
                                            </div>

                                            <!-- Isentos -->
                                            <div class="m-widget1__item">
                                                <div class="row m-row--no-padding align-items-center">
                                                    <div class="col">
                                                        <h5 class="m-widget1__title">
                                                            <i class="la la-minus-circle m--font-metal"></i>
                                                            Isentos
                                                        </h3>
                                                        <span class="m-widget1__desc"><?= $percent_isento ?>% do total de membros</span>
                                                    </div>
                                                    <div class="col m--align-right">
                                                        <span class="m-widget1__number m--font-metal">
                                                            <?= $data['total_isentos'] ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="progress m-progress--sm m--margin-top-10">
                                                    <div class="progress-bar m--bg-metal" style="width: <?= $percent_isento ?>%"></div>
                                                </div>
                                            </div>

                                            <!-- Total -->
                                            <div class="m-widget1__item">
                                                <div class="row m-row--no-padding align-items-center">
                                                    <div class="col">
                                                        <h5 class="m-widget1__title">
                                                            <i class="la la-users m--font-info"></i>
                                                            Total de registros
                                                        </h3>
                                                        <span class="m-widget1__desc">Integrantes no mês</span>
                                                    </div>
                                                    <div class="col m--align-right">
                                                        <span class="m-widget1__number m--font-info">
                                                            <?= $data['total_registros'] ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <!-- Coluna direita: membros da facção -->
                                <div class="col-xl-4">
                                    <div class="m-portlet m-portlet--full-height">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">
                                                        Integrantes da Facção
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="m-portlet__head-tools">
                                                <a href="integrantes_faccao"
                                                   class="btn btn-sm btn-outline-info m-btn m-btn--pill">
                                                    Ver todos
                                                </a>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">

                                            <div style="max-width: 220px; margin: 0 auto 20px;">
                                                <canvas id="graficoIntegrantes"></canvas>
                                            </div>

                                            <div class="m-widget1">
                                                <div class="m-widget1__item">
                                                    <div class="row m-row--no-padding align-items-center">
                                                        <div class="col">
                                                            <h5 class="m-widget1__title">Ativos</h5>
                                                        </div>
                                                        <div class="col m--align-right">
                                                            <span class="m-widget1__number m--font-success"><?= $total_ativos ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="m-widget1__item">
                                                    <div class="row m-row--no-padding align-items-center">
                                                        <div class="col">
                                                            <h5 class="m-widget1__title">Afastados</h5>
                                                        </div>
                                                        <div class="col m--align-right">
                                                            <span class="m-widget1__number m--font-warning"><?= $total_afastados ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="m-widget1__item">
                                                    <div class="row m-row--no-padding align-items-center">
                                                        <div class="col">
                                                            <h5 class="m-widget1__title">Desligados</h5>
                                                        </div>
                                                        <div class="col m--align-right">
                                                            <span class="m-widget1__number m--font-danger"><?= $total_desligados ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="m-widget1__item">
                                                    <div class="row m-row--no-padding align-items-center">
                                                        <div class="col">
                                                            <h5 class="m-widget1__title">Suspensos</h5>
                                                        </div>
                                                        <div class="col m--align-right">
                                                            <span class="m-widget1__number m--font-metal"><?= $total_suspensos ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="m-widget1__item">
                                                    <div class="row m-row--no-padding align-items-center">
                                                        <div class="col">
                                                            <h5 class="m-widget1__title"><strong>Total</strong></h5>
                                                        </div>
                                                        <div class="col m--align-right">
                                                            <span class="m-widget1__number m--font-info"><?= $total_integrantes ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /FAIXA 2 -->

                            <!-- FAIXA 3: Últimos pagamentos + Pendentes + Isentos -->
                            <div class="row">

                                <!-- Últimos pagamentos -->
                                <div class="col-xl-4">
                                    <div class="m-portlet m-portlet--full-height">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">
                                                        <i class="la la-check m--font-success"></i>
                                                        Últimos Pagamentos — <?= $nome_mes ?>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="m-portlet__head-tools">
                                                <a href="financeiro/list.php?mes=<?= $mes ?>&ano=<?= $ano ?>"
                                                   class="btn btn-sm btn-outline-success m-btn m-btn--pill">
                                                    Ver todos
                                                </a>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">
                                            <?php if (empty($ultimos_pagamentos)): ?>
                                                <div class="m--align-center m--margin-top-20 m--margin-bottom-20 m--font-metal">
                                                    Nenhum pagamento registrado neste mês.
                                                </div>
                                            <?php else: ?>
                                            <div class="m-widget4">
                                                <?php foreach ($ultimos_pagamentos as $pg): ?>
                                                <div class="m-widget4__item">
                                                    <div class="m-widget4__img m-widget4__img--icon">
                                                        <i class="flaticon-user m--font-success" style="font-size: 2rem;"></i>
                                                    </div>
                                                    <div class="m-widget4__info">
                                                        <span class="m-widget4__title">
                                                            <?= htmlspecialchars($pg['apelido']) ?>
                                                        </span>
                                                        <br>
                                                        <span class="m-widget4__sub">
                                                            <?= $pg['data_pagamento'] ? date('d/m/Y', strtotime($pg['data_pagamento'])) : '—' ?>
                                                        </span>
                                                    </div>
                                                    <span class="m-widget4__ext">
                                                        <span class="m-widget4__number m--font-success">
                                                            R$ <?= number_format($pg['valor_total'], 2, ',', '.') ?>
                                                        </span>
                                                    </span>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pendentes -->
                                <div class="col-xl-4">
                                    <div class="m-portlet m-portlet--full-height">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">
                                                        <i class="la la-exclamation-triangle m--font-warning"></i>
                                                        Pendentes — <?= $nome_mes ?>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="m-portlet__head-tools">
                                                <a href="financeiro/pendentes.php?mes=<?= $mes ?>&ano=<?= $ano ?>"
                                                   class="btn btn-sm btn-outline-warning m-btn m-btn--pill">
                                                    Ver todos
                                                </a>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">
                                            <?php if (empty($inadimplentes)): ?>
                                                <div class="m--align-center m--margin-top-20 m--margin-bottom-20 m--font-success">
                                                    <i class="la la-check-circle" style="font-size: 2rem;"></i><br>
                                                    Todos pagaram neste mês!
                                                </div>
                                            <?php else: ?>
                                            <div class="m-widget4">
                                                <?php foreach ($inadimplentes as $ind): ?>
                                                <div class="m-widget4__item">
                                                    <div class="m-widget4__img m-widget4__img--icon">
                                                        <i class="flaticon-user m--font-warning" style="font-size: 2rem;"></i>
                                                    </div>
                                                    <div class="m-widget4__info">
                                                        <span class="m-widget4__title">
                                                            <?= htmlspecialchars($ind['apelido']) ?>
                                                        </span>
                                                        <br>
                                                        <span class="m-widget4__sub">Em aberto</span>
                                                    </div>
                                                    <span class="m-widget4__ext">
                                                        <span class="m-widget4__number m--font-warning">
                                                            R$ <?= number_format($ind['valor_total'], 2, ',', '.') ?>
                                                        </span>
                                                    </span>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Isentos -->
                                <div class="col-xl-4">
                                    <div class="m-portlet m-portlet--full-height">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">
                                                        <i class="la la-minus-circle m--font-metal"></i>
                                                        Isentos — <?= $nome_mes ?>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="m-portlet__head-tools">
                                                <a href="financeiro/list.php?mes=<?= $mes ?>&ano=<?= $ano ?>"
                                                   class="btn btn-sm btn-outline-secondary m-btn m-btn--pill">
                                                    Ver todos
                                                </a>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">
                                            <?php if (empty($isentos_mes)): ?>
                                                <div class="m--align-center m--margin-top-20 m--margin-bottom-20 m--font-metal">
                                                    Nenhum isento neste mês.
                                                </div>
                                            <?php else: ?>
                                            <div class="m-widget4">
                                                <?php foreach ($isentos_mes as $is): ?>
                                                <div class="m-widget4__item">
                                                    <div class="m-widget4__img m-widget4__img--icon">
                                                        <i class="flaticon-user m--font-metal" style="font-size: 2rem;"></i>
                                                    </div>
                                                    <div class="m-widget4__info">
                                                        <span class="m-widget4__title">
                                                            <?= htmlspecialchars($is['apelido']) ?>
                                                        </span>
                                                        <br>
                                                        <span class="m-widget4__sub">
                                                            Pat. <?= patente_label($is['patente_no_mes']) ?>
                                                        </span>
                                                    </div>
                                                    <span class="m-widget4__ext">
                                                        <span class="m-badge m-badge--metal m-badge--wide">Isento</span>
                                                    </span>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- /FAIXA 3 -->

                        </div>
                        <!-- /m-content -->

                    </div>
                </div>
            </div>
            <!-- end::Body -->

            <?php require_once ('../inc/footer.php'); ?>
        </div>

        <div class="m-scroll-top m-scroll-top--skin-top" data-toggle="m-scroll-top" data-scroll-offset="500" data-scroll-speed="300">
            <i class="la la-arrow-up"></i>
        </div>

        <script src="js/vendors.bundle.js" type="text/javascript"></script>
        <script src="js/scripts.bundle.js" type="text/javascript"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
        // Gráfico donut — Mensalidades do mês
        new Chart(document.getElementById('graficoMensalidades'), {
            type: 'doughnut',
            data: {
                labels: ['Pagos', 'Pendentes', 'Isentos'],
                datasets: [{
                    data: [
                        <?= (int)$data['total_pagos'] ?>,
                        <?= (int)$data['total_pendentes'] ?>,
                        <?= (int)$data['total_isentos'] ?>
                    ],
                    backgroundColor: ['#34bfa3', '#ffb822', '#a0a0a0'],
                    borderWidth: 2
                }]
            },
            options: {
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { font: { family: 'Poppins' } } }
                }
            }
        });

        // Gráfico donut — Integrantes por status
        new Chart(document.getElementById('graficoIntegrantes'), {
            type: 'doughnut',
            data: {
                labels: ['Ativos', 'Afastados', 'Desligados', 'Suspensos'],
                datasets: [{
                    data: [
                        <?= (int)$total_ativos ?>,
                        <?= (int)$total_afastados ?>,
                        <?= (int)$total_desligados ?>,
                        <?= (int)$total_suspensos ?>
                    ],
                    backgroundColor: ['#34bfa3', '#ffb822', '#f4516c', '#a0a0a0'],
                    borderWidth: 2
                }]
            },
            options: {
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { font: { family: 'Poppins' } } }
                }
            }
        });
        </script>

    </body>
</html>