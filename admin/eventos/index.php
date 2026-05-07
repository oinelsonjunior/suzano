<?php
    session_start();
    require_once ('../inc/general.php');

    $hoje     = new DateTime();
    $mes_nav  = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)$hoje->format('m');
    $ano_nav  = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)$hoje->format('Y');

    // Normalizar mês
    if ($mes_nav < 1)  { $mes_nav = 12; $ano_nav--; }
    if ($mes_nav > 12) { $mes_nav = 1;  $ano_nav++; }

    $tipos_evento = [
        1 => 'Sede Estadual (padrão)',
        2 => 'Sede Estadual (reunião geral)',
        3 => 'Sede Suzano (reunião)',
        4 => 'Sede Suzano (confraternização)',
        5 => 'Evento Fora',
        6 => 'Evento Obrigatório',
    ];

    $tipo_color = [
        1 => '#a0a0a0',   // metal
        2 => '#716aca',   // brand
        3 => '#36a3f7',   // info
        4 => '#34bfa3',   // success
        5 => '#ffb822',   // warning
        6 => '#f4516c',   // danger
    ];

    $tipo_badge = [
        1 => 'm-badge--metal',
        2 => 'm-badge--brand',
        3 => 'm-badge--info',
        4 => 'm-badge--success',
        5 => 'm-badge--warning',
        6 => 'm-badge--danger',
    ];

    $meses_pt = [
        1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',
        5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',
        9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'
    ];

    // Eventos do mês exibido
    $stmt = $conn->prepare("
        SELECT e.id, e.tipo, e.nome, e.data_evento, e.observacao,
               COUNT(f.id) AS total_conv,
               SUM(f.presente = 1) AS total_pres
        FROM eventos e
        LEFT JOIN frequencias f ON f.evento_id = e.id
        WHERE e.faccao = 1
          AND MONTH(e.data_evento) = ? AND YEAR(e.data_evento) = ?
        GROUP BY e.id
        ORDER BY e.data_evento ASC
    ");
    $stmt->bind_param("ii", $mes_nav, $ano_nav);
    $stmt->execute();
    $eventos_mes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Indexar eventos por dia para o calendário
    $eventos_por_dia = [];
    foreach ($eventos_mes as $ev) {
        $dia = (int)date('j', strtotime($ev['data_evento']));
        $eventos_por_dia[$dia][] = $ev;
    }

    // Próximos eventos (a partir de hoje, excluindo o mês atual se já estivermos vendo outro)
    $stmt2 = $conn->prepare("
        SELECT e.id, e.tipo, e.nome, e.data_evento, e.observacao,
               COUNT(f.id) AS total_conv,
               SUM(f.presente = 1) AS total_pres
        FROM eventos e
        LEFT JOIN frequencias f ON f.evento_id = e.id
        WHERE e.faccao = 1
          AND e.data_evento >= CURDATE()
        GROUP BY e.id
        ORDER BY e.data_evento ASC
        LIMIT 8
    ");
    $stmt2->execute();
    $proximos = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

    // Montar grade do calendário
    $primeiro_dia_mes = new DateTime("$ano_nav-$mes_nav-01");
    $dias_no_mes      = (int)$primeiro_dia_mes->format('t');
    $dia_semana_inicio = (int)$primeiro_dia_mes->format('w'); // 0=dom

    // Total de eventos no mês
    $total_mes = count($eventos_mes);
    $is_mes_atual = ($mes_nav == (int)$hoje->format('m') && $ano_nav == (int)$hoje->format('Y'));
    $dia_hoje = $is_mes_atual ? (int)$hoje->format('j') : 0;

    // Mês anterior e próximo para navegação
    $mes_ant = $mes_nav - 1; $ano_ant = $ano_nav;
    if ($mes_ant < 1) { $mes_ant = 12; $ano_ant--; }
    $mes_prox = $mes_nav + 1; $ano_prox = $ano_nav;
    if ($mes_prox > 12) { $mes_prox = 1; $ano_prox++; }
?>
<!DOCTYPE html>
<html lang="pt-br">
    <base href="../">
    <head>
        <meta charset="utf-8" />
        <title>Abutre's MC | Eventos</title>
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
            /* ── Calendário ── */
            .cal-wrap        { overflow-x: auto; }
            .cal-grid        { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; min-width: 560px; }
            .cal-header-day  { text-align: center; font-size: 11px; font-weight: 600; text-transform: uppercase;
                               letter-spacing: .06em; color: #9699a2; padding: 6px 0; }
            .cal-cell        { min-height: 90px; border-radius: 8px; padding: 6px; background: #f8f9fa;
                               border: 1px solid #ebedf2; position: relative; transition: background .15s; }
            .cal-cell:hover  { background: #f0f1f7; }
            .cal-cell.today  { background: #eef1ff; border-color: #716aca; }
            .cal-cell.today .cal-day-num { color: #716aca; font-weight: 700; }
            .cal-cell.outro-mes { background: #fafafa; opacity: .45; }
            .cal-cell.tem-evento { border-color: #36a3f7; }
            .cal-day-num     { font-size: 13px; font-weight: 500; color: #575962; line-height: 1; margin-bottom: 4px; }
            .cal-evento-chip { display: block; font-size: 10px; font-weight: 500; color: #fff; border-radius: 4px;
                               padding: 2px 5px; margin-bottom: 2px; white-space: nowrap; overflow: hidden;
                               text-overflow: ellipsis; cursor: pointer; text-decoration: none; }
            .cal-evento-chip:hover { opacity: .85; color: #fff; }
            .cal-mais        { font-size: 10px; color: #9699a2; margin-top: 2px; }
            .cal-vazio       { background: transparent; border: 1px solid transparent; }

            /* ── Próximos eventos ── */
            .prox-item       { display: flex; align-items: flex-start; gap: 12px; padding: 10px 0;
                               border-bottom: 1px solid #ebedf2; }
            .prox-item:last-child { border-bottom: none; }
            .prox-data-box   { flex-shrink: 0; width: 44px; text-align: center; background: #f8f9fa;
                               border-radius: 8px; padding: 6px 4px; border: 1px solid #ebedf2; }
            .prox-dia        { font-size: 18px; font-weight: 700; line-height: 1; color: #575962; }
            .prox-mes        { font-size: 10px; color: #9699a2; text-transform: uppercase; }
            .prox-info       { flex: 1; min-width: 0; }
            .prox-nome       { font-size: 13px; font-weight: 600; color: #575962;
                               white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .prox-tipo       { font-size: 11px; margin-top: 2px; }
            .prox-acoes      { flex-shrink: 0; }
            .prox-item.hoje-ev .prox-data-box { background: #eef1ff; border-color: #716aca; }
            .prox-item.hoje-ev .prox-dia      { color: #716aca; }

            /* ── Legenda ── */
            .leg-item   { display: flex; align-items: center; gap: 6px; font-size: 12px; margin-bottom: 5px; }
            .leg-dot    { width: 12px; height: 12px; border-radius: 3px; flex-shrink: 0; }
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
                                        Calendário de Eventos
                                    </h3>
                                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                                        <li class="m-nav__item m-nav__item--home">
                                            <a href="index" class="m-nav__link m-nav__link--icon">
                                                <i class="m-nav__link-icon la la-home"></i>
                                            </a>
                                        </li>
                                        <li class="m-nav__separator">—</li>
                                        <li class="m-nav__item">
                                            <span class="m-nav__link-text">Eventos</span>
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <a href="frequencia/index.php?mes=<?= $mes_nav ?>&ano=<?= $ano_nav ?>"
                                       class="btn btn-sm btn-info m-btn m-btn--icon m-btn--pill">
                                        <span><i class="la la-check-square-o"></i><span>Frequência</span></span>
                                    </a>
                                    &nbsp;
                                    <a href="frequencia/evento_form.php"
                                       class="btn btn-sm btn-brand m-btn m-btn--icon m-btn--pill">
                                        <span><i class="la la-plus"></i><span>Novo Evento</span></span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="m-content">
                            <div class="row">

                                <!-- COLUNA PRINCIPAL: Calendário -->
                                <div class="col-xl-8">
                                    <div class="m-portlet m-portlet--mobile">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">
                                                        <!-- Navegação de mês -->
                                                        <a href="eventos/index.php?mes=<?= $mes_ant ?>&ano=<?= $ano_ant ?>"
                                                           class="btn btn-sm btn-secondary m-btn m-btn--icon m-btn--pill m--margin-right-10">
                                                            <i class="la la-angle-left"></i>
                                                        </a>
                                                        <?= $meses_pt[$mes_nav] ?> <?= $ano_nav ?>
                                                        <a href="eventos/index.php?mes=<?= $mes_prox ?>&ano=<?= $ano_prox ?>"
                                                           class="btn btn-sm btn-secondary m-btn m-btn--icon m-btn--pill m--margin-left-10">
                                                            <i class="la la-angle-right"></i>
                                                        </a>
                                                        <?php if (!$is_mes_atual): ?>
                                                        <a href="eventos/index.php"
                                                           class="btn btn-sm btn-outline-brand m-btn m-btn--pill m--margin-left-10"
                                                           style="font-size:11px;">
                                                            Hoje
                                                        </a>
                                                        <?php endif; ?>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="m-portlet__head-tools">
                                                <span class="m--font-metal" style="font-size:12px;">
                                                    <?= $total_mes ?> evento<?= $total_mes != 1 ? 's' : '' ?> neste mês
                                                </span>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">
                                            <div class="cal-wrap">
                                                <div class="cal-grid">
                                                    <!-- Cabeçalhos de dia -->
                                                    <?php
                                                    $dias_semana = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];
                                                    foreach ($dias_semana as $ds):
                                                    ?>
                                                    <div class="cal-header-day"><?= $ds ?></div>
                                                    <?php endforeach; ?>

                                                    <!-- Células vazias antes do dia 1 -->
                                                    <?php for ($v = 0; $v < $dia_semana_inicio; $v++): ?>
                                                    <div class="cal-cell cal-vazio"></div>
                                                    <?php endfor; ?>

                                                    <!-- Dias do mês -->
                                                    <?php for ($d = 1; $d <= $dias_no_mes; $d++):
                                                        $evs    = $eventos_por_dia[$d] ?? [];
                                                        $is_hoje = ($d === $dia_hoje);
                                                        $classes = 'cal-cell';
                                                        if ($is_hoje) $classes .= ' today';
                                                        if (!empty($evs)) $classes .= ' tem-evento';
                                                    ?>
                                                    <div class="<?= $classes ?>">
                                                        <div class="cal-day-num"><?= $d ?></div>
                                                        <?php foreach (array_slice($evs, 0, 2) as $ev): ?>
                                                            <a href="eventos/evento_edit.php?id=<?= $ev['id'] ?>"
                                                               class="cal-evento-chip"
                                                               style="background:<?= $tipo_color[$ev['tipo']] ?>;"
                                                               title="<?= htmlspecialchars($ev['nome']) ?> — <?= htmlspecialchars($tipos_evento[$ev['tipo']]) ?>">
                                                                <?= htmlspecialchars($ev['nome']) ?>
                                                            </a>
                                                        <?php endforeach; ?>
                                                        <?php if (count($evs) > 2): ?>
                                                            <div class="cal-mais">+<?= count($evs) - 2 ?> mais</div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php endfor; ?>

                                                </div>
                                            </div>

                                            <!-- Legenda de tipos -->
                                            <div class="m--margin-top-20" style="columns: 2; column-gap: 20px;">
                                                <?php foreach ($tipos_evento as $tid => $tnome): ?>
                                                <div class="leg-item">
                                                    <div class="leg-dot" style="background:<?= $tipo_color[$tid] ?>;"></div>
                                                    <span class="m--font-metal"><?= $tnome ?></span>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>

                                        </div>
                                    </div>

                                    <!-- Lista dos eventos do mês -->
                                    <?php if (!empty($eventos_mes)): ?>
                                    <div class="m-portlet m-portlet--mobile">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">
                                                        Eventos de <?= $meses_pt[$mes_nav] ?>/<?= $ano_nav ?>
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">
                                            <div class="table-responsive">
                                                <table class="table table-hover m-table m-table--head-bg-brand">
                                                    <thead>
                                                        <tr>
                                                            <th>Data</th>
                                                            <th>Evento</th>
                                                            <th>Tipo</th>
                                                            <th class="m--align-center">Presença</th>
                                                            <th class="m--align-center">Ações</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($eventos_mes as $ev):
                                                            $pc = $ev['total_conv'] > 0
                                                                ? round(($ev['total_pres'] / $ev['total_conv']) * 100) : 0;
                                                            $ev_data = new DateTime($ev['data_evento']);
                                                            $passado = $ev_data < $hoje;
                                                        ?>
                                                        <tr>
                                                            <td style="white-space:nowrap;">
                                                                <?php if ($ev_data->format('d/m/Y') === $hoje->format('d/m/Y')): ?>
                                                                    <span class="m-badge m-badge--brand m-badge--wide">Hoje</span>
                                                                <?php else: ?>
                                                                    <span class="<?= $passado ? 'm--font-metal' : 'm--font-boldest' ?>">
                                                                        <?= $ev_data->format('d/m/Y') ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <span class="<?= $passado ? 'm--font-metal' : 'm--font-boldest' ?>">
                                                                    <?= htmlspecialchars($ev['nome']) ?>
                                                                </span>
                                                                <?php if ($ev['observacao']): ?>
                                                                    <br><small class="m--font-metal"><?= htmlspecialchars($ev['observacao']) ?></small>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <span class="m-badge <?= $tipo_badge[$ev['tipo']] ?> m-badge--wide">
                                                                    <?= $tipos_evento[$ev['tipo']] ?>
                                                                </span>
                                                            </td>
                                                            <td class="m--align-center">
                                                                <?php if ($ev['total_conv'] > 0): ?>
                                                                    <span class="m--font-boldest <?= $pc >= 70 ? 'm--font-success' : ($pc >= 50 ? 'm--font-warning' : 'm--font-danger') ?>">
                                                                        <?= $pc ?>%
                                                                    </span>
                                                                    <div class="progress m-progress--sm m--margin-top-5">
                                                                        <div class="progress-bar <?= $pc >= 70 ? 'm--bg-success' : ($pc >= 50 ? 'm--bg-warning' : 'm--bg-danger') ?>"
                                                                             style="width:<?= $pc ?>%"></div>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <span class="m--font-metal">—</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="m--align-center" style="white-space:nowrap;">
                                                                <a href="frequencia/chamada.php?id=<?= $ev['id'] ?>"
                                                                   class="btn btn-sm btn-info m-btn m-btn--icon m-btn--pill"
                                                                   title="Chamada">
                                                                    <span><i class="la la-check-square-o"></i></span>
                                                                </a>
                                                                <a href="eventos/evento_edit.php?id=<?= $ev['id'] ?>"
                                                                   class="btn btn-sm btn-warning m-btn m-btn--icon m-btn--pill"
                                                                   title="Editar">
                                                                    <span><i class="la la-pencil"></i></span>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- COLUNA LATERAL: Próximos eventos -->
                                <div class="col-xl-4">
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
                                                   class="btn btn-sm btn-outline-brand m-btn m-btn--pill">
                                                    <i class="la la-plus"></i> Novo
                                                </a>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">
                                            <?php if (empty($proximos)): ?>
                                                <div class="m--align-center" style="padding:30px 0;">
                                                    <i class="la la-calendar-times-o m--font-metal" style="font-size:3rem;"></i>
                                                    <p class="m--font-metal m--margin-top-10">Nenhum evento agendado.</p>
                                                </div>
                                            <?php else: ?>
                                                <?php foreach ($proximos as $pev):
                                                    $pdata  = new DateTime($pev['data_evento']);
                                                    $is_hj  = $pdata->format('Y-m-d') === $hoje->format('Y-m-d');
                                                ?>
                                                <div class="prox-item <?= $is_hj ? 'hoje-ev' : '' ?>">
                                                    <div class="prox-data-box">
                                                        <div class="prox-dia"><?= $pdata->format('d') ?></div>
                                                        <div class="prox-mes"><?= strtoupper(substr($meses_pt[(int)$pdata->format('m')], 0, 3)) ?></div>
                                                    </div>
                                                    <div class="prox-info">
                                                        <div class="prox-nome" title="<?= htmlspecialchars($pev['nome']) ?>">
                                                            <?= htmlspecialchars($pev['nome']) ?>
                                                        </div>
                                                        <div class="prox-tipo">
                                                            <span class="m-badge <?= $tipo_badge[$pev['tipo']] ?> m-badge--wide" style="font-size:10px;">
                                                                <?= $tipos_evento[$pev['tipo']] ?>
                                                            </span>
                                                        </div>
                                                        <?php if ($is_hj): ?>
                                                            <div style="font-size:11px;" class="m--font-brand m--font-boldest">
                                                                <i class="la la-star"></i> Hoje!
                                                            </div>
                                                        <?php else: ?>
                                                            <?php
                                                            $diff = $hoje->diff($pdata);
                                                            $dias_faltam = $diff->days;
                                                            ?>
                                                            <div style="font-size:11px;" class="m--font-metal">
                                                                em <?= $dias_faltam ?> dia<?= $dias_faltam != 1 ? 's' : '' ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="prox-acoes d-flex flex-column" style="gap:4px;">
                                                        <a href="frequencia/chamada.php?id=<?= $pev['id'] ?>"
                                                           class="btn btn-xs btn-info m-btn m-btn--pill"
                                                           title="Chamada" style="font-size:11px; padding:3px 8px;">
                                                            <i class="la la-check-square-o"></i>
                                                        </a>
                                                        <a href="eventos/evento_edit.php?id=<?= $pev['id'] ?>"
                                                           class="btn btn-xs btn-warning m-btn m-btn--pill"
                                                           title="Editar" style="font-size:11px; padding:3px 8px;">
                                                            <i class="la la-pencil"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                            </div>
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
    </body>
</html>