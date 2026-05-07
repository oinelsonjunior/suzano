<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('../inc/general.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header("Location: ../disciplina/"); exit; }

// Buscar suspensão com dados do integrante
$stmt = $conn->prepare("
    SELECT d.*,
           ci.apelido, ci.nome, ci.patente, ci.status AS status_integrante, ci.foto,
           DATEDIFF(d.data_fim, CURDATE()) AS dias_restantes,
           DATEDIFF(d.data_fim, d.data_inicio) + 1 AS duracao_real
    FROM disciplina d
    JOIN cadastro_integrante ci ON ci.id = d.integrante_id
    WHERE d.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$disc = $stmt->get_result()->fetch_assoc();

if (!$disc) { header("Location: ../disciplina/"); exit; }

$hoje         = date('Y-m-d');
$dr           = (int)$disc['dias_restantes'];
$decorridos   = $disc['duracao_dias'] - max($dr, 0);
$pc_decorrido = $disc['duracao_dias'] > 0
    ? min(100, round(($decorridos / $disc['duracao_dias']) * 100))
    : 0;

$patentes      = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',
                  6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X'];
$status_labels = [1=>'Ativo',2=>'Afastado',3=>'Desligado',4=>'Suspenso'];
$status_badge  = [1=>'m-badge--success',2=>'m-badge--warning',
                  3=>'m-badge--danger',4=>'m-badge--metal'];

// Foto do integrante
$foto_view = $disc['foto'] ?? null;
$foto_ok   = $foto_view && file_exists(__DIR__ . '/../../' . $foto_view);
$foto_src  = $foto_ok ? ('../' . $foto_view) : null;

// Origem — para o botão Voltar
$origem = $_GET['origem'] ?? 'lista'; // 'lista' ou 'integrante'
?>
<!DOCTYPE html>
<html lang="pt-br">
    <base href="../">
    <head>
        <meta charset="utf-8"/>
        <title>Abutre's MC | Suspensão #<?= $id ?></title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
        <script>WebFont.load({google:{"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},active:function(){sessionStorage.fonts=true;}});</script>
        <link href="css/style.bundle.css" rel="stylesheet" type="text/css"/>
        <style>
            .info-label {
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: .05em;
                color: #9699a2;
                font-weight: 600;
                margin-bottom: 4px;
            }
            .info-value {
                font-size: 14px;
                color: #575962;
                font-weight: 500;
            }
            .info-block { margin-bottom: 20px; }
            .status-pill {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 6px 16px;
                border-radius: 30px;
                font-size: 13px;
                font-weight: 600;
            }
            .pill-ativa    { background: #fff0f3; color: #f4516c; border: 1px solid #f4516c; }
            .pill-encerrada{ background: #f8f9fa; color: #9699a2; border: 1px solid #d0d0d0; }
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
                                    Suspensão #<?= $id ?>
                                    <?php if ($disc['ativo']): ?>
                                        <span class="status-pill pill-ativa m--margin-left-10" style="font-size:12px; vertical-align:middle;">
                                            <i class="la la-gavel"></i> Em curso
                                        </span>
                                    <?php else: ?>
                                        <span class="status-pill pill-encerrada m--margin-left-10" style="font-size:12px; vertical-align:middle;">
                                            <i class="la la-check"></i> Encerrada
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
                                        <a href="disciplina/" class="m-nav__link">
                                            <span class="m-nav__link-text">Disciplina</span>
                                        </a>
                                    </li>
                                    <li class="m-nav__separator">—</li>
                                    <li class="m-nav__item">
                                        <span class="m-nav__link-text">Suspensão #<?= $id ?></span>
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <?php if ($disc['ativo']): ?>
                                <a href="disciplina/disciplina_form.php?id=<?= $id ?>"
                                   class="btn btn-sm btn-warning m-btn m-btn--icon m-btn--pill">
                                    <span><i class="la la-pencil"></i><span>Editar</span></span>
                                </a>
                                &nbsp;
                                <a href="disciplina/disciplina_encerrar.php?id=<?= $id ?>"
                                   class="btn btn-sm btn-success m-btn m-btn--icon m-btn--pill"
                                   onclick="return confirm('Encerrar esta suspensão e reativar <?= htmlspecialchars(addslashes($disc['apelido'])) ?>?')">
                                    <span><i class="la la-check-circle"></i><span>Fim de Suspensão</span></span>
                                </a>
                                &nbsp;
                                <?php endif; ?>
                                <?php if ($origem === 'integrante'): ?>
                                <a href="integrante_view?id=<?= $disc['integrante_id'] ?>"
                                   class="btn btn-sm btn-secondary m-btn m-btn--icon m-btn--pill">
                                    <span><i class="la la-arrow-left"></i><span>Voltar ao Perfil</span></span>
                                </a>
                                <?php else: ?>
                                <a href="disciplina/"
                                   class="btn btn-sm btn-secondary m-btn m-btn--icon m-btn--pill">
                                    <span><i class="la la-arrow-left"></i><span>Voltar</span></span>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="m-content">
                        <div class="row">

                            <!-- ══ COLUNA ESQUERDA: Integrante ══ -->
                            <div class="col-xl-3">
                                <div class="m-portlet m-portlet--mobile">
                                    <div class="m-portlet__body" style="text-align:center; padding:25px 15px;">

                                        <!-- Avatar -->
                                        <div style="width:80px;height:80px;border-radius:50%;background:#716aca;
                                                    display:flex;align-items:center;justify-content:center;
                                                    margin:0 auto 12px;overflow:hidden;
                                                    border:3px solid <?= $disc['ativo'] ? '#f4516c' : '#ebedf2' ?>;">
                                            <?php if ($foto_src): ?>
                                                <img src="<?= htmlspecialchars($foto_src) ?>"
                                                     style="width:100%;height:100%;object-fit:cover;">
                                            <?php else: ?>
                                                <i class="flaticon-user" style="font-size:2.5rem;color:#fff;"></i>
                                            <?php endif; ?>
                                        </div>

                                        <h4 class="m--font-boldest" style="margin-bottom:4px;">
                                            <?= htmlspecialchars($disc['apelido']) ?>
                                        </h4>
                                        <p class="m--font-metal" style="font-size:12px; margin-bottom:10px;">
                                            <?= htmlspecialchars($disc['nome']) ?>
                                        </p>
                                        <span class="m-badge m-badge--info m-badge--wide m--margin-right-5">
                                            Pat. <?= $patentes[$disc['patente']] ?? '—' ?>
                                        </span>
                                        <span class="m-badge <?= $status_badge[$disc['status_integrante']] ?> m-badge--wide">
                                            <?= $status_labels[$disc['status_integrante']] ?>
                                        </span>

                                        <div class="m--margin-top-20">
                                            <a href="integrante_view?id=<?= $disc['integrante_id'] ?>"
                                               class="btn btn-info btn-block m-btn m-btn--icon m-btn--pill"
                                               style="font-size:12px;">
                                                <span>
                                                    <i class="la la-eye"></i>
                                                    <span>Ver Perfil Completo</span>
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status da suspensão -->
                                <div class="m-portlet m-portlet--mobile"
                                     style="<?= $disc['ativo'] ? 'border:1px solid #f4516c;' : '' ?>">
                                    <div class="m-portlet__head"
                                         style="<?= $disc['ativo'] ? 'background:#fff5f6; border-bottom:1px solid #f4516c;' : '' ?>">
                                        <div class="m-portlet__head-caption">
                                            <div class="m-portlet__head-title">
                                                <h3 class="m-portlet__head-text <?= $disc['ativo'] ? 'm--font-danger' : 'm--font-metal' ?>">
                                                    <?= $disc['ativo'] ? 'Em Curso' : 'Encerrada' ?>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="m-portlet__body">
                                        <?php if ($disc['ativo']): ?>
                                            <!-- Progresso da suspensão ativa -->
                                            <div class="m--align-center m--margin-bottom-15">
                                                <div class="m--font-boldest m--font-danger" style="font-size:2.5rem; line-height:1;">
                                                    <?= $dr >= 0 ? $dr : 0 ?>
                                                </div>
                                                <div class="m--font-metal" style="font-size:12px;">
                                                    dia<?= $dr != 1 ? 's' : '' ?> restante<?= $dr != 1 ? 's' : '' ?>
                                                </div>
                                            </div>
                                            <div class="progress m-progress--sm m--margin-bottom-10">
                                                <div class="progress-bar m--bg-danger"
                                                     style="width:<?= $pc_decorrido ?>%"></div>
                                            </div>
                                            <div class="d-flex justify-content-between" style="font-size:11px; color:#9699a2;">
                                                <span><?= $decorridos ?> dia<?= $decorridos!=1?'s':'' ?> decorrido<?= $decorridos!=1?'s':'' ?></span>
                                                <span><?= $disc['duracao_dias'] ?> dias total</span>
                                            </div>
                                            <?php if ($dr < 0): ?>
                                            <div class="m-alert m-alert--icon m-alert--air m-alert--danger m--margin-top-15" role="alert">
                                                <div class="m-alert__icon"><i class="la la-exclamation-triangle"></i></div>
                                                <div class="m-alert__text" style="font-size:12px;">
                                                    Suspensão vencida há <strong><?= abs($dr) ?> dia<?= abs($dr)!=1?'s':'' ?></strong>.
                                                    Encerre manualmente.
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="m--align-center" style="padding:10px 0;">
                                                <i class="la la-check-circle m--font-success" style="font-size:2.5rem;"></i>
                                                <p class="m--font-metal m--margin-top-10" style="font-size:13px;">
                                                    Suspensão encerrada em<br>
                                                    <strong><?= $disc['encerrado_em'] ? date('d/m/Y', strtotime($disc['encerrado_em'])) : '—' ?></strong>
                                                </p>
                                                <?php if ($disc['encerrado_por']): ?>
                                                <p class="m--font-metal" style="font-size:12px;">
                                                    por <strong><?= htmlspecialchars($disc['encerrado_por']) ?></strong>
                                                </p>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            </div>
                            <!-- /COL ESQUERDA -->

                            <!-- ══ COLUNA DIREITA: Detalhes ══ -->
                            <div class="col-xl-9">
                                <div class="m-portlet m-portlet--mobile">
                                    <div class="m-portlet__head">
                                        <div class="m-portlet__head-caption">
                                            <div class="m-portlet__head-title">
                                                <h3 class="m-portlet__head-text">
                                                    <i class="la la-gavel m--font-danger"></i>
                                                    Detalhes da Medida Disciplinar
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="m-portlet__body">

                                        <div class="row">

                                            <!-- Duração e datas -->
                                            <div class="col-md-4">
                                                <div class="info-block">
                                                    <div class="info-label">Duração</div>
                                                    <div class="info-value">
                                                        <span class="m-badge m-badge--danger m-badge--wide" style="font-size:14px; padding:6px 14px;">
                                                            <?= $disc['duracao_dias'] ?> dias
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="info-block">
                                                    <div class="info-label">Data de Início</div>
                                                    <div class="info-value">
                                                        <i class="la la-calendar m--font-metal m--margin-right-5"></i>
                                                        <?= date('d/m/Y', strtotime($disc['data_inicio'])) ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="info-block">
                                                    <div class="info-label">Data de Fim</div>
                                                    <div class="info-value">
                                                        <i class="la la-calendar-times-o m--font-<?= $disc['ativo'] ? 'danger' : 'metal' ?> m--margin-right-5"></i>
                                                        <?= date('d/m/Y', strtotime($disc['data_fim'])) ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Aplicado por e registrado em -->
                                            <div class="col-md-4">
                                                <div class="info-block">
                                                    <div class="info-label">Aplicado por</div>
                                                    <div class="info-value">
                                                        <i class="la la-user m--font-metal m--margin-right-5"></i>
                                                        <?= htmlspecialchars($disc['aplicado_por']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="info-block">
                                                    <div class="info-label">Registrado em</div>
                                                    <div class="info-value m--font-metal">
                                                        <?= $disc['created_at']
                                                            ? date('d/m/Y \à\s H:i', strtotime($disc['created_at']))
                                                            : '—' ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="info-block">
                                                    <div class="info-label">Situação</div>
                                                    <div class="info-value">
                                                        <?php if ($disc['ativo']): ?>
                                                            <span class="status-pill pill-ativa">
                                                                <i class="la la-gavel"></i> Em curso
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="status-pill pill-encerrada">
                                                                <i class="la la-check"></i> Encerrada
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        <hr style="border-color:#ebedf2; margin:10px 0 20px;">

                                        <!-- Motivo (destaque) -->
                                        <div class="info-block">
                                            <div class="info-label">Motivo da Suspensão</div>
                                            <div style="background:#f8f9fa; border-left:4px solid #f4516c;
                                                        padding:15px 20px; border-radius:0 6px 6px 0;
                                                        font-size:14px; color:#575962; line-height:1.6; margin-top:6px;">
                                                <?= nl2br(htmlspecialchars($disc['motivo'])) ?>
                                            </div>
                                        </div>

                                        <?php if (!$disc['ativo'] && ($disc['encerrado_em'] || $disc['encerrado_por'])): ?>
                                        <hr style="border-color:#ebedf2; margin:10px 0 20px;">
                                        <div class="row">
                                            <?php if ($disc['encerrado_em']): ?>
                                            <div class="col-md-4">
                                                <div class="info-block">
                                                    <div class="info-label">Encerrada em</div>
                                                    <div class="info-value m--font-metal">
                                                        <?= date('d/m/Y', strtotime($disc['encerrado_em'])) ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            <?php if ($disc['encerrado_por']): ?>
                                            <div class="col-md-4">
                                                <div class="info-block">
                                                    <div class="info-label">Encerrada por</div>
                                                    <div class="info-value m--font-metal">
                                                        <?= htmlspecialchars($disc['encerrado_por']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>

                                    </div>
                                </div>

                                <!-- Ações -->
                                <?php if ($disc['ativo']): ?>
                                <div class="m-portlet m-portlet--mobile" style="border:1px solid #f4516c;">
                                    <div class="m-portlet__head" style="background:#fff5f6; border-bottom:1px solid #f4516c;">
                                        <div class="m-portlet__head-caption">
                                            <div class="m-portlet__head-title">
                                                <h3 class="m-portlet__head-text m--font-danger">
                                                    <i class="la la-cogs"></i> Ações
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="m-portlet__body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <a href="disciplina/disciplina_form.php?id=<?= $id ?>"
                                                   class="btn btn-warning btn-block m-btn m-btn--icon m-btn--pill">
                                                    <span>
                                                        <i class="la la-pencil"></i>
                                                        <span>Editar esta Suspensão</span>
                                                    </span>
                                                </a>
                                            </div>
                                            <div class="col-md-4">
                                                <a href="disciplina/disciplina_encerrar.php?id=<?= $id ?>"
                                                   class="btn btn-success btn-block m-btn m-btn--icon m-btn--pill"
                                                   onclick="return confirm('Encerrar esta suspensão e reativar <?= htmlspecialchars(addslashes($disc['apelido'])) ?>?')">
                                                    <span>
                                                        <i class="la la-check-circle"></i>
                                                        <span>Encerrar Suspensão</span>
                                                    </span>
                                                </a>
                                            </div>
                                            <div class="col-md-4">
                                                <a href="disciplina/disciplina_delete.php?id=<?= $id ?>&origem=<?= $origem ?>"
                                                   class="btn btn-danger btn-block m-btn m-btn--icon m-btn--pill"
                                                   onclick="return confirm('Excluir permanentemente? O status de <?= htmlspecialchars(addslashes($disc['apelido'])) ?> voltará para Ativo.')">
                                                    <span>
                                                        <i class="la la-trash"></i>
                                                        <span>Excluir Suspensão</span>
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                            </div>
                            <!-- /COL DIREITA -->

                        </div>
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
