<?php
    session_start();
    require_once ('../inc/general.php');

    $ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');
    $tipo_filtro = isset($_GET['tipo']) ? (int)$_GET['tipo'] : 0;

    $tipos_evento = [
        1 => 'Sede Estadual (padrão)',
        2 => 'Sede Estadual (reunião geral)',
        3 => 'Sede Suzano (reunião)',
        4 => 'Sede Suzano (confraternização)',
        5 => 'Evento Fora',
        6 => 'Evento Obrigatório',
    ];

    $tipo_badge = [
        1 => 'm-badge--metal',
        2 => 'm-badge--brand',
        3 => 'm-badge--info',
        4 => 'm-badge--success',
        5 => 'm-badge--warning',
        6 => 'm-badge--danger',
    ];

    $where_tipo = $tipo_filtro > 0 ? "AND e.tipo = $tipo_filtro" : "";

    // Resumo por integrante
    $stmt = $conn->prepare("
        SELECT
            ci.id, ci.apelido, ci.nome, ci.patente, ci.status,
            COUNT(f.id)              AS total_eventos,
            SUM(f.presente = 1)      AS total_presentes,
            SUM(f.presente = 0)      AS total_ausentes
        FROM cadastro_integrante ci
        LEFT JOIN frequencias f      ON f.integrante_id = ci.id
        LEFT JOIN eventos e          ON e.id = f.evento_id
                                     AND YEAR(e.data_evento) = ?
                                     AND e.data_evento < CURDATE()
                                     $where_tipo
        WHERE ci.faccao = 1
          AND ci.status IN (1, 2, 4)
        GROUP BY ci.id
        ORDER BY total_presentes DESC, ci.apelido ASC
    ");
    $stmt->bind_param("i", $ano);
    $stmt->execute();
    $por_integrante = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Resumo por tipo de evento
    $stmt2 = $conn->prepare("
        SELECT
            e.tipo,
            COUNT(DISTINCT e.id)     AS total_eventos,
            COUNT(f.id)              AS total_registros,
            SUM(f.presente = 1)      AS total_presentes
        FROM eventos e
        LEFT JOIN frequencias f ON f.evento_id = e.id
        WHERE e.faccao = 1 AND YEAR(e.data_evento) = ?
          AND e.data_evento < CURDATE()
        GROUP BY e.tipo
        ORDER BY e.tipo ASC
    ");
    $stmt2->bind_param("i", $ano);
    $stmt2->execute();
    $por_tipo = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

    // Eventos obrigatórios — quem faltou
    $stmt3 = $conn->prepare("
        SELECT
            ci.apelido,
            COUNT(f.id)          AS obrigatorios_total,
            SUM(f.presente = 1)  AS obrigatorios_presentes,
            SUM(f.presente = 0)  AS obrigatorios_faltas
        FROM frequencias f
        INNER JOIN eventos e             ON e.id = f.evento_id AND e.tipo = 6 AND YEAR(e.data_evento) = ? AND e.data_evento < CURDATE()
        INNER JOIN cadastro_integrante ci ON ci.id = f.integrante_id
        WHERE ci.faccao = 1
        GROUP BY ci.id
        HAVING obrigatorios_faltas > 0
        ORDER BY obrigatorios_faltas DESC, ci.apelido ASC
    ");
    $stmt3->bind_param("i", $ano);
    $stmt3->execute();
    $faltas_obrigatorios = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);

    function patente_label($p) {
        $map = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X'];
        return $map[$p] ?? '—';
    }
    function status_label($s) {
        return [1=>'Ativo',2=>'Afastado',3=>'Desligado',4=>'Suspenso'][$s] ?? '—';
    }
    function status_badge($s) {
        return [1=>'m-badge--success',2=>'m-badge--warning',3=>'m-badge--danger',4=>'m-badge--metal'][$s] ?? '';
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
    <base href="../">
    <head>
        <meta charset="utf-8" />
        <title>Abutre's MC | Resumo de Frequência</title>
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
                                        Resumo de Frequência — <?= $ano ?>
                                    </h3>
                                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                                        <li class="m-nav__item m-nav__item--home">
                                            <a href="index" class="m-nav__link m-nav__link--icon">
                                                <i class="m-nav__link-icon la la-home"></i>
                                            </a>
                                        </li>
                                        <li class="m-nav__separator">—</li>
                                        <li class="m-nav__item">
                                            <a href="frequencia/index.php" class="m-nav__link">
                                                <span class="m-nav__link-text">Frequência</span>
                                            </a>
                                        </li>
                                        <li class="m-nav__separator">—</li>
                                        <li class="m-nav__item">
                                            <span class="m-nav__link-text">Resumo</span>
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <a href="frequencia/index.php?ano=<?= $ano ?>"
                                       class="btn btn-sm btn-secondary m-btn m-btn--icon m-btn--pill">
                                        <span><i class="la la-arrow-left"></i><span>Voltar</span></span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="m-content">

                            <!-- FILTRO -->
                            <div class="m-portlet m-portlet--mobile">
                                <div class="m-portlet__body">
                                    <form class="form-inline" method="GET">
                                        <label class="mr-2 font-weight-bold">Ano:</label>
                                        <input type="number" name="ano" class="form-control form-control-sm mr-2"
                                               value="<?= $ano ?>" min="2020" max="2099" style="width:90px;">
                                        <label class="mr-2 font-weight-bold">Tipo:</label>
                                        <select name="tipo" class="form-control form-control-sm mr-2">
                                            <option value="0">Todos os tipos</option>
                                            <?php foreach ($tipos_evento as $id => $nome): ?>
                                                <option value="<?= $id ?>" <?= $id == $tipo_filtro ? 'selected' : '' ?>><?= $nome ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-sm btn-dark m-btn m-btn--icon m-btn--pill" type="submit">
                                            <span><i class="la la-search"></i><span>Filtrar</span></span>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- FAIXA 1: Resumo por tipo de evento -->
                            <div class="m-portlet m-portlet--mobile">
                                <div class="m-portlet__head">
                                    <div class="m-portlet__head-caption">
                                        <div class="m-portlet__head-title">
                                            <h3 class="m-portlet__head-text">Presença por Tipo de Evento — <?= $ano ?></h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="m-portlet__body">
                                    <?php if (empty($por_tipo)): ?>
                                        <p class="m--font-metal m--align-center">Nenhum evento registrado em <?= $ano ?>.</p>
                                    <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover m-table m-table--head-bg-brand">
                                            <thead>
                                                <tr>
                                                    <th>Tipo</th>
                                                    <th class="m--align-center">Eventos</th>
                                                    <th class="m--align-center">Convocações</th>
                                                    <th class="m--align-center">Presenças</th>
                                                    <th class="m--align-center">% Média</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($por_tipo as $t):
                                                    $pc_t = $t['total_registros'] > 0
                                                        ? round(($t['total_presentes'] / $t['total_registros']) * 100)
                                                        : 0;
                                                    $cls = $pc_t >= 70 ? 'm--font-success' : ($pc_t >= 50 ? 'm--font-warning' : 'm--font-danger');
                                                ?>
                                                <tr>
                                                    <td>
                                                        <span class="m-badge <?= $tipo_badge[$t['tipo']] ?> m-badge--wide">
                                                            <?= $tipos_evento[$t['tipo']] ?>
                                                        </span>
                                                    </td>
                                                    <td class="m--align-center m--font-metal"><?= $t['total_eventos'] ?></td>
                                                    <td class="m--align-center m--font-metal"><?= $t['total_registros'] ?></td>
                                                    <td class="m--align-center m--font-success m--font-boldest"><?= $t['total_presentes'] ?></td>
                                                    <td class="m--align-center">
                                                        <span class="m--font-boldest <?= $cls ?>"><?= $pc_t ?>%</span>
                                                        <div class="progress m-progress--sm m--margin-top-5">
                                                            <div class="progress-bar <?= str_replace('font','bg',$cls) ?>" style="width:<?= $pc_t ?>%"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- FAIXA 2: Por integrante + Faltas em obrigatórios -->
                            <div class="row">

                                <!-- Ranking por integrante -->
                                <div class="col-xl-8">
                                    <div class="m-portlet m-portlet--full-height">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">
                                                        Frequência por Integrante — <?= $ano ?>
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">
                                            <?php if (empty($por_integrante)): ?>
                                                <p class="m--font-metal m--align-center">Nenhum dado disponível.</p>
                                            <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover m-table m-table--head-bg-info">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Apelido</th>
                                                            <th class="m--align-center">Patente</th>
                                                            <th class="m--align-center">Status</th>
                                                            <th class="m--align-center">Eventos</th>
                                                            <th class="m--align-center">Presentes</th>
                                                            <th class="m--align-center">Faltas</th>
                                                            <th class="m--align-center">%</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($por_integrante as $i => $r):
                                                            $pc_r = $r['total_eventos'] > 0
                                                                ? round(($r['total_presentes'] / $r['total_eventos']) * 100)
                                                                : 0;
                                                            $cls_r = $pc_r >= 70 ? 'm--font-success' : ($pc_r >= 50 ? 'm--font-warning' : 'm--font-danger');
                                                        ?>
                                                        <tr>
                                                            <td class="m--font-metal"><?= $i + 1 ?></td>
                                                            <td class="m--font-boldest"><?= htmlspecialchars($r['apelido']) ?></td>
                                                            <td class="m--align-center">
                                                                <span class="m-badge m-badge--info m-badge--wide">
                                                                    Pat. <?= patente_label($r['patente']) ?>
                                                                </span>
                                                            </td>
                                                            <td class="m--align-center">
                                                                <span class="m-badge <?= status_badge($r['status']) ?> m-badge--wide">
                                                                    <?= status_label($r['status']) ?>
                                                                </span>
                                                            </td>
                                                            <td class="m--align-center m--font-metal"><?= $r['total_eventos'] ?></td>
                                                            <td class="m--align-center m--font-success m--font-boldest"><?= $r['total_presentes'] ?></td>
                                                            <td class="m--align-center <?= $r['total_ausentes'] > 0 ? 'm--font-danger' : 'm--font-metal' ?>">
                                                                <?= $r['total_ausentes'] ?>
                                                            </td>
                                                            <td class="m--align-center">
                                                                <?php if ($r['total_eventos'] > 0): ?>
                                                                    <span class="m--font-boldest <?= $cls_r ?>"><?= $pc_r ?>%</span>
                                                                    <div class="progress m-progress--sm m--margin-top-5">
                                                                        <div class="progress-bar <?= str_replace('font','bg',$cls_r) ?>" style="width:<?= $pc_r ?>%"></div>
                                                                    </div>
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

                                <!-- Faltas em eventos obrigatórios -->
                                <div class="col-xl-4">
                                    <div class="m-portlet m-portlet--full-height">
                                        <div class="m-portlet__head">
                                            <div class="m-portlet__head-caption">
                                                <div class="m-portlet__head-title">
                                                    <h3 class="m-portlet__head-text">
                                                        <i class="la la-exclamation-triangle m--font-danger"></i>
                                                        Faltas em Obrigatórios
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="m-portlet__body">
                                            <?php if (empty($faltas_obrigatorios)): ?>
                                                <div class="m--align-center" style="padding:30px 0;">
                                                    <i class="la la-check-circle m--font-success" style="font-size:3rem;"></i>
                                                    <h4 class="m--font-success m--margin-top-10">
                                                        Nenhuma falta em evento obrigatório!
                                                    </h4>
                                                </div>
                                            <?php else: ?>
                                            <div class="m-widget4">
                                                <?php foreach ($faltas_obrigatorios as $fo): ?>
                                                <div class="m-widget4__item">
                                                    <div class="m-widget4__img m-widget4__img--icon">
                                                        <i class="flaticon-user m--font-danger" style="font-size:2rem;"></i>
                                                    </div>
                                                    <div class="m-widget4__info">
                                                        <span class="m-widget4__title">
                                                            <?= htmlspecialchars($fo['apelido']) ?>
                                                        </span>
                                                        <br>
                                                        <span class="m-widget4__sub">
                                                            <?= $fo['obrigatorios_presentes'] ?>/<?= $fo['obrigatorios_total'] ?> presências
                                                        </span>
                                                    </div>
                                                    <span class="m-widget4__ext">
                                                        <span class="m-badge m-badge--danger m-badge--wide">
                                                            <?= $fo['obrigatorios_faltas'] ?> falta<?= $fo['obrigatorios_faltas'] > 1 ? 's' : '' ?>
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
                            <!-- /FAIXA 2 -->

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