<?php
    session_start();
    require_once ('../inc/general.php');

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

    // Filtro de período
    $ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');
    $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : 0; // 0 = todos os meses

    $meses_pt = [
        0=>'Todos',1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',
        5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',
        9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'
    ];

    // Buscar eventos com resumo de presença
    $where_mes = $mes > 0 ? "AND MONTH(e.data_evento) = $mes" : "";
    $sql = "
        SELECT
            e.id, e.tipo, e.nome, e.data_evento, e.observacao,
            COUNT(f.id)             AS total_convocados,
            SUM(f.presente = 1)     AS total_presentes,
            SUM(f.presente = 0)     AS total_ausentes
        FROM eventos e
        LEFT JOIN frequencias f ON f.evento_id = e.id
        WHERE e.faccao = 1
          AND YEAR(e.data_evento) = ?
          $where_mes
        GROUP BY e.id
        ORDER BY e.data_evento DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ano);
    $stmt->execute();
    $eventos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // KPIs gerais do período
    $total_eventos     = count($eventos);
    $total_obrigatorios = count(array_filter($eventos, fn($e) => $e['tipo'] == 6));
    $media_presenca = 0;
    if ($total_eventos > 0) {
        $soma = array_sum(array_map(function($e) {
            return $e['total_convocados'] > 0
                ? round(($e['total_presentes'] / $e['total_convocados']) * 100)
                : 0;
        }, $eventos));
        $media_presenca = round($soma / $total_eventos);
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
    <base href="../">
    <head>
        <meta charset="utf-8" />
        <title>Abutre's MC | Frequência</title>
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
                                        Frequência
                                    </h3>
                                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                                        <li class="m-nav__item m-nav__item--home">
                                            <a href="index" class="m-nav__link m-nav__link--icon">
                                                <i class="m-nav__link-icon la la-home"></i>
                                            </a>
                                        </li>
                                        <li class="m-nav__separator">—</li>
                                        <li class="m-nav__item">
                                            <span class="m-nav__link-text">Frequência</span>
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <a href="frequencia/resumo.php?ano=<?= $ano ?>"
                                       class="btn btn-sm btn-info m-btn m-btn--icon m-btn--pill">
                                        <span><i class="la la-bar-chart"></i><span>Resumo Geral</span></span>
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

                            <!-- FILTRO -->
                            <div class="m-portlet m-portlet--mobile">
                                <div class="m-portlet__body">
                                    <form class="form-inline" method="GET">
                                        <label class="mr-2 font-weight-bold">Período:</label>
                                        <select name="mes" class="form-control form-control-sm mr-2">
                                            <?php foreach ($meses_pt as $num => $nome): ?>
                                                <option value="<?= $num ?>" <?= $num == $mes ? 'selected' : '' ?>><?= $nome ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="number" name="ano" class="form-control form-control-sm mr-2"
                                               value="<?= $ano ?>" min="2020" max="2099" style="width:90px;">
                                        <button class="btn btn-sm btn-dark m-btn m-btn--icon m-btn--pill" type="submit">
                                            <span><i class="la la-search"></i><span>Filtrar</span></span>
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
                                                    <h4 class="m-widget24__title">Total de Eventos</h4><br>
                                                    <span class="m-widget24__desc">No período selecionado</span>
                                                    <span class="m-widget24__stats m--font-brand"><?= $total_eventos ?></span>
                                                    <div class="m--space-10"></div>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-brand" style="width:100%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Obrigatórios</span>
                                                    <span class="m-widget24__number"><?= $total_obrigatorios ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-lg-4 col-xl-4">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Média de Presença</h4><br>
                                                    <span class="m-widget24__desc">Média geral dos eventos</span>
                                                    <span class="m-widget24__stats <?= $media_presenca >= 70 ? 'm--font-success' : ($media_presenca >= 50 ? 'm--font-warning' : 'm--font-danger') ?>">
                                                        <?= $media_presenca ?>%
                                                    </span>
                                                    <div class="m--space-10"></div>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar <?= $media_presenca >= 70 ? 'm--bg-success' : ($media_presenca >= 50 ? 'm--bg-warning' : 'm--bg-danger') ?>"
                                                             style="width:<?= $media_presenca ?>%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Meta mínima recomendada</span>
                                                    <span class="m-widget24__number">70%</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-lg-4 col-xl-4">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Tipos de Evento</h4><br>
                                                    <span class="m-widget24__desc">Categorias disponíveis</span>
                                                    <span class="m-widget24__stats m--font-info"><?= count($tipos_evento) ?></span>
                                                    <div class="m--space-10"></div>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-info" style="width:100%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Ver resumo completo</span>
                                                    <span class="m-widget24__number">
                                                        <a href="frequencia/resumo.php?ano=<?= $ano ?>">→</a>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- LISTA DE EVENTOS -->
                            <div class="m-portlet m-portlet--mobile">
                                <div class="m-portlet__head">
                                    <div class="m-portlet__head-caption">
                                        <div class="m-portlet__head-title">
                                            <h3 class="m-portlet__head-text">
                                                Eventos <?= $mes > 0 ? '— ' . $meses_pt[$mes] : '' ?>/<?= $ano ?>
                                            </h3>
                                        </div>
                                    </div>
                                    <div class="m-portlet__head-tools">
                                        <a href="frequencia/evento_form.php"
                                           class="btn btn-sm btn-brand m-btn m-btn--icon m-btn--pill">
                                            <span><i class="la la-plus"></i><span>Novo Evento</span></span>
                                        </a>
                                    </div>
                                </div>
                                <div class="m-portlet__body">

                                    <?php if (empty($eventos)): ?>
                                        <div class="m--align-center" style="padding:40px 0;">
                                            <i class="la la-calendar m--font-metal" style="font-size:4rem;"></i>
                                            <h3 class="m--margin-top-10 m--font-metal">Nenhum evento encontrado neste período.</h3>
                                            <a href="frequencia/evento_form.php" class="btn btn-brand m-btn m-btn--pill m--margin-top-10">
                                                Cadastrar primeiro evento
                                            </a>
                                        </div>
                                    <?php else: ?>

                                    <div class="table-responsive">
                                        <table class="table table-hover m-table m-table--head-bg-brand">
                                            <thead>
                                                <tr>
                                                    <th>Data</th>
                                                    <th>Evento</th>
                                                    <th>Tipo</th>
                                                    <th class="m--align-center">Convocados</th>
                                                    <th class="m--align-center">Presentes</th>
                                                    <th class="m--align-center">Ausentes</th>
                                                    <th class="m--align-center">% Presença</th>
                                                    <th class="m--align-center">Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($eventos as $ev):
                                                    $pc = $ev['total_convocados'] > 0
                                                        ? round(($ev['total_presentes'] / $ev['total_convocados']) * 100)
                                                        : 0;
                                                    $pc_class = $pc >= 70 ? 'm--font-success' : ($pc >= 50 ? 'm--font-warning' : 'm--font-danger');
                                                ?>
                                                <tr>
                                                    <td class="m--font-metal" style="white-space:nowrap;">
                                                        <?= date('d/m/Y', strtotime($ev['data_evento'])) ?>
                                                    </td>
                                                    <td>
                                                        <span class="m--font-boldest"><?= htmlspecialchars($ev['nome']) ?></span>
                                                        <?php if ($ev['observacao']): ?>
                                                            <br><small class="m--font-metal"><?= htmlspecialchars($ev['observacao']) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="m-badge <?= $tipo_badge[$ev['tipo']] ?> m-badge--wide">
                                                            <?= $tipos_evento[$ev['tipo']] ?>
                                                        </span>
                                                    </td>
                                                    <td class="m--align-center m--font-metal"><?= $ev['total_convocados'] ?></td>
                                                    <td class="m--align-center m--font-success m--font-boldest"><?= $ev['total_presentes'] ?></td>
                                                    <td class="m--align-center m--font-danger"><?= $ev['total_ausentes'] ?></td>
                                                    <td class="m--align-center">
                                                        <span class="m--font-boldest <?= $pc_class ?>"><?= $pc ?>%</span>
                                                        <div class="progress m-progress--sm m--margin-top-5">
                                                            <div class="progress-bar <?= str_replace('font','bg',$pc_class) ?>"
                                                                 style="width:<?= $pc ?>%"></div>
                                                        </div>
                                                    </td>
                                                    <td class="m--align-center" style="white-space:nowrap;">
                                                        <a href="frequencia/chamada.php?id=<?= $ev['id'] ?>"
                                                           class="btn btn-sm btn-brand m-btn m-btn--icon m-btn--pill"
                                                           title="Fazer chamada">
                                                            <span><i class="la la-check-square-o"></i><span>Chamada</span></span>
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