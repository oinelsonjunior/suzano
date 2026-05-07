<?php
    include_once('inc/display_errors.php');
    session_start();
    require_once ('inc/general.php');

    $mes_atual = (int)date('m');
    $ano_atual = (int)date('Y');
    $hoje      = date('Y-m-d');

    $meses_pt = [
        1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',
        5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',
        9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'
    ];

    $tipos_evento = [
        1=>'Sede Estadual (padrão)',2=>'Sede Estadual (reunião geral)',
        3=>'Sede Suzano (reunião)',4=>'Sede Suzano (confraternização)',
        5=>'Evento Fora',6=>'Evento Obrigatório',
    ];
    $tipo_badge = [
        1=>'m-badge--metal',2=>'m-badge--brand',3=>'m-badge--info',
        4=>'m-badge--success',5=>'m-badge--warning',6=>'m-badge--danger',
    ];

    function patente_lbl($p) {
        return [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X'][$p] ?? '—';
    }

    // ── FINANCEIRO ────────────────────────────────────────────────
    $res = $conn->query("SELECT SUM(valor_faccao) as caixa FROM mensalidades WHERE pago = 1");
    $caixa_bruto = (float)($res->fetch_assoc()['caixa'] ?? 0);

    $res = $conn->query("SELECT SUM(valor) as saidas FROM caixa_saida");
    $total_saidas = (float)($res->fetch_assoc()['saidas'] ?? 0);
    $caixa_real = $caixa_bruto - $total_saidas;

    // Mensalidades do mês atual
    $stmt = $conn->prepare("
        SELECT
            COUNT(*) as total,
            SUM(pago=1 AND isento=0) as pagos,
            SUM(pago=0 AND isento=0) as pendentes,
            SUM(isento=1 OR patente_no_mes=6) as isentos,
            SUM(CASE WHEN pago=1 AND isento=0 THEN valor_total ELSE 0 END) as arrecadado
        FROM mensalidades WHERE mes = ? AND ano = ?
    ");
    $stmt->bind_param("ii", $mes_atual, $ano_atual);
    $stmt->execute();
    $mens = $stmt->get_result()->fetch_assoc();

    $total_obrigados = max(0, (int)$mens['total'] - (int)$mens['isentos']);
    $pc_pago = $total_obrigados > 0 ? round(($mens['pagos'] / $total_obrigados) * 100) : 0;

    // Inadimplentes do mês
    $stmt = $conn->prepare("
        SELECT ci.id, ci.apelido, m.valor_total
        FROM mensalidades m
        JOIN cadastro_integrante ci ON ci.id = m.integrante_id
        WHERE m.pago = 0 AND m.isento = 0 AND m.patente_no_mes != 6
          AND m.mes = ? AND m.ano = ?
        ORDER BY ci.apelido ASC LIMIT 6
    ");
    $stmt->bind_param("ii", $mes_atual, $ano_atual);
    $stmt->execute();
    $inadimplentes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Últimos pagamentos
    $stmt = $conn->prepare("
        SELECT ci.apelido, m.valor_total, m.data_pagamento
        FROM mensalidades m
        JOIN cadastro_integrante ci ON ci.id = m.integrante_id
        WHERE m.pago = 1 AND m.mes = ? AND m.ano = ?
        ORDER BY m.data_pagamento DESC LIMIT 5
    ");
    $stmt->bind_param("ii", $mes_atual, $ano_atual);
    $stmt->execute();
    $ultimos_pagamentos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // ── EVENTOS ───────────────────────────────────────────────────
    // Próximos eventos (a partir de hoje)
    $res = $conn->query("
        SELECT e.id, e.tipo, e.nome, e.data_evento,
               COUNT(f.id) as conv, SUM(f.presente=1) as pres
        FROM eventos e
        LEFT JOIN frequencias f ON f.evento_id = e.id
        WHERE e.faccao = 1 AND e.data_evento >= '$hoje'
        GROUP BY e.id
        ORDER BY e.data_evento ASC LIMIT 5
    ");
    $proximos_eventos = $res->fetch_all(MYSQLI_ASSOC);

    // Próximo evento (para KPI)
    $proximo = !empty($proximos_eventos) ? $proximos_eventos[0] : null;
    $dias_proximo = null;
    if ($proximo) {
        $diff = (new DateTime($hoje))->diff(new DateTime($proximo['data_evento']));
        $dias_proximo = $diff->days;
    }

    // Faltas em evento obrigatório recente
    $res = $conn->query("
        SELECT ci.id, ci.apelido, e.nome as evento,
               SUM(f.presente=0) as faltas
        FROM frequencias f
        JOIN eventos e ON e.id = f.evento_id AND e.tipo = 6
        JOIN cadastro_integrante ci ON ci.id = f.integrante_id
        WHERE e.data_evento >= DATE_SUB('$hoje', INTERVAL 60 DAY)
          AND e.faccao = 1
        GROUP BY ci.id
        HAVING faltas > 0
        ORDER BY faltas DESC LIMIT 5
    ");
    $faltas_obrigatorios = $res->fetch_all(MYSQLI_ASSOC);

    // Média de presença do ano
    $res = $conn->query("
        SELECT COUNT(f.id) as total, SUM(f.presente=1) as presentes
        FROM frequencias f
        JOIN eventos e ON e.id = f.evento_id
        WHERE YEAR(e.data_evento) = $ano_atual AND e.faccao = 1
          AND e.data_evento < CURDATE()
    ");
    $freq_ano = $res->fetch_assoc();
    $pc_freq_ano = $freq_ano['total'] > 0
        ? round(($freq_ano['presentes'] / $freq_ano['total']) * 100) : 0;

    // Frequência por tipo de evento no ano
    $res = $conn->query("
        SELECT e.tipo,
               COUNT(f.id) as total,
               SUM(f.presente=1) as presentes
        FROM frequencias f
        JOIN eventos e ON e.id = f.evento_id
        WHERE YEAR(e.data_evento) = $ano_atual AND e.faccao = 1
          AND e.data_evento < CURDATE()
        GROUP BY e.tipo ORDER BY e.tipo
    ");
    $freq_por_tipo = $res->fetch_all(MYSQLI_ASSOC);

    // Últimas presenças confirmadas
    $res = $conn->query("
        SELECT ci.apelido, e.nome as evento, e.data_evento, e.tipo
        FROM frequencias f
        JOIN cadastro_integrante ci ON ci.id = f.integrante_id
        JOIN eventos e ON e.id = f.evento_id
        WHERE f.presente = 1
        ORDER BY f.updated_at DESC LIMIT 5
    ");
    $ultimas_presencas = $res->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="utf-8" />
        <title>Abutre's MC | Dashboard</title>
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
        <style>
            .kpi-link { text-decoration: none !important; display: block; }
            .kpi-link:hover .m-portlet { box-shadow: 0 4px 20px rgba(0,0,0,.1); transition: box-shadow .2s; }

            /* Card de próximo evento */
            .ev-card { display:flex; gap:14px; align-items:flex-start; padding:10px 0; border-bottom:1px solid #ebedf2; }
            .ev-card:last-child { border-bottom:none; }
            .ev-data  { flex-shrink:0; width:46px; text-align:center; background:#f8f9fa;
                        border-radius:8px; padding:6px 4px; border:1px solid #ebedf2; }
            .ev-dia   { font-size:20px; font-weight:700; line-height:1; color:#575962; }
            .ev-mes   { font-size:10px; color:#9699a2; text-transform:uppercase; }
            .ev-info  { flex:1; min-width:0; }
            .ev-nome  { font-size:13px; font-weight:600; color:#575962;
                        white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
            .ev-card.hoje .ev-data { background:#eef1ff; border-color:#716aca; }
            .ev-card.hoje .ev-dia  { color:#716aca; }
            .ev-card.urgente .ev-data { background:#fff5f6; border-color:#f4516c; }
            .ev-card.urgente .ev-dia  { color:#f4516c; }

            /* Alerta de inadimplente */
            .alerta-item { display:flex; align-items:center; gap:10px; padding:8px 0;
                           border-bottom:1px solid #ebedf2; font-size:13px; }
            .alerta-item:last-child { border-bottom:none; }
        </style>
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
                            <?php include_once('inc/topbar.php'); ?>
                        </div>
                    </div>
                </div>
                <?php include_once('inc/header_bottom.php'); ?>
            </header>

            <div class="m-grid__item m-grid__item--fluid m-grid m-grid--hor-desktop m-grid--desktop m-body">
                <div class="m-grid__item m-grid__item--fluid m-grid m-grid--ver m-container m-container--responsive m-container--xxl m-page__container">
                    <div class="m-grid__item m-grid__item--fluid m-wrapper">

                        <div class="m-subheader">
                            <div class="d-flex align-items-center">
                                <div class="mr-auto">
                                    <h3 class="m-subheader__title" style="text-transform:uppercase;">
                                        Abutre's MC &mdash; <?= htmlspecialchars($nome_faccao) ?>
                                    </h3>
                                    <span class="m--font-metal" style="font-size:13px;">
                                        <?= $meses_pt[$mes_atual] ?> <?= $ano_atual ?> &mdash;
                                        Olá, <strong><?= htmlspecialchars($apelido) ?></strong>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="m-content">

                            <!-- ══ FAIXA 1: KPIs rápidos ══ -->
                            <div class="m-portlet m-portlet--mobile">
                                <div class="m-portlet__body m-portlet__body--no-padding">
                                    <div class="row m-row--no-padding m-row--col-separator-xl">

                                        <!-- Caixa Real -->
                                        <div class="col-md-6 col-lg-3 col-xl-3">
                                            <a href="financeiro/dashboard" class="kpi-link">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Caixa Real</h4><br>
                                                    <span class="m-widget24__desc">Disponível no caixa</span>
                                                    <span class="m-widget24__stats <?= $caixa_real >= 0 ? 'm--font-success' : 'm--font-danger' ?>">
                                                        R$ <?= number_format($caixa_real, 2, ',', '.') ?>
                                                    </span>
                                                    <div class="m--space-10"></div>
                                                    <?php $pc_caixa = $caixa_bruto > 0 ? round(($caixa_real/$caixa_bruto)*100) : 0; ?>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar <?= $caixa_real >= 0 ? 'm--bg-success' : 'm--bg-danger' ?>"
                                                             style="width:<?= min(abs($pc_caixa),100) ?>%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Sobre o arrecadado total</span>
                                                    <span class="m-widget24__number"><?= $pc_caixa ?>%</span>
                                                </div>
                                            </div>
                                            </a>
                                        </div>

                                        <!-- Integrantes Ativos -->
                                        <div class="col-md-6 col-lg-3 col-xl-3">
                                            <a href="integrantes_faccao?status=1" class="kpi-link">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Integrantes Ativos</h4><br>
                                                    <span class="m-widget24__desc">Total: <?= $total_integrantes ?></span>
                                                    <span class="m-widget24__stats m--font-brand">
                                                        <?= $total_ativos ?>
                                                    </span>
                                                    <div class="m--space-10"></div>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-brand"
                                                             style="width:<?= $percent_ativos ?>%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Do total de membros</span>
                                                    <span class="m-widget24__number"><?= $percent_ativos ?>%</span>
                                                </div>
                                            </div>
                                            </a>
                                        </div>

                                        <!-- Inadimplentes do mês -->
                                        <div class="col-md-6 col-lg-3 col-xl-3">
                                            <a href="financeiro/pendentes.php?mes=<?= $mes_atual ?>&ano=<?= $ano_atual ?>" class="kpi-link">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Inadimplentes</h4><br>
                                                    <span class="m-widget24__desc"><?= $meses_pt[$mes_atual] ?>/<?= $ano_atual ?></span>
                                                    <span class="m-widget24__stats <?= $mens['pendentes'] > 0 ? 'm--font-warning' : 'm--font-success' ?>">
                                                        <?= (int)$mens['pendentes'] ?>
                                                    </span>
                                                    <div class="m--space-10"></div>
                                                    <?php $pc_inadimp = $total_obrigados > 0 ? round(($mens['pendentes']/$total_obrigados)*100) : 0; ?>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar <?= $mens['pendentes'] > 0 ? 'm--bg-warning' : 'm--bg-success' ?>"
                                                             style="width:<?= $pc_inadimp ?>%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Mensalidades pagas</span>
                                                    <span class="m-widget24__number"><?= $pc_pago ?>%</span>
                                                </div>
                                            </div>
                                            </a>
                                        </div>

                                        <!-- Próximo evento -->
                                        <div class="col-md-6 col-lg-3 col-xl-3">
                                            <a href="eventos/index.php" class="kpi-link">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Próximo Evento</h4><br>
                                                    <?php if ($proximo): ?>
                                                        <span class="m-widget24__desc">
                                                            <?= date('d/m/Y', strtotime($proximo['data_evento'])) ?>
                                                        </span>
                                                        <span class="m-widget24__stats <?= $dias_proximo == 0 ? 'm--font-danger' : ($dias_proximo <= 7 ? 'm--font-warning' : 'm--font-info') ?>"
                                                              style="font-size:22px; line-height:1.2;">
                                                            <?= $dias_proximo == 0 ? 'Hoje!' : "em $dias_proximo dias" ?>
                                                        </span>
                                                        <div class="m--space-10"></div>
                                                        <div class="progress m-progress--sm">
                                                            <div class="progress-bar m--bg-info" style="width:100%"></div>
                                                        </div>
                                                        <span class="m-widget24__change" style="max-width:150px;display:inline-block;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">
                                                            <?= htmlspecialchars($proximo['nome']) ?>
                                                        </span>
                                                        <span class="m-widget24__number">
                                                            <span class="m-badge <?= $tipo_badge[$proximo['tipo']] ?>" style="font-size:10px;">
                                                                <?= $tipos_evento[$proximo['tipo']] ?>
                                                            </span>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="m-widget24__desc">Sem eventos agendados</span>
                                                        <span class="m-widget24__stats m--font-metal">—</span>
                                                        <div class="m--space-10"></div>
                                                        <div class="progress m-progress--sm">
                                                            <div class="progress-bar m--bg-metal" style="width:0%"></div>
                                                        </div>
                                                        <span class="m-widget24__change">Cadastre um evento</span>
                                                        <span class="m-widget24__number">→</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- ══ FAIXA 2: Três donuts ══ -->
                            <div class="row">

                                <!-- Integrantes por status -->
                                <div class="col-xl-4">
                                    <div class="m-portlet m-portlet--full-height">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">Integrantes</h3>
                                                </div>
                                            </div>
                                            <div class="m-portlet__head-tools">
                                                <a href="integrantes_faccao" class="btn btn-sm btn-outline-brand m-btn m-btn--pill">
                                                    Ver todos
                                                </a>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">
                                            <div style="max-width:200px; margin:0 auto 16px;">
                                                <canvas id="donutIntegrantes"></canvas>
                                            </div>
                                            <div class="m-widget1">
                                                <?php
                                                $rows_int = [
                                                    ['Ativos',    $total_ativos,    'm--font-success', 'integrantes_faccao?status=1'],
                                                    ['Afastados', $total_afastados, 'm--font-warning', 'integrantes_faccao?status=2'],
                                                    ['Suspensos', $total_suspensos, 'm--font-metal',   'integrantes_faccao?status=4'],
                                                    ['Desligados',$total_desligados,'m--font-danger',  'integrantes_faccao?status=3'],
                                                ];
                                                foreach ($rows_int as [$lbl, $val, $cls, $href]): ?>
                                                <div class="m-widget1__item">
                                                    <div class="row m-row--no-padding align-items-center">
                                                        <div class="col">
                                                            <h5 class="m-widget1__title">
                                                                <a href="<?= $href ?>" style="color:inherit;text-decoration:none;"><?= $lbl ?></a>
                                                            </h5>
                                                        </div>
                                                        <div class="col m--align-right">
                                                            <span class="m-widget1__number <?= $cls ?>" style="font-size:16px;"><?= $val ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mensalidades do mês -->
                                <div class="col-xl-4">
                                    <div class="m-portlet m-portlet--full-height">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">
                                                        Mensalidades — <?= $meses_pt[$mes_atual] ?>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="m-portlet__head-tools">
                                                <a href="financeiro/list.php?mes=<?= $mes_atual ?>&ano=<?= $ano_atual ?>"
                                                   class="btn btn-sm btn-outline-success m-btn m-btn--pill">
                                                    Ver todos
                                                </a>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">
                                            <div style="max-width:200px; margin:0 auto 16px;">
                                                <canvas id="donutMensalidades"></canvas>
                                            </div>
                                            <div class="m-widget1">
                                                <div class="m-widget1__item">
                                                    <div class="row m-row--no-padding align-items-center">
                                                        <div class="col"><h5 class="m-widget1__title">Arrecadado</h5></div>
                                                        <div class="col m--align-right">
                                                            <span class="m-widget1__number m--font-success" style="font-size:15px;">
                                                                R$ <?= number_format($mens['arrecadado'], 2, ',', '.') ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                                $rows_men = [
                                                    ['Pagos',    (int)$mens['pagos'],    'm--font-success', "financeiro/list.php?mes=$mes_atual&ano=$ano_atual"],
                                                    ['Pendentes',(int)$mens['pendentes'],'m--font-warning', "financeiro/pendentes.php?mes=$mes_atual&ano=$ano_atual"],
                                                    ['Isentos',  (int)$mens['isentos'],  'm--font-metal',   "financeiro/list.php?mes=$mes_atual&ano=$ano_atual"],
                                                ];
                                                foreach ($rows_men as [$lbl, $val, $cls, $href]): ?>
                                                <div class="m-widget1__item">
                                                    <div class="row m-row--no-padding align-items-center">
                                                        <div class="col">
                                                            <h5 class="m-widget1__title">
                                                                <a href="<?= $href ?>" style="color:inherit;text-decoration:none;"><?= $lbl ?></a>
                                                            </h5>
                                                        </div>
                                                        <div class="col m--align-right">
                                                            <span class="m-widget1__number <?= $cls ?>" style="font-size:16px;"><?= $val ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Frequência do ano -->
                                <div class="col-xl-4">
                                    <div class="m-portlet m-portlet--full-height">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">Frequência <?= $ano_atual ?></h3>
                                                </div>
                                            </div>
                                            <div class="m-portlet__head-tools">
                                                <a href="frequencia/resumo.php?ano=<?= $ano_atual ?>"
                                                   class="btn btn-sm btn-outline-info m-btn m-btn--pill">
                                                    Resumo
                                                </a>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">
                                            <!-- Taxa geral -->
                                            <div class="m--align-center m--margin-bottom-20">
                                                <div class="m--font-boldest <?= $pc_freq_ano >= 70 ? 'm--font-success' : ($pc_freq_ano >= 50 ? 'm--font-warning' : 'm--font-danger') ?>"
                                                     style="font-size:3rem; line-height:1;">
                                                    <?= $pc_freq_ano ?>%
                                                </div>
                                                <div class="m--font-metal" style="font-size:12px;">Média geral de presença</div>
                                                <div class="progress m-progress--sm m--margin-top-10">
                                                    <div class="progress-bar <?= $pc_freq_ano >= 70 ? 'm--bg-success' : ($pc_freq_ano >= 50 ? 'm--bg-warning' : 'm--bg-danger') ?>"
                                                         style="width:<?= $pc_freq_ano ?>%"></div>
                                                </div>
                                            </div>
                                            <!-- Por tipo -->
                                            <?php if (!empty($freq_por_tipo)): ?>
                                            <div class="m-widget1">
                                                <?php foreach ($freq_por_tipo as $ft):
                                                    $pc_t = $ft['total'] > 0 ? round(($ft['presentes']/$ft['total'])*100) : 0;
                                                    $cls_t = $pc_t >= 70 ? 'm--font-success' : ($pc_t >= 50 ? 'm--font-warning' : 'm--font-danger');
                                                ?>
                                                <div class="m-widget1__item">
                                                    <div class="row m-row--no-padding align-items-center">
                                                        <div class="col">
                                                            <h5 class="m-widget1__title" style="font-size:11px;">
                                                                <?= $tipos_evento[$ft['tipo']] ?>
                                                            </h5>
                                                        </div>
                                                        <div class="col m--align-right">
                                                            <span class="<?= $cls_t ?> m--font-boldest" style="font-size:13px;"><?= $pc_t ?>%</span>
                                                        </div>
                                                    </div>
                                                    <div class="progress m-progress--sm m--margin-top-5">
                                                        <div class="progress-bar <?= str_replace('font','bg',$cls_t) ?>" style="width:<?= $pc_t ?>%"></div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php else: ?>
                                                <p class="m--font-metal m--align-center" style="font-size:13px;">
                                                    Nenhum evento registrado em <?= $ano_atual ?>.
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- /FAIXA 2 -->

                            <!-- ══ FAIXA 3: Próximos eventos + Alertas ══ -->
                            <div class="row">

                                <!-- Próximos eventos -->
                                <div class="col-xl-8">
                                    <div class="m-portlet m-portlet--full-height">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">
                                                        <i class="la la-calendar m--font-brand"></i>
                                                        Próximos Eventos
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="m-portlet__head-tools">
                                                <a href="frequencia/evento_form.php"
                                                   class="btn btn-sm btn-outline-brand m-btn m-btn--pill m--margin-right-5">
                                                    <i class="la la-plus"></i> Novo
                                                </a>
                                                <a href="eventos/index.php"
                                                   class="btn btn-sm btn-outline-brand m-btn m-btn--pill">
                                                    Calendário
                                                </a>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">
                                            <?php if (empty($proximos_eventos)): ?>
                                                <div class="m--align-center" style="padding:30px 0;">
                                                    <i class="la la-calendar-times-o m--font-metal" style="font-size:3rem;"></i>
                                                    <p class="m--font-metal m--margin-top-10">Nenhum evento agendado.</p>
                                                    <a href="frequencia/evento_form.php" class="btn btn-brand m-btn m-btn--pill">
                                                        Cadastrar evento
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <?php foreach ($proximos_eventos as $ev):
                                                    $ev_data = new DateTime($ev['data_evento']);
                                                    $diff    = (new DateTime($hoje))->diff($ev_data);
                                                    $dias    = $diff->days;
                                                    $cls_card = $dias == 0 ? 'hoje' : ($dias <= 3 ? 'urgente' : '');
                                                    $pc_ev = $ev['conv'] > 0 ? round(($ev['pres']/$ev['conv'])*100) : null;
                                                ?>
                                                <div class="ev-card <?= $cls_card ?>">
                                                    <div class="ev-data">
                                                        <div class="ev-dia"><?= $ev_data->format('d') ?></div>
                                                        <div class="ev-mes"><?= strtoupper(substr($meses_pt[(int)$ev_data->format('m')],0,3)) ?></div>
                                                    </div>
                                                    <div class="ev-info">
                                                        <div class="ev-nome"><?= htmlspecialchars($ev['nome']) ?></div>
                                                        <div class="m--margin-top-5">
                                                            <span class="m-badge <?= $tipo_badge[$ev['tipo']] ?> m-badge--wide" style="font-size:10px;">
                                                                <?= $tipos_evento[$ev['tipo']] ?>
                                                            </span>
                                                            <?php if ($dias == 0): ?>
                                                                <span class="m--font-danger m--font-boldest m--margin-left-5" style="font-size:11px;">Hoje!</span>
                                                            <?php elseif ($dias <= 3): ?>
                                                                <span class="m--font-warning m--font-boldest m--margin-left-5" style="font-size:11px;">em <?= $dias ?> dia<?= $dias>1?'s':'' ?></span>
                                                            <?php else: ?>
                                                                <span class="m--font-metal m--margin-left-5" style="font-size:11px;">em <?= $dias ?> dias</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if ($pc_ev !== null): ?>
                                                        <div class="progress m-progress--sm m--margin-top-8" style="max-width:200px;">
                                                            <div class="progress-bar <?= $pc_ev>=70?'m--bg-success':($pc_ev>=50?'m--bg-warning':'m--bg-danger') ?>"
                                                                 style="width:<?= $pc_ev ?>%"></div>
                                                        </div>
                                                        <span style="font-size:10px;" class="m--font-metal">
                                                            Presença: <?= $ev['pres'] ?>/<?= $ev['conv'] ?> (<?= $pc_ev ?>%)
                                                        </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div style="flex-shrink:0; display:flex; flex-direction:column; gap:4px;">
                                                        <a href="frequencia/chamada.php?id=<?= $ev['id'] ?>"
                                                           class="btn btn-xs btn-info m-btn m-btn--pill"
                                                           style="font-size:11px; padding:3px 8px;" title="Chamada">
                                                            <i class="la la-check-square-o"></i>
                                                        </a>
                                                        <a href="eventos/evento_edit.php?id=<?= $ev['id'] ?>"
                                                           class="btn btn-xs btn-warning m-btn m-btn--pill"
                                                           style="font-size:11px; padding:3px 8px;" title="Editar">
                                                            <i class="la la-pencil"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Alertas -->
                                <div class="col-xl-4">
                                    <div class="m-portlet m-portlet--full-height">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">
                                                        <i class="la la-exclamation-triangle m--font-warning"></i>
                                                        Alertas
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">

                                            <!-- Inadimplentes -->
                                            <?php if (!empty($inadimplentes)): ?>
                                            <p class="m--font-metal" style="font-size:11px; text-transform:uppercase; font-weight:600; margin-bottom:8px;">
                                                Inadimplentes — <?= $meses_pt[$mes_atual] ?>
                                            </p>
                                            <?php foreach ($inadimplentes as $ind): ?>
                                            <div class="alerta-item">
                                                <i class="la la-money m--font-warning" style="font-size:1.4rem; flex-shrink:0;"></i>
                                                <div style="flex:1; min-width:0;">
                                                    <a href="integrante_view?id=<?= $ind['id'] ?>"
                                                       class="m--font-boldest" style="text-decoration:none; font-size:13px;">
                                                        <?= htmlspecialchars($ind['apelido']) ?>
                                                    </a>
                                                </div>
                                                <span class="m--font-danger m--font-boldest" style="font-size:12px; flex-shrink:0;">
                                                    R$ <?= number_format($ind['valor_total'], 2, ',', '.') ?>
                                                </span>
                                            </div>
                                            <?php endforeach; ?>
                                            <a href="financeiro/pendentes.php?mes=<?= $mes_atual ?>&ano=<?= $ano_atual ?>"
                                               class="btn btn-sm btn-outline-warning btn-block m-btn m-btn--pill m--margin-top-10 m--margin-bottom-15">
                                                Ver todos os pendentes
                                            </a>
                                            <?php else: ?>
                                            <div class="alerta-item">
                                                <i class="la la-check-circle m--font-success" style="font-size:1.4rem;"></i>
                                                <span class="m--font-metal" style="font-size:13px;">
                                                    Todos em dia em <?= $meses_pt[$mes_atual] ?>!
                                                </span>
                                            </div>
                                            <?php endif; ?>

                                            <!-- Faltas em obrigatórios -->
                                            <?php if (!empty($faltas_obrigatorios)): ?>
                                            <p class="m--font-metal m--margin-top-15" style="font-size:11px; text-transform:uppercase; font-weight:600; margin-bottom:8px;">
                                                Faltas em eventos obrigatórios (60 dias)
                                            </p>
                                            <?php foreach ($faltas_obrigatorios as $fo): ?>
                                            <div class="alerta-item">
                                                <i class="la la-calendar-times-o m--font-danger" style="font-size:1.4rem; flex-shrink:0;"></i>
                                                <div style="flex:1; min-width:0;">
                                                    <a href="integrante_view?id=<?= $fo['id'] ?>"
                                                       class="m--font-boldest" style="text-decoration:none; font-size:13px;">
                                                        <?= htmlspecialchars($fo['apelido']) ?>
                                                    </a>
                                                </div>
                                                <span class="m-badge m-badge--danger" style="flex-shrink:0;">
                                                    <?= $fo['faltas'] ?> falta<?= $fo['faltas']>1?'s':'' ?>
                                                </span>
                                            </div>
                                            <?php endforeach; ?>
                                            <a href="frequencia/resumo.php?ano=<?= $ano_atual ?>"
                                               class="btn btn-sm btn-outline-danger btn-block m-btn m-btn--pill m--margin-top-10">
                                                Ver resumo de frequência
                                            </a>
                                            <?php endif; ?>

                                            <?php if (empty($inadimplentes) && empty($faltas_obrigatorios)): ?>
                                            <div class="m--align-center" style="padding:20px 0;">
                                                <i class="la la-check-circle m--font-success" style="font-size:3rem;"></i>
                                                <p class="m--font-success m--margin-top-10 m--font-boldest">Tudo em ordem!</p>
                                                <p class="m--font-metal" style="font-size:12px;">Sem alertas no momento.</p>
                                            </div>
                                            <?php endif; ?>

                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- /FAIXA 3 -->

                            <!-- ══ FAIXA 4: Atividade recente ══ -->
                            <div class="row">

                                <!-- Últimos pagamentos -->
                                <div class="col-xl-6">
                                    <div class="m-portlet m-portlet--full-height">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">
                                                        <i class="la la-check-circle m--font-success"></i>
                                                        Últimos Pagamentos — <?= $meses_pt[$mes_atual] ?>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="m-portlet__head-tools">
                                                <a href="financeiro/list.php?mes=<?= $mes_atual ?>&ano=<?= $ano_atual ?>"
                                                   class="btn btn-sm btn-outline-success m-btn m-btn--pill">Ver todos</a>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">
                                            <?php if (empty($ultimos_pagamentos)): ?>
                                                <p class="m--font-metal m--align-center">Nenhum pagamento registrado este mês.</p>
                                            <?php else: ?>
                                            <div class="m-widget4">
                                                <?php foreach ($ultimos_pagamentos as $pg): ?>
                                                <div class="m-widget4__item">
                                                    <div class="m-widget4__img m-widget4__img--icon">
                                                        <i class="flaticon-user m--font-success" style="font-size:2rem;"></i>
                                                    </div>
                                                    <div class="m-widget4__info">
                                                        <span class="m-widget4__title"><?= htmlspecialchars($pg['apelido']) ?></span>
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

                                <!-- Últimas presenças confirmadas -->
                                <div class="col-xl-6">
                                    <div class="m-portlet m-portlet--full-height">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">
                                                        <i class="la la-calendar-check-o m--font-brand"></i>
                                                        Últimas Presenças Confirmadas
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="m-portlet__head-tools">
                                                <a href="frequencia/index.php"
                                                   class="btn btn-sm btn-outline-brand m-btn m-btn--pill">Ver frequência</a>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">
                                            <?php if (empty($ultimas_presencas)): ?>
                                                <p class="m--font-metal m--align-center">Nenhuma presença registrada ainda.</p>
                                            <?php else: ?>
                                            <div class="m-widget4">
                                                <?php foreach ($ultimas_presencas as $pr): ?>
                                                <div class="m-widget4__item">
                                                    <div class="m-widget4__img m-widget4__img--icon">
                                                        <i class="flaticon-user m--font-brand" style="font-size:2rem;"></i>
                                                    </div>
                                                    <div class="m-widget4__info">
                                                        <span class="m-widget4__title"><?= htmlspecialchars($pr['apelido']) ?></span>
                                                        <br>
                                                        <span class="m-widget4__sub" style="max-width:180px;display:inline-block;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">
                                                            <?= htmlspecialchars($pr['evento']) ?>
                                                        </span>
                                                    </div>
                                                    <span class="m-widget4__ext">
                                                        <span class="m-widget4__number m--font-metal" style="font-size:11px;">
                                                            <?= date('d/m/Y', strtotime($pr['data_evento'])) ?>
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
                            <!-- /FAIXA 4 -->

                        </div>
                        <!-- /m-content -->

                    </div>
                </div>
            </div>

            <?php require_once ('inc/footer.php'); ?>
        </div>

        <div class="m-scroll-top m-scroll-top--skin-top" data-toggle="m-scroll-top" data-scroll-offset="500" data-scroll-speed="300">
            <i class="la la-arrow-up"></i>
        </div>

        <script src="js/vendors.bundle.js" type="text/javascript"></script>
        <script src="js/scripts.bundle.js" type="text/javascript"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        // Donut integrantes
        new Chart(document.getElementById('donutIntegrantes'), {
            type: 'doughnut',
            data: {
                labels: ['Ativos','Afastados','Suspensos','Desligados'],
                datasets: [{
                    data: [<?= (int)$total_ativos ?>, <?= (int)$total_afastados ?>, <?= (int)$total_suspensos ?>, <?= (int)$total_desligados ?>],
                    backgroundColor: ['#34bfa3','#ffb822','#a0a0a0','#f4516c'],
                    borderWidth: 2
                }]
            },
            options: {
                cutout: '70%',
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // Donut mensalidades
        new Chart(document.getElementById('donutMensalidades'), {
            type: 'doughnut',
            data: {
                labels: ['Pagos','Pendentes','Isentos'],
                datasets: [{
                    data: [<?= (int)$mens['pagos'] ?>, <?= (int)$mens['pendentes'] ?>, <?= (int)$mens['isentos'] ?>],
                    backgroundColor: ['#34bfa3','#ffb822','#a0a0a0'],
                    borderWidth: 2
                }]
            },
            options: {
                cutout: '70%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
        </script>

    </body>
</html>