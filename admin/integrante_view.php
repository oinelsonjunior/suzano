<?php
    session_start();
    require_once('inc/general.php');

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) { header("Location: integrantes_faccao"); exit; }

    $stmt = $conn->prepare("SELECT * FROM cadastro_integrante WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $integrante = $stmt->get_result()->fetch_assoc();
    if (!$integrante) { header("Location: integrantes_faccao"); exit; }

    // Padrinho
    $padrinho_nome = '—';
    if ($integrante['padrinho'] > 0) {
        $sp = $conn->prepare("SELECT apelido FROM cadastro_integrante WHERE id = ?");
        $sp->bind_param("i", $integrante['padrinho']);
        $sp->execute();
        $pr = $sp->get_result()->fetch_assoc();
        $padrinho_nome = $pr['apelido'] ?? '—';
    }

    $hoje = date('Y-m-d');

    // FREQUÊNCIA — lista completa
    $stmt2 = $conn->prepare("
        SELECT f.presente, e.nome, e.data_evento, e.tipo
        FROM frequencias f
        INNER JOIN eventos e ON e.id = f.evento_id
        WHERE f.integrante_id = ?
        ORDER BY e.data_evento DESC
        LIMIT 20
    ");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $freq_recente = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

    // Taxa de frequência: só eventos ANTERIORES a hoje (1 dia após o evento)
    $stmt3 = $conn->prepare("
        SELECT COUNT(*) as total, SUM(f.presente=1) as presentes
        FROM frequencias f
        INNER JOIN eventos e ON e.id = f.evento_id
        WHERE f.integrante_id = ? AND e.data_evento < ?
    ");
    $stmt3->bind_param("is", $id, $hoje);
    $stmt3->execute();
    $freq_total = $stmt3->get_result()->fetch_assoc();
    $pc_freq = ($freq_total['total'] > 0)
        ? round(($freq_total['presentes'] / $freq_total['total']) * 100) : 0;

    // MENSALIDADES — todas, sem limite
    $stmt4 = $conn->prepare("
        SELECT m.id, m.mes, m.ano, m.pago, m.isento, m.valor_total,
               m.valor_repasse, m.valor_faccao, m.data_pagamento, m.patente_no_mes,
               (m.isento = 1 OR m.patente_no_mes = 6) AS isento_efetivo,
               i.motivo AS motivo_isencao,
               u.username AS isento_por
        FROM mensalidades m
        LEFT JOIN isencoes i ON i.mensalidade_id = m.id
        LEFT JOIN users u    ON u.id = i.admin_id
        WHERE m.integrante_id = ?
        ORDER BY m.ano DESC, m.mes DESC
    ");
    $stmt4->bind_param("i", $id);
    $stmt4->execute();
    $mensalidades = $stmt4->get_result()->fetch_all(MYSQLI_ASSOC);

    // Totais financeiros
    $total_pago = $total_pendente = $total_repasse_pago = $total_repasse_geral = 0;
    foreach ($mensalidades as $m) {
        if ($m['isento_efetivo']) continue;
        $total_repasse_geral += (float)$m['valor_repasse'];
        if ($m['pago']) {
            $total_pago         += (float)$m['valor_total'];
            $total_repasse_pago += (float)$m['valor_repasse'];
        } else {
            $total_pendente += (float)$m['valor_total'];
        }
    }
    $pendentes_geral = array_filter($mensalidades, fn($m) => !$m['isento_efetivo'] && !$m['pago']);

    // SAÍDAS — todas (visão global do caixa)
    $saidas = $conn->query(
        "SELECT * FROM caixa_saida ORDER BY data_saida DESC"
    )->fetch_all(MYSQLI_ASSOC);
    $total_saidas = array_sum(array_column($saidas, 'valor'));

    // DISCIPLINA
    $stmt_disc = $conn->prepare("
        SELECT *, DATEDIFF(data_fim, ?) AS dias_restantes
        FROM disciplina
        WHERE integrante_id = ?
        ORDER BY created_at DESC
    ");
    $stmt_disc->bind_param("si", $hoje, $id);
    $stmt_disc->execute();
    $disciplinas = $stmt_disc->get_result()->fetch_all(MYSQLI_ASSOC);
    $disc_ativas = array_filter($disciplinas, fn($d) => $d['ativo'] == 1);

    // Labels
    $patentes      = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X'];
    $status_labels = [1=>'Ativo',2=>'Afastado',3=>'Desligado',4=>'Suspenso'];
    $status_badge  = [1=>'m-badge--success',2=>'m-badge--warning',3=>'m-badge--danger',4=>'m-badge--metal'];
    $meses_pt      = [1=>'Jan',2=>'Fev',3=>'Mar',4=>'Abr',5=>'Mai',6=>'Jun',
                      7=>'Jul',8=>'Ago',9=>'Set',10=>'Out',11=>'Nov',12=>'Dez'];
    $tipos_evento  = [1=>'Sede Est. (padrão)',2=>'Sede Est. (reunião)',
                      3=>'Suzano (reunião)',4=>'Suzano (confrat.)',
                      5=>'Evento Fora',6=>'Obrigatório'];
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="utf-8"/>
        <title>Abutre's MC | <?= htmlspecialchars($integrante['apelido']) ?></title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
        <script>
            WebFont.load({
                google: {"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},
                active: function() { sessionStorage.fonts = true; }
            });
        </script>
        <link href="css/style.bundle.css" rel="stylesheet" type="text/css"/>
        <style>
            .dias-badge { display:inline-block; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:600; }
            .dias-critico { background:#fff0f3; color:#f4516c; border:1px solid #f4516c; }
            .dias-alerta  { background:#fff8ee; color:#ffb822; border:1px solid #ffb822; }
            .dias-ok      { background:#f0faf7; color:#34bfa3; border:1px solid #34bfa3; }
            .nav-tabs .nav-link { color:#6c6e86; font-size:13px; border:none; padding:10px 15px; }
            .nav-tabs .nav-link.active { color:#716aca; font-weight:600; border-bottom:2px solid #716aca; background:transparent; }
            .nav-tabs { border-bottom:1px solid #ebedf2; }
            .tab-content { padding-top:20px; }
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
                                    <?= htmlspecialchars($integrante['apelido']) ?>
                                    <?php if (!empty($disc_ativas)): ?>
                                        <span class="m-badge m-badge--danger m-badge--wide"
                                              style="font-size:13px; vertical-align:middle; margin-left:8px;">
                                            <i class="la la-gavel"></i> Suspenso
                                        </span>
                                    <?php endif; ?>
                                </h3>
                                <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                                    <li class="m-nav__item m-nav__item--home">
                                        <a href="index" class="m-nav__link m-nav__link--icon">
                                            <i class="m-nav__link-icon la la-home"></i>
                                        </a>
                                    </li>
                                    <li class="m-nav__separator">—</li>
                                    <li class="m-nav__item">
                                        <a href="integrantes_faccao" class="m-nav__link">
                                            <span class="m-nav__link-text">Integrantes</span>
                                        </a>
                                    </li>
                                    <li class="m-nav__separator">—</li>
                                    <li class="m-nav__item">
                                        <span class="m-nav__link-text"><?= htmlspecialchars($integrante['apelido']) ?></span>
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <a href="disciplina/form.php?integrante_id=<?= $id ?>"
                                   class="btn btn-sm btn-danger m-btn m-btn--icon m-btn--pill">
                                    <span><i class="la la-gavel"></i><span>Suspensão</span></span>
                                </a>
                                &nbsp;
                                <a href="integrantes/integrante_form.php?id=<?= $id ?>"
                                   class="btn btn-sm btn-warning m-btn m-btn--icon m-btn--pill">
                                    <span><i class="la la-pencil"></i><span>Editar</span></span>
                                </a>
                                &nbsp;
                                <a href="integrantes_faccao"
                                   class="btn btn-sm btn-secondary m-btn m-btn--icon m-btn--pill">
                                    <span><i class="la la-arrow-left"></i><span>Voltar</span></span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="m-content">
                        <div class="row">

                            <!-- ══ COL ESQUERDA: Perfil ══ -->
                            <div class="col-xl-3">

                                <!-- Avatar + badges -->
                                <div class="m-portlet m-portlet--mobile">
                                    <div class="m-portlet__body" style="text-align:center; padding:25px 15px;">
                                        <?php
                                        $foto_view = $integrante['foto'] ?? null;
                                        $foto_ok   = $foto_view && file_exists(__DIR__ . '/../' . $foto_view);
                                        $foto_src  = $foto_ok ? ('../' . $foto_view) : null;
                                        ?>
                                        <div style="width:90px;height:90px;border-radius:50%;background:#716aca;
                                                    display:flex;align-items:center;justify-content:center;
                                                    margin:0 auto 12px;overflow:hidden;
                                                    border:3px solid <?= !empty($disc_ativas) ? '#f4516c' : '#ebedf2' ?>;">
                                            <?php if ($foto_src): ?>
                                                <img src="<?= htmlspecialchars($foto_src) ?>"
                                                     style="width:100%;height:100%;object-fit:cover;">
                                            <?php else: ?>
                                                <i class="flaticon-user" style="font-size:2.5rem;color:#fff;"></i>
                                            <?php endif; ?>
                                        </div>
                                        <h4 class="m--font-boldest" style="margin-bottom:4px;">
                                            <?= htmlspecialchars($integrante['apelido']) ?>
                                        </h4>
                                        <p class="m--font-metal" style="font-size:12px; margin-bottom:10px;">
                                            <?= htmlspecialchars($integrante['nome']) ?>
                                        </p>
                                        <span class="m-badge m-badge--info m-badge--wide m--margin-right-5">
                                            Pat. <?= $patentes[$integrante['patente']] ?? '—' ?>
                                        </span>
                                        <span class="m-badge <?= $status_badge[$integrante['status']] ?> m-badge--wide">
                                            <?= $status_labels[$integrante['status']] ?>
                                        </span>
                                        <?php if ($integrante['celular'] || $integrante['telefone']): ?>
                                        <div class="m--margin-top-15">
                                            <?php $cel = $integrante['celular'] ?: $integrante['telefone']; ?>
                                            <a href="https://wa.me/55<?= preg_replace('/\D/','',$cel) ?>"
                                               target="_blank"
                                               class="btn btn-success btn-block m-btn m-btn--icon m-btn--pill"
                                               style="font-size:12px;">
                                                <span>
                                                    <i class="la la-whatsapp"></i>
                                                    <span><?= htmlspecialchars($cel) ?></span>
                                                </span>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Dados pessoais -->
                                <div class="m-portlet m-portlet--mobile">
                                    <div class="m-portlet__head">
                                        <div class="m-portlet__head-caption">
                                            <div class="m-portlet__head-title">
                                                <h3 class="m-portlet__head-text">Dados</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="m-portlet__body">
                                        <div class="m-widget1">
                                            <?php
                                            $linha = function($label, $valor, $icon='') {
                                                if (!$valor || $valor === '—') return;
                                                echo '<div class="m-widget1__item">';
                                                echo '<div class="row m-row--no-padding align-items-center">';
                                                echo '<div class="col"><h5 class="m-widget1__title">'
                                                    . ($icon ? '<i class="la la-'.$icon.' m--font-metal m--margin-right-5"></i>' : '')
                                                    . htmlspecialchars($label) . '</h5></div>';
                                                echo '<div class="col m--align-right"><span class="m--font-metal" style="font-size:12px;">'
                                                    . $valor . '</span></div>';
                                                echo '</div></div>';
                                            };
                                            $linha('Padrinho', $padrinho_nome, 'user');
                                            $linha('Apresentação',
                                                ($integrante['data_apresentacao'] && $integrante['data_apresentacao'] != '0000-00-00 00:00:00')
                                                ? date('d/m/Y', strtotime($integrante['data_apresentacao'])) : null, 'calendar');
                                            $linha('Nascimento',
                                                ($integrante['nascimento'] && $integrante['nascimento'] != '0000-00-00 00:00:00')
                                                ? date('d/m/Y', strtotime($integrante['nascimento'])) : null, 'calendar');
                                            $linha('Veículo', $integrante['veiculo'], 'motorcycle');
                                            $linha('CNH', $integrante['cnh'] ?: null, 'id-card');
                                            $linha('Email',
                                                $integrante['email']
                                                ? '<a href="mailto:'.htmlspecialchars($integrante['email']).'">'.htmlspecialchars($integrante['email']).'</a>'
                                                : null, 'envelope');
                                            ?>
                                            <?php if ($integrante['endereco']): ?>
                                            <div class="m-widget1__item">
                                                <h5 class="m-widget1__title">
                                                    <i class="la la-map-marker m--font-metal m--margin-right-5"></i>Endereço
                                                </h5>
                                                <p class="m--font-metal m--margin-top-5" style="font-size:11px;margin-bottom:0;">
                                                    <?= htmlspecialchars($integrante['endereco']) ?>, <?= htmlspecialchars($integrante['num_endereco']) ?>
                                                    <?= $integrante['complemento'] ? ' — '.htmlspecialchars($integrante['complemento']) : '' ?><br>
                                                    <?= htmlspecialchars($integrante['bairro']) ?> — <?= htmlspecialchars($integrante['cidade']) ?>/<?= htmlspecialchars($integrante['estado']) ?>
                                                    <?= $integrante['cep'] ? '<br>CEP '.htmlspecialchars($integrante['cep']) : '' ?>
                                                </p>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- KPIs financeiros rápidos -->
                                <div class="m-portlet m-portlet--mobile">
                                    <div class="m-portlet__head">
                                        <div class="m-portlet__head-caption">
                                            <div class="m-portlet__head-title">
                                                <h3 class="m-portlet__head-text">
                                                    <i class="la la-bar-chart m--font-brand"></i> Resumo
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="m-portlet__body">
                                        <div class="m-widget1">
                                            <div class="m-widget1__item">
                                                <div class="row m-row--no-padding align-items-center">
                                                    <div class="col"><h5 class="m-widget1__title">Frequência</h5>
                                                    <span class="m-widget1__desc" style="font-size:10px;">eventos passados</span></div>
                                                    <div class="col m--align-right">
                                                        <span class="m--font-boldest <?= $pc_freq>=70?'m--font-success':($pc_freq>=50?'m--font-warning':'m--font-danger') ?>" style="font-size:14px;">
                                                            <?= $pc_freq ?>%
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="m-widget1__item">
                                                <div class="row m-row--no-padding align-items-center">
                                                    <div class="col"><h5 class="m-widget1__title">Total pago</h5></div>
                                                    <div class="col m--align-right">
                                                        <span class="m--font-success m--font-boldest" style="font-size:13px;">
                                                            R$ <?= number_format($total_pago,2,',','.') ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="m-widget1__item">
                                                <div class="row m-row--no-padding align-items-center">
                                                    <div class="col"><h5 class="m-widget1__title">Pendente</h5></div>
                                                    <div class="col m--align-right">
                                                        <span class="<?= $total_pendente>0?'m--font-warning m--font-boldest':'m--font-metal' ?>" style="font-size:13px;">
                                                            R$ <?= number_format($total_pendente,2,',','.') ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="m-widget1__item">
                                                <div class="row m-row--no-padding align-items-center">
                                                    <div class="col">
                                                        <h5 class="m-widget1__title">Repasse Estadual</h5>
                                                        <span class="m-widget1__desc" style="font-size:10px;">total gerado</span>
                                                    </div>
                                                    <div class="col m--align-right">
                                                        <span class="m--font-metal" style="font-size:13px;">
                                                            R$ <?= number_format($total_repasse_geral,2,',','.') ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- /COL ESQUERDA -->

                            <!-- ══ COL DIREITA: Tabs ══ -->
                            <div class="col-xl-9">
                                <div class="m-portlet m-portlet--mobile">
                                    <div class="m-portlet__body" style="padding-bottom:0;">
                                        <ul class="nav nav-tabs" id="tabPerfil" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-toggle="tab" href="#tab-mensalidades">
                                                    <i class="la la-money"></i> Mensalidades
                                                    <?php if (!empty($pendentes_geral)): ?>
                                                        <span class="m-badge m-badge--warning" style="font-size:10px; padding:1px 5px; margin-left:3px;">
                                                            <?= count($pendentes_geral) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-toggle="tab" href="#tab-frequencia">
                                                    <i class="la la-calendar-check-o"></i> Frequência
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link <?= !empty($disc_ativas) ? 'text-danger' : '' ?>"
                                                   data-toggle="tab" href="#tab-disciplina">
                                                    <i class="la la-gavel"></i> Disciplina
                                                    <?php if (!empty($disc_ativas)): ?>
                                                        <span class="m-badge m-badge--danger" style="font-size:10px; padding:1px 5px; margin-left:3px;">!</span>
                                                    <?php endif; ?>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="m-portlet__body">
                                        <div class="tab-content">

                                            <!-- TAB MENSALIDADES -->
                                            <div class="tab-pane fade show active" id="tab-mensalidades" role="tabpanel">

                                                <?php if (!empty($pendentes_geral)): ?>
                                                <div class="m-alert m-alert--icon m-alert--air m-alert--warning m--margin-bottom-20" role="alert">
                                                    <div class="m-alert__icon"><i class="la la-exclamation-triangle"></i></div>
                                                    <div class="m-alert__text">
                                                        <strong><?= count($pendentes_geral) ?> mensalidade<?= count($pendentes_geral)!=1?'s':'' ?> pendente<?= count($pendentes_geral)!=1?'s':'' ?></strong>
                                                        — Em aberto:
                                                        <strong class="m--font-danger">
                                                            R$ <?= number_format(array_sum(array_column(array_values($pendentes_geral),'valor_total')),2,',','.') ?>
                                                        </strong>
                                                    </div>
                                                </div>
                                                <?php endif; ?>

                                                <?php if (empty($mensalidades)): ?>
                                                    <p class="m--font-metal m--align-center">Nenhuma mensalidade registrada.</p>
                                                <?php else: ?>
                                                <div class="table-responsive">
                                                    <table class="table table-hover m-table m-table--head-bg-success" style="font-size:13px;">
                                                        <thead>
                                                            <tr>
                                                                <th>Mês/Ano</th>
                                                                <th class="m--align-right">Total</th>
                                                                <th class="m--align-right">Repasse</th>
                                                                <th class="m--align-right">Facção</th>
                                                                <th class="m--align-center">Situação</th>
                                                                <th>Motivo isenção</th>
                                                                <th class="m--align-center">Pagamento</th>
                                                                <th class="m--align-center">Ação</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($mensalidades as $m): ?>
                                                            <tr>
                                                                <td class="m--font-boldest">
                                                                    <?= $meses_pt[$m['mes']] ?>/<?= $m['ano'] ?>
                                                                </td>
                                                                <td class="m--align-right <?= (!$m['isento_efetivo'] && !$m['pago']) ? 'm--font-danger m--font-boldest' : 'm--font-metal' ?>">
                                                                    R$ <?= number_format($m['valor_total'],2,',','.') ?>
                                                                </td>
                                                                <td class="m--align-right m--font-metal">
                                                                    R$ <?= number_format($m['valor_repasse'],2,',','.') ?>
                                                                </td>
                                                                <td class="m--align-right m--font-metal">
                                                                    R$ <?= number_format($m['valor_faccao'],2,',','.') ?>
                                                                </td>
                                                                <td class="m--align-center">
                                                                    <?php if ($m['isento_efetivo']): ?>
                                                                        <span class="m-badge m-badge--metal m-badge--wide">Isento</span>
                                                                    <?php elseif ($m['pago']): ?>
                                                                        <span class="m-badge m-badge--success m-badge--wide">Pago</span>
                                                                    <?php else: ?>
                                                                        <span class="m-badge m-badge--warning m-badge--wide">Pendente</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td style="font-size:11px; max-width:140px;">
                                                                    <?php if ($m['isento_efetivo'] && $m['motivo_isencao']): ?>
                                                                        <span class="m--font-metal" title="<?= htmlspecialchars($m['motivo_isencao']) ?>">
                                                                            <?= htmlspecialchars(mb_strimwidth($m['motivo_isencao'],0,40,'…')) ?>
                                                                        </span>
                                                                        <?php if ($m['isento_por']): ?>
                                                                        <br><em style="font-size:10px; color:#9699a2;">
                                                                            por <?= htmlspecialchars($m['isento_por']) ?>
                                                                        </em>
                                                                        <?php endif; ?>
                                                                    <?php elseif ($m['isento_efetivo'] && $m['patente_no_mes']==6): ?>
                                                                        <span class="m--font-metal">Patente VI</span>
                                                                    <?php else: ?>
                                                                        <span class="m--font-metal">—</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="m--align-center m--font-metal" style="font-size:11px;">
                                                                    <?= $m['data_pagamento'] ? date('d/m/Y', strtotime($m['data_pagamento'])) : '—' ?>
                                                                </td>
                                                                <td class="m--align-center" style="white-space:nowrap;">
                                                                    <?php if (!$m['isento_efetivo']): ?>
                                                                    <a href="financeiro/pagar.php?id=<?= $m['id'] ?>&mes=<?= $m['mes'] ?>&ano=<?= $m['ano'] ?>&origem=list"
                                                                       class="btn btn-xs <?= $m['pago'] ? 'btn-warning' : 'btn-success' ?> m-btn m-btn--pill"
                                                                       style="font-size:11px; padding:3px 8px;"
                                                                       onclick="return confirm('<?= $m['pago'] ? 'Desfazer' : 'Confirmar' ?> pagamento de <?= $meses_pt[$m['mes']].'/'.$m['ano'] ?>?')"
                                                                       title="<?= $m['pago'] ? 'Desfazer pagamento' : 'Marcar como pago' ?>">
                                                                        <i class="la la-<?= $m['pago'] ? 'undo' : 'check' ?>"></i>
                                                                    </a>
                                                                    <?php endif; ?>
                                                                    <?php if (!$m['pago']): ?>
                                                                    <a href="financeiro/isento_form.php?id=<?= $m['id'] ?>&mes=<?= $m['mes'] ?>&ano=<?= $m['ano'] ?>&origem=list"
                                                                       class="btn btn-xs <?= $m['isento_efetivo'] ? 'btn-secondary' : 'btn-info' ?> m-btn m-btn--pill"
                                                                       style="font-size:11px; padding:3px 8px;"
                                                                       title="<?= $m['isento_efetivo'] ? 'Remover isenção' : 'Isentar' ?>">
                                                                        <i class="la la-<?= $m['isento_efetivo'] ? 'times' : 'minus-circle' ?>"></i>
                                                                    </a>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="m--font-boldest">
                                                                <td>Total pago</td>
                                                                <td class="m--align-right m--font-success">
                                                                    R$ <?= number_format($total_pago,2,',','.') ?>
                                                                </td>
                                                                <td class="m--align-right m--font-metal">
                                                                    R$ <?= number_format($total_repasse_pago,2,',','.') ?>
                                                                </td>
                                                                <td colspan="5"></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- TAB FREQUÊNCIA -->
                                            <div class="tab-pane fade" id="tab-frequencia" role="tabpanel">
                                                <div class="row m--margin-bottom-15 text-center">
                                                    <div class="col-4">
                                                        <div class="m--font-boldest m--font-brand" style="font-size:2rem;"><?= (int)$freq_total['total'] ?></div>
                                                        <div class="m--font-metal" style="font-size:12px;">Eventos passados</div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="m--font-boldest m--font-success" style="font-size:2rem;"><?= (int)$freq_total['presentes'] ?></div>
                                                        <div class="m--font-metal" style="font-size:12px;">Presenças</div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="m--font-boldest <?= $pc_freq>=70?'m--font-success':($pc_freq>=50?'m--font-warning':'m--font-danger') ?>" style="font-size:2rem;">
                                                            <?= $pc_freq ?>%
                                                        </div>
                                                        <div class="m--font-metal" style="font-size:12px;">Taxa</div>
                                                    </div>
                                                </div>
                                                <div class="progress m-progress--sm m--margin-bottom-15">
                                                    <div class="progress-bar <?= $pc_freq>=70?'m--bg-success':($pc_freq>=50?'m--bg-warning':'m--bg-danger') ?>"
                                                         style="width:<?= $pc_freq ?>%"></div>
                                                </div>
                                                <div class="m-alert m-alert--icon m-alert--air m--margin-bottom-15"
                                                     style="background:#f8f9fa; border:none; padding:8px 15px;">
                                                    <div class="m-alert__icon"><i class="la la-info-circle m--font-metal"></i></div>
                                                    <div class="m-alert__text" style="font-size:11px; color:#9699a2;">
                                                        A taxa considera apenas eventos com data anterior a hoje.
                                                        Eventos futuros aparecem como "Aguardando".
                                                    </div>
                                                </div>
                                                <?php if (!empty($freq_recente)): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-hover m-table" style="font-size:13px;">
                                                        <thead>
                                                            <tr>
                                                                <th>Data</th>
                                                                <th>Evento</th>
                                                                <th>Tipo</th>
                                                                <th class="m--align-center">Presença</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($freq_recente as $f): ?>
                                                            <tr>
                                                                <td class="m--font-metal" style="white-space:nowrap;">
                                                                    <?= date('d/m/Y', strtotime($f['data_evento'])) ?>
                                                                </td>
                                                                <td><?= htmlspecialchars($f['nome']) ?></td>
                                                                <td class="m--font-metal" style="font-size:11px;">
                                                                    <?= $tipos_evento[$f['tipo']] ?? '—' ?>
                                                                </td>
                                                                <td class="m--align-center">
                                                                    <?php if ($f['data_evento'] >= $hoje): ?>
                                                                        <span class="m-badge m-badge--metal m-badge--wide">Aguardando</span>
                                                                    <?php elseif ($f['presente']): ?>
                                                                        <span class="m-badge m-badge--success m-badge--wide">Presente</span>
                                                                    <?php else: ?>
                                                                        <span class="m-badge m-badge--danger m-badge--wide">Ausente</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <?php else: ?>
                                                    <p class="m--font-metal m--align-center">Nenhum evento registrado.</p>
                                                <?php endif; ?>
                                            </div>

                                            
                                            <!-- TAB DISCIPLINA -->
                                            <div class="tab-pane fade" id="tab-disciplina" role="tabpanel">
                                                <div class="d-flex justify-content-between align-items-center m--margin-bottom-15">
                                                    <div>
                                                        <?php if (!empty($disc_ativas)): ?>
                                                            <span class="m-badge m-badge--danger m-badge--wide" style="font-size:12px;">
                                                                <i class="la la-gavel"></i>
                                                                <?= count($disc_ativas) ?> suspensão<?= count($disc_ativas)!=1?'ões':'' ?> ativa<?= count($disc_ativas)!=1?'s':'' ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="m--font-metal" style="font-size:12px;">
                                                                Nenhuma suspensão ativa.
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <a href="disciplina/form.php?integrante_id=<?= $id ?>"
                                                       class="btn btn-sm btn-outline-danger m-btn m-btn--icon m-btn--pill">
                                                        <span><i class="la la-gavel"></i><span>Nova Suspensão</span></span>
                                                    </a>
                                                </div>
                                                <?php if (empty($disciplinas)): ?>
                                                    <p class="m--font-metal m--align-center">Nenhuma medida disciplinar registrada.</p>
                                                <?php else: ?>
                                                <div class="table-responsive">
                                                    <table class="table table-hover m-table m-table--head-bg-danger" style="font-size:13px;">
                                                        <thead>
                                                            <tr>
                                                                <th class="m--align-center">Dur.</th>
                                                                <th class="m--align-center">Início</th>
                                                                <th class="m--align-center">Fim</th>
                                                                <th>Motivo</th>
                                                                <th>Aplicado por</th>
                                                                <th class="m--align-center">Situação</th>
                                                                <th class="m--align-center">Ação</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($disciplinas as $disc):
                                                                $dr = (int)$disc['dias_restantes'];
                                                            ?>
                                                            <tr>
                                                                <td class="m--align-center">
                                                                    <span class="m-badge <?= $disc['ativo'] ? 'm-badge--danger' : 'm-badge--metal' ?> m-badge--wide">
                                                                        <?= $disc['duracao_dias'] ?>d
                                                                    </span>
                                                                </td>
                                                                <td class="m--align-center m--font-metal">
                                                                    <?= date('d/m/Y', strtotime($disc['data_inicio'])) ?>
                                                                </td>
                                                                <td class="m--align-center m--font-metal">
                                                                    <?= date('d/m/Y', strtotime($disc['data_fim'])) ?>
                                                                </td>
                                                                <td style="max-width:160px; font-size:12px;">
                                                                    <?= htmlspecialchars($disc['motivo']) ?>
                                                                </td>
                                                                <td class="m--font-metal" style="font-size:12px;">
                                                                    <?= htmlspecialchars($disc['aplicado_por']) ?>
                                                                </td>
                                                                <td class="m--align-center">
                                                                    <?php if ($disc['ativo']): ?>
                                                                        <?php
                                                                        $bc = $dr <= 7 ? 'dias-critico' : ($dr <= 20 ? 'dias-alerta' : 'dias-ok');
                                                                        $bl = $dr >= 0
                                                                            ? $dr . 'd restante' . ($dr!=1?'s':'')
                                                                            : 'Vencida há '.abs($dr).'d';
                                                                        ?>
                                                                        <span class="dias-badge <?= $bc ?>"><?= $bl ?></span>
                                                                        <div class="progress m-progress--sm m--margin-top-5">
                                                                            <?php $pcd = min(100, round((($disc['duracao_dias'] - max($dr,0)) / $disc['duracao_dias']) * 100)); ?>
                                                                            <div class="progress-bar m--bg-danger" style="width:<?= $pcd ?>%"></div>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <span class="m-badge m-badge--metal m-badge--wide">Encerrada</span>
                                                                        <?php if ($disc['encerrado_em']): ?>
                                                                        <br><small class="m--font-metal" style="font-size:10px;">
                                                                            <?= date('d/m/Y', strtotime($disc['encerrado_em'])) ?>
                                                                            <?= $disc['encerrado_por'] ? ' por '.htmlspecialchars($disc['encerrado_por']) : '' ?>
                                                                        </small>
                                                                        <?php endif; ?>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="m--align-center">
                                                                    <?php if ($disc['ativo']): ?>
                                                                    <a href="disciplina/disciplina_view.php?id=<?= $disc['id'] ?>&origem=integrante"
                                                                       class="btn btn-sm btn-info m-btn m-btn--icon m-btn--pill m--margin-right-5"
                                                                       title="Ver detalhes">
                                                                        <span><i class="la la-eye"></i></span>
                                                                    </a>
                                                                    <a href="disciplina/encerrar.php?id=<?= $disc['id'] ?>"
                                                                       class="btn btn-sm btn-success m-btn m-btn--icon m-btn--pill"
                                                                       onclick="return confirm('Encerrar suspensão e reativar <?= htmlspecialchars(addslashes($integrante['apelido'])) ?>?')"
                                                                       title="Fim de suspensão">
                                                                        <span><i class="la la-check-circle"></i><span>Fim</span></span>
                                                                    </a>
                                                                    <?php else: ?>
                                                                        <span class="m--font-metal">—</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <?php endif; ?>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /COL DIREITA -->

                        </div>
                    </div>

                </div>
            </div>
        </div>

        <?php require_once('inc/footer.php'); ?>
    </div>

    <div class="m-scroll-top m-scroll-top--skin-top" data-toggle="m-scroll-top"
         data-scroll-offset="500" data-scroll-speed="300">
        <i class="la la-arrow-up"></i>
    </div>
    <script src="js/vendors.bundle.js" type="text/javascript"></script>
    <script src="js/scripts.bundle.js" type="text/javascript"></script>
</body>
</html>