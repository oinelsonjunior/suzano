<?php
    include_once('inc/display_errors.php');
    session_start();
    require_once ('inc/general.php');
    require_once 'inc/functions.php';

    // Filtros
    $filtro_status  = isset($_GET['status'])  ? (int)$_GET['status']  : 0;
    $filtro_patente = isset($_GET['patente']) ? (int)$_GET['patente'] : 0;
    $busca          = trim($_GET['busca'] ?? '');

    // Query principal
    $where = "WHERE faccao = '$id_faccao'";
    if ($filtro_status  > 0) $where .= " AND status = $filtro_status";
    if ($filtro_patente > 0) $where .= " AND patente = $filtro_patente";
    if ($busca !== '') {
        $busca_safe = $conn->real_escape_string($busca);
        $where .= " AND (apelido LIKE '%$busca_safe%' OR nome LIKE '%$busca_safe%')";
    }

    $sql = "SELECT * FROM cadastro_integrante $where ORDER BY patente ASC, apelido ASC";
    $result = $conn->query($sql);
    $integrantes = $result->fetch_all(MYSQLI_ASSOC);

    // KPIs sempre sobre todos (sem filtro)
    $res_kpi = $conn->query("
        SELECT
            COUNT(*) as total,
            SUM(status=1) as ativos,
            SUM(status=2) as afastados,
            SUM(status=3) as desligados,
            SUM(status=4) as suspensos
        FROM cadastro_integrante WHERE faccao = '$id_faccao' ORDER BY patente DESC
    ");
    $kpi = $res_kpi->fetch_assoc();

    $patentes      = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X'];
    $status_labels = [1=>'Ativo',2=>'Afastado',3=>'Desligado',4=>'Suspenso'];
    $status_badge  = [
        1=>'m-badge--success',
        2=>'m-badge--warning',
        3=>'m-badge--danger',
        4=>'m-badge--metal'
    ];

    function patente_label($p) {
        $map = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X'];
        return $map[$p] ?? '—';
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="utf-8" />
        <title>Abutre's MC | Integrantes</title>
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
                                        Integrantes — <?= htmlspecialchars($nome_faccao) ?>
                                    </h3>
                                    <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                                        <li class="m-nav__item m-nav__item--home">
                                            <a href="index" class="m-nav__link m-nav__link--icon">
                                                <i class="m-nav__link-icon la la-home"></i>
                                            </a>
                                        </li>
                                        <li class="m-nav__separator">—</li>
                                        <li class="m-nav__item">
                                            <span class="m-nav__link-text">Integrantes</span>
                                        </li>
                                    </ul>
                                </div>
                                <div>
                                    <a href="integrantes/integrante_form.php"
                                       class="btn btn-sm btn-brand m-btn m-btn--icon m-btn--pill">
                                        <span><i class="la la-plus"></i><span>Novo Integrante</span></span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="m-content">

                            <!-- KPIs -->
                            <div class="m-portlet m-portlet--mobile">
                                <div class="m-portlet__body m-portlet__body--no-padding">
                                    <div class="row m-row--no-padding m-row--col-separator-xl">

                                        <div class="col-md-6 col-lg-3 col-xl-3">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Total</h4><br>
                                                    <span class="m-widget24__desc">Todos os integrantes</span>
                                                    <span class="m-widget24__stats m--font-brand"><?= $kpi['total'] ?></span>
                                                    <div class="m--space-10"></div>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-brand" style="width:100%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Ativos</span>
                                                    <span class="m-widget24__number"><?= $kpi['ativos'] ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-lg-3 col-xl-3">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Afastados</h4><br>
                                                    <span class="m-widget24__desc">Temporariamente fora</span>
                                                    <span class="m-widget24__stats m--font-warning"><?= $kpi['afastados'] ?></span>
                                                    <div class="m--space-10"></div>
                                                    <?php $pc_af = $kpi['total'] > 0 ? round(($kpi['afastados']/$kpi['total'])*100) : 0 ?>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-warning" style="width:<?= $pc_af ?>%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Do total</span>
                                                    <span class="m-widget24__number"><?= $pc_af ?>%</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-lg-3 col-xl-3">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Suspensos</h4><br>
                                                    <span class="m-widget24__desc">Suspensão ativa</span>
                                                    <span class="m-widget24__stats m--font-metal"><?= $kpi['suspensos'] ?></span>
                                                    <div class="m--space-10"></div>
                                                    <?php $pc_sus = $kpi['total'] > 0 ? round(($kpi['suspensos']/$kpi['total'])*100) : 0 ?>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-metal" style="width:<?= $pc_sus ?>%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Do total</span>
                                                    <span class="m-widget24__number"><?= $pc_sus ?>%</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-lg-3 col-xl-3">
                                            <div class="m-widget24">
                                                <div class="m-widget24__item">
                                                    <h4 class="m-widget24__title">Desligados</h4><br>
                                                    <span class="m-widget24__desc">Fora do clube</span>
                                                    <span class="m-widget24__stats m--font-danger"><?= $kpi['desligados'] ?></span>
                                                    <div class="m--space-10"></div>
                                                    <?php $pc_des = $kpi['total'] > 0 ? round(($kpi['desligados']/$kpi['total'])*100) : 0 ?>
                                                    <div class="progress m-progress--sm">
                                                        <div class="progress-bar m--bg-danger" style="width:<?= $pc_des ?>%"></div>
                                                    </div>
                                                    <span class="m-widget24__change">Do total</span>
                                                    <span class="m-widget24__number"><?= $pc_des ?>%</span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- Filtros e busca -->
                            <div class="m-portlet m-portlet--mobile">
                                <div class="m-portlet__body">
                                    <form class="form-inline" method="GET">
                                        <label class="mr-2 font-weight-bold">Busca:</label>
                                        <input type="text" name="busca" class="form-control form-control-sm mr-3"
                                               value="<?= htmlspecialchars($busca) ?>"
                                               placeholder="Apelido ou nome..." style="width:200px;">

                                        <label class="mr-2 font-weight-bold">Status:</label>
                                        <select name="status" class="form-control form-control-sm mr-3">
                                            <option value="0">Todos</option>
                                            <?php foreach ($status_labels as $sv => $sl): ?>
                                                <option value="<?= $sv ?>" <?= $sv == $filtro_status ? 'selected' : '' ?>>
                                                    <?= $sl ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <label class="mr-2 font-weight-bold">Patente:</label>
                                        <select name="patente" class="form-control form-control-sm mr-3">
                                            <option value="0">Todas</option>
                                            <?php foreach ($patentes as $pv => $pl): ?>
                                                <option value="<?= $pv ?>" <?= $pv == $filtro_patente ? 'selected' : '' ?>>
                                                    Pat. <?= $pl ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <button class="btn btn-sm btn-dark m-btn m-btn--icon m-btn--pill mr-2" type="submit">
                                            <span><i class="la la-search"></i><span>Filtrar</span></span>
                                        </button>

                                        <?php if ($busca || $filtro_status || $filtro_patente): ?>
                                        <a href="integrantes_faccao" class="btn btn-sm btn-secondary m-btn m-btn--pill">
                                            <i class="la la-times"></i> Limpar
                                        </a>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>

                            <!-- Tabela de integrantes -->
                            <div class="m-portlet m-portlet--mobile">
                                <div class="m-portlet__head">
                                    <div class="m-portlet__head-caption">
                                        <div class="m-portlet__head-title">
                                            <h3 class="m-portlet__head-text">
                                                <?php if ($filtro_status > 0): ?>
                                                    <?= $status_labels[$filtro_status] ?>s
                                                <?php elseif ($busca): ?>
                                                    Resultado para "<?= htmlspecialchars($busca) ?>"
                                                <?php else: ?>
                                                    Todos os Integrantes
                                                <?php endif; ?>
                                                <small class="m--font-metal m--margin-left-10" style="font-size:13px;">
                                                    <?= count($integrantes) ?> encontrado<?= count($integrantes) != 1 ? 's' : '' ?>
                                                </small>
                                            </h3>
                                        </div>
                                    </div>
                                    <div class="m-portlet__head-tools">
                                        <a href="integrantes/integrante_form.php"
                                           class="btn btn-sm btn-brand m-btn m-btn--icon m-btn--pill">
                                            <span><i class="la la-plus"></i><span>Novo Integrante</span></span>
                                        </a>
                                    </div>
                                </div>
                                <div class="m-portlet__body">

                                    <?php if (empty($integrantes)): ?>
                                        <div class="m--align-center" style="padding:40px 0;">
                                            <i class="la la-users m--font-metal" style="font-size:4rem;"></i>
                                            <h3 class="m--font-metal m--margin-top-10">Nenhum integrante encontrado.</h3>
                                            <?php if ($busca || $filtro_status || $filtro_patente): ?>
                                                <a href="integrantes_faccao" class="btn btn-secondary m-btn m-btn--pill m--margin-top-10">
                                                    Ver todos
                                                </a>
                                            <?php else: ?>
                                                <a href="integrantes/integrante_form.php" class="btn btn-brand m-btn m-btn--pill m--margin-top-10">
                                                    Cadastrar primeiro integrante
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>

                                    <div class="table-responsive">
                                        <table class="table table-hover m-table m-table--head-bg-brand">
                                            <thead>
                                                <tr>
                                                    <th style="width:40px;">#</th>
                                                    <th>Apelido</th>
                                                    <th>Nome</th>
                                                    <th class="m--align-center">Patente</th>
                                                    <th class="m--align-center">Status</th>
                                                    <th class="m--align-center">Apresentação</th>
                                                    <th class="m--align-center">Contato</th>
                                                    <th class="m--align-center">Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($integrantes as $i => $r): ?>
                                                <tr>
                                                    <td class="m--font-metal"><?= $i + 1 ?></td>
                                                    <td>
                                                        <a href="integrante_view?id=<?= $r['id'] ?>"
                                                           class="m--font-boldest m--font-brand"
                                                           style="text-decoration:none;">
                                                            <?= htmlspecialchars($r['apelido']) ?>
                                                        </a>
                                                    </td>
                                                    <td class="m--font-metal">
                                                        <?= htmlspecialchars($r['nome']) ?>
                                                    </td>
                                                    <td class="m--align-center">
                                                        <span class="m-badge m-badge--info m-badge--wide">
                                                            Pat. <?= patente_label($r['patente']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="m--align-center">
                                                        <span class="m-badge <?= $status_badge[$r['status']] ?> m-badge--wide">
                                                            <?= $status_labels[$r['status']] ?>
                                                        </span>
                                                    </td>
                                                    <td class="m--align-center m--font-metal" style="font-size:12px;">
                                                        <?= $r['data_apresentacao'] && $r['data_apresentacao'] != '0000-00-00 00:00:00'
                                                            ? date('d/m/Y', strtotime($r['data_apresentacao']))
                                                            : '—' ?>
                                                    </td>
                                                    <td class="m--align-center">
                                                        <?php $cel = $r['celular'] ?: $r['telefone']; ?>
                                                        <?php if ($cel): ?>
                                                            <a href="https://wa.me/55<?= preg_replace('/\D/','',$cel) ?>"
                                                               target="_blank"
                                                               class="btn btn-xs btn-success m-btn m-btn--pill"
                                                               title="<?= htmlspecialchars($cel) ?>"
                                                               style="font-size:11px; padding:3px 8px;">
                                                                <i class="la la-whatsapp"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="m--font-metal">—</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="m--align-center" style="white-space:nowrap;">
                                                        <a href="integrante_view?id=<?= $r['id'] ?>"
                                                           class="btn btn-sm btn-info m-btn m-btn--icon m-btn--pill"
                                                           title="Ver perfil">
                                                            <span><i class="la la-eye"></i></span>
                                                        </a>
                                                        <a href="integrantes/integrante_form.php?id=<?= $r['id'] ?>"
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

                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
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
    </body>
</html>