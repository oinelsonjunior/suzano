<?php
session_start();
require_once('../inc/general.php');

$hoje = date('Y-m-d');

// Suspensões ativas
$ativas = $conn->query("
    SELECT d.*, ci.apelido, ci.nome, ci.patente, ci.status,
           DATEDIFF(d.data_fim, '$hoje') AS dias_restantes
    FROM disciplina d
    JOIN cadastro_integrante ci ON ci.id = d.integrante_id
    WHERE d.ativo = 1
    ORDER BY d.data_fim ASC
")->fetch_all(MYSQLI_ASSOC);

// Histórico (encerradas)
$historico = $conn->query("
    SELECT d.*, ci.apelido, ci.nome,
           DATEDIFF(d.data_fim, d.data_inicio) + 1 AS dias_total
    FROM disciplina d
    JOIN cadastro_integrante ci ON ci.id = d.integrante_id
    WHERE d.ativo = 0
    ORDER BY d.created_at DESC
    LIMIT 30
")->fetch_all(MYSQLI_ASSOC);

$patentes = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',
             6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8"/>
    <base href="../"><title>Abutre's MC | Disciplina</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
    <script>WebFont.load({google:{"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},active:function(){sessionStorage.fonts=true;}});</script>
    <link href="css/style.bundle.css" rel="stylesheet" type="text/css"/>
    <style>
        .dias-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .dias-critico  { background:#fff0f3; color:#f4516c; border:1px solid #f4516c; }
        .dias-alerta   { background:#fff8ee; color:#ffb822; border:1px solid #ffb822; }
        .dias-ok       { background:#f0faf7; color:#34bfa3; border:1px solid #34bfa3; }
        .dias-vencido  { background:#f4516c; color:#fff; }
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
                            <h3 class="m-subheader__title" style="text-transform:uppercase;">Disciplina</h3>
                            <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                                <li class="m-nav__item m-nav__item--home">
                                    <a href="../index" class="m-nav__link m-nav__link--icon">
                                        <i class="m-nav__link-icon la la-home"></i>
                                    </a>
                                </li>
                                <li class="m-nav__separator">—</li>
                                <li class="m-nav__item"><span class="m-nav__link-text">Disciplina</span></li>
                            </ul>
                        </div>
                        <div>
                            <a href="disciplina/disciplina_form.php" class="btn btn-sm btn-danger m-btn m-btn--icon m-btn--pill">
                                <span><i class="la la-gavel"></i><span>Nova Suspensão</span></span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="m-content">

                    <?php if (isset($_GET['ok'])): ?>
                    <div class="alert alert-success m-alert--air">
                        <i class="la la-check-circle"></i>&nbsp;
                        <?= ['criada'    =>'Suspensão registrada. Status do integrante atualizado para Suspenso.',
                             'encerrada'  =>'Suspensão encerrada. Integrante reativado com sucesso.',
                             'atualizada' =>'Suspensão atualizada com sucesso.'][$_GET['ok']] ?? 'OK.' ?>
                    </div>
                    <?php endif; ?>

                    <!-- KPIs -->
                    <div class="m-portlet m-portlet--mobile">
                        <div class="m-portlet__body m-portlet__body--no-padding">
                            <div class="row m-row--no-padding m-row--col-separator-xl">
                                <div class="col-md-6 col-lg-4 col-xl-4">
                                    <div class="m-widget24"><div class="m-widget24__item">
                                        <h4 class="m-widget24__title">Suspensões Ativas</h4><br>
                                        <span class="m-widget24__desc">Em curso agora</span>
                                        <span class="m-widget24__stats m--font-danger"><?= count($ativas) ?></span>
                                        <div class="m--space-10"></div>
                                        <div class="progress m-progress--sm"><div class="progress-bar m--bg-danger" style="width:100%"></div></div>
                                        <span class="m-widget24__change">Integrantes suspensos</span>
                                        <span class="m-widget24__number"><?= count($ativas) ?></span>
                                    </div></div>
                                </div>
                                <div class="col-md-6 col-lg-4 col-xl-4">
                                    <?php
                                    $vencendo = count(array_filter($ativas, fn($a) => $a['dias_restantes'] <= 7 && $a['dias_restantes'] >= 0));
                                    ?>
                                    <div class="m-widget24"><div class="m-widget24__item">
                                        <h4 class="m-widget24__title">Vencendo em breve</h4><br>
                                        <span class="m-widget24__desc">Próximos 7 dias</span>
                                        <span class="m-widget24__stats m--font-warning"><?= $vencendo ?></span>
                                        <div class="m--space-10"></div>
                                        <div class="progress m-progress--sm"><div class="progress-bar m--bg-warning" style="width:100%"></div></div>
                                        <span class="m-widget24__change">Aguardam encerramento</span>
                                        <span class="m-widget24__number"><?= $vencendo ?></span>
                                    </div></div>
                                </div>
                                <div class="col-md-6 col-lg-4 col-xl-4">
                                    <div class="m-widget24"><div class="m-widget24__item">
                                        <h4 class="m-widget24__title">Histórico</h4><br>
                                        <span class="m-widget24__desc">Suspensões encerradas</span>
                                        <span class="m-widget24__stats m--font-metal"><?= count($historico) ?></span>
                                        <div class="m--space-10"></div>
                                        <div class="progress m-progress--sm"><div class="progress-bar m--bg-metal" style="width:100%"></div></div>
                                        <span class="m-widget24__change">Últimos 30 registros</span>
                                        <span class="m-widget24__number">—</span>
                                    </div></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Suspensões ativas -->
                    <div class="m-portlet m-portlet--mobile">
                        <div class="m-portlet__head">
                            <div class="m-portlet__head-caption">
                                <div class="m-portlet__head-title">
                                    <h3 class="m-portlet__head-text">
                                        <i class="la la-gavel m--font-danger"></i>
                                        Suspensões Ativas
                                    </h3>
                                </div>
                            </div>
                            <div class="m-portlet__head-tools">
                                <a href="disciplina/disciplina_form.php" class="btn btn-sm btn-outline-danger m-btn m-btn--pill">
                                    <i class="la la-plus"></i> Nova
                                </a>
                            </div>
                        </div>
                        <div class="m-portlet__body">
                            <?php if (empty($ativas)): ?>
                                <div class="m--align-center" style="padding:40px 0;">
                                    <i class="la la-check-circle m--font-success" style="font-size:3rem;"></i>
                                    <h3 class="m--font-success m--margin-top-10">Nenhum integrante suspenso.</h3>
                                </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover m-table m-table--head-bg-danger">
                                    <thead>
                                        <tr>
                                            <th>Integrante</th>
                                            <th>Motivo</th>
                                            <th class="m--align-center">Duração</th>
                                            <th class="m--align-center">Início</th>
                                            <th class="m--align-center">Ações</th>
                                            <th class="m--align-center">Dias restantes</th>
                                            <th>Aplicado por</th>
                                            <th class="m--align-center">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ativas as $s):
                                            $dr = (int)$s['dias_restantes'];
                                            if ($dr < 0)       { $badge_cls = 'dias-vencido'; $badge_txt = 'Vencida há ' . abs($dr) . 'd'; }
                                            elseif ($dr <= 7)  { $badge_cls = 'dias-critico'; $badge_txt = $dr . ' dia' . ($dr!=1?'s':''); }
                                            elseif ($dr <= 20) { $badge_cls = 'dias-alerta';  $badge_txt = $dr . ' dias'; }
                                            else               { $badge_cls = 'dias-ok';       $badge_txt = $dr . ' dias'; }

                                            $pc_decorrido = 0;
                                            $total_dias = (int)$s['duracao_dias'];
                                            if ($total_dias > 0) {
                                                $decorridos = $total_dias - max($dr, 0);
                                                $pc_decorrido = round(($decorridos / $total_dias) * 100);
                                            }
                                        ?>
                                        <tr>
                                            <td>
                                                <a href="../admin/integrante_view?id=<?= $s['integrante_id'] ?>" style="text-decoration:none;">
                                                    <span class="m--font-boldest"><?= htmlspecialchars($s['apelido']) ?></span>
                                                </a>
                                                <br>
                                                <small class="m--font-metal"><?= htmlspecialchars($s['nome']) ?></small>
                                            </td>
                                            <td style="max-width:200px;">
                                                <span title="<?= htmlspecialchars($s['motivo']) ?>">
                                                    <?= htmlspecialchars(mb_strimwidth($s['motivo'], 0, 60, '…')) ?>
                                                </span>
                                            </td>
                                            <td class="m--align-center">
                                                <span class="m-badge m-badge--danger m-badge--wide">
                                                    <?= $s['duracao_dias'] ?> dias
                                                </span>
                                            </td>
                                            <td class="m--align-center m--font-metal">
                                                <?= date('d/m/Y', strtotime($s['data_inicio'])) ?>
                                            </td>
                                            <td class="m--align-center m--font-metal">
                                                <?= date('d/m/Y', strtotime($s['data_fim'])) ?>
                                            </td>
                                            <td class="m--align-center">
                                                <span class="dias-badge <?= $badge_cls ?>"><?= $badge_txt ?></span>
                                                <div class="progress m-progress--sm m--margin-top-5" style="max-width:80px;margin:4px auto 0;">
                                                    <div class="progress-bar m--bg-danger" style="width:<?= $pc_decorrido ?>%"></div>
                                                </div>
                                            </td>
                                            <td class="m--font-metal" style="font-size:13px;">
                                                <?= htmlspecialchars($s['aplicado_por']) ?>
                                            </td>
                                            <td class="m--align-center" style="white-space:nowrap;">
                                                <a href="disciplina/disciplina_view.php?id=<?= $s['id'] ?>"
                                                   class="btn btn-sm btn-info m-btn m-btn--icon m-btn--pill"
                                                   title="Visualizar">
                                                    <span><i class="la la-eye"></i></span>
                                                </a>
                                                <a href="disciplina/disciplina_form.php?id=<?= $s['id'] ?>"
                                                   class="btn btn-sm btn-warning m-btn m-btn--icon m-btn--pill"
                                                   title="Editar suspensão">
                                                    <span><i class="la la-pencil"></i></span>
                                                </a>
                                                <a href="disciplina/disciplina_encerrar.php?id=<?= $s['id'] ?>"
                                                   class="btn btn-sm btn-success m-btn m-btn--icon m-btn--pill"
                                                   onclick="return confirm('Encerrar suspensão de <?= htmlspecialchars(addslashes($s['apelido'])) ?> e reativar o integrante?')"
                                                   title="Fim de suspensão">
                                                    <span><i class="la la-check-circle"></i><span>Fim</span></span>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Histórico -->
                    <?php if (!empty($historico)): ?>
                    <div class="m-portlet m-portlet--mobile">
                        <div class="m-portlet__head">
                            <div class="m-portlet__head-caption">
                                <div class="m-portlet__head-title">
                                    <h3 class="m-portlet__head-text">
                                        <i class="la la-history m--font-metal"></i>
                                        Histórico de Suspensões
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="m-portlet__body">
                            <div class="table-responsive">
                                <table class="table table-hover m-table m-table--head-bg-metal">
                                    <thead>
                                        <tr>
                                            <th>Integrante</th>
                                            <th>Motivo</th>
                                            <th class="m--align-center">Duração</th>
                                            <th class="m--align-center">Período</th>
                                            <th>Aplicado por</th>
                                            <th>Encerrado por</th>
                                            <th class="m--align-center">Encerrado em</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historico as $h): ?>
                                        <tr>
                                            <td>
                                                <a href="../integrante_view?id=<?= $h['integrante_id'] ?>" style="text-decoration:none;">
                                                    <span class="m--font-boldest"><?= htmlspecialchars($h['apelido']) ?></span>
                                                </a>
                                                <br>
                                                <small class="m--font-metal"><?= htmlspecialchars($h['nome']) ?></small>
                                            </td>
                                            <td style="max-width:200px; font-size:13px;" class="m--font-metal">
                                                <?= htmlspecialchars(mb_strimwidth($h['motivo'], 0, 60, '…')) ?>
                                            </td>
                                            <td class="m--align-center">
                                                <span class="m-badge m-badge--metal m-badge--wide">
                                                    <?= $h['duracao_dias'] ?> dias
                                                </span>
                                            </td>
                                            <td class="m--align-center m--font-metal" style="font-size:12px;">
                                                <?= date('d/m/Y', strtotime($h['data_inicio'])) ?>
                                                →
                                                <?= date('d/m/Y', strtotime($h['data_fim'])) ?>
                                            </td>
                                            <td class="m--font-metal" style="font-size:13px;">
                                                <?= htmlspecialchars($h['aplicado_por']) ?>
                                            </td>
                                            <td class="m--font-metal" style="font-size:13px;">
                                                <?= htmlspecialchars($h['encerrado_por'] ?? '—') ?>
                                            </td>
                                            <td class="m--align-center m--font-metal" style="font-size:12px;">
                                                <?= $h['encerrado_em'] ? date('d/m/Y', strtotime($h['encerrado_em'])) : '—' ?>
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
            </div>
        </div>
    </div>

    <?php require_once('../inc/footer.php'); ?>
</div>
<div class="m-scroll-top m-scroll-top--skin-top" data-toggle="m-scroll-top" data-scroll-offset="500" data-scroll-speed="300">
    <i class="la la-arrow-up"></i>
</div>
<script src="js/vendors.bundle.js"></script>
<script src="js/scripts.bundle.js"></script>
</body>
</html>