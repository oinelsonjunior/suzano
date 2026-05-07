<?php
session_start();
require_once 'inc/auth.php';
require_once '../includes/connection.php';

$email_sessao = $_SESSION['membro_login'];

// Buscar dados do usuário + integrante vinculado
$stmt = $conn->prepare("
    SELECT u.id, u.username, u.email, u.id_cadastro,
           ci.nome, ci.apelido, ci.patente, ci.status,
           ci.data_apresentacao, ci.celular, ci.foto
    FROM users u
    JOIN cadastro_integrante ci ON ci.id = u.id_cadastro
    WHERE u.email = ? AND u.role = 'membro' AND u.enabled = 1
    LIMIT 1
");
$stmt->bind_param("s", $email_sessao);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Sessão inválida ou usuário desativado
if (!$user) {
    session_unset();
    session_destroy();
    header("Location: ../");
    exit;
}

$id_cadastro = (int)$user['id_cadastro'];
$ano_atual   = (int)date('Y');
$mes_atual   = (int)date('m');
$hoje        = date('Y-m-d');

$meses_pt = [
    1=>'Janeiro', 2=>'Fevereiro', 3=>'Março',    4=>'Abril',
    5=>'Maio',    6=>'Junho',     7=>'Julho',     8=>'Agosto',
    9=>'Setembro',10=>'Outubro', 11=>'Novembro', 12=>'Dezembro'
];
$patentes      = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X'];
$status_labels = [1=>'Ativo',2=>'Afastado',3=>'Desligado',4=>'Suspenso'];
$status_badge  = [1=>'m-badge--success',2=>'m-badge--warning',3=>'m-badge--danger',4=>'m-badge--metal'];
$tipos_evento  = [
    1=>'Sede Estadual (padrão)', 2=>'Sede Estadual (reunião geral)',
    3=>'Sede Suzano (reunião)',  4=>'Sede Suzano (confraternização)',
    5=>'Evento Fora',            6=>'Evento Obrigatório',
];
$tipo_badge = [
    1=>'m-badge--metal', 2=>'m-badge--brand',   3=>'m-badge--info',
    4=>'m-badge--success',5=>'m-badge--warning', 6=>'m-badge--danger',
];

// Mensalidades (últimos 12 meses)
$stmt2 = $conn->prepare("
    SELECT mes, ano, pago, isento, valor_total, data_pagamento, patente_no_mes,
           (isento = 1 OR patente_no_mes = 6) AS isento_efetivo
    FROM mensalidades
    WHERE integrante_id = ?
    ORDER BY ano DESC, mes DESC
    LIMIT 12
");
$stmt2->bind_param("i", $id_cadastro);
$stmt2->execute();
$mensalidades = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

$total_pagas    = count(array_filter($mensalidades, fn($m) => $m['pago'] == 1));
$total_pendentes = count(array_filter($mensalidades, fn($m) => $m['pago'] == 0 && !$m['isento_efetivo']));

// Frequência — histórico completo
$stmt3 = $conn->prepare("
    SELECT f.presente, e.nome, e.data_evento, e.tipo
    FROM frequencias f
    JOIN eventos e ON e.id = f.evento_id
    WHERE f.integrante_id = ?
    ORDER BY e.data_evento DESC
    LIMIT 20
");
$stmt3->bind_param("i", $id_cadastro);
$stmt3->execute();
$freq_historico = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);

// Totais de frequência
$stmt4 = $conn->prepare("
    SELECT COUNT(*) as total, SUM(presente = 1) as presentes
    FROM frequencias
    WHERE integrante_id = ?
");
$stmt4->bind_param("i", $id_cadastro);
$stmt4->execute();
$freq_total = $stmt4->get_result()->fetch_assoc();
$pc_freq = ($freq_total['total'] > 0)
    ? round(($freq_total['presentes'] / $freq_total['total']) * 100)
    : 0;

// Próximos eventos
$stmt5 = $conn->prepare("
    SELECT id, tipo, nome, data_evento
    FROM eventos
    WHERE faccao = 1 AND data_evento >= ?
    ORDER BY data_evento ASC
    LIMIT 4
");
$stmt5->bind_param("s", $hoje);
$stmt5->execute();
$proximos = $stmt5->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8"/>
    <title>Abutre's MC | <?= htmlspecialchars($user['apelido']) ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
    <script>
        WebFont.load({
            google: {"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},
            active: function() { sessionStorage.fonts = true; }
        });
    </script>
    <link href="../css/style.bundle.css" rel="stylesheet" type="text/css"/>
    <style>
        /* Logo: replicar o comportamento da área admin */
        .m-brand {
            width: 235px;
            background: #2c2d3a;
        }
        .m-brand .m-brand__logo-wrapper img {
            max-height: 50px;
            width: auto;
            display: block;
        }
        /* Header fixo: garantir altura correta */
        .m-header .m-header__top {
            background: #2c2d3a;
        }
        @media (min-width: 993px) {
            .m-header .m-header__top {
                height: 90px;
            }
        }
        /* Nome do usuário na topbar */
        .m-topbar__welcome,
        .m-topbar__username {
            color: #9699a2;
        }
    </style>
</head>
<body class="m-page--wide m-header--fixed m-header--fixed-mobile m-footer--push m-aside--offcanvas-default">
<div class="m-grid m-grid--hor m-grid--root m-page">

    <!-- HEADER -->
    <header class="m-grid__item m-header" data-minimize="minimize"
            data-minimize-offset="200" data-minimize-mobile-offset="200">
        <div class="m-header__top">
            <div class="m-container m-container--responsive m-container--xxl m-container--full-height m-page__container">
                <div class="m-stack m-stack--ver m-stack--desktop">

                    <!-- Logo -->
                    <div class="m-stack__item m-brand">
                        <div class="m-stack m-stack--ver m-stack--general m-stack--inline">
                            <div class="m-stack__item m-stack__item--middle m-brand__logo">
                                <a href="index.php" class="m-brand__logo-wrapper">
                                    <img alt="Abutre's MC" src="../images/logo.png"/>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Topbar -->
                    <div class="m-stack__item m-stack__item--fluid m-header-head">
                        <div class="m-topbar m-stack m-stack--ver m-stack--general">
                            <div class="m-stack__item m-topbar__nav-wrapper">
                                <ul class="m-topbar__nav m-nav m-nav--inline">
                                    <li class="m-nav__item m-topbar__user-profile m-dropdown m-dropdown--medium
                                                m-dropdown--arrow m-dropdown--header-bg-fill m-dropdown--align-right
                                                m-dropdown--mobile-full-width m-dropdown--skin-light"
                                        data-dropdown-toggle="click">
                                        <a href="#" class="m-nav__link m-dropdown__toggle" style="display:table;">
                                            <span class="m-topbar__welcome" style="display:table-cell;vertical-align:middle;">Olá,&nbsp;</span>
                                            <span class="m-topbar__username" style="display:table-cell;vertical-align:middle;"><?= htmlspecialchars($user['apelido']) ?></span>
                                        </a>
                                        <div class="m-dropdown__wrapper">
                                            <span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
                                            <div class="m-dropdown__inner">
                                                <div class="m-dropdown__header m--align-center" style="background:#000;">
                                                    <div class="m-card-user m-card-user--skin-dark">
                                                        <div class="m-card-user__details">
                                                            <span class="m-card-user__name m--font-weight-500">
                                                                <?= htmlspecialchars($user['apelido']) ?>
                                                            </span>
                                                            <br>
                                                            <span class="m--font-metal" style="font-size:12px;">
                                                                <?= htmlspecialchars($user['email']) ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="m-dropdown__body">
                                                    <div class="m-dropdown__content">
                                                        <ul class="m-nav m-nav--skin-light">
                                                            <li class="m-nav__separator m-nav__separator--fit"></li>
                                                            <li class="m-nav__item" style="padding:10px 20px;">
                                                                <a href="../includes/logout"
                                                                   class="btn m-btn--pill btn-secondary m-btn m-btn--custom m-btn--label-brand m-btn--bolder btn-block">
                                                                    <i class="la la-sign-out"></i> Sair
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </header>
    <!-- /HEADER -->

    <!-- BODY -->
    <div class="m-grid__item m-grid__item--fluid m-grid m-grid--hor-desktop m-grid--desktop m-body">
        <div class="m-grid__item m-grid__item--fluid m-grid m-grid--ver m-container
                    m-container--responsive m-container--xxl m-page__container">
            <div class="m-grid__item m-grid__item--fluid m-wrapper">

                <!-- Subheader -->
                <div class="m-subheader">
                    <div class="d-flex align-items-center">
                        <div class="mr-auto">
                            <h3 class="m-subheader__title" style="text-transform:uppercase;">
                                Meu Histórico
                            </h3>
                            <span class="m--font-metal" style="font-size:13px;">
                                <?= $meses_pt[$mes_atual] ?> <?= $ano_atual ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="m-content">

                    <!-- ── Card de identidade ── -->
                    <div class="m-portlet m-portlet--mobile">
                        <div class="m-portlet__body">
                            <div class="row align-items-center">

                                <div class="col-md-1 m--align-center m--margin-bottom-10">
                                    <?php
                                    $foto_m   = $user['foto'] ?? null;
                                    $foto_m_ok = $foto_m && file_exists('../' . $foto_m);
                                    ?>
                                    <div style="width:60px;height:60px;border-radius:50%;background:#716aca;
                                                display:flex;align-items:center;justify-content:center;
                                                margin:0 auto;overflow:hidden;border:2px solid #ebedf2;">
                                        <?php if ($foto_m_ok): ?>
                                            <img src="../<?= htmlspecialchars($foto_m) ?>"
                                                 style="width:100%;height:100%;object-fit:cover;">
                                        <?php else: ?>
                                            <i class="flaticon-user" style="font-size:2rem;color:#fff;"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-7">
                                    <h3 class="m--font-boldest m--margin-bottom-5">
                                        <?= htmlspecialchars($user['apelido']) ?>
                                    </h3>
                                    <span class="m--font-metal" style="font-size:13px;">
                                        <?= htmlspecialchars($user['nome']) ?>
                                    </span>
                                    &nbsp;
                                    <span class="m-badge m-badge--info m-badge--wide">
                                        Pat. <?= $patentes[$user['patente']] ?? '—' ?>
                                    </span>
                                    <span class="m-badge <?= $status_badge[$user['status']] ?> m-badge--wide">
                                        <?= $status_labels[$user['status']] ?>
                                    </span>
                                    <?php if (!empty($user['data_apresentacao']) && $user['data_apresentacao'] !== '0000-00-00 00:00:00'): ?>
                                    <br>
                                    <small class="m--font-metal">
                                        Membro desde <?= date('d/m/Y', strtotime($user['data_apresentacao'])) ?>
                                    </small>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-4 m--align-right">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="m--font-boldest m--font-success" style="font-size:1.8rem;">
                                                <?= $total_pagas ?>
                                            </div>
                                            <div class="m--font-metal" style="font-size:11px;">Mens. pagas</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="m--font-boldest <?= $total_pendentes > 0 ? 'm--font-warning' : 'm--font-success' ?>"
                                                 style="font-size:1.8rem;">
                                                <?= $total_pendentes ?>
                                            </div>
                                            <div class="m--font-metal" style="font-size:11px;">Pendentes</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="m--font-boldest <?= $pc_freq >= 70 ? 'm--font-success' : ($pc_freq >= 50 ? 'm--font-warning' : 'm--font-danger') ?>"
                                                 style="font-size:1.8rem;">
                                                <?= $pc_freq ?>%
                                            </div>
                                            <div class="m--font-metal" style="font-size:11px;">Frequência</div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- ── Mensalidades + Frequência ── -->
                    <div class="row">

                        <!-- Mensalidades -->
                        <div class="col-xl-6">
                            <div class="m-portlet m-portlet--full-height">
                                <div class="m-portlet__head">
                                    <div class="m-portlet__head-caption">
                                        <div class="m-portlet__head-title">
                                            <h3 class="m-portlet__head-text">
                                                <i class="la la-money m--font-success"></i>
                                                Mensalidades
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="m-portlet__body">
                                    <?php if (empty($mensalidades)): ?>
                                        <p class="m--font-metal m--align-center m--margin-top-20">
                                            Nenhuma mensalidade registrada.
                                        </p>
                                    <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover m-table m-table--head-bg-success">
                                            <thead>
                                                <tr>
                                                    <th>Mês/Ano</th>
                                                    <th class="m--align-right">Valor</th>
                                                    <th class="m--align-center">Situação</th>
                                                    <th class="m--align-center">Pagamento</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($mensalidades as $m): ?>
                                                <tr>
                                                    <td class="m--font-boldest">
                                                        <?= $meses_pt[$m['mes']] ?>/<?= $m['ano'] ?>
                                                    </td>
                                                    <td class="m--align-right m--font-metal">
                                                        R$ <?= number_format($m['valor_total'], 2, ',', '.') ?>
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
                                                    <td class="m--align-center m--font-metal" style="font-size:12px;">
                                                        <?= $m['data_pagamento']
                                                            ? date('d/m/Y', strtotime($m['data_pagamento']))
                                                            : '—' ?>
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

                        <!-- Frequência -->
                        <div class="col-xl-6">
                            <div class="m-portlet m-portlet--full-height">
                                <div class="m-portlet__head">
                                    <div class="m-portlet__head-caption">
                                        <div class="m-portlet__head-title">
                                            <h3 class="m-portlet__head-text">
                                                <i class="la la-calendar-check-o m--font-brand"></i>
                                                Frequência
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="m-portlet__body">

                                    <!-- Totais -->
                                    <div class="row m--margin-bottom-15">
                                        <div class="col-4 text-center">
                                            <div class="m--font-boldest m--font-info" style="font-size:1.8rem;">
                                                <?= (int)$freq_total['total'] ?>
                                            </div>
                                            <div class="m--font-metal" style="font-size:11px;">Eventos</div>
                                        </div>
                                        <div class="col-4 text-center">
                                            <div class="m--font-boldest m--font-success" style="font-size:1.8rem;">
                                                <?= (int)$freq_total['presentes'] ?>
                                            </div>
                                            <div class="m--font-metal" style="font-size:11px;">Presenças</div>
                                        </div>
                                        <div class="col-4 text-center">
                                            <div class="m--font-boldest <?= $pc_freq >= 70 ? 'm--font-success' : ($pc_freq >= 50 ? 'm--font-warning' : 'm--font-danger') ?>"
                                                 style="font-size:1.8rem;">
                                                <?= $pc_freq ?>%
                                            </div>
                                            <div class="m--font-metal" style="font-size:11px;">Taxa</div>
                                        </div>
                                    </div>
                                    <div class="progress m-progress--sm m--margin-bottom-20">
                                        <div class="progress-bar <?= $pc_freq >= 70 ? 'm--bg-success' : ($pc_freq >= 50 ? 'm--bg-warning' : 'm--bg-danger') ?>"
                                             style="width:<?= $pc_freq ?>%"></div>
                                    </div>

                                    <?php if (!empty($freq_historico)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover m-table" style="font-size:13px;">
                                            <thead>
                                                <tr>
                                                    <th>Data</th>
                                                    <th>Evento</th>
                                                    <th class="m--align-center">Presença</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($freq_historico as $f): ?>
                                                <tr>
                                                    <td class="m--font-metal" style="white-space:nowrap;">
                                                        <?= date('d/m/Y', strtotime($f['data_evento'])) ?>
                                                    </td>
                                                    <td>
                                                        <span><?= htmlspecialchars($f['nome']) ?></span>
                                                        <br>
                                                        <span class="m-badge <?= $tipo_badge[$f['tipo']] ?> m-badge--wide" style="font-size:10px;">
                                                            <?= $tipos_evento[$f['tipo']] ?>
                                                        </span>
                                                    </td>
                                                    <td class="m--align-center">
                                                        <?php if ($f['presente']): ?>
                                                            <span class="m-badge m-badge--success m-badge--wide">
                                                                <i class="la la-check"></i> Presente
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="m-badge m-badge--danger m-badge--wide">
                                                                <i class="la la-times"></i> Ausente
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php else: ?>
                                        <p class="m--font-metal m--align-center">Nenhum evento registrado ainda.</p>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- /row mensalidades + frequência -->

                    <!-- ── Próximos eventos ── -->
                    <?php if (!empty($proximos)): ?>
                    <div class="m-portlet m-portlet--mobile">
                        <div class="m-portlet__head">
                            <div class="m-portlet__head-caption">
                                <div class="m-portlet__head-title">
                                    <h3 class="m-portlet__head-text">
                                        <i class="la la-calendar m--font-brand"></i>
                                        Próximos Eventos
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="m-portlet__body">
                            <div class="row">
                                <?php foreach ($proximos as $pev):
                                    $pdata    = new DateTime($pev['data_evento']);
                                    $diff     = (new DateTime($hoje))->diff($pdata);
                                    $dias     = (int)$diff->days;
                                    $mes_abr  = strtoupper(substr($meses_pt[(int)$pdata->format('m')], 0, 3));
                                ?>
                                <div class="col-md-3 col-sm-6 m--margin-bottom-10">
                                    <div style="background:#f8f9fa; border-radius:8px; padding:16px; text-align:center;
                                                border:1px solid #ebedf2;">
                                        <div style="font-size:2rem; font-weight:700; color:#575962; line-height:1;">
                                            <?= $pdata->format('d') ?>
                                        </div>
                                        <div style="font-size:11px; color:#9699a2; text-transform:uppercase; margin-bottom:8px;">
                                            <?= $mes_abr ?>/<?= $pdata->format('Y') ?>
                                        </div>
                                        <span class="m-badge <?= $tipo_badge[$pev['tipo']] ?> m-badge--wide" style="font-size:10px;">
                                            <?= $tipos_evento[$pev['tipo']] ?>
                                        </span>
                                        <div style="font-size:12px; font-weight:600; color:#575962; margin-top:8px;">
                                            <?= htmlspecialchars($pev['nome']) ?>
                                        </div>
                                        <div style="font-size:11px; margin-top:4px;">
                                            <?php if ($dias === 0): ?>
                                                <span class="m--font-danger m--font-boldest">Hoje!</span>
                                            <?php elseif ($dias === 1): ?>
                                                <span class="m--font-warning">Amanhã</span>
                                            <?php else: ?>
                                                <span class="m--font-metal">em <?= $dias ?> dias</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
                <!-- /m-content -->

            </div>
        </div>
    </div>
    <!-- /BODY -->

    <!-- FOOTER -->
    <footer class="m-grid__item m-footer">
        <div class="m-container m-container--responsive m-container--xxl m-container--full-height m-page__container">
            <div class="m-footer__wrapper">
                <div class="m-stack m-stack--flex-tablet-and-mobile m-stack--ver m-stack--desktop">
                    <div class="m-stack__item m-stack__item--fluid m-footer__left">
                        <span class="m-footer__copyright" style="font-size:12px; color:#9699a2;">
                            <?= date('Y') ?> &copy; Abutre's MC — Sistema de Gestão
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</div>

<script src="../admin/js/vendors.bundle.js"></script>
<script src="../admin/js/scripts.bundle.js"></script>
</body>
</html>